# JALL Invitation — Changelog

Semua perubahan penting pada proyek ini didokumentasikan di file ini.

Format berdasarkan [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Changed

- **Hadiah tampil sebagai kartu di semua template (2026-10-03)**
  - Bagian hadiah kini memakai tampilan kartu yang sama seperti Elegant Rose di semua template: transfer bank digambar sebagai kartu ATM (warnanya mengikuti bank bila dikenali), e-wallet dan hadiah fisik jadi kartu terpisah, masing-masing dengan tombol salin. Partial kartu dipindah ke `invitations/shared` supaya semua template memakainya.
- **Keluarga di penutup berdampingan di HP juga (2026-10-03)**
  - Dua kolom keluarga mempelai di bagian penutup kini tetap berdampingan di layar sempit (tidak lagi menumpuk), sama seperti Elegant Rose, dan berlaku di semua template. Label, jarak, dan ukuran huruf dikecilkan agar tetap rapi.

### Added

- **Opsi jadi perilaku otomatis di semua template (2026-10-03)**
  - Empat opsi yang dulu toggle hanya di Elegant Rose kini otomatis dan berlaku di kelima template: jam selesai kosong otomatis menulis "s/d Selesai", sampul otomatis menampilkan nama pasangan bertumpuk, video cover otomatis dipakai di seksi pembuka, dan hadiah otomatis tampil sebagai satu blok "Kirim Hadiah".
  - Hanya dua opsi yang tetap bisa diatur per undangan: sembunyikan zona waktu (WIB/WITA) dan gabung RSVP & Buku Ucapan.
  - Fun Storybook kini mendukung video cover (sebelumnya hanya poster), jadi video pembuka otomatis bisa dipakai di template itu.

- **Opsi khusus per undangan (2026-10-01)**
  - Lima opsi baru pada Elegant Rose yang diaktifkan per undangan: sampul menampilkan nama pasangan bertumpuk, sembunyikan zona waktu (WIB/WITA), jam selesai "s/d Selesai", gabung RSVP & Buku Ucapan jadi satu form (catatan otomatis jadi ucapan), dan hadiah dalam satu blok "Kirim Hadiah".
  - Form gabungan menyimpan RSVP sekaligus ucapan (menunggu moderasi) lewat endpoint baru `/{slug}/konfirmasi`.

- **Keluarga berdampingan di penutup (2026-10-01)**
  - Field baru "Keluarga di Penutup" di admin: tiap baris jadi satu kolom, dua baris tampil berdampingan (menumpuk di layar sempit). Berlaku di kelima template, dengan label dan nama yang tetap bisa diedit.
  - Field "Turut Mengundang / Teks Bawah (opsional)" dirender **setelah** blok keluarga, jadi "turut mengundang" tetap berada di bawahnya.

- **Popup detail mempelai (2026-09-30)**
  - Kartu mempelai menampilkan foto, peran, nama, orang tua ("Putra/Putri dari"), dan Instagram. Klik **foto** mempelai membuka dialog berisi urutan anak dan bio. Berlaku di kelima template.
  - Urutan anak dan bio tetap dirender inline dan hanya diringkas setelah script siap (`hosts-armed`), jadi tanpa JavaScript semua informasi tetap terbaca.

- **Template Celestial Vow (2026-09-30)**
  - Template langit malam "the sky writes your names": rasi bintang di sampul yang menggambar sendiri saat undangan dibuka, navigasi titik bintang di tepi kanan, parallax bintang saat scroll, galeri grid vertikal, dan countdown dengan bulan.
  - Manifest, preview SVG, CSS, dan JavaScript terisolasi; terdaftar di Vite dan seluruh kontrak template.

- **Revisi Elegant Rose: mempelai sejajar & galeri vertikal (2026-09-30)**
  - Foto kedua mempelai kini sejajar (tidak lagi bertingkat).
  - Galeri tampil ke bawah sebagai grid (tanpa geser ke samping); foto pertama jadi bingkai besar, sisanya mengalir di bawahnya.

- **Nomor urut di kartu acara dihapus (2026-09-29)**
  - Angka "01 / 02 / ..." di kartu acara dihapus dari semua template; urutan acara tetap jelas dari tata letaknya. Nomor di navigasi/galeri Midnight Ledger tidak diubah.

- **Teks orang tua mempelai tidak terpotong (2026-09-29)**
  - Pemotongan/penyembunyian teks orang tua dihapus di semua template: Midnight Ledger tidak lagi membatasi 2 baris, Coastal Vow tidak lagi menyembunyikan barisnya di layar kecil.
  - "Putra/Putri dari" kini jadi label kecil di baris sendiri dengan nama orang tua di bawahnya, jadi nama panjang tidak terbelah.

- **Judul penutup pakai nama pasangan (2026-09-29)**
  - Seksi penutup kini menampilkan nama kedua mempelai (mis. "Teddy & Anindya"), bukan judul undangan lengkap ("Pernikahan Teddy & Anindya"). Bila belum ada dua mempelai, kembali ke judul undangan.

- **Buku ucapan panjang tidak memanjangkan halaman (2026-09-29)**
  - Daftar ucapan dibatasi setinggi ~45% layar dan digulir di dalam kotaknya (maksimal 20 ucapan terbaru dimuat), jadi seksi tetap ringkas walau ucapan banyak. Kotaknya bisa difokus keyboard.

- **Hapus aksi kalender (2026-09-29)**
  - Tombol "Google Calendar" dan "Unduh ICS" dihapus dari semua template; seksi "Simpan Kalender" ikut dihapus dari daftar seksi supaya tidak jadi kontrol mati. Endpoint unduh ICS tetap tersedia di backend.

- **Perataan seksi bebas (2026-09-29)**
  - Tiap elemen pada Seksi Tambahan (Blok Bebas) bisa diatur perataannya (kiri/tengah/kanan/rata kiri-kanan) dari panel admin. "Judul Seksi Baru" mengatur perataan seluruh seksi; elemen tanpa perataan sendiri mengikuti seksi induknya.

- **Link umum tanpa sapaan (2026-09-29)**
  - Link tanpa nama tamu tidak lagi menampilkan "Kepada Yth. Bapak/Ibu/Saudara/i" di sampul maupun mengisi kolom Nama; tamu mengisi namanya sendiri di form RSVP dan buku ucapan. Link personal tetap menyapa dan mengisi nama tamu. Pesan share juga disesuaikan.

- **Label zona waktu yang enak dibaca (2026-09-29)**
  - Undangan menampilkan `WIB` / `WITA` / `WIT` (atau offset `UTC±HH:MM` untuk zona lain) alih-alih nama IANA mentah seperti `Asia/Makassar`. Data dan file kalender tetap memakai nama IANA.

- **Scroll normal pada template (2026-09-29)**
  - Halaman undangan memakai scroll normal dan smooth; pemaksaan scroll-snap vertikal di Midnight Ledger dan Coastal Vow dihapus (snap horizontal galeri tetap dipertahankan).

- **Preview katalog memakai palet template (2026-09-29)**
  - Saat undangan contoh tampil pada desain lain, warna prime bawaan template itu yang dipakai, bukan warna yang tersimpan di undangan contoh.

- **Warna rekomendasi per template (2026-09-29)**
  - Setiap template mendeklarasikan preset warna aksen (dan latar) pada manifest; panel admin menampilkan swatch rekomendasi di samping color picker.
  - Nilai preset divalidasi sebagai hex enam digit sebelum dipakai, dan selalu dimulai dari warna bawaan template.

- **Satu undangan contoh untuk seluruh katalog (2026-09-29)**
  - Katalog memakai satu undangan contoh untuk semua template; tiap desain merender data yang sama dengan gayanya sendiri lewat `?template=`.
  - `CatalogDemos` mengembalikan satu slug contoh terbaru, dan override template hanya berlaku untuk undangan contoh.

- **Template Coastal Vow (2026-09-29)**
  - Template pesisir "Tide Lines": hero cakrawala bertingkat, palet kaca laut, dan tipografi Fraunces/Karla.
  - Navigasi dock bawah yang menyingkir saat tamu membaca, galeri horizontal berbasis swipe, dan motion "tide rises".
  - Form RSVP dan buku ucapan responsif: selebar kolom di layar HP dengan border field yang jelas.
  - Manifest, preview SVG, CSS, dan JavaScript terisolasi; terdaftar pada Vite dan seluruh kontrak template.

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
