# JALL Invitation — Product Requirements Document (PRD)

> **Versi:** 1.0.0-draft
> **Tanggal:** 2026-08-03
> **Status:** Menunggu persetujuan

---

## 1. Ringkasan Produk

**JALL Invitation** adalah platform jasa pembuatan undangan digital berbasis web. Admin menerima pesanan melalui WhatsApp, menginput data pelanggan dan undangan melalui panel admin, kemudian menerbitkan undangan yang dapat diakses publik melalui URL unik.

### 1.1 Prinsip Utama

- **Satu aplikasi, banyak pelanggan, banyak undangan.**
- **Multi-template:** satu data undangan bisa berganti template tanpa input ulang.
- **Admin-operated:** pelanggan belum memiliki akun atau dashboard sendiri.
- **Tanpa pembayaran otomatis, reseller, marketplace, atau WhatsApp blast.**
- **Shared hosting ready.**

---

## 2. Aktor dan Hak Akses

| Aktor | Deskripsi | Hak Akses |
|-------|-----------|-----------|
| **Super Admin** | Pemilik platform (Anda) | Akses penuh: kelola pelanggan, undangan, template, moderasi, pengaturan global |
| **Admin** | Staff operasional (opsional di masa depan) | Kelola undangan dan pelanggan yang ditugaskan, moderasi |
| **Pengunjung Publik** | Tamu undangan | Lihat undangan, RSVP, kirim ucapan, lihat peta, salin alamat, unduh kalender |
| **Pelanggan** | Pemesan undangan (via WhatsApp) | Tidak memiliki akses ke sistem; berinteraksi via WhatsApp |

### 2.1 Kebijakan Akses MVP

- Hanya satu role: **Super Admin** (menggunakan Filament Shield atau guard sederhana).
- Role **Admin** disiapkan di database tetapi tidak diimplementasi pada MVP.

---

## 3. Alur Pemesanan

```
┌──────────────┐    ┌──────────────────┐    ┌─────────────────┐
│  Pelanggan   │───▶│  Komunikasi via  │───▶│  Admin membuat  │
│  menghubungi │    │  WhatsApp        │    │  data pelanggan │
│  via WA      │    │                  │    │  & undangan     │
└──────────────┘    └──────────────────┘    └────────┬────────┘
                                                     │
                    ┌──────────────────┐              ▼
                    │  Admin memilih   │    ┌─────────────────┐
                    │  template &      │◀───│  Input data:    │
                    │  konfigurasi     │    │  pasangan, acara│
                    │  section         │    │  galeri, dll    │
                    └────────┬─────────┘    └─────────────────┘
                             │
                             ▼
                    ┌──────────────────┐    ┌─────────────────┐
                    │  Preview link    │───▶│  Pelanggan      │
                    │  dikirim ke      │    │  review via WA  │
                    │  pelanggan       │    │                 │
                    └──────────────────┘    └────────┬────────┘
                                                     │
                             ┌────────────────────────┘
                             ▼
                    ┌──────────────────┐
                    │  Revisi?         │──── Ya ──▶ Admin edit ──▶ Preview ulang
                    └────────┬─────────┘
                             │ Tidak
                             ▼
                    ┌──────────────────┐    ┌─────────────────┐
                    │  Admin publish   │───▶│  Link undangan  │
                    │  undangan        │    │  aktif & publik  │
                    └──────────────────┘    └─────────────────┘
```

---

## 4. Fitur MVP (Fase 1–3)

### 4.1 Panel Admin

| # | Fitur | Prioritas |
|---|-------|-----------|
| 1 | Login & autentikasi admin | P0 |
| 2 | Dashboard ringkasan (jumlah undangan, RSVP terbaru, ucapan baru) | P0 |
| 3 | CRUD pelanggan | P0 |
| 4 | CRUD undangan dengan status lifecycle | P0 |
| 5 | Pemilihan template tanpa input ulang data | P0 |
| 6 | Pengelolaan profil pasangan/tuan rumah & keluarga | P0 |
| 7 | Pengelolaan jadwal acara (multi-event) | P0 |
| 8 | Pengelolaan lokasi (alamat, koordinat, catatan parkir/landmark) | P0 |
| 9 | Pengelolaan galeri foto & video | P0 |
| 10 | Pengelolaan love story / timeline | P1 |
| 11 | Pengelolaan musik latar | P0 |
| 12 | Pengelolaan amplop digital & metode hadiah | P1 |
| 13 | Pengelolaan kontak keluarga | P1 |
| 14 | Pengelolaan livestream (opsional per undangan) | P2 |
| 15 | Pengaturan section: aktif/nonaktif & urutan | P0 |
| 16 | Pengaturan tema (warna, font, foto, animasi sesuai izin template) | P1 |
| 17 | Preview undangan sebelum publish | P0 |
| 18 | CRUD tamu & impor CSV | P0 |
| 19 | Generate link personal per tamu | P0 |
| 20 | Dashboard RSVP (filter, hitung, ekspor) | P0 |
| 21 | Moderasi buku ucapan | P0 |
| 22 | Salin link & share via WhatsApp dari admin | P1 |

### 4.2 Halaman Publik (Undangan)

| # | Fitur | Prioritas |
|---|-------|-----------|
| 1 | Cover pembuka dengan nama tamu personal | P0 |
| 2 | Tombol "Buka Undangan" yang memulai musik | P0 |
| 3 | Profil pasangan & keluarga | P0 |
| 4 | Jadwal acara (akad, resepsi, dll) | P0 |
| 5 | Countdown menuju acara utama | P0 |
| 6 | Tambahkan ke kalender (Google Calendar / ICS) | P0 |
| 7 | Alamat lengkap dengan salin alamat | P0 |
| 8 | Preview Maps (embed atau gambar interaktif) | P0 |
| 9 | Tombol petunjuk arah Google Maps | P0 |
| 10 | Petunjuk lokasi, parkir, landmark | P1 |
| 11 | Love story / timeline | P1 |
| 12 | Galeri foto responsif | P0 |
| 13 | Galeri video (opsional) | P2 |
| 14 | RSVP form (hadir/tidak hadir/belum pasti + jumlah tamu) | P0 |
| 15 | Buku ucapan dengan moderasi | P0 |
| 16 | Amplop digital (bank, e-wallet, alamat kirim) | P1 |
| 17 | Kontak keluarga | P1 |
| 18 | Livestream link (opsional) | P2 |
| 19 | Salin link & bagikan via WhatsApp | P0 |
| 20 | Closing section | P0 |
| 21 | Mobile-first responsive design | P0 |
| 22 | Fallback neutral jika nama tamu kosong | P0 |

---

## 5. Fitur Lanjutan (Post-MVP)

| # | Fitur | Fase |
|---|-------|------|
| 1 | Role Admin staff dengan pembagian tugas | 4+ |
| 2 | Dashboard pelanggan (self-service) | 4+ |
| 3 | Pembayaran otomatis | 4+ |
| 4 | Analytics pengunjung per undangan | 4+ |
| 5 | WhatsApp blast / notifikasi otomatis | 4+ |
| 6 | Marketplace template | 4+ |
| 7 | Sistem reseller | 4+ |
| 8 | Multi-bahasa (i18n) | 4+ |
| 9 | Watermark pada preview (sebelum bayar) | 4+ |
| 10 | Custom domain per undangan | 4+ |
| 11 | QR code undangan | 4+ |
| 12 | Template builder visual | 4+ |

---

## 6. Status Undangan (Lifecycle)

```
         ┌─────────┐
         │  draft   │ ◀── Baru dibuat / belum lengkap
         └────┬─────┘
              │ Admin request preview
              ▼
         ┌─────────┐
         │ preview  │ ◀── Dapat dilihat via preview link (bukan public slug)
         └────┬─────┘
              │ Admin publish
              ▼
         ┌──────────┐
         │published │ ◀── Aktif, dapat diakses publik
         └────┬─────┘
              │ Melewati tanggal kadaluarsa ATAU admin unpublish
              ▼
         ┌─────────┐
         │ expired  │ ◀── Tidak aktif, tampilkan pesan kadaluarsa
         └────┬─────┘
              │ Admin archive
              ▼
         ┌──────────┐
         │ archived │ ◀── Tidak dapat diakses publik, data tersimpan
         └──────────┘
```

### Aturan Transisi

| Dari | Ke | Syarat |
|------|----|--------|
| draft | preview | Data wajib terisi minimal |
| preview | published | Validasi lengkap + admin konfirmasi |
| preview | draft | Admin mengembalikan ke draft |
| published | expired | Otomatis (cron) atau manual |
| published | draft | Admin unpublish untuk revisi besar |
| expired | published | Admin perpanjang masa aktif |
| expired | archived | Admin arsipkan |
| archived | draft | Admin reaktivasi (opsional) |

---

## 7. Persyaratan Non-Fungsional

| Kategori | Persyaratan |
|----------|-------------|
| **Performa** | Halaman publik load < 3 detik pada 3G; lazy-load gambar dan media |
| **Keamanan** | XSS escape pada semua output; CSRF pada form; validasi upload; rate limiting RSVP & guestbook |
| **Responsif** | Mobile-first (360–390px), tablet (768px), desktop (1280px+) |
| **Aksesibilitas** | Heading hierarchy benar; alt text pada gambar; kontras cukup; reduced-motion support |
| **SEO** | Meta title & description per undangan; semantic HTML |
| **Hosting** | Shared hosting compatible (tanpa Docker/container dependency) |
| **Backup** | Database backup strategy via cron/manual |
| **Media** | Responsive image variants; format WebP preferred; max upload size configurable |
