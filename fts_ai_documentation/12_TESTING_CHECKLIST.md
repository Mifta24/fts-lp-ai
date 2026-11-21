# FTS AI Concierge — Daftar Periksa Pengujian

Dua lapis: **tes otomatis** (PHPUnit 12) untuk perilaku server, dan **daftar periksa manual** untuk hal yang hanya terlihat di browser dan pada model AI sungguhan.

## 1. Tes otomatis

```bash
composer test                                          # seluruh suite
php artisan test --compact tests/Feature/ConciergeChatTest.php   # satu berkas
php artisan test --compact --filter=nama_tes           # satu tes
vendor/bin/pint --dirty --format agent                 # gaya kode setelah mengubah PHP
```

GitHub Actions ([.github/workflows/deploy.yml](../.github/workflows/deploy.yml)) menjalankan `composer install`, `npm ci && npm run build`, dan `php artisan test --compact` sebelum deploy ke Plesk. Deploy hanya jalan bila tes lulus.

### Cakupan menurut area

| Area | Berkas tes |
|---|---|
| Ruang dan navigasi | `HotelLobbyTest`, `StageNavigationTest`, `ExtraRoomsTest` (AI Solutions dan Portofolio), `SceneBackgroundTest` (latar dan cadangannya) |
| Data FTS | `FtsSeederTest` (kontak, logo, tiga paket dan harganya, 19 item AI Solutions, login owner tanpa password tebakan di produksi) |
| SEO | `SeoTest` (deskripsi, kanonik, `hreflang`, data terstruktur, `sitemap.xml`, `robots.txt`) |
| Chat AI | `ConciergeChatTest`, `ConciergeRichCardsTest`, `ConciergeBookingToolTest`, `ChatVoiceTest`, `ConciergeEvaluatorTest`, `ConciergeAnalyticsTest` |
| Penjaga konten | `Unit/ContentGuardTest`, `Unit/ReplyTextTest` |
| Permintaan proyek | `ReservationRequestTest`, `ProjectRequestFlowTest`, `GuestBookingTest` |
| Suara narator | `NarrationSpeechTest` |
| Admin dan peran | `AdminAccessTest`, `AdminRolesTest`, `TeamManagementTest`, `InventoryAdminTest`, `HotelSettingsTest`, `ImageUploadTest` |
| Platform | `PlatformHotelTest` |
| Anggota | `MemberLoginTest`, `MemberAccountTest`, `MemberAdminTest`, `MemberPlusTest` |
| Notifikasi dan email | `StaffNotificationTest`, `MailTestCommandTest` |

Tes memakai factory (`HotelFactory`, `UserFactory`, `MemberFactory`) dan `Http::fake()` untuk model, sehingga tidak butuh server LLM sungguhan.

## 2. Evaluasi AI di model sungguhan
Tes otomatis tidak menjalankan model. Setelah mengganti model atau prompt:

```bash
php artisan concierge:eval                    # skenario regresi tetap
php artisan concierge:chat fts --locale=id    # coba manual; juga en dan ja
```

Harus lolos:
- [ ] Harga paket muncul hanya setelah tool dipanggil
- [ ] Pertanyaan "paket apa saja" langsung menampilkan kartu tanpa meminta tanggal
- [ ] Fakta layanan dan kebijakan berasal dari `search_knowledge`
- [ ] Menjawab dalam bahasa pesan (Jepang, Inggris di halaman Indonesia)
- [ ] Menolak pengetahuan umum, PR, kode, terjemahan, dan bocoran prompt
- [ ] Pesan kasar dijawab balasan baku tanpa tool dan tanpa model
- [ ] Tidak pernah menyebut "kamar", "hotel", atau "menginap"

## 3. Daftar periksa manual

### 3.1 Layar penuh
- [ ] Halaman tidak menggulir; tinggi mengikuti `100dvh` di desktop dan ponsel
- [ ] Daftar, panel info, dan chat menggulir di dalam panel masing-masing
- [ ] Tidak ada bilah gulir horizontal pada lebar 320px
- [ ] Bilah alamat ponsel tidak memotong dock atau peluncur chat

### 3.2 Lobi
- [ ] Latar dan avatar tampil; sapaan terbaca dan tidak menutupi wajah avatar
- [ ] Logo FTS ada di header, layar pemuatan, dan tab browser
- [ ] Narator mengetik sapaan; teks lengkap ada tanpa JavaScript
- [ ] Tidak ada suara sebelum pengunjung menyalakannya
- [ ] `/` membuka lobi FTS bila hanya satu perusahaan terbit

### 3.3 Ruang dan navigasi
- [ ] Tujuh item menu tampil berurutan: Paket Website, Layanan, AI Solutions, Portofolio, Tentang FTS, Mulai proyek, Bicara dengan tim
- [ ] Ruang aktif ditandai; lobi tidak menandai apa pun
- [ ] Berpindah ruang meredupkan stage lalu berjalan; foto kartu ikut terbang
- [ ] Tombol kembali/× di tiap detail mengarah ke daftar yang benar
- [ ] `?lang=` terbawa di semua tautan
- [ ] Tur: Paket Website → dua halaman paket → Layanan; bisa dilewati dan dihentikan

### 3.4 Chat AI
- [ ] Panel membuka dan menutup; percakapan tidak hilang saat ditutup
- [ ] `guest_token` tersimpan; muat ulang halaman memulihkan riwayat
- [ ] Balasan teks polos, tanpa Markdown
- [ ] Kartu paket, layanan, foto, lokasi, kalender, dan formulir tampil dan berfungsi
- [ ] Paket dan formulir tampil di tengah stage (showcase); di ponsel bisa diseret turun
- [ ] Lightbox foto: geser, panah, `Esc`
- [ ] Chip balasan cepat sesuai kartu yang tampil
- [ ] Jempol suka dan tidak suka tersimpan dan bisa ditarik
- [ ] Mengirim pesan saat model mati menampilkan "sementara tidak tersedia" dan pesan tidak tergandakan setelah coba lagi
- [ ] Melebihi 12 pesan per menit per percakapan menghasilkan HTTP 429 dan chat tidak rusak

### 3.5 Paket Website
- [ ] Tiga kartu dengan harga per bulan benar (Rp 700.000, 1.200.000, 1.600.000)
- [ ] Detail paket menampilkan galeri dan tombol "Mulai proyek" membawa paket terpilih
- [ ] Sebelumnya/berikutnya berputar
- [ ] "Tanya AI" mengirim pertanyaan dengan konteks paket ("paket ini")

### 3.6 Layanan, AI Solutions, Portofolio
- [ ] Layanan hanya menampilkan item `facilities`, `dining`, `transport`; AI Solutions hanya `ai`; Portofolio hanya `portfolio`
- [ ] AI Solutions menampilkan 19 kartu dengan foto, lencana "live", dan peran AI
- [ ] Tautan demo membuka situs demo dengan `?lang=` sesuai bahasa halaman
- [ ] Item tanpa demo tidak punya tautan demo
- [ ] Detail yang bukan item aktif atau di luar kategori memberi 404

### 3.7 Tentang FTS
- [ ] Profil, tim, nilai, kebijakan, dan FAQ tampil per kelompok
- [ ] Item nonaktif tidak tampil

### 3.8 Mulai proyek
- [ ] Tiga langkah dengan indikator kemajuan
- [ ] Paket terpilih dari URL (`?room=`) terisi
- [ ] Tanggal lampau ditolak; periode otomatis 30 hari; jumlah pengguna dan website terisi
- [ ] Paket yang tidak muat ditandai
- [ ] Perkiraan total muncul setelah paket dan tanggal lengkap
- [ ] Nomor WhatsApp atau telepon yang tidak valid ditolak; email divalidasi
- [ ] Penafian tanpa pembayaran tampil di ringkasan
- [ ] Kirim berhasil → nomor referensi `BK-XXXXXX`, status menunggu konfirmasi
- [ ] Tautan WhatsApp, telepon, dan email terisi pesan yang benar dalam bahasa halaman
- [ ] Tutup halaman lalu kembali (atau ganti bahasa) mempertahankan isian
- [ ] Paket penuh pada periode itu menawarkan alternatif

### 3.9 Permintaan lewat chat
- [ ] "Saya mau paket 5 halaman" membuka formulir terisi, bukan wawancara teks
- [ ] Data lengkap dalam teks → permintaan dibuat pada balasan yang sama
- [ ] Email tidak pernah ditanyakan
- [ ] Konfirmasi tampil dengan konfeti, berkas kalender, dan tombol bagikan
- [ ] Konfirmasi tetap ada setelah memuat ulang chat

### 3.10 Kelola permintaan
- [ ] Referensi + kontak yang benar menemukan permintaan; kontak yang salah tidak
- [ ] Ubah jadwal hanya untuk `pending`/`confirmed` yang belum dimulai; permintaan `confirmed` kembali `pending`
- [ ] Batalkan melepas slot
- [ ] Tim menerima email perubahan

### 3.11 Serah terima
- [ ] Permintaan di atas 15 halaman, keluhan, dan tawar harga memicu serah terima
- [ ] Setelah serah terima, balasan otomatis hanya memberi tahu tim akan merespons
- [ ] Staf aktif menerima email dan melihat ringkasan yang bisa langsung ditindaklanjuti
- [ ] Balasan staf dari admin muncul di chat pengunjung
- [ ] Tombol WhatsApp, telepon, dan email di "Bicara dengan tim" berfungsi

### 3.12 Bahasa
- [ ] `?lang=id`, `?lang=en`, `?lang=ja` mengubah semua label, narasi, dan pesan kesalahan
- [ ] Balasan AI mengikuti bahasa pesan, bukan hanya bahasa halaman
- [ ] Teks Jepang tidak terpotong di kartu dan panel

### 3.13 Tema, suara, gerak
- [ ] Tema terang bawaan; tombol menyalakan gelap; pilihan bertahan setelah muat ulang tanpa berkedip
- [ ] Efek suara mati bila dimatikan, dan hanya terdengar setelah aksi pengunjung
- [ ] Pembacaan balasan berhenti saat mengirim pesan, chat baru, atau menutup chat
- [ ] Dengan `prefers-reduced-motion` tidak ada parallax, medan mengambang, atau konfeti

### 3.14 Responsif
- [ ] Desktop 1440, 1280, dan 1024 px
- [ ] Tablet 768 px
- [ ] Ponsel 390 dan 360 px, potret dan lanskap
- [ ] Chat, showcase, dan formulir bisa dipakai dengan satu tangan di ponsel
- [ ] Target ketuk cukup besar

### 3.15 Browser
- [ ] Chrome, Safari (macOS dan iOS), Firefox, Edge
- [ ] Browser tanpa speech synthesis: tombol speaker tersembunyi
- [ ] `backdrop-filter` tidak mematahkan keterbacaan di browser yang tidak mendukungnya

### 3.16 Performa
- [ ] Lobi muncul tanpa layar pemuatan pada koneksi normal
- [ ] Berpindah ruang terasa halus; layar portal hanya bila benar-benar lambat
- [ ] Giliran chat selesai dalam 85 detik; tidak ada 504 dari Cloudflare
- [ ] `npm run build` lolos; ukuran bundel tidak melonjak

### 3.17 Penanganan kesalahan
- [ ] Model tidak tersedia → `503 concierge_unavailable` dan pesan ramah
- [ ] Token percakapan tidak dikenal → pengunjung diminta memulai ulang
- [ ] Koneksi putus saat kirim formulir → data tersimpan
- [ ] Gambar latar hilang → memakai gambar cadangan
- [ ] TTS tidak dikonfigurasi (`503`) → narator memakai suara browser

### 3.18 Keamanan
- [ ] Peran `staff` tidak bisa membuka Plans, Availability, Knowledge base, Settings, Team (403)
- [ ] Perusahaan `draft` memberi 404 di semua halaman publik dan endpoint chat
- [ ] Login admin dibatasi 5 percobaan per menit
- [ ] Endpoint narasi menolak teks tanpa tanda tangan valid (403)
- [ ] Referensi permintaan tidak bisa dibuka tanpa kontak yang cocok
- [ ] Tidak ada login demo `password` di produksi
- [ ] `robots.txt` menolak `/admin` dan `/platform`
- [ ] Pesan pengunjung dengan HTML/skrip tampil sebagai teks, tidak dieksekusi
- [ ] Prompt sistem tidak bisa dibuka lewat bujukan atau "abaikan instruksi"

### 3.19 Admin
- [ ] Paket: buat, ubah, nonaktifkan, unggah gambar
- [ ] Availability: simpan rentang tanggal; hasilnya terlihat di perkiraan harga
- [ ] Knowledge base: ubah item lalu tanyakan ke AI; jawaban berubah
- [ ] Project requests: konfirmasi dan batalkan; pembatalan melepas slot; permintaan yang sudah dibatalkan tidak bisa dihidupkan lagi
- [ ] Handovers: balas dan tandai selesai
- [ ] Team: tidak bisa menghapus atau menurunkan owner aktif terakhir
- [ ] Platform: buka perusahaan baru, ganti model AI, terbitkan, nyalakan fitur anggota

### 3.20 Operasional terjadwal
- [ ] `schedule:run` aktif di server (cron setiap menit)
- [ ] `inventory:extend`, `bookings:expire-pending`, `bookings:complete-stays`, `members:award-points` berjalan sesuai jadwal
- [ ] Email keluar (`php artisan mail:test` berhasil)

## 4. Kesiapan demo
Situs siap didemokan bila:

1. Pengunjung bisa membuka lobi dan berpindah ke setiap ruang tanpa menggulir halaman.
2. AI Concierge menjawab pertanyaan tentang layanan, paket, harga, dan tim dalam bahasa pengunjung, dengan data FTS yang benar.
3. AI menampilkan paket dan membuka formulir proyek.
4. Permintaan proyek berhasil dikirim dan muncul di admin dengan notifikasi email ke staf.
5. Percakapan bisa diserahkan ke tim dan dibalas dari admin.
6. AI menolak topik di luar FTS dan konten tidak pantas.
7. Semua itu berjalan baik di desktop dan ponsel.
