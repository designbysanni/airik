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
 * The "notify someone immediately" requirement is handled directly here via
 * PHP mail(), NOT via a GHL workflow — GHL's workflow builder turned out to
 * have real UI friction for "email an arbitrary address" (its "Email"
 * action only sends to the contact; "Internal Notification" only reaches
 * GHL platform users, not arbitrary addresses) that isn't worth fighting
 * when this script already has every field in hand. GHL's tag + note are
 * still created for his CRM/pipeline, but the notification email no longer
 * depends on that succeeding, or on GHL workflow config at all — see
 * $notifyResult below, which fires independently of the GHL try/catch.
 */

header('Content-Type: application/json');

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
    $tag = 'Website Lead - Contact';
    $noteTitle = 'Website Contact Form Submission';
    $projectType = clean($data['project-type'] ?? '', 100);
    $budget = clean($data['budget'] ?? '', 100);
    $message = clean($data['message'] ?? '', 3000);
    $noteBody = "Project type: {$projectType}\nBudget: {$budget}\nMessage: {$message}";
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
    $upsert = ghlRequest('POST', '/contacts/upsert', $config['ghl_token'], [
        'name' => $name,
        'email' => $email,
        'locationId' => $config['ghl_location_id'],
        'source' => $ghlSource,
    ]);

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
    $headers = [
        'From: AAEC Website <' . $fromAddress . '>',
        'Reply-To: ' . $email, // hit reply and it goes straight to the lead
        'Content-Type: text/plain; charset=UTF-8',
        'X-Priority: 1 (Highest)',
        'X-MSMail-Priority: High',
        'Importance: High',
    ];

    $notifyOk = @mail($notifyTo, $subject, $body, implode("\r\n", $headers));
    if (!$notifyOk) {
        error_log('submit-lead.php: mail() to notify_email failed');
    }
}

if ($notifyOk || $ghlOk) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'Could not send this right now. Please email info@airikart.com directly.',
    ]);
}
