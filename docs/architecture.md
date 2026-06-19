# 🏗️ System Architecture - Kelompok 6 / TEAM-06

This document describes the technical architecture and request integration flow for the **Smart Parking System**.

---

## 🗺️ High-Level Component Architecture

The system consists of three main groups:
1. **API Gateway (Nginx)**: The single ingress controller for all client requests.
2. **Internal Microservices**: Backend business services (Service A, B, C) running in isolated Docker containers.
3. **External Central Infrastructure**: Cloud systems provided by lecturers (SSO server, SOAP audit log database, RabbitMQ message broker).

```mermaid
graph TD
    Client[Client / Postman] -->|HTTP Port 80| Nginx[API Gateway - Nginx]
    
    subgraph Local Container Network (smart_parking_net)
        Nginx -->|Proxy Pass| ServA[Service A: Lahan & Lokasi]
        Nginx -.->|Proxy Pass| ServB[Service B: Transaksi - Stub]
        Nginx -.->|Proxy Pass| ServC[Service C: Pembayaran - Stub]
        
        ServA -->|TCP 3306| DBA[(Service A MySQL DB)]
        ServB -.-> DBB[(Service B DB)]
        ServC -.-> DBC[(Service C DB)]
    end

    subgraph External Central Cloud (Dosen Infrastructure)
        SSO[SSO Cloud Server - JWKS]
        SOAP[Legacy SOAP Audit Server]
        RMQ[RabbitMQ Broker]
    end

    ServA -->|1. Validate JWT via JWKS| SSO
    ServA -->|2. Send Transaction Envelope| SOAP
    ServA -->|3. Publish location.created JSON| RMQ
```

---

## 🛡️ Routing and Security (API Gateway)

No backend container (such as Laravel app, MySQL database) is directly accessible from the internet. All incoming traffic must pass through the **Nginx API Gateway**:
* **Port Exposure**: Only port `80` (HTTP) is mapped on the host machine.
* **Routing Rules**:
  * `/api/v1/locations` $\rightarrow$ `http://smart-parking-service-a-app:3001`
  * `/api/v1/sso` $\rightarrow$ `http://smart-parking-service-a-app:3001`
  * `/api/v1/transactions` $\rightarrow$ Service B (to be implemented)
  * `/api/v1/payments` $\rightarrow$ Service C (to be implemented)
* **Header Enrichment**: Nginx acts as a reverse proxy, forwarding client headers (`Host`, `X-Real-IP`, `X-Forwarded-For`, `Authorization`) to downstream services to ensure JWT signatures and IPs can be processed accurately.

---

## 🔗 Central Infrastructure Compliance Flow

Every state-changing transaction in our services (such as registering a new parking location in Service A) triggers a three-step orchestration process:

### 1. Federated SSO Validation (JWT)
* Clients authenticate via the central SSO server: `POST /api/v1/auth/token` with credentials (Email/Password or API Key).
* Clients send requests to local services with a `Authorization: Bearer <JWT>` header.
* Local services intercept this request using a custom SSO middleware. The middleware fetches the JSON Web Key Set (JWKS) public keys from the central SSO server (`/api/v1/auth/jwks` or `/.well-known/jwks.json`), caches it for 1 hour, and verifies the JWT signature (RS256).
* The user's identity is extracted from the JWT payload and mapped to the local `roles` table.

### 2. Legacy SOAP XML Audit
* Upon validating the transaction payload, the service wraps details into a strict SOAP XML envelope:
  ```xml
  <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.virtualfri.id">
     <soapenv:Header/>
     <soapenv:Body>
        <iae:AuditRequest>
           <iae:TeamID>TEAM-06</iae:TeamID>
           <iae:ActivityName>LocationCreated</iae:ActivityName>
           <iae:LogContent><![CDATA[{"location_id":"loc_001",...}]]></iae:LogContent>
        </iae:AuditRequest>
     </soapenv:Body>
  </soapenv:Envelope>
  ```
* The payload is dispatched to `https://iae-sso.virtualfri.id/soap/v1/audit` with headers `Content-Type: text/xml` and `SOAPAction: audit`.
* The server responds with an audit status and a `ReceiptNumber` which is parsed and saved locally as audit proof.

### 3. AMQP Event Broadcasting & Consumption (RabbitMQ)
* **Event Broadcasting (Publishing)**:
  * After obtaining the `ReceiptNumber`, the service notifies other microservices asynchronously.
  * An AMQP event publisher dispatches a JSON message to RabbitMQ:
    * **Endpoint**: `https://iae-sso.virtualfri.id/api/v1/messages/publish` (or direct AMQP connection)
    * **Routing Key**: `location.created`
    * **Payload**: Includes database record fields + the retrieved `ReceiptNumber`.
  * This triggers real-time updates across the dashboard and other listening services (e.g. Service B knows that a new location is available for bookings).
* **Event Consumption**:
  * Service A runs a background consumer command (`php artisan rabbitmq:consume`) to listen to the exchange `iae.central.exchange` (topic type) via the queue `team06_smart_parking_queue`.
  * Service A processes three incoming integration events to dynamically adjust slots:
    * `parking.slot.occupied` $\rightarrow$ Decrements `available_spots` for the specified location.
    * `parking.slot.released` $\rightarrow$ Increments `available_spots` for the specified location.
    * `parking.payment.completed` $\rightarrow$ Triggers the release of the occupied parking spot, incrementing `available_spots`.
  * For stateless deployments, Service A also provides a webhook simulation endpoint `POST /api/v1/events/rabbitmq-callback` which mirrors the consumer's business logic.
