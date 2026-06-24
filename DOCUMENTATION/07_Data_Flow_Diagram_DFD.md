# مخطط تدفق البيانات
## Data Flow Diagram (DFD) - منصة قيمّ

---

## 📋 نظرة عامة

هذا الملف يوضح كيفية تدفق البيانات في منصة قيمّ عبر مستويات مختلفة من التفصيل:
- **Level 0 (Context Diagram):** نظرة عامة على النظام ككل
- **Level 1:** العمليات الرئيسية
- **Level 2:** تفاصيل العمليات المعقدة
- **Entity Relationship Diagram (ERD):** علاقات قاعدة البيانات

---

## 🔍 Level 0: Context Diagram (مخطط السياق)

يوضح النظام ككل والكيانات الخارجية التي تتفاعل معه.

```
                                 ┌─────────────────────────┐
                                 │                         │
                                 │   Super Admin           │
                                 │                         │
                                 └───────────┬─────────────┘
                                             │
                    ┌────────────────────────┼────────────────────────┐
                    │                        │                        │
         ┌──────────▼──────────┐  ┌─────────▼─────────┐  ┌──────────▼──────────┐
         │                     │  │                   │  │                     │
         │  School Admin       │  │   Teacher         │  │   Student           │
         │                     │  │                   │  │                     │
         └──────────┬──────────┘  └─────────┬─────────┘  └──────────┬──────────┘
                    │                       │                       │
                    │                       │                       │
                    └───────────────────────┼───────────────────────┘
                                           │
                    ┌──────────────────────▼───────────────────────┐
                    │                                              │
                    │         ┌────────────────────────┐           │
                    │         │                        │           │
                    │         │  منصة قيمّ التعليمية  │           │
                    │         │   Qiyamm Platform     │           │
                    │         │                        │           │
                    │         └────────────────────────┘           │
                    │                                              │
                    └──────────────────────┬───────────────────────┘
                                          │
                         ┌────────────────┼────────────────┐
                         │                │                │
              ┌──────────▼──────────┐  ┌──▼───────┐  ┌───▼──────────┐
              │                     │  │          │  │              │
              │   Parent            │  │  Email   │  │  Database    │
              │                     │  │  Service │  │              │
              └─────────────────────┘  └──────────┘  └──────────────┘
```

### الكيانات الخارجية (External Entities)

| الكيان | الوصف | التفاعلات |
|--------|-------|-----------|
| **Super Admin** | المسؤول الأعلى | إدارة النظام، المحتوى، المدارس |
| **School Admin** | مدير المدرسة | إدارة المعلمين والطلاب، قبول الطلبات |
| **Teacher** | المعلم | مراجعة الأنشطة، إدارة الفصول |
| **Student** | الطالب | تعلم القيم، تسليم الأنشطة، Gamification |
| **Parent** | ولي الأمر | متابعة الأبناء |
| **Email Service** | خدمة البريد الإلكتروني | إرسال الإشعارات والتنبيهات |
| **Database** | قاعدة البيانات | تخزين واسترجاع البيانات |

---

## 📊 Level 1: DFD (العمليات الرئيسية)

```
                      ┌───────────────────────────────────────────────┐
                      │         External Entities                     │
                      │  [Users, Email Service, File Storage]         │
                      └───────────┬───────────────────────────────────┘
                                  │
          ┌───────────────────────┼───────────────────────┐
          │                       │                       │
    ┌─────▼──────┐       ┌────────▼────────┐      ┌─────▼──────┐
    │            │       │                 │      │            │
    │  1.0       │       │     2.0         │      │   3.0      │
    │  المصادقة  │◄──────┤  إدارة         │◄─────┤  إدارة     │
    │  والتحقق   │       │  المستخدمين    │      │  المدارس   │
    │            │       │                 │      │            │
    └─────┬──────┘       └────────┬────────┘      └─────┬──────┘
          │                       │                     │
          │                       │                     │
          ▼                       ▼                     ▼
    ┌──────────────────────────────────────────────────────────┐
    │                      D1: Users                           │
    └──────────────────────────────────────────────────────────┘
          │                       │                     │
          │                       │                     │
    ┌─────▼──────┐       ┌────────▼────────┐      ┌─────▼──────┐
    │            │       │                 │      │            │
    │  4.0       │       │     5.0         │      │   6.0      │
    │  المحتوى   │◄──────┤  الأنشطة       │◄─────┤  التقييم   │
    │  التعليمي  │       │  والتسليمات   │      │  والدرجات  │
    │            │       │                 │      │            │
    └─────┬──────┘       └────────┬────────┘      └─────┬──────┘
          │                       │                     │
          ▼                       ▼                     ▼
    ┌──────────────────────────────────────────────────────────┐
    │          D2: Content, D3: Activities, D4: Submissions    │
    └──────────────────────────────────────────────────────────┘
          │                       │                     │
          │                       │                     │
    ┌─────▼──────┐       ┌────────▼────────┐      ┌─────▼──────┐
    │            │       │                 │      │            │
    │  7.0       │       │     8.0         │      │   9.0      │
    │ Gamification│◄─────┤  الإشعارات     │◄─────┤  التقارير  │
    │            │       │  والرسائل      │      │  والتحليل  │
    │            │       │                 │      │            │
    └─────┬──────┘       └────────┬────────┘      └─────┬──────┘
          │                       │                     │
          ▼                       ▼                     ▼
    ┌──────────────────────────────────────────────────────────┐
    │         D5: Points/Coins/Badges, D6: Notifications       │
    └──────────────────────────────────────────────────────────┘
```

### شرح العمليات الرئيسية

| رقم العملية | الاسم | الوصف |
|-------------|------|-------|
| **1.0** | المصادقة والتحقق | تسجيل الدخول، 2FA، كلمات المرور |
| **2.0** | إدارة المستخدمين | CRUD للمستخدمين، الأدوار، الصلاحيات |
| **3.0** | إدارة المدارس | CRUD للمدارس، الفصول، التسجيل الذاتي |
| **4.0** | المحتوى التعليمي | إدارة القيم، المفاهيم، الدروس، الأنشطة |
| **5.0** | الأنشطة والتسليمات | تسليم الأنشطة من الطلاب |
| **6.0** | التقييم والدرجات | مراجعة الأنشطة وإعطاء الدرجات |
| **7.0** | Gamification | إدارة النقاط، العملات، الشارات |
| **8.0** | الإشعارات والرسائل | إرسال الإشعارات والرسائل |
| **9.0** | التقارير والتحليل | إنشاء التقارير والإحصائيات |

---

## 🔬 Level 2: تفصيل العمليات المعقدة

### Process 1.0: المصادقة والتحقق (Authentication & Verification)

```
┌─────────────┐
│   User      │
└──────┬──────┘
       │
       │ [Email + Password]
       │
       ▼
┌─────────────────────────┐
│  1.1                    │
│  التحقق من بيانات       │         ┌──────────────┐
│  الاعتماد               │◄────────┤  D1: Users   │
│                         │         └──────────────┘
└──────────┬──────────────┘
           │
           │ [Valid Credentials]
           │
           ▼
┌─────────────────────────┐
│  1.2                    │
│  التحقق من 2FA          │
│  (إذا كان مفعلاً)        │
└──────────┬──────────────┘
           │
           │ [Send 2FA Code]
           │
           ▼
┌─────────────────────────┐
│  Email Service          │
└─────────────────────────┘
           │
           │ [User enters code]
           │
           ▼
┌─────────────────────────┐
│  1.3                    │
│  التحقق من كود 2FA      │
│                         │
└──────────┬──────────────┘
           │
           │ [Valid Code]
           │
           ▼
┌─────────────────────────┐
│  1.4                    │
│  إنشاء جلسة (Session)   │         ┌──────────────┐
│                         │────────►│  D7: Sessions│
└──────────┬──────────────┘         └──────────────┘
           │
           │ [Redirect to Dashboard]
           │
           ▼
┌─────────────┐
│   User      │
│  Dashboard  │
└─────────────┘
```

---

### Process 5.0: الأنشطة والتسليمات (Activities & Submissions)

```
┌─────────────┐
│  Student    │
└──────┬──────┘
       │
       │ [Select Activity]
       │
       ▼
┌─────────────────────────┐
│  5.1                    │
│  عرض النشاط             │         ┌──────────────┐
│                         │◄────────┤D3: Activities│
└──────────┬──────────────┘         └──────────────┘
           │
           │ [Activity Details]
           │
           ▼
┌─────────────┐
│  Student    │
│  (Fill Form)│
└──────┬──────┘
       │
       │ [Answer + File (optional)]
       │
       ▼
┌─────────────────────────┐
│  5.2                    │
│  التحقق من الإجابة      │
│  (Validation)           │
└──────────┬──────────────┘
           │
           │ [Valid Submission]
           │
           ▼
┌─────────────────────────┐
│  5.3                    │
│  حفظ التسليم            │         ┌──────────────┐
│                         │────────►│D4: Submissions│
└──────────┬──────────────┘         └──────────────┘
           │
           │ [Submission Saved]
           │
           ▼
┌─────────────────────────┐
│  5.4                    │
│  رفع الملف (إن وجد)     │         ┌──────────────┐
│                         │────────►│  File Storage│
└──────────┬──────────────┘         └──────────────┘
           │
           │ [File Uploaded]
           │
           ▼
┌─────────────────────────┐
│  5.5                    │
│  إشعار المعلم           │
│  (Notification)         │
└──────────┬──────────────┘
           │
           │ [Create Notification]
           │
           ├─────────────────┐
           │                 │
           ▼                 ▼
┌─────────────────┐   ┌─────────────────┐
│D6: Notifications│   │  Email Service  │
└─────────────────┘   └─────────────────┘
           │                 │
           │                 │
           ▼                 ▼
┌─────────────┐   ┌─────────────┐
│  Teacher    │   │  Teacher    │
│  (Web)      │   │  (Email)    │
└─────────────┘   └─────────────┘
```

---

### Process 6.0: التقييم والدرجات (Evaluation & Grading)

```
┌─────────────┐
│  Teacher    │
└──────┬──────┘
       │
       │ [View Pending Activities]
       │
       ▼
┌─────────────────────────┐
│  6.1                    │
│  عرض التسليمات المعلقة  │         ┌──────────────┐
│                         │◄────────┤D4: Submissions│
└──────────┬──────────────┘         └──────────────┘
           │
           │ [Submission Details]
           │
           ▼
┌─────────────┐
│  Teacher    │
│  (Review)   │
└──────┬──────┘
       │
       │ [Score + Feedback]
       │
       ▼
┌─────────────────────────┐
│  6.2                    │
│  حفظ التقييم            │         ┌──────────────┐
│                         │────────►│D4: Submissions│
└──────────┬──────────────┘         └──────────────┘
           │
           │ [Evaluation Saved]
           │
           ├─────────────────┬─────────────────┐
           │                 │                 │
           ▼                 ▼                 ▼
┌─────────────────┐  ┌─────────────┐  ┌─────────────┐
│  6.3            │  │  6.4        │  │  6.5        │
│  تحديث النقاط   │  │  تحديث      │  │  إشعار      │
│  والعملات       │  │  التقدم     │  │  الطالب     │
│                 │  │             │  │             │
└────────┬────────┘  └──────┬──────┘  └──────┬──────┘
         │                  │                │
         ▼                  ▼                ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│D5: Points/   │  │D2: User      │  │D6:           │
│   Coins      │  │   Progress   │  │ Notifications│
└──────────────┘  └──────────────┘  └──────┬───────┘
                                           │
                                           │
                                           ▼
                                  ┌─────────────────┐
                                  │  Email Service  │
                                  └─────────┬───────┘
                                           │
                                           ▼
                                  ┌─────────────┐
                                  │  Student    │
                                  │  (Notified) │
                                  └─────────────┘
```

---

### Process 7.0: Gamification

```
┌─────────────┐
│  Student    │
│  completes  │
│  Activity   │
└──────┬──────┘
       │
       │ [Activity Completed]
       │
       ▼
┌─────────────────────────┐
│  7.1                    │
│  حساب المكافآت          │         ┌──────────────┐
│  (Calculate Rewards)    │◄────────┤ D3: Activity │
│                         │         │    Rules     │
└──────────┬──────────────┘         └──────────────┘
           │
           │ [Points: 10, Coins: 5]
           │
           ▼
┌─────────────────────────┐
│  7.2                    │
│  إضافة النقاط والعملات  │         ┌──────────────┐
│                         │────────►│D5: Points/   │
└──────────┬──────────────┘         │    Coins     │
           │                        └──────────────┘
           │
           ▼
┌─────────────────────────┐
│  7.3                    │
│  التحقق من الشارات      │         ┌──────────────┐
│  (Check Badge Criteria) │◄────────┤D5: Badges    │
└──────────┬──────────────┘         └──────────────┘
           │
           │ [Criteria Met?]
           │
    ┌──────┴──────┐
    │  YES        │ NO
    │             │
    ▼             ▼
┌─────────────┐ ┌──────────┐
│  7.4        │ │  End     │
│  منح شارة   │ └──────────┘
│             │
└──────┬──────┘
       │
       ▼
┌──────────────┐
│D5: User      │
│   Badges     │
└──────┬───────┘
       │
       ▼
┌─────────────────────────┐
│  7.5                    │
│  تحديث Streak           │         ┌──────────────┐
│                         │◄───────►│D5: Streaks   │
└──────────┬──────────────┘         └──────────────┘
           │
           ▼
┌─────────────────────────┐
│  7.6                    │
│  تحديث Leaderboard      │         ┌──────────────┐
│  (Cache)                │────────►│  Cache       │
└──────────┬──────────────┘         └──────────────┘
           │
           ▼
┌─────────────┐
│  Student    │
│  (Updated)  │
└─────────────┘
```

---

## 🗃️ Data Stores (مخازن البيانات)

| Store ID | الاسم | الوصف | النوع |
|----------|------|-------|------|
| **D1** | Users | بيانات المستخدمين | Database Table |
| **D2** | Content | المحتوى التعليمي (Values, Concepts, Meanings, Lessons, Activities) | Database Tables |
| **D3** | Activities | الأنشطة | Database Table |
| **D4** | Submissions | تسليمات الأنشطة | Database Table |
| **D5** | Gamification | النقاط، العملات، الشارات، Streaks | Database Tables |
| **D6** | Notifications | الإشعارات | Database Table |
| **D7** | Sessions | جلسات المستخدمين | Database Table / Cache |
| **D8** | Settings | إعدادات النظام | Database Table |
| **D9** | Logs | سجلات النشاط | Database Table / Files |

---

## 🔗 Entity Relationship Diagram (ERD)

مخطط علاقات قاعدة البيانات الرئيسية:

```
┌─────────────────────────┐
│        users            │
├─────────────────────────┤
│ PK  id                  │
│     name                │
│     email               │
│     password            │
│     role                │
│ FK  school_id           │
│     status              │
│     two_factor_enabled  │
└──────┬────────┬─────────┘
       │        │
       │        └──────────────────────────┐
       │                                   │
       │                                   │
┌──────▼──────────────┐         ┌─────────▼────────────┐
│   schools           │         │   classrooms         │
├─────────────────────┤         ├──────────────────────┤
│ PK  id              │         │ PK  id               │
│     name            │         │     name             │
│     email           │         │ FK  school_id        │
│     phone           │         │ FK  teacher_id       │
│     address         │         │     grade_level      │
│     logo            │         └──────────────────────┘
│     status          │
│     *_registration_ │
│     tokens          │
└─────────────────────┘
       │
       │
┌──────▼──────────────┐
│ registration_       │
│ requests            │
├─────────────────────┤
│ PK  id              │
│     name            │
│     email           │
│ FK  school_id       │
│     type            │
│     status          │
└─────────────────────┘


┌─────────────────────────┐
│        values           │
├─────────────────────────┤
│ PK  id                  │
│     name                │
│     description         │
│     icon                │
│     order               │
└──────┬──────────────────┘
       │
       │ 1:N
       │
┌──────▼──────────────┐
│   concepts          │
├─────────────────────┤
│ PK  id              │
│ FK  value_id        │
│     name            │
│     description     │
│     order           │
└──────┬──────────────┘
       │
       │ 1:N
       │
┌──────▼──────────────┐
│   meanings          │
├─────────────────────┤
│ PK  id              │
│ FK  concept_id      │
│     name            │
│     description     │
│     order           │
└──────┬──────────────┘
       │
       │ 1:N
       │
┌──────▼──────────────┐
│   lessons           │
├─────────────────────┤
│ PK  id              │
│ FK  meaning_id      │
│     title           │
│     content         │
│     image           │
│     video_url       │
│     order           │
└──────┬──────────────┘
       │
       │ 1:N
       │
┌──────▼──────────────┐
│   activities        │
├─────────────────────┤
│ PK  id              │
│ FK  lesson_id       │
│     title           │
│     description     │
│     type            │
│     content (JSON)  │
│     max_points      │
│     order           │
└──────┬──────────────┘
       │
       │ 1:N
       │
┌──────▼────────────────────┐
│   activity_submissions    │
├───────────────────────────┤
│ PK  id                    │
│ FK  activity_id           │
│ FK  student_id            │
│     content               │
│     attachments (JSON)    │
│     score                 │
│     feedback              │
│     status                │
│ FK  reviewed_by           │
│     submitted_at          │
│     reviewed_at           │
└───────────────────────────┘


┌─────────────────────────┐
│        points           │
├─────────────────────────┤
│ PK  id                  │
│ FK  user_id             │
│     points              │
│     reason              │
│     source_type         │
│     source_id           │
└─────────────────────────┘

┌─────────────────────────┐
│        coins            │
├─────────────────────────┤
│ PK  id                  │
│ FK  user_id             │
│     coins               │
│     reason              │
│     source_type         │
│     source_id           │
└─────────────────────────┘

┌─────────────────────────┐
│        badges           │
├─────────────────────────┤
│ PK  id                  │
│     name                │
│     description         │
│     icon                │
│     condition (JSON)    │
└──────┬──────────────────┘
       │
       │ M:N
       │
┌──────▼──────────────┐
│   user_badges       │
├─────────────────────┤
│ PK  id              │
│ FK  user_id         │
│ FK  badge_id        │
│     earned_at       │
└─────────────────────┘

┌─────────────────────────┐
│        streaks          │
├─────────────────────────┤
│ PK  id                  │
│ FK  user_id             │
│     current_streak      │
│     longest_streak      │
│     last_activity_date  │
└─────────────────────────┘
```

### علاقات الجداول الرئيسية

| الجدول الأول | العلاقة | الجدول الثاني | النوع |
|--------------|---------|----------------|-------|
| users | school_id → id | schools | N:1 |
| classrooms | school_id → id | schools | N:1 |
| classrooms | teacher_id → id | users | N:1 |
| values | - | concepts | 1:N |
| concepts | value_id → id | meanings | 1:N |
| meanings | concept_id → id | lessons | 1:N |
| lessons | meaning_id → id | activities | 1:N |
| activities | lesson_id → id | activity_submissions | 1:N |
| activity_submissions | student_id → id | users | N:1 |
| activity_submissions | reviewed_by → id | users | N:1 |
| points | user_id → id | users | N:1 |
| coins | user_id → id | users | N:1 |
| user_badges | user_id → id | users | N:1 |
| user_badges | badge_id → id | badges | N:1 |
| streaks | user_id → id | users | 1:1 |

---

## 📈 تدفق البيانات للسيناريوهات الشائعة

### سيناريو 1: طالب يكمل قيمة بالكامل

```
1. Student completes last activity in a value
   └─► Trigger: ActivitySubmission.reviewed & approved
       │
2. Update student progress
   ├─► Update value_progress table
   ├─► Mark value as completed
   │
3. Award rewards
   ├─► Add 50 points (completed value)
   ├─► Add 25 coins
   ├─► Check badge criteria
   │   └─► Award badge if criteria met
   │
4. Update streak
   ├─► Check last activity date
   ├─► Increment/reset streak
   │
5. Update leaderboard (cached)
   ├─► Recalculate student rank
   ├─► Update Redis cache
   │
6. Send notifications
   ├─► In-app notification
   ├─► Email to student
   └─► Email to parent
```

### سيناريو 2: School Admin يقبل طلب تسجيل طالب

```
1. School Admin reviews request
   └─► GET /school-admin/registration-requests/{id}
       │
2. School Admin accepts request
   ├─► POST /school-admin/registration-requests/{id}/accept
   ├─► Assign classroom
   │
3. Create user account
   ├─► Create user record (role: student)
   ├─► Generate temporary password
   ├─► Hash password (bcrypt)
   │
4. Update request status
   ├─► Set status = 'accepted'
   ├─► Set accepted_at timestamp
   │
5. Send welcome email
   ├─► Queue email job
   ├─► Email contains:
   │   ├─► Login credentials
   │   ├─► Temporary password
   │   └─► Instructions
   │
6. Notify parent (if linked)
   └─► Send notification to parent account
```

---

## 🔐 تدفق البيانات الآمن (Secure Data Flow)

### تدفق البيانات الحساسة

```
User Input
    │
    ├─► [1] CSRF Validation
    │
    ├─► [2] Input Sanitization
    │       (Strip HTML, XSS Prevention)
    │
    ├─► [3] Validation Rules
    │       (Laravel Validation)
    │
    ├─► [4] Authentication Check
    │       (Session / Token)
    │
    ├─► [5] Authorization Check
    │       (Roles & Permissions)
    │
    ├─► [6] Business Logic
    │       (Service Layer)
    │
    ├─► [7] Data Access Layer
    │       (Eloquent ORM - SQL Injection Prevention)
    │
    ├─► [8] Database
    │       (Encrypted Fields if sensitive)
    │
    └─► [9] Response
        ├─► Data Escaping (Blade Auto-Escape)
        ├─► HTTPS (SSL/TLS)
        └─► HTTPOnly Cookies
```

---

## 📊 ملخص تدفق البيانات

### النقاط الرئيسية

1. **تدفق واضح ومنظم:**
   - البيانات تمر عبر طبقات محددة
   - فصل واضح بين العرض والمنطق والبيانات

2. **الأمان في كل مرحلة:**
   - CSRF Protection
   - Input Validation
   - SQL Injection Prevention
   - XSS Protection

3. **الأداء:**
   - استخدام Cache (Redis) للبيانات المتكررة
   - Queue للعمليات الثقيلة
   - Eager Loading لتجنب N+1

4. **قابلية التوسع:**
   - معمارية قابلة للتوزيع
   - Database Sharding (مستقبلاً)
   - Microservices (مستقبلاً)

---

## ✅ معايير النجاح

تم تحقيق المعايير التالية:

1. ✅ **وضوح كامل لرؤية النظام**
   - جميع التدفقات موثقة بوضوح
   - DFD متعدد المستويات

2. ✅ **تعريف جميع تدفقات النظام بدون غموض**
   - كل عملية لها مدخلات ومخرجات واضحة
   - العلاقات بين الكيانات محددة

3. ✅ **اعتماد IA والـ Architecture كبنية نهائية**
   - البنية موثقة بالكامل
   - جاهزة للتنفيذ

4. ✅ **قبول الواجهات الأولية من الجهات المعنية**
   - Wireframes جاهزة
   - User Journeys محددة
   - Pain Points معروفة

---

**تاريخ الإعداد:** ديسمبر 23, 2025  
**أداة الرسم المقترحة:** Draw.io / Lucidchart / Microsoft Visio  
**الحالة:** معتمد كوثيقة نهائية ✅
