<?php
/**
 * Receives Contact/Careers form submissions (fetch() from main.js), creates
 * or updates the contact in GHL, tags it by source, and attaches the
 * free-text details as a note. See CLAUDE.md "GHL integration" for the
 * full architecture and why this exists instead of a GHL-hosted form.
 *
 * GHL API reference (verified live 2026-07-18):
 * https://marketplace.gohighlevel.com/docs/ghl/contacts/upsert-contact
 * https://marketplace.gohighlevel.com/docs/ghl/contacts/add-tags
 * https://marketplace.gohighlevel.com/docs/ghl/contacts/create-note
 *
 * The "notify someone immediately" requirement is handled directly here,
 * NOT via a GHL workflow — GHL's workflow builder turned out to have real
 * UI friction for "email an arbitrary address" (its "Email" action only
 * sends to the contact; "Internal Notification" only reaches GHL platform
 * users, not arbitrary addresses) that isn't worth fighting when this
 * script already has every field in hand. GHL's tag + note are still
 * created for his CRM/pipeline, but the notification email no longer
 * depends on that succeeding, or on GHL workflow config at all — see the
 * notification block below, which fires independently of the GHL try/catch.
 *
 * Notification delivery: sends over authenticated SMTP (via the vendored
 * PHPMailer in api/lib/PHPMailer/) using the real info@airikart.com
 * mailbox, not PHP's raw mail(). This is a deliberate fix (2026-08-29) —
 * mail() reported success on every call but Airik never received a single
 * notification, even after SPF/DKIM/DMARC were all confirmed correctly
 * configured for the domain. Root cause: mail() on Hostinger shared
 * hosting sends through the web server's local relay, which is a
 * completely different outbound path than the domain's actual
 * (DKIM-signed) mailbox infrastructure — the DNS records being correct
 * doesn't help if the message never goes out through the server they
 * authenticate. Authenticating as the real mailbox and sending via its own
 * SMTP server sidesteps that gap entirely. See config.example.php for the
 * new smtp_* keys this requires; mail() is kept as a fallback only for the
 * (temporary) case where those keys aren't filled in yet.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/lib/PHPMailer/Exception.php';
require_once __DIR__ . '/lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    error_log('submit-lead.php: config.php missing — copy config.example.php and fill in real values');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server not configured']);
    exit;
}
$config = require $configPath;

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Honeypot: a hidden "website" field real users never see or fill in.
// Bots that auto-fill every input trip it. Pretend success so they don't
// learn anything from the response.
if (!empty($data['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

function clean($value, $maxLength = 500) {
    $value = is_string($value) ? $value : '';
    $value = preg_replace('/[\r\n]+/', ' ', $value); // single-line fields only
    $value = trim($value);
    return mb_substr($value, 0, $maxLength);
}

$source = clean($data['source'] ?? '');
if (!in_array($source, ['contact', 'careers'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid form source']);
    exit;
}

$name = clean($data['name'] ?? '', 100);
$email = trim((string) ($data['email'] ?? ''));
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Name and a valid email are required']);
    exit;
}

if ($source === 'contact') {
    $phone = clean($data['phone'] ?? '', 30);
    if ($phone === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Phone number is required']);
        exit;
    }
    $tag = 'Website Lead - Contact';
    $noteTitle = 'Website Contact Form Submission';
    $projectType = clean($data['project-type'] ?? '', 100);
    $budget = clean($data['budget'] ?? '', 100);
    $message = clean($data['message'] ?? '', 3000);
    $noteBody = "Phone: {$phone}\nProject type: {$projectType}\nBudget: {$budget}\nMessage: {$message}";
    $ghlSource = 'Website - Start a Project form';
} else {
    $tag = 'Website Lead - Careers';
    $noteTitle = 'Website Careers Application';
    $area = clean($data['area'] ?? '', 100);
    $message = clean($data['message'] ?? '', 3000);
    $noteBody = "Area of interest: {$area}\nMessage: {$message}";
    $ghlSource = 'Website - Careers form';
}

/**
 * Minimal GHL v2 API client. Throws on any non-2xx response or transport
 * error so the caller's try/catch handles both the same way.
 */
function ghlRequest($method, $path, $token, ?array $body = null) {
    $ch = curl_init('https://services.leadconnectorhq.com' . $path);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
            'Version: 2021-07-28',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new Exception("GHL request to {$path} failed: {$curlError}");
    }
    if ($status < 200 || $status >= 300) {
        throw new Exception("GHL {$path} returned HTTP {$status}: {$response}");
    }
    return json_decode($response, true);
}

// GHL sync: valuable for the CRM/pipeline, but must not block or break the
// notification email below if it fails for any reason.
$ghlOk = true;
$ghlError = null;
try {
    $upsertBody = [
        'name' => $name,
        'email' => $email,
        'locationId' => $config['ghl_location_id'],
        'source' => $ghlSource,
    ];
    if ($source === 'contact') {
        $upsertBody['phone'] = $phone;
    }
    $upsert = ghlRequest('POST', '/contacts/upsert', $config['ghl_token'], $upsertBody);

    $contactId = $upsert['contact']['id'] ?? null;
    if (!$contactId) {
        throw new Exception('Upsert response had no contact id: ' . json_encode($upsert));
    }

    ghlRequest('POST', "/contacts/{$contactId}/tags", $config['ghl_token'], [
        'tags' => [$tag],
    ]);

    ghlRequest('POST', "/contacts/{$contactId}/notes", $config['ghl_token'], [
        'title' => $noteTitle,
        'body' => $noteBody,
    ]);
} catch (Exception $e) {
    $ghlOk = false;
    $ghlError = $e->getMessage();
    error_log('submit-lead.php GHL error: ' . $ghlError);
}

// Direct notification email — the actual "someone needs to see this now"
// mechanism. Fires regardless of whether GHL succeeded, so a lead never
// gets missed just because the CRM sync had a bad moment.
$notifyTo = $config['notify_email'] ?? null;
$notifyOk = false;
if ($notifyTo) {
    $subjectLine = ($source === 'contact' ? 'New Project Inquiry' : 'New Careers Application')
        . " from {$name} - Contact ASAP";
    $subject = '=?UTF-8?B?' . base64_encode("URGENT - {$subjectLine}") . '?='; // safe for non-ASCII names

    $lines = [
        'New submission through the website. Please reach out by phone or email within 24 hours.',
        '',
        "Name: {$name}",
        "Email: {$email}",
    ];
    if ($source === 'contact') {
        $lines[] = "Phone: {$phone}";
        $lines[] = "Project type: {$projectType}";
        $lines[] = "Budget: {$budget}";
        $lines[] = "Message: {$message}";
    } else {
        $lines[] = "Area of interest: {$area}";
        $lines[] = "Message: {$message}";
    }
    if (!$ghlOk) {
        $lines[] = '';
        $lines[] = "(Note: syncing this to GHL failed, so it may not show up there yet — {$ghlError})";
    }
    $body = implode("\n", $lines);

    $fromAddress = $config['from_email'] ?? 'info@airikart.com';
    $notifyError = null;

    // Preferred path: authenticated SMTP through the real mailbox (see the
    // file-level doc comment above for why this replaced mail()).
    $notifyMethod = 'none';
    if (!empty($config['smtp_host']) && !empty($config['smtp_user']) && !empty($config['smtp_pass'])) {
        try {
            $mailer = new PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $config['smtp_host'];
            $mailer->Port = $config['smtp_port'] ?? 465;
            $mailer->SMTPAuth = true;
            $mailer->Username = $config['smtp_user'];
            $mailer->Password = $config['smtp_pass'];
            $mailer->SMTPSecure = $config['smtp_secure'] ?? PHPMailer::ENCRYPTION_SMTPS; // 'ssl' (465) or 'tls' for STARTTLS (587)
            $mailer->Timeout = 15;
            $mailer->CharSet = PHPMailer::CHARSET_UTF8;

            $mailer->setFrom($fromAddress, 'AAEC Website');
            foreach (explode(',', $notifyTo) as $recipient) {
                $recipient = trim($recipient);
                if ($recipient !== '') {
                    $mailer->addAddress($recipient);
                }
            }
            $mailer->addReplyTo($email, $name); // hit reply and it goes straight to the lead
            $mailer->Subject = "URGENT - {$subjectLine}";
            $mailer->Body = $body;
            $mailer->isHTML(false);
            $mailer->priority = 1;
            $mailer->addCustomHeader('X-MSMail-Priority', 'High');
            $mailer->addCustomHeader('Importance', 'High');

            $mailer->send();
            $notifyOk = true;
            $notifyMethod = 'smtp';
        } catch (PHPMailerException $e) {
            $notifyError = $mailer->ErrorInfo ?: $e->getMessage();
            error_log('submit-lead.php: SMTP send failed — ' . $notifyError);
        }
    } else {
        error_log('submit-lead.php: smtp_host/smtp_user/smtp_pass not configured, falling back to mail() — see config.example.php');
    }

    // Fallback only: raw mail() is unreliable on this host (see doc comment
    // above) but still better than nothing while SMTP isn't configured yet.
    if (!$notifyOk) {
        $headers = [
            'From: AAEC Website <' . $fromAddress . '>',
            'Reply-To: ' . $email,
            'Content-Type: text/plain; charset=UTF-8',
            'X-Priority: 1 (Highest)',
            'X-MSMail-Priority: High',
            'Importance: High',
        ];
        $notifyOk = @mail($notifyTo, $subject, $body, implode("\r\n", $headers));
        $notifyMethod = $notifyOk ? 'mail()-fallback' : 'none';
        if (!$notifyOk) {
            error_log('submit-lead.php: mail() fallback to notify_email also failed');
        }
    }
}

// TEMP DIAGNOSTIC — remove after confirming the SMTP fix actually delivers.
// Only active when the request carries ?debug=aaec2026.
$debug = null;
if (($_GET['debug'] ?? '') === 'aaec2026') {
    $debug = [
        'ghlOk' => $ghlOk,
        'ghlError' => $ghlError,
        'notifyTo' => $notifyTo ?? null,
        'notifyOk' => $notifyOk,
        'notifyMethod' => $notifyMethod ?? 'n/a',
        'notifyError' => $notifyError ?? null,
    ];
}

if ($notifyOk || $ghlOk) {
    $out = ['success' => true];
    if ($debug !== null) $out['debug'] = $debug;
    echo json_encode($out);
} else {
    http_response_code(502);
    $out = [
        'success' => false,
        'error' => 'Could not send this right now. Please email info@airikart.com directly.',
    ];
    if ($debug !== null) $out['debug'] = $debug;
    echo json_encode($out);
}
