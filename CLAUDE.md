# AAEC Website — Project Context for Claude Code

This file is auto-loaded by Claude Code in this repo. Read it before making changes.
It exists so that any future prompt like "add a new project to the Work page" or
"update the hero copy" can be handled correctly without re-explaining the brand
or the architecture every time.

## What this project is

Marketing/portfolio website for AIRIK ART & ENTERTAINMENT CO. (AAEC), a Chicago-based
creative agency (film, photography, cinematography, live events, brand campaigns) run
by Airik Crawford. Client of Sanni (the person operating this repo/Claude Code).
Domain: airikart.com. Business email: info@airikart.com.

## Stack — read this before assuming a framework is needed

Plain HTML/CSS/JS. No build step, no npm dependencies required to run or deploy.
This is intentional: the site deploys to Hostinger shared hosting by uploading the
files as-is, and content updates should be doable in a single Claude Code prompt
without a build/deploy pipeline. One deliberate exception: `/api/submit-lead.php`
— the Contact/Careers forms need *something* server-side to hold the GHL API
token safely, and PHP is the only server-side runtime Hostinger shared hosting
gives you without a build step, so that's what it is. See "GHL integration"
below before touching it.

- `/assets/css/style.css` — single stylesheet, all design tokens as CSS custom
  properties at the top (`:root`). Change brand colors/fonts/spacing there, not
  inline in pages.
- `/assets/js/main.js` — single script, vanilla JS, no dependencies. Handles: header
  scroll state, mobile nav toggle, scroll-reveal animations, the Work page filter
  grid (reads `/data/work.json`), the About page team grid + modal (reads
  `/data/team.json`), and shared header/footer injection (reads `/partials/*.html`).
- `/partials/header.html` and `/partials/footer.html` — nav and footer markup, shared
  across every page via a small fetch-based include in `main.js`. Edit these once
  instead of editing nav/footer in 8 different files.
- `/data/*.json` — structured content (work, team, testimonials, clients, lftlr,
  careers). Prefer editing these over hand-editing HTML when the change is "add a
  new project / team member / testimonial." `work.json` and `team.json` are fetched
  and rendered dynamically by `main.js` on every page that needs them (Home, Work,
  About). `clients.json` and `testimonials.json` are likewise fetched and rendered
  on Home. `lftlr.json` is NOT currently fetched — see its own entry below.
- `/sitemap.xml`, `/robots.txt` — basic SEO config (brief deliverable). Sitemap only
  lists the 7 marketing pages; add individual `/work/<slug>.html` project pages to
  it once real ones exist (don't list `/work/template.html`, it's a dev template).
- Each top-level page (`index.html`, `about.html`, `services.html`, `work.html`,
  `lftlr.html`, `careers.html`, `contact.html`) is a plain static HTML file with a
  `<header data-include-header>` and `<footer data-include-footer>` placeholder.

**Important:** because `main.js` uses `fetch()` for partials and JSON data, pages
must be served over http(s), not opened directly as a `file://` URL. Use a local
static server for preview (see README).

## Content model

- `/data/work.json` — array of portfolio items: `title`, `category` (must be one of
  Commercial, Sports, Community, Music & Culture, Photography, Documentary, Events —
  these match the filter buttons on `/work.html`), `image` (path to the project's
  hero/thumbnail image), `href` (link to the project's detail page), `featured`
  (boolean — Home's "Featured Work" grid only shows items with `featured: true`;
  `/work.html` always shows everything and ignores this flag). Currently 14 real
  projects built from Airik's PHOTOGRAPHY export — see
  `assets/images/work/CREDITS.md` for what's real vs. still a gap.
- `/work/template.html` — the pattern for an individual project detail page. To add a
  real project: duplicate this file as `/work/<slug>.html`, fill in the real content,
  then add a matching entry to `/data/work.json` pointing `href` at the new file. Do
  not try to generate detail pages dynamically from JSON alone — they should be real,
  crawlable static HTML for SEO. Video is NOT part of `work.json` — YouTube embeds
  are hand-authored directly into the relevant project page(s) using `.video-embed`
  (see `style.css`; a responsive 16:9 `<iframe>` wrapper). One video → single
  `.video-embed` in its own section; multiple videos on one project (see
  `/work/sports.html`) → a `.grid.grid-2` of `.video-embed` blocks with a caption
  under each. `src="https://www.youtube.com/embed/<id>?rel=0"`.
- `/data/team.json` — array of team members: `name`, `role`, `photo`, `bio`,
  `expertise` (array of strings), `relatedWork` (optional array of
  `{"title": ..., "href": ...}` pointing at `/work/<slug>.html` pages — the About
  page modal renders a "Related Work" list when this is non-empty, per the brief's
  requirement that team profiles show portrait, bio, role, expertise, AND related
  work). Currently only Airik Crawford (founder) is populated; the rest of the team,
  if any, needs to come from the client.
- `/data/testimonials.json`, `/data/clients.json`, `/data/careers.json` — same
  pattern, currently placeholder/partial data. Anything marked `"PLACEHOLDER"` or
  with a `"note"` field is a known gap, not a mistake.
- `/data/lftlr.json` — schema exists (`existingSiteUrl`, `featuredArtists`,
  `episodes`, `gallery`, `timeline`) but `/lftlr.html` does NOT fetch it yet; the
  page is hand-written static placeholder HTML since there's no real LFTLR content
  to render dynamically yet. Once real content lands, either wire `/lftlr.html` up
  to fetch/render this file (matching the `work.json`/`team.json` pattern) or just
  hand-edit the HTML if the content ends up small and mostly static.

## Brand system (from AAEC Brand Guide v1.0)

**Colors** (brand guide marks these as unfinalized placeholders, confirm final hex
with Airik before print/high-stakes use, but fine to build with for now):
black `#000000`, red `#B0151B`, white `#FFFFFF`, silver `#A6A6A6`. Defined as CSS
variables in `style.css` (`--aaec-black`, `--aaec-red`, `--aaec-white`,
`--aaec-silver`). No bright/trendy colors outside this palette.

Brand red on black background is only ~3:1 contrast, which fails WCAG AA for small
text (needs 4.5:1). `--aaec-red-text` (`#E63946`, ~5:1 on black) is a brightened
variant used anywhere red text sits directly on black (eyebrows, nav/footer link
hover, form focus border) — `--aaec-red` stays reserved for filled backgrounds
(buttons, active filter pills) where the text on top is white, which already clears
7:1. If Airik's final brand hex differs, keep this same pattern (a true-brand-red
fill color plus a brightened text-safe variant) rather than collapsing back to one
red used everywhere.

**Type:** brand guide specifies Bebas Neue (headlines), Futura (nav/subheads/labels),
Helvetica Neue (body). Futura and Helvetica Neue are commercial fonts without free
default web licensing. This build substitutes **Jost** for Futura's role and
**Inter** for Helvetica Neue's role (both free, geometric/humanist sans, loaded via
Google Fonts in each page's `<head>`). If Airik confirms a real font license
(Adobe Fonts, Linotype, etc.) for Futura/Helvetica Neue, swap the `<link>` tags and
the `--font-structural` / `--font-body` variables in `style.css`, that's the only
place font family names are set.

**Voice:** AAEC speaks as "we" (institutional voice), not "I", except in the Founder
section of the About page where "he/his" refers to Airik specifically. Tone:
professional without being stiff, creative without being chaotic, confident without
arrogance, elegant without feeling inaccessible. Avoid corporate filler words like
"synergy," "solutions," "leveraging."

**Logo usage:** don't stretch or rotate either mark. The Aquinas Symbol
(`/assets/images/brand/aquinas-symbol-*.png`) should be aligned to its visual
centerline when placed, not the file's bounding box edges (per brand guide production
note) — the current usage (footer, roughly centered) is a safe default, but be careful
if repositioning it.

## Photography: real vs. stock

Most of the site now runs on **real AAEC photography** from Airik's own
PHOTOGRAPHY export, not stock. `/assets/images/work/<slug>/0N.jpg` holds the
selected images per project (see `assets/images/work/CREDITS.md` for
sourcing/selection details and known gaps), and `/assets/images/work/lftlr/`
holds real Live From The Living Room photos used on `/lftlr.html`.

Real photography does **not** get the grayscale/red-duotone treatment —
`.brand-photo` is reserved for the one remaining stock image (see below).
Showing the client's actual work in its true color/quality is the point;
don't apply that filter to new real photos without a specific reason.

`/assets/images/team/airik-crawford.jpg` is Airik's real portrait (from
`PHOTOGRAPHY/airik.jpg`, added 2026-07-18), used in `data/team.json` and the
About page Founder section. This replaced an earlier no-identifiable-face
silhouette placeholder that stood in only because no real photo of him
existed yet — that reasoning no longer applies now that a real one does. If
the team grows, follow the same pattern: a real photo per person in
`assets/images/team/`, referenced from `data/team.json`. Don't reach for a
stock/silhouette placeholder for a *named* team member unless there's
genuinely no photo of them yet — see `assets/images/stock/CREDITS.md` for
why that distinction matters.

One stock image remains, deliberately, in `/assets/images/stock/` (see
`assets/images/stock/CREDITS.md`):
- `chicago-skyline-night.jpg` — Home/About/Contact hero backgrounds. Kept
  because it's meaningfully tied to AAEC's real Chicago origin story, not
  filler; swap for a real Chicago shot of Airik's own if he ever takes one,
  otherwise leave it.

If Airik sends more/better photos later (especially for `US CELLULAR`, which
only had one usable real image after filtering out screenshots — see
`assets/images/work/CREDITS.md`), swap files directly in
`assets/images/work/<slug>/` — the HTML/JSON reference positional filenames
(`01.jpg`, `02.jpg`, ...), not specific content, so dropping in a replacement
with the same filename just works.

## Known blockers / open items (check if still true before assuming)

- **Portfolio media**: RESOLVED as of the PHOTOGRAPHY export — `/data/work.json`
  has 14 real projects with real photos and, where applicable, real embedded
  YouTube video. Remaining gap: none of these projects have real client-approved
  "Brief"/"Approach" copy — the current text on each `/work/<slug>.html` is
  written by Claude based on the category/title, not provided by Airik. Confirm
  or replace with his actual language before treating it as final client-facing copy.
- **Team**: only the founder is populated in `/data/team.json`. No other team member
  bios/photos received yet.
- **LFTLR**: photos are real now (see above), but `/data/lftlr.json` and
  `/lftlr.html` still need the URL of the existing Live From The Living Room
  site, real names/roles for the people in the SZN 3 photos (captions
  currently say "Live From The Living Room, SZN 3" rather than guessing
  names), and Episodes/Timeline content — none of that was in the
  PHOTOGRAPHY export.
- **Careers form / Contact form**: both forms in `careers.html` and `contact.html`
  currently just show a "not yet live" status message (see `initInquiryForms()` in
  `main.js`, which handles every `[data-inquiry-form]` on the site). They are NOT
  wired to GoHighLevel yet.
- **Favicon**: currently the black Aquinas Symbol PNG (`aquinas-symbol-black.png`),
  which is only visible on light-colored browser chrome — it disappears against a
  dark browser theme/tab bar. A proper favicon (multi-size, with a background chip
  or an adaptive SVG) should be generated from a vector master once one exists;
  treat the current one as a functional placeholder, not the final asset.
- **Testimonials/client logos**: `/data/testimonials.json` is a placeholder. Client
  names on the Home page are text tags (from the brand guide's client list), not
  actual logo images — get logo permission/files before swapping to real logos.
- **Photography direction**: brand guide explicitly leaves color treatment (b&w vs.
  color), framing (tight portrait vs. wide/environmental), and lighting mood
  (moody/low-key vs. bright/hard-contrast) undefined. Don't invent a firm direction
  here without checking the client's reference portfolio
  (https://ericcrawford.myportfolio.com/) or asking.

## GHL (GoHighLevel) integration — read before touching forms

**As of 2026-07-18, both forms are live**, wired to GHL through a custom PHP
endpoint rather than an embedded GHL form widget — Airik wanted the forms to
stay fully on-brand rather than looking like a generic GHL widget.

### Architecture

`contact.html` and `careers.html` each have a real `<form data-inquiry-form>`
built with the site's own markup/CSS (not GHL's). `initInquiryForms()` in
`main.js` intercepts submit, does a `fetch()` POST (JSON) to
`/api/submit-lead.php`, and shows a themed success/"email us directly" error
message based on the response — no page reload, no GHL branding anywhere.

`api/submit-lead.php` (PHP — the one exception to "no server-side code" in
this otherwise fully static site) does the actual GHL work server-side:
1. Rejects anything that isn't POST, isn't valid JSON, or trips the
   honeypot (`website` field — see `.hp-field` in `style.css`, off-screen
   rather than `display:none` so basic bots filling every input can't tell
   it's a trap).
2. Validates name + email.
3. Calls GHL's v2 API three times (all confirmed working against the live
   account on 2026-07-18, then the test contact was deleted): `POST
   /contacts/upsert` (create/update the contact), `POST
   /contacts/{id}/tags` (apply `"Website Lead - Contact"` or `"Website Lead
   - Careers"` depending on which form it was — **not** passed via the
   upsert call's own `tags` field, since that field *overwrites* all
   existing tags rather than adding one), then `POST
   /contacts/{id}/notes` (the free-text fields — project type/budget/
   message, or area-of-interest/message — as one formatted note, since we
   don't have GHL custom-field IDs to target individual fields).
4. Auth: `Authorization: Bearer <token>` + `Version: 2021-07-28` headers.
   Full endpoint reference: https://marketplace.gohighlevel.com/docs/ghl/contacts/

### Why this needed a PHP file at all

GHL doesn't publish a documented, secret-free API for anonymous form
submissions — their real Contacts API requires the Private Integration
Token, which can **never** go in browser JS (or anywhere in this repo —
same rule as always: it's public the moment it's in a file that reaches the
browser or git history). A raw client-side `fetch()` straight to GHL was
not an option. `api/submit-lead.php` is a minimal server-side proxy: it's
the only thing that ever touches the token, and it never returns the token
or raw GHL responses to the browser.

This is why Hostinger needs to run PHP for the forms to work — true for
essentially every Hostinger shared hosting plan, but worth confirming. If
PHP genuinely isn't available, the forms degrade to showing the "email us
directly" fallback message rather than breaking (the `fetch()` catch
handler treats a non-JSON response — e.g. a 404 HTML page — the same as a
GHL failure).

### The two secrets/config files

- `api/config.example.php` — committed template, placeholder token.
- `api/config.php` — **gitignored**, holds the real
  `pit-...` token and the (non-secret) `ghl_location_id`
  (`qKXPbny1l22naqOolkOb`, from the GHL location URL). This file exists
  locally but is NOT in git and will NOT come along with a `git pull` on
  Hostinger — **it has to be uploaded there manually** (File Manager or
  SFTP), once, outside the git deploy flow. If Hostinger's git deploy ever
  does a clean wipe+re-clone instead of an in-place pull, this file would
  need re-uploading after every deploy — worth testing/confirming with
  Airik/Sanni once the first real deploy happens.

### GHL-side setup (workflows, not forms)

Since there's no GHL Form involved anymore, don't build GHL "Form
Submitted" workflow triggers — use **"Tag Added"** instead, filtered to
`Website Lead - Contact` / `Website Lead - Careers` respectively. Each
workflow should send an internal notification email to
airikcrawford@gmail.com with an urgent subject line (a sender can request
high-priority headers, but Gmail's own "Important" marker is algorithmic —
not something a sender can force outright, so don't oversell that part).
Exact prompts for GHL's AI workflow builder were provided to Sanni in chat
when this was built; regenerate similar ones if these need to be rebuilt.

## Deployment target

Hostinger shared hosting, static files + one PHP endpoint (`api/submit-lead.php`),
git-deployed. No Node/build step on the server. See
README.md for the exact upload steps and local preview instructions.
