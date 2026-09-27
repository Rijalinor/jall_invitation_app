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
- Check that fixed elements respect safe-area insets and do not cover content.

## Motion

- The template states its motion concept in one sentence, and every effect serves it.
- Exactly one signature moment exists, normally the cover-to-content transition.
- Sections do not share one identical fade-and-slide treatment.
- Timings come from template tokens, with shorter exits than entrances.
- Only `transform` and `opacity` animate; nothing shifts layout while scrolling.
- Content is only hidden under a class that JavaScript added to the document root.
- With JavaScript disabled, blocked, or failed, the whole invitation is visible and usable.
- The `motion` manifest setting changes the motion in kind; `off` removes all reveal and scroll animation.
- `prefers-reduced-motion` produces the no-motion experience regardless of the setting.
- No motion delays the first readable content or blocks navigation, forms, or audio controls.
- Scroll effects do not hijack, trap, or smooth-scroll against the guest's intent.
- Motion is not driven by a per-frame scroll handler that reads layout.
- A motion library, when used, is loaded per template and does not block the first render.
- Audio starts only from a user gesture and never restarts on re-render.

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
