# Tubes EAI Smart Parking Kelompok 6

Project di dalam sistem ini semua service dihubungkan melalui satu API Gateway (Nginx) dan dijalankan menggunakan Docker Compose.

## Pembagian Kerja dan servicenya
1. **Service A - Lahan & Lokasi Parkir**  
   Developer: **Farid Maulana**  
   NIM: **102022400039**

2. **Service B - Transaksi Parkir & Payment**  
   Developer: **Hadid Hamar**  
   NIM: **102022400126**

3. **Service C - Membership & Voucher**  
   Developer: **Dinda Juniar**  
   NIM: **102022400023**

## Struktur Folder Repositori
```
Tubes-IAE-Kelompok-6/
├── README.md               # File panduan ini
├── docker-compose.yml      # Orchestration semua container Docker
├── AI_PROMPT_LOG.md        # Log percakapan asisten AI (Farid Maulana)
├── api-gateway/            # Konfigurasi Nginx API Gateway
│   └── nginx.conf
├── docs/                   # Dokumen desain dan arsitektur sistem
│   ├── architecture.md
│   └── member-services.md
└── services/
    ├── farid-lahan-lokasi/
    ├── hadid-transaksi-parkir/
    └── dinda-membership-voucher/
```

## Teknologi yang Digunakan
* **API Gateway**: Nginx
* **Container Orchestration**: Docker Compose
* **Service A**: Laravel, PHP, MySQL
* **Service B**: Laravel, PHP, MySQL, REST + GraphQL
* **Service C**: Laravel, PHP, MySQL
* **SSO & Auth**: JWT dari Server SSO Dosen
* **Audit System**: SOAP XML Web Service
* **Message Broker**: RabbitMQ via HTTP Publisher Cloud Dosen

## Cara Menjalankan Project

### Persyaratan
* Pastikan sudah menginstal Docker Desktop dan Git di laptop.

### Langkah-langkah
1. Clone repositori ini ke lokal:
   ```bash
   git clone https://github.com/IAE-2026/Tubes-IAE-Kelompok-6.git
   cd Tubes-IAE-Kelompok-6
   ```
2. Copy berkas `.env.example` menjadi `.env` di folder Service A:
   ```bash
   cp services/farid-lahan-lokasi/.env.example services/farid-lahan-lokasi/.env
   ```
   *Sesuaikan variabel database dan API Key di dalam `.env` tersebut.*
3. Jalankan container Docker:
   ```bash
   docker compose up --build -d
   ```
4. Cek status container:
   ```bash
   docker compose ps
   ```

Setelah semua container aktif, API Gateway akan berjalan di port `80`. Semua request API dari luar wajib masuk lewat Gateway:
`http://localhost/api/v1/...`

## Alur Integrasi Sistem Pusat
Semua service yang berjalan di kelompok ini wajib mengikuti standar integrasi eksternal secara berurutan:
1. Validasi Token SSO JWT dari server Cloud Dosen.
2. Pengiriman SOAP Audit untuk mencatat transaksi penting dan mendapatkan `ReceiptNumber`.
3. Broadcast event transaksi (seperti `location.created` atau `parking.payment.completed`) ke RabbitMQ.
