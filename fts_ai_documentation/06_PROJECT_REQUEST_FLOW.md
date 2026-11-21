# FTS AI Concierge — Alur Permintaan Proyek

"Permintaan proyek" adalah nama untuk pengunjung; di kode dan database namanya **reservation** / `Booking`. Semua harga, ketersediaan, dan pembuatan permintaan lewat satu tempat: [ReservationService](../app/Services/Reservation/ReservationService.php). Wizard di halaman dan tool AI memakai service yang sama, jadi keduanya tidak bisa berbeda hasil.

## 1. Prinsip
- **Terpandu, bukan formulir panjang**: tiga langkah, banyak isian terisi otomatis.
- **Tanpa pembayaran**: hasilnya selalu permintaan `pending`. Lingkup dan biaya final dikonfirmasi tim FTS.
- **Server adalah sumber kebenaran**: JavaScript hanya membantu; semua aturan divalidasi ulang di server.
- **Dua pintu masuk, satu hasil**: wizard di ruang "Mulai proyek", dan formulir atau tool di chat AI.

## 2. Pintu masuk
| Dari | Cara |
|---|---|
| Menu atau dock "Mulai proyek" | `GET /fts/reservation` |
| Detail paket | Tombol "Mulai proyek", membawa `?room={slug}` sehingga paket terpilih |
| Kartu paket di chat | Tombol minta proyek membuka formulir di showcase |
| Teks chat | AI memanggil `open_booking_form` atau `create_booking_request` |

## 3. Data yang dikumpulkan
| Isian (nama di API) | Wajib | Keterangan |
|---|---|---|
| Paket (`room_type_slug`) | ya | Slug paket aktif |
| Tanggal mulai (`check_in`) | ya | `Y-m-d`, tidak boleh sebelum hari ini (zona waktu perusahaan) |
| Tanggal selesai (`check_out`) | ya | Setelah tanggal mulai. Wizard mengisinya otomatis 30 hari setelah mulai |
| Pengguna (`adults`) | ya | 1–20 |
| Pengguna tambahan (`children`) | tidak | 0–10 |
| Jumlah website (`rooms`) | ya | 1–10 |
| Nama (`guest_name`) | ya | Maks. 100 karakter |
| Cara dihubungi (`contact_type`) | ya | `whatsapp`, `phone`, atau `email` |
| Nomor atau email (`contact_value`) | ya | Email divalidasi sebagai email; WhatsApp/telepon harus cocok pola `^\+?[0-9\s\-().]{6,20}$` |
| Catatan (`special_request`) | tidak | Maks. 500 karakter |

Email tidak pernah ditanyakan oleh AI; hanya diminta bila pengunjung memilih email sebagai cara dihubungi.

## 4. Wizard tiga langkah
Kode: [resources/js/reservation.js](../resources/js/reservation.js). Judul langkah: *Paket & mulai · Data Anda · Ringkasan*.

### Langkah 1 — Paket dan mulai
- Pilih paket (kartu menampilkan "Maks. N pengguna per paket"). Paket yang terlalu kecil ditandai "Tidak cukup untuk tim Anda".
- Pilih tanggal mulai. Tanggal selesai dan periode (**30 hari**, sama dengan satu biaya bulanan) terisi otomatis. Jumlah pengguna dan jumlah website terisi otomatis.
- Setelah paket dan tanggal lengkap, browser memanggil `POST /fts/reservation/quote` untuk perkiraan total ("Memeriksa ketersediaan…").

### Langkah 2 — Data Anda
Nama, cara dihubungi, nomor atau email, catatan opsional. Anggota yang sudah masuk melihat nama dan kontaknya terisi dari akun.

### Langkah 3 — Ringkasan
Paket, periode, pengguna, kontak, **perkiraan total**, dan potongan anggota bila ada. Penafian: *"Ini adalah permintaan proyek. Lingkup dan biaya final dikonfirmasi oleh tim FTS. Belum ada pembayaran yang diambil."* Tombol "Kirim permintaan proyek" memanggil `POST /fts/reservation`.

### Hasil
- Nomor referensi, status "Menunggu konfirmasi FTS".
- Tombol **Kirim ke WhatsApp**, **Telepon FTS**, **Email FTS** (lihat §9), dan "Permintaan baru".
- Kemajuan wizard tersimpan di `sessionStorage` sehingga tidak hilang saat pindah halaman atau ganti bahasa. Pesan "Data Anda tersimpan" muncul saat koneksi gagal.

## 5. Validasi tanggal dan periode
- Tanggal mulai tidak boleh di masa lalu (membandingkan dengan hari ini di zona waktu perusahaan, `Asia/Jakarta`).
- Tanggal selesai harus setelah tanggal mulai.
- Periode maksimal **30 hari** (`ReservationService::MAX_NIGHTS`). Lebih dari itu: *"Periode dibatasi 30 hari. Hubungi tim kami untuk periode lebih lama."*
- Pesan kesalahan tersedia dalam `id`, `en`, `ja` (`ReservationController::MESSAGES`).

## 6. Validasi kapasitas
`ReservationService::fitsOccupancy()`:
- `adults ≤ max_adults × jumlah website`
- `adults + children ≤ maxOccupancy × jumlah website`

Di FTS `max_adults` = 5 per paket. Bila tidak muat: *"Paket ini tidak cukup untuk jumlah pengguna tersebut. Pilih paket lain atau tambah website."*

## 7. Ketersediaan dan harga
Tabel `room_inventory` menyimpan satu baris per paket per hari (`stay_date`): `total_units`, `booked_units`, `price`.

`ReservationService::quote()`:
1. Mengambil baris setiap hari dari tanggal mulai sampai sehari sebelum tanggal selesai.
2. Bila ada hari yang tidak punya baris, atau unit tersisa kurang dari jumlah website, hasilnya `null` (tidak tersedia).
3. Total = jumlah `price` tiap hari × jumlah website, ditambah extra bed bila relevan (tidak dipakai FTS), dikurangi diskon anggota.

Di seeder, `price` per hari = **harga bulanan ÷ 30**, sehingga periode 30 hari kira-kira sama dengan satu biaya bulanan. Slot dibuka 120 hari ke depan dengan 20 unit per paket; sebagian sudah terisi tetapi tidak pernah lebih dari separuh. Perintah `inventory:extend` (harian) terus membuka hari baru dengan mengulang kamar dan harga minggu sebelumnya.

Bila tidak tersedia, `quote` menjawab HTTP 422 dengan pesan "tidak tersedia pada periode tersebut" dan daftar `alternatives` (paket lain yang muat dan tersedia, beserta total hitungannya).

## 8. Membuat permintaan
`ReservationService::createRequest()` dalam satu transaksi:
1. Hitung ulang `quote` dengan `lockForUpdate`. Bila tidak lagi tersedia: gagal, tidak ada yang berubah.
2. Buat `Booking` berstatus `pending` dengan nomor referensi `BK-XXXXXX` (6 karakter dari alfabet tanpa huruf/angka yang mudah tertukar).
3. Tambah `booked_units` pada setiap hari periode.
4. Setelah transaksi, kirim email **NewBookingRequest** ke staf aktif perusahaan. Kegagalan email dicatat tetapi tidak membatalkan permintaan.

Bila dibuat lewat chat (`via_chat`), konfirmasi disimpan juga sebagai pesan di percakapan agar tetap ada saat pengunjung kembali.

### Status
| Status | Arti |
|---|---|
| `pending` | Baru dibuat, menunggu konfirmasi FTS |
| `confirmed` | Dikonfirmasi staf di admin |
| `cancelled` | Dibatalkan oleh staf, pengunjung, atau kedaluwarsa |
| `completed` | Diset sistem setelah hari selesai sebuah permintaan `confirmed` |

Staf mengubah status di admin (peran yang punya `bookings.manage`).

### Otomatisasi terjadwal
| Perintah | Jadwal | Fungsi |
|---|---|---|
| `bookings:expire-pending` | tiap jam | Membatalkan permintaan `pending` yang berumur lebih dari **24 jam** (`--hours`) dan mengembalikan slotnya |
| `bookings:complete-stays` | 02:30 harian | Menandai `confirmed` yang periodenya sudah lewat sebagai `completed` |
| `inventory:extend` | harian | Membuka hari berikutnya di inventori |
| `members:award-points` | 03:00 harian | Memberi poin untuk permintaan selesai, menarik poin yang dibatalkan |

## 9. Serah terima WhatsApp, telepon, email
[ReservationHandover](../app/Services/Reservation/ReservationHandover.php) menyusun tautan dari kontak perusahaan:
- **WhatsApp**: `https://wa.me/{nomor}?text=…` dengan pesan terisi: salam, nama, mulai, selesai, pengguna, jumlah website, paket, catatan, dan nomor referensi, dalam bahasa halaman.
- **Telepon**: `tel:{nomor}`.
- **Email**: `mailto:{email}` dengan subjek "Permintaan proyek {referensi}" dan isi yang sama.

Tautan yang kontaknya kosong tidak ditampilkan. Untuk "Bicara dengan tim" tanpa permintaan, pesannya salam singkat "ingin bicara dengan tim FTS".

## 10. Kelola permintaan oleh pengunjung
Halaman `/fts/booking`:
1. Pengunjung memasukkan **referensi** dan **kontak yang sama dengan saat meminta**. Referensi saja tidak cukup, agar permintaan orang lain tidak bisa dibuka dengan menebak. Batas laju mencegah penebakan.
2. **Ubah jadwal**: hanya bila status `pending` atau `confirmed` dan tanggal mulai belum lewat. Tanggal baru divalidasi sama seperti permintaan baru (tidak lampau, setelah mulai, maks. 30 hari, tersedia). Slot lama dilepas dan slot baru ditahan dalam satu transaksi. Permintaan `confirmed` kembali ke `pending`.
3. **Batalkan**: bila masih bisa diubah; slot dilepas segera.
4. Tim FTS menerima email **GuestChangedBooking**.

## 11. Anggota
Bila fitur anggota menyala:
- Pengunjung masuk dengan kode sekali pakai yang dikirim ke email.
- Nama dan kontak di formulir terisi dari akun, dan permintaan terhubung ke akun (`member_id`).
- Dengan `member_plus`, perkiraan total memuat potongan sesuai tingkat anggota, tercatat di `discount_percent` dan `discount_amount`.

## 12. Aturan pembayaran
Tidak ada pembayaran di situs. Pembayaran dibahas dan dikonfirmasi tim FTS di luar aplikasi. Bila pengunjung menyebut masalah pembayaran, AI menyerahkannya ke tim (`payment_issue`).

## 13. Keluar dan kembali
- Pengunjung bisa kembali ke langkah sebelumnya dan mengubah isian tanpa kehilangan data.
- Meninggalkan ruang tidak menghapus draf; draf hilang saat sesi browser berakhir atau setelah permintaan terkirim ("Permintaan baru").
