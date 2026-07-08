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
without a build/deploy pipeline.

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
  new project / team member / testimonial."
- Each top-level page (`index.html`, `about.html`, `services.html`, `work.html`,
  `lftlr.html`, `careers.html`, `contact.html`) is a plain static HTML file with a
  `<header data-include-header>` and `<footer data-include-footer>` placeholder.

**Important:** because `main.js` uses `fetch()` for partials and JSON data, pages
must be served over http(s), not opened directly as a `file://` URL. Use a local
static server for preview (see README).

## Content model

- `/data/work.json` — array of portfolio items: `title`, `category` (must be one of
  Commercial, Sports, Community, Music & Culture, Photography, Documentary, Events —
  these match the filter buttons on `/work.html`), `image` (path or empty string for
  placeholder), `href` (link to the project's detail page).
- `/work/template.html` — the pattern for an individual project detail page. To add a
  real project: duplicate this file as `/work/<slug>.html`, fill in the real content,
  then add a matching entry to `/data/work.json` pointing `href` at the new file. Do
  not try to generate detail pages dynamically from JSON alone — they should be real,
  crawlable static HTML for SEO.
- `/data/team.json` — array of team members: `name`, `role`, `photo`, `bio`,
  `expertise` (array of strings). Rendered as clickable profile cards + modal on
  `/about.html`. Currently only Airik Crawford (founder) is populated; the rest of
  the team, if any, needs to come from the client.
- `/data/testimonials.json`, `/data/clients.json`, `/data/lftlr.json`,
  `/data/careers.json` — same pattern, currently placeholder/partial data. Anything
  marked `"PLACEHOLDER"` or with a `"note"` field is a known gap, not a mistake.

## Brand system (from AAEC Brand Guide v1.0)

**Colors** (brand guide marks these as unfinalized placeholders, confirm final hex
with Airik before print/high-stakes use, but fine to build with for now):
black `#000000`, red `#B0151B`, white `#FFFFFF`, silver `#A6A6A6`. Defined as CSS
variables in `style.css` (`--aaec-black`, `--aaec-red`, `--aaec-white`,
`--aaec-silver`). No bright/trendy colors outside this palette.

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

## Known blockers / open items (check if still true before assuming)

- **Portfolio media**: no real photos/videos yet. `/data/work.json` has 6 placeholder
  entries and `/work/template.html` is unfilled. Waiting on a WeTransfer zip from
  Airik. Once it arrives, replace placeholders with real projects and images.
- **Team**: only the founder is populated in `/data/team.json`. No other team member
  bios/photos received yet.
- **LFTLR**: `/data/lftlr.json` and `/lftlr.html` need the URL of the existing Live
  From The Living Room site, plus featured artist/episode content, none provided yet.
- **Careers form / Contact form**: both forms in `careers.html` and `contact.html`
  currently just show a "not yet live" status message (see `initContactForm()` in
  `main.js`). They are NOT wired to GoHighLevel yet.
- **Testimonials/client logos**: `/data/testimonials.json` is a placeholder. Client
  names on the Home page are text tags (from the brand guide's client list), not
  actual logo images — get logo permission/files before swapping to real logos.
- **Photography direction**: brand guide explicitly leaves color treatment (b&w vs.
  color), framing (tight portrait vs. wide/environmental), and lighting mood
  (moody/low-key vs. bright/hard-contrast) undefined. Don't invent a firm direction
  here without checking the client's reference portfolio
  (https://ericcrawford.myportfolio.com/) or asking.

## GHL (GoHighLevel) integration — read before touching forms

The plan is to connect `contact.html` and `careers.html` forms to a GHL sub-account
for lead capture, tracking, and automation. Two things to keep in mind:

1. GHL's client-side embeds (form/calendar widgets, tracking snippet) use public
   form/location IDs and are safe to paste into these HTML pages.
2. GHL **Private Integration Tokens** (strings starting with `pit-`) are API secrets
   for server-side/backend calls. They must never be pasted into any HTML/JS file in
   this repo, since anything in these files is publicly visible in page source once
   deployed. If a private integration token is ever needed for something (custom
   backend automation, Zapier step, etc.), it belongs in a server-side environment
   variable, not in this static site.

## Deployment target

Hostinger shared hosting, static files only, no Node/build step on the server. See
README.md for the exact upload steps and local preview instructions.
