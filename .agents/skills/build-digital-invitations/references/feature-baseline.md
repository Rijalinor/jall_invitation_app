# Feature Baseline

What a complete invitation contains on this platform. Treat it as product capability, not
mandatory visible content: an operator may disable an irrelevant section per invitation,
but the platform must not need a code change to enable it later.

## Contents

1. Completion rule
2. Public sections
3. Location and directions
4. Guest and sharing workflow
5. Admin capabilities
6. Event-type adaptation

## 1. Completion rule

An invitation is complete when its buttons, links, forms, personalization, responsive
media, and admin controls work end to end — not merely when every section is visible.

- Sections are enabled, disabled, and ordered per invitation. A disabled or empty optional
  section renders **nothing**: no heading, card, navigation item, or leftover gap.
- Shared features (RSVP, guestbook, gifts, gallery, maps, countdown, guest links) live once
  in the platform and every template consumes them. Never re-implement a feature per design.
- Operator-built free content uses **blocks**, grouped by the section that holds them, so an
  operator can add extra sections and place each on its own. A block with nothing to show is
  dropped before it reaches the template.

The sections a template can declare: `opening`, `hosts`, `events`, `countdown`, `map`,
`story`, `gallery`, `blocks`, `rsvp`, `guestbook`, `gifts`, `contacts`, `livestream`,
`sharing`, `closing`.

## 2. Public sections

### Opening

- Personalized recipient name from a guest token or a sanitized `?to=` value; when none
  exists, hide the greeting or use a neutral fallback such as `Bapak/Ibu/Saudara/i` — never
  a placeholder the guest must delete.
- Event title, couple/host names, primary date, and a clear `Buka Undangan` action.
- Music starts only after an intentional interaction.
- The cover works without audio and when media fails.

### Hosts and families

- Names, photos, short profiles, roles, parents, and optional social links.
- Keep a card to the name, role, and one short line; move longer profile text behind a
  detail action. Collapse it only under a class the script adds, so it stays readable when
  scripting fails.
- Configurable quotation or introductory text through labels; never invent religious text
  or personal details.

### Events and countdown

- One or more sessions (akad, reception, engagement, birthday session, livestream).
- Localized date, start/end time, venue, full address, and timezone.
- Countdown targets the primary event and handles an elapsed event gracefully.
- Each event offers directions to the venue and a copy-address action. Add-to-calendar is
  deliberately not offered: the ICS endpoint still exists for the admin, but no design
  links to it, and no template may reintroduce a calendar button.

### Story and media

- Optional ordered story/timeline.
- Responsive gallery with captions/alt text and a lightbox that lets the guest move between
  photos (swipe on phone, drag/arrows on desktop).
- Optimized images, lazy loading, and safe handling of missing media.

### Attendance and interaction

- RSVP for attending, tentative, and not attending, with a party-size limit from the guest
  allowance.
- The name field starts empty on a general link; a personal link pre-fills it.
- Submissions happen in place with inline success/error feedback, separate error bags for
  RSVP and guestbook, and prevention of accidental duplicates.
- Moderated guestbook with spam controls (honeypot + rate limit); only approved entries
  show.
- Optional anonymous attendance recap: counts only (`Hadir 5, Ragu-ragu 1, Tidak 1`), never
  guest names. Off by default for a design that does not want it, toggled by
  `show_rsvp_summary`.

### Gifts, contacts, closing

- Optional bank, e-wallet, physical gift, or delivery-address methods, with copy actions and
  clear success feedback.
- Contact actions using validated WhatsApp/phone links.
- Optional livestream link with a fallback when unavailable.
- Closing message, family blocks, host names, and optional footer attribution.

## 3. Location and directions

Store structured location data for every physical event: venue, full address, latitude and
longitude, map URL, and optional parking/entrance/landmark notes. The view model builds:

- `map_embed_url` — a keyless embed preview.
- `directions_url` — Google Maps directions from coordinates, else from the address.

The view model still exposes `calendar_url` and `ics_url`, but no template renders them: the
add-to-calendar actions were removed from every design on purpose.

Provide copy-address and keep the full textual address and landmark notes visible when the
map fails. Never expose private API keys in browser code; validate admin-entered URLs and
reject unsafe schemes.

## 4. Guest and sharing workflow

- Create or CSV-import guests, each with an opaque token; generate a personal link.
- Preserve spaces, honorifics, and Unicode in names.
- Provide copy-link and WhatsApp share actions; keep the message editable and correctly
  URL-encoded.
- One guest must never reach another guest's data.
- Keep the neutral public link working when the invitation allows it.

## 5. Admin capabilities

Through Filament, an operator can:

- create, edit, duplicate, preview, publish, unpublish, and archive invitations
- select a template without re-entering content; switching templates preserves content
- edit every supported section, and enable/disable and order sections
- manage blocks and their alignment/layout
- set safe theme values declared by the manifest (colour, motion, cover video/focal,
  toggles), with per-template colour swatches
- manage images, audio, and video
- create guests, import CSV, and copy personal links
- view, filter, count, and export RSVPs
- moderate guestbook entries
- set event location fields and preview directions
- see incomplete required content before publish/preview

Never expose raw HTML, CSS, JavaScript, database fields, or template source as the normal
editing interface.

## 6. Event-type adaptation

The event types are `wedding`, `engagement`, `birthday`, `aqiqah`, `graduation`, `general`.
A template declares which it supports in `event_types`; the catalogue filters on it. Adapt
labels and host fields rather than forcing wedding vocabulary.

| Event type | Host presentation | Typical schedule labels |
|---|---|---|
| Wedding | Couple and families | Akad, pemberkatan, reception |
| Engagement | Couple and families | Ceremony, celebration |
| Birthday | Celebrant and family | Party, dinner, activity session |
| Aqiqah | Child and parents | Prayer, meal, gathering |
| Graduation | Graduate and family | Ceremony, celebration |
| General event | Organizer or honoree | Opening, main session, closing |

Keep the schedule, guest, location, RSVP, media, contact, and sharing contracts reusable
across every type.
