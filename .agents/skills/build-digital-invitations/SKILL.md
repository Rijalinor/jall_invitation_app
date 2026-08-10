---
name: build-digital-invitations
description: Build, extend, review, or repair digital invitation websites and reusable invitation platforms. Use for wedding, engagement, birthday, aqiqah, graduation, or event invitation sites; guest-name links; configurable themes; multi-template systems; Laravel or other web implementations; invitation admin panels; RSVP, guestbook, gallery, maps, countdown, music, and digital-gift features. Use whenever the work must keep invitation content separate from visual design so many substantially different website designs can share the same application and data.
---

# Build Digital Invitations

Build invitation websites as a reusable product with independent data, features, and presentation layers. Preserve creative freedom: a new template may change composition, section order, navigation, illustration style, typography, transitions, and interaction patterns—not merely colors.

## Route the Work

1. Inspect the existing repository and its instructions before choosing tools or changing files.
2. Classify the request:
   - Build a one-off invitation only when explicitly requested.
   - Default to a reusable multi-invitation platform when the user mentions templates, customers, sales, an admin panel, or multiple designs.
   - Extend the current architecture rather than replacing it when working in an existing project.
3. Confirm or reasonably infer the event type, visual direction, required sections, stack, and delivery scope. Ask only when a missing choice materially changes the implementation.
4. For a new platform, default to Laravel, Blade, Tailwind CSS, Alpine.js, MySQL, and Filament when this fits the repository and the user has not chosen another stack.
5. Read the relevant references before implementation:
   - Read `references/complete-invitation-baseline.md` for every new public invitation or platform build.
   - Read `references/template-architecture.md` for any reusable platform, template, database, or rendering work.
   - Read `references/design-system.md` whenever creating or changing a visual design.
   - Read `references/quality-checklist.md` before declaring implementation complete.

## Protect the Core Architecture

Enforce these invariants:

- Store event and customer content independently from template code.
- Keep shared features such as guests, RSVP, guestbook, gifts, and analytics outside individual templates.
- Give every template its own manifest, preview image, asset namespace, entry view, and supported-section declaration.
- Render templates through a stable data contract or view model; never let a template query arbitrary application tables.
- Scope template CSS and JavaScript so one template cannot alter another or the admin panel.
- Allow a template to replace layouts and section implementations while retaining compatible feature contracts.
- Keep editable content in structured fields. Do not make administrators edit HTML, Blade, CSS, or source code for normal customer changes.
- Never hard-code a customer's names, dates, account numbers, guest list, or images into reusable template files.
- Provide graceful fallbacks for missing optional sections or media.

Follow the concrete contract, suggested schema, and folder boundaries in `references/template-architecture.md`.

## Establish the Creative Direction

Before designing, define a compact creative brief:

- event type and audience
- mood and cultural cues
- visual concept or metaphor
- palette and contrast
- heading and body typography
- image treatment
- layout rhythm and section order
- navigation model
- motion character and intensity

When the user supplies a reference, extract its principles instead of copying protected artwork or producing a near-duplicate. When no direction is supplied, propose a distinct direction appropriate to the event and continue with a documented assumption.

Avoid producing interchangeable templates. Each new template must differ from existing ones in at least three structural dimensions such as hero composition, navigation, section framing, reading flow, typography system, media treatment, or motion language. Use `references/design-system.md` for the complete design protocol.

For premium wedding directions similar to Golden Vow, apply the immersive section pattern in `references/design-system.md`: one full-screen section per scroll, dense responsive mobile composition, side-by-side couple portraits, editorial photo treatment, compact icon-only music controls, and optional cover video support.

## Build a Complete Invitation by Default

For a new invitation platform, implement the complete reusable feature baseline in `references/complete-invitation-baseline.md`. Treat the baseline as product capability, not as mandatory visible content: administrators may disable irrelevant sections per invitation, but the platform must not require code changes to enable them later.

At minimum, a complete wedding invitation must support:

- personalized opening cover and intentional music start
- couple or host profiles and family information
- one or more event schedules with timezone-aware date and time
- countdown and add-to-calendar action
- complete address, copy-address action, map view or preview, Google Maps directions, and a failure fallback
- story or timeline and responsive gallery
- RSVP with attendance state and party-size limits
- moderated wishes or guestbook
- digital gifts with copyable details and an optional delivery address
- contact person and optional livestream information
- share action, personalized guest links, and neutral recipient fallback
- configurable closing section

Do not substitute a static map image for working directions. Do not show empty cards for disabled or missing optional content. Apply equivalent complete sections for birthdays, aqiqah, graduations, and other event types while adapting labels and host fields.

## Implement in Vertical Slices

Build the smallest end-to-end path first:

1. Create or select an invitation.
2. Assign a template and editable theme settings.
3. Render a public invitation from stored data.
4. Personalize the recipient through a guest record or sanitized URL parameter.
5. Add required sections one at a time.
6. Add admin editing and preview for the same fields.
7. Complete the baseline modules and make optional presentation sections configurable.
8. Verify Maps directions, calendar links, sharing, RSVP, and guest personalization end to end.
9. Verify mobile behavior, security, accessibility, and performance.

Keep every slice usable. Do not build a large template marketplace, reseller system, billing engine, or WhatsApp automation unless requested.

## Personalize Guest Links Safely

- Prefer an opaque guest token when tracking, RSVP identity, or privacy matters.
- Permit a human-readable `to` query parameter for lightweight invitations.
- Escape output and sanitize display values; never render raw query text as HTML.
- Show a neutral fallback such as `Bapak/Ibu/Saudara/i` when no recipient exists.
- Keep the public invitation accessible when personalization fails unless the user explicitly requires private access.
- URL-encode generated share links and preserve Unicode names correctly.
- Do not expose private guest-list data through predictable numeric identifiers.

## Handle Media and Interaction

- Design mobile-first and verify common narrow screens before desktop polish.
- Start music only after an intentional user interaction; provide visible play and pause controls.
- Optimize uploads, generate responsive image sizes, prefer modern formats, and lazy-load off-screen media.
- Respect reduced-motion preferences and avoid animations that block reading or navigation.
- Provide fallbacks when maps, audio, fonts, or third-party embeds fail.
- Treat gift details and account numbers as sensitive editable content and reveal them intentionally.

## Finish with Evidence

Run the repository's relevant tests, formatter, build, and static checks. Exercise at least one invitation with complete data and one with optional fields missing. Test recipient links containing spaces and non-ASCII characters. Inspect representative mobile and desktop renders when visual tooling is available.

Use `references/quality-checklist.md`; report what was verified, what could not be verified, and any assumptions that remain.
