# Candycutz — System Diagrams & Visual Models

## 1. Physical Network & Single-VPS Production Topology

```mermaid
flowchart TD
    subgraph WAN ["Internet / Public Access"]
        ClientWeb["Web Browser<br/>(candycutz.com / admin.candycutz.com)"]
        ClientMobile["Mobile Device<br/>(CandyCutz Expo App)"]
        StripeWebhook["Stripe Payment Cloud<br/>(Signed Webhook Events)"]
    end

    subgraph HostVPS ["Single VPS (Ubuntu 22.04 LTS / Docker Host)"]
        subgraph DockerNet ["Docker Network: candycutz-net"]
            Nginx["Nginx Reverse Proxy (Port 80/443)<br/>SSL Termination via Let's Encrypt"]
            
            subgraph AppTier ["Application Tier"]
                PHP["Laravel 11 PHP-FPM 8.2<br/>(Port 9000)<br/>OPcache Enabled"]
                Worker["Laravel Queue Worker<br/>(Supervisor: queue:work)"]
                Scheduler["Laravel Scheduler<br/>(cron: schedule:run)"]
            end
            
            subgraph DataTier ["Data & Cache Tier"]
                MySQL[("MySQL 8.0 Engine<br/>InnoDB / Persistent Volume")]
                Redis[("Redis 7.2 In-Memory<br/>Session, Cache & Queue")]
            end
            
            subgraph StorageTier ["File Storage"]
                StaticFiles["Static Vue 3 Build<br/>(/dist/assets)"]
                Uploads["User Uploads & Avatars<br/>(storage/app/public)"]
            end
        end
    end

    subgraph ExternalSaaS ["Third-Party Managed Services"]
        Brevo["Brevo Transactional SMTP / API<br/>(Branded Client Emails)"]
        ExpoPush["Expo Push Notification Gateway<br/>(APNs / FCM Relay)"]
        GoogleAuth["Google Identity OAuth2<br/>(JWKS Token Verification)"]
        AppleAuth["Apple ID Auth Services<br/>(ES256 Token Verification)"]
    end

    %% Network routing
    ClientWeb -->|"HTTPS:443"| Nginx
    ClientMobile -->|"HTTPS:443 (/api/v1/*)"| Nginx
    StripeWebhook -->|"HTTPS:443 (/api/v1/payments/webhook)"| Nginx

    Nginx -->|"Static Files (/*)"| StaticFiles
    Nginx -->|"Uploads (/storage/*)"| Uploads
    Nginx -->|"FastCGI (/api/*)"| PHP

    PHP -->|"SQL Queries (Port 3306)"| MySQL
    PHP -->|"Cache & Jobs (Port 6379)"| Redis
    Worker -->|"Pop Jobs"| Redis
    Worker -->|"Update State"| MySQL

    Worker -->|"Send Emails"| Brevo
    Worker -->|"Send Push"| ExpoPush
    PHP -->|"Verify Tokens"| GoogleAuth
    PHP -->|"Verify Tokens"| AppleAuth
```

---

## 2. Multi-Device Authentication & Token Scoping Flow

```mermaid
sequenceDiagram
    autonumber
    actor User as Customer / Barber
    participant Web as Vue 3 Web Client
    participant Mobile as Expo Mobile App
    participant API as Laravel 11 Backend
    participant DB as MySQL (users & tokens)

    %% Flow 1: Web Login
    Note over User,DB: Scenario 1: User logs in on Desktop Web
    User->>Web: Enters @username & Password
    Web->>API: POST /api/v1/auth/login (client_name: "web-client")
    API->>DB: Verify credentials via Hash::check()
    API->>DB: Insert Sanctum token named 'web-client' (DO NOT purge old tokens)
    API-->>Web: Return 200 OK + { token: "web_token_abc", user: {...} }
    Web->>Web: Store in localStorage ('candycutz_auth_token')

    %% Flow 2: Mobile Login on Same Account
    Note over User,DB: Scenario 2: Same user opens mobile phone and logs in
    User->>Mobile: Enters Phone & Password
    Mobile->>API: POST /api/v1/auth/login (client_name: "mobile-client")
    API->>DB: Verify credentials
    API->>DB: Insert Sanctum token named 'mobile-client'
    API-->>Mobile: Return 200 OK + { token: "mobile_token_xyz", user: {...} }
    Mobile->>Mobile: Store in SecureStore ('candycutz_auth_token')

    %% Flow 3: Concurrent Access Verification
    Note over User,DB: Scenario 3: User continues using Web without interruption
    Web->>API: GET /api/v1/appointments (Bearer web_token_abc)
    API->>DB: Validate token 'web_token_abc'
    DB-->>API: Valid session (User #42)
    API-->>Web: Return 200 OK (Appointments List)
    Note over Web,Mobile: Both Web and Mobile remain concurrently logged in!
```

---

## 3. Concurrency-Safe Booking Transaction Flow

```mermaid
sequenceDiagram
    autonumber
    actor Customer as Customer Client
    participant API as Laravel 11 Booking Engine
    participant DB as MySQL Database
    participant Stripe as Stripe Gateway
    participant Queue as Redis Queue Worker
    participant Brevo as Brevo Email Service

    Customer->>API: POST /api/v1/appointments<br/>{barber_id, date, start_time, services, mode}
    
    rect rgb(20, 20, 30)
        Note over API,DB: BEGIN DATABASE TRANSACTION
        API->>DB: SELECT * FROM appointments<br/>WHERE barber_id = ? AND date = ?<br/>AND (start_time < ? AND end_time > ?)<br/>FOR UPDATE
        alt Overlapping Appointment Exists
            DB-->>API: Row found (Conflict)
            API-->>Customer: 422 Conflict: "Slot unavailable"
            Note over API,DB: ROLLBACK TRANSACTION
        else No Overlapping Rows
            DB-->>API: 0 conflicting rows locked
            API->>DB: INSERT INTO appointments (status = 'pending_payment')
            API->>DB: INSERT INTO appointment_items (...)
            API->>DB: INSERT INTO appointment_status_history (...)
            API->>Stripe: Create PaymentIntent (amount, currency='NGN')
            Stripe-->>API: Return client_secret & intent_id
            API->>DB: INSERT INTO payments (status='pending', intent_id)
            Note over API,DB: COMMIT TRANSACTION
        end
    end

    API-->>Customer: 201 Created + {appointment, client_secret}

    Note over Customer,Brevo: Asynchronous Payment Webhook Settlement
    Customer->>Stripe: Confirms Card Authorization
    Stripe->>API: POST /api/v1/payments/webhook (payment_intent.succeeded)
    API->>API: Verify Stripe-Signature Header
    API->>DB: Check payment_transactions for duplicate event_id
    API->>DB: UPDATE payments SET status = 'successful'
    API->>DB: UPDATE appointments SET status = 'confirmed'
    API->>Queue: Dispatch SendBookingConfirmationJob(appointment_id)
    API-->>Stripe: 200 OK (Acknowledged)
    
    Queue->>Brevo: Send Luxury Branded Confirmation Email via SMTP
    Brevo-->>Customer: Deliver Email to Inbox
```

---

## 4. Unified Mobile App Role-Based Navigation Architecture

```mermaid
graph TD
    AppStart["Mobile App Launches<br/>(Expo Root: app/_layout.tsx)"]
    CheckToken["Read Token from Hardware SecureStore<br/>(tokenStorage.get)"]
    
    AppStart --> CheckToken
    
    CheckToken -->|No Token Found| AuthStack["Mount (auth) Layout<br/>• LoginScreen<br/>• RegisterScreen<br/>• ForgotPasswordScreen"]
    CheckToken -->|Token Found| FetchMe["Call GET /api/v1/auth/me<br/>(Verify with Server)"]
    
    FetchMe -->|Token Expired / 401| ClearStore["Delete Token from SecureStore"]
    ClearStore --> AuthStack
    
    FetchMe -->|200 OK: Inspect user.role| RoleRouter{"Role Evaluation"}
    
    RoleRouter -->|"role === 'customer'"| CustomerTabs["Mount (customer) Layout<br/>─────────────<br/>• Home Tab (Featured Services & Keffi Flagship)<br/>• Explore Tab (Barbers & Portfolios)<br/>• Book Wizard (Multi-Service & Home Service)<br/>• Appointments Tab (Active Cuts & Verification QR)<br/>• Profile Tab (Address Book & Dark Mode)"]
    
    RoleRouter -->|"role === 'barber'"| BarberTabs["Mount (barber) Layout<br/>─────────────<br/>• Dashboard Tab (Chair Status & Active Timer)<br/>• Queue Tab (Today's Scheduled Clients)<br/>• Schedule Tab (Weekly Working Hours & Blocks)<br/>• Walk-In Modal (30-Sec Fast Guest Registration)<br/>• Profile Tab (Stylist Bio & Portfolio Gallery)"]
```
