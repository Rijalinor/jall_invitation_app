# Verification

How to prove invitation work is done, and the commands and contract tests that prove it.
Run the focused check first, then the broader ones when the change warrants them.

## Commands

```text
composer test                 # clears config, runs the suite on in-memory SQLite
vendor/bin/pint --test        # code style
npm run build                 # production assets
php artisan test --filter=Name  # one test
```

- `composer test` uses the in-memory SQLite config from `phpunit.xml`; tests must never touch
  the development database. Run it **once, sequentially** — parallel runs corrupt
  `storage/framework/views` with access-denied rename errors.
- `public/build` is gitignored. After any CSS/JS edit, rebuild and re-upload. On cPanel that
  means both the path PHP reads (`~/PROJECT/public/build/manifest.json`) and the path the
  browser fetches (`~/public_html/build/assets/…`); updating only one leaves every invitation
  at 500 with *"Unable to locate file in Vite manifest"*.
- There is no npm test or lint script.

## Contract tests

Run these whenever you touch a template, the registry, the view model, or the catalogue:

| Test | Guards |
|---|---|
| `tests/Unit/TemplateClassContractTest.php` | Every structural class is styled in `theme.css`; the tint is the last background rule; notice colour outranks paragraph rules |
| `tests/Feature/InvitationArchitectureTest.php` | Registry resolution + traversal guard, colour presets led by the default, view-model contract, `show_rsvp_summary` on every template, closing families, block grouping, section order |
| `tests/Feature/TemplateMultiLineTest.php` | The `CONVERTED` allow-list for line breaks in textareas |
| `tests/Feature/PublicInvitationTest.php` | Published-only resolution, recipient sanitization, fallback |
| `tests/Feature/TemplateOptionTogglesTest.php`, `tests/Feature/RsvpSummaryTest.php` | Per-invitation option toggles and the RSVP recap |
| `tests/Feature/LocationCalendarTest.php` | Map, directions, and that no design offers add-to-calendar |
| `tests/Feature/InvitationFormLinkTest.php`, `tests/Feature/GuestManagementTest.php` | Customer form link, guest CRUD/import/sharing |
| `tests/Feature/LandingCatalogTest.php` | Catalogue: one sample rendered in every design with its own palette |
| `tests/Feature/InvitationPreviewTest.php`, `tests/Feature/InvitationPreviewReadinessTest.php` | Signed draft preview and publish-readiness rules |
| `tests/Feature/InvitationQueryBudgetTest.php`, `tests/Feature/TemplateRegistryMemoizationTest.php` | Query-count budgets and registry memoization |
| `tests/Feature/PruneOrphanUploadsTest.php` | Upload cleanup |

## Functional

- A public slug resolves only a published, non-expired invitation.
- Complete and partially filled invitations both render without error.
- Section visibility and ordering follow the saved configuration and the position field.
- Template switching changes presentation without damaging invitation data.
- One published sample represents the catalogue; every design renders it and shows that
  design's own primary palette. An explicit `?template=` override resets only colour
  settings, never content or wording, and only applies to a catalog demo.
- Guest links work with spaces, punctuation, and Unicode names; missing recipient data uses a
  neutral fallback.
- Countdown uses the intended timezone and event date. No design shows an add-to-calendar
  action (no Google Calendar or ICS link).
- Map preview shows the intended venue; directions open the correct destination; copy-address
  works and the full textual address remains when the map fails.
- RSVP validates attendance state and party limits; guestbook entries follow moderation and
  anti-spam rules; music requires interaction and has working play/pause.
- Admin preview matches the public page; copy-link and WhatsApp sharing preserve the
  personalized URL; contact and livestream actions use validated destinations.

## Template isolation and contract

- Template assets are namespaced/scoped and cannot leak into another template or the admin.
- No template queries business tables, accesses session/auth, or includes another template.
- No template hard-codes customer-specific content.
- Unknown template ids and view paths are rejected; the registry never turns request text into
  an include path.
- Settings are validated against the schema; colour presets are allow-listed hex led by the
  default; a setting is offered only when the selected template declares it.
- The common keys (`accent_color` with presets, `motion`, `cover_video_*`, `hide_timezone`,
  `merge_rsvp_guestbook`, `show_rsvp_summary`) exist in every template.
- A new template is registered everywhere (see the New Template Checklist in
  `references/platform-contract.md`): `vite.config.js` inputs and
  `TemplateMultiLineTest::CONVERTED`.
- The cover control opens the invitation without scripting (a real anchor), and content is
  hidden only under a class the script adds to the document root.
- Every structural class the entry view renders is styled by the template stylesheet; the
  tint rule is the last rule that paints the section.
- An existing published invitation still renders after a new template is added.

## Responsive and visual

- Inspect representative widths around 360, 390, 768, and 1280px; the phone layout is primary.
- Check long names, long addresses, missing images, and large text settings.
- Verify cover, navigation, forms, gallery, gift details, and floating controls.
- Confirm contrast, focus visibility, heading order, labels, and alt text.
- Check fixed elements respect safe-area insets and do not cover content.
- Shared forms span their column with visible field borders and focus states; a fixed bottom
  bar reserves padding for its height and steps aside while scrolling.
- Actually render the design at phone width and look at it; passing server-side tests alone is
  not evidence a design works.

## Motion

- The template states its motion concept in one sentence; every effect serves it.
- Exactly one signature moment exists, normally cover-to-content; sections do not share one
  identical fade-and-slide.
- A scroll-linked highlight exists — not entrance reveals alone — and responds to the scroll
  position (a parallax layer, a progress trail, a drawing rule, a turning backdrop).
- The scroll-linked highlight is layered **behind and in front of** the content — a far, slow
  layer and a near, faster, softly blurred one — so the depth reads as 3D.
- One decorative detail animates continuously on its own (for example a marching stitched
  border), and it stops under `motion: off` and `prefers-reduced-motion`.
- Ambient particles loop across the whole viewport (not only drift with `scrollY`), so they
  are still present in the last section as well as the first.
- The scroll-linked effect comes from one rAF-throttled, passive listener that writes CSS
  custom properties, and only `transform`/`opacity` animate.
- Under `motion: off` and `prefers-reduced-motion` the scroll-linked highlight and every
  decorative motion layer are removed, and the page still reads as finished.
- Timings come from tokens, with shorter exits than entrances; only `transform`/`opacity`
  animate.
- Content is hidden only under a class JavaScript added; with scripting blocked the whole
  invitation is visible and usable.
- The `motion` setting changes motion in kind; `off` removes all reveal/scroll animation.
- `prefers-reduced-motion` produces the no-motion experience regardless of the setting.
- No motion delays the first readable content or blocks navigation, forms, or audio.
- Scroll effects do not hijack or trap scrolling; the page scrolls normally.
- Audio starts only from a user gesture and never restarts on re-render.

## Security and privacy

- Escape guest names, messages, and other user-controlled content.
- Validate and authorize admin changes; preserve CSRF protection, rate limits, validation, and
  separate RSVP/guestbook error bags.
- Restrict upload type, size, storage path, and execution behaviour.
- Use opaque guest identifiers where privacy or tracking matters; do not expose the full guest
  list, private contacts, secrets, or predictable private records.
- Treat gift and financial details as protected editable data.
- Solutions must hold on plain shared hosting (Apache/LiteSpeed, no CDN/proxy in front).

## Performance and reliability

- Generate responsive image variants and lazy-load non-critical images.
- Avoid blocking the first render on galleries, maps, music, or animation libraries.
- Use only necessary font files and weights; confirm the production build succeeds.
- Provide fallbacks for failed fonts, audio, maps, and embeds.
- Confirm personalized cache keys cannot leak one guest's page to another.

## Verification evidence

- Run the focused test(s) for the change, then `composer test` and `vendor/bin/pint --test`,
  and `npm run build` after any asset edit.
- Render at least one complete fixture and one sparse fixture; render at least one template
  with JavaScript disabled.
- Record untested browsers, unavailable services, and assumptions in the handoff.
