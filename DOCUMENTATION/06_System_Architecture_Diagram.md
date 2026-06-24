# مخطط هندسة النظام
## System Architecture Diagram - منصة قيمّ

---

## 📋 نظرة عامة

هذا الملف يوضح الهندسة الكاملة لمنصة قيمّ، بما في ذلك:
- البنية متعددة الطبقات (Multi-Tier Architecture)
- المكونات الرئيسية والفرعية
- التكامل بين الأنظمة
- قنوات الاتصال
- البنية التحتية والاستضافة

---

## 🏗️ نمط الهندسة المستخدم

**Pattern:** MVC (Model-View-Controller) + Layered Architecture

**المبررات:**
- ✅ فصل واضح بين المنطق والعرض والبيانات
- ✅ سهولة الصيانة والتطوير
- ✅ قابلية اختبار عالية
- ✅ Laravel يدعم هذا النمط بشكل أصلي

---

## 🎨 مخطط الهندسة الكامل (High-Level Architecture)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              Client Layer (طبقة العميل)                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│   │  Web Browser │  │  Mobile App  │  │  Tablet App  │  │ Desktop App  │ │
│   │              │  │  (Future)    │  │  (Future)    │  │  (Future)    │ │
│   │  Chrome      │  │  iOS/Android │  │  iPad/Tab    │  │  Electron    │ │
│   │  Firefox     │  │              │  │              │  │              │ │
│   │  Safari      │  │              │  │              │  │              │ │
│   │  Edge        │  │              │  │              │  │              │ │
│   └──────┬───────┘  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘ │
│          │                 │                 │                 │          │
└──────────┼─────────────────┼─────────────────┼─────────────────┼──────────┘
           │                 │                 │                 │
           └─────────────────┴─────────────────┴─────────────────┘
                                     │
                              ┌──────▼──────┐
                              │   HTTPS     │
                              │   SSL/TLS   │
                              └──────┬──────┘
                                     │
┌─────────────────────────────────────────────────────────────────────────────┐
│                         Load Balancer (اختياري)                            │
│                         [Nginx / AWS ELB]                                   │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   │
┌──────────────────────────────────▼──────────────────────────────────────────┐
│                           Web Server Layer (طبقة الويب)                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │                         Nginx / Apache                              │  │
│   │                         (Web Server)                                │  │
│   └─────────────────────────────────┬───────────────────────────────────┘  │
│                                     │                                       │
└─────────────────────────────────────┼───────────────────────────────────────┘
                                      │
┌─────────────────────────────────────▼───────────────────────────────────────┐
│                      Application Layer (طبقة التطبيق)                      │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │                         Laravel 12.x                                │  │
│   │                         PHP 8.2+                                    │  │
│   └─────────────────────────────────┬───────────────────────────────────┘  │
│                                     │                                       │
│   ┌──────────────────────────────────────────────────────────────────┐     │
│   │                       Presentation Layer                         │     │
│   │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐           │     │
│   │  │    Views     │  │   Blade      │  │   Assets     │           │     │
│   │  │   (Blade)    │  │  Templates   │  │ (CSS/JS/Img) │           │     │
│   │  └──────────────┘  └──────────────┘  └──────────────┘           │     │
│   └──────────────────────────────────────────────────────────────────┘     │
│                                     │                                       │
│   ┌──────────────────────────────────────────────────────────────────┐     │
│   │                       Controller Layer                           │     │
│   │  ┌────────────────────────────────────────────────────────────┐  │     │
│   │  │  Controllers                                               │  │     │
│   │  │  ├─ AuthController         ├─ SchoolAdminController       │  │     │
│   │  │  ├─ SuperAdminController   ├─ TeacherController           │  │     │
│   │  │  ├─ StudentController      ├─ ParentController            │  │     │
│   │  │  └─ API Controllers                                        │  │     │
│   │  └────────────────────────────────────────────────────────────┘  │     │
│   └──────────────────────────────────────────────────────────────────┘     │
│                                     │                                       │
│   ┌──────────────────────────────────────────────────────────────────┐     │
│   │                       Service Layer                              │     │
│   │  ┌────────────────────────────────────────────────────────────┐  │     │
│   │  │  Services (Business Logic)                                 │  │     │
│   │  │  ├─ AuthService            ├─ NotificationService          │  │     │
│   │  │  ├─ GamificationService    ├─ ReportService               │  │     │
│   │  │  ├─ ActivityService        ├─ RegistrationService         │  │     │
│   │  │  └─ ProgressTrackingService                                │  │     │
│   │  └────────────────────────────────────────────────────────────┘  │     │
│   └──────────────────────────────────────────────────────────────────┘     │
│                                     │                                       │
│   ┌──────────────────────────────────────────────────────────────────┐     │
│   │                       Data Access Layer                          │     │
│   │  ┌────────────────────────────────────────────────────────────┐  │     │
│   │  │  Eloquent ORM + Repositories                               │  │     │
│   │  │  ├─ User Repository        ├─ Activity Repository          │  │     │
│   │  │  ├─ School Repository      ├─ Value Repository             │  │     │
│   │  │  └─ Student Repository                                      │  │     │
│   │  └────────────────────────────────────────────────────────────┘  │     │
│   └──────────────────────────────────────────────────────────────────┘     │
│                                     │                                       │
│   ┌──────────────────────────────────────────────────────────────────┐     │
│   │                       Middleware Layer                           │     │
│   │  ┌────────────────────────────────────────────────────────────┐  │     │
│   │  │  Middleware                                                │  │     │
│   │  │  ├─ Authentication        ├─ CSRF Protection               │  │     │
│   │  │  ├─ Authorization (Roles) ├─ Rate Limiting                 │  │     │
│   │  │  ├─ 2FA Verification      ├─ Logging                       │  │     │
│   │  │  └─ Input Validation                                        │  │     │
│   │  └────────────────────────────────────────────────────────────┘  │     │
│   └──────────────────────────────────────────────────────────────────┘     │
│                                                                             │
└─────────────────────────────────────┬───────────────────────────────────────┘
                                      │
┌─────────────────────────────────────▼───────────────────────────────────────┐
│                           Data Layer (طبقة البيانات)                       │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │                      Primary Database                               │  │
│   │                      MySQL 8.0+ / PostgreSQL                        │  │
│   │  ┌───────────────────────────────────────────────────────────────┐  │  │
│   │  │  Tables:                                                      │  │  │
│   │  │  ├─ users               ├─ schools            ├─ classrooms   │  │  │
│   │  │  ├─ values              ├─ concepts           ├─ meanings     │  │  │
│   │  │  ├─ lessons             ├─ activities         ├─ submissions  │  │  │
│   │  │  ├─ points              ├─ coins              ├─ badges       │  │  │
│   │  │  ├─ teams               ├─ notifications      ├─ settings     │  │  │
│   │  │  └─ activity_log                                              │  │  │
│   │  └───────────────────────────────────────────────────────────────┘  │  │
│   └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │                      Cache Layer (اختياري)                          │  │
│   │                      Redis / Memcached                              │  │
│   │  ┌───────────────────────────────────────────────────────────────┐  │  │
│   │  │  Cached Data:                                                 │  │  │
│   │  │  ├─ User Sessions        ├─ Settings          ├─ Leaderboard │  │  │
│   │  │  ├─ Frequently Accessed Data                                  │  │  │
│   │  │  └─ Query Results (1 hour TTL)                                │  │  │
│   │  └───────────────────────────────────────────────────────────────┘  │  │
│   └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
┌─────────────────────────────────────▼───────────────────────────────────────┐
│                      External Services Layer (خدمات خارجية)                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│   │  Email       │  │  Storage     │  │  CDN         │  │  Analytics   │ │
│   │  Service     │  │  Service     │  │  (Future)    │  │  (Future)    │ │
│   │              │  │              │  │              │  │              │ │
│   │  Mailgun     │  │  AWS S3 /    │  │  CloudFlare  │  │  Google      │ │
│   │  SendGrid    │  │  Local Disk  │  │  Cloudinary  │  │  Analytics   │ │
│   └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘ │
│                                                                             │
│   ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│   │  Backup      │  │  Logging     │  │  Monitoring  │  │  Security    │ │
│   │  Service     │  │  Service     │  │  (Future)    │  │              │ │
│   │              │  │              │  │              │  │              │ │
│   │  Spatie      │  │  Laravel Log │  │  New Relic   │  │  Cloudflare  │ │
│   │  Backup      │  │  Monolog     │  │  Datadog     │  │  WAF         │ │
│   └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘ │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
┌─────────────────────────────────────▼───────────────────────────────────────┐
│                      Queue & Background Jobs Layer                          │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │                      Queue System                                   │  │
│   │                      Laravel Queue + Database                       │  │
│   │  ┌───────────────────────────────────────────────────────────────┐  │  │
│   │  │  Background Jobs:                                             │  │  │
│   │  │  ├─ Send Email Notifications                                  │  │  │
│   │  │  ├─ Generate Reports                                          │  │  │
│   │  │  ├─ Process File Uploads                                      │  │  │
│   │  │  ├─ Calculate Leaderboard                                     │  │  │
│   │  │  ├─ Daily Streak Updates                                      │  │  │
│   │  │  └─ Backup Database                                           │  │  │
│   │  └───────────────────────────────────────────────────────────────┘  │  │
│   └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔐 طبقة الأمان (Security Layer)

### مكونات الأمان المطبقة

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                            Security Architecture                            │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │  1. Authentication & Authorization                                  │  │
│   │  ┌───────────────────────────────────────────────────────────────┐  │  │
│   │  │  ├─ Laravel Sanctum (API Tokens)                              │  │  │
│   │  │  ├─ Session-Based Auth (Web)                                  │  │  │
│   │  │  ├─ Two-Factor Authentication (2FA via Email)                 │  │  │
│   │  │  ├─ Spatie Laravel Permission (RBAC)                          │  │  │
│   │  │  │    └─ Roles: super_admin, school_admin, teacher,          │  │  │
│   │  │  │              student, parent                               │  │  │
│   │  │  └─ Password Policies (8 chars, mixed case, numbers, symbols)│  │  │
│   │  └───────────────────────────────────────────────────────────────┘  │  │
│   └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │  2. Input Validation & Sanitization                                 │  │
│   │  ┌───────────────────────────────────────────────────────────────┐  │  │
│   │  │  ├─ Laravel Validation Rules                                   │  │  │
│   │  │  ├─ CSRF Protection (Middleware)                               │  │  │
│   │  │  ├─ XSS Protection (Blade Auto-Escaping)                       │  │  │
│   │  │  ├─ SQL Injection Prevention (Eloquent ORM)                    │  │  │
│   │  │  └─ File Upload Validation (Size, Type, Content)              │  │  │
│   │  └───────────────────────────────────────────────────────────────┘  │  │
│   └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │  3. Rate Limiting & Throttling                                      │  │
│   │  ┌───────────────────────────────────────────────────────────────┐  │  │
│   │  │  ├─ Login: 20 attempts/minute                                  │  │  │
│   │  │  ├─ 2FA Verification: 10 attempts/minute                       │  │  │
│   │  │  ├─ Password Reset: 5 requests/hour                            │  │  │
│   │  │  ├─ API Endpoints: 60 requests/minute                          │  │  │
│   │  │  └─ Exponential Backoff for Failed Logins                      │  │  │
│   │  └───────────────────────────────────────────────────────────────┘  │  │
│   └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │  4. Session Security                                                │  │
│   │  ┌───────────────────────────────────────────────────────────────┐  │  │
│   │  │  ├─ HTTPOnly Cookies                                           │  │  │
│   │  │  ├─ Secure Flag (HTTPS)                                        │  │  │
│   │  │  ├─ SameSite=Lax                                               │  │  │
│   │  │  ├─ Session Timeout: 2 hours                                   │  │  │
│   │  │  └─ Regenerate Session ID on Login                            │  │  │
│   │  └───────────────────────────────────────────────────────────────┘  │  │
│   └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │  5. Encryption & Hashing                                            │  │
│   │  ┌───────────────────────────────────────────────────────────────┐  │  │
│   │  │  ├─ Passwords: bcrypt (Laravel's default)                      │  │  │
│   │  │  ├─ Sensitive Data: AES-256 Encryption                         │  │  │
│   │  │  ├─ 2FA Codes: Hashed in Database                              │  │  │
│   │  │  └─ Registration Tokens: 256-bit Random Strings               │  │  │
│   │  └───────────────────────────────────────────────────────────────┘  │  │
│   └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐  │
│   │  6. Activity Logging & Monitoring                                   │  │
│   │  ┌───────────────────────────────────────────────────────────────┐  │  │
│   │  │  ├─ Spatie Activity Log (User Actions)                         │  │  │
│   │  │  ├─ Laravel Log (System Events)                                │  │  │
│   │  │  ├─ Login Attempts Logging                                     │  │  │
│   │  │  ├─ Failed Authentication Alerts                               │  │  │
│   │  │  └─ IP Address & User Agent Tracking                          │  │  │
│   │  └───────────────────────────────────────────────────────────────┘  │  │
│   └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 تدفق البيانات (Data Flow)

### 1. تدفق تسجيل الدخول (Login Flow)

```
User (Browser)
    │
    ├─ GET /login
    │
    ▼
[Route] → [AuthController::showLogin()] → [View: login.blade.php]
                                                    │
                                                    ▼
                                            User enters credentials
                                                    │
                                                    ├─ POST /login
                                                    │
                                                    ▼
[Route] → [Middleware: CSRF, Throttle] → [AuthController::login()]
                                                    │
                                                    ├─ Validate Input
                                                    │
                                                    ▼
                                          [AuthService::attempt()]
                                                    │
                                                    ├─ Check User in DB
                                                    │
                                                    ▼
                                          ┌─────────┴─────────┐
                                          │  Valid?           │
                                          └─────────┬─────────┘
                                  ┌─────────────────┴─────────────────┐
                                  │ NO                                │ YES
                                  ▼                                   ▼
                        Return Error Message              ┌──────────────────┐
                        Increment Failed Attempts         │ 2FA Enabled?     │
                                                          └──────┬───────────┘
                                                  ┌──────────────┴──────────────┐
                                                  │ NO                          │ YES
                                                  ▼                             ▼
                                        Login Success             Send 2FA Code via Email
                                        Create Session            Redirect to /two-factor/verify
                                        Redirect to Dashboard             │
                                                                          ├─ User enters code
                                                                          │
                                                                          ▼
                                                              [AuthController::verifyTwoFactor()]
                                                                          │
                                                                          ├─ Validate Code
                                                                          │
                                                                          ▼
                                                                  ┌───────┴───────┐
                                                                  │ Valid & Fresh?│
                                                                  └───────┬───────┘
                                                          ┌───────────────┴───────────────┐
                                                          │ NO                            │ YES
                                                          ▼                               ▼
                                                Return Error                    Login Success
                                                                          Create Session
                                                                          Redirect to Dashboard
```

### 2. تدفق تسليم نشاط (Activity Submission Flow)

```
Student (Browser)
    │
    ├─ GET /student/activities/{id}
    │
    ▼
[Route] → [Middleware: Auth, Role:student] → [StudentController::showActivity($id)]
                                                           │
                                                           ├─ Load Activity from DB
                                                           ├─ Check if already submitted
                                                           │
                                                           ▼
                                                  [View: activity.blade.php]
                                                           │
                                                           ▼
                                                  Student fills the form
                                                  (Text + Optional File)
                                                           │
                                                           ├─ POST /student/activities/{id}/submit
                                                           │
                                                           ▼
[Route] → [Middleware: Auth, CSRF] → [StudentController::submitActivity($id)]
                                                           │
                                                           ├─ Validate Input
                                                           ├─ Upload File (if any)
                                                           │
                                                           ▼
                                                  [ActivityService::submit()]
                                                           │
                                                           ├─ Create ActivitySubmission
                                                           ├─ Store File Path
                                                           ├─ Set Status = 'pending'
                                                           │
                                                           ▼
                                                  [NotificationService::notifyTeacher()]
                                                           │
                                                           ├─ Create Notification
                                                           ├─ Send Email (Queue)
                                                           │
                                                           ▼
                                                  Return Success Message
                                                  Redirect to Dashboard
```

---

## 🗂️ هيكل الملفات (File Structure)

```
qiyamm-master/
│
├── app/
│   ├── Console/
│   │   └── Commands/              # أوامر Artisan مخصصة
│   │
│   ├── Events/                    # الأحداث (Events)
│   │
│   ├── Exceptions/                # معالجة الأخطاء
│   │
│   ├── Http/
│   │   ├── Controllers/           # Controllers
│   │   │   ├── Admin/             # Controllers للـ Super Admin
│   │   │   ├── Auth/              # Controllers للمصادقة
│   │   │   ├── School/            # Controllers للـ School Admin
│   │   │   ├── Teacher/           # Controllers للمعلم
│   │   │   ├── Student/           # Controllers للطالب
│   │   │   └── Parent/            # Controllers لولي الأمر
│   │   │
│   │   ├── Middleware/            # Middleware
│   │   │   ├── Authenticate.php
│   │   │   ├── CheckRole.php
│   │   │   ├── TwoFactorAuth.php
│   │   │   └── RateLimiting.php
│   │   │
│   │   └── Requests/              # Form Requests للتحقق
│   │
│   ├── Models/                    # Eloquent Models
│   │   ├── User.php
│   │   ├── School.php
│   │   ├── Value.php
│   │   ├── Activity.php
│   │   └── [... other models]
│   │
│   ├── Services/                  # Business Logic
│   │   ├── AuthService.php
│   │   ├── GamificationService.php
│   │   ├── ActivityService.php
│   │   ├── NotificationService.php
│   │   └── ReportService.php
│   │
│   ├── Notifications/             # إشعارات Laravel
│   │
│   ├── Listeners/                 # Event Listeners
│   │
│   ├── Helpers/                   # Helper Functions
│   │   └── SettingsHelper.php
│   │
│   └── Providers/                 # Service Providers
│       └── AppServiceProvider.php
│
├── bootstrap/                     # Bootstrap Files
│
├── config/                        # ملفات الإعدادات
│   ├── app.php
│   ├── database.php
│   ├── mail.php
│   └── [... other configs]
│
├── database/
│   ├── migrations/                # Database Migrations
│   ├── seeders/                   # Database Seeders
│   └── factories/                 # Model Factories
│
├── resources/
│   ├── views/                     # Blade Templates
│   │   ├── auth/                  # صفحات المصادقة
│   │   ├── admin/                 # صفحات Super Admin
│   │   ├── school-admin/          # صفحات School Admin
│   │   ├── teacher/               # صفحات المعلم
│   │   ├── student/               # صفحات الطالب
│   │   ├── parent/                # صفحات ولي الأمر
│   │   └── layouts/               # القوالب الأساسية
│   │
│   ├── css/                       # ملفات CSS
│   │   └── app.css
│   │
│   └── js/                        # ملفات JavaScript
│       └── app.js
│
├── routes/
│   ├── web.php                    # Web Routes
│   ├── api.php                    # API Routes
│   └── channels.php               # Broadcasting Channels
│
├── storage/
│   ├── app/                       # ملفات التطبيق
│   │   ├── public/                # ملفات عامة (صور، مستندات)
│   │   └── private/               # ملفات خاصة
│   │
│   ├── framework/                 # ملفات Framework
│   └── logs/                      # ملفات Log
│
├── tests/                         # الاختبارات
│   ├── Feature/                   # Feature Tests
│   └── Unit/                      # Unit Tests
│
├── vendor/                        # مكتبات Composer
│
├── .env                           # Environment Variables
├── .env.example                   # Environment Example
├── composer.json                  # Composer Dependencies
├── package.json                   # NPM Dependencies
└── artisan                        # Artisan CLI
```

---

## 🚀 معمارية الاستضافة (Deployment Architecture)

### خيار 1: Shared Hosting (بسيط)

```
┌─────────────────────────────────────────────────────────────┐
│                      Shared Hosting                         │
│                      (cPanel / Plesk)                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌────────────────────────────────────────────────────┐    │
│  │  Web Server (Apache)                               │    │
│  │  PHP 8.2+                                          │    │
│  └────────────────────────────────────────────────────┘    │
│                           │                                 │
│  ┌────────────────────────▼───────────────────────────┐    │
│  │  Laravel Application                               │    │
│  │  /public_html/qiyamm/public                        │    │
│  └────────────────────────────────────────────────────┘    │
│                           │                                 │
│  ┌────────────────────────▼───────────────────────────┐    │
│  │  MySQL Database                                    │    │
│  │  (phpMyAdmin)                                      │    │
│  └────────────────────────────────────────────────────┘    │
│                                                             │
│  ┌────────────────────────────────────────────────────┐    │
│  │  File Storage                                      │    │
│  │  /storage/ folder                                  │    │
│  └────────────────────────────────────────────────────┘    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**المميزات:**
- ✅ سهل الإعداد
- ✅ تكلفة منخفضة
- ✅ مناسب للبداية

**العيوب:**
- ⚠️ أداء محدود
- ⚠️ موارد مشتركة
- ⚠️ صعوبة التوسع

---

### خيار 2: VPS / Cloud Server (موصى به)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                            Cloud Server (VPS)                               │
│                     (AWS EC2 / DigitalOcean / Linode)                       │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Load Balancer (Optional)                                           │   │
│  │  Nginx / AWS ELB                                                    │   │
│  └─────────────────────────────────┬───────────────────────────────────┘   │
│                                    │                                        │
│  ┌─────────────────────────────────▼───────────────────────────────────┐   │
│  │  Web Server                                                         │   │
│  │  Nginx + PHP-FPM 8.2+                                               │   │
│  │  Ubuntu 22.04 LTS                                                   │   │
│  └─────────────────────────────────┬───────────────────────────────────┘   │
│                                    │                                        │
│  ┌─────────────────────────────────▼───────────────────────────────────┐   │
│  │  Laravel Application                                                │   │
│  │  /var/www/qiyamm                                                    │   │
│  └─────────────────────────────────┬───────────────────────────────────┘   │
│                                    │                                        │
│  ┌─────────────────────────────────▼───────────────────────────────────┐   │
│  │  Database Server                                                    │   │
│  │  MySQL 8.0+ / PostgreSQL 14+                                        │   │
│  │  (Same server or separate RDS)                                      │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Cache Server (Optional)                                            │   │
│  │  Redis / Memcached                                                  │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  File Storage                                                       │   │
│  │  AWS S3 / DigitalOcean Spaces / Local Disk                          │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Backup Solution                                                    │   │
│  │  Daily Automated Backups (DB + Files)                               │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

**المميزات:**
- ✅ أداء عالٍ
- ✅ موارد مخصصة
- ✅ سهولة التوسع
- ✅ تحكم كامل

**العيوب:**
- ⚠️ يتطلب معرفة تقنية للإعداد
- ⚠️ تكلفة أعلى نسبياً

---

## 📊 قابلية التوسع (Scalability)

### المرحلة 1: Small Scale (1-1000 users)

```
Single Server
├── Nginx
├── PHP-FPM
├── MySQL
└── Storage (Local Disk)
```

### المرحلة 2: Medium Scale (1000-10,000 users)

```
┌──────────────┐
│ Load Balancer│
└──────┬───────┘
       │
  ┌────┴────┬────────┬────────┐
  │         │        │        │
┌─▼──┐   ┌─▼──┐  ┌─▼──┐   ┌─▼──┐
│Web1│   │Web2│  │Web3│   │...│
└────┘   └────┘  └────┘   └────┘
       │
  ┌────▼────┐
  │ Database│
  │ (Master)│
  └─────────┘
       │
  ┌────▼────┐
  │  Redis  │
  │ (Cache) │
  └─────────┘
       │
  ┌────▼────┐
  │   S3    │
  │(Storage)│
  └─────────┘
```

### المرحلة 3: Large Scale (10,000+ users)

```
        ┌──────────────┐
        │ CDN          │
        │ (CloudFlare) │
        └──────┬───────┘
               │
        ┌──────▼───────┐
        │Load Balancer │
        │(AWS ELB)     │
        └──────┬───────┘
               │
  ┌────────────┴────────────┬──────────────┐
  │                         │              │
┌─▼──────────┐   ┌─────────▼──┐   ┌──────▼─────┐
│Web Servers │   │Web Servers │   │Web Servers │
│(Auto Scale)│   │(Auto Scale)│   │(Auto Scale)│
└────────────┘   └────────────┘   └────────────┘
       │
  ┌────▼────────────────┐
  │Database Cluster     │
  │(Master + Replicas)  │
  └─────────────────────┘
       │
  ┌────▼────────────────┐
  │Redis Cluster        │
  │(Distributed Cache)  │
  └─────────────────────┘
       │
  ┌────▼────────────────┐
  │Queue Workers        │
  │(Background Jobs)    │
  └─────────────────────┘
       │
  ┌────▼────────────────┐
  │AWS S3 + CloudFront  │
  │(CDN Storage)        │
  └─────────────────────┘
```

---

## 🔧 المتطلبات التقنية (Technical Requirements)

### Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **CPU** | 2 Cores | 4+ Cores |
| **RAM** | 4 GB | 8+ GB |
| **Storage** | 50 GB | 100+ GB SSD |
| **PHP** | 8.2+ | 8.3+ |
| **Database** | MySQL 8.0+ | PostgreSQL 14+ |
| **Web Server** | Apache 2.4+ | Nginx 1.20+ |

### PHP Extensions Required

```
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- PDO_MySQL
- Tokenizer
- XML
- GD / Imagick (for image processing)
```

---

## 📝 ملخص المعمارية

### نقاط القوة
1. ✅ **معمارية متعددة الطبقات** - فصل واضح بين الطبقات
2. ✅ **قابلية التوسع** - يمكن التوسع أفقياً ورأسياً
3. ✅ **الأمان** - طبقات أمان متعددة
4. ✅ **الأداء** - استخدام Caching و Queue
5. ✅ **الصيانة** - هيكل واضح وسهل الصيانة

### نقاط التحسين المستقبلية
1. 🔮 إضافة Microservices للأجزاء المعقدة
2. 🔮 استخدام Docker & Kubernetes للتوزيع
3. 🔮 إضافة Elasticsearch للبحث المتقدم
4. 🔮 استخدام GraphQL API بجانب REST
5. 🔮 إضافة Real-time Features (WebSockets)

---

**تاريخ الإعداد:** ديسمبر 23, 2025  
**أداة الرسم المقترحة:** Draw.io / Lucidchart / PlantUML  
**الحالة:** معتمد كبنية نهائية ✅
