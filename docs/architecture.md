# Arsitektur Sistem Kelompok 6

Dokumen ini menjelaskan arsitektur integrasi Smart Parking TEAM-06. Fokusnya ada pada API Gateway, komunikasi antar-service, dan integrasi dengan infrastruktur dosen.

## Komponen Utama

Sistem terdiri dari enam bagian:

1. Client atau Postman.
2. API Gateway Nginx.
3. Service A untuk lokasi parkir.
4. Service B untuk transaksi dan payment.
5. Service C untuk membership dan voucher.
6. Infrastruktur dosen untuk SSO, SOAP Audit, dan RabbitMQ.

## Diagram Komponen

~~~mermaid
graph TD
    Client[Client atau Postman] -->|HTTP port 80| Gateway[API Gateway Nginx]

    subgraph DockerNetwork[Docker network smart_parking_net]
        Gateway -->|/api/v1/locations| A[Service A Lahan dan Lokasi]
        Gateway -->|/api/v1/transactions| B[Service B Transaksi dan Payment]
        Gateway -->|/api/v1/memberships| C[Service C Membership dan Voucher]

        A --> DBA[(MySQL Service A)]
        B --> DBB[(MySQL Service B)]
        C --> DBC[(MySQL Service C)]

        B -->|GET lokasi dan update slot| A
        B -->|GET membership| C
    end

    subgraph CloudDosen[Infrastruktur Dosen]
        SSO[SSO JWT dan JWKS]
        SOAP[SOAP Audit]
        MQ[RabbitMQ Publisher]
    end

    A -->|M2M token api_key + nim| SSO
    A -->|Audit lokasi| SOAP
    A -->|Publish location.created| MQ

    B -->|M2M token api_key + nim| SSO
    C -->|M2M token api_key + nim| SSO
    C -->|Audit membership| SOAP
    C -->|Publish membership.created| MQ
~~~

## Aturan Gateway

Client hanya mengakses API lewat http://localhost. Container service tidak membuka port langsung ke host.

Routing gateway:

- /api/v1/locations ke Service A.
- /api/v1/sso ke Service A.
- /api/v1/transactions ke Service B.
- /graphql ke Service B.
- /graphiql ke Service B.
- /api/v1/memberships ke Service C.
- /api/sso ke Service C.

Gateway meneruskan header penting:

- Authorization
- X-IAE-KEY
- Host
- X-Real-IP
- X-Forwarded-For
- X-Forwarded-Proto

## Penggunaan SOAP

SOAP dipakai untuk audit transaksi penting. Pola ini cocok karena audit butuh format kontrak yang jelas dan bukti receipt.

Service yang memakai SOAP:

- Service A saat membuat lokasi baru.
- Service C saat membuat membership baru.

Contoh bukti hasil test lokal:

~~~json
{
  "receipt_number": "IAE-LOG-2026-A4C6D4B1"
}
~~~

## Penggunaan RabbitMQ

RabbitMQ dipakai untuk event yang tidak harus memblokir response utama. Service tetap bisa memberi response walau event publisher sedang lambat.

Event yang dikirim:

- location.created dari Service A.
- membership.created dari Service C.
- event payment dapat dikembangkan dari Service B.

Publisher memakai endpoint cloud dosen:

~~~text
https://iae-sso.virtualfri.id/api/v1/messages/publish
~~~

## Alur SSO M2M

Dosen mewajibkan field nim pada request token M2M. Semua service sudah mengikuti format ini.

Contoh request:

~~~json
{
  "api_key": "KEY-MHS-67",
  "nim": "102022400039"
}
~~~

Mapping NIM:

- Farid: 102022400039
- Hadid: 102022400126
- Dinda: 102022400023

## Alur End-to-End yang Diuji

1. Client membuat lokasi baru ke Service A lewat gateway.
2. Service A mengambil token M2M dari SSO dosen.
3. Service A mengirim SOAP Audit dan menerima receipt number.
4. Service A publish event location.created.
5. Client membuat transaksi ke Service B dengan location_id dari Service A.
6. Service B membaca lokasi dari Service A.
7. Service B membaca membership dari Service C.
8. Service B membuat transaksi dengan status BERLANGSUNG.
9. Client melakukan checkout.
10. Service B menghitung biaya dari base_rate Service A dan diskon Service C.
11. Client melakukan payment.
12. Service B mengubah status transaksi menjadi SELESAI.
13. Service B memanggil Service A untuk release slot.

Contoh hasil test lokal:

~~~json
{
  "id": "trx_006",
  "location_id": "loc_004",
  "member_card_id": "MEM001",
  "base_rate": 4000,
  "benefit": 800,
  "total_amount": 3200,
  "status": "SELESAI",
  "payment_method": "qris"
}
~~~

## Bukti Kesesuaian Rubrik

API Gateway dan routing hub:

- Semua request eksternal masuk lewat Nginx.
- Service internal tidak diekspos langsung ke host.
- Gateway meneruskan route ke Service A, B, dan C.

End-to-end core business flow:

- Service B memanggil Service A untuk lokasi dan slot.
- Service B memanggil Service C untuk membership dan diskon.
- Checkout dan payment berjalan sampai status SELESAI.

Central infrastructure compliance:

- Token SSO M2M mengirim api_key dan nim.
- SOAP Audit menghasilkan receipt number.
- RabbitMQ publisher menerima event dari service.