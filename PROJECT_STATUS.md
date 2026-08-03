# JALL Invitation — Project Status

> **Terakhir diperbarui:** 2026-08-03

---

## Status Keseluruhan: FASE 12 SELESAI, SIAP DEPLOY

| Aspek | Status |
|-------|--------|
| Rancangan produk | ✅ Selesai |
| Arsitektur teknis | ✅ Selesai |
| Database & Migrations | ✅ Database `jall_invitation` & 13 tabel dibuat |
| Models & Enums | ✅ 12 Models, 5 Enums, Super Admin Seeder |
| Customer Admin CRUD | ✅ `CustomerResource` (List, Create, Edit, Soft Delete) |
| Invitation Admin CRUD | ✅ `InvitationResource` (Form, Slug auto-gen, Template select, Lifecycle transitions) |
| Template Registry Service | ✅ `TemplateRegistry` service untuk pengenalan manifest template |
| Next Phase | Deployment production setelah akses hosting tersedia |

---

## Fase Saat Ini

**Fase 9: Tema, Preview, dan Polish (Selesai)**

### Completed

- [x] Service `TemplateRegistry` dibuat (`app/Services/TemplateRegistry.php`)
- [x] Filament Resource `CustomerResource` & Pages (`ListCustomers`, `CreateCustomer`, `EditCustomer`)
- [x] Filament Resource `InvitationResource` & Pages (`ListInvitations`, `CreateInvitation`, `EditInvitation`)
- [x] Auto-seeding 15 default sections saat Invitation dibuat (`CreateInvitation::afterCreate()`)
- [x] Lifecycle transition actions di `InvitationResource` (`Publish`, `Set Preview`, `Kembalikan ke Draft`)
- [x] Widget `StatsOverviewWidget` untuk statistik dashboard admin
- [x] Verifikasi integrasi Customer-Invitation & status lifecycle via test execution

---

## Keputusan Teknologi

| Keputusan | Pilihan | Alasan |
|-----------|---------|--------|
| Framework | Laravel 12.64 | PHP 8.2 kompatibel |
| Admin Panel | Filament v4.0 | Panel admin modern (Schema & Component architecture) |
| Customer CRUD | `CustomerResource` | Pengelolaan data pemesan & catatan admin |
| Invitation CRUD | `InvitationResource` | Pengelolaan slug, jenis acara, template, dan status |

---

## Fase 3 Selesai
- Relationship Managers di Filament untuk `InvitationResource`:
  - `HostsRelationManager` (Mempelai / Tuan Rumah)
  - `EventsRelationManager` (Acara Akad, Resepsi, dll.)
  - `StoriesRelationManager` (Love Story / Timeline)
  - `MediaRelationManager` (Galeri Foto & Video)
  - `GiftMethodsRelationManager` (Rekening & Hadiah)
  - `ContactsRelationManager` (Kontak Penanggung Jawab)
- Widget / Manager untuk Pengaturan Urutan Section (`sections`)

- [x] Seluruh relationship manager terpasang pada halaman edit undangan
- [x] Upload foto host dan pengelolaan detail lokasi acara
- [x] Toggle serta drag-and-drop urutan section
- [x] Preview/Publish mensyaratkan minimal satu host dan satu acara utama
- [x] Tes regresi kesiapan preview

## Selanjutnya: Fase 4 (Template Engine & Rendering Publik)

- View model undangan yang tidak mengekspos ORM ke template
- Renderer dan route publik berdasarkan slug
- Resolusi nama penerima yang aman
- Template awal Elegant Rose

### Completed

- [x] Registry memvalidasi manifest dan menolak template/path asing
- [x] `InvitationViewModel` menyediakan data presentasi tanpa ORM di template
- [x] Renderer dan controller publik untuk slug published yang belum kedaluwarsa
- [x] Personalisasi token tamu dan parameter `to` dengan fallback netral
- [x] Elegant Rose dengan aset terisolasi, preview, cover, jadwal, countdown, peta, kalender, galeri, cerita, kontak, sharing, dan musik berbasis interaksi
- [x] Fixture lengkap dan sparse, Unicode, sanitasi, status publikasi, dan unknown-template teruji

## Selanjutnya: Fase 5 (RSVP, Ucapan, dan Musik)

- Endpoint dan form RSVP dengan batas jumlah tamu
- Guestbook dengan moderasi dan pembatasan spam
- Pengelolaan RSVP dan guestbook di admin

### Completed

- [x] RSVP hadir, tidak hadir, dan belum pasti dengan batas rombongan
- [x] RSVP tamu bertoken diperbarui tanpa membuat duplikat
- [x] Guestbook pending dengan sanitasi, honeypot, dan rate limit
- [x] Hanya ucapan approved yang tampil di undangan
- [x] Moderasi guestbook dan pengelolaan RSVP melalui Filament
- [x] Musik dimulai setelah interaksi dengan kontrol putar/jeda

## Selanjutnya: Fase 6 (Tamu, Link Personal, dan Sharing)

- CRUD tamu dan token otomatis
- Impor CSV tamu
- Copy link personal dan WhatsApp sharing

### Completed

- [x] CRUD tamu di halaman edit undangan
- [x] Token opaque otomatis untuk setiap tamu
- [x] Impor CSV `name,group,phone,invitation_limit`
- [x] Link personal yang dapat disalin dan WhatsApp sharing ter-encode
- [x] Pencatatan waktu pertama link dibuka
- [x] Nama Unicode, normalisasi CSV, token, tracking, dan sharing teruji

## Selanjutnya: Fase 7 (Lokasi, Peta, Kalender, dan Countdown)

- Preview peta dan fallback alamat
- Penyempurnaan directions, copy alamat, kalender, dan countdown
- Download kalender ICS

### Completed

- [x] Latitude, longitude, map URL, parkir, landmark, dan pintu masuk di admin
- [x] Preview peta responsif tanpa API key dengan fallback alamat lengkap
- [x] Google Maps directions dari koordinat atau alamat
- [x] Copy alamat dan catatan lokasi
- [x] Google Calendar dan unduhan ICS dalam UTC
- [x] Countdown berbasis acara utama dan timezone acara

## Selanjutnya: Fase 8 (Galeri, Story, Hadiah, dan Kontak)

- Penyempurnaan galeri dan optimasi gambar
- Tampilan hadiah digital serta copy detail
- Penyempurnaan kontak dan livestream

### Completed

- [x] Story/timeline terurut dengan foto opsional
- [x] Galeri responsif, lazy loading, dan lightbox `<dialog>` native
- [x] Resize upload dan pembatasan JPEG/PNG/WebP tanpa dependency baru
- [x] Hadiah bank, e-wallet, atau fisik dengan detail terlipat dan copy action
- [x] Kontak WhatsApp/telepon tervalidasi serta livestream URL aman
- [x] Optional section kosong tidak meninggalkan heading atau card kosong

## Selanjutnya: Fase 9 (Tema, Preview, dan Polish)

- Theme settings berdasarkan manifest
- Preview admin dan template switching
- Statistik serta ekspor RSVP

### Completed

- [x] Warna aksen dan motion tervalidasi sesuai schema manifest
- [x] Preview draft menggunakan renderer publik melalui signed URL 15 menit
- [x] Preview hanya dapat diakses admin terautentikasi
- [x] Pergantian template tidak mengubah data konten
- [x] Validasi kelengkapan konsisten pada aksi Preview dan Publish
- [x] Statistik RSVP/ucapan, filter RSVP, dan ekspor CSV streaming

## Selanjutnya: Fase 10 (Mobile Polish, Testing, dan Pre-Deploy)

### Completed

- [x] Audit responsive statis, aksesibilitas, keamanan, dan edge case
- [x] Keyboard focus cover, reduced motion, safe-area, dan wrapping teks panjang
- [x] Error RSVP dan buku ucapan terpisah
- [x] Fixture lengkap/sparse, Unicode, dan input berbahaya teruji
- [x] Test kontrak `TemplateRegistry` dan `InvitationViewModel`
- [x] Build production dan dokumentasi audit kualitas

### Menunggu verifikasi production

- [ ] Inspeksi browser/perangkat nyata pada 360px, 390px, 768px, dan 1280px
- [ ] Uji restore backup dan integrasi eksternal pada domain production

## Fase 11: Deployment Shared Hosting

### Completed

- [x] Build asset production tersedia di `public/build`
- [x] Contoh environment production aman tersedia
- [x] Panduan instalasi, migrasi, storage link, Filament assets, dan cache production
- [x] Konfigurasi cron scheduler terdokumentasi
- [x] Strategi backup database dan upload terdokumentasi
- [x] Health check `/up` dan checklist verifikasi production terdokumentasi

### Menunggu akses hosting

- [ ] Setup database dan cron pada panel hosting
- [ ] Upload aplikasi dan arahkan document root ke `public`
- [ ] Uji end-to-end pada domain production
- [ ] Aktifkan dan uji restore backup provider

## Fase 12: Template Kedua dan Ekspansi

### Completed

- [x] Template `Midnight Ledger` dengan konsep editorial sinematik
- [x] Struktur, navigasi, tipografi, galeri, background, dan motion berbeda dari Elegant Rose
- [x] Seluruh fitur publik memakai kontrak data bersama tanpa query ORM di template
- [x] Manifest, preview SVG, CSS, dan JavaScript terisolasi
- [x] Galeri preview template tersedia pada form undangan admin
- [x] Pergantian template mempertahankan seluruh konten undangan
- [x] Default warna dan motion mengikuti manifest template masing-masing
