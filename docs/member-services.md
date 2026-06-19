# Team dan Service Kelompok 6

Dokumen ini menjelaskan pembagian service, batas tanggung jawab, dan kontrak integrasi antar-service pada sistem Smart Parking TEAM-06.

## Mapping Anggota

| Komponen | Service | Developer | NIM | Teknologi |
| --- | --- | --- | --- | --- |
| Service A | Lahan dan Lokasi Parkir | Farid Maulana | 102022400039 | Laravel, PHP, MySQL |
| Service B | Transaksi Parkir dan Payment | Hadid Hamar | 102022400126 | Laravel, PHP, MySQL, REST, GraphQL |
| Service C | Membership dan Voucher | Dinda Juniar | 102022400023 | Laravel, PHP, MySQL |

## Service A - Lahan dan Lokasi Parkir

Service A mengelola data lokasi parkir. Service ini menyimpan nama lokasi, alamat, jenis parkir, jumlah slot, slot tersedia, dan tarif dasar.

Detail teknis:

- Folder: services/farid-lahan-lokasi
- Docker hostname: smart-parking-service-a-app
- Port internal: 3001
- Database: smart_parking_service_a
- Tabel utama: locations, roles, audit_receipts
- Endpoint utama: /api/v1/locations
- Auth: Bearer JWT dari SSO dosen

Tugas integrasi:

- Memvalidasi JWT lewat JWKS SSO dosen.
- Mengambil token M2M dengan api_key dan nim 102022400039.
- Mengirim SOAP Audit saat data lokasi dibuat.
- Mempublish event location.created ke RabbitMQ publisher dosen.
- Menyediakan endpoint occupy dan release untuk update slot.

## Service B - Transaksi Parkir dan Payment

Service B mengelola alur transaksi parkir. Service ini menangani check-in, checkout, perhitungan biaya, benefit membership, dan payment.

Detail teknis:

- Folder: services/hadid-transaksi-parkir
- Docker hostname: smart-parking-service-b-app
- Port internal: 3002
- Database: service_b
- Tabel utama: transactions, users, roles, audit_logs
- Endpoint utama: /api/v1/transactions
- Endpoint GraphQL: /graphql dan /graphiql
- Auth internal: X-IAE-KEY 102022400126

Tugas integrasi:

- Memanggil Service A lewat SERVICE_A_URL untuk membaca lokasi.
- Memanggil Service A untuk mengurangi slot saat check-in.
- Memanggil Service A untuk mengembalikan slot saat payment selesai.
- Memanggil Service C lewat SERVICE_C_URL untuk membaca membership.
- Mengambil token M2M dengan api_key dan nim 102022400126 saat memanggil service yang butuh Bearer JWT.

## Service C - Membership dan Voucher

Service C mengelola data membership dan voucher. Service ini memberi data diskon yang dipakai Service B saat checkout transaksi.

Detail teknis:

- Folder: services/dinda-membership-voucher
- Docker hostname: smart-parking-service-c-app
- Port internal: 8000
- Database: smart_parking
- Tabel utama: memberships, vouchers, membership_usages
- Endpoint utama: /api/v1/memberships
- Auth: Bearer JWT dari SSO dosen

Tugas integrasi:

- Memvalidasi JWT lewat JWKS SSO dosen.
- Mengambil token M2M dengan api_key dan nim 102022400023.
- Mengirim SOAP Audit saat membership dibuat.
- Mempublish event membership.created ke RabbitMQ publisher dosen.
- Menyediakan data membership untuk Service B.

## Kontrak Komunikasi Antar-Service

Alur check-in transaksi:

1. Client mengirim POST /api/v1/transactions ke API Gateway.
2. Gateway meneruskan request ke Service B.
3. Service B membaca lokasi ke Service A lewat GET /api/v1/locations/{id}.
4. Service B membaca membership ke Service C lewat GET /api/v1/memberships/{member_code}.
5. Service B memanggil Service A untuk occupy slot.
6. Service B membuat transaksi dengan status BERLANGSUNG.

Alur checkout dan payment:

1. Client mengirim POST /api/v1/transactions/{id}/checkout.
2. Service B mengambil tarif dasar dari Service A.
3. Service B mengambil diskon membership dari Service C.
4. Service B menghitung total bayar.
5. Client mengirim POST /api/v1/transactions/{id}/pay.
6. Service B mengubah status transaksi menjadi SELESAI.
7. Service B memanggil Service A untuk release slot.

## Mermaid Flow

~~~mermaid
sequenceDiagram
    autonumber
    actor Client
    participant Gateway as API Gateway
    participant B as Service B Transaksi
    participant A as Service A Lokasi
    participant C as Service C Membership
    participant SSO as SSO Dosen
    participant SOAP as SOAP Audit
    participant MQ as RabbitMQ Publisher

    Client->>Gateway: POST /api/v1/locations
    Gateway->>A: Forward request
    A->>SSO: POST /api/v1/auth/token api_key + nim
    SSO-->>A: M2M JWT
    A->>SOAP: Send audit log
    SOAP-->>A: ReceiptNumber
    A->>MQ: Publish location.created
    A-->>Gateway: Location created

    Client->>Gateway: POST /api/v1/transactions
    Gateway->>B: Forward request
    B->>A: GET /api/v1/locations/{id}
    A-->>B: Location data
    B->>C: GET /api/v1/memberships/{member_code}
    C-->>B: Membership data
    B->>A: POST /api/v1/locations/{id}/occupy
    B-->>Gateway: Transaction created

    Client->>Gateway: POST /api/v1/transactions/{id}/checkout
    Gateway->>B: Forward request
    B-->>Gateway: Total amount

    Client->>Gateway: POST /api/v1/transactions/{id}/pay
    Gateway->>B: Forward request
    B->>A: POST /api/v1/locations/{id}/release
    B-->>Gateway: Status SELESAI
~~~

## Standar Response JSON

Response sukses:

~~~json
{
  "status": "success",
  "message": "Data berhasil diambil",
  "data": {},
  "meta": {
    "service_name": "Nama-Service",
    "api_version": "v1"
  }
}
~~~

Response error:

~~~json
{
  "status": "error",
  "message": "Pesan error",
  "errors": null
}
~~~