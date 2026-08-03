# Reusable Template Architecture

## Contents

1. Layer boundaries
2. Template contract
3. Suggested Laravel structure
4. Suggested data model
5. Rendering flow
6. Editable design settings
7. Versioning and compatibility

## 1. Layer Boundaries

Separate the product into three layers:

1. **Content:** customer, couple or host, events, stories, gallery, gifts, guests, RSVP, and guestbook data.
2. **Features:** validation, authorization, uploads, guest-link generation, RSVP submission, moderation, analytics, and admin workflows.
3. **Presentation:** template manifest, views, components, CSS, JavaScript, fonts, decorative media, and theme defaults.

A presentation template may consume normalized content but must not own business data or feature rules.

## 2. Template Contract

Give every template a stable identifier and a manifest equivalent to:

```json
{
  "id": "garden-letter",
  "name": "Garden Letter",
  "version": "1.0.0",
  "event_types": ["wedding", "engagement"],
  "entry_view": "invitation-templates::garden-letter.index",
  "preview": "preview.webp",
  "sections": [
    "opening",
    "hosts",
    "events",
    "story",
    "gallery",
    "countdown",
    "calendar",
    "map",
    "rsvp",
    "guestbook",
    "gifts",
    "contacts",
    "livestream",
    "sharing",
    "closing"
  ],
  "settings_schema": {
    "accent_color": {"type": "color", "default": "#765448"},
    "motion": {"type": "select", "options": ["calm", "expressive", "off"], "default": "calm"}
  }
}
```

Treat the exact storage format as framework-dependent, but retain these concepts:

- stable ID and semantic version
- human-readable name and preview
- entry point
- declared compatible event types
- declared supported sections
- validated editable settings with defaults

Pass a normalized invitation view model to the template. It should expose only presentation-ready values such as hosts, events, story items, gallery items, recipient, theme settings, feature flags, and public actions. Do not pass ORM access as the template API when it can be avoided.

## 3. Suggested Laravel Structure

Adapt names to the existing project instead of forcing this exact layout:

```text
app/
  Domain/Invitations/
  ViewModels/InvitationViewModel.php
  Services/TemplateRegistry.php
  Services/InvitationRenderer.php
resources/
  views/invitations/shared/
  invitation-templates/
    garden-letter/
      manifest.json
      views/index.blade.php
      views/sections/
      assets/theme.css
      assets/theme.js
      preview.webp
```

Use a template registry to discover approved manifests, validate them, and resolve entry views. Reject unknown paths and never turn untrusted request text directly into an include path.

Shared feature forms can be rendered through stable section interfaces. A template may wrap or restyle them but should not duplicate submission and validation logic.

## 4. Suggested Data Model

Use the minimum tables required by scope. A reusable platform commonly needs:

| Table | Purpose | Important fields |
|---|---|---|
| `customers` | Owner or purchaser | name, contact |
| `invitations` | Public invitation record | customer_id, slug, event_type, title, template_id, status, published_at, settings_json |
| `hosts` | Couple, family, or event hosts | invitation_id, role, name, parent_text, bio, photo |
| `events` | Ceremony or event sessions | invitation_id, label, date, start_time, end_time, timezone, venue, address, map_url, latitude, longitude, location_notes |
| `sections` | Optional ordering and copy overrides | invitation_id, key, enabled, position, content_json |
| `stories` | Timeline entries | invitation_id, date, title, body, image, position |
| `media` | Gallery and audio metadata | invitation_id, type, path, alt_text, position |
| `guests` | Personalized recipients | invitation_id, token, display_name, group, phone, invitation_limit |
| `rsvps` | Attendance responses | guest_id or invitation_id, name, status, party_size, note |
| `guestbook_entries` | Public messages | invitation_id, guest_id nullable, name, message, moderation_status |
| `gift_methods` | Optional gift information | invitation_id, type, provider, account_name, encrypted_or_protected_value, position |
| `contacts` | Host or organizer contacts | invitation_id, label, name, channel, protected_value, position |

Use JSON only for flexible, non-relational settings or section-specific content. Keep queryable relationships and transactional responses in dedicated tables.

## 5. Rendering Flow

Use this sequence:

1. Resolve invitation by published slug.
2. Resolve recipient by opaque token, or sanitize the optional `to` value.
3. Load normalized invitation data efficiently.
4. Merge template defaults, invitation settings, and safe per-section overrides.
5. Validate the selected template against the registry.
6. Create an immutable presentation view model.
7. Render the registered entry view.

Cache public rendering where useful, but vary cache keys by invitation, template version, locale, and recipient personalization. Do not leak one guest's personalized page to another through shared caching.

## 6. Editable Design Settings

Provide two levels of customization:

- **Safe customer settings:** palette choices, allowed font pairing, cover style, photo selection, section visibility, section order, motion intensity, wording, and background music.
- **Developer template code:** composition, custom section views, decorative assets, responsive layout, advanced motion, and interaction patterns.

Validate all settings against the manifest schema. Emit CSS custom properties from allow-listed values. Never place arbitrary customer CSS or JavaScript into the public page unless the product explicitly supports trusted developer access with isolation.

Do not force all templates through one rigid DOM tree. Share data and feature contracts, while allowing each template to own its markup and visual composition.

## 7. Versioning and Compatibility

- Use semantic template versions.
- Preserve a template's existing behavior for published invitations unless a migration is intentional.
- Record or infer which template version an invitation expects.
- Add new optional view-model fields compatibly.
- Provide defaults when old invitations lack new settings.
- Test at least one existing invitation before upgrading a shared feature contract.
