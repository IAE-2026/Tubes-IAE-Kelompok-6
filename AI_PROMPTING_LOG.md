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


### Prompt 2 — Implementasi Middleware Keamanan & Pembatasan 3 Endpoint
Mendaftarkan middleware otentikasi API Key berbasis string alias dan mengunci rute API agar mematuhi batas minimum spesifikasi kontrak.

* **Input Prompt:**
  > "bagaimana cara mendaftarkan api key di app.php"
  > "pastikan endpoint hanya 3 sesuai contract"

### Prompt 3 — Integrasi Library OpenAPI Annotations
Memasukkan metadata dokumentasi interaktif langsung ke dalam kode pengontrol menggunakan sintaks standar library OpenAPI.

* **Input Prompt:**
  > "pastikan ada library openAPI as oa"

### Prompt 4 — Debugging Error: Skipping Unknown Middleware
Menyelesaikan masalah kegagalan kompilasi berkas Swagger akibat jejak penulisan nama middleware lama yang masih tertinggal di dalam sistem.

* **Input Prompt:**
  > "ErrorException: Skipping unknown App\Http\Middleware\IaeKeyMiddleware"

### Prompt 5 — Debugging Error: Required @OA\Info() Not Found
Mengatasi masalah parser generator dokumen yang melewatkan atau tidak menemukan block informasi utama dokumen API.

* **Input Prompt:**
  > "ErrorException: Required @OA\Info() not found"
*
### Prompt 6 — Sinkronisasi Port Server Pengembangan Lokal
Menyesuaikan konfigurasi endpoint Swagger agar selaras dengan port default lokal yang sedang aktif di terminal komputer pengguna.

* **Input Prompt:**
  > "[http://127.0.0.1:8000]. masih yang itu"

---

## Status Akhir Target Luaran (Learning Outcomes)

- [x] **Fungsionalitas REST (40%)**: Terpenuhi. Terdapat 3 jenis endpoint sesuai standard wrapper JSON.
- [x] **API Documentation (20%)**: Terpenuhi. Dokumen OpenAPI berhasil di-compile tanpa error.
- [x] **Security Standard (10%)**: Terpenuhi. Menggunakan `APIKeyMiddleware` melalui validasi header `X-IAE-KEY` bernilai NIM `102022400039`.
- [x] **Log Kemajuan**: Berkas log ini disimpan di root folder repositori.