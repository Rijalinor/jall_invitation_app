# Platform Contract

The concrete contract this platform already implements. Read it before touching a template,
the registry, the view model, the renderer, or the catalogue. Paths are real; keep them.

## Contents

1. Where things live
2. Template folder
3. Manifest schema
4. Settings schema
5. Labels
6. Sections and blocks
7. Template registry
8. View model data contract
9. Renderer and the catalogue override
10. New Template Checklist
11. Contract tests

## 1. Where things live

| Concern | Location |
|---|---|
| Content models | `app/Models` |
| Public routes | `routes/web.php` |
| Public controller | `app/Http/Controllers/PublicInvitationController.php` |
| Interaction endpoints (RSVP, confirmation, guestbook) | `app/Http/Controllers/InvitationInteractionController.php` |
| Calendar / ICS | `app/Http/Controllers/InvitationCalendarController.php` |
| Customer self-service form + audience tools | `app/Http/Controllers/InvitationFormController.php` |
| Draft preview | `app/Http/Controllers/InvitationPreviewController.php` |
| Catalogue landing page | `app/Http/Controllers/LandingController.php` |
| Registry | `app/Services/TemplateRegistry.php` |
| Renderer | `app/Services/InvitationRenderer.php` |
| Catalogue sample resolver | `app/Services/CatalogDemos.php` |
| Presentation view model | `app/ViewModels/InvitationViewModel.php` |
| Shared invitation CSS | `resources/css/invitations.css` |
| Templates | `resources/invitation-templates/{id}/` |
| Admin | `app/Providers/Filament`, `app/Filament` |
| Asset entry points | `vite.config.js` |

`AppServiceProvider` binds `TemplateRegistry` as a singleton and adds
`resource_path('invitation-templates')` as a Blade view location — that is why entry views
are addressed as `{id}.views.index`.

## 2. Template folder

```text
resources/invitation-templates/{id}/
├── manifest.json
├── preview.svg            # or .jpg/.png/.webp — served at /template-previews/{id}
├── views/
│   └── index.blade.php    # the entry view: {id}.views.index
└── assets/
    ├── theme.css          # scoped to the template root class
    └── theme.js
```

The folder name must equal the manifest `id`. A template's assets are namespaced by a root
class (for example `.er-*`, `.ml-*`, `.fsb-*`) and never style another template or the
admin panel.

Every template ships **one** entry view with its sections written inline inside a single
`@foreach ($sections as $section)` / `@if … @elseif` chain — that is the platform
convention. A design may add `views/sections/*.blade.php` partials if it prefers, but none
do today. What every template shares is the partials under
`resources/views/invitations/shared/`, included with the dot-path `invitations.shared.*`:
`og`, `blocks`, `rsvp`, `confirmation`, `rsvp-summary`, `guestbook`, `gifts` (plus the gift
cards), `closing-families`, and `host-dialog`. The shared JavaScript
(`invitation-hosts.js`, `invitation-forms.js`, `invitation-lightbox.js`) is loaded through
`@vite` and must not be re-implemented per template.

## 3. Manifest schema

```json
{
  "id": "garden-letter",
  "name": "Garden Letter",
  "version": "1.0.0",
  "event_types": ["wedding", "engagement"],
  "entry_view": "garden-letter.views.index",
  "preview": "preview.svg",
  "sections": ["opening", "hosts", "events", "countdown", "map", "story", "gallery", "blocks", "rsvp", "guestbook", "gifts", "contacts", "livestream", "sharing", "closing"],
  "section_height": true,
  "labels": { "hosts_title": "Mempelai", "events_title": "Rangkaian Acara" },
  "settings_schema": {
    "accent_color": {
      "type": "color",
      "default": "#765448",
      "presets": [{ "label": "Rose Wine", "value": "#7b2639" }]
    },
    "motion": { "type": "select", "options": ["calm", "expressive", "off"], "default": "calm" }
  }
}
```

Validation the registry enforces (`isValid`): `id` equals the folder name; `name`,
`version`, `entry_view`, and `sections` are present; `sections` is an array; and the
`entry_view` view exists. A manifest that fails any of these is simply not registered.

## 4. Settings schema

Supported `type` values: `color`, `select`, `boolean`, `url`, `number` (with `min`/`max`).

Every template must declare these keys, because the platform relies on them across designs:

- `accent_color` — `color`, with `presets`. The **first preset must equal `default`**, and
  every preset value must be a six digit hex. `TemplateRegistry::colorPresets()` drops
  anything else, so a manifest cannot inject a bad value.
- `motion` — `select` with options `["calm", "expressive", "off"]`.
- `cover_video_enabled`, `cover_video_desktop`, `cover_video_mobile`, `cover_poster_image`.
- `hide_timezone`, `merge_rsvp_guestbook`, `show_rsvp_summary` — booleans.

`show_rsvp_summary` is asserted for **every** template, so an operator can switch the RSVP
recap for any design. Optional keys a design may add: `bg_color`, `font_pairing`,
`cover_focal_x`, `cover_focal_y`, `cover_overlay_opacity`, `cover_text_position`,
`ornament_style`, `music_enabled`, `auto_scroll`.

Only values that survive validation reach the page: hex colours are regex-checked, selects
must be a declared option, numbers are clamped to `min`/`max`, URLs must be `http`/`https`.
The view model emits a key into `theme` **only when the manifest declares it** (plus
`accent_color` and `motion`, always). The admin offers a control only for declared keys, so
a design never receives a setting it ignores.

## 5. Labels

`labels` is a flat `string → string` map of section wording. A template reads a label with
a fallback, e.g. `$labels['hosts_title'] ?? 'Mempelai'`. One invitation may override any
key through `settings_json['labels']`; a blank or unknown override is ignored, so clearing
the field falls back to the manifest. Keep the keys a view reads aligned with the keys the
manifest declares — including any key read by a shared partial.

## 6. Sections and blocks

Base section keys the platform understands:

`opening`, `hosts`, `events`, `countdown`, `map`, `story`, `gallery`, `blocks`, `rsvp`,
`guestbook`, `gifts`, `contacts`, `livestream`, `sharing`, `closing`.

Countdown is its own section. Add-to-calendar actions were removed from every design on
purpose — the ICS endpoint remains for the admin, but no template renders a calendar link.

Each invitation stores enabled sections with a position. The view model returns:

- `sections` — enabled keys in the operator's order, intersected with the manifest's
  supported keys. When an invitation has none configured, the manifest `sections` order is
  used.
- `section_heights` — `{key: "auto"|"half"|"tall"}` for templates that declare
  `section_height: true`; ignore it otherwise.
- `blocks` — operator-built free blocks (text, note, quote, image, section), flattened.
- `block_sections` — the same blocks grouped by the section that holds them, so an
  operator can build several extra sections and place each separately. Custom section keys
  are `blocks` or `blocks:{name}`.

Blocks with no renderable content are dropped before they reach the template, so a view can
render what it is given without guarding every field.

## 7. Template registry

`TemplateRegistry` (a per-request singleton) exposes:

- `all()` — registered manifests keyed by id. Cached across requests on the `file` store
  under a directory fingerprint (folder names + manifest mtimes), so it invalidates the
  moment a manifest is added, removed, or edited. Tests read from disk so temporary
  templates never leak between tests.
- `find(string $id): ?array`
- `previewPath(string $id): ?string` — realpath-guarded; rejects anything outside the
  templates directory, so a traversal id resolves to `null`.
- `labels(string $id)` / `labelsByTemplate()`
- `colorPresets(string $id, string $key)`
- `supportsSectionHeight(string $id)`

Never turn request text directly into an include path; resolve through `find()`.

## 8. View model data contract

`InvitationViewModel::from($invitation, $recipient, $manifest, $guest = null,
$useTemplateDefaults = false)->data` is the only data a template sees. Keys:

| Key | Meaning |
|---|---|
| `title`, `couple_title` | Invitation title; couple names with the title as fallback |
| `share_image` | og:image: chosen photo, else cover poster, gallery, host |
| `recipient`, `guest_token`, `invitation_limit` | Sanitized recipient and guest context |
| `rsvp_url`, `confirmation_url`, `guestbook_url` | Interaction endpoints |
| `share_url`, `whatsapp_url` | Sharing (guest link carries the token) |
| `opening_text`, `closing_message`, `closing_footer`, `closing_families` | Wording; empty families dropped |
| `music_url`, `livestream_url`, `livestream_label` | Media |
| `sections`, `section_heights`, `blocks`, `block_sections` | Structure (see section 6) |
| `hosts`, `events`, `primary_event` | Profiles and schedule, pre-formatted, with `directions_url` and `map_embed_url` (`calendar_url`/`ics_url` still exist but no template renders them) |
| `stories`, `gallery`, `gifts`, `contacts` | Content lists |
| `wishes`, `guestbook_count`, `rsvp_summary` | Guestbook and anonymous attendance tally |
| `labels`, `theme` | Resolved wording and validated theme values |

Templates must not query models, call `auth()`/`session()`, or `@include` another template.

## 9. Renderer and the catalogue override

`InvitationRenderer::render($invitation, $recipient, $guest, $templateId)` renders the
stored template, or an explicit one for a catalogue sample. When the explicit template
differs from the invitation's own, the saved **colour** settings are dropped so the
template's declared palette shows (content, wording, and motion stay). This is what lets
one sample invitation appear in every design.

`CatalogDemos::slug()` finds the newest published invitation flagged `is_catalog_demo`; the
catalogue links each card to it with `?template={id}`. The public controller only honours
`template` when the invitation is a catalog demo and the id is registered. A normal
customer invitation ignores it — the stored template is the customer's choice.

## 10. New Template Checklist

Creating a template means touching every registration point below. Skipping one makes the
design render, preview, or test wrongly, usually silently.

1. `resources/invitation-templates/{id}/manifest.json` — `id` equals the folder name; set
   `name`, `version`, `entry_view` (`{id}.views.index`), `preview`, `sections`,
   `section_height`, `labels`, and `settings_schema`.
2. `views/index.blade.php` — root element carrying the template class; render enabled
   sections in `sections` order and honor `section_heights` when `section_height` is true.
3. Write the sections inline in `index.blade.php` and include the shared partials
   (`invitations.shared.*`); split into `views/sections/*.blade.php` only if you prefer.
4. `assets/theme.css` and `assets/theme.js` — scoped to the template root. Every structural
   class the view renders must have a rule in `theme.css`, and the tint rule must be the
   last rule that paints `.invitation-section`.
5. `preview.svg` (or a raster image) — served at `/template-previews/{id}`.
6. Register **both** asset inputs in `vite.config.js` (`theme.css` and `theme.js`). Without
   this the built stylesheet never loads.
7. Add the id to `TemplateMultiLineTest::CONVERTED` (templates that keep an operator's line
   breaks in a textarea). `test_the_unconverted_templates_are_exactly_the_ones_still_pending`
   fails until the two lists agree.
8. Declare the common settings keys (section 4), especially `accent_color` presets led by
   the default, and `show_rsvp_summary`.
9. Keep the label keys a view reads aligned with the manifest `labels`.
10. No model access, no session/auth, no cross-template includes.

## 11. Contract tests

These tests encode sections 3–10; run them when you touch a template or the contract. See
`references/verification.md` for commands and the full list.

- `tests/Unit/TemplateClassContractTest.php` — every structural class is styled; the tint
  is the last background rule; notice colour outranks paragraph rules.
- `tests/Feature/InvitationArchitectureTest.php` — registry resolution and traversal guard,
  colour presets led by the default, the view-model contract, `show_rsvp_summary` on every
  template, closing families, blocks grouping, section order.
- `tests/Feature/TemplateMultiLineTest.php` — the `CONVERTED` allow-list.
