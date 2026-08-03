# Fase 10 — Quality Audit

Tanggal audit: 2026-08-03

## Terverifikasi

- Layout mobile memakai lebar fleksibel, `clamp()`, grid satu kolom sebelum
  breakpoint 768px, target sentuh minimal 44px, dan kontrol fixed memakai safe area.
- Nama, alamat, label tombol, dan detail hadiah panjang dapat membungkus tanpa
  menyebabkan overflow horizontal.
- Cover mengunci fokus dari konten di belakang dan memindahkan fokus ke konten
  utama setelah undangan dibuka.
- Focus indicator, reduced motion, label form, alt text, lazy image, lazy map,
  dialog native, dan kontrol musik berbasis interaksi tersedia.
- Blade melakukan escaping output; URL eksternal dibatasi ke HTTP/HTTPS; template
  asing ditolak; CSRF, honeypot, rate limit, validasi upload, dan token tamu opaque aktif.
- Error RSVP dan buku ucapan memakai error bag terpisah dan tampil di form asal.
- Fixture lengkap, sparse, Unicode, data berbahaya, lifecycle publikasi, RSVP,
  guestbook, kalender, peta, preview, dan admin tercakup automated test.
- Build Vite production dan cache Laravel production berhasil.

## Verifikasi manual saat domain tersedia

- Inspeksi visual browser nyata pada 360px, 390px, 768px, dan 1280px.
- Safari/iOS autoplay policy, Web Share, clipboard, dan safe-area perangkat notch.
- Restore backup hosting serta layanan eksternal Google Maps/Calendar/WhatsApp.

Poin manual tersebut bergantung pada browser/perangkat atau hosting production dan
masuk dalam checklist end-to-end deployment.
