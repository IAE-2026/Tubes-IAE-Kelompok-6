# Tubes IAE Kelompok 6 - Smart Parking

Repositori ini menggabungkan tiga service Smart Parking dalam satu sistem. Kamu menjalankan semua service dengan Docker Compose. Kamu mengakses semua API lewat API Gateway Nginx di http://localhost.

## Anggota dan Service

1. Service A - Lahan dan Lokasi Parkir
   - Developer: Farid Maulana
   - NIM: 102022400039
   - Folder: services/farid-lahan-lokasi
   - Tugas utama: mengelola lokasi parkir, kapasitas slot, tarif dasar, SOAP audit, dan publish event lokasi.

2. Service B - Transaksi Parkir dan Payment
   - Developer: Hadid Hamar
   - NIM: 102022400126
   - Folder: services/hadid-transaksi-parkir
   - Tugas utama: membuat transaksi parkir, checkout, hitung biaya, hitung benefit membership, dan menyelesaikan payment.

3. Service C - Membership dan Voucher
   - Developer: Dinda Juniar
   - NIM: 102022400023
   - Folder: services/dinda-membership-voucher
   - Tugas utama: mengelola data membership, voucher, diskon, SOAP audit membership, dan publish event membership.

## Struktur Repo

~~~text
Tubes-IAE-Kelompok-6/
|-- README.md
|-- docker-compose.yml
|-- api-gateway/
|   |-- nginx.conf
|-- docs/
|   |-- architecture.md
|   |-- member-services.md
|-- services/
|   |-- farid-lahan-lokasi/
|   |-- hadid-transaksi-parkir/
|   |-- dinda-membership-voucher/
~~~

## Arsitektur Singkat

- Client mengirim request ke API Gateway.
- API Gateway meneruskan request ke service internal.
- Service A, Service B, dan Service C berjalan dalam network Docker yang sama.
- Hanya API Gateway yang membuka port host, yaitu port 80.
- Service B memanggil Service A untuk membaca lokasi dan update slot parkir.
- Service B memanggil Service C untuk membaca membership dan diskon.
- Service yang melakukan transaksi penting memakai alur SSO, SOAP Audit, lalu mengirim event ke sistem pusat.
- Service C mengambil token M2M sendiri sebelum mengirim event ke sistem pusat.

## Teknologi

- API Gateway: Nginx
- Container: Docker Compose
- Backend: Laravel dan PHP
- Database: MySQL 8
- Auth: JWT dari SSO dosen
- Audit: SOAP XML
- Event: HTTP publisher ke sistem pusat dosen
- API tambahan Service B: REST dan GraphQL

## Konfigurasi SSO Dosen

Request token M2M wajib membawa `api_key` dan `nim`.

- Service A memakai `KEY-MHS-67` dan NIM `102022400039`.
- Service B memakai `KEY-MHS-185` dan NIM `102022400126`.
- Service C memakai `KEY-MHS-45` dan NIM `102022400023`.

Service C memakai token M2M Dinda sebelum mengirim event ke sistem pusat. Ini mencegah error `401 Unauthorized` saat proses integrasi berjalan.

## Cara Menjalankan

1. Clone repo.

~~~bash
git clone https://github.com/IAE-2026/Tubes-IAE-Kelompok-6.git
cd Tubes-IAE-Kelompok-6
~~~

2. Jalankan semua container.

~~~bash
docker compose up -d --build
~~~

3. Cek container.

~~~bash
docker compose ps
~~~

Container yang harus hidup:

- smart-parking-api-gateway
- smart-parking-service-a-app
- smart-parking-service-b-app
- smart-parking-service-c-app
- smart-parking-service-a-db
- smart-parking-service-b-db
- smart-parking-service-c-db

## Endpoint Lewat Gateway

- GET /health
- GET /health/service-a
- GET /health/service-b
- GET /health/service-c
- GET /api/v1/locations
- POST /api/v1/locations
- GET /api/v1/transactions
- POST /api/v1/transactions
- POST /api/v1/transactions/{id}/checkout
- POST /api/v1/transactions/{id}/pay
- GET /api/v1/memberships
- POST /api/v1/memberships
- POST /graphql
- GET /graphiql

## Contoh Test Singkat

Ambil token M2M Service A. Command ini menyimpan token ke variabel `$token`.

~~~powershell
$response = curl.exe -s -X POST http://localhost/api/v1/sso/login-m2m `
  -H "Content-Type: application/json" `
  -d '{"api_key":"KEY-MHS-67","nim":"102022400039"}' | ConvertFrom-Json

$token = $response.data.token
~~~

Cek token. Token yang benar biasanya panjang dan diawali `eyJ`.

~~~powershell
$token
~~~

Cek lokasi dengan token asli dari variabel `$token`.

~~~powershell
curl.exe http://localhost/api/v1/locations `
  -H "Authorization: Bearer $token"
~~~

Cek membership dengan token yang sama.

~~~powershell
curl.exe http://localhost/api/v1/memberships `
  -H "Authorization: Bearer $token"
~~~

Cek log integrasi pusat dari Service C setelah endpoint membership dipanggil.

~~~powershell
docker compose logs app_service_c --tail=120 | Select-String -Pattern "Publish Sukses|Gagal|Token M2M"
~~~

Output yang diharapkan:

~~~text
Publish Sukses
~~~

Cek transaksi.

~~~powershell
curl.exe http://localhost/api/v1/transactions -H "X-IAE-KEY: 102022400126"
~~~

## Flow End-to-End yang Sudah Diuji

1. Buat lokasi baru lewat Service A.
2. Service A mengirim SOAP Audit dan menerima receipt number.
3. Buat transaksi di Service B dengan location_id dari Service A.
4. Service B membaca membership dari Service C.
5. Service B menghitung checkout.
6. Service B menyelesaikan payment.
7. Status transaksi berubah menjadi SELESAI.

## Bukti yang Perlu Ditunjukkan Saat Demo

- docker compose ps menampilkan semua container Up.
- GET http://localhost/ menampilkan status gateway TEAM-06.
- POST /api/v1/locations menghasilkan receipt_number dari SOAP Audit.
- GET /api/v1/memberships memicu integrasi event dari Service C.
- Log Service C menampilkan status publish sukses.
- POST /api/v1/transactions memakai location_id dari Service A.
- Checkout memakai base_rate dari Service A.
- Payment menghasilkan status SELESAI.
- GET /api/v1/memberships menampilkan data membership dari Service C.

## Catatan Kontribusi

Repo ini memakai branch service untuk menjaga bukti kontribusi anggota:

- service-a-farid
- service-b-hadid
- service-c-dinda
- main sebagai branch integrasi

Log prompt AI tersedia di folder service masing-masing.
