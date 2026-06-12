**Pemilihan Transaksi Penting dan Transaksi yang Harus Disebar**

Di Service A (Lahan & Lokasi) ini ada tiga endpoint utama, yaitu GET /api/v1/locations untuk melihat daftar lokasi parkir, GET /api/v1/locations/{id} untuk melihat detail dari satu lokasi tertentu (seperti tarif dan tipe parkir VIP/regular), dan POST /api/v1/locations untuk menambahkan data master lahan baru.

Dari ketiga pilihan tersebut, endpoint yang saya pilih sebagai transaksi penting untuk SOAP Audit sekaligus transaksi yang harus disebar lewat RabbitMQ adalah POST /api/v1/locations.

**Alasan Pemilihan**

Alasannya karena POST /api/v1/locations adalah satu-satunya endpoint yang melakukan perubahan data (create). Sementara, dua endpoint GET lainnya hanya dipakai untuk membaca data saja, jadi tidak ada perubahan status data yang perlu dicatat atau dioper ke service lain. Karena POST ini menambahkan lokasi parkir baru, otomatis ada data masuk yang wajib dipertanggungjawabkan ke akuratannya.

**Alur Bisnis**

1. Request Token (Autentikasi)
Proses diawali dengan mengirimkan kredensial warga berupa email dan password, dan juga menggunakan API Key untuk token M2M (TEAM-06), ke SSO Server Cloud Pusat melalui Postman. Server kemudian memvalidasi akun tersebut dan mengirimkan kembali token JWT yang nantinya digunakan sebagai akses untuk endpoint yang dikunci.

2. Validasi Awal (JWKS)
Untuk memastikan token yang diterima sah, service lokal akan mengambil public key dari JWKS SSO Server agar memverifikasi si JWT tersebut. Jika proses verifikasi ini aman dan berhasil, data profil pengguna akan dikirimkan balik ke sistem.

3. Kirim Data Lokasi & Verifikasi Token
Setelah memegang Bearer JWT, request POST dikirim ke /api/v1/locations dengan membawa data lokasi baru seperti nama gedung, alamat, kapasitas, dan tarif. Di sisi server, komponen JWT Verifier akan mengecek token tersebut, jika salah atau kedaluwarsa, sistem otomatis menolak dengan error 401 Unauthorized, namun jika valid, request diteruskan ke Location Service.

4. Simpan Data Lokal
Begitu request diterima, Location Service langsung memproses dan menyimpan data lokasi baru tersebut ke dalam Database Lokal. Setelah data berhasil masuk, sistem akan menerbitkan konfirmasi berupa location_id yang dibuat secara otomatis.

5. Pencatatan SOAP Audit
Setelah sukses tersimpan di lokal, Location Service memanggil fungsi SOAP Audit untuk mengirim log transaksi yang dibungkus dalam SOAP XML Envelope ke Cloud Pusat. Cloud Pusat kemudian membalas dengan mengirimkan ReceiptNumber, yang langsung disimpan ke Database Lokal sebagai bukti audit yang sah.

6. Publish ke RabbitMQ
Langkah berikutnya, Location Service memanggil Event Publisher untuk menyebarkan event location.created beserta data lengkap lokasinya ke RabbitMQ. Pesan ini dikirimkan ke bagian exchange dan sistem akan menunggu konfirmasi hingga proses publish dinyatakan berhasil.

7. Response Akhir
Setelah seluruh rangkaian proses di atas selesai tanpa ada kendala, Location Service akan mengirimkan respon akhir 201 Created kembali ke pengguna. Respon ini menampilkan seluruh data master lokasi yang baru terdaftar, lengkap dengan receipt_number sebagai bukti final.


**Batasan Service**

Service A ini fokusnya hanya untuk mengelola data master dari lokasi parkir saja. Jadi, service ini tidak ikut campur dalam mengurus data kendaraan, pencatatan jam masuk/keluar parkir, ataupun sistem pembayarannya. Ketika ada lokasi baru yang diinput, Service A cuma menyimpan informasi yang sifatnya statis seperti nama, alamat, tipe, kapasitas slot, dan tarif dasarnya.

Selain itu, Service A punya ketergantungan yang tinggi pada Cloud Pusat untuk urusan autentikasi (SSO/JWKS), pencatatan transaksi (SOAP), dan penyebaran pesan (RabbitMQ). Kalau Cloud Pusat sedang bermasalah atau tidak bisa diakses, otomatis fitur autentikasi dan integrasi sistemnya akan gagal, meskipun proses simpan data ke database lokal sebenarnya masih bisa berjalan.

