# Panduan Deployment JALL Invitation

Panduan mandiri untuk shared hosting/cPanel dengan PHP 8.2+, MySQL atau
MariaDB, SSL, Composer, dan Terminal/SSH.

## Checklist sebelum mulai

- Domain atau subdomain sudah dibuat dan SSL aktif.
- PHP minimal 8.2 dengan extension `bcmath`, `ctype`, `curl`, `dom`,
  `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, dan `xml`.
- Database, username database, dan password database sudah dibuat.
- Backup file `.env`, database, dan `storage/app/public` jika ini update.
- Jalankan dari komputer lokal:

```bash
composer install
npm install
vendor/bin/pint --test
php artisan test
npm run build
```

Folder `public/build` wajib ikut diunggah. Node.js tidak diperlukan di server
jika asset sudah dibangun di komputer.

## Struktur folder yang disarankan

Simpan aplikasi di luar folder web publik:

```text
/home/USERNAME/apps/jall-invitation/   <- seluruh aplikasi Laravel
/home/USERNAME/apps/jall-invitation/public/ <- document root domain
```

Di cPanel buka **Domains**, pilih domain/subdomain, lalu ubah Document Root ke:

```text
/home/USERNAME/apps/jall-invitation/public
```

Jangan menjadikan root proyek Laravel sebagai document root karena `.env`,
source code, dan file private dapat terekspos.

Jika provider tidak mengizinkan perubahan document root, hubungi provider.
Memindahkan isi folder `public` secara manual ke `public_html` membutuhkan
perubahan `index.php` dan lebih mudah salah, sehingga bukan pilihan utama.

## Upload aplikasi

Upload seluruh proyek kecuali file development berikut:

```text
.env
node_modules/
tests/
.git/
storage/logs/*.log
```

`vendor/` boleh dibuat langsung di server dengan Composer. Jika hosting tidak
memiliki Composer, jalankan `composer install --no-dev --optimize-autoloader`
di komputer menggunakan versi PHP yang sama, lalu unggah folder `vendor`.

## Konfigurasi environment

Salin `.env.production.example` menjadi `.env` di server, kemudian isi:

```dotenv
APP_NAME="JALL Invitation"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://domain-kamu.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database
```

Catatan penting:

- Jangan memakai `.env` lokal pada server.
- Jangan membagikan atau memasukkan `.env` ke repository.
- `APP_URL` harus memakai domain final dan HTTPS tanpa garis miring di akhir.
- Jalankan `key:generate` hanya saat instalasi pertama. Jangan mengganti
  `APP_KEY` pada update karena session dan data terenkripsi dapat rusak.

## Instalasi pertama

Dari root aplikasi:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan filament:assets
php artisan optimize
```

Jangan menjalankan `php artisan db:seed` di production. Seeder development
memakai password awal yang tidak aman. Buat akun admin secara interaktif:

```bash
php artisan make:filament-user
```

Gunakan email dan password kuat milikmu sendiri.

## Permission folder

Proses PHP harus dapat menulis ke:

```text
storage/
bootstrap/cache/
```

Umumnya permission folder `755` sudah cukup pada shared hosting. Gunakan `775`
hanya jika user web server memang berbeda. Jangan memakai `777`.

Pastikan `public/storage` merupakan symlink menuju `storage/app/public`:

```bash
php artisan storage:link
```

Tes dengan mengunggah satu foto dari admin, lalu buka URL fotonya. Jika gambar
rusak, periksa symlink, `APP_URL`, dan permission `storage/app/public`.

## Cron dan queue

Tambahkan cron berikut setiap menit melalui cPanel **Cron Jobs**. Sesuaikan path
PHP dan username hosting:

```cron
* * * * * cd /home/USERNAME/apps/jall-invitation && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Aplikasi saat ini tidak memiliki proses queue wajib. Jika nanti memakai email
atau job asynchronous, tambahkan worker sesuai fasilitas hosting; jangan
menjalankan worker permanen jika provider tidak mendukungnya.

## Backup

Aktifkan dari panel hosting:

- Backup database setiap hari.
- Backup `storage/app/public` setiap hari.
- Simpan tujuh backup harian dan minimal satu backup bulanan.
- Simpan satu salinan di luar akun hosting.
- Lakukan tes restore sebelum menerima data pelanggan nyata.

File aplikasi dapat diunggah ulang. Database dan upload pelanggan tidak dapat
dibangun ulang, jadi keduanya wajib masuk backup.

## Verifikasi setelah deployment

Jalankan:

```bash
php artisan about --only=environment
php artisan migrate:status
php artisan route:list
```

Kemudian periksa manual:

1. `https://DOMAIN/up` memberikan status 200.
2. `/admin` dapat login.
3. Buat atau edit satu undangan dan upload foto.
4. Preview Elegant Rose dan Midnight Ledger.
5. Publish undangan dan buka URL publik dari mode incognito.
6. Uji link personal tamu, RSVP, buku ucapan, musik, peta, kalender, WhatsApp,
   livestream, dan ekspor CSV.
7. Pastikan halaman error tidak menampilkan stack trace.
8. Periksa tampilan pada ponsel dan desktop.

## Cara melakukan update

Backup database dan upload terlebih dahulu, lalu:

```bash
php artisan down --retry=60
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan filament:assets
php artisan optimize
php artisan up
```

Unggah `public/build` terbaru sebelum menjalankan rangkaian tersebut. Jika ada
perintah gagal, perbaiki penyebabnya sebelum menjalankan `artisan up`.

## Troubleshooting

### Tampilan admin tanpa CSS

```bash
php artisan filament:assets
php artisan optimize:clear
php artisan optimize
```

Pastikan folder `public/css/filament` dan `public/js/filament` ikut tersedia.

### Foto berhasil di-upload tetapi tidak tampil

```bash
php artisan storage:link
php artisan optimize:clear
```

Pastikan file berada di `storage/app/public/invitations`, URL memakai
`https://DOMAIN/storage/...`, dan `APP_URL` sudah benar.

### Error 500 setelah upload atau update

Lihat log tanpa membagikan isi `.env`:

```bash
tail -n 100 storage/logs/laravel.log
php artisan optimize:clear
```

Periksa versi PHP, extension PHP, permission, kredensial database, dan apakah
`public/build/manifest.json` tersedia.

### Perubahan `.env` tidak terbaca

```bash
php artisan optimize:clear
php artisan optimize
```

### Migration gagal

Jangan menghapus tabel atau menjalankan `migrate:fresh` di production. Pulihkan
backup jika data berubah sebagian, perbaiki error, lalu jalankan kembali:

```bash
php artisan migrate --force
```
