# FTS AI Concierge — Spesifikasi API dan Backend

Laravel 13 / PHP 8.3+ (dikembangkan di 8.4). Rute ada di [routes/web.php](../routes/web.php); semua endpoint publik dan admin ada di sana, tanpa prefiks `/api` dan tanpa versi. Halaman dirender server (Blade) dan endpoint JSON dipanggil oleh JavaScript vanilla di halaman yang sama.

## 1. Prinsip utama
1. **Model bahasa tidak pernah menjadi sumber data.** Fakta perusahaan, harga, dan ketersediaan hanya berasal dari database lewat tool (`HotelConciergeTools`) dan service (`ReservationService`).
2. **Satu sumber kebenaran harga.** Wizard, tool AI, dan kelola permintaan memakai `ReservationService`.
3. **Semua endpoint publik terikat perusahaan.** `{hotelSlug}` harus milik perusahaan berstatus `published`; selain itu `404`.
4. **Validasi di server.** Pesan validasi tamu dalam `id`/`en`/`ja` mengikuti `locale`.
5. **Batas laju** pada setiap endpoint yang bisa disalahgunakan.

## 2. Perusahaan dan domain
- Slug dicadangkan: `up`, `admin`, `platform`, `narration` tidak bisa menjadi slug karena rute `/{hotelSlug}` menolaknya (`where` di rute). Slug lain yang dicadangkan oleh `HotelOnboarding` dan `Hotel::reservedSlugs()` termasuk `storage`, `build`, `images`, `api`, `login`, `logout`, dll.
- `/` menampilkan lobi perusahaan bila hanya ada **satu** perusahaan terbit, selain itu layar pembuka.
- **Domain khusus**: middleware `ResolveCustomDomain` melayani perusahaan dengan `custom_domain` dari akar domainnya (`https://domain/rooms` ditangani sebagai `/{slug}/rooms`). Awalan milik aplikasi (`admin`, `platform`, `narration`, `up`, `storage`, `build`, `images`, `fonts`) tetap di alamatnya sendiri. Pencarian domain di-cache (`custom-domain:{host}`).

## 3. Halaman (GET, HTML)
Semua menerima `?lang=id|en|ja` (selain itu memakai `default_locale` perusahaan).

| Path | Nama rute | Ruang |
|---|---|---|
| `/` | `home` | Lobi atau layar pembuka |
| `/{slug}` | `hotel.show` | Lobi |
| `/{slug}/rooms` | `hotel.rooms` | Paket Website |
| `/{slug}/rooms/{roomSlug}` | `hotel.room` | Detail paket (`404` bila slug tidak ada) |
| `/{slug}/facilities` | `hotel.facilities` | Layanan |
| `/{slug}/solutions` | `hotel.solutions` | AI Solutions |
| `/{slug}/portfolio` | `hotel.portfolio` | Portofolio |
| `/{slug}/facilities/{id}` | `hotel.facility` | Detail layanan / AI Solutions / portofolio (`404` bila bukan item aktif) |
| `/{slug}/info` | `hotel.info` | Tentang FTS |
| `/{slug}/staff` | `hotel.staff` | Bicara dengan tim |
| `/{slug}/reservation` | `hotel.reservation` | Mulai proyek (`?room={slug}` memilih paket) |
| `/{slug}/booking` | `hotel.booking` | Kelola permintaan |
| `/sitemap.xml`, `/robots.txt` | `sitemap`, `robots` | SEO |

`robots.txt` menolak `/admin` dan `/platform` serta menunjuk ke sitemap. `sitemap.xml` mendaftar semua ruang dari perusahaan yang terbit.

## 4. Chat concierge (JSON)
Awalan `/{slug}/concierge`.

### `POST /concierge/start` — throttle `concierge-start` (30/menit per IP)
Masukan: `locale` (opsional, `id|en|ja`).
Keluaran:
```json
{ "guest_token": "uuid", "locale": "id" }
```
Membuat `Conversation` berstatus `active`.

### `POST /concierge/message` — throttle `concierge-message` (12/menit per `guest_token`, 120/menit per IP)
Masukan:
| Kolom | Aturan |
|---|---|
| `guest_token` | wajib, UUID |
| `message` | wajib, maks. 2000 karakter |
| `scene` | opsional, salah satu `Conversation::SCENES` |
| `selected_room` | opsional, slug paket |
| `selected_facility` | opsional, id item |
| `reservation.*` | opsional: `check_in`, `check_out` (`Y-m-d`), `adults` (1–20), `children` (0–10), `rooms` (1–10), `room_type_slug` |

Keluaran `200`:
```json
{
  "message": {
    "id": 123, "rating": null, "role": "assistant",
    "content": "teks polos", "ui_payload": [ { "type": "room_results", "...": "..." } ],
    "suggested_actions": [ { "action": "reserve", "room": "website-5-pages" } ],
    "quick_replies": ["pick_dates", "cheapest"],
    "created_at": "2026-11-21T10:00:00+07:00"
  },
  "status": "active"
}
```
Kesalahan: `422` bila `guest_token` tidak dikenal ("This conversation no longer exists. Please start a new one."), `503` `{ "error": "concierge_unavailable", "message": "..." }` bila model gagal atau melewati anggaran 85 detik.

Bila `status` percakapan `handed_over`, balasan otomatis hanya pesan sistem bahwa tim akan merespons.

### `GET /concierge/history` — throttle `concierge-history` (20/menit per token, 300/menit per IP)
Masukan: `guest_token` (UUID). Keluaran: `{ "messages": [...], "status": "..." }` berisi pesan peran `guest`, `assistant`, `staff`, `system` terurut waktu (format sama seperti `message` di atas).

### `POST /concierge/feedback` — throttle `concierge-feedback` (30/menit per token)
Masukan: `guest_token`, `message_id`, `rating` (`1`, `-1`, atau `0` untuk menarik). Hanya pesan `assistant`. Keluaran: `{ "id": 123, "rating": 1 }`.

### Jenis `ui_payload`
`room_results`, `room_detail`, `room_photos`, `availability`, `facility_results`, `dining_menu`, `location`, `booking_form`, `stay_picker`, `tour_start`, `booking_confirmation`, `handover`. Front-end merendernya di [concierge-cards.js](../resources/js/concierge-cards.js).

## 5. Permintaan proyek (JSON)
Awalan `/{slug}/reservation`, throttle `20/menit`.

### `POST /reservation/quote`
Masukan: `room_type_slug`, `check_in`, `check_out`, `adults`, `children`, `rooms`, `extra_bed`, `locale` (aturan di [06_PROJECT_REQUEST_FLOW.md](06_PROJECT_REQUEST_FLOW.md)).

`200`:
```json
{
  "available": true, "nights": 30, "room_total": 700000, "extra_bed_total": 0,
  "subtotal": 700000, "member_discount_percent": 0, "discount_amount": 0,
  "grand_total": 700000, "currency": "IDR"
}
```
`422` bila tidak tersedia: `{ message, errors, alternatives: [{slug, name, total}], currency }`. `422` juga untuk validasi tanggal, kapasitas, atau periode > 30 hari.

### `POST /reservation`
Masukan: kolom `quote` ditambah `guest_name`, `contact_type` (`whatsapp|phone|email`), `contact_value`, `special_request`, `guest_token` (opsional), `via_chat` (opsional).

`201`:
```json
{
  "reference": "BK-7K2M9Q", "status": "pending", "total": 700000, "currency": "IDR",
  "handover": { "whatsapp_url": "...", "phone_url": "...", "email_url": "..." },
  "confirmation": null
}
```
`confirmation` berisi payload kartu konfirmasi bila `via_chat` dan percakapan ada.

## 6. Kelola permintaan oleh pengunjung
Awalan `/{slug}/booking`, throttle `booking-manage` (10/menit per referensi+IP, 60/menit per IP).

| Endpoint | Fungsi |
|---|---|
| `POST /booking/lookup` | Cari berdasarkan `reference` + kontak yang cocok |
| `POST /booking/reschedule` | Pindah tanggal (`check_in`, `check_out`). `409` bila tidak bisa diubah, `422` bila tidak tersedia atau melewati batas |
| `POST /booking/cancel` | Batalkan bila masih bisa diubah |

Permintaan hanya bisa diubah bila statusnya `pending` atau `confirmed` dan tanggal mulainya belum lewat.

## 7. Anggota (bila fitur `member` menyala)
Awalan `/{slug}/member`, di bawah middleware `member.enabled`.

| Endpoint | Fungsi | Batas laju |
|---|---|---|
| `GET/POST /member/login` | Minta kode ke email | `member-code`: 3/menit per email+IP, 30/menit per IP |
| `GET/POST /member/verify` | Verifikasi kode | `member-verify`: 10/menit per email+IP, 60/menit per IP |
| `GET /member` | Akun | `member.auth` |
| `PUT /member/profile` | Ubah profil | `member.auth` |
| `POST /member/logout` | Keluar | `member.auth` |
| `POST /member/bookings/{reference}/reschedule` | Ubah jadwal permintaan sendiri | `member.auth` |
| `POST /member/bookings/{reference}/cancel` | Batalkan permintaan sendiri | `member.auth` |

Kode login disimpan sebagai hash (`code_hash`) dengan batas percobaan dan masa berlaku, lalu dipangkas harian (`model:prune`).

## 8. Narasi suara
`POST /narration/speech` (throttle `narration-speech`, 20/menit per IP).

Masukan: `text` (maks. 1500 karakter), `locale`, `signature`. Tanda tangan adalah HMAC SHA-256 dari `locale` + teks dengan `APP_KEY`; hanya teks yang dirender oleh halaman yang punya tanda tangan valid, jadi endpoint publik ini tidak bisa dipakai menyintesis (dan menagih) teks sembarang.

- `503` bila OpenRouter belum dikonfigurasi atau penyedia gagal; halaman jatuh ke speech synthesis browser.
- `403` bila tanda tangan tidak valid.
- `200` mengembalikan `audio/wav` (`Cache-Control: private, max-age=86400`). Setiap baris disintesis sekali dan disimpan di disk untuk semua pengunjung berikutnya.

## 9. Admin dan platform (HTML, perlu login)
Detail modul di [09_ADMIN_CMS.md](09_ADMIN_CMS.md).

- `/admin/login` (tamu saja, `throttle:5,1`), `POST /admin/logout`.
- Grup `auth` dengan middleware `hotel.can:{ability}`; peran tanpa kemampuan itu mendapat `403`.
- `/platform/hotels*` di bawah `auth` dan `platform.admin`.

## 10. Antarmuka layanan LLM
`ConciergeService::chatCompletion()` memanggil endpoint kompatibel OpenAI: `POST {LOCAL_LLM_BASE_URL}/v1/chat/completions` dengan `Authorization: Bearer {LOCAL_LLM_API_KEY}`. Isi:

```json
{ "model": "...", "messages": [...], "tools": [...], "tool_choice": "auto",
  "temperature": 0.3, "reasoning_effort": "none", "max_tokens": 4096 }
```

- Model diambil dari `settings.ai_model` perusahaan, bila tidak ada dari `LOCAL_LLM_MODEL`.
- `finish_reason = length` tanpa tool call dianggap gagal (token habis di tengah "berpikir").
- LM Studio atau Ollama yang di-host sendiri; pada setup saat ini dijangkau lewat Tailscale.

## 11. Alur satu giliran chat
```text
POST /concierge/message
  ├─ simpan konteks UI (rememberContext)
  ├─ simpan pesan pengunjung
  ├─ percakapan handed_over?  → pesan sistem, selesai
  ├─ ContentGuard menolak?     → balasan baku, selesai
  ├─ susun prompt + riwayat 40 pesan + pengingat cakupan
  ├─ panggilan tool lebih awal bila niat jelas (preemptedToolCall)
  ├─ chat completion → (tantang harga tanpa sumber / janji tanpa aksi, sekali)
  ├─ loop tool (maks. 6): dispatch tool → hasil teks + ui_payload → chat completion
  ├─ ReplyText::plain() membersihkan Markdown
  ├─ ContentGuard memeriksa balasan model
  └─ simpan pesan assistant (content, ui_payload, tool_calls) → JSON
```
Bila ada kegagalan, pesan pengunjung dihapus agar percobaan ulang tidak menggandakan.

## 12. Tool AI
Definisi di `HotelConciergeTools::definitions()` (skema function-calling OpenAI):

| Tool | Fungsi | Kartu di UI |
|---|---|---|
| `search_knowledge` | Cari di basis pengetahuan (maks. 5 hasil) | tidak ada |
| `show_facility_photos` | Kartu foto layanan | `facility_results` |
| `show_location` | Alamat dan peta | `location` |
| `show_room_photos` | Galeri foto paket | `room_photos` |
| `show_menu` | Isi layanan dan jam | `dining_menu` |
| `search_rooms` | Paket sesuai tanggal dan pengguna, dengan harga dan ketersediaan | `room_results` |
| `recommend_rooms` | Rekomendasi paket tanpa tanggal (maks. 6) | `room_results` |
| `open_booking_form` | Formulir proyek di chat, terisi | `booking_form` |
| `ask_stay_details` | Kalender dan penghitung pengguna | `stay_picker` |
| `start_tour` | Mulai tur berpemandu | `tour_start` |
| `get_room_detail` | Detail dan foto satu paket | `room_detail` |
| `check_availability` | Ketersediaan dan total harga satu paket | `availability` |
| `create_booking_request` | Membuat permintaan `pending` (cek ulang ketersediaan) | `booking_confirmation` |
| `request_human_handover` | Serahkan ke tim dengan alasan dan ringkasan | `handover` |

Setiap tool hanya bekerja pada perusahaan dan percakapan yang sedang berjalan. `dispatch()` mengembalikan `{ text, ui, miss? }`: `text` untuk model, `ui` untuk pengunjung.

## 13. Keamanan
- **CSRF** untuk semua POST dari halaman (token Laravel); endpoint publik tidak memakai sesi untuk identitas selain admin/anggota.
- **Identitas pengunjung**: `guest_token` UUID acak per percakapan; tidak ada akun.
- **Admin**: login berbatas 5 percobaan/menit, otorisasi per peran, bukan sekadar login.
- **Konten**: tampilan DOM dibuat dengan `textContent`; Blade melepas karakter khusus; data terstruktur JSON-LD memakai `JSON_HEX_TAG`.
- **Seeder produksi**: tidak pernah membuat login yang bisa ditebak ([FtsSeeder](../database/seeders/FtsSeeder.php)).
- **Unggahan gambar**: logo dan sampul `jpeg|png|webp` dengan batas ukuran ([ImageUploader](../app/Services/ImageUploader.php)).
- **Email gagal** tidak membatalkan permintaan yang sudah dibuat.

## 14. Pencatatan
- Kegagalan chat: `Log::error('Concierge reply failed', { hotel_id, error })`.
- Kegagalan TTS: `Log::error('Narration speech failed', ...)`.
- Jejak aktivitas staf dan sistem: tabel `audit_logs` (lihat [08_DATABASE_SCHEMA.md](08_DATABASE_SCHEMA.md)), dilihat di admin **Activity**.
- Isi percakapan, tool yang dipanggil, dan jempol tersimpan di `conversation_messages` dan menjadi bahan **Analytics**.
