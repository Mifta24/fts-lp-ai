# FTS AI Concierge — Daftar Ruang dan Aset

Panduan langkah demi langkah untuk mengganti gambar ada di [docs/stage-images.md](../docs/stage-images.md), dan kredit foto stok di [docs/image-credits.md](../docs/image-credits.md). Dokumen ini hanya memetakan ruang ke aset dan aturan teknisnya.

Semua gambar ada di [public/images/](../public/images). Tiap ruang membaca file dengan **nama tetap**, jadi mengganti gambar cukup dengan menimpa file bernama sama; tidak perlu mengubah kode dan tidak perlu build ulang (muat ulang halaman). Pemetaan ruang ke file ada di `sceneBackdrop()` di [HotelPageController](../app/Http/Controllers/HotelPageController.php).

## 1. Ruang

Setiap ruang terdiri dari **latar polos** + **potongan avatar** transparan yang dilapis di depan. Bila file latar ruang hilang, ruang jatuh ke **gambar cadangan** (satu gambar dengan avatar di dalamnya).

| Ruang | Rute | Latar | Avatar | Cadangan |
|---|---|---|---|---|
| Lobi | `hotel.show` | `lobby-bg.webp` (lobi kantor FTS) | `character right hand.webp` | `concierge-lobby.webp` |
| Paket Website | `hotel.rooms`, `hotel.room` | `rooms-bg.webp` (foto stok: meja kayu dan tanaman) | `character right hand.webp` (dicerminkan) | `suite.webp` |
| Layanan | `hotel.facilities` | `facilities-bg.webp` (lounge layanan FTS) | `character right hand.webp` (dicerminkan) | `facility.webp` |
| AI Solutions | `hotel.solutions` | `solutions-bg.webp` (ruang AI Solutions FTS) | `character right hand.webp` (dicerminkan) | `facility.webp` |
| Portofolio | `hotel.portfolio` | `portfolio-bg.webp` (dinding portofolio FTS) | `character right hand.webp` (dicerminkan) | `facility.webp` |
| Detail layanan / solusi / portofolio | `hotel.facility` | Mengikuti ruang daftarnya | Mengikuti ruang daftarnya | |
| Tentang FTS | `hotel.info` | `about-bg.webp` (ruang rapat FTS) | `character greeting.webp` | `information.webp` |
| Bicara dengan tim | `hotel.staff` | `staff-bg.webp` (foto stok: meja resepsionis) | `character grateful.webp` | `talk to staff.webp` |
| Mulai proyek | `hotel.reservation` | `consultation-bg.webp` (foto stok: ruang rapat kaca) | `character right hand.webp` (dicerminkan) | `reservation.webp` |

Catatan:
- Berkas dengan spasi di nama (`talk to staff.webp`, `character right hand.webp`) memang begitu di kode; jangan diganti nama.
- Berkas `.png` dengan nama sama dengan cadangan `.webp` adalah sumber awal berukuran besar dan tidak dimuat halaman.
- Ruang Paket Website punya konfigurasi `upgrade` (latar dan avatar dicerminkan) yang dipakai begitu `rooms-bg.webp` terpasang.

## 2. Pengaturan per ruang di `sceneBackdrop()`
Hanya diubah bila avatar baru terpotong atau terlalu kecil.

| Kunci | Fungsi |
|---|---|
| `focus`, `focusMobile` | Titik fokus latar di desktop dan ponsel |
| `anchor` | Sisi avatar (`left`/`right`) |
| `flip` | Mencerminkan avatar agar menunjuk ke panel |
| `text` | Letak judul (`top`/`bottom`) |
| `avatarZoom`, `avatarFocus` | Perbesaran dan posisi wajah untuk avatar kecil di chat |

## 3. Avatar AI Concierge
- Karakter: wanita berambut bob dengan blazer navy, dibuat dengan Figma AI dalam satu lembar karakter agar ketiga pose sama persis.
- Tiga pose di `public/images/character/`:
  - `character right hand.webp`: menunjuk ke samping (lobi, daftar, mulai proyek)
  - `character greeting.webp`: melambai (Tentang FTS)
  - `character grateful.webp`: tangan tertangkup (Bicara dengan tim)
- Berkas sumber ada di berkas Figma "FTS Avatar".
- Potongan avatar sebaiknya rapat (tanpa ruang kosong di atas kepala), dengan tubuh terpotong di tepi bawah.
- Avatar kecil di chat memotong wajah dari potongan avatar (atau dari latar bila tidak ada potongan).

## 4. Merek
| Aset | Berkas | Dipakai di |
|---|---|---|
| Logo lengkap | `public/images/logo.webp` | Cadangan data terstruktur dan materi lain |
| Ikon logo (gunung dan matahari) | `public/images/fts-mark.png` | Header, layar pemuatan, tab browser |
| Ikon 64 px | `public/images/fts-icon-64.png` | Versi kecil dari ikon logo (belum dirujuk kode) |

`Hotel.logo_path` (seeder mengisi `images/fts-mark.png`) menentukan logo yang dipakai. Logo bisa diganti lewat **Company settings**; unggahan memakai disk `public`. Ikon pengganti: PNG persegi berlatar putih.

## 5. Gambar konten
| Konten | Sumber |
|---|---|
| Paket website | Satu foto Unsplash per paket (disimpan sebagai `image_url` di `room_images`) |
| Layanan dan portofolio | URL gambar dari fts-tech.co.id atau Unsplash (kolom `image_url`) |
| AI Solutions (19 item) | Tangkapan layar tiap demo di `public/images/demos/{subdomain}.webp` (16 demo + Thai Travel Booking dan dua produk lain). Dipotret ulang dari situs langsungnya |

Pada seeder, nama berkas demo mengikuti **subdomain** URL demo, mis. `https://spa-ai.fts-tech.co.id/` → `spa-ai.webp`.

## 6. Ukuran dan format
- **Latar**: 16:9 landscape, kira-kira 2400 × 1350 px, WebP atau PNG, diusahakan di bawah 400 KB. Sisakan area kosong di kiri bawah (tempat judul dan bar chat) dan jangan menaruh detail penting di bagian paling bawah (tertutup chip).
- **Potongan avatar**: PNG atau WebP dengan latar transparan, potret kira-kira 1024 × 1536 px, badan sampai pinggang, wajah di sepertiga atas.
- **Foto konten**: unggahan admin diperkecil ke lebar maks. 1600 px; `jpeg`, `png`, `webp`, maks. 8 MB.
- **Foto demo**: WebP; saat ini berukuran sekitar 60–126 KB.

## 7. Suara
- **Efek suara**: tidak ada berkas audio. Disintesis oleh Web Audio API di [resources/js/sound.js](../resources/js/sound.js).
- **Suara narator**: dibuat server lewat OpenRouter TTS (`OPENROUTER_API_KEY`, `OPENROUTER_TTS_MODEL`, `OPENROUTER_TTS_VOICE`), diperlakukan sebagai WAV, dan di-cache di disk per teks dan bahasa. Tanpa konfigurasi, narator memakai speech synthesis browser.
- **Pembacaan balasan chat**: speech synthesis browser.

## 8. Ikon
Ikon tidak berupa berkas gambar: komponen Blade `menu-icon`, `facility-icon` (dipilih dari tag dan judul item), `info-icon`, dan `ai-mark` di [resources/views/components/](../resources/views/components). Font: Space Grotesk dan Inter lewat direktif `@fonts`.

## 9. Performa
- Latar WebP sekitar 200–440 KB; avatar sekitar 40 KB.
- Foto kartu memakai `loading="lazy"`.
- Layar "portal" hanya muncul bila pemuatan lambat.
- Efek gerak dimatikan untuk `prefers-reduced-motion`.
- Pemuatan JavaScript vanilla; tanpa framework. Bundel dibangun Vite 8 dan Tailwind CSS 4 (`npm run build`).

## 10. Daftar periksa aset

### Lobi
- [ ] `lobby-bg.webp` terpasang, judul tidak menutupi avatar
- [ ] `character right hand.webp` tampak benar di desktop dan ponsel
- [ ] `fts-mark.png` tampil di header, layar pemuatan, dan tab

### Ruang lain
- [ ] Latar tiap ruang di §1 ada, ukuran sesuai §6
- [ ] Tiga pose avatar tidak terpotong di wajah
- [ ] Foto stok `rooms-bg`, `consultation-bg`, `staff-bg` tercantum di `docs/image-credits.md`

### Konten
- [ ] Setiap paket punya gambar
- [ ] Setiap item AI Solutions punya tangkapan layar di `public/images/demos/`
- [ ] Setiap item pengetahuan penting punya `image_url`

### Responsif
- [ ] Desktop (≥ 900px) dan ponsel (< 900px) diperiksa di tiap ruang
- [ ] Teks di chat tidak tertutup dock chip
