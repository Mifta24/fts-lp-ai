# FTS AI Concierge — Struktur Basis Pengetahuan

## 1. Tujuan
Basis pengetahuan adalah **satu-satunya sumber fakta perusahaan** bagi AI Concierge dan bagi ruang-ruang di situs. AI tidak boleh menjawab fakta dari ingatan; ia memanggil `search_knowledge` dan hanya memakai hasilnya. Isinya diambil dari [fts-tech.co.id](https://fts-tech.co.id/) dan [ai-solutions.fts-tech.co.id](https://ai-solutions.fts-tech.co.id/), dan diisi oleh [FtsSeeder](../database/seeders/FtsSeeder.php) atau lewat admin.

Ada tiga sumber data, masing-masing di tabel sendiri:

| Sumber | Tabel / model | Dipakai untuk |
|---|---|---|
| Profil perusahaan | `hotels` / `Hotel` | Nama, deskripsi, alamat, kontak, jam kerja, bahasa, mata uang, logo |
| Paket website | `room_types` / `RoomType` (+ `room_images`, `room_inventory`) | Ruang Paket Website, harga, ketersediaan, permintaan proyek |
| Item pengetahuan | `hotel_knowledge_items` / `HotelKnowledgeItem` | Layanan, AI Solutions, portofolio, tim, nilai, kebijakan, FAQ, kantor |

## 2. Kategori item pengetahuan
Konstanta di [HotelKnowledgeItem](../app/Models/HotelKnowledgeItem.php):

| Kategori | Isi di FTS | Tampil di |
|---|---|---|
| `general` | Tentang FTS, profil pendiri dan direksi, nilai-nilai FTS | Tentang FTS |
| `facilities` | Enam layanan FTS | Layanan |
| `dining` | (tidak dipakai FTS; tersedia untuk menu/isi layanan) | Layanan |
| `transport` | Kantor FTS dan cara ke sana | Layanan; juga dipakai kartu lokasi di chat |
| `ai` | 16 demo langsung + 3 produk langsung | AI Solutions |
| `portfolio` | 12 proyek | Portofolio |
| `policies` | Jam kerja dan waktu respons, kontrak dan biaya paket, proyek di atas 15 halaman | Tentang FTS |
| `faq` | Biaya website, biaya desain, WhatsApp, offshore, cara memulai proyek | Tentang FTS |

Pemetaan kategori ke ruang ada di `HotelPageController::SECTIONS`:
- Layanan: `facilities`, `dining`, `transport`
- AI Solutions: `ai`
- Portofolio: `portfolio`

Tool `search_knowledge` menerima filter kategori `general`, `facilities`, `policies`, `dining`, `transport`, `faq`, `ai`, `portfolio`.

## 3. Struktur satu item
| Kolom | Tipe | Fungsi |
|---|---|---|
| `hotel_id` | FK | Perusahaan pemilik |
| `category` | string | Salah satu kategori di atas |
| `title`, `body` | string, text | Teks dalam bahasa Indonesia (bahasa bawaan) |
| `translations` | JSON | `{ "en": {title, body, menu?, role?}, "ja": {...} }`; bila kosong dipakai teks bawaan |
| `tags` | JSON | Kata kunci pencarian dan penentu ikon |
| `image_url` | string | Foto sampul |
| `images` | JSON | Galeri tambahan |
| `details` | JSON | `demo_url`, `role`, `hours`, `menu` (daftar `{name, description, price}`) |
| `is_active` | bool | Hanya item aktif yang tampil dan dicari |
| `sort_order` | int | Urutan tampil |

Aksesor penting: `translatedTitle($locale)`, `translatedBody($locale)`, `photoUrls()` (sampul dulu, lalu galeri tanpa duplikat), `demoUrl($locale)` (menambahkan `?lang=`), `demoRole($locale)`, `menuEntries($locale)`, `openingHours()`, `iconName()`.

## 4. Profil perusahaan (`Hotel`)
Isi seeder untuk FTS:

| Kolom | Nilai |
|---|---|
| `name` | Fujiyama Technology Solutions |
| `slug` | `fts` |
| `description` | Dengan terjemahan `id`, `en`, `ja` di `translations` |
| `address`, `city`, `country` | Neo Soho Mall, Jl. Let. Jend. S. Parman Kav. 28 Unit 2011, Tanjung Duren Selatan, Grogol Petamburan · West Jakarta, DKI Jakarta 11470 · Indonesia |
| `latitude`, `longitude` | `null` (kartu lokasi memakai alamat teks sebagai target peta) |
| `phone`, `whatsapp`, `email` | +62 895 2933 6179 · 6289529336179 · info@fts-tech.co.id |
| `timezone`, `currency`, `default_locale` | `Asia/Jakarta`, `IDR`, `id` |
| `check_in_time`, `check_out_time` | `09:00`, `18:00` (dipakai sebagai jam kerja Senin–Jumat) |
| `logo_path` | `images/fts-mark.png` |
| `public_status` | `published` |

`public_status` harus `published` agar perusahaan bisa diakses; selain itu `404`.

## 5. Paket website (`RoomType`)
Tiga paket bulanan. Desain dan pengembangan gratis; yang dibayar hanya biaya server per bulan, kontrak 36 bulan dengan 6 bulan terakhir gratis.

| Slug | Nama | Harga per bulan |
|---|---|---|
| `website-5-pages` | Website Hingga 5 Halaman | Rp 700.000 |
| `website-10-pages` | Website Hingga 10 Halaman | Rp 1.200.000 |
| `website-15-pages` | Website Hingga 15 Halaman | Rp 1.600.000 |

- `translations` menyimpan nama dan deskripsi `id`, `en`, `ja`.
- Satu gambar per paket (`room_images`), bertag `website`.
- `max_adults` = 5 (jumlah pengguna per paket); kolom khas kamar (ukuran, tempat tidur, pemandangan, sarapan, extra bed) dibiarkan kosong atau `false`.
- Untuk lebih dari 15 halaman, FTS memberi penawaran khusus; AI menyerahkannya ke tim (alasan `group_booking`).
- Kuota harian dijelaskan di [08_DATABASE_SCHEMA.md](08_DATABASE_SCHEMA.md) dan [06_PROJECT_REQUEST_FLOW.md](06_PROJECT_REQUEST_FLOW.md).

## 6. Isi seeder per kelompok

### Tentang FTS (`general`)
Profil perusahaan; Yoshihiro Nakagawa (pendiri / presiden direktur); Takakazu Kaburaki (COO); Naoki Yoshida (direktur, teknologi dan inovasi); Takaki Morita (direktur, bidang AI); nilai-nilai FTS (Innovation, Quality, Transparency, Growth).

### Layanan (`facilities`)
Web Development, AI Automation, AI Agent Development, Offshore Development, Website Restoran, Otomasi Bisnis & Digital Marketing. Tiap layanan punya foto, deskripsi tiga bahasa, daftar fitur di `details.menu`, dan tag.

### AI Solutions (`ai`)
- **16 demo langsung** dari ai-solutions.fts-tech.co.id: FTS Wedding Venue AI, FTS Hotel AI, FTS Restaurant AI, Cafe AI, FTS Apartment AI, FTS Car Rental AI, Spa AI, Beauty Salon AI, Fitness Gym AI, Aurelia Event Hall, FTS Training Venue AI, FTS Recruitment Venue, Digital Agency AI, IT Company AI, Software SaaS AI, Maju Jaya Studio.
- **3 produk langsung**: Wild Mongolia, Thai Travel Booking, FTS Workspace.
- Tiap item punya `details.demo_url`, peran AI (mis. "AI Barista"), dan tiga fitur di `details.menu`. Tag `live product` menandai tiga produk. Demo memakai data contoh.

### Portofolio (`portfolio`)
Dua belas proyek dari fts-tech.co.id/projects/all, dengan gambar dari situs itu.

### Kantor (`transport`)
"Kunjungi kantor FTS di Jakarta Barat": alamat, jam buka Senin–Jumat 09:00–18:00, telepon, email.

### Kebijakan (`policies`)
- **Jam kerja dan waktu respons**: Senin–Jumat 09:00–18:00; email dijawab dalam 4 jam, telepon langsung, penawaran proyek dalam 24 jam.
- **Kontrak dan biaya paket website**: desain dan pengembangan gratis, biaya server bulanan, kontrak 36 bulan (6 bulan terakhir gratis), tanpa biaya tersembunyi.
- **Proyek lebih dari 15 halaman**: penawaran khusus, dijawab dalam 24 jam.

### FAQ (`faq`)
Biaya membuat website; biaya desain dan pengembangan; integrasi WhatsApp; tim offshore untuk perusahaan Jepang; cara memulai proyek.

## 7. Cara AI mencari
`HotelConciergeTools::matchingKnowledge()`:
1. Hanya item `is_active`, diurutkan `sort_order`, bisa dibatasi `category`.
2. Query kosong mengembalikan semua item.
3. Kata kunci (minimal 3 huruf) dicocokkan ke judul, isi, dan tag.
4. `search_knowledge` mengembalikan **maksimal 5 item** (`category`, `title`, `body` dalam bahasa percakapan, dan `demo_url` bila ada).
5. Bila tidak ada yang cocok, tool mengembalikan catatan agar AI **tidak menebak**: katakan akan dicek ke tim, atau serah terima.

Tool lain yang membaca item: `show_facility_photos` (kartu foto, maks. 6; kategori `facilities`, `dining`, `transport`, `ai`, `portfolio`), `show_menu` (isi layanan/menu, jam buka), `show_location` (alamat plus tiga item `transport`).

## 8. Aturan produksi
- Jangan taruh fakta yang belum disetujui di basis pengetahuan; AI akan mengutipnya sebagai fakta.
- Harga paket **tidak** disimpan sebagai teks bebas yang diandalkan AI. Sumber kebenaran harga adalah `room_types.base_price` dan `room_inventory.price`.
- Setiap item sebaiknya punya terjemahan `en` dan `ja`; tanpa itu, pengunjung berbahasa lain melihat teks Indonesia.
- Item dinonaktifkan dengan `is_active = false`, bukan dihapus, bila hanya ingin disembunyikan sementara.
- `db:seed` (DatabaseSeeder) hanya jalan di `local` dan `testing`. Di produksi jalankan `php artisan db:seed --class=FtsSeeder --force`: password owner diambil dari `FTS_OWNER_PASSWORD`, atau dibuat acak dan dicetak sekali bila kosong. Password demo `password` tidak pernah dipakai di produksi.

## 9. Alur pembaruan
1. Edit lewat admin **Knowledge base** (peran `owner`) atau ubah `FtsSeeder` lalu `php artisan db:seed --class=FtsSeeder` (seeder memakai `updateOrCreate`, jadi aman diulang).
2. Periksa tampilannya di ruang terkait dan tanyakan ke chat.
3. Jalankan `php artisan concierge:eval` bila perubahan memengaruhi perilaku jawaban.

## 10. Prioritas bila sumber berbeda
1. Data tool terstruktur: `room_types` dan `room_inventory` (harga, ketersediaan).
2. Item pengetahuan aktif (kebijakan, layanan, FAQ).
3. Profil perusahaan (`hotels`).
4. Bila tetap tidak ada jawaban: serah terima ke tim, bukan tebakan.
