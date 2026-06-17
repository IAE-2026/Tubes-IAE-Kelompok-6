# Service A - Lahan & Lokasi Parkir

Repository ini berisi Service A (Lahan & Lokasi Parkir) untuk tugas besar Integrasi Aplikasi Enterprise (IAE). Service ini dibangun menggunakan Laravel 11, PHP 8.2, dan database MySQL.

Service ini bertanggung jawab buat mengelola master data lokasi parkir, kapasitas total slot, sisa kuota slot yang tersedia, serta menghitung tarif dasar parkir.

## Identitas Anggota
* **Nama**: Farid Maulana
* **NIM**: 102022400039
* **Tim**: TEAM-06 (Kelompok 6)

## Skema Database
Terdapat 3 tabel utama yang digunakan:
1. `locations`: Menyimpan informasi lahan parkir (id, nama, alamat, tipe indoor/outdoor, tipe parkir regular/vip, total slot, sisa slot, dan tarif dasar). Format ID lokasi adalah custom `loc_XXX` (contoh: `loc_001`).
2. `roles`: Sinkronisasi hak akses email SSO lokal (default `viewer`).
3. `audit_receipts`: Menyimpan `receipt_number` hasil SOAP Audit yang dikirim ke server pusat dosen.

## Kontrak API Endpoints

Semua endpoint dilindungi oleh middleware SSO JWT (`iae.sso`) dan wajib menyertakan header `Authorization: Bearer <token_jwt>`.

### 1. GET /api/v1/locations
* **Fungsi**: Menampilkan seluruh daftar lokasi parkir.
* **Response**: List lokasi parkir dalam format JSON.

### 2. GET /api/v1/locations/{id}
* **Fungsi**: Menampilkan detail data untuk satu lokasi parkir berdasarkan ID.
* **Response**: Detail lokasi parkir JSON.

### 3. POST /api/v1/locations
* **Fungsi**: Menambahkan data master lokasi parkir baru.
* **Request Body**:
  ```json
  {
    "name": "Gedung Parkir TULT",
    "address": "Jl. Telekomunikasi No. 1, Bandung",
    "type": "indoor",
    "parking_type": "regular",
    "total_spots": 100,
    "base_rate": 3000
  }
  ```
* **Proses di belakang layar**:
  1. Meng-generate ID baru secara berurutan (`loc_001`, `loc_002`, dst).
  2. Menyimpan data lokasi ke database lokal.
  3. Memanggil M2M token SSO, lalu mengirim SOAP XML Audit ke server pusat dosen untuk mengambil `ReceiptNumber`.
  4. Menyimpan `ReceiptNumber` ke tabel `audit_receipts` lokal.
  5. Mempublikasikan event `location.created` berisi data lengkap ke RabbitMQ exchange pusat.
  6. Mengembalikan response 201 dengan data lokasi dan receipt number.

### 4. POST /api/v1/locations/{id}/occupy
* **Fungsi**: Mengurangi sisa kapasitas slot parkir (`available_spots`) saat digunakan.
* **Request Body** (opsional): `{"slots": 1}`
* **Response**: Mengembalikan data lokasi terupdate. Jika sisa slot habis (0), akan membalas dengan error `400 Bad Request`.

### 5. POST /api/v1/locations/{id}/release
* **Fungsi**: Menambah kembali sisa kapasitas slot parkir saat slot kosong.
* **Request Body** (opsional): `{"slots": 1}`
* **Response**: Mengembalikan data lokasi terupdate. Penambahan slot tidak boleh melebihi batas total slot (`total_spots`).

### 6. POST /api/v1/events/rabbitmq-callback
* **Fungsi**: Webhook simulasi penerimaan event asinkron dari RabbitMQ via HTTP.
* **Request Body**:
  ```json
  {
    "event": "parking.slot.occupied",
    "data": {
      "location_id": "loc_001",
      "slots": 1
    }
  }
  ```
* **Keterangan**: Menerima event simulasi `parking.slot.occupied` (kurangi kuota slot), `parking.slot.released` (tambah kuota slot), atau `parking.payment.completed` (tambah kuota slot).

## Asynchronous RabbitMQ Consumer (Worker)
Untuk mendengarkan event RabbitMQ secara langsung di background, jalankan perintah Artisan berikut:
```bash
php artisan rabbitmq:consume
```
Worker ini otomatis mendengarkan exchange `iae.central.exchange` dengan queue `team06_smart_parking_queue` untuk event berikut:
* `parking.slot.occupied` -> Otomatis mengurangi `available_spots` di database.
* `parking.slot.released` -> Otomatis menambah `available_spots` di database.
* `parking.payment.completed` -> Otomatis menambah `available_spots` di database.

## Instalasi & Cara Menjalankan
1. Salin `.env.example` menjadi `.env` dan atur konfigurasi database serta kredensial SSO pusat.
2. Jalankan perintah:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve --port=3001
   ```
