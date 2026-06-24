# 🏗️ نظرة عامة على البنية المعمارية — منصة قيمّ

## مكوّنات النظام

```mermaid
graph TB
    subgraph "Client Layer"
        Web[🌐 Web Browser]
        Mobile[📱 Mobile App]
        Admin[👨‍💼 Admin Panel]
    end

    subgraph "Edge / CDN"
        CF[Cloudflare CDN<br/>SSL/HSTS]
    end

    subgraph "Application Layer (Hostinger)"
        Apache[Apache + PHP-FPM<br/>OPcache]
        Laravel[Laravel 12<br/>PHP 8.2]

        subgraph "Middleware Stack"
            CSRF[CSRF Verification]
            SH[SecurityHeaders<br/>CSP/HSTS]
            Theme[ApplyTheme]
            Auth[Sanctum Auth]
            Throttle[Rate Limiter]
            R2FA[Force2FAForAdmins]
        end

        subgraph "Controllers"
            Web_C[Web Controllers]
            API_C[API Controllers v1]
            Admin_C[Admin Controllers]
            Health[Health Check]
        end

        subgraph "Domain Layer"
            Services[Services<br/>Gamification, Backup, Points]
            Actions[Actions<br/>SubmitActivityAction]
            Policies[Policies<br/>Activity, Lesson, Message]
            Events[Events + Listeners]
        end

        subgraph "Models / Eloquent"
            User[User Model<br/>+ Saving Guards]
            Activity[Activity / Submission]
            School[School / Classroom]
            Points[Points / Coins<br/>Append-Only]
        end
    end

    subgraph "Data Layer"
        MySQL[(MySQL 8<br/>Hostinger)]
        Redis[(Redis<br/>Cache + Queue)]
        Storage[Local Storage<br/>+ S3 Backups]
    end

    subgraph "External Services"
        SMTP[SMTP Hostinger]
        Sentry[Sentry<br/>Error Tracking]
    end

    Web --> CF
    Mobile --> CF
    Admin --> CF
    CF --> Apache
    Apache --> Laravel
    Laravel --> CSRF
    CSRF --> SH --> Theme --> Auth --> Throttle --> R2FA
    R2FA --> Web_C
    R2FA --> API_C
    R2FA --> Admin_C
    Web_C --> Services
    API_C --> Services
    Admin_C --> Services
    Services --> Actions
    Actions --> Policies
    Policies --> Events
    Events --> User
    Services --> User
    User --> MySQL
    Activity --> MySQL
    School --> MySQL
    Points --> MySQL
    Services --> Redis
    Services --> Storage
    Storage --> S3[S3 Backups]
    Services --> SMTP
    Laravel -.-> Sentry
```

## التدفق العام (Request Lifecycle)

```mermaid
sequenceDiagram
    participant U as User
    participant CF as Cloudflare
    participant A as Apache+PHP
    participant L as Laravel
    participant DB as MySQL
    participant R as Redis
    participant Q as Queue Worker

    U->>CF: HTTPS Request
    CF->>A: Forward
    A->>L: HTTP Request
    L->>L: 1. Bootstrap (config/routes/services)
    L->>L: 2. Middleware: CSRF, Security, Throttle
    L->>L: 3. Authentication (Sanctum)
    L->>L: 4. Route → Controller
    L->>L: 5. Form Request validation
    L->>L: 6. Authorization (Policy/Gate)
    L->>R: Cache::get?
    alt Cache hit
        R-->>L: cached value
    else Cache miss
        L->>DB: Query
        DB-->>L: rows
        L->>R: Cache::put (TTL 15min)
    end
    L->>L: 7. Service/Action processing
    L->>DB: Transaction (write)
    L->>Q: Dispatch job (Mail, Notifications)
    L-->>A: HTTP Response (JSON/HTML)
    A-->>CF: Response
    CF-->>U: 200 OK
    Q->>Q: Process async (Mail::send)
```

## Domain Model (Core Entities)

```mermaid
erDiagram
    User ||--o{ ActivitySubmission : submits
    User ||--o{ Point : earns
    User ||--o{ Coin : earns
    User }o--|| School : belongs_to
    User }o--o{ Classroom : enrolled_in
    User }o--o{ User : parent_of
    School ||--o{ Classroom : has
    Classroom }o--|| User : taught_by
    Activity ||--o{ ActivitySubmission : has
    Activity }o--|| Lesson : belongs_to
    Lesson }o--|| Concept : belongs_to
    Concept }o--|| Value : belongs_to
    User ||--o{ Message : sender
    User ||--o{ Message : receiver
    Conversation ||--o{ Message : contains
    User ||--o{ Notification : receives
    User ||--o{ TeacherPoint : earns
    User ||--o{ ParentPoint : earns
    School ||--o{ SchoolPoint : earns

    User {
        bigint id PK
        string name
        string email UK
        enum role "super_admin|school_admin|teacher|student|parent"
        bigint school_id FK
        enum status "active|inactive"
        boolean two_factor_enabled
    }

    School {
        bigint id PK
        string name
        string city
        enum status
    }

    Activity {
        bigint id PK
        bigint lesson_id FK
        bigint created_by FK
        bigint classroom_id FK
        enum approval_status
        json questions
        int points
    }

    ActivitySubmission {
        bigint id PK
        bigint activity_id FK
        bigint student_id FK
        int score
        enum status "pending|approved|rejected|completed"
        bigint reviewed_by FK
    }

    Point {
        bigint id PK
        bigint user_id FK
        int points
        string source
        bigint activity_id FK
    }
```

## Authorization Flow (Policies + Guards)

```mermaid
flowchart TD
    Request[HTTP Request] --> Auth{Authenticated?}
    Auth -->|No| Login[Redirect /login]
    Auth -->|Yes| RoleMW{role middleware<br/>matches?}
    RoleMW -->|No| F403[403 Forbidden]
    RoleMW -->|Yes| Force2FA{Admin without 2FA?}
    Force2FA -->|Yes| Setup2FA[Redirect 2FA setup]
    Force2FA -->|No| Controller[Controller Action]
    Controller --> Policy{$this->authorize?}
    Policy -->|Pass| ELoad[Eloquent Load/Save]
    Policy -->|Fail| F403
    ELoad --> ESave{Save Triggered?}
    ESave -->|Yes| Guard{User::booted<br/>saving guard}
    Guard -->|Sensitive field<br/>+ non-admin actor| GuardFail[abort 403]
    Guard -->|Pass| DB[(Save to DB)]
    ESave -->|No| Response[Return Response]
    DB --> Response
```

## Caching Strategy

```mermaid
graph LR
    subgraph "Hot Reads"
        Lead[Leaderboards]
        Stats[School Statistics]
        Settings[Settings]
        Theme[Theme Config]
    end

    subgraph "Cache Layer"
        Redis[(Redis<br/>TTL 15min)]
    end

    subgraph "Background Refresh"
        Cron[Hourly Cron<br/>schools:refresh-stats]
    end

    Lead -->|Cache::remember| Redis
    Stats -->|via Cache table| Redis
    Settings -->|TTL 24h| Redis
    Theme -->|TTL 1h| Redis
    Cron -->|recompute + warm| Redis
```

## Queue Processing (when Redis available)

```mermaid
sequenceDiagram
    participant C as Controller
    participant Q as Queue (Redis)
    participant W as Worker (Horizon)
    participant SMTP as SMTP/Sentry

    C->>Q: dispatch(Mail::send)
    C-->>Client: 200 OK (immediate)
    W->>Q: pop next job
    W->>SMTP: send email
    SMTP-->>W: ack/fail
    alt success
        W->>Q: ack
    else fail
        W->>Q: retry (3x)
        W->>Sentry: report error after 3rd retry
    end
```

## Security Layers (Defense in Depth)

```mermaid
graph TB
    subgraph "Layer 1: Network"
        L1A[Cloudflare DDoS Protection]
        L1B[HSTS Force HTTPS]
        L1C[WAF Rules]
    end

    subgraph "Layer 2: Application"
        L2A[Rate Limiting<br/>60/min API, 5/min Login]
        L2B[CSRF Tokens]
        L2C[SecurityHeaders<br/>CSP/X-Frame-Options]
    end

    subgraph "Layer 3: Authentication"
        L3A[Bcrypt Hashing rounds=12]
        L3B[Sanctum Bearer Tokens]
        L3C[2FA for Admins]
        L3D[Encrypted Sessions]
    end

    subgraph "Layer 4: Authorization"
        L4A[Route Middleware<br/>role:teacher]
        L4B[Policies $this->authorize]
        L4C[Eloquent Saving Guards<br/>booted method]
    end

    subgraph "Layer 5: Data"
        L5A[Append-only Point/Coin]
        L5B[School-scoped Queries]
        L5C[FK Constraints]
        L5D[Form Request Validation]
    end

    L1A --> L2A --> L3A --> L4A --> L5A
```
