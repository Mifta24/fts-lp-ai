# FTS AI Concierge — Admin CMS

Panel admin ada di `/admin` (satu aplikasi, banyak perusahaan) dan panel platform di `/platform`. Tampilan admin masih berbahasa Inggris dan sebagian istilahnya diganti ke istilah FTS ("Plans", "Project requests", "Company settings"). Kode ada di [app/Http/Controllers/Admin/](../app/Http/Controllers/Admin) dan [resources/views/admin/](../resources/views/admin).

## 1. Tujuan
Staf FTS mengelola semua hal yang dilihat pengunjung dan yang dikatakan AI tanpa menyentuh kode: paket, ketersediaan, basis pengetahuan, permintaan proyek, percakapan yang diserahkan, anggota, dan pengaturan perusahaan.

## 2. Masuk dan peran
Login di `/admin/login` (email + kata sandi, maksimal 5 percobaan per menit). Setelah masuk, pengguna bekerja di satu **perusahaan aktif**; bila ia anggota beberapa perusahaan, ada pemilih "Switch company" di sidebar.

Peran per perusahaan disimpan di `hotel_users.role`. Kemampuan tiap peran ([HotelUser::ABILITIES](../app/Models/HotelUser.php)):

| Kemampuan | `owner` | `front_desk` | `staff` |
|---|:-:|:-:|:-:|
| `content` (paket dan knowledge base) | ✓ | | |
| `inventory` (ketersediaan) | ✓ | ✓ | |
| `bookings.view` | ✓ | ✓ | ✓ |
| `bookings.manage` (ubah status) | ✓ | ✓ | |
| `handovers` | ✓ | ✓ | ✓ |
| `analytics` | ✓ | ✓ | |
| `activity` | ✓ | | |
| `settings` | ✓ | | |
| `team` | ✓ | | |
| `members` | ✓ | ✓ | |

Rute yang kemampuannya tidak dimiliki peran memberi `403`, bukan hanya menyembunyikan menu. Menu hanya menampilkan yang boleh dibuka, dan tanda angka muncul untuk pekerjaan yang menunggu (mis. permintaan `pending`, handover terbuka).

Pengguna dengan `is_platform_admin` juga bisa membuka **All companies** (`/platform/hotels`).

## 3. Modul sidebar
| Menu | Rute | Kemampuan |
|---|---|---|
| Dashboard | `/admin` | semua |
| Plans | `/admin/room-types` | `content` |
| Availability | `/admin/inventory` | `inventory` |
| Knowledge base | `/admin/knowledge-items` | `content` |
| Project requests | `/admin/bookings` | `bookings.view` |
| Handovers | `/admin/handovers` | `handovers` |
| Members | `/admin/members` | `members` + fitur `member` |
| Points & tiers | `/admin/loyalty` | `settings` + fitur `member_plus` |
| Analytics | `/admin/analytics` | `analytics` |
| Team | `/admin/team` | `team` |
| Company settings | `/admin/settings` | `settings` |
| Activity | `/admin/activity` | `activity` |
| My account | `/admin/account` | semua |
| All companies | `/platform/hotels` | platform admin |

## 4. Dashboard
Ringkasan: jumlah paket, jumlah item pengetahuan, permintaan `pending`, handover terbuka, lima permintaan terbaru, dan lima handover terbuka.

## 5. Plans (paket website)
CRUD paket. Kolom form: nama, deskripsi, terjemahan `en` dan `ja` (nama dan deskripsi), harga bulanan (`base_price`), pengguna per paket (`max_adults`, `max_children`), urutan, status aktif, dan gambar (unggah hingga 20, tag, alt text). Slug dibuat otomatis dari nama dan tidak berubah saat mengedit. Beberapa kolom warisan hotel (ukuran, tempat tidur, pemandangan, sarapan, extra bed, fasilitas) masih ada di form dan tidak dipakai FTS.

Terjemahan Indonesia adalah teks utama (`name`, `description`); admin hanya menyediakan kolom `en` dan `ja` tambahan.

## 6. Availability (inventori)
Editor kuota: pilih paket, rentang tanggal (`from` tidak boleh sebelum hari ini, `to` setelah atau sama dengan `from`), kapasitas harian (`total_units`), dan harga harian (`price`). Menyimpan menimpa baris `room_inventory` untuk rentang itu. Untuk FTS, harga harian = harga bulanan ÷ 30.

Perintah `inventory:extend` (harian) memperpanjang inventori dengan mengulang baris minggu sebelumnya, jadi tanggal ke depan tidak pernah habis.

## 7. Knowledge base
CRUD item pengetahuan. Kolom form:

| Kolom | Keterangan |
|---|---|
| Kategori | `general`, `facilities`, `policies`, `dining`, `transport`, `faq`, `ai`, `portfolio` |
| Judul dan isi | Bahasa Indonesia |
| Terjemahan | Judul dan isi `en` dan `ja` |
| Tag | Dipisah koma; membantu pencarian AI dan ikon |
| Gambar sampul | URL atau unggahan (`jpeg`, `png`, `webp`, maks. 8 MB) |
| Galeri | URL atau unggahan, hingga 8 foto |
| Jam | Teks bebas (`details.hours`) |
| Menu | Daftar isi layanan (`details.menu`) |
| Aktif, urutan | |

Item nonaktif tidak tampil dan tidak dicari AI. Perubahan langsung berlaku di ruang dan jawaban chat. Sebelum memublikasikan fakta baru, pastikan benar: AI mengutipnya sebagai fakta. Detail struktur ada di [05_KNOWLEDGE_BASE_STRUCTURE.md](05_KNOWLEDGE_BASE_STRUCTURE.md).

## 8. Project requests (permintaan proyek)
- **Daftar** dengan kolom referensi, pengunjung, paket, tanggal, total, dan status.
- **Detail**: data pengunjung, kontak, catatan, total (dengan potongan anggota), percakapan asal, dan **catatan internal** staf.
- **Ubah status** (peran `bookings.manage`): hanya ke `confirmed` atau `cancelled`.
  - `cancelled` melepas slot kembali ke inventori dan tidak bisa dihidupkan lagi tanpa membuat permintaan baru.
  - `completed` diatur sistem; permintaan `completed` masih bisa dibatalkan (tidak datang, pengembalian) tetapi tidak kembali ke `confirmed`.
- Perubahan tercatat di Activity.
- Permintaan `pending` yang tidak dikonfirmasi dalam 24 jam dibatalkan otomatis oleh sistem dan slotnya dikembalikan. Staf perlu memperhatikan antrean `pending` setiap hari.

Staf aktif menerima email `NewBookingRequest` untuk setiap permintaan baru dan `GuestChangedBooking` saat pengunjung mengubah jadwal atau membatalkan.

## 9. Handovers
Antrean percakapan yang diserahkan AI. Tiap handover memuat alasan, ringkasan, dan seluruh percakapan.
1. Staf membuka handover.
2. Membalas (maks. 2000 karakter): pesan disimpan dengan peran `staff` dan muncul di chat pengunjung lewat `history`.
3. Menandai **resolved**.
Staf bisa menambahkan catatan internal.

Staf aktif menerima email `NewHandoverRequest` begitu ada handover baru.

## 10. Members dan Points & tiers
Hanya tampil bila fitur `member` (dan `member_plus`) dinyalakan oleh platform admin.
- **Members**: daftar anggota, detail (profil, riwayat, poin), penyesuaian poin manual (tercatat dengan nama staf), dan catatan internal.
- **Points & tiers**: aturan program: belanja per poin dan tingkatan (bawaan Silver 200 poin / diskon 5%, Gold 1.000 poin / 10%). Bila belum diatur, program awal itu yang berlaku. Belanja per poin bawaan: 10.000 untuk IDR, 1.000 untuk JPY.

## 11. Analytics
Periode 7, 30, atau 90 hari (bawaan 30). Dihitung dari percakapan yang sudah tersimpan oleh `ConciergeAnalytics`:
- jumlah percakapan dan pengunjung yang benar-benar bertanya;
- permintaan proyek total, yang berasal dari chat, dan percakapan yang berujung permintaan;
- pertanyaan yang sering diulang dan topik pencarian pengetahuan teratas;
- tool yang paling sering dipakai;
- **celah pengetahuan**: pencarian yang tidak menemukan hasil (bahan untuk menambah knowledge base);
- handover yang belum ditangani;
- jempol suka dan tidak suka, serta balasan yang tidak disukai.

## 12. Team
Hanya `owner`. Tambah anggota dengan email, nama opsional, dan peran (`owner`, `front_desk`, `staff`); bila akun belum ada, dibuat dengan kata sandi acak. Ubah peran atau status (`active`/`suspended`), atau keluarkan. Perusahaan wajib punya **minimal satu owner aktif**; mengubah atau mengeluarkan owner terakhir ditolak. Setiap perubahan tercatat.

## 12a. Company settings
Hanya `owner`:

| Kelompok | Kolom |
|---|---|
| Profil | Nama, deskripsi, alamat, kota, negara, lintang dan bujur |
| Kontak | Telepon, WhatsApp (hanya angka dan tanda telepon), email |
| Operasional | Zona waktu, mata uang (3 huruf), bahasa bawaan (`id`/`en`/`ja`), jam kerja (dipakai sebagai jam buka kantor) |
| Publikasi | Status `draft` atau `published` |
| Domain | Domain khusus (valid, unik, bukan domain aplikasi) |
| AI | **Panduan AI** (`ai_instructions`, maks. 1.500 karakter): nada dan hal yang disebut. Tidak mengalahkan aturan keras (harga dari tool, cakupan, konten) |
| Merek | Logo dan sampul (`jpeg`, `png`, `webp`, maks. 8 MB, diperkecil ke lebar 1600 px) |

Model AI per perusahaan **tidak** diatur di sini; hanya platform admin yang bisa (lihat §14).

## 13. Activity
Jejak `audit_logs`: siapa (staf, pengunjung, atau sistem), apa, kapan, dan objeknya. Mencakup perubahan status permintaan, balasan handover, perubahan tim, model AI, dan fitur.

## 14. Panel platform (`/platform/hotels`)
Hanya platform admin. Untuk seluruh platform:
- **Buka perusahaan baru** (nama, slug opsional, kota, negara, mata uang, bahasa, email owner). Perusahaan baru berstatus `draft` (tersembunyi) sampai dipublikasikan. Bila akun owner belum ada, dibuat dengan kata sandi acak yang ditampilkan sekali.
- **Ubah model AI** per perusahaan (`settings.ai_model`; karakter yang diperbolehkan huruf, angka, `. _ : / -`).
- **Terbitkan atau sembunyikan** (`public_status`).
- **Nyalakan fitur tambahan**: `member` dan `member_plus`.

Setara dengan perintah CLI:
```bash
php artisan hotel:create      # membuka perusahaan baru beserta akun owner
php artisan platform:admin    # menjadikan seorang pengguna platform admin
```

## 15. Aturan media
- Format `jpeg`, `png`, `webp`; maksimum 8 MB per berkas.
- Unggahan diperkecil (lebar maks. 1600 px) dan disimpan di disk `public`; jalankan `php artisan storage:link` di server.
- Gambar bisa juga berupa URL luar (`http`/`https`).
- Gambar latar ruang dan avatar bukan bagian admin; lihat [11_SCENE_AND_ASSET_LIST.md](11_SCENE_AND_ASSET_LIST.md).

## 16. Akun saya
Setiap pengguna bisa mengganti kata sandinya sendiri (kata sandi saat ini wajib, kata sandi baru minimal 10 karakter dan dikonfirmasi).
