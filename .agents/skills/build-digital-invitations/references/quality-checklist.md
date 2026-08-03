# Invitation Quality Checklist

## Functional

- Public slug resolves only a published invitation.
- Complete and partially filled invitations both render without errors.
- Section visibility and ordering follow saved configuration.
- Template switching changes presentation without damaging invitation data.
- Guest links work with spaces, punctuation, and Unicode names.
- Missing recipient data uses a neutral fallback.
- Countdown uses the intended timezone and event date.
- Add-to-calendar uses the correct title, timezone, start/end time, and venue.
- Map preview shows the intended venue.
- Google Maps directions open the correct destination.
- Copy-address works and full textual directions remain available when the map fails.
- RSVP validates attendance state and party limits.
- Guestbook entries follow moderation and anti-spam rules.
- Music requires user interaction and has working play and pause controls.
- Admin preview accurately represents the public page.
- Copy-link and WhatsApp sharing preserve the personalized guest URL.
- Contact and optional livestream actions use validated destinations.

## Template Isolation

- Template assets are namespaced or scoped.
- No template queries arbitrary business tables.
- No template contains customer-specific hard-coded content.
- Unknown template IDs and view paths are rejected.
- Template settings are validated against a schema.
- An existing published invitation still renders after a new template is added.

## Responsive and Visual

- Inspect representative widths around 360, 390, 768, and 1280 px.
- Check long names, long addresses, missing images, and large text settings.
- Verify cover, navigation, forms, gallery, gift details, and floating controls.
- Confirm contrast, focus visibility, heading order, labels, and alternative text.
- Confirm reduced-motion behavior.
- Check that fixed elements respect safe-area insets and do not cover content.

## Security and Privacy

- Escape guest names, messages, and other user-controlled content.
- Validate and authorize admin changes.
- Restrict upload type, size, storage path, and execution behavior.
- Protect forms with CSRF controls where applicable.
- Rate-limit public submissions and prevent duplicate spam.
- Use opaque guest identifiers when privacy or tracking matters.
- Do not expose the full guest list, private contacts, secrets, or predictable private records.
- Treat gift and financial information as protected editable data.

## Performance and Reliability

- Generate responsive image variants and lazy-load non-critical images.
- Avoid blocking the first render on galleries, maps, music, or animation libraries.
- Use only necessary font files and weights.
- Confirm the production asset build succeeds.
- Provide fallbacks for failed fonts, audio, maps, and embeds.
- Check personalized cache keys cannot leak one guest's page to another.

## Verification Evidence

- Run targeted automated tests for rendering, publishing rules, guest resolution, RSVP, and validation.
- Run the project's formatter, build, and static analysis when available.
- Render at least one complete fixture and one sparse fixture.
- Record untested browsers, unavailable services, and assumptions in the handoff.
