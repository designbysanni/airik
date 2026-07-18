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
 */
return [
    'ghl_token' => 'pit-REPLACE_WITH_REAL_PRIVATE_INTEGRATION_TOKEN',
    'ghl_location_id' => 'qKXPbny1l22naqOolkOb',
];
