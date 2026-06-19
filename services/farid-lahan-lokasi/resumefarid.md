Resume Kontribusi Individu

Nama: Farid Maulana
NIM: 102022400039
Service: Service A, Lahan dan Lokasi Parkir
Folder: services/farid-lahan-lokasi

Ringkasan kontribusi

Saya mengerjakan Service A sebagai service lahan dan lokasi parkir. Service ini menangani data lokasi parkir, kapasitas slot, tarif dasar, audit ke sistem pusat, dan pengiriman event saat data lokasi berubah.

Kontribusi ini terlihat pada log commit di folder Service A. Commit yang tercatat untuk Service A adalah:

1. UPDATE LOGIN M2M
2. SSO NIM FARID
3. ENV NIM FARID

Ada juga commit integrasi dari repo kelompok:

1. fix docker

Commit tersebut menyesuaikan Dockerfile agar Service A bisa berjalan dalam Docker Compose kelompok.

Detail kontribusi

1. Menyesuaikan request SSO M2M dengan NIM

Saya menambahkan NIM Farid, yaitu 102022400039, ke proses request token M2M. Service A sekarang mengikuti aturan terbaru dosen. Request token M2M membawa api_key dan nim.

Dampak:
Service A bisa mengambil token SSO M2M dengan identitas yang benar.

2. Menyesuaikan login M2M pada controller SSO

Saya memperbarui alur login M2M di SsoAuthController. Endpoint login M2M menerima api_key dan nim, lalu meneruskan data itu ke SSO dosen.

Dampak:
Pengujian token lewat endpoint Service A bisa berjalan sesuai format terbaru.

3. Menyesuaikan token M2M untuk proses lokasi

Saya memperbarui proses pengambilan bearer token di LocationController. Service A memakai token M2M saat menjalankan proses penting seperti audit dan pengiriman event.

Dampak:
Service A bisa menjalankan integrasi pusat tanpa bergantung pada input token manual dari user.

4. Menambahkan konfigurasi NIM di environment

Saya menambahkan IAE_NIM ke file .env.example Service A. Nilainya memakai NIM 102022400039.

Dampak:
Orang lain bisa menjalankan Service A dengan konfigurasi yang jelas saat clone repo.


Peran Service A dalam sistem kelompok

Service A menyediakan data lokasi parkir untuk flow utama Smart Parking. Service B memakai data ini saat membuat transaksi, menghitung checkout, dan memperbarui slot parkir.

Fungsi utama Service A:

1. GET /api/v1/locations untuk melihat daftar lokasi.
2. GET /api/v1/locations/{id} untuk melihat detail lokasi.
3. POST /api/v1/locations untuk membuat lokasi baru.
4. POST /api/v1/locations/{id}/occupy untuk mengurangi slot tersedia.
5. POST /api/v1/locations/{id}/release untuk mengembalikan slot tersedia.

Hasil teknis yang bisa dicek

1. Service A berjalan di belakang API Gateway.
2. Endpoint lokasi membutuhkan token SSO yang valid.
3. Request token M2M memakai api_key dan nim.
4. Lokasi baru bisa dibuat lewat POST /api/v1/locations.
5. Response lokasi baru membawa receipt_number dari proses audit.
6. Slot parkir bisa berkurang saat transaksi masuk.
7. Slot parkir bisa bertambah kembali saat transaksi selesai.

Nilai kontribusi untuk integrasi kelompok

Service A menjadi sumber data lokasi dan slot parkir. Service ini membuat Service B bisa memakai data lokasi nyata, bukan data mock. Dengan perubahan ini, alur Smart Parking kelompok bisa berjalan dari pemilihan lokasi, transaksi parkir, checkout, sampai payment selesai.
