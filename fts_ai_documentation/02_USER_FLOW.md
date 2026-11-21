# FTS AI Concierge — Alur Pengguna

## 1. Alur global

```text
Buka situs
   ↓
Lobi ──→ menu atas: Paket Website · Layanan · AI Solutions · Portofolio · Tentang FTS · Mulai proyek · Bicara dengan tim
   │
   ├─→ Chat AI (dari ruang mana pun)
   │      ├─ kartu paket / layanan / foto / lokasi
   │      ├─ pemilih tanggal
   │      ├─ formulir proyek di chat
   │      └─ serah terima ke tim
   │
   ├─→ Mulai proyek (3 langkah) ──→ referensi ──→ WhatsApp / telepon / email
   │
   └─→ Bicara dengan tim
```

## 2. Pembukaan
1. Pengunjung membuka `/`. Bila hanya ada satu perusahaan terbit, `/` menampilkan lobi perusahaan itu (`/fts`). Bila ada beberapa, `/` menampilkan layar pembuka berisi daftar perusahaan. Bila belum ada, layar pembuka memberi tahu bahwa belum ada yang siap.
2. Layar pemuatan (logo FTS) hanya tampil bila gambar latar belum siap.
3. Lobi tampil dengan latar kantor FTS, avatar, dan narator yang mengetik sapaan.
4. Bahasa halaman mengikuti `?lang=` bila valid, kalau tidak mengikuti bahasa bawaan perusahaan (`id`).
5. Suara tidak pernah menyala sendiri. Pengunjung menyalakan efek suara lewat tombol di header, dan menyalakan pembacaan balasan chat lewat tombol speaker di header chat.

## 3. Lobi
- Narator menyapa. Teks selalu ada di DOM untuk pembaca layar dan tanpa JavaScript.
- Menu atas memuat tujuh ruang. Tautan geser (glider) menandai ruang aktif.
- Dock chip di bagian bawah menautkan ke ruang yang sama dengan menu atas.
- Tombol chat membuka panel AI Concierge.
- Header memuat pemilih bahasa, tombol suara, dan tombol tema terang/gelap.

## 4. Paket Website
1. Daftar tiga kartu paket mengambang (5, 10, 15 halaman) dengan harga per bulan.
2. Narator menyebut jumlah paket dan harga terendah, dibangun dari data tersimpan, tidak dikarang.
3. Membuka kartu membawa ke halaman detail paket: galeri, deskripsi, harga, tombol "Mulai proyek" dengan paket terpilih, tombol sebelumnya/berikutnya (berputar).
4. Tombol "Tanya AI" mengirim pertanyaan awal ke chat dengan paket sebagai konteks ("paket ini").

## 5. Layanan, AI Solutions, Portofolio
Ketiganya memakai satu tampilan daftar dan satu tampilan detail yang sama. Yang membedakan hanya kategori knowledge item:

| Ruang | Kategori |
|---|---|
| Layanan | `facilities`, `dining`, `transport` |
| AI Solutions | `ai` |
| Portofolio | `portfolio` |

1. Daftar berupa medan kartu kaca yang mengambang; kartu terbang masuk saat terlihat, bergeser mengikuti penunjuk, dan miring ke arah kursor.
2. Mengetuk kartu membuka detail: foto/galeri, isi, dan untuk AI Solutions sebuah tautan **demo langsung** beserta peran AI dan tiga fitur utamanya.
3. Tombol sebelumnya/berikutnya berputar dalam daftar yang sama.
4. "Tanya AI" mengirim pertanyaan yang cocok dengan ruangnya.

## 6. Tentang FTS
Satu panel info berisi item kategori `general` (profil, tim, nilai), `policies` (jam kerja, kontrak, proyek di atas 15 halaman), dan `faq`, dikelompokkan per kategori.

## 7. Mulai proyek
Tiga langkah, lengkapnya di [06_PROJECT_REQUEST_FLOW.md](06_PROJECT_REQUEST_FLOW.md):
1. **Paket & mulai**: pilih paket dan tanggal mulai. Periode selalu 30 hari; jumlah pengguna dan website terisi otomatis.
2. **Data Anda**: nama, cara dihubungi (WhatsApp / telepon / email), nomor atau email, catatan opsional.
3. **Ringkasan**: perkiraan total, lalu kirim. Hasilnya nomor referensi dan tombol kirim ke WhatsApp, telepon, atau email.

Kemajuan formulir tersimpan di `sessionStorage` sehingga tidak hilang saat pindah halaman atau ganti bahasa.

## 8. Chat AI
1. Pengunjung membuka panel chat. Saat pertama kali, browser memanggil `POST /{slug}/concierge/start` dan menyimpan `guest_token` di `localStorage` (kunci `concierge_token_{slug}`). Riwayat dimuat ulang lewat `GET /concierge/history`.
2. Setiap pesan dikirim bersama konteks UI: ruang aktif, paket atau layanan yang sedang dilihat, dan draf formulir.
3. AI membalas dengan teks polos dan, bila perlu, **kartu** di dalam chat. Kartu paket dan formulir tampil di tengah stage ("showcase"), bukan di kolom chat sempit. Di ponsel berupa lembar yang bisa diseret turun untuk menutup.
4. Di bawah balasan ada **chip balasan cepat** (mis. "Pilih tanggal", "Termurah", "Lokasi", "Tur").
5. Pengunjung bisa memberi jempol suka atau tidak suka pada balasan.
6. Bila AI membuat permintaan proyek, kartu konfirmasi muncul dengan konfeti, berkas kalender, dan tombol bagikan.

### Tur berpemandu
Pengunjung meminta "antar saya keliling" atau mengetuk chip "Tur". AI memanggil `start_tour`. Tur berjalan: Paket Website → dua halaman paket → Layanan. Tiap ruang menjalankan narasinya, lalu tur lanjut sendiri setelah narasi selesai. Pengunjung bisa melewati atau menghentikan kapan saja. Kemajuan tur disimpan di `sessionStorage`.

## 9. Serah terima ke tim
Pemicu: proyek khusus (mis. lebih dari 15 halaman), keluhan, negosiasi harga, pembatalan tidak biasa, masalah pembayaran, atau AI tidak yakin.

1. AI memanggil `request_human_handover` dengan alasan dan ringkasan untuk rekan yang belum membaca percakapan.
2. Percakapan berstatus `handed_over`; balasan otomatis berikutnya hanya memberi tahu bahwa tim akan merespons.
3. Staf yang aktif di perusahaan menerima notifikasi email.
4. Staf membuka antrean **Handovers** di admin, membalas langsung (pesan berperan `staff`), lalu menandai selesai.
5. Chat pengunjung mengambil balasan staf lewat `history`.

Pengunjung juga bisa langsung ke **Bicara dengan tim** dan memakai tombol WhatsApp, telepon, atau email.

## 10. Kelola permintaan yang sudah dibuat
Di `/fts/booking` pengunjung memasukkan **nomor referensi dan kontak yang dipakai saat meminta** (referensi saja tidak cukup). Selama status `pending` atau `confirmed` dan tanggal mulai belum lewat, mereka bisa **mengubah jadwal** atau **membatalkan**. Permintaan `confirmed` yang jadwalnya diubah kembali ke `pending`. Tim menerima notifikasi perubahan.

## 11. Anggota (opsional)
Bila fitur `member` dinyalakan: login lewat kode sekali pakai yang dikirim ke email, halaman akun dengan riwayat permintaan, dan formulir proyek yang terisi otomatis. Dengan `member_plus`: poin, tingkatan, dan diskon anggota di perkiraan harga.

## 12. Navigasi mundur
- Tombol "kembali" pada detail mengarah ke daftar ruangnya.
- Menu atas selalu bisa dipakai untuk pindah ruang.
- Menutup panel chat tidak menghapus percakapan.
- Menutup showcase tidak menghilangkan isian formulir; formulir hanya dilepas, tidak dibuang.

## 13. Pertanyaan di luar cakupan
AI menolak dengan satu atau dua kalimat singkat, menyatakan hanya bisa membantu soal FTS, lalu mengarahkan kembali ke paket, layanan, permintaan proyek, atau tim. Pesan kasar, seksual, atau ilegal dijawab dengan balasan baku **tanpa sampai ke model**.

## 14. Kesalahan
- **Model tidak menjawab**: pengunjung melihat "AI Concierge sementara tidak tersedia, coba lagi sebentar" (HTTP 503, kode `concierge_unavailable`). Pesan pengunjung yang gagal dihapus dari riwayat, jadi mencoba lagi tidak menggandakan pesan.
- **Koneksi bermasalah di formulir**: pesan "Data Anda tersimpan, coba lagi atau hubungi tim kami".
- **Paket penuh pada periode itu**: formulir menawarkan paket lain yang tersedia.
- **Batas laju terlampaui**: HTTP 429; lihat [07_API_BACKEND_SPEC.md](07_API_BACKEND_SPEC.md).

## 15. Sesi yang kembali
`guest_token` di `localStorage` memulihkan percakapan di browser yang sama. Pilihan tema dan suara juga tersimpan per browser. Token yang tidak dikenal server memicu pesan agar memulai percakapan baru.
