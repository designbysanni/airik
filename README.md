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

No build step, no Node runtime needed on the server. Two options:

**Option A — File Manager (simplest)**
1. Zip the project folder contents (not the folder itself, the files at its root).
2. In hPanel, go to Files → File Manager → `public_html` for the domain.
3. Upload the zip and extract it into `public_html`.
4. Confirm `airikart.com` resolves to that `public_html` directory in hPanel →
   Domains.

**Option B — FTP/SFTP**
1. Get FTP credentials from hPanel → Files → FTP Accounts.
2. Use any FTP client (FileZilla, Cyberduck) to upload the project root's contents
   into `public_html`.

Either way, updates after the first deploy are just: make the change locally (or via
Claude Code), re-upload the changed files (or the whole folder), done. No build,
no deploy pipeline, no server restart.

## GoHighLevel (GHL)

Contact and Careers forms are placeholders right now (see `CLAUDE.md` → "GHL
integration"). When the GHL sub-account forms are ready, paste GHL's public
form/calendar embed snippet into `contact.html` / `careers.html` in place of the
current `<form data-inquiry-form>` block, and remove the corresponding placeholder
logic in `initContactForm()` in `assets/js/main.js` if GHL's own embed handles
submission. Never put a GHL Private Integration Token (`pit-...`) into any file in
this repo, see `CLAUDE.md` for why.
