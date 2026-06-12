# Log Prompt Engineering bersama AI — Tugas 3

Dokumen ini adalah bukti akuntabilitas progres (Modul 4, bobot 10%). Berisi rekap prompt yang digunakan saat mengeksplorasi dan membangun integrasi Service B ke tiga sistem terpusat dosen (SSO, SOAP Audit, RabbitMQ) di atas GraphQL (Laravel + Lighthouse).

## Informasi Proyek

| Parameter | Nilai |
|-----------|-------|
| Mahasiswa | Hadid Hamar |
| Absen | 13 |
| Layanan | Service B — Transaksi & Pembayaran (Smart Parking) |
| Stack | Laravel + Lighthouse (GraphQL), firebase/php-jwt |
| Tujuan | Modul 1 SSO, Modul 2 SOAP, Modul 3 RabbitMQ |

---

## Sesi 1 — Perancangan & pemilihan transaksi kritis

**Prompt:**
> "Saya punya mini-service Smart Parking (Service B: transaksi & pembayaran) berbasis Laravel. Untuk Tugas 3 IAE saya harus mengintegrasikannya ke SSO, audit SOAP, dan RabbitMQ pusat. Transaksi mana yang paling tepat ditetapkan sebagai 'transaksi kritis state-changing' dan kenapa?"

**Inti jawaban yang dipakai:** Pembayaran (`payTransaction` → status `SELESAI`) dipilih karena bersifat final secara finansial, melepas sumber daya (slot/voucher), dan tidak boleh terjadi tanpa jejak audit. Tahap tap-in/checkout tidak kritis karena belum final. → ditulis ke `analisis_tugas_3.md`.

**Prompt lanjutan:**
> "Bedakan kapan harus pakai SOAP (sinkron) vs RabbitMQ (asinkron) untuk peristiwa pembayaran ini."

**Hasil:** SOAP untuk bukti audit otoritatif (butuh ReceiptNumber, sinkron); RabbitMQ untuk broadcast event ke banyak departemen tanpa menunggu (asinkron, fire-and-forget).

---

## Sesi 2 — Modul 1: Federated SSO & pemetaan role

**Prompt:**
> "Di Laravel, bagaimana cara memverifikasi JWT RS256 yang diterbitkan server lain menggunakan endpoint JWKS-nya? Saya pakai firebase/php-jwt."

**Hasil:** Gunakan `JWK::parseKeySet($jwks)` lalu `JWT::decode($token, $keys)`; set `JWT::$leeway` untuk toleransi clock-skew; refresh JWKS sekali bila `kid` tidak ditemukan (key rotation). → `app/Services/JwtVerifier.php`.

**Prompt:**
> "Bagaimana pola yang baik memetakan klaim JWT (role/scope, atau realm_access ala Keycloak) ke tabel roles lokal, lalu provision user otomatis?"

**Hasil:** `RoleMapper` mengumpulkan kandidat klaim role/scope, mencocokkan ke `claim_map` berurutan privilege, fallback ke tipe token, lalu default. User di-`updateOrCreate` berdasar `iae_subject` (klaim `sub`) dan role di-`syncWithoutDetaching`. → `app/Services/RoleMapper.php`, migrasi `users`/`roles`/`role_user`.

**Prompt:**
> "Di Lighthouse, bagaimana menambahkan middleware kustom yang berjalan pada setiap request /graphql namun tidak memblokir query publik?"

**Hasil:** Daftarkan middleware pada `config('lighthouse.route.middleware')`; buat resolusi identitas bersifat non-blocking dan tegakkan otorisasi role di resolver mutation kritis. → `config/lighthouse.php`, `app/Http/Middleware/ResolveIaeIdentity.php`.

---

## Sesi 3 — Modul 2: SOAP XML Client

**Prompt:**
> "Beri saya cara membangun SOAP Envelope XML yang valid dan aman dari escaping di PHP, dengan satu elemen LogContent berisi CDATA JSON."

**Hasil:** Gunakan `DOMDocument` + `createElementNS` untuk namespace `soap`/`iae`, dan `createCDATASection` untuk membungkus JSON. Lebih aman daripada string concat. → `SoapAuditClient::buildEnvelope()`.

**Prompt:**
> "Bagaimana parsing respons XML untuk mengambil <iae:ReceiptNumber> dan <iae:Status> tanpa peduli prefix namespace?"

**Hasil:** Regex toleran prefix `(?:[a-z0-9]+:)?ReceiptNumber`, lalu `html_entity_decode`. Disimpan ke `audit_logs` dan `transactions.audit_receipt_number`.

---

## Sesi 4 — Modul 3: AMQP Publisher

**Prompt:**
> "Endpoint dosen mempublish ke RabbitMQ lewat HTTP POST /api/v1/messages/publish (Bearer). Bagaimana struktur payload event yang rapi dan bagaimana memastikan kegagalan publish tidak menggagalkan transaksi utama?"

**Hasil:** Bungkus event dengan metadata (`exchange`, `routing_key`, `message_id`, `occurred_at`, `data`), pola fire-and-forget: catat status (`PUBLISHED`/`FAILED`) tetapi jangan lempar error yang membatalkan pembayaran yang sudah final. → `app/Services/MessageBrokerClient.php`.

---

## Sesi 5 — Orkestrasi & GraphQL schema

**Prompt:**
> "Susun mutation GraphQL Lighthouse `payTransaction` yang mengorkestrasi berurutan: cek role SSO → SOAP audit → publish RabbitMQ, dan kembalikan bukti tiap langkah."

**Hasil:** Resolver `PayTransaction` memvalidasi `IaeIdentity::canPay()`, menjalankan `TransactionService::pay()`, lalu `SoapAuditClient::send()` dan `MessageBrokerClient::publish()`, mengembalikan tipe `PaymentResult { transaction, audit, event }`. → `graphql/schema.graphql`, `app/GraphQL/Mutations/PayTransaction.php`.

---

## Catatan Verifikasi Mandiri

- Seluruh kode di-lint (`php -l`) dan schema GraphQL diperiksa strukturnya sebelum commit.
- Token M2M & JWKS di-cache untuk mengurangi panggilan berulang ke server pusat.
- Kredensial sensitif (API key, password warga) tidak ditanam di kode — seluruhnya dibaca dari `.env` via `config/iae.php`.
- Output AI tidak diterima mentah: penamaan, struktur folder, dan konvensi disesuaikan dengan basis kode Service B yang sudah ada (Tugas 2).
