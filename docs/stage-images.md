# Mengganti gambar latar dan avatar

Semua gambar ada di `public/images/`. Tiap adegan membaca file dengan nama tetap, jadi cukup menimpa file dengan nama yang sama. Tidak perlu mengubah kode. Setelah menimpa, muat ulang halaman (tidak perlu build ulang).

## Dua cara pakai

1. **Satu gambar dengan avatar di dalamnya** (cara paling sederhana). Dipakai sebagai cadangan kalau foto latar sebuah adegan tidak ada.
2. **Latar polos + potongan avatar terpisah** (lebih fleksibel). Avatar dilapis di atas latar, jadi bisa dipindah dan dipakai ulang. Semua adegan sekarang memakai cara ini.

## Daftar file

| Adegan | Gambar bawaan (avatar di dalam) | Latar polos (opsional, mode 2) | Potongan avatar |
|--------|----------------------------------|--------------------------------|-----------------|
| Lobi | `concierge-lobby.webp` | `lobby-bg.webp` (sudah terpasang: lobi kantor FTS) | `character/character right hand.webp` (sudah terpasang) |
| Paket website | `suite.webp` (cadangan) | `rooms-bg.webp` (sudah terpasang: meja kayu dan tanaman, foto stok) | `character/character right hand.webp` (dicerminkan) |
| Layanan | `facility.webp` (cadangan) | `facilities-bg.webp` (sudah terpasang: lounge layanan FTS) | `character/character right hand.webp` (dicerminkan) |
| AI Solutions | `facility.webp` (cadangan) | `solutions-bg.webp` (sudah terpasang: ruang AI Solutions FTS) | `character/character right hand.webp` (dicerminkan) |
| Portofolio | `facility.webp` (cadangan) | `portfolio-bg.webp` (sudah terpasang: dinding portofolio FTS) | `character/character right hand.webp` (dicerminkan) |
| Tentang FTS | `information.webp` (cadangan) | `about-bg.webp` (sudah terpasang: ruang rapat FTS) | `character/character greeting.webp` |
| Bicara dengan tim | `talk to staff.webp` (cadangan) | `staff-bg.webp` (sudah terpasang: meja resepsionis, foto stok) | `character/character grateful.webp` |
| Mulai proyek | `reservation.webp` (cadangan) | `consultation-bg.webp` (sudah terpasang: ruang rapat kaca, foto stok) | `character/character right hand.webp` (dicerminkan) |

Kalau file latar sebuah adegan dihapus atau diganti nama, adegan itu otomatis kembali ke gambar cadangannya (kolom "avatar di dalam").

Tiga foto latar terbaru (`rooms-bg.webp`, `consultation-bg.webp`, `staff-bg.webp`) adalah foto stok Unsplash sebagai pengganti sementara. Ganti dengan gambar buatan sendiri kapan saja dengan menimpa file bernama sama. Kredit fotografer ada di [image-credits.md](image-credits.md).

## Ukuran dan format

- **Latar:** 16:9 landscape, sekitar 2400 × 1350 px, WebP atau PNG, di bawah 400 KB bila bisa. Sisakan area kosong di kiri bawah (tempat judul dan bar chat) dan jangan taruh detail penting di bagian paling bawah (tertutup chip).
- **Potongan avatar:** PNG atau WebP dengan latar transparan, potret sekitar 1024 × 1536 px, badan sampai pinggang, wajah di sepertiga atas.
- Nama file dengan spasi (mis. `talk to staff.webp`) memang begitu adanya di kode, jangan diganti.

## Catatan

- Avatar FTS (wanita berambut bob, blazer navy) dibuat dengan Figma AI dalam satu lembar karakter supaya ketiga pose sama persis: `character right hand.webp` (menunjuk ke samping), `character greeting.webp` (melambai), `character grateful.webp` (tangan tertangkup). File sumbernya ada di file Figma "FTS Avatar". Untuk mengganti, timpa ketiga file itu; potongan avatar sebaiknya rapat (tanpa ruang kosong di atas kepala) dengan tubuh terpotong di tepi bawah.
- Posisi dan zoom wajah untuk avatar kecil di chat diatur per adegan di `sceneBackdrop()` di `app/Http/Controllers/HotelPageController.php` (kunci `focus`, `anchor`, `avatarZoom`, `avatarFocus`). Ubah hanya jika avatar baru terpotong atau terlalu kecil.
- Logo lengkap ada di `public/images/logo.webp`. Ikon logo (gunung dan matahari tanpa tulisan) ada di `public/images/fts-mark.png` dan dipakai di header, layar loading, dan tab browser. Untuk mengganti, timpa file itu dengan PNG persegi berlatar putih, atau unggah logo lewat pengaturan admin.
