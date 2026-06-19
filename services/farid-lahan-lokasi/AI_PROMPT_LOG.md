# AI Prompt Log — Tubes IAE Smart Parking

## Farid Maulana - Service A

### Prompt 1
Mengekstrak fungsi Service A keluar dari folder grup asli agar menjadi repositori mandiri yang bersih, serta mempersiapkan environment Docker terisolasi yang berisi container Laravel App dan database MySQL terkelompok.
* **Hasil Perubahan**: Folder dibersihkan dari service lain, tersisa Service A (Laravel) dan file konfigurasi Docker (`Dockerfile` dan `docker-compose.yml`) yang berjalan mandiri pada port 3001.

### Prompt 2
Mendaftarkan middleware otentikasi API Key berbasis string alias dan mengunci rute API agar mematuhi batas minimum spesifikasi kontrak 3 endpoint utama.
* **Hasil Perubahan**: Membuat `APIKeyMiddleware.php` yang memvalidasi header `X-IAE-KEY` bernilai NIM mahasiswa dan mendaftarkannya pada API routes.

### Prompt 3
Memasukkan dokumentasi interaktif OpenAPI Annotations ke dalam seluruh controller.
* **Hasil Perubahan**: Menambahkan anotasi OpenAPI (`#[OA\Get]`, `#[OA\Post]`, dll.) ke dalam method index, show, dan store pada `LocationController.php` untuk autofile swagger docs.

### Prompt 4
Membuat service class `SoapAuditService` di Laravel untuk mengirim log aktivitas penting ke Cloud Dosen dalam bentuk SOAP XML Request.
* **Hasil Perubahan**: Berkas `SoapAuditService.php` dibuat, mampu menyusun SOAP Envelope XML dengan payload CDATA JSON, mengirimkan ke server audit, dan mengambil `ReceiptNumber`.

### Prompt 5
Membuat `AmqpPublisherService` untuk mempublikasikan event asinkron ke RabbitMQ Cloud Dosen menggunakan HTTP wrapper POST.
* **Hasil Perubahan**: Berkas `AmqpPublisherService.php` dibuat, memformat payload event JSON dan mempublikasikannya ke exchange pusat.

### Prompt 6
Mengintegrasikan `SoapAuditService` dan `AmqpPublisherService` ke dalam method `store()` di `LocationController.php`.
* **Hasil Perubahan**: Rangkaian proses transaksi POST lokasi baru menyimpan data secara lokal $\rightarrow$ memicu SOAP Audit untuk mengambil `ReceiptNumber` $\rightarrow$ mempublikasikan event `location.created` ke RabbitMQ $\rightarrow$ mengembalikan respon JSON yang bersih.

### Prompt 7
Membuat token M2M (Machine-to-Machine) via login SSO Dosen agar data pengirim tercatat atas nama tim `TEAM-06`.
* **Hasil Perubahan**: Implementasi method `obtainBearerToken()` yang melakukan request ke `/api/v1/auth/token` menggunakan API Key tim sebelum melakukan integrasi SOAP/AMQP.

### Prompt 8
Menambahkan endpoint untuk mengurangi (`occupy`) dan menambahkan kembali (`release`) ketersediaan slot parkir pada lokasi tertentu.
* **Hasil Perubahan**:
  * Menambahkan rute `POST /v1/locations/{id}/occupy` dan `POST /v1/locations/{id}/release` di `api.php`.
  * Mengimplementasikan method `occupy` (mengurangi `available_spots` dengan validasi sisa slot) dan method `release` (menambah `available_spots` dengan batas maksimum `total_spots`) di `LocationController.php`.

### Prompt 9
Mengintegrasikan penerimaan pesan asinkron dari RabbitMQ untuk pembaruan ketersediaan slot secara asinkron saat menerima event transaksi.
* **Hasil Perubahan**:
  * Menambahkan dependensi `php-amqplib/php-amqplib` ke `composer.json`.
  * Membuat command Artisan `rabbitmq:consume` (`ConsumeRabbitMQ.php`) untuk mendengarkan antrean event `parking.slot.occupied`, `parking.slot.released`, dan `parking.payment.completed`.
  * Membuat endpoint webhook `POST /v1/events/rabbitmq-callback` untuk menyimulasikan penerimaan event asinkron tersebut secara HTTP.
