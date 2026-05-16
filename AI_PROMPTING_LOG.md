# AI Prompt Log - Service A (Lahan & Lokasi)

Rekap penggunaan AI dalam pengembangan backend Service A - Lahan & Lokasi untuk Tugas Besar Integrasi Aplikasi Enterprise. File ini telah dirombak untuk berfokus penuh secara mandiri pada Service A (menghapus dependensi lokal Service B & C).

## Informasi Proyek

| Parameter | Value |
|-----------|-------|
| **Mata Kuliah** | BBK2HAB3 - Integrasi Aplikasi Enterprise |
| **Kelompok** | 6 (Fokus Mandiri Service A) |
| **Tanggal Pembaruan** | 16 Mei 2026 |
| **Teknologi Backend** | Laravel 11 |
| **Mahasiswa** | Farid Maulana (NIM: 102022400039) |

---

## Log Prompting & Alur Kerja Pengembangan

### Prompt 1 — Isolasi dan Refactor Project ke Laravel Mandiri
Mengekstrak fungsi Service A keluar dari folder grup asli agar menjadi repositori mandiri yang bersih, serta mempersiapkan environment Docker terisolasi yang berisi container Laravel App dan database MySQL terkelompok.

* **Input Prompt:**
  > "karena ini tugasnya di kerjakan perservice, dan di push ke github masing masing peserta, di case ini saya mengerjakan hanya service a saja, dan yang service b dan c itu teman saya. saya mau anda merombak project ini berfokuskan service a saja jadi service b dan c itu di hapus saja, keluarkan semua file yang ada pada folder service a dan lakukan perubahan. pastikan sesuai ketentuan read @Context1.md dan @Context2.md. untuk container docker pastikan berjalan di local docker ada app dan mysql nya ketika di expand, remove context1.md and context2.md also howtouse.md and INTEGRATION-SEPARATE-REPOS.md. and HOW-TO-USE.md. pastikan dalam bentuk laravel, berikan step setiap pengerjaan dan command command nya"
* **Output AI:**
  - Menyusun struktur root repository mandiri khusus Service A.
  - Membuat kerangka database migration tabel `locations` dengan skema *auto-increment custom ID* (`loc_001`).
  - Menyediakan berkas `Dockerfile` dan `docker-compose.yml` terintegrasi yang menggabungkan container `smart-parking-service-a-app` dan `smart-parking-service-a-db` (MySQL 8.0) dalam satu network Docker Desktop yang rapi ketika di-expand.

### Prompt 2 — Implementasi Middleware Keamanan & Pembatasan 3 Endpoint
Mendaftarkan middleware otentikasi API Key berbasis string alias dan mengunci rute API agar mematuhi batas minimum spesifikasi kontrak.

* **Input Prompt:**
  > "bagaimana cara mendaftarkan api key di app.php"
  > "pastikan endpoint hanya 3 sesuai contract"
* **Output AI:**
  - Menunjukkan cara meregistrasikan `APIKeyMiddleware` ke dalam objek `$middleware->alias()` pada file `bootstrap/app.php` bawaan Laravel 11.
  - Membatasi dan membersihkan rute pada `routes/api.php` agar hanya mengekspos tepat 3 endpoint fungsional dengan skema pembungkus JSON standar:
    1. `GET /api/v1/locations` (Collection)
    2. `GET /api/v1/locations/{id}` (Resource)
    3. `POST /api/v1/locations` (Action)

### Prompt 3 — Integrasi Library OpenAPI Annotations
Memasukkan metadata dokumentasi interaktif langsung ke dalam kode pengontrol menggunakan sintaks standar library OpenAPI.

* **Input Prompt:**
  > "pastikan ada library openAPI as oa"
* **Output AI:**
  - Mengimpor namespace `use OpenApi\Annotations as OA;` di bagian atas controller.
  - Melengkapi blok dokumentasi `@OA\Info`, `@OA\Server`, `@OA\SecurityScheme`, `@OA\Get`, dan `@OA\Post` yang bersih dari komentar baris penjelas kode program biasa.

### Prompt 4 — Debugging Error: Skipping Unknown Middleware
Menyelesaikan masalah kegagalan kompilasi berkas Swagger akibat jejak penulisan nama middleware lama yang masih tertinggal di dalam sistem.

* **Input Prompt:**
  > "ErrorException: Skipping unknown App\Http\Middleware\IaeKeyMiddleware"
* **Output AI:**
  - Menganalisis sisa pelacakan berkas dan memberikan instruksi pembersihan cache rute internal Laravel.
  - Mengganti seluruh referensi teks `IaeKeyMiddleware` lama menjadi `APIKeyMiddleware` yang baru secara konsisten di `routes/api.php` dan `bootstrap/app.php`.

### Prompt 5 — Debugging Error: Required @OA\Info() Not Found
Mengatasi masalah parser generator dokumen yang melewatkan atau tidak menemukan block informasi utama dokumen API.

* **Input Prompt:**
  > "ErrorException: Required @OA\Info() not found"
* **Output AI:**
  - Menemukan penyebab berupa kegagalan pemindaian (scanning) pada kelas *abstract* bawaan Laravel 11.
  - Solusi: Memindahkan seluruh dekorator utama `@OA\Info()` dari `Controller.php` dan menaruhnya tepat di atas deklarasi kelas fisik non-abstract `LocationController.php`.
  - Memperbaiki kesalahan penulisan spasi di antara tanda bintang komentar (`* * @OA`) menjadi format baku dokumen (`* @OA`).

### Prompt 6 — Sinkronisasi Port Server Pengembangan Lokal
Menyesuaikan konfigurasi endpoint Swagger agar selaras dengan port default lokal yang sedang aktif di terminal komputer pengguna.

* **Input Prompt:**
  > "[http://127.0.0.1:8000]. masih yang itu"
* **Output AI:**
  - Mengubah nilai properti `url` pada dekorator `@OA\Server` menjadi `http://127.0.0.1:8000/api/v1`.
  - Memberikan panduan perintah eksekusi standar untuk menjalankan kompilasi dokumen di port default:
    ```bash
    php artisan l5-swagger:generate
    php artisan serve
    ```

---

## Status Akhir Target Luaran (Learning Outcomes)

- [x] **Fungsionalitas REST (40%)**: Terpenuhi. Terdapat 3 jenis endpoint sesuai standard wrapper JSON.
- [x] **API Documentation (20%)**: Terpenuhi. Dokumen OpenAPI berhasil di-compile tanpa error.
- [x] **Security Standard (10%)**: Terpenuhi. Menggunakan `APIKeyMiddleware` melalui validasi header `X-IAE-KEY` bernilai NIM `102022400039`.
- [x] **Log Kemajuan**: Berkas log ini disimpan di root folder repositori.