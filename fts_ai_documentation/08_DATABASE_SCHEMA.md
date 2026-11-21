# FTS AI Concierge — Skema Database

Database bawaan **SQLite** ([database/database.sqlite](../database/database.sqlite)); sesi, cache, dan antrean memakai driver `database`. Skema ditetapkan oleh migrasi di [database/migrations/](../database/migrations). Nama tabel dan kolom masih memakai istilah hotel; arti FTS-nya ada di [01_PROJECT_CONCEPT.md](01_PROJECT_CONCEPT.md#2-asal-usul-dan-istilah-kode).

```text
hotels (perusahaan) ─┬─ hotel_users ─ users
                     ├─ room_types (paket) ─┬─ room_images
                     │                      └─ room_inventory (kuota harian)
                     ├─ hotel_knowledge_items
                     ├─ conversations ─┬─ conversation_messages
                     │                 └─ handover_requests
                     ├─ bookings (permintaan proyek) ── members ── member_points
                     ├─ audit_logs
                     └─ internal_notes (morph: booking | handover | member)
```

Semua tabel data perusahaan memiliki `hotel_id` dengan `cascadeOnDelete`, kecuali yang dijelaskan lain.

## 1. Tabel inti

### `hotels` — perusahaan
| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | PK | |
| `name` | string | Fujiyama Technology Solutions |
| `slug` | string, unik | `fts` |
| `custom_domain` | string, unik, null | Layani dari akar domain sendiri |
| `description` | text, null | Bahasa Indonesia |
| `translations` | JSON, null | `{ "en": {"description"}, "ja": {...} }` |
| `address`, `city`, `country` | string, null | |
| `latitude`, `longitude` | decimal(10,7), null | `null` di FTS |
| `phone`, `whatsapp`, `email` | string, null | |
| `timezone` | string | bawaan `Asia/Jakarta` |
| `currency` | string(3) | bawaan `IDR` |
| `default_locale` | string(2) | bawaan `id` |
| `check_in_time`, `check_out_time` | time | Jam kerja (09:00, 18:00) |
| `logo_path`, `cover_path` | string, null | Awalan `images/` = berkas di `public/`, selain itu di disk `public` |
| `public_status` | string | `draft` (bawaan) atau `published` |
| `settings` | JSON, null | `ai_model`, `ai_instructions`, `loyalty` |
| `features` | JSON, null | `member`, `member_plus` |
| `deleted_at`, timestamps | | Soft delete |

### `users`
Tabel Laravel standar ditambah `is_platform_admin` (boolean). Pengguna dikaitkan ke perusahaan lewat `hotel_users`.

### `hotel_users` — peran di perusahaan
| Kolom | Catatan |
|---|---|
| `hotel_id`, `user_id` | Unik berpasangan |
| `role` | `owner`, `front_desk`, `staff` (bawaan `staff`) |
| `status` | `active` (bawaan). Hanya staf aktif yang menerima notifikasi email |

### `room_types` — paket website
| Kolom | Catatan |
|---|---|
| `name`, `slug` | `slug` unik per perusahaan (`website-5-pages`) |
| `description`, `translations` | Teks `id` + `{id,en,ja: {name, description}}` |
| `base_price` | decimal(12,2): harga per bulan |
| `max_adults`, `max_children` | Pengguna per paket (FTS: 5 dan 0) |
| `size_sqm`, `bed_config`, `view_type`, `breakfast_included`, `extra_bed_available`, `extra_bed_price`, `amenities` | Sisa kolom hotel; tidak dipakai FTS |
| `is_active`, `sort_order` | |
| soft delete | |

### `room_images`
`room_type_id`, `image_path` (unggahan) atau `image_url` (luar), `tags` JSON, `alt_text`, `sort_order`. Aksesor `image_source` memilih salah satunya.

### `room_inventory` — kuota harian
| Kolom | Catatan |
|---|---|
| `room_type_id`, `stay_date` | Unik berpasangan: satu baris per paket per hari |
| `total_units` | Kapasitas hari itu |
| `booked_units` | Terpakai oleh permintaan `pending`/`confirmed` |
| `price` | decimal(12,2): harga hari itu (FTS: harga bulanan ÷ 30) |

`availableUnits() = total_units − booked_units`. Tabel ini yang menentukan ketersediaan dan total harga.

### `hotel_knowledge_items`
| Kolom | Catatan |
|---|---|
| `category` | `general`, `facilities`, `policies`, `dining`, `transport`, `faq`, `ai`, `portfolio` |
| `title`, `body` | Teks Indonesia |
| `translations` | JSON per bahasa: `title`, `body`, `menu`, `role` |
| `tags` | JSON |
| `image_url`, `images` | Sampul dan galeri |
| `details` | JSON: `demo_url`, `role`, `hours`, `menu` |
| `is_active`, `sort_order` | |
| Indeks | `(hotel_id, category)` |

Detail di [05_KNOWLEDGE_BASE_STRUCTURE.md](05_KNOWLEDGE_BASE_STRUCTURE.md).

## 2. Percakapan

### `conversations`
| Kolom | Catatan |
|---|---|
| `guest_token` | UUID unik; identitas pengunjung di browser |
| `guest_name`, `guest_email` | Opsional |
| `locale` | `id`, `en`, `ja` |
| `status` | `active`, `handed_over`, `closed` |
| `handover_summary` | Ringkasan untuk staf |
| `last_message_at` | |
| `current_scene` | string(20), bawaan `reception` (lihat `Conversation::SCENES`) |
| `selected_room_type_id` | FK null → `room_types` (`nullOnDelete`) |
| `selected_facility_id` | FK null → `hotel_knowledge_items` (`nullOnDelete`) |
| `reservation_state` | JSON: draf formulir yang sedang diisi |

### `conversation_messages`
| Kolom | Catatan |
|---|---|
| `conversation_id` | |
| `role` | `guest`, `assistant`, `staff`, `system` |
| `content` | text, null (balasan boleh hanya kartu) |
| `ui_payload` | JSON: daftar kartu yang dirender ke pengunjung |
| `tool_calls` | JSON: tool yang dipanggil (`name`, `input`, `miss`); bahan analitik |
| `rating` | tinyint null: `1` suka, `-1` tidak suka; diberi pengunjung |

### `handover_requests`
| Kolom | Catatan |
|---|---|
| `conversation_id` | |
| `reason` | `special_request`, `complaint`, `group_booking`, `negotiated_rate`, `unusual_cancellation`, `payment_issue`, `low_confidence` |
| `summary` | text |
| `status` | `open`, `resolved` |
| `assigned_to` | FK null → `users` |
| `resolved_at` | |

## 3. Permintaan proyek

### `bookings`
| Kolom | Catatan |
|---|---|
| `reference` | string(20), unik, null: `BK-XXXXXX` |
| `room_type_id` | Paket yang diminta |
| `conversation_id` | FK null: percakapan asal |
| `member_id` | FK null → `members` |
| `guest_name`, `guest_email`, `guest_phone` | |
| `contact_type` | `whatsapp`, `phone`, `email` |
| `check_in`, `check_out` | date: mulai dan selesai |
| `adults`, `children` | Pengguna dan pengguna tambahan |
| `room_count` | Jumlah website |
| `extra_bed` | boolean (tidak dipakai FTS) |
| `total_price` | decimal(12,2): setelah diskon |
| `discount_percent`, `discount_amount` | Potongan anggota |
| `status` | `pending`, `confirmed`, `cancelled`, `completed` |
| `notes` | Catatan pengunjung |

## 4. Anggota (fitur opsional)
Dibuat sebagai modul `app/Core/Member` yang polimorfik terhadap "tenant", jadi tidak terikat ke `hotels`.

### `members`
`tenant_type` + `tenant_id` (morph), `name`, `email`, `phone`, `locale`, `email_verified_at`, `last_login_at`, `remember_token`. Unik pada `(tenant_type, tenant_id, email)`.

### `member_login_codes`
`tenant`, `email`, `code_hash` (hash 64 karakter, terikat ke tenant+email), `attempts`, `expires_at`, `consumed_at`. Dipangkas harian.

### `member_points`
`member_id`, `type` (`earn`, `reverse`, `adjust`), `points` (bertanda), `source` (morph, mis. sebuah permintaan), `note`, `actor_name`. Unik pada `(source_type, source_id, type)` sehingga satu sumber tidak memberi poin dua kali.

## 5. Operasional

### `audit_logs`
`hotel_id`, `user_id` (null), `actor` (`staff`/`guest`/`system`), `action`, `summary`, `subject` (morph, null), `meta` JSON, `created_at`. Indeks `(hotel_id, created_at)`.

### `internal_notes`
`hotel_id`, `user_id` (null), `notable` (morph: `booking`, `handover`, `member`), `body`. Catatan internal staf; tidak terlihat pengunjung.

## 6. Infrastruktur Laravel
`password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`.

## 7. Aturan integritas
- Hapus perusahaan (hard) menghapus kaskade semua datanya; `Hotel`, `RoomType` memakai **soft delete**.
- `bookings` menahan `booked_units` pada `room_inventory` untuk setiap hari periode. Setiap perubahan status ke `cancelled` (staf, pengunjung, atau kedaluwarsa) harus melepas unit itu lewat `ReservationService::releaseInventory()`, dan pemindahan jadwal melepas yang lama lalu menahan yang baru dalam satu transaksi.
- Pembuatan permintaan mengunci baris inventori (`lockForUpdate`) agar dua permintaan bersamaan tidak melebihi kapasitas.
- Pencarian paket dan layanan selalu dibatasi `is_active = true`.

## 8. Data awal
[FtsSeeder](../database/seeders/FtsSeeder.php) mengisi: satu perusahaan `fts`, satu akun owner, tiga paket website (masing-masing satu gambar dan 120 hari inventori), dan seluruh item pengetahuan. Memakai `updateOrCreate`, jadi aman dijalankan ulang. Factory: `HotelFactory`, `UserFactory`, `MemberFactory`.
