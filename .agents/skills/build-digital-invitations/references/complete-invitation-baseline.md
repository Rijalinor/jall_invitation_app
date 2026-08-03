# Complete Invitation Baseline

## Contents

1. Completion rule
2. Public invitation features
3. Location and directions
4. Guest and sharing workflow
5. Admin capabilities
6. Event-type adaptation

## 1. Completion Rule

Build these capabilities for every new reusable invitation platform unless the user explicitly requests a smaller prototype. Allow an administrator to enable, disable, reorder, and edit presentation sections per invitation. A disabled feature must fail closed and leave no empty heading, card, navigation item, or spacing gap.

Do not declare an invitation complete merely because every section is visible. Its buttons, links, forms, personalization, responsive media, and admin controls must work end to end.

## 2. Public Invitation Features

### Opening

- Personalized recipient name with a neutral fallback.
- Event title, hosts, primary date, and clear `Buka Undangan` action.
- Music begins only after intentional interaction.
- Cover works without audio and when media loading fails.

### Hosts and Families

- Couple or host names, photos, short profiles, and optional social links.
- Parent or family names where culturally appropriate.
- Configurable quotation, prayer, or introductory text; never invent religious text or personal information.

### Event Schedule

- Multiple sessions such as akad, reception, engagement, birthday session, or livestream.
- Localized date, start and end time, venue, full address, and timezone.
- Countdown targets the intended primary event and handles elapsed events gracefully.
- Add-to-calendar action with correct title, timezone, location, and start/end time.

### Story and Media

- Optional ordered story or timeline.
- Responsive photo gallery with meaningful captions or alternative text.
- Optional video or livestream link with a fallback when unavailable.
- Optimized images, lazy loading, and safe handling of missing media.

### Attendance and Interaction

- RSVP for attending, not attending, or tentative states as required.
- Party-size limits based on the guest invitation allowance.
- Submission confirmation and prevention of accidental duplicates.
- Moderated wishes or guestbook with spam controls.
- Optional attendance deadline displayed in the visitor's expected timezone.

### Gifts, Contacts, and Closing

- Optional bank, e-wallet, physical gift, or delivery-address methods.
- Copy buttons with clear success feedback.
- Contact-person actions using validated phone or messaging links.
- Closing message, host names, and optional footer attribution.

## 3. Location and Directions

Store structured location data for every physical event:

- venue name
- complete human-readable address
- latitude and longitude when available
- canonical map URL or provider place identifier
- optional parking, entrance, landmark, and accessibility notes

Provide all of these visitor actions:

1. View a map preview or embedded map when supported.
2. Open turn-by-turn directions in Google Maps using the event coordinates or a validated destination URL.
3. Copy the textual address.
4. Show the complete address and landmark instructions when the map provider fails.

Never expose private API keys in browser code. Prefer provider URLs that do not require a paid API for basic directions. Validate admin-entered URLs and do not render arbitrary unsafe schemes.

## 4. Guest and Sharing Workflow

- Import or create guests and generate a personalized link for each guest.
- Preserve spaces, honorifics, and Unicode characters in names.
- Prefer opaque guest tokens where RSVP identity or privacy matters.
- Provide a copy-link action and an optional WhatsApp share action.
- Keep the share message editable and URL-encode its content correctly.
- Prevent one guest from discovering another guest's data.
- Keep the invitation usable through a neutral public link when allowed by the invitation settings.

## 5. Admin Capabilities

Provide an administrator with:

- invitation create, edit, duplicate, preview, publish, unpublish, and archive workflows
- template selection without re-entering invitation content
- content editing for every supported section
- section enable/disable and ordering controls
- safe theme controls declared by the template manifest
- image, audio, and optional video management
- guest creation and CSV or spreadsheet import when bulk guests are in scope
- personalized-link generation and copy actions
- RSVP list, filtering, counts, and export
- guestbook moderation
- event location fields and a directions-link preview
- validation status showing which required content is incomplete before publication

Do not expose raw HTML, CSS, JavaScript, database fields, or template source as the normal editing interface.

## 6. Event-Type Adaptation

Adapt the shared capabilities instead of forcing wedding terminology:

| Event type | Host presentation | Typical schedule labels |
|---|---|---|
| Wedding | Couple and families | Akad, pemberkatan, reception |
| Engagement | Couple and families | Ceremony, celebration |
| Birthday | Celebrant and family | Party, dinner, activity session |
| Aqiqah | Child and parents | Prayer, meal, gathering |
| Graduation | Graduate and family | Ceremony, celebration |
| General event | Organizer or honoree | Opening, main session, closing |

Use event-type configuration for labels and visible fields. Keep the underlying schedule, guest, location, RSVP, media, contact, and sharing contracts reusable.
