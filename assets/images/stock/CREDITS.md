# Stock photo credits

All images in this folder are **temporary placeholders** standing in for real
AAEC portfolio/team photography, which hasn't been received yet (see
`CLAUDE.md` → "Known blockers"). Replace them as real photos come in — swap
the `image` path in `data/work.json` and delete the corresponding file here.

Every image is from **Wikimedia Commons**, chosen for a clear, verifiable
commercial-use license. Two of the eight licenses (CC BY, CC BY-SA) legally
require attribution — that's what this file is for. Keep it if the stock
images stay past this preview; if/when a photo here is replaced by real AAEC
work, delete its row.

All are shown on the site with a uniform grayscale + red-duotone CSS filter
(`.brand-photo` in `style.css`) so they read as one system rather than random
stock photography — see the note in `CLAUDE.md` → "Brand system" for how to
adjust or remove that treatment once real photography direction is decided.

| File | Title | Author | License |
|---|---|---|---|
| `chicago-skyline-night.jpg` | [Chicago Grant Park night pano](https://commons.wikimedia.org/wiki/File:Chicago_Grant_Park_night_pano.jpg) | Daniel Schwen | [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0) |
| `work-commercial-clapperboard.jpg` | [1992-04-24 Filmklappe Filmschindel Gedanken 3](https://commons.wikimedia.org/wiki/File:1992-04-24_Filmklappe_Filmschindel_Gedanken_3.jpg) | Bernd Schwabe in Hannover | [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0) |
| `work-music-culture-concert.jpg` | [Musician performs with red guitar during an outdoor concert at night under vibrant lights](https://commons.wikimedia.org/wiki/File:Musician_performs_with_red_guitar_during_an_outdoor_concert_at_night_under_vibrant_lights.jpg) | Shixart1985 | [CC BY 2.0](https://creativecommons.org/licenses/by/2.0) |
| `work-sports-basketball.jpg` | [Dunk by Hapoel Jerusalem, GPO](https://commons.wikimedia.org/wiki/File:Flickr_-_Government_Press_Office_(GPO)_-_Dunk_by_Hapoel_Jerusalem.jpg) | Avi Ohayon / Israel GPO | [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0) |
| `work-documentary-bw.jpg` | [Arba'een In Mehran City 2016 - Iran (Black And White Photography)](https://commons.wikimedia.org/wiki/File:Arba%27een_In_Mehran_City_2016_-_Iran_(Black_And_White_Photography-Mostafa_Meraji)_...jpg) | Mostafa Meraji | [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0) |
| `work-community-mural-chicago.jpg` | [Artist Ron Blackburn painting an outdoor wall mural, Chicago (NARA)](https://commons.wikimedia.org/wiki/File:ARTIST_RON_BLACKBURN_PAINTING_AN_OUTDOOR_WALL_MURAL_AT_THE_CORNER_OF_33RD_AND_GILES_STREETS_IN_CHICAGO._HE_IS_ONE_OF..._-_NARA_-_556229.jpg) | John H. White / US National Archives (NARA) | Public domain — no attribution legally required |
| `work-events-theater.jpg` | [Theater Show](https://commons.wikimedia.org/wiki/File:Theater_Show.jpg) | Kurt Kaiser | [CC0](http://creativecommons.org/publicdomain/zero/1.0/deed.en) — no attribution legally required |
| `lftlr-vinyl-turntable.jpg` | [Groove and needle in close embrace from beginning to end](https://commons.wikimedia.org/wiki/File:Groove_and_needle_in_close_embrace_from_beginning_to_end.jpg) | Franz van Duns | [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0) |

## Not used (flagged during sourcing, worth knowing about)

- A Wikimedia Commons full-text search for "basketball action black and white
  game" initially returned a photo that turned out to be an athlete's ankle
  being taped mid-crowd, not action photography — swapped for the dunk photo
  above. Always visually check a stock photo before using it; the title/
  category metadata alone isn't reliable.
- Deliberately did **not** put a stock photo in Airik Crawford's team
  portrait slot (`data/team.json`) or in the LFTLR "Featured Artists" cards —
  those slots represent specific real, named people, and a stranger's stock
  photo there would misrepresent who they are, not just decorate an empty
  space. Left as the existing "portrait/artist pending" placeholder instead.
- No hero **video** — none of Commons' free-licensed video clips fit the
  brand (mostly historical/archival `.ogv` footage with poor Safari support).
  Home hero uses the Chicago skyline image instead. A real cinematic hero
  video is still worth sourcing/shooting for the actual launch.
