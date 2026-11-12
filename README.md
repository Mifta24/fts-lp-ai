# FTS AI Landing Page

Landing page Fujiyama Technology Solutions (FTS) untuk layanan AI automation, AI agent, web development, dan offshore development. Satu halaman dengan hero avatar AI, dalam tiga bahasa (Indonesia, Inggris, Jepang). Tampilannya mengikuti tema [fts-tech.co.id](https://fts-tech.co.id/) dan susunan hero seperti [hotel-ai.fts-tech.co.id](https://hotel-ai.fts-tech.co.id/).

Dibuat dari kode `fts-hotel-ai` yang sudah dipangkas. Semua fitur hotel, admin, booking, member, dan chat AI sudah dihapus. Tidak ada database.

## Teknologi

- PHP 8.3+ (diuji di 8.4) dan Laravel 13
- Vite 8, Tailwind CSS 4, JavaScript vanilla
- PHPUnit untuk sisi server, `node --test` untuk fungsi JavaScript

## Memulai

Butuh PHP 8.3+, Composer, dan Node.js 20.19+ (atau 22.12+).

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan serve
```

Buka http://127.0.0.1:8000. Untuk pengembangan dengan hot reload, jalankan `npm run dev` di terminal lain.

## Bahasa

- `?lang=id`, `?lang=en`, atau `?lang=ja`. Pilihan disimpan di cookie `fts_lang` selama setahun.
- Nilai yang tidak dikenal diabaikan, dan halaman tampil dalam bahasa bawaan (`APP_LOCALE`, default `id`).
- Semua teks ada di `lang/{id,en,ja}/landing.php`. Ketiga file harus punya kunci yang sama (dijaga oleh tes).

## Yang biasa diubah

| Yang diubah | Di mana |
|-------------|---------|
| Nomor WhatsApp, email, telepon, kurs USD | `.env`: `FTS_WHATSAPP`, `FTS_EMAIL`, `FTS_PHONE`, `FTS_USD_RATE` |
| Gambar avatar | `public/images/avatar.webp`. Pakai potret transparan (PNG/WebP) dengan wajah di tengah atas |
| Logo | `public/images/logo.webp` |
| Teks halaman | `lang/*/landing.php` |
| Harga paket | `resources/views/partials/pricing.blade.php` (array `$plans`) |
| Anggota tim | `resources/views/partials/team.blade.php` (array `$members`) dan `lang/*/landing.php` |
| Warna dan font | `resources/css/app.css` (blok `@theme`) |

## Cara kerja singkat

- `routes/web.php` hanya punya satu route (`/`) ke `LandingController`.
- `app/Http/Middleware/SetLocale.php` menentukan bahasa.
- `resources/views/landing.blade.php` menyusun partial di `resources/views/partials/`.
- `resources/js/landing.js` menangani menu mobile, penanda menu aktif saat scroll, toggle IDR/USD, dan form kontak. Form membuka aplikasi email pengunjung lewat `mailto:`, tanpa mengirim data ke server.
- `resources/js/starfield.js` menggambar bintang di hero. Animasi berhenti kalau pengunjung memilih `prefers-reduced-motion`.

## Tes

```bash
php artisan test
npm test
```

CI di `.github/workflows/ci.yml` menjalankan keduanya dan build Vite pada setiap push dan pull request ke `main`.

## Deploy

1. Di server: `composer install --no-dev --optimize-autoloader`, lalu `npm ci && npm run build` (atau unggah folder `public/build` hasil build lokal).
2. Salin `.env.example` menjadi `.env`, isi `APP_KEY` (`php artisan key:generate`), set `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL`.
3. Arahkan document root ke folder `public/`.
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`.

Paket `plesk/ext-laravel-integration` tetap dipasang dari proyek asal supaya mudah di-deploy di Plesk. Hapus dengan `composer remove plesk/ext-laravel-integration` kalau tidak dipakai.

## Dokumen desain

Spec dan rencana kerja ada di `docs/superpowers/`.
