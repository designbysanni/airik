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

## Videos

The 9 YouTube videos (all from the AIRIK ART & ENT CO channel, confirmed via
oEmbed) were placed on the project page matching their subject:

- Dodge Charger Commercial → `/work/cars.html`
- Kinki Behavior Goddess Commercial → `/work/kinki-behavior.html`
- KK Hat Commercial → `/work/kwame-kruma.html`
- Kohl's Jewelers Diamond Commercial → `/work/product.html`
- Documentary — Scene 7: Community → `/work/documentary.html`
- Owen Pappoe (player feature), Gatorade UA All America Game Recap, Fort
  Myers Holiday Basketball Tournament Recap, American Family Insurance 3PT
  Shootout & Slam Dunk Contest Promo → all four on `/work/sports.html`

## LFTLR

`assets/images/work/lftlr/` (6 photos from `PHOTOGRAPHY/LIVE FROM THE LIVING
ROOM/`) are real photos from the actual "SZN 3" set, used on `/lftlr.html`
for the hero background, Featured Artists cards, and Gallery. Names/roles of
the people pictured weren't provided — captions say "Live From The Living
Room, SZN 3" rather than guessing who's who. Add real names once Airik
confirms them.
