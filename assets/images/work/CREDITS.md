# Work photo sourcing

Every image in this folder is **real AAEC photography**, pulled from the
PHOTOGRAPHY export Airik provided (`PHOTOGRAPHY/<CATEGORY>/...`), not stock.
This replaced the placeholder "Sample Project" stock photos entirely.

## How images were selected

For each of the 14 top-level PHOTOGRAPHY folders, up to 6 images were
selected automatically:

1. Excluded macOS resource-fork junk (`._*` files), Shutterstock-licensed
   stock (`shutterstock_*.jpg` in `US CELLULAR/`, licensing status unknown —
   don't reuse it), and screenshots (lower quality, often work-in-progress
   editing frames rather than finished photography).
2. Preferred actual photographs over extracted video-still frame grabs
   (filenames containing "Still") when both existed in a folder.
3. Within each group, sorted by file size descending (a rough proxy for
   "full-resolution finished photo" vs. a smaller export) and took the top 6.
4. Resized to a 1600px long edge, re-encoded as JPEG (quality 82) for web
   performance — originals were often 10–30MB camera files.

This is an **automated heuristic, not a curated edit** — nobody reviewed all
~150 source photos one by one to pick the objectively best 6 per category.
If a category's gallery doesn't feel like the best representation of that
work, swap individual files in `assets/images/work/<slug>/0N.jpg` for better
picks from the original `PHOTOGRAPHY/<CATEGORY>/` folder — no code changes
needed, the filenames (`01.jpg`–`06.jpg`) are just positional.

## Category → project mapping

Each top-level PHOTOGRAPHY folder became one `/work/<slug>.html` project
(see `data/work.json`). A few folders have rich sub-brand content (Sports has
Nike/Adidas/Puma/DJ Moore Football Camp/College Slam & 3PT Shootout/Chris
Mendoza Training as subfolders; Life Style has Models/Clothing Brands/Party
Event/Success Junkie) that got folded into one project page rather than
split into their own — if any of those deserve a dedicated project page of
their own later, that's a reasonable follow-up, not something this pass did.

`US CELLULAR` only had one usable image after filtering (the rest were
screenshots of what looked like in-progress video edits, not finished
photography) — that's an honest gap, not a bug, until better source photos
turn up.

## Videos (superseded — see below)

The note below is from the original 2026-07-18 pass and is now stale (pages
have since been renamed/merged/split). Kept for history; don't trust the
specific filenames. Current video placement per page is hand-authored
directly in each `/work/<slug>.html` — check the page itself, not this file.

## Round 2 (2026-07): real portfolio sourcing + 4 stock photos

Most new/rebuilt project pages added in Revision Round 2 use real images,
copy, and video sourced directly from Airik's own portfolio site
(ericcrawford.myportfolio.com) — downloaded via its CDN, resized the same
way as the PHOTOGRAPHY export (1600px long edge, JPEG q82). These live in
folders named after the project (`under-armour/`, `nike-women/`,
`devine-feminine/`, `sankofa/`, `1865-fest/`, `born-a-legend/`,
`rooftop-brunch/`, `galleria/`, `trace/`, `career-day/`, `concept-shoots/`,
`pf-*.jpg` files inside existing folders like `cars/`, `community/`,
`kwame-kruma/`, `product/`, `lifestyle/`, `t-mobile-streetz/`, `lftlr/`).

Four projects had **no usable image** anywhere in the portfolio export
(confirmed by checking the matching portfolio page directly) — these use a
single real, commercially-licensed (CC BY 2.0) Flickr stock photo each,
thematically matched to the project subject, instead of a fabricated AAEC
production photo or a visible "image pending" placeholder:

| Project | File | Title | Author | License |
|---|---|---|---|---|
| Kohl's Jewelers Diamond Commercial | `kohls-jewelers/01.jpg` | [Diamond ring](https://www.flickr.com/photos/85473033@N00/263628038) | AMagill | [CC BY 2.0](https://creativecommons.org/licenses/by/2.0/) |
| Music Video Archive | `music-video-archive/01.jpg` | [Vocal Microphone](https://www.flickr.com/photos/40959269@N05/3772473532) | SimonDeanMedia | [CC BY 2.0](https://creativecommons.org/licenses/by/2.0/) |
| The Guest House Chicago: It's A Marathon | `guest-house/02.jpg` | [Finish: Athlone Flatline Half Marathon 2014](https://www.flickr.com/photos/25874444@N00/15038969318) | Peter Mooney | [CC BY 2.0](https://creativecommons.org/licenses/by/2.0/) |
| A Greater Good Foundation: Let's Grow Fest | `lets-grow-fest/01.jpg` | [Wayne C Henderson Music Festival and Guitar Competition 2014](https://www.flickr.com/photos/37922399@N05/14489096085) | vastateparksstaff | [CC BY 2.0](https://creativecommons.org/licenses/by/2.0/) |

If Airik has real photos for any of these four, swap the file in directly
(same filename) — this stock fallback stops being needed the moment real
media exists.

## LFTLR

`assets/images/work/lftlr/` (6 photos from `PHOTOGRAPHY/LIVE FROM THE LIVING
ROOM/`) are real photos from the actual "SZN 3" set, used on `/lftlr.html`
for the hero background, Featured Artists cards, and Gallery. Names/roles of
the people pictured weren't provided — captions say "Live From The Living
Room, SZN 3" rather than guessing who's who. Add real names once Airik
confirms them.
