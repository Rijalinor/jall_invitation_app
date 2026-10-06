---
name: build-digital-invitations
description: Build, extend, review, or repair digital invitation websites and reusable invitation platforms. Use for wedding, engagement, birthday, aqiqah, graduation, or event invitation sites; guest-name links; configurable themes; multi-template systems; Laravel or other web implementations; invitation admin panels; RSVP, guestbook, gallery, maps, countdown, music, and digital-gift features. Use whenever the work must keep invitation content separate from visual design so many substantially different website designs can share the same application and data.
---

# Build Digital Invitations

Build invitation websites as a reusable product with independent data, features, and presentation layers. Preserve creative freedom: a new template may change composition, section order, navigation, illustration style, typography, transitions, and interaction patterns—not merely colors.

Design **mobile-first**: the guest almost always opens the invitation on a phone, held one-handed, so the phone layout is the primary deliverable and desktop is the enhancement. Compose the narrow layout first and derive wider layouts from it — never design desktop and shrink it down.

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
- motion concept in one sentence, its signature moment, and its intensity

When the user supplies a reference, extract its principles instead of copying protected artwork or producing a near-duplicate. When no direction is supplied, propose a distinct direction appropriate to the event and continue with a documented assumption.

Avoid producing interchangeable templates. Each new template must differ from existing ones in at least three structural dimensions such as hero composition, navigation, section framing, reading flow, typography system, media treatment, or motion language. Use `references/design-system.md` for the complete design protocol.

### Primary Reference Benchmark: Elegant Rose

Use **Elegant Rose** (`resources/invitation-templates/elegant-rose`) as the primary design and architectural reference benchmark for all template creation, extension, and repair tasks:
- **Design Quality & Spacing**: Reference `elegant-rose` for spacious card layouts, balanced mobile padding, non-cramped grid compositions, and responsive typography hierarchy.
- **Section Standards**: Follow the section structure, numbered event cards, structured action buttons, floating pill navigation (`.er-nav`), audio toggle (`.er-music`), and card-based host profiles from `elegant-rose`.
- **Template Refactoring**: When fixing or improving existing templates (such as `elegant-rose`, `midnight-ledger`, or `fun-storybook`), measure visual quality and mobile usability against `elegant-rose`.

Use **Coastal Vow** (`resources/invitation-templates/coastal-vow`) as the reference for a light, editorial, seaside direction: a horizon hero, a bottom-docked navigation that steps aside while reading, a horizontal gallery rail, and a single "tide rises" motion moment. It is the model for a warm, airy palette built from CSS layers rather than a dark cinematic treatment.

For premium wedding directions similar to Golden Vow or Elegant Rose, apply the immersive section pattern in `references/design-system.md`: full-screen sections that read on a normal, smooth scroll, dense responsive mobile composition, side-by-side or cleanly stacked couple portraits, editorial photo treatment, compact icon-only music controls, and optional cover video support.

## Design Mobile-First

The phone is the canvas; desktop is the enhancement. Design the narrow layout first, judge it there, and only then widen it — a desktop-only view is not evidence that a template works.

- Design at 360–390px first, then enhance at ~430px, tablet, and desktop. Check the shortest common phone height as well as the tallest; re-check after every change.
- Treat vertical space as scarce. Compose each section to read in about one thumb-scroll, and never leave a large blank area merely to satisfy a full-height section.
- Keep couples side by side on phones and keep names and headings on whole words — no one-word-per-line stacks and no narrow columns that squeeze important text, addresses, or venues.
- Make every action a comfortable touch target (about 44px), reachable one-handed and clear of browser chrome and safe-area insets. Let a fixed bottom bar reserve its own space and step aside while the guest scrolls so it never covers the actions it floats above.
- Prefer full-width stacked blocks and one readable column. Move secondary detail into cards, accordions, or dialogs instead of shrinking type.
- Reflow, do not crop: choose aspect ratios and focal points that survive a phone portrait crop, and verify long Indonesian names, honorifics, and addresses at the narrowest width.
- Use the template's own breakpoint consistently (for example `48rem`) and verify both sides of it; capture 393px and a desktop width when visual tooling is available.

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
- Show a neutral fallback such as `Bapak/Ibu/Saudara/i` when no recipient exists, or leave the name field empty so the guest types their own. Never prefill a form with a placeholder the guest has to delete first, and only greet by name when there is a real name.
- Keep the public invitation accessible when personalization fails unless the user explicitly requires private access.
- URL-encode generated share links and preserve Unicode names correctly.
- Do not expose private guest-list data through predictable numeric identifiers.

## Handle Media and Interaction

- Design mobile-first (see **Design Mobile-First**) and verify common narrow screens before desktop polish.
- Start music only after an intentional user interaction; provide visible play and pause controls.
- Optimize uploads, generate responsive image sizes, prefer modern formats, and lazy-load off-screen media.
- Treat motion as a designed layer with a named concept and one signature moment, never a decorative finish. Follow section 7 of `references/design-system.md`.
- Never let motion hide content: an element may only be hidden under a class that JavaScript added to the document root, so a slow or blocked script cannot leave the guest with a blank invitation.
- Respect `prefers-reduced-motion` and always keep a genuine no-motion path.
- Provide fallbacks when maps, audio, fonts, or third-party embeds fail.
- Treat gift details and account numbers as sensitive editable content and reveal them intentionally.
- A shared form centred with `margin: auto` inside a `display: grid` section collapses to its widest label. Give every form a real width (for example `width: min(100%, 40rem)`) and give its fields a visible border and focus ring.
- A fixed bottom bar can cover the actions of a full-height section. Reserve bottom padding equal to its height plus offset, and let it step aside while the guest scrolls forward.

## Ship a Reusable Catalogue and Theme Presets

When the product has a public catalogue of designs:

- Represent the whole catalogue with one published sample invitation. Render that same content through every template so an operator maintains one example, not one per design.
- When a sample is shown through a template other than its own, reset that template's declared colour settings to the template defaults so each card shows its own primary palette. Keep wording, media, and motion as saved; never let one design's stored colour bleed into another design's preview.
- Let every template declare recommended colour presets next to its colour setting. Validate them as allow-listed values, lead the list with the template default, and offer them in the admin as swatches beside the free colour picker.
- Expose a setting in the admin only when the selected template declares it, so an operator never fills a field the design ignores.

## Finish with Evidence

Run the repository's relevant tests, formatter, build, and static checks. Exercise at least one invitation with complete data and one with optional fields missing. Test recipient links containing spaces and non-ASCII characters. Render at least one template with JavaScript disabled to confirm the invitation is still fully visible and usable. Inspect the **mobile** render first (360–390px) and only then a desktop width when visual tooling is available; the phone layout is the one most guests will actually see.

Use `references/quality-checklist.md`; report what was verified, what could not be verified, and any assumptions that remain.
