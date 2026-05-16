# AI Prompt Log

Rekap penggunaan AI dalam pengembangan backend Smart Parking System untuk Tugas Besar Integrasi Aplikasi Enterprise.

## Informasi Proyek

| Parameter | Value |
|-----------|-------|
| **Mata Kuliah** | BBK2HAB3 - Integrasi Aplikasi Enterprise |
| **Kelompok** | 6 |
| **Tanggal** | 14 Mei 2026 |
| **AI Tool** | Claude Opus 4 |

Anggota kelompok:

| Nama | NIM | Service |
|------|-----|---------|
| Farid Maulana | 102022400039 | Service A - Lahan & Lokasi |
| Hadid Hamar | 102022400126 | Service B - Transaksi & Payment |
| Dinda Juniar | 102022400023 | Service C - Membership & Voucher |

## Prompt 1 — Scaffold Backend Microservices

Membuat seluruh backend berdasarkan dokumen `Context1.md`, `Context2.md`, dan `Group 6 Smart Parking.md`. Setiap service harus memiliki REST API, Swagger docs, GraphQL, keamanan via API Key, dan Dockerfile.

Prompt:

```text
Buatkan 3 backend service untuk Smart Parking sesuai spesifikasi pada file konteks.
Setiap service menggunakan NIM anggota sebagai X-IAE-KEY.
Sertakan Dockerfile, docker-compose, dan dokumentasi cara penggunaan.
```

Output AI:

- Inisialisasi 3 direktori: `service-a/`, `service-b/`, `service-c/`
- Install dependencies: `express`, `cors`, `swagger-jsdoc`, `swagger-ui-express`, `graphql`, `express-graphql`, `axios`
- Implementasi Service A dengan 5 REST endpoint dan GraphQL query
- Implementasi Service B dengan 5 REST endpoint, GraphQL, dan logika orchestrator ke Service A dan C
- Implementasi Service C dengan 7 REST endpoint dan GraphQL query
- Pembuatan `Dockerfile` dan `.dockerignore` untuk masing-masing service
- Pembuatan `docker-compose.yml` untuk orkestrasi ketiga container
- Validasi end-to-end flow: Tapping In → Checkout → Payment
- Pembuatan `HOW-TO-USE.md` sebagai panduan deployment

## Prompt 2 — Perbaikan Navigasi Direktori

Terminal menampilkan error saat menjalankan `cd service-a` dari dalam folder `service-c`.

Prompt:

```text
Kenapa perintah cd service-a error saat dijalankan dari direktori service-c?
```

Output AI:

- Menjelaskan bahwa `service-a` adalah sibling directory, bukan subdirectory
- Memperbaiki instruksi di `HOW-TO-USE.md` menggunakan absolute path

## Prompt 3 — Node.js Deprecation Warning

Muncul warning `DEP0169: url.parse()` saat menjalankan service.

Prompt:

```text
Apa arti warning DEP0169 DeprecationWarning url.parse() yang muncul di terminal?
```

Output AI:

- Warning berasal dari internal library `swagger-ui-express`, bukan dari kode proyek
- Tidak mempengaruhi fungsionalitas, aman untuk diabaikan

## Prompt 4 — Docker Engine Tidak Terdeteksi

Error saat build container karena Docker Desktop belum aktif.

Prompt:

```text
Error saat docker-compose up --build:
failed to connect to the docker API at npipe:////./pipe/dockerDesktopLinuxEngine
```

Output AI:

- Docker Desktop harus dibuka terlebih dahulu sebelum menjalankan perintah Docker
- Menghapus atribut `version: '3.8'` yang sudah deprecated dari `docker-compose.yml`

## Prompt 5 — Port Konflik (EADDRINUSE)

Port 3001, 3002, dan 3003 sudah terpakai oleh proses Node.js sebelumnya.

Prompt:

```text
Error port 3002 sudah terpakai saat menjalankan service.
```

Output AI:

- Mendeteksi proses Node.js lama yang masih berjalan
- Menggunakan `netstat -ano` untuk identifikasi PID
- Menjalankan `taskkill /F` untuk menghentikan proses yang menempati port

## Prompt 6 — Konflik Port Lokal vs Docker

Port 3003 terpakai saat menjalankan `docker-compose up` karena service lokal masih aktif.

Prompt:

```text
Error dari daemon: ports are not available, port TCP 0.0.0.0:3003 sudah terpakai.
```

Output AI:

- Menjelaskan bahwa menjalankan `npm start` secara lokal dan Docker secara bersamaan akan menyebabkan konflik port
- Solusi: gunakan salah satu mode saja, lokal atau Docker, tidak keduanya

## Prompt 7 — GraphQL Playground Error 401

GraphQL Playground tidak bisa dimuat karena middleware autentikasi memblokir initial GET request.

Prompt:

```text
GraphQL Playground menampilkan Unauthorized saat diakses di browser.
Bagaimana cara mengatasinya agar playground bisa dibuka?
```

Output AI:

- Memisahkan handler GraphQL menjadi dua route:
  - `GET /graphql` untuk menyajikan halaman GraphiQL playground (tanpa auth)
  - `POST /graphql` untuk memproses query (dengan auth via `X-IAE-KEY`)

## Prompt 8 — Custom NIM Input pada GraphQL Playground

Built-in header editor pada GraphiQL memerlukan format JSON yang valid, tidak mendukung input NIM secara langsung.

Prompt:

```text
Saya ingin memasukkan NIM langsung di GraphQL playground tanpa harus menulis format JSON lengkap.
Misalnya cukup ketik 102022400126 dan langsung bisa query.
```

Output AI:

- Menonaktifkan built-in header editor bawaan GraphiQL (`headerEditorEnabled: false`)
- Membuat custom input bar di bagian atas halaman dengan label `X-IAE-KEY`
- Fetcher membaca nilai dari input field dan menyisipkannya sebagai HTTP header
- Jika input kosong, server mengembalikan response `Unauthorized`
- Jika NIM valid, server mengembalikan data sesuai query

## Prompt 9 — Migrasi Service B ke Laravel

Prompt:

```text
Ubah project agar Service B milik Hadid Hamar menggunakan Laravel, tetap sesuai Context1, Context2, dan Group 6 Smart Parking, serta perbaiki integrasi occupy spot, voucher usage, dan frontend action.
```

Output AI:

- Membuat ulang Service B sebagai Laravel service dengan REST wrapper, middleware `X-IAE-KEY`, Swagger/OpenAPI, dan GraphQL.
- Memperbaiki integrasi Service B ke Service A agar occupy/release spot memakai `POST`.
- Memperbaiki flow voucher agar voucher ditandai terpakai setelah payment success.
- Menyesuaikan frontend action agar TAP_OUT mengarah ke `/checkout` dan PAYMENT_SUCCESS mengarah ke `/pay`.
