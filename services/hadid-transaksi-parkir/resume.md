Resume Kontribusi Individu

Nama: Hadid Hamar
NIM: 102022400126
Service: Service B, Transaksi Parkir dan Payment
Folder: services/hadid-transaksi-parkir

Ringkasan kontribusi

Saya mengerjakan Service B sebagai service transaksi parkir dan payment. Service ini menangani proses tapping masuk, checkout, perhitungan biaya, diskon membership, dan pembayaran akhir.

Kontribusi ini terlihat pada log commit di folder Service B. Commit yang tercatat atas nama Hadidzz adalah:

1. 1ab5a11, tambahan nim
2. d8559a2, tambah nim saya
3. ef94902, m2mtoken perbaikan
4. 567cd8e, nim hadid
5. e3c0652, koneksi service b dan c

Detail kontribusi

1. Menyesuaikan identitas integrasi SSO

Saya menambahkan NIM saya sendiri, yaitu 102022400126, ke konfigurasi Service B. Perubahan ini membuat Service B mengikuti aturan terbaru dosen. Request token M2M sekarang membawa api_key dan nim.

Dampak:
Service B bisa meminta token SSO M2M dengan identitas yang benar.

2. Memperbaiki proses token M2M

Saya memperbaiki proses pengambilan token M2M di IaeSsoClient. Service B memakai token ini untuk kebutuhan integrasi pusat, seperti audit dan pengiriman event.

Dampak:
Service B tidak hanya bergantung pada token dari user. Service bisa mengambil token M2M sendiri saat perlu memanggil sistem pusat.

3. Menghubungkan Service B dengan Service A

Saya memperbaiki koneksi Service B ke Service A melalui SmartParkingGateway. Service B bisa membaca data lokasi parkir, mengambil tarif dasar, dan memperbarui slot parkir saat transaksi berjalan.

Contoh hasil:
Saat transaksi memakai location_id dari Service A, checkout memakai base_rate dari lokasi tersebut.

4. Menghubungkan Service B dengan Service C

Saya memperbaiki koneksi Service B ke Service C. Service B bisa membaca data membership dan memakai diskon member saat menghitung total pembayaran.

Contoh hasil:
Member MEM001 mendapat diskon membership saat checkout.

5. Menjaga alur transaksi end-to-end

Saya memastikan alur transaksi berjalan dari tapping masuk, checkout, sampai payment selesai.

Alur yang diuji:
1. User membuat transaksi parkir.
2. Service B membaca lokasi dari Service A.
3. Service B membaca membership dari Service C.
4. Service B menghitung durasi, tarif, diskon, dan total bayar.
5. Service B menyelesaikan payment.
6. Status transaksi berubah menjadi SELESAI.

Hasil teknis yang bisa dicek

1. GET /api/v1/transactions menampilkan data transaksi.
2. POST /api/v1/transactions membuat transaksi tapping masuk.
3. POST /api/v1/transactions/{id}/checkout menghitung biaya parkir.
4. POST /api/v1/transactions/{id}/pay menyelesaikan pembayaran.
5. Service B memakai data lokasi dari Service A.
6. Service B memakai data membership dari Service C.
7. Service B memakai konfigurasi NIM 102022400126 untuk kebutuhan SSO M2M.

Nilai kontribusi untuk integrasi kelompok

Service B menjadi pusat alur bisnis Smart Parking. Service ini menghubungkan data lokasi dari Farid, data membership dari Dinda, dan proses pembayaran dari Hadid. Dengan perubahan ini, sistem kelompok bisa berjalan sebagai satu flow end-to-end melalui API Gateway.
