# AAEC Website (airikart.com)

Static, no-build-step HTML/CSS/JS site for AIRIK ART & ENTERTAINMENT CO.
See `CLAUDE.md` for full project/brand context, this file is just setup and deploy steps.

## Local preview

The site uses `fetch()` for shared header/footer and JSON content, so it must be
served over http, not opened as a `file://` path. From the project root:

```
python3 -m http.server 8080
```

or, if you have Node installed:

```
npx serve .
```

Then open `http://localhost:8080`.

## Project structure

```
index.html, about.html, services.html, work.html, lftlr.html, careers.html, contact.html
work/template.html        <- duplicate this to add a new portfolio project page
partials/header.html      <- shared nav, edit once
partials/footer.html      <- shared footer, edit once
assets/css/style.css      <- all design tokens (colors/fonts/spacing) + styles
assets/js/main.js         <- all site behavior (nav, filters, modal, reveal animations)
assets/images/brand/      <- logo files (AIRIK wordmark, Aquinas symbol, LFTLR marks)
assets/images/work/       <- portfolio photos/video stills go here once received
data/*.json               <- portfolio, team, testimonials, clients, lftlr, careers content
```

## Making updates with Claude Code

This repo is set up so most updates are a single prompt to Claude Code, for example:

- "Add a new project called X to the Work page in the Commercial category" →
  Claude Code duplicates `work/template.html`, fills it in, and adds an entry to
  `data/work.json`.
- "Update the hero headline on the homepage" → edit `index.html` directly.
- "Add a new team member" → add an entry to `data/team.json` with a photo in
  `assets/images/brand/` or a new `assets/images/team/` folder.
- "Change the accent color" → edit the `--aaec-red` variable (and friends) at the
  top of `assets/css/style.css`.

Claude Code will read `CLAUDE.md` automatically for brand voice, content model, and
known open items before making changes.

## Deploying to Hostinger

No build step. The site needs a plain static file host **plus PHP** for
`api/submit-lead.php` (the Contact/Careers forms) — Hostinger shared
hosting runs PHP by default, so this needs no extra setup, just note it if
ever moving off Hostinger. Two options:

**Option A — Git (what this repo is set up for)**
Hostinger's hPanel → Advanced → Git lets you connect this GitHub repo and
deploy on push. **One manual step the first time, and again if a deploy
ever wipes the directory instead of an in-place pull**: `api/config.php` is
gitignored (it holds the real GHL token) and will never come from git —
copy `api/config.example.php` to `api/config.php` on the server (File
Manager or SFTP) and fill in the real token. See `CLAUDE.md` → "GHL
integration" for the full picture.

**Option B — File Manager**
1. Zip the project folder contents (not the folder itself, the files at its root).
2. In hPanel, go to Files → File Manager → `public_html` for the domain.
3. Upload the zip and extract it into `public_html`.
4. Confirm `airikart.com` resolves to that `public_html` directory in hPanel →
   Domains.
5. Manually create `api/config.php` there too (same as Option A).

**Option C — FTP/SFTP**
1. Get FTP credentials from hPanel → Files → FTP Accounts.
2. Use any FTP client (FileZilla, Cyberduck) to upload the project root's contents
   into `public_html`, including a manually-created `api/config.php`.

Either way, updates after the first deploy are just: make the change locally (or via
Claude Code), re-upload the changed files (or push to git), done. No build,
no deploy pipeline, no server restart — `api/config.php` only needs touching again
if it gets wiped by a deploy.

## GoHighLevel (GHL)

Contact and Careers forms are live, wired to GHL through a custom PHP endpoint
(`api/submit-lead.php`) instead of an embedded GHL form widget, so they stay
fully on-brand. Full architecture, the two config files, and the GHL-side
workflow setup are documented in `CLAUDE.md` → "GHL integration" — read that
before touching any form-related code. Never put a GHL Private Integration
Token (`pit-...`) into any file that isn't `api/config.php` (which is
gitignored) — see `CLAUDE.md` for why.
