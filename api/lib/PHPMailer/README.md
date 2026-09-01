# Vendored: PHPMailer v7.1.1

Only `PHPMailer.php`, `SMTP.php`, and `Exception.php` are vendored here
(the full library also ships POP-before-SMTP, OAuth, and DKIM-signing
classes this project doesn't use) — pulled directly from the official
project's tagged release, not installed via Composer, to keep this a
plain-file, no-build-step deploy like the rest of the site:

https://github.com/PHPMailer/PHPMailer/tree/v7.1.1/src

Used by `api/submit-lead.php` to send the "new lead" notification email
over authenticated SMTP (real mailbox login) instead of PHP's raw `mail()`
— see the doc comment at the top of that file for why (mail() was
reporting success while never actually delivering).

Licensed LGPL-2.1 (see `LICENSE` in this folder) — unmodified from
upstream. To update: replace these same three files with a newer tagged
release's copies and bump the version in this note.
