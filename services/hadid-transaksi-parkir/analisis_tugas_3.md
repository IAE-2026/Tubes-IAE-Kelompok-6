Kelompok 6
2. **Service B (Transaction & Payment Service) [Hadid, saya sendiri]** : menangani transaksi parkir (tap-in, checkout, pay) dan pembayaran (service ini).

### alur bisnis Service B (Transaction & Payment Service):

1. **Tapping Masuk (Tap-In)** : User masuk ke area parkir dengan memicu mutation `createTransaction(input: {location_id, member_card_id})`. Sistem akan mengecek slot kosong di Service A dan validitas kartu member di Service C. Jika slot tersedia, sistem menandai slot terpakai di Service A dan membuat transaksi dengan status `BERLANGSUNG`.
2. **Tapping Keluar (Checkout)** : Saat user akan keluar, kasir/user memicu mutation `checkoutTransaction(id, voucher_code)`. Sistem menghitung durasi parkir berdasarkan selisih waktu masuk dan keluar, memanggil tarif dasar dari Service A, serta menghitung potongan diskon member atau voucher dari Service C. Transaksi diperbarui menjadi status `SUDAH_CHECKOUT` beserta nilai `total_amount`.
3. **Autentikasi & Otorisasi SSO** : Saat user melakukan pembayaran via mutation `payTransaction(id, payment_method)`, request menyertakan token JWT SSO dosen. Middleware memvalidasi JWT ke SSO dosen, memetakan cloud role ke role lokal (`finance-admin`, `cashier`, `service-account`, `customer`), dan menolak request jika role lokal tidak sesuai atau tidak berhak memproses pembayaran.
4. **Eksekusi Transaksi Kritis** : Memproses transaksi pembayaran parkir. Sistem mengubah status transaksi menjadi `SELESAI` (Paid), melepaskan slot parkir di Service A, dan mencatat penggunaan voucher/member di Service C. Transaksi ini diaudit secara sinkron ke sistem Legacy SOAP dosen, di mana `ReceiptNumber` yang diterima akan disimpan di database lokal.
5. **Penyebaran Event** : Setelah transaksi berhasil diselesaikan, service mempublikasikan event `PaymentProcessed` ke message broker RabbitMQ dosen secara asinkron agar service lain (seperti Finance untuk pelaporan dan departemen Notifikasi untuk struk) mengetahui pembayaran telah lunas.
6. **Selesai** : Respons sukses dikembalikan ke user beserta rincian transaksi, receipt number audit SOAP, dan status publish RabbitMQ.

**Posisi service B didalam alur:**

Sebagai **Pengelola Siklus Hidup Transaksi Parkir**. Dimana service ini menjadi penanggung jawab tunggal untuk mencatat data tapping masuk, menghitung kalkulasi tarif keluar secara presisi, serta menjamin kevalidan transaksi keuangan parkir. Semua perubahan status parkir wajib melalui endpoint transaksi service ini.

## Batasan Service

**Tanggung jawab service ini hanya sebatas:**

- CRUD dan pencatatan transaksi parkir (`transactions`).
- Perhitungan durasi parkir dan tarif dasar transaksi.
- Validasi hak akses role SSO lokal dan eksekusi pembayaran transaksi (transaksi kritis).
- Audit transaksi pembayaran ke Legacy SOAP dosen dan penyimpanan `ReceiptNumber`.
- Publikasi event `PaymentProcessed` ke message broker RabbitMQ pusat.

**Di luar tanggung jawab service ini:**

- Penyediaan master data lokasi dan jumlah kapasitas slot parkir (milik Service A).
- Pengelolaan data membership dan masa berlaku voucher (milik Service C).

Pada service ini service lain hanya bisa berkomunikasi melalui REST API (HTTP Client) atau GraphQL. Service lain tidak ada akses langsung ke database service ini, dan begitu juga sebaliknya service ini tidak bisa membaca database service lain secara langsung.


## Pemilihan Transaksi Kritis

Transaksi yang dipilih: **Pembayaran Transaksi Parkir (`payTransaction`)**

```graphql
mutation {
  payTransaction(id: "trx_00X", payment_method: "qris") {
    status
    message
    transaction {
      id
      status
      audit_receipt_number
    }
  }
}
```

Dipanggil oleh kasir atau warga saat menyelesaikan transaksi parkir.

Headers Service:

```
Authorization: Bearer <Token JWT dari SSO>
X-IAE-KEY: 102022400126
Content-Type: application/json
```

Efek dari transaksi ini adalah status transaksi berubah menjadi `SELESAI` (Paid), slot parkir di Service A dibebaskan, voucher di Service C ditandai terpakai, audit log terkirim ke SOAP Dosen untuk mendapatkan `ReceiptNumber`, dan terakhir event `PaymentProcessed` dipublikasikan ke message broker RabbitMQ.



## Alasan Pemilihan Transaksi Kritis

Pembayaran parkir memenuhi seluruh kriteria transaksi kritis pada rubrik penilaian (service masuk ke kategori **keuangan/finansial** dan transaksi ini bersifat **state-changing**):

1. **Mengubah state keuangan secara tetap.** Status transaksi diubah dari `SUDAH_CHECKOUT` menjadi `SELESAI` (Paid) secara permanen di database. Ini adalah titik perubahan state finansial utama yang tidak boleh mengalami inkonsistensi.
2. **Transaksi ini berdampak langsung pada finansial.** Setiap pembayaran yang terproses berhubungan langsung dengan aliran dana masuk. Kesalahan pada transaksi pembayaran (seperti status lunas tanpa pembayaran riil) berisiko menimbulkan kerugian pendapatan parkir atau kegagalan operasional di lapangan.
3. **Rawan terjadi duplikasi pembayaran (Race Condition).** Jika pengguna memicu request pembayaran ganda secara bersamaan untuk satu transaksi parkir, sistem harus memastikan status transaksi dikunci (*row lock*) sehingga hanya satu request pembayaran yang diproses untuk menghindari *double payment*.
4. **Riwayat Transaksi harus ter-record secara ketat.** Karena berdampak langsung ke finansial, setiap eksekusi pembayaran harus dilaporkan secara sinkron ke sistem Legacy SOAP dosen untuk mendapatkan bukti audit berupa `ReceiptNumber` **sebelum transaksi selesai secara final**. Jika SOAP gagal merespons sukses, transaksi di-rollback demi menjaga konsistensi keuangan.
5. **Transaksi harus diketahui service lain.** Perubahan status transaksi menjadi lunas harus disebarkan ke departemen lain secara asinkron (rekap keuangan oleh Finance dan pencetakan struk oleh Notification).

### Operasi lain yang tidak dipilih:

`query { transactions }` Operasi ini bersifat read-only, tidak mengubah state transaksi.

`mutation { createTransaction }` Operasi ini hanya menginisiasi awal transaksi parkir (tap-in) dengan status `BERLANGSUNG` dan belum memiliki implikasi keuangan final.

`mutation { checkoutTransaction }` Operasi ini hanya melakukan kalkulasi biaya parkir dan belum mengubah status keuangan menjadi selesai/paid.


## Sequence Diagram Internal

![sequence](Sequence%20Diagram1.png)
