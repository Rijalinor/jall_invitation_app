---
name: build-digital-invitations
description: Work inside the JALL invitation platform — a Laravel 12 + Filament 4, multi-customer, multi-template digital invitation product. Use to add, extend, repair, or refactor an invitation template; change a shared invitation section or feature (RSVP, guestbook, gifts, gallery, maps, countdown, sharing, guest links); touch the public catalogue, preview, or admin panel; or fix one visual defect across every template. Use for wedding, engagement, birthday, aqiqah, graduation, or general event invitations where customer content stays separate from visual design so many designs share one application and data set.
---

# Build Digital Invitations

JALL Invitation is a running product, not a blank slate: many templates already render
through one data contract. Extend the existing architecture; do not rebuild the layers or
introduce a parallel structure.

- Content lives in Eloquent models (`app/Models`, `database/`).
- Features live in controllers and services (`app/Http`, `app/Services`).
- Admin lives in Filament (`app/Providers/Filament`, `app/Filament`).
- A design lives in its own folder, `resources/invitation-templates/{id}/`.
- The public page renders through the template's entry view from `InvitationViewModel`.

Read the project's `AGENTS.md` first: it holds the stack, commands, and deployment rules.

## Route the Work

Classify the request, then follow the route. Each route lists the files that must move
together; the contract behind them is in `references/platform-contract.md`.

1. **New template.** A new design folder with manifest, entry view, CSS, JS, and preview.
   Every point in the *New Template Checklist* is mandatory — a template that is not
   registered where the platform keeps its allow-lists renders, previews, or tests wrongly.
2. **Repair or refactor one template.** Change a single design's markup, CSS, JS, or
   manifest. Keep it inside that folder unless the root cause is a shared contract.
3. **Shared section or feature.** RSVP, guestbook, gifts, gallery, countdown, map,
   sharing, guest links. Fix it once for every template; never fork the logic
   per design.
4. **Catalogue, preview, or admin.** The sample-in-every-design catalogue, the `?template=`
   render override, template colour swatches, the section editor, relation managers.
5. **Cross-template visual defect.** The same bug shows in more than one design. Fix the
   shared source and propagate it — the habit here is "benahi di semua template".

When a missing choice would change the implementation, ask; otherwise choose the sensible
option and state the assumption.

## Invariants — Do Not Break

- A template renders from `InvitationViewModel` only. No model queries, no `auth()` or
  `session()`, no including another template's views.
- Template CSS and JS stay scoped to the template root; they must not affect another
  template or the Filament admin panel.
- Customer-editable content lives in structured fields. Never hard-code names, dates,
  account numbers, guest lists, or image paths into a template.
- A disabled or empty optional section renders nothing: no heading, card, navigation item,
  or leftover gap.
- Theme values reach the page only through the manifest `settings_schema`; the admin shows
  a control only when the selected template declares it.
- Public invitations resolve published, non-expired records only. Recipient values are
  sanitized and escaped, with a neutral fallback when absent.

## Three Ideas That Shape Every Change

**Mobile-first is the deliverable, not a polish pass.** The guest opens the invitation on
a phone, one-handed. Compose at 360–390px first, judge it there, then widen; never design
desktop and shrink it. Full method in `references/design-playbook.md`.

**Spend the motion budget on one signature moment.** Motion is part of the brief, not a
finishing effect: name it in one sentence, give the template one memorable moment (usually
cover-to-content), keep the rest quiet. Content may only be hidden under a class the script
adds to the document root, so a blocked script never leaves a blank invitation.

**Give scrolling its own highlight.** Entrance reveals alone are not enough: the page must
also react to scroll *position* so scrolling feels alive. Every template carries one
scroll-linked effect — a drifting parallax layer, a progress trail, a rule that draws, a
slowly turning backdrop — on top of the one-time reveals. Layer it on **both sides of the
content**: a slow layer behind the sections and a faster, softly blurred layer in front, so
the depth reads as 3D. Also give the page one detail that keeps moving on its own — a
marching stitched border, drifting specks, a slow shimmer — so it feels alive even before the
guest scrolls. Feed it from a single rAF-throttled, passive scroll listener that
writes CSS custom properties; animate `transform`/`opacity` only; and remove it under
`motion: off` and `prefers-reduced-motion`. A long invitation that only fades sections in
still scrolls like a document, and that is a defect. Method and examples in
`references/design-playbook.md`.

## Before Implementation

Read the reference that matches the route:

- `references/platform-contract.md` — the real code contract: manifest schema, registry,
  the `InvitationViewModel` data keys, renderer, `blocks`, and the **New Template
  Checklist**. Read for any template, contract, or rendering work.
- `references/feature-baseline.md` — what a complete invitation contains, reconciled with
  the sections the platform actually ships.
- `references/design-playbook.md` — creative brief, differentiation, tokens, mobile
  composition, immersive sections, motion, media, and the inventory of the seven existing
  templates. Before creating or changing a visual design.
- `references/verification.md` — the checks, the contract tests, and the project commands
  to run before calling the work done.

## Finish with Evidence

Run `composer test`, `vendor/bin/pint --test`, and `npm run build`, plus the contract tests
listed in `references/verification.md`. Render one complete invitation and one with
optional fields missing. Inspect the mobile render first. Report what you verified, what
you could not, and any assumption that remains.
