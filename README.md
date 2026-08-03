# JALL Invitation

Platform jasa pembuatan undangan digital berbasis web. Satu aplikasi untuk banyak pelanggan dan banyak undangan dengan sistem multi-template.

## Tentang

**JALL Invitation** adalah platform undangan digital yang memungkinkan admin mengelola pelanggan, membuat undangan, dan menerbitkan undangan dengan URL unik per pelanggan. Setiap undangan dapat menggunakan template yang berbeda tanpa input ulang data.

## Stack Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 12 |
| Admin Panel | Filament 4 |
| Database | MariaDB 10.4 |
| Frontend | Blade, Tailwind CSS, Alpine.js |
| Interaktivitas | Livewire 3 (via Filament) |
| Build Tool | Vite |
| Runtime | PHP 8.2, Node.js 24 |

## Fitur Utama

- **Multi-template:** Elegant Rose dan Midnight Ledger memakai satu data undangan
- **Pengelolaan pelanggan** melalui panel admin
- **Undangan lengkap:** Cover personal, profil pasangan, jadwal acara, countdown, kalender, peta, galeri, love story, RSVP, buku ucapan, amplop digital, kontak keluarga, livestream
- **Link personal per tamu** dengan token unik
- **Impor daftar tamu** via CSV
- **Moderasi buku ucapan**
- **Dashboard RSVP** dengan ekspor data
- **Status lifecycle:** draft → preview → published → expired → archived
- **Masa aktif undangan** otomatis
- **Mobile-first responsive design**
- **Shared hosting ready**

## Dokumentasi

| Dokumen | Deskripsi |
|---------|-----------|
| [docs/PRD.md](docs/PRD.md) | Product Requirements Document |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Arsitektur & keputusan teknis |
| [docs/DATABASE.md](docs/DATABASE.md) | Rancangan database |
| [docs/IMPLEMENTATION_PHASES.md](docs/IMPLEMENTATION_PHASES.md) | Fase implementasi |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Deployment ke shared hosting |
| [docs/QUALITY_AUDIT.md](docs/QUALITY_AUDIT.md) | Hasil audit kualitas Fase 10 |
| [PROJECT_STATUS.md](PROJECT_STATUS.md) | Status proyek saat ini |
| [SESSION_HANDOVER.md](SESSION_HANDOVER.md) | Handover antar sesi |
| [CHANGELOG.md](CHANGELOG.md) | Catatan perubahan |

## Persyaratan Sistem

- PHP ≥ 8.2 dengan extensions: bcmath, ctype, curl, dom, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml
- MariaDB ≥ 10.4 atau MySQL ≥ 8.0
- Composer ≥ 2.7
- Node.js ≥ 18, npm ≥ 9
- XAMPP (untuk development lokal)

## Instalasi

```bash
# 1. Clone repository
git clone <repo-url> wedding_app_jall
cd wedding_app_jall

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Setup database
# Buat database 'jall_invitation' di MariaDB
php artisan migrate --seed

# 5. Build assets
npm run build

# 6. Jalankan development server
php artisan serve
npm run dev
```

## Struktur Proyek

```
wedding_app_jall/
├── .agents/                    # Skill files (jangan dihapus)
├── app/
│   ├── Enums/                  # Status, tipe, dll
│   ├── Filament/               # Admin panel resources
│   ├── Http/Controllers/       # Public controllers
│   ├── Models/                 # Eloquent models
│   ├── Services/               # Business logic
│   └── ViewModels/             # Presentation data
├── docs/                       # Dokumentasi teknis
├── resources/
│   ├── views/                  # Blade views
│   └── invitation-templates/   # Template undangan
├── storage/                    # File uploads
└── tests/                      # Test suites
```

## Lisensi

Proprietary — Hak cipta dilindungi.
