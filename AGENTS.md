# JALL Invitation Agent Guide

## Project Scope

JALL Invitation is a Laravel 12 and Filament 4 platform for multiple customers and reusable digital invitation templates. Use the existing architecture and conventions before introducing new abstractions.

## Key Boundaries

- Content: Eloquent models, migrations, factories, and seeders in `app/Models` and `database/`.
- Features: controllers, validation, authorization, uploads, RSVP, guestbook, and rendering services in `app/Http` and `app/Services`.
- Admin: Filament panel configuration, resources, pages, widgets, and relation managers in `app/Providers/Filament` and `app/Filament`.
- Presentation: Blade views and isolated assets in `resources/views` and `resources/invitation-templates`.
- Public routes and throttling are defined in `routes/web.php`.

Templates must render from `InvitationViewModel` and must not query models, access session/auth directly, include another template, or affect Filament styling. Keep each template's manifest, views, CSS, JavaScript, and media isolated; register assets through `vite.config.js`.

For template or invitation work, read [.agents/skills/build-digital-invitations/SKILL.md](.agents/skills/build-digital-invitations/SKILL.md) and its relevant references first.

## Security-Critical Behavior

- Public invitations resolve only published, non-expired invitations.
- Guest links should use opaque tokens. Sanitize, length-limit, and escape `?to=` recipient values, with a neutral fallback when absent.
- Preserve CSRF protection, rate limits, validation, separate RSVP/guestbook error bags, and authorization when changing public forms or admin actions.
- Treat uploaded media and gift/account details as untrusted or sensitive data.

## Development Commands

Run focused checks first, then the broader checks when the change warrants them:

```text
composer test
vendor/bin/pint --test
npm run build
```

`composer test` clears config and runs the Laravel test suite. Tests use the in-memory SQLite configuration from `phpunit.xml`, while local development documentation targets MariaDB. There is no npm test or lint script. Run one test with `php artisan test --filter=TestName`.

Run `composer test` once, sequentially: parallel runs corrupt `storage/framework/views` with access-denied rename errors.

`public/build` is gitignored, so rebuild (`npm run build`) and re-upload it after any CSS/JS edit. On Windows, inline `php -r` mangles `$_`/`$c`; write a temporary PHP script under the system temp dir instead.

For local setup, follow [README.md](README.md). For deployment and production safety, follow [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md); never run development seeders in production and never expose the Laravel project root as the web document root.

## Working Conventions

- Prefer small, vertical changes that preserve public APIs and existing Filament/Laravel patterns.
- Keep customer-editable content structured in models and admin fields; do not hard-code customer data into templates.
- Do not render empty optional sections. Preserve graceful fallbacks for missing media, maps, audio, and optional invitation content.
- Update or add focused tests for behavior changes. Check mobile-first template behavior and recipient names containing spaces or non-ASCII characters when relevant.
- Use four-space indentation, UTF-8, LF line endings, and a final newline as defined by `.editorconfig`.

## Deployment & media gotchas

Media URLs come from `Storage::disk('public')->url($path)` (`APP_URL` + `/storage`) in `app/ViewModels/InvitationViewModel.php`. Admin media `FileUpload` fields (cover video, poster, music, host photo, gallery, stories) all pin `->disk('public')`; only the CSV guest import uses `local`.

- `public/storage` must be a symlink to `storage/app/public`, sitting next to the web-served `index.php` (the document root). On cPanel where `public/` was copied into `public_html`, the link belongs at `~/public_html/storage`, not `~/project/public/storage`.
- Windows-built ZIPs turn the `public/storage` junction into a stale real directory: new uploads 404 while old files still resolve. Delete the directory and re-link.
- `php artisan storage:link` fails when the host disables both `symlink()` and `exec()` (Laravel falls back to `exec('ln -s …')`). Link manually: `ln -s "$(pwd)/storage/app/public" public/storage`.
- `APP_URL` must be the final HTTPS domain with no trailing slash; a copied local `.env` with `APP_URL=http://127.0.0.1:8000` breaks every media URL.

## Documentation Map

- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md): layer boundaries and template contract
- [docs/DATABASE.md](docs/DATABASE.md): data model
- [docs/PRD.md](docs/PRD.md): product requirements
- [docs/QUALITY_AUDIT.md](docs/QUALITY_AUDIT.md): known quality findings
- [PROJECT_STATUS.md](PROJECT_STATUS.md): current implementation status
- [SESSION_HANDOVER.md](SESSION_HANDOVER.md): recent handover context