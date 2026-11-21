# FTS AI Concierge — Konsep Proyek

## 1. Nama dan tujuan
**FTS AI Concierge** adalah situs interaktif satu layar penuh untuk **Fujiyama Technology Solutions (FTS)**, perusahaan IT berakar Jepang di Jakarta Barat. Layanannya: web development, AI automation, AI agent, offshore development, website restoran, dan otomasi bisnis & digital marketing.

Pengunjung tidak menggulir halaman panjang. Mereka "berjalan" dari ruang ke ruang (lobi, paket website, layanan, AI Solutions, portofolio, tentang FTS, mulai proyek, bicara dengan tim) sambil mengobrol dengan **AI Concierge**, avatar wanita berblazer navy yang mewakili tim FTS.

Tujuan bisnisnya: mengubah pengunjung menjadi **permintaan proyek** (project request) dan percakapan dengan tim FTS.

## 2. Asal-usul dan istilah kode
Aplikasi ini diturunkan dari `fts-hotel-ai`. Strukturnya tetap struktur hotel, dan data FTS ditempatkan di tempat yang sama. Semua teks yang dilihat pengunjung (label, narasi, formulir, prompt AI) sudah memakai istilah FTS. Hanya **nama di kode, tabel database, dan sebagian halaman admin bagian dalam** yang masih memakai istilah hotel.

| Istilah di kode / database | Arti di FTS |
|---|---|
| `Hotel` (`hotels`) | Perusahaan (FTS, slug `fts`). Satu aplikasi bisa melayani beberapa perusahaan |
| `RoomType` (`room_types`) | Paket website: 5, 10, atau 15 halaman |
| `RoomInventory` (`room_inventory`) | Slot kapasitas per hari untuk sebuah paket |
| `Booking` (`bookings`) | Permintaan proyek |
| `check_in` / `check_out` | Tanggal mulai / tanggal selesai periode |
| `adults`, `children` | Pengguna, pengguna tambahan |
| `room_count` | Jumlah website |
| `HotelKnowledgeItem` | Item basis pengetahuan: layanan, AI Solutions, portofolio, tim, kebijakan, FAQ, kantor |
| `facilities` | Layanan FTS |
| `guest` | Pengunjung situs |
| `check_in_time` / `check_out_time` | Jam kerja kantor (09:00–18:00) |

Dokumen lain di folder ini memakai istilah FTS di teks, dan menyebut nama kode dalam `backtick` bila perlu.

## 2a. Alur utama

```text
Buka situs
    ↓
Lobi FTS (avatar menyapa, menu ruang, tombol chat)
    ↓
Pilih ruang atau tanya AI Concierge
    ↓
Paket Website / Layanan / AI Solutions / Portofolio / Tentang FTS
    ↓
Mulai proyek (3 langkah)  ──→  nomor referensi + WhatsApp / telepon / email
    ↓
Bicara dengan tim (serah terima ke manusia bila perlu)
```

## 3. Prinsip UX
- **Satu layar penuh**: `.stage` memakai `100dvh` dan `overflow: hidden`. Isi yang panjang (daftar kartu, panel info, chat) menggulir di dalam panelnya sendiri, bukan di halaman.
- **Ruang, bukan halaman**: berpindah ruang memakai transisi (layar "portal" muncul hanya bila pemuatan lambat), bukan memuat halaman web biasa.
- **AI selalu tersedia**: chat bisa dibuka dari ruang mana pun dan tahu ruang yang sedang dilihat pengunjung.
- **Ketukan sebelum ketikan**: kartu, kalender, dan formulir di dalam chat menggantikan pertanyaan teks bila memungkinkan.
- **Tanpa pembayaran**: situs hanya membuat permintaan berstatus `pending`. Lingkup dan biaya final dikonfirmasi tim FTS.

## 4. Ruang (scene)

| Ruang | URL | Isi |
|---|---|---|
| Lobi | `/` atau `/fts` | Sapaan, menu ruang, chat. Latar: lobi kantor FTS |
| Paket Website | `/fts/rooms`, `/fts/rooms/{slug}` | Tiga paket bulanan, detail per paket |
| Layanan | `/fts/facilities`, `/fts/facilities/{id}` | Enam layanan FTS (dan item kantor/transport) |
| AI Solutions | `/fts/solutions` | 16 demo langsung dan 3 produk langsung dari ai-solutions.fts-tech.co.id |
| Portofolio | `/fts/portfolio` | 12 proyek dari fts-tech.co.id/projects/all |
| Tentang FTS | `/fts/info` | Profil, tim, nilai, kebijakan, FAQ |
| Mulai proyek | `/fts/reservation` | Formulir 3 langkah |
| Bicara dengan tim | `/fts/staff` | Tombol WhatsApp, telepon, email, dan serah terima |
| Kelola permintaan | `/fts/booking` | Cari permintaan lewat referensi + kontak, ubah jadwal atau batalkan |
| Akun anggota | `/fts/member` | Hanya bila fitur anggota dinyalakan |

Detail ruang ada di [03_UI_UX_SPEC.md](03_UI_UX_SPEC.md) dan [11_SCENE_AND_ASSET_LIST.md](11_SCENE_AND_ASSET_LIST.md).

## 5. Peran AI Concierge
AI Concierge adalah anggota tim FTS yang berpengetahuan. Ia:
- menyambut pengunjung dan menjawab dalam bahasa mereka (Indonesia, Inggris, Jepang);
- menjawab fakta perusahaan **hanya dari basis pengetahuan** lewat tool `search_knowledge`;
- menampilkan paket, harga, dan ketersediaan **hanya dari tool**, tidak dari ingatan model;
- membantu membuat permintaan proyek (formulir di chat atau lewat teks);
- menyerahkan percakapan ke tim bila perlu (proyek khusus di atas 15 halaman, keluhan, negosiasi harga, masalah pembayaran, pertanyaan yang tidak bisa dijawab);
- menolak topik di luar FTS dan konten kasar, seksual, atau ilegal.

Aturan lengkap: [04_AI_BEHAVIOR.md](04_AI_BEHAVIOR.md) dan [10_PROMPT_SYSTEM.md](10_PROMPT_SYSTEM.md).

## 6. Bahasa
- **Indonesia** (`id`, bawaan), **Inggris** (`en`), **Jepang** (`ja`).
- Bahasa halaman dipilih lewat `?lang=`; bahasa balasan AI mengikuti bahasa pesan terakhir pengunjung.

## 7. Cakupan saat ini

### Sudah ada
- Delapan ruang bertema stage dengan narator (teks berketik, suara dari server dengan cadangan suara browser) dan efek suara yang bisa dimatikan.
- Tema terang (bawaan) dan gelap lewat tombol di header.
- Chat AI dengan 14 tool, kartu di dalam chat, pemilih tanggal, formulir proyek di chat, tur berpemandu, dan jempol suka/tidak suka.
- Formulir "Mulai proyek" 3 langkah, kuota harian, nomor referensi, tautan WhatsApp/telepon/email.
- Kelola permintaan oleh pengunjung (ubah jadwal, batalkan) dan akun anggota dengan poin dan tingkatan (fitur opsional per perusahaan).
- Panel admin (`/admin`) dan panel platform (`/platform`) untuk banyak perusahaan, domain khusus, analitik percakapan, jejak aktivitas.
- SEO: deskripsi, kanonik, `hreflang`, data terstruktur Organization, `sitemap.xml`, `robots.txt`.

### Belum ada / di luar cakupan
- Pembayaran langsung, integrasi PMS/OTA, avatar 3D, sinkronisasi bibir real-time.
- Konfirmasi proyek otomatis oleh AI: AI hanya membuat permintaan `pending`.
- Input suara dari pengunjung (hanya balasan yang bisa dibacakan).

## 8. Kriteria berhasil
Pengunjung bisa:
1. Membuka situs dan melihat lobi FTS.
2. Berpindah antar ruang tanpa menggulir halaman panjang.
3. Bertanya ke AI dan mendapat jawaban yang sesuai data FTS, dalam bahasanya.
4. Melihat paket, layanan, AI Solutions, dan portofolio.
5. Mengirim permintaan proyek dan mendapat nomor referensi.
6. Menghubungi tim FTS lewat WhatsApp, telepon, atau email.
7. Melakukan semua itu di desktop maupun ponsel.
