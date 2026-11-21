# FTS AI Concierge — Spesifikasi UI/UX

## 1. Aturan tata letak inti
Seluruh pengalaman tampil di satu viewport tetap. Implementasinya di [resources/css/app.css](../resources/css/app.css):

```css
.stage-page { height: 100dvh; overflow: hidden; }
.stage      { position: relative; height: 100dvh; overflow: hidden; isolation: isolate; }
```

- Tidak ada gulir vertikal pada halaman. Daftar, panel info, dan chat menggulir **di dalam** panelnya.
- Tinggi header `--header: 84px`, lebar kolom chat `--col: clamp(300px, 26vw, 380px)`, tinggi dock `--dock: 156px`.
- Lapisan dari belakang ke depan: latar → avatar (cut-out) → panel/kartu → header → chat → showcase.

## 2. Komponen stage
Blade: [resources/views/components/hotel-stage.blade.php](../resources/views/components/hotel-stage.blade.php), dipakai semua ruang.

| Bagian | Isi |
|---|---|
| Header | Logo FTS (`images/fts-mark.png`) dan nama, menu ruang dengan glider, tombol suara, tombol tema, pemilih bahasa `id`/`en`/`ja` |
| Latar | Foto 16:9 per ruang, dengan parallax halus |
| Avatar | Cut-out AI Concierge berlapis di depan latar |
| Narator | Intro ruang yang diketik, bar suara bergerak saat "berbicara" |
| Panel | Konten khas ruang (kartu paket, daftar, panel info, formulir) |
| Dock | Chip pintasan ke ruang |
| Chat | Peluncur dan panel chat AI |
| Loader | Layar "portal" saat pemuatan benar-benar lambat |

## 3. Ruang dan tata letaknya

### Lobi
Eyebrow nama perusahaan, judul sambutan, satu paragraf intro, avatar, dock, dan peluncur chat. Chat di lobi terdocking di sisi kiri-tengah (`data-chat-dock="lobby"`).

### Paket Website (`rooms`)
Medan kartu kaca mengambang berisi tiga paket. Kartu menampilkan nama, gambar, dan harga per bulan. Ketukan pada kartu menerbangkannya ke depan sebelum stage berjalan ke halaman detail.

### Detail paket (`room`)
Panel di tengah dengan galeri (thumbnail 4:3), deskripsi, harga, tombol **Mulai proyek** (membawa paket terpilih), tombol **Tanya AI**, dan navigasi sebelumnya/berikutnya. Sidebar daftar paket (`room-nav`).

### Layanan, AI Solutions, Portofolio
Daftar memakai tampilan `facilities-index` dan detail memakai `facility-detail`. Medan kartu mengambang dengan efek: kartu terbang masuk, medan bergeser mengikuti penunjuk (setiap kartu punya `--depth` sendiri), dan kartu di bawah kursor miring dengan cahaya mengikuti. Semua efek dimatikan untuk pengunjung yang meminta gerak lebih sedikit.

### Tentang FTS (`info`)
Panel info kaca di kiri-tengah (lebar `min(440px, 40vw)`) berisi item `general`, `policies`, `faq` per kelompok.

### Mulai proyek (`reservation`)
Wizard 3 langkah dengan indikator kemajuan; lihat [06_PROJECT_REQUEST_FLOW.md](06_PROJECT_REQUEST_FLOW.md).

### Bicara dengan tim (`staff`)
Panel kontak dengan tombol yang membuka chat dengan topik tim, tombol WhatsApp, telepon, dan email, tautan ke "Kelola permintaan" (dan akun anggota bila fitur menyala), serta alamat, telepon, dan email kantor.

## 4. Menu dan navigasi
Urutan menu atas (`stageMenu()` di [HotelPageController](../app/Http/Controllers/HotelPageController.php)):

1. Paket Website
2. Layanan
3. AI Solutions
4. Portofolio
5. Tentang FTS
6. Mulai proyek
7. Bicara dengan tim

- Logo membawa kembali ke lobi.
- Semua tautan antar ruang memakai `data-stage-exit`: stage meredup lalu berjalan, dan foto kartu bisa "ikut terbang" ke ruang berikutnya.
- Tautan membawa `?lang=` agar bahasa tidak hilang.

## 5. Chat AI
Dibuat dengan JavaScript vanilla ([resources/js/concierge.js](../resources/js/concierge.js) dan modul `concierge-*`). Semua DOM dibuat dengan node dan `textContent`, bukan string HTML, agar isi tidak bisa menyuntik markup.

- **Peluncur** di bawah; panel muncul dengan transisi `opacity` + `transform`.
- **Balasan teks polos**: tidak ada Markdown. Daftar memakai "• " atau "1. " per baris.
- **Kartu**: rail kartu paket (foto, detail yang membuka di tempat, tombol minta proyek), kartu layanan, galeri foto, menu/isi layanan, kartu lokasi dengan "buka di peta" dan "petunjuk arah", pemilih tanggal, formulir proyek, kartu ketersediaan, konfirmasi, dan pemberitahuan serah terima.
- **Showcase**: paket dan formulir ditampilkan di tengah stage. Satu hal pada satu waktu; formulir yang dibuka dari daftar punya jalan kembali. Saat aktif, transkrip chat disingkirkan dan latar diredupkan. Di ponsel berupa lembar yang menutup dengan diseret.
- **Lightbox foto**: layar penuh, geser atau panah keyboard untuk pindah, `Esc` atau ketuk luar untuk menutup.
- **Chip balasan cepat** di bawah balasan, dengan tepi memudar bila bisa digulir ([chip-row.js](../resources/js/chip-row.js)).
- **Jempol** suka/tidak suka per balasan.
- **Suara**: tombol speaker di header chat menyalakan pembacaan balasan lewat speech synthesis browser. Tombol tersembunyi di browser yang tidak mendukung. Suara berhenti saat pengunjung mengirim pesan, memulai chat baru, atau menutup chat.
- **Perayaan**: konfeti singkat, berkas kalender (`.ics`) untuk tanggal mulai, dan tombol bagikan saat permintaan proyek berhasil.

## 6. Perilaku per ukuran layar
Titik patah utama di CSS adalah **900px**; ada juga 1100px, 760px, dan 599px.

### Desktop (≥ 900px)
Kolom chat di kanan (`right: 24px`), panel konten di tengah/kiri, avatar di sisi berlawanan. Chat di lobi, info, dan beberapa ruang terdocking di tengah atau kiri sesuai `data-chat-dock`.

### Ponsel dan tablet (< 900px)
- Panel info dan konten melebar penuh (`left/right: 12px`) di bawah header.
- Chat menjadi lembar penuh; showcase menjadi lembar yang diseret turun untuk menutup.
- Haptic ringan (getar singkat) pada ketukan di perangkat yang mendukung; di tempat lain senyap.
- Parallax mengikuti kemiringan ponsel, bukan penunjuk.
- Judul dan bar chat disisakan di kiri-bawah latar; detail penting jangan di bagian paling bawah (tertutup chip).

## 7. Tema, suara, dan gerak
- **Tema**: terang adalah bawaan. Tombol di header menyalakan tema gelap; pilihan tersimpan per browser dan diterapkan di `<head>` sebelum halaman dicat agar tidak berkedip. Warna beralih dengan cross-fade singkat.
- **Efek suara** ([sound.js](../resources/js/sound.js)): disintesis dengan Web Audio API (tanpa berkas audio): "whoosh" dengan dua bunyi naik saat pindah ruang, "ding" lembut untuk pesan masuk, ketukan saat mengirim. Hanya berbunyi mengikuti aksi pengunjung, tidak pernah sendiri, dan bisa dimatikan; pilihan tersimpan per browser.
- **Narator**: teks diketik seperti ucapan. Pembacaan suara hanya atas permintaan pengunjung, memakai suara dari server (OpenRouter TTS, di-cache) dengan cadangan speech synthesis browser. Setelah baris selesai, narator mengirim event `narrator:done` yang dipakai tur.
- **Gerak**: `prefers-reduced-motion: reduce` mematikan parallax, medan mengambang, konfeti, dan animasi besar; kontennya tetap ada dan statis.

## 8. Status pemuatan
- **Awal**: layar "Menyiapkan pengalaman FTS…" dengan logo, hanya bila gambar latar belum siap.
- **AI**: indikator "berpikir" di chat.
- **Antar ruang**: stage meredup lalu berjalan; layar portal muncul hanya bila lambat.
- **Formulir**: "Memeriksa ketersediaan…" saat perkiraan harga dihitung, "Mengirim…" saat submit.

## 9. Status kesalahan
- **AI**: "AI Concierge sementara tidak tersedia, coba lagi sebentar." Teks pengunjung tetap di kotak ketik.
- **Formulir**: pesan per kolom (tanggal lampau, selesai sebelum mulai, periode lebih dari 30 hari, paket tidak muat, kontak tidak valid) dalam bahasa halaman. Data tersimpan saat koneksi gagal.
- **Aset**: bila foto latar sebuah ruang hilang, ruang memakai gambar cadangan (lihat [11_SCENE_AND_ASSET_LIST.md](11_SCENE_AND_ASSET_LIST.md)).

## 10. Aksesibilitas
- Teks narator selalu ada di DOM untuk pembaca layar dan tanpa JavaScript.
- `lang` dokumen mengikuti bahasa halaman; setiap tombol bahasa punya `aria-label`.
- Tautan ruang aktif memakai `aria-current`.
- Tur memakai `aria-live="polite"`.
- Lightbox dan panel chat bisa ditutup dengan `Esc`.
- Gerak dikurangi sesuai preferensi sistem.

## 11. Gaya visual
- **Palet**: navy FTS dan biru langit, panel kaca (frosted glass) dengan tepi lembut. Ruang berlatar foto kantor FTS dan foto stok yang disaring serasi.
- **Tipografi**: Space Grotesk untuk judul dan sapaan (`--font-display`), Inter untuk teks panel dan chat (`--font-sans`).
- **Avatar**: wanita berambut bob dan blazer navy, tiga pose (menunjuk, melambai, tangan tertangkup) agar ruang terasa hidup tanpa animasi berat.
- **Nuansa**: kantor teknologi yang hangat dan profesional, bukan template korporat generik.
