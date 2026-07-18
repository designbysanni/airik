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
 * to get flagged as spam.
 */
return [
    'ghl_token' => 'pit-REPLACE_WITH_REAL_PRIVATE_INTEGRATION_TOKEN',
    'ghl_location_id' => 'qKXPbny1l22naqOolkOb',
    'notify_email' => 'airikcrawford@gmail.com',
    'from_email' => 'info@airikart.com',
];
