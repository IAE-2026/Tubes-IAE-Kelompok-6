# AI Prompt Log

Rekap penggunaan AI dalam pengembangan backend Smart Parking System — **Service C (Keanggotaan & Voucher)** untuk Tugas Besar Integrasi Aplikasi Enterprise.

## Informasi Proyek

| Parameter | Value |
|-----------|-------|
| **Mata Kuliah** | BBK2HAB3 - Integrasi Aplikasi Enterprise |
| **Kelompok** | 6 |
| **Tanggal Mulai** | 14 Mei 2026 |
| **Tanggal Selesai** | 16 Mei 2026 |
| **AI Tool** | Google Gemini 2.5 Pro |

Anggota kelompok:

| Nama | NIM | Service |
|------|-----|---------|
| Farid Maulana | 102022400039 | Service A - Lahan & Lokasi |
| Hadid Hamar | 102022400126 | Service B - Transaksi & Payment |
| Dinda Juniar | 102022400023 | Service C - Membership & Voucher |

---

## Prompt 1 — Perencanaan Arsitektur Service C

> **Tanggal:** 14 Mei 2026

Prompt:

```
Saya ingin membuat backend Service C untuk Smart Parking System. Service ini bertanggung jawab mengelola data membership dan voucher parkir. Teknologi yang digunakan adalah Laravel dengan MySQL. Tolong bantu saya merancang arsitektur yang sesuai, termasuk:
- Struktur database (tabel apa saja yang dibutuhkan)
- Endpoint REST API yang diperlukan
- Mekanisme keamanan menggunakan API Key (X-IAE-KEY)
- Rencana containerisasi dengan Docker
```

Respon AI:

- AI merekomendasikan arsitektur menggunakan Laravel sebagai framework utama
- Tabel yang disarankan: `memberships`, `vouchers`, `membership_usages`
- Endpoint mengikuti spesifikasi kelompok:
  - `GET /api/v1/memberships` — daftar seluruh member
  - `GET /api/v1/memberships/{id}` — detail member berdasarkan kode
  - `POST /api/v1/memberships` — mendaftarkan member baru
- Keamanan menggunakan custom middleware yang memvalidasi header `X-IAE-KEY`
- Docker menggunakan dua container: app (PHP 8.2) dan mysql (MySQL 8.0)

Tindakan saya: menyetujui arsitektur yang diusulkan dan melanjutkan ke implementasi.

---

## Prompt 2 — Inisialisasi Project dan Model

> **Tanggal:** 14 Mei 2026

Prompt:

```
Buatkan project Laravel baru untuk Service C. Saya butuh:
1. Model Eloquent untuk Membership dengan field: member_code, name, email, phone, membership_type (perunggu/perak/emas/platina), status, discount_percent, registered_at, expired_at
2. Model Voucher dengan field: code, description, discount_type, discount_value, max_discount, is_used, valid_until
3. Migration untuk kedua tabel tersebut
4. Seeder dengan data contoh (5 member dan 4 voucher)
```

Respon AI:

- Membuat project baru: `composer create-project laravel/laravel .`
- Membuat 3 model Eloquent: `Membership.php`, `Voucher.php`, `MembershipUsage.php`
- Membuat 3 file migration sesuai skema database
- Membuat `MembershipSeeder` dengan 5 data member awal
- Membuat `VoucherSeeder` dengan 4 data voucher awal

Tindakan saya: menjalankan `php artisan migrate --seed` untuk memverifikasi migrasi dan data berhasil.

---

## Prompt 3 — Implementasi REST API Controller

> **Tanggal:** 14 Mei 2026

Prompt:

```
Buatkan MembershipController dengan 3 endpoint sesuai spesifikasi kelompok:
1. GET /api/v1/memberships — mengembalikan daftar seluruh member
2. GET /api/v1/memberships/{id} — mencari member berdasarkan member_code, return 404 jika tidak ditemukan
3. POST /api/v1/memberships — mendaftarkan member baru dengan auto-generate member_code (MEM001, MEM002, dst)

Setiap response harus mengikuti format Standard Integration Contract:
{
  "status": "success/error",
  "message": "...",
  "data": {...},
  "meta": { "service_name": "...", "api_version": "v1" }
}
```

Respon AI:

- Membuat `MembershipController` di `app/Http/Controllers/Api/V1/`
- Implementasi 3 method: `index()`, `show()`, `store()`
- Auto-generate member_code: mengambil ID terakhir + 1, format `MEM` + 3 digit
- Mapping tipe keanggotaan ke diskon: perunggu=10%, perak=15%, emas=20%, platina=50%
- Registrasi routes di `routes/api.php` dengan prefix `v1`
- Semua response menggunakan wrapper JSON yang konsisten

Tindakan saya: mengubah beberapa pesan error ke Bahasa Indonesia agar konsisten.

---

## Prompt 4 — Middleware Keamanan API Key

> **Tanggal:** 15 Mei 2026

Prompt:

```
Buatkan middleware VerifyApiKey untuk memproteksi semua endpoint. Aturannya:
- Setiap request wajib mengirim header X-IAE-KEY
- API Key yang valid adalah NIM anggota kelompok: 102022400023, 102022400039, 102022400126
- Jika header tidak ada → 401 Unauthorized
- Jika key salah → 403 Forbidden
- Daftar key dibaca dari environment variable IAE_API_KEYS
```

Respon AI:

- Membuat `app/Http/Middleware/VerifyApiKey.php`
- Middleware membaca header `X-IAE-KEY` dari setiap request
- Daftar key dibaca dari env `IAE_API_KEYS` (comma-separated)
- Response error mengikuti format Standard Integration Contract
- Middleware diterapkan di level route group, bukan global

Tindakan saya: menguji middleware dengan Postman — berhasil mengembalikan 401 dan 403 sesuai skenario.

---

## Prompt 5 — Dokumentasi Swagger/OpenAPI

> **Tanggal:** 15 Mei 2026

Prompt:

```
Saya ingin menambahkan dokumentasi API interaktif menggunakan Swagger. Install L5-Swagger dan dokumentasikan ketiga endpoint membership. Swagger UI harus bisa diakses di /api/docs. Gunakan PHP 8 Attributes, bukan docblock annotations.
```

Respon AI:

- Install package: `composer require darkaonline/l5-swagger`
- Menggunakan PHP 8 Attributes (`#[OA\Get]`, `#[OA\Post]`, dst.) di controller
- Menambahkan `#[OA\Info]`, `#[OA\SecurityScheme]`, dan `#[OA\Schema]` di base `Controller.php`
- Mengkonfigurasi route Swagger UI di `config/l5-swagger.php` → path `/api/docs`
- Menjalankan `php artisan l5-swagger:generate` untuk generate file JSON

Tindakan saya: membuka `/api/docs` di browser — Swagger UI tampil dengan 3 endpoint yang terdokumentasi lengkap.

---

## Prompt 6 — GraphQL dengan Lighthouse

> **Tanggal:** 15 Mei 2026

Prompt:

```
Tambahkan fitur GraphQL menggunakan Lighthouse supaya data bisa di-query secara fleksibel. Saya butuh:
- Query untuk mengambil daftar memberships dan vouchers
- Query untuk mencari satu member berdasarkan member_code
- GraphQL Playground yang bisa diakses di /graphiql
```

Respon AI:

- Install package: `composer require nuwave/lighthouse mll-lab/laravel-graphiql`
- Membuat schema GraphQL di `graphql/schema.graphql`
- Definisi type: `Membership`, `Voucher`, `MembershipUsage`
- Query menggunakan Lighthouse directives: `@all`, `@find`, `@eq`, `@hasMany`
- Publish config Lighthouse: `php artisan vendor:publish --tag=lighthouse-config`

Tindakan saya: menguji query di `/graphiql` — berhasil mengambil data memberships dan vouchers.

---

## Prompt 7 — Docker Setup

> **Tanggal:** 15 Mei 2026

Prompt:

```
Buatkan konfigurasi Docker agar Service C bisa berjalan di container. Spesifikasinya:
- Dockerfile berbasis PHP 8.2 CLI
- docker-compose.yml dengan 2 service: app dan mysql
- MySQL harus punya healthcheck agar app menunggu sampai database siap
- Migration dan seeder harus jalan otomatis saat container pertama kali start
- Buat entrypoint script yang men-generate .env dari Docker environment variables
```

Respon AI:

- Membuat `Dockerfile` berbasis `php:8.2-cli` dengan ekstensi pdo_mysql, mbstring, gd, dll
- Membuat `docker-compose.yml` dengan service `app` (port 8000) dan `mysql` (port 3307)
- MySQL menggunakan healthcheck (`mysqladmin ping`) agar app tidak start duluan
- Membuat `docker-entrypoint.sh` yang:
  1. Menunggu MySQL ready dengan loop PDO connection test
  2. Generate file `.env` dari Docker environment variables
  3. Menjalankan `key:generate`, `migrate`, `db:seed`, `l5-swagger:generate`
  4. Start `php artisan serve`
- Membuat `.dockerignore` untuk mengecualikan vendor, node_modules, dll

Tindakan saya: menjalankan `docker-compose up --build -d` — kedua container (`service-c-app` dan `service-c-mysql`) berjalan dengan sukses.

---

## Prompt 8 — Landing Page UI

> **Tanggal:** 16 Mei 2026

Prompt:

```
Buatkan halaman landing page yang menarik ketika mengakses localhost:8000. Halaman ini harus menampilkan:
- Informasi Service C (nama, versi, pembuat)
- Link navigasi ke Swagger UI, GraphQL Playground, dan Health Check
- Daftar 3 REST API endpoint
- Informasi autentikasi (header X-IAE-KEY) dengan tombol copy
- Desain modern dark theme
```

Respon AI:

- Membuat Blade template `resources/views/welcome.blade.php`
- Desain menggunakan dark theme dengan glassmorphism dan animated background
- Typography menggunakan Google Fonts (Inter)
- 3 navigation card untuk Swagger, GraphQL, dan Health Check dengan hover effects
- Tabel endpoint REST API dengan badge GET/POST berwarna
- Section autentikasi dengan kode API Key dan tombol "Copy" menggunakan Clipboard API
- Footer menampilkan versi Laravel, PHP, dan MySQL secara dinamis
- Update route `web.php` agar root `/` menampilkan Blade view

Tindakan saya: membuka `localhost:8000` — landing page tampil dengan desain yang bagus dan semua link berfungsi.

---

## Prompt 9 — Debugging dan Final Testing

> **Tanggal:** 16 Mei 2026

Prompt:

```
Saya menemukan error MissingAppKeyException saat membuka localhost:8000 di Docker. Tolong perbaiki.
```

Respon AI:

- Root cause: file `.env` yang di-generate di `docker-entrypoint.sh` tidak memiliki baris `APP_KEY=`, sehingga perintah `php artisan key:generate` tidak bisa menulis key-nya
- Solusi: menambahkan baris `APP_KEY=` (kosong) di dalam heredoc `.env` di entrypoint script
- Setelah fix, `key:generate` berhasil mengisi key dan semua halaman bisa diakses

Tindakan saya:
- Rebuild container: `docker-compose down -v && docker-compose up --build -d`
- Verifikasi semua halaman:
  - ✅ `localhost:8000` — landing page tampil
  - ✅ `localhost:8000/api/docs` — Swagger UI dengan 3 endpoint
  - ✅ `localhost:8000/graphiql` — GraphQL Playground berfungsi
  - ✅ `localhost:8000/health` — JSON health check response
  - ✅ `GET /api/v1/memberships` — mengembalikan 5 member
  - ✅ `GET /api/v1/memberships/MEM001` — mengembalikan detail Budi Santoso
  - ✅ `POST /api/v1/memberships` — berhasil membuat MEM006
  - ✅ Request tanpa header `X-IAE-KEY` → 401 Unauthorized

---

## Ringkasan

| Komponen | Status |
|----------|--------|
| Project Laravel | ✅ |
| Model & Migration | ✅ |
| Seeder (5 member, 4 voucher) | ✅ |
| REST API (3 endpoint) | ✅ |
| Middleware API Key | ✅ |
| Swagger/OpenAPI | ✅ |
| GraphQL (Lighthouse) | ✅ |
| Docker (app + mysql) | ✅ |
| Landing Page UI | ✅ |
| Testing & Verifikasi | ✅ |
