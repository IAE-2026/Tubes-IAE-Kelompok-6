# Prompt Engineering Log — Tugas 3 IAE
**Service:** Lahan-Lokasi-Service (Service A)  
**NIM:** 102022400039  
**Nama:** Farid Maulana  
**Team:** TEAM-06  
**Tanggal:** 12 Juni 2026

---

## Prompt 1 — Pembuatan SoapAuditService

> Buatkan service class `SoapAuditService` di Laravel yang mengirim SOAP XML request ke endpoint `https://iae-sso.virtualfri.id/soap/v1/audit`. Service harus membuat SOAP Envelope XML dengan tag `<iae:TeamID>`, `<iae:ActivityName>`, dan `<iae:LogContent>` berisi CDATA JSON transaksi. Kirim dengan header `Content-Type: text/xml`, `SOAPAction`, dan `Authorization: Bearer`. Parse response XML untuk extract `<iae:Status>` dan `<iae:ReceiptNumber>`, lalu simpan receipt ke database.

---

## Prompt 2 — Pembuatan AmqpPublisherService

> Buatkan `AmqpPublisherService` yang publish event ke RabbitMQ dosen melalui `https://iae-sso.virtualfri.id/api/v1/messages/publish`. Payload harus `{"message": {...}, "routing_key": "location.created"}`. Message berisi event name, data lokasi, timestamp otomatis via `now()`, source `service-a-lahan-lokasi`, dan team_id dari `.env`.

---

## Prompt 3 — Integrasi SOAP + AMQP di LocationController

> Integrasikan `SoapAuditService` dan `AmqpPublisherService` ke method `store()` di `LocationController`. Saat user POST lokasi baru, otomatis: simpan ke DB → kirim SOAP audit → ambil receipt_number → publish event ke RabbitMQ dengan data lokasi + receipt_number → return response bersih dengan receipt_number.

---

## Prompt 4 — M2M Token agar Pengirim = TEAM-06

> Buat method `obtainBearerToken()` yang selalu login M2M ke `/api/v1/auth/token` dengan `api_key: KEY-MHS-67`. Jangan pakai token Warga karena di dashboard dosen akan muncul sebagai email, bukan TEAM-06. Prioritas: M2M dulu, Warga sebagai fallback.

---

## Prompt 5 — Format Response Bersih Seperti Inventory-Service

> Ubah response `store()` agar bersih tanpa raw SOAP XML. Format: `{ status, message, data: { location: {..., receipt_number}, receipt_number }, meta: { service_name, api_version } }`. Hilangkan key `integration` yang berisi SOAP request/response mentah.

---

## Prompt 6 — Routing Key untuk Label Hijau di Papan

> Tambahkan field `routing_key` pada payload publish ke RabbitMQ agar di papan pengumuman dosen muncul label hijau `location.created`, seperti team lain yang punya `vehicle.dispatched`, `krs.created`, dll.

---

## Prompt 7 — Cleanup Code dan File

> Hapus file .md yang tidak diperlukan untuk tugas (analisis, tutorial, perbaikan log). Bersihkan komentar berlebih di code PHP, sisakan hanya komentar singkat yang to-the-point.
