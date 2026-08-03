# JALL Invitation — Changelog

Semua perubahan penting pada proyek ini didokumentasikan di file ini.

Format berdasarkan [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Added

- **Fase 12: Template Kedua dan Ekspansi (2026-08-03)**
  - Template Midnight Ledger dengan layout editorial, navigasi rail, galeri horizontal, dan aset terisolasi.
  - Preview SVG dan galeri template pada form admin.
  - Default tema per-manifest serta test pergantian template tanpa kehilangan konten.

- **Fase 10: Mobile Polish, Testing, dan Pre-Deploy (2026-08-03)**
  - Perbaikan keyboard focus pada cover, wrapping konten panjang, dan feedback tombol salin.
  - Error bag terpisah untuk RSVP dan buku ucapan.
  - Test eksplisit kontrak `TemplateRegistry` dan `InvitationViewModel` serta laporan audit kualitas.

- **Fase 11: Kesiapan Deployment Shared Hosting (2026-08-03)**
  - Environment production example yang aman dan dokumentasi deployment cPanel/shared hosting.
  - Prosedur instalasi, cron scheduler, backup, health check, verifikasi, dan deployment berikutnya.

- **Fase 9: Tema, Preview, dan Polish (2026-08-03)**
  - Theme settings tervalidasi dan preview draft dengan signed URL khusus admin.
  - Statistik RSVP/ucapan, filter RSVP, serta ekspor CSV streaming.
  - Validasi Preview/Publish konsisten pada list dan edit undangan.

- **Fase 8: Galeri, Story, Hadiah, dan Kontak (2026-08-03)**
  - Galeri responsif dengan resize upload, lazy loading, dan lightbox native.
  - Hadiah digital/fisik dengan reveal dan copy action.
  - Kontak WhatsApp/telepon tervalidasi serta livestream aman.

- **Fase 7: Lokasi, Peta, Kalender, dan Countdown (2026-08-03)**
  - Preview Google Maps tanpa API key, directions, copy alamat, dan fallback tekstual.
  - Google Calendar, download ICS UTC, serta countdown timezone-aware.
  - Field koordinat dan petunjuk lokasi lengkap di admin.

- **Fase 6: Tamu, Link Personal, dan Sharing (2026-08-03)**
  - CRUD tamu Filament, token opaque otomatis, dan impor CSV tanpa dependency baru.
  - Link personal, WhatsApp sharing, serta tracking pembukaan pertama.
  - Tes normalisasi CSV, Unicode, token, tracking, dan personal sharing.

- **Fase 5: RSVP, Ucapan, dan Musik (2026-08-03)**
  - RSVP tervalidasi dengan batas rombongan, pencegahan duplikat, dan rate limit.
  - Guestbook pending dengan sanitasi, honeypot, rate limit, dan moderasi Filament.
  - Form publik reusable serta tampilan ucapan yang sudah disetujui.

- **Fase 4: Template Engine & Rendering Publik (2026-08-03)**
  - Rendering slug published melalui `InvitationViewModel`, renderer, dan manifest tervalidasi.
  - Personalisasi penerima dari token tamu atau parameter URL yang disanitasi.
  - Template Elegant Rose dengan preview serta aset CSS/JS terisolasi.
  - Tes undangan lengkap, sparse, Unicode, status publikasi, dan template tidak dikenal.

- **Fase 2: Admin CRUD — Pelanggan & Undangan (2026-08-03)**
  - `TemplateRegistry` service (`app/Services/TemplateRegistry.php`) untuk mendeteksi & mendaftar template undangan.
  - `CustomerResource` (List, Create, Edit) untuk manajemen data pelanggan.
  - `InvitationResource` (List, Create, Edit) dengan fitur:
    - Auto-generated slug dari Judul Undangan.
    - Seleksi Template dinamis via `TemplateRegistry`.
    - Lifecycle status actions (`Publish`, `Set Preview`, `Kembalikan ke Draft`).
    - Hook `afterCreate` di `CreateInvitation` untuk auto-seed 15 default sections (`opening`, `hosts`, `events`, `countdown`, `calendar`, `map`, `story`, `gallery`, `rsvp`, `guestbook`, `gifts`, `contacts`, `livestream`, `sharing`, `closing`).
  - `StatsOverviewWidget` untuk overview statistik di Filament Dashboard.
- **Fase 1: Fondasi & Infrastruktur (2026-08-03)**
  - Inisialisasi Laravel 12.64 & Filament v4.0.0.
  - 13 Tabel Migrasi, 5 Enums, 12 Eloquent Models, & Database Seeder Super Admin.
