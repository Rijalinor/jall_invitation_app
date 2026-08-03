# JALL Invitation — Architecture Document

> **Versi:** 1.0.0-draft
> **Tanggal:** 2026-08-03

---

## 1. Stack Teknologi

| Komponen | Teknologi | Versi | Alasan |
|----------|-----------|-------|--------|
| Backend Framework | Laravel | 12.x | PHP 8.2 kompatibel, security support hingga Feb 2027 |
| Admin Panel | Filament | 4.x | Stabil, bug fixes hingga Jan 2027, Laravel 12 kompatibel |
| Database | MariaDB | 10.4.x | Sudah tersedia di XAMPP; kompatibel shared hosting |
| Frontend (Template) | Blade + Tailwind CSS 4 + Alpine.js | Latest stable | Sesuai stack Filament 4; ringan untuk shared hosting |
| Interaktivitas Admin | Livewire | 3.x (bundled Filament) | Sudah terintegrasi dengan Filament |
| Build Tool | Vite | Bundled Laravel 12 | Default build tool Laravel |
| PHP | 8.2.12 | — | Versi di environment XAMPP |
| Node.js | 24.15.0 | — | Untuk asset compilation |
| npm | 11.12.1 | — | Package manager |

### 1.1 Pertimbangan Shared Hosting

- Tidak menggunakan Docker, Redis, queue worker daemon, atau layanan external wajib.
- Queue menggunakan `database` driver (bukan Redis).
- Cache menggunakan `file` driver (default).
- Session menggunakan `file` driver.
- Cron job tunggal untuk `schedule:run`.
- Asset di-compile lokal, hasil build di-deploy (bukan compile di server).
- Storage link melalui symlink atau konfigurasi public disk.

---

## 2. Arsitektur Layer (Three-Layer Separation)

Mengikuti panduan dari skill reference `template-architecture.md`:

```
┌─────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                    │
│  Template Manifest + Views + CSS + JS + Assets          │
│  Setiap template punya folder sendiri, manifest,        │
│  preview, dan deklarasi section yang didukung            │
│  Template TIDAK boleh query database langsung            │
├─────────────────────────────────────────────────────────┤
│                     FEATURE LAYER                        │
│  Filament Admin Panel │ Controllers │ Services           │
│  Validation │ Authorization │ Upload │ RSVP │ Moderation │
│  Guest-link generation │ Template Registry │ Renderer     │
├─────────────────────────────────────────────────────────┤
│                     CONTENT LAYER                        │
│  Models │ Migrations │ Database                          │
│  Customer, Invitation, Host, Event, Story, Gallery,      │
│  Guest, RSVP, Guestbook, Gift, Contact, Section, Media   │
└─────────────────────────────────────────────────────────┘
```

---

## 3. Kontrak Sistem Template

### 3.1 Template Manifest

Setiap template wajib memiliki file `manifest.json`:

```json
{
  "id": "elegant-rose",
  "name": "Elegant Rose",
  "version": "1.0.0",
  "event_types": ["wedding", "engagement"],
  "entry_view": "invitation-templates.elegant-rose.index",
  "preview": "preview.webp",
  "thumbnail": "thumbnail.webp",
  "sections": [
    "opening", "hosts", "events", "countdown", "calendar",
    "map", "story", "gallery", "rsvp", "guestbook",
    "gifts", "contacts", "livestream", "sharing", "closing"
  ],
  "settings_schema": {
    "accent_color": { "type": "color", "default": "#b76e79" },
    "secondary_color": { "type": "color", "default": "#f5e6e8" },
    "font_display": {
      "type": "select",
      "options": ["Cormorant Garamond", "Playfair Display", "Great Vibes"],
      "default": "Playfair Display"
    },
    "font_body": {
      "type": "select",
      "options": ["Inter", "Lato", "Poppins"],
      "default": "Inter"
    },
    "motion": {
      "type": "select",
      "options": ["calm", "expressive", "off"],
      "default": "calm"
    },
    "cover_style": {
      "type": "select",
      "options": ["fullscreen", "card", "split"],
      "default": "fullscreen"
    }
  }
}
```

### 3.2 Template Registry

```php
// app/Services/TemplateRegistry.php
// - Discover manifests dari folder resources/invitation-templates/
// - Validasi manifest schema
// - Resolve entry view path secara aman (no arbitrary path inclusion)
// - Cache manifest discovery
// - Reject unknown template IDs
```

### 3.3 Invitation View Model

Template menerima `InvitationViewModel` yang berisi data presentation-ready:

```php
// app/ViewModels/InvitationViewModel.php
class InvitationViewModel {
    public string $title;
    public string $eventType;
    public string $recipientName;      // sanitized, with fallback
    public ?string $recipientToken;
    public array $hosts;               // [{name, role, photo_url, bio, parents}]
    public array $events;              // [{label, date, start_time, end_time, timezone, venue, address, map_url, lat, lng, notes}]
    public array $stories;             // [{date, title, body, image_url}]
    public array $gallery;             // [{url, alt_text, type}]
    public array $giftMethods;         // [{type, provider, account_name, display_value}]
    public array $contacts;            // [{label, name, channel, value}]
    public array $sections;            // [{key, enabled, position, content}]
    public array $themeSettings;       // merged: template defaults + invitation overrides
    public array $featureFlags;        // {rsvp_enabled, guestbook_enabled, gifts_enabled, ...}
    public ?string $musicUrl;
    public ?string $livestreamUrl;
    public ?string $closingMessage;
    public Carbon $primaryEventDate;
    public string $shareUrl;
    public string $whatsappShareUrl;
}
```

### 3.4 Rendering Flow

```
1. Request: /{slug}?to=NamaTamu atau /{slug}/g/{token}
2. Resolve invitation by slug (hanya status published)
3. Resolve recipient:
   a. Jika ada token → cari guest record
   b. Jika ada ?to= → sanitize & display
   c. Fallback → "Bapak/Ibu/Saudara/i"
4. Load invitation data + relations (eager load)
5. Merge theme settings: template defaults ← invitation overrides
6. Validate template via TemplateRegistry
7. Build InvitationViewModel (immutable)
8. Render template entry view
```

### 3.5 Aturan Isolasi Template

- Setiap template memiliki folder sendiri di `resources/invitation-templates/{template-id}/`
- CSS dan JS di-scope per template (namespace class atau build terpisah)
- Template TIDAK boleh:
  - Query model/database langsung
  - Mengakses session atau auth
  - Include file dari template lain
  - Mempengaruhi style admin panel
- Template BOLEH:
  - Menggunakan layout dan section implementation berbeda
  - Memiliki desain, navigasi, animasi yang sepenuhnya unik
  - Mendefinisikan section order sendiri dalam manifest
  - Wrap shared feature forms (RSVP, guestbook) dengan style sendiri

---

## 4. Struktur Folder Proyek

```
wedding_app_jall/
├── .agents/                          # PRESERVED — skill files
│   └── skills/
│       └── build-digital-invitations/
├── app/
│   ├── Enums/
│   │   ├── InvitationStatus.php
│   │   ├── EventType.php
│   │   ├── GiftMethodType.php
│   │   ├── ModerationStatus.php
│   │   └── RsvpStatus.php
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── CustomerResource/
│   │   │   ├── InvitationResource/
│   │   │   ├── GuestResource/
│   │   │   ├── RsvpResource/
│   │   │   └── GuestbookEntryResource/
│   │   ├── Pages/
│   │   │   └── Dashboard.php
│   │   └── Widgets/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── PublicInvitationController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Customer.php
│   │   ├── Invitation.php
│   │   ├── Host.php
│   │   ├── Event.php
│   │   ├── Section.php
│   │   ├── Story.php
│   │   ├── Media.php
│   │   ├── Guest.php
│   │   ├── Rsvp.php
│   │   ├── GuestbookEntry.php
│   │   ├── GiftMethod.php
│   │   └── Contact.php
│   ├── Services/
│   │   ├── TemplateRegistry.php
│   │   ├── InvitationRenderer.php
│   │   └── GuestImportService.php
│   └── ViewModels/
│       └── InvitationViewModel.php
├── config/
│   └── invitation.php               # Konfigurasi platform
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── docs/
│   ├── PRD.md
│   ├── ARCHITECTURE.md
│   ├── DATABASE.md
│   └── IMPLEMENTATION_PHASES.md
├── public/
│   ├── build/                        # Compiled assets
│   └── storage/                      # Symlink ke storage/app/public
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   ├── views/
│   │   ├── layouts/
│   │   ├── public/
│   │   │   ├── invitation-shell.blade.php
│   │   │   ├── expired.blade.php
│   │   │   └── not-found.blade.php
│   │   └── shared/
│   │       ├── sections/             # Shared section partials (RSVP form, etc.)
│   │       └── components/           # Reusable Blade components
│   └── invitation-templates/         # ★ TEMPLATE DIRECTORY
│       ├── elegant-rose/
│       │   ├── manifest.json
│       │   ├── preview.webp
│       │   ├── thumbnail.webp
│       │   ├── views/
│       │   │   ├── index.blade.php
│       │   │   └── sections/
│       │   │       ├── opening.blade.php
│       │   │       ├── hosts.blade.php
│       │   │       ├── events.blade.php
│       │   │       └── ...
│       │   └── assets/
│       │       ├── theme.css
│       │       └── theme.js
│       └── {template-id}/           # Template lain mengikuti struktur sama
├── routes/
│   ├── web.php
│   └── api.php
├── storage/
│   └── app/
│       └── public/
│           ├── invitations/          # Per-invitation media
│           │   └── {invitation-id}/
│           │       ├── gallery/
│           │       ├── hosts/
│           │       ├── stories/
│           │       └── music/
│           └── templates/            # Template preview assets
├── tests/
│   ├── Feature/
│   └── Unit/
├── README.md
├── PROJECT_STATUS.md
├── SESSION_HANDOVER.md
├── CHANGELOG.md
├── .env.example
├── composer.json
├── package.json
└── vite.config.js
```

---

## 5. Strategi Penyimpanan Media

### 5.1 Struktur Storage

```
storage/app/public/
├── invitations/{invitation-id}/
│   ├── gallery/                 # Foto galeri
│   │   ├── original/
│   │   └── thumbnails/
│   ├── hosts/                   # Foto pasangan/keluarga
│   ├── stories/                 # Foto love story
│   ├── music/                   # File audio
│   └── cover/                   # Foto cover (jika custom)
└── templates/
    └── {template-id}/
        ├── preview.webp
        └── thumbnail.webp
```

### 5.2 Aturan Upload

| Jenis | Format | Max Size | Resize |
|-------|--------|----------|--------|
| Foto galeri | JPG, PNG, WebP | 5 MB | 1200px, 600px thumbnail |
| Foto profil | JPG, PNG, WebP | 3 MB | 800px |
| Foto story | JPG, PNG, WebP | 3 MB | 800px |
| Audio | MP3, M4A, OGG | 10 MB | Tidak |
| Video | Embed URL only | — | — |

### 5.3 Optimisasi

- Generate thumbnail dan versi responsif saat upload.
- Prefer WebP output.
- Lazy-load semua gambar di bawah fold.
- Serve melalui public disk dengan symlink.
- Cleanup file saat undangan dihapus.

---

## 6. Strategi Keamanan

### 6.1 Input & Output

- **XSS Prevention:** Escape semua output menggunakan `{{ }}` Blade (bukan `{!! !!}` kecuali markup terkontrol).
- **Sanitasi nama tamu:** Strip HTML, trim, dan URL-decode pada parameter `to`.
- **CSRF Protection:** Semua form menggunakan `@csrf`.
- **Upload Validation:** Validasi MIME type, ukuran, dan ekstensi. Simpan di luar webroot.

### 6.2 Autentikasi & Otorisasi

- Admin login melalui Filament authentication.
- Tidak ada public authentication (tamu tidak login).
- Guard default Laravel untuk admin.
- Filament policies untuk resource-level authorization.

### 6.3 Guest Link Privacy

- Guest token menggunakan opaque string (UUID atau random token), bukan ID numerik.
- Tidak ada cara menebak token tamu lain.
- Guest list tidak terekspos ke publik.
- Gift detail (nomor rekening) ditampilkan secara intentional, bukan di source HTML mentah.

### 6.4 Rate Limiting

- RSVP submission: max 5 per menit per IP.
- Guestbook submission: max 3 per menit per IP.
- Admin login: throttle bawaan Filament/Laravel.

### 6.5 Data Protection

- Nomor rekening dan data sensitif di-encrypt at rest (optional, configurable).
- Backup database terjadwal.
- `.env` tidak pernah di-commit.

---

## 7. Strategi Testing

### 7.1 Test Categories

| Kategori | Cakupan | Tool |
|----------|---------|------|
| Unit Tests | TemplateRegistry, InvitationViewModel, helpers | PHPUnit |
| Feature Tests | Rendering published invitation, RSVP submission, guest resolution, admin CRUD | PHPUnit + Laravel HTTP tests |
| Browser Tests | Visual rendering, mobile responsiveness (opsional) | Laravel Dusk (post-MVP) |
| Static Analysis | Code quality | Larastan / PHPStan level 5 |

### 7.2 Fixture Strategy

- **Complete fixture:** Undangan dengan semua section terisi.
- **Sparse fixture:** Undangan dengan hanya section wajib.
- **Edge cases:** Nama Unicode, nama panjang, alamat panjang, gambar kosong.

### 7.3 Template Testing

- Setiap template baru harus render tanpa error menggunakan fixture lengkap dan sparse.
- Template switching tidak merusak data undangan.

---

## 8. Strategi Deployment (Shared Hosting)

### 8.1 Struktur Deploy

```
public_html/             ← Document root (isi folder public/)
├── index.php
├── .htaccess
├── build/
└── storage/             ← Symlink ke ../app/storage/app/public

../app/                  ← Laravel app (di luar public_html)
├── artisan
├── composer.json
├── .env
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
└── vendor/
```

### 8.2 Deployment Steps

1. Build assets lokal: `npm run build`
2. Upload seluruh folder app ke server (di luar public_html)
3. Upload isi folder `public/` ke `public_html/`
4. Edit `index.php` untuk mengarah ke path Laravel yang benar
5. Create symlink storage
6. Set permissions: `storage/` dan `bootstrap/cache/` writable
7. Run `php artisan migrate --force`
8. Run `php artisan config:cache && php artisan route:cache && php artisan view:cache`
9. Setup cron: `* * * * * php /path/to/artisan schedule:run`

### 8.3 Environment Requirements

- PHP ≥ 8.2 dengan extensions: bcmath, ctype, curl, dom, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml
- MariaDB ≥ 10.4 atau MySQL ≥ 8.0
- Composer (untuk dependency)
- Cron access (untuk schedule)
- SSH access (recommended, bukan wajib)

---

## 9. Halaman Panel Admin

| # | Halaman | Resource/Page |
|---|---------|---------------|
| 1 | Dashboard | Custom Filament Dashboard |
| 2 | Daftar Pelanggan | CustomerResource (List) |
| 3 | Detail Pelanggan | CustomerResource (View) |
| 4 | Form Pelanggan | CustomerResource (Create/Edit) |
| 5 | Daftar Undangan | InvitationResource (List) |
| 6 | Detail Undangan | InvitationResource (View) — with tabs |
| 7 | Form Undangan | InvitationResource (Create/Edit) |
| 8 | Tab: Profil Pasangan | InvitationResource → HostsRelationManager |
| 9 | Tab: Jadwal Acara | InvitationResource → EventsRelationManager |
| 10 | Tab: Love Story | InvitationResource → StoriesRelationManager |
| 11 | Tab: Galeri | InvitationResource → MediaRelationManager |
| 12 | Tab: Pengaturan Section | InvitationResource → SectionsRelationManager |
| 13 | Tab: Amplop Digital | InvitationResource → GiftMethodsRelationManager |
| 14 | Tab: Kontak | InvitationResource → ContactsRelationManager |
| 15 | Tab: Daftar Tamu | InvitationResource → GuestsRelationManager |
| 16 | Tab: RSVP | InvitationResource → RsvpsRelationManager |
| 17 | Tab: Buku Ucapan | InvitationResource → GuestbookRelationManager |
| 18 | Tab: Pengaturan Tema | InvitationResource → Custom Page |
| 19 | Preview Undangan | Custom route / page |
| 20 | Import Tamu | Custom action/page |
| 21 | Ekspor RSVP | Custom action |

---

## 10. Halaman Publik

| # | Route | Deskripsi |
|---|-------|-----------|
| 1 | `/{slug}` | Undangan publik (tanpa personalisasi) |
| 2 | `/{slug}?to={name}` | Undangan publik dengan nama tamu (URL param) |
| 3 | `/{slug}/g/{token}` | Undangan publik dengan guest token |
| 4 | `/{slug}/preview` | Preview undangan (membutuhkan auth/signed URL) |
| 5 | `/rsvp/{invitation}` | POST endpoint RSVP submission |
| 6 | `/guestbook/{invitation}` | POST endpoint guestbook submission |
| 7 | `/expired` | Halaman undangan kadaluarsa |
| 8 | `/not-found` | Halaman undangan tidak ditemukan |
