# JALL Invitation — Implementation Phases

> **Versi:** 1.0.0-draft
> **Tanggal:** 2026-08-03

---

## Strategi Umum

Mengikuti prinsip **vertical slice** dari skill: bangun jalur end-to-end terkecil terlebih dahulu, lalu tambahkan fitur satu per satu. Setiap fase menghasilkan deliverable yang dapat diuji.

---

## Fase 1: Fondasi & Infrastruktur

**Estimasi:** 1–2 sesi kerja
**Prasyarat:** Persetujuan rencana

### Deliverable

- [ ] Instalasi Laravel 12 (tanpa menghapus folder `.agents`)
- [ ] Konfigurasi database MariaDB (`jall_invitation`)
- [ ] Instalasi Filament v4
- [ ] Konfigurasi Tailwind CSS, Alpine.js, Vite
- [ ] Setup `.env.example` dan konfigurasi
- [ ] Buat semua migration files (13 tabel)
- [ ] Buat semua Eloquent models dengan relasi
- [ ] Buat Enum classes (InvitationStatus, EventType, RsvpStatus, ModerationStatus, GiftMethodType)
- [ ] Setup admin user seeder
- [ ] Verifikasi: `php artisan migrate` sukses, login admin berhasil

### Kriteria Selesai

Admin dapat login ke panel Filament yang kosong. Database memiliki semua tabel.

---

## Fase 2: Admin CRUD — Pelanggan & Undangan

**Estimasi:** 1–2 sesi kerja

### Deliverable

- [ ] CustomerResource (List, Create, Edit, View) dengan Filament
- [ ] InvitationResource (List, Create, Edit, View) dengan Filament
- [ ] Form undangan: judul, slug, event_type, template_id, status, tanggal publish/expire
- [ ] Slug auto-generate dari judul
- [ ] Status lifecycle actions (draft → preview → published → expired → archived)
- [ ] Validasi status transition
- [ ] Template selection dropdown (dari TemplateRegistry — data statis dulu)
- [ ] Dashboard widget: jumlah undangan per status, pelanggan terbaru

### Kriteria Selesai

Admin dapat membuat pelanggan, membuat undangan, memilih template, dan mengubah status undangan.

---

## Fase 3: Konten Undangan — Profil, Acara, Lokasi

**Estimasi:** 1–2 sesi kerja

### Deliverable

- [ ] HostsRelationManager (CRUD profil pasangan/keluarga dalam undangan)
- [ ] EventsRelationManager (CRUD jadwal acara)
- [ ] Form lokasi lengkap: venue, alamat, koordinat, map_url, catatan parkir/landmark
- [ ] SectionsRelationManager (toggle aktif/nonaktif, reorder)
- [ ] Default sections auto-created saat undangan dibuat
- [ ] Upload foto profil (pasangan)
- [ ] Validasi minimal sebelum status preview

### Kriteria Selesai

Admin dapat mengelola seluruh konten undangan melalui tab-tab di halaman undangan.

---

## Fase 4: Template Engine & Rendering Publik

**Estimasi:** 2–3 sesi kerja

### Deliverable

- [ ] `TemplateRegistry` service — discover, validate, resolve manifests
- [ ] `InvitationViewModel` — normalized data contract
- [ ] `InvitationRenderer` service — rendering flow lengkap
- [ ] `PublicInvitationController` — route handler
- [ ] Route: `/{slug}`, `/{slug}?to={name}`, `/{slug}/g/{token}`
- [ ] Guest resolution: token → guest record, `?to=` → sanitize, fallback
- [ ] Template pertama: **Elegant Rose** (wedding template starter)
  - [ ] Manifest.json
  - [ ] Layout & section views (opening, hosts, events, countdown, map, closing)
  - [ ] Scoped CSS & JS
  - [ ] Preview image
- [ ] Halaman expired dan not-found
- [ ] Section rendering berdasarkan konfigurasi (enabled, position)

### Kriteria Selesai

Undangan yang di-publish dapat diakses publik dengan URL slug. Template menampilkan data dari database. Nama tamu terpersonalisasi.

---

## Fase 5: Fitur Interaktif — RSVP, Ucapan, Musik

**Estimasi:** 1–2 sesi kerja

### Deliverable

- [ ] RSVP form di halaman publik (attending/not attending/tentative + party size)
- [ ] RSVP validation & rate limiting
- [ ] Pencegahan duplikat RSVP per guest
- [ ] Guestbook form di halaman publik
- [ ] Guestbook moderation (auto-pending, admin approve/reject)
- [ ] Guestbook anti-spam (rate limit + honeypot)
- [ ] Music player: play/pause, mulai setelah "Buka Undangan"
- [ ] Music controls visible & accessible
- [ ] Admin: RsvpRelationManager (view responses)
- [ ] Admin: GuestbookRelationManager (moderate)

### Kriteria Selesai

Pengunjung dapat mengisi RSVP dan mengirim ucapan. Admin dapat melihat RSVP dan memoderasi ucapan.

---

## Fase 6: Tamu, Link Personal, Sharing

**Estimasi:** 1–2 sesi kerja

### Deliverable

- [ ] GuestsRelationManager (CRUD tamu dalam undangan)
- [ ] Guest token auto-generate (UUID/random)
- [ ] CSV import tamu (nama, grup, telepon, limit)
- [ ] Generate link personal per tamu
- [ ] Copy link action
- [ ] WhatsApp share action (dengan pesan template + URL encoded)
- [ ] Bulk generate links
- [ ] Link tracking (link_opened_at)

### Kriteria Selesai

Admin dapat menambah tamu satu-per-satu atau impor CSV, generate link personal, dan berbagi via WhatsApp.

---

## Fase 7: Lokasi, Peta, Kalender, Countdown

**Estimasi:** 1–2 sesi kerja

### Deliverable

- [ ] Maps preview di halaman publik (Google Maps embed tanpa API key, atau static link)
- [ ] Tombol "Petunjuk Arah" → buka Google Maps directions
- [ ] Tombol "Salin Alamat" dengan feedback
- [ ] Tampilkan petunjuk parkir, landmark
- [ ] Countdown component (Alpine.js) — target acara utama
- [ ] Countdown handle elapsed event gracefully
- [ ] Add-to-calendar: Google Calendar link + ICS file download
- [ ] Calendar link dengan title, timezone, start/end time, venue yang benar
- [ ] Fallback jika maps/embed gagal

### Kriteria Selesai

Semua fitur lokasi dan waktu berfungsi end-to-end.

---

## Fase 8: Galeri, Story, Hadiah, Kontak

**Estimasi:** 1–2 sesi kerja

### Deliverable

- [ ] StoriesRelationManager (CRUD love story)
- [ ] Love story section di template
- [ ] MediaRelationManager (upload galeri foto)
- [ ] Image optimization: resize, thumbnail, WebP conversion
- [ ] Responsive gallery di template (lightbox/modal)
- [ ] Lazy loading gambar
- [ ] GiftMethodsRelationManager (CRUD amplop digital)
- [ ] Gift section di template: bank, e-wallet, alamat
- [ ] Copy nomor rekening dengan feedback
- [ ] ContactsRelationManager (CRUD kontak keluarga)
- [ ] Contact section di template: tombol WA/telepon
- [ ] Livestream section (opsional) di template

### Kriteria Selesai

Seluruh section baseline undangan tersedia dan berfungsi.

---

## Fase 9: Tema, Preview, & Polish

**Estimasi:** 1–2 sesi kerja

### Deliverable

- [ ] Theme settings form di admin (berdasarkan manifest schema)
- [ ] Settings merge: template defaults ← invitation overrides → CSS custom properties
- [ ] Admin preview page (signed URL / authenticated)
- [ ] Preview akurat merefleksikan halaman publik
- [ ] Template switching tanpa kehilangan data
- [ ] Dashboard RSVP: statistik, filter, ekspor CSV
- [ ] Dashboard ringkasan: widget undangan, RSVP, ucapan baru
- [ ] Validation status: field wajib yang belum terisi sebelum publish

### Kriteria Selesai

Admin dapat menyesuaikan tema, preview undangan, dan mengelola data RSVP. Template switching berfungsi tanpa kehilangan data.

---

## Fase 10: Mobile Polish, Testing, & Pre-Deploy

**Estimasi:** 1–2 sesi kerja

### Deliverable

- [ ] Mobile responsiveness audit (360px, 390px, 768px, 1280px)
- [ ] Edge case testing: nama Unicode, nama panjang, alamat panjang
- [ ] Template isolation audit (CSS scope, no cross-contamination)
- [ ] Security audit: XSS, CSRF, upload validation, rate limiting
- [ ] Reduced-motion support
- [ ] Feature tests: rendering, publishing, guest resolution, RSVP, guestbook
- [ ] Unit tests: TemplateRegistry, InvitationViewModel
- [ ] Fixture tests: complete data + sparse data
- [ ] Performance: lazy loading, font loading, image optimization
- [ ] Documentation update

### Kriteria Selesai

Aplikasi siap deploy. Semua test hijau. Checklist kualitas terpenuhi.

---

## Fase 11: Deployment ke Shared Hosting

**Estimasi:** 1 sesi kerja

### Deliverable

- [ ] Build assets: `npm run build`
- [ ] Konfigurasi shared hosting deployment
- [ ] Deploy aplikasi
- [ ] Setup cron job
- [ ] Setup database production
- [ ] Test end-to-end di production
- [ ] Backup strategy aktif
- [ ] Dokumentasi deployment

### Kriteria Selesai

Aplikasi live di shared hosting dan dapat diakses publik.

---

## Fase 12: Template Kedua & Ekspansi

**Estimasi:** 1–2 sesi kerja

### Deliverable

- [ ] Template kedua dengan konsep visual berbeda (minimal 3 dimensi struktural berbeda)
- [ ] Verifikasi template switching antar 2 template
- [ ] Template preview gallery di admin

### Kriteria Selesai

Dua template berbeda tersedia dan dapat digunakan tanpa masalah.

---

## Ringkasan Timeline

| Fase | Nama | Sesi |
|------|------|------|
| 1 | Fondasi & Infrastruktur | 1–2 |
| 2 | Admin CRUD — Pelanggan & Undangan | 1–2 |
| 3 | Konten Undangan | 1–2 |
| 4 | Template Engine & Rendering | 2–3 |
| 5 | RSVP, Ucapan, Musik | 1–2 |
| 6 | Tamu, Link, Sharing | 1–2 |
| 7 | Lokasi, Peta, Kalender | 1–2 |
| 8 | Galeri, Story, Hadiah, Kontak | 1–2 |
| 9 | Tema, Preview, Polish | 1–2 |
| 10 | Testing & Pre-Deploy | 1–2 |
| 11 | Deployment | 1 |
| 12 | Template Kedua | 1–2 |
| **Total** | | **~13–22 sesi** |

> **Catatan:** Urutan Fase 5–8 bersifat fleksibel dan dapat dikerjakan dalam urutan berbeda berdasarkan prioritas bisnis.
