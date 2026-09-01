<?php
/**
 * Copy this file to config.php (same folder) and fill in real values.
 * config.php is gitignored — it must NEVER be committed. On Hostinger,
 * upload the real config.php directly via File Manager/SFTP, not git,
 * since the git-deployed directory won't include gitignored files.
 *
 * ghl_token: a GHL Private Integration Token (starts with "pit-"), created
 * under the GHL sub-account: Settings -> Private Integrations. Needs the
 * contacts.write scope. This is the same kind of secret referenced in
 * CLAUDE.md's "GHL integration" section — treat it exactly the same way:
 * server-side only, never in any file that reaches the browser or git.
 *
 * ghl_location_id: NOT a secret, safe to reference anywhere — it's the
 * sub-account ID from the GHL location URL
 * (https://app.guaranteedcrm.io/v2/location/<this-id>).
 *
 * notify_email: where the urgent "someone submitted a form" email goes.
 * PHP's mail() accepts a comma-separated list here if more than one person
 * should get it (e.g. during testing: "airikcrawford@gmail.com, you@example.com").
 * This fires independently of the GHL sync — see submit-lead.php.
 *
 * from_email: the "From" address on that notification email. Should be a
 * real mailbox on this domain (e.g. info@airikart.com) for best
 * deliverability — an unconfigured/nonexistent From address is more likely
 * to get flagged as spam. This is ALSO the address SMTP authenticates as
 * below, so it must be a real, existing mailbox, not just an alias.
 *
 * smtp_host / smtp_port / smtp_secure / smtp_user / smtp_pass: required for
 * the notification email to actually be delivered (2026-08-29: PHP's raw
 * mail() was confirmed reporting success on every call while never once
 * actually reaching the inbox — see the doc comment in submit-lead.php).
 * These authenticate as the real from_email mailbox and send over its own
 * SMTP server instead, which is what actually fixes deliverability. Find
 * the right host/port in Hostinger's hPanel under Emails -> that mailbox ->
 * "Configure Email Client" (commonly smtp.hostinger.com, port 465 with
 * smtp_secure 'ssl', or port 587 with smtp_secure 'tls' for STARTTLS —
 * confirm against what hPanel actually shows for this account rather than
 * assuming). smtp_user is normally the full email address (from_email);
 * smtp_pass is that mailbox's real password — treat it with the same care
 * as ghl_token above. Leaving these blank falls back to the old (unreliable)
 * mail() behavior rather than breaking outright, but don't leave it that
 * way — fill them in.
 */
return [
    'ghl_token' => 'pit-REPLACE_WITH_REAL_PRIVATE_INTEGRATION_TOKEN',
    'ghl_location_id' => 'qKXPbny1l22naqOolkOb',
    'notify_email' => 'airikcrawford@gmail.com',
    'from_email' => 'info@airikart.com',
    'smtp_host' => 'smtp.hostinger.com',
    'smtp_port' => 465,
    'smtp_secure' => 'ssl',
    'smtp_user' => 'info@airikart.com',
    'smtp_pass' => 'REPLACE_WITH_REAL_MAILBOX_PASSWORD',
];
