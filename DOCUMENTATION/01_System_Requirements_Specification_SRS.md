# وثيقة متطلبات النظام (SRS)
## System Requirements Specification
### منصة قيمّ - EdTech Platform

---

## 📋 معلومات الوثيقة

| البند | التفاصيل |
|------|----------|
| **اسم المشروع** | منصة قيمّ - Qiyamm Platform |
| **نوع المشروع** | نظام إدارة تعليمية (LMS) مع Gamification |
| **الجهة المستفيدة** | مؤسسة بناء القيم التعليمية |
| **تاريخ الإصدار** | ديسمبر 2025 |
| **حالة الوثيقة** | نسخة نهائية معتمدة |
| **مستوى السرية** | سري |

---

## 🎯 1. نظرة عامة على المشروع

### 1.1 الغرض من النظام
منصة تعليمية رقمية متكاملة تهدف إلى:
- تعزيز القيم الأخلاقية والتربوية لدى الطلاب
- توفير بيئة تعليمية تفاعلية وممتعة
- تسهيل التواصل بين المعلمين والطلاب وأولياء الأمور
- تتبع تقدم الطلاب وتقييم أدائهم
- تحفيز الطلاب عبر نظام Gamification متقدم

### 1.2 نطاق المشروع
النظام يشمل:
- ✅ نظام إدارة محتوى تعليمي هرمي (Values → Concepts → Meanings → Lessons → Activities)
- ✅ نظام مصادقة متعدد الأدوار مع 2FA
- ✅ لوحات تحكم مخصصة لكل دور (5 أدوار رئيسية)
- ✅ نظام Gamification شامل (نقاط، عملات، شارات، تيجان)
- ✅ نظام إدارة المدارس والفروع
- ✅ نظام التسجيل الذاتي عبر روابط وQR Codes
- ✅ نظام التقارير والتحليلات
- ✅ واجهة برمجية (REST API) للتطبيقات المحمولة

### 1.3 الفئات المستهدفة

| الدور | الوصف | العدد المتوقع |
|------|--------|---------------|
| **Super Admin** | المسؤول الأعلى للنظام | 1-3 مستخدمين |
| **School Admin** | مدير المدرسة | 50-100 مدرسة |
| **Teacher** | المعلم | 500-1000 معلم |
| **Student** | الطالب | 10,000-50,000 طالب |
| **Parent** | ولي الأمر | 8,000-40,000 ولي أمر |

---

## 🔧 2. المتطلبات الوظيفية (Functional Requirements)

### 2.1 نظام المصادقة والأمان (FR-AUTH)

#### FR-AUTH-001: تسجيل الدخول
- **الأولوية:** عالية جداً ⭐⭐⭐
- **الوصف:** نظام تسجيل دخول موحد لجميع الأدوار
- **المتطلبات:**
  - تسجيل دخول بالبريد الإلكتروني وكلمة المرور
  - كشف الدور التلقائي وإعادة التوجيه المناسب
  - Rate Limiting: 20 محاولة/دقيقة
  - Exponential Backoff للحماية من Brute Force
  - حفظ IP Address وUser Agent لكل جلسة
  - Remember Me لمدة 30 يوم

#### FR-AUTH-002: التحقق بخطوتين (2FA)
- **الأولوية:** عالية جداً ⭐⭐⭐
- **الوصف:** طبقة أمان إضافية عبر البريد الإلكتروني
- **المتطلبات:**
  - إرسال كود مكون من 6 أرقام
  - صلاحية الكود: 10 دقائق
  - إعادة إرسال الكود: مرة واحدة كل دقيقتين
  - تعطيل 2FA مؤقتاً بعد 5 محاولات فاشلة
  - Rate Limiting: 10 محاولات/دقيقة

#### FR-AUTH-003: كلمات المرور المؤقتة
- **الأولوية:** عالية ⭐⭐
- **الوصف:** إجبار تغيير كلمة المرور عند التسجيل الأول
- **المتطلبات:**
  - توليد كلمات مرور عشوائية قوية
  - إرسال كلمة المرور عبر البريد الإلكتروني
  - منع الوصول لأي صفحة حتى تغيير كلمة المرور
  - متطلبات كلمة المرور الجديدة:
    - 8 أحرف على الأقل
    - حرف كبير + حرف صغير + رقم + رمز خاص

#### FR-AUTH-004: استعادة كلمة المرور
- **الأولوية:** متوسطة ⭐
- **الوصف:** نظام استعادة كلمة المرور المنسية
- **المتطلبات:**
  - رابط إعادة تعيين صالح لمدة ساعة واحدة
  - Rate Limiting: 5 طلبات/ساعة لكل بريد إلكتروني
  - إلغاء جميع الجلسات القديمة بعد التغيير
  - إشعار بريدي بنجاح العملية

---

### 2.2 نظام Super Admin (FR-SUPER)

#### FR-SUPER-001: لوحة التحكم الرئيسية
- **الأولوية:** عالية ⭐⭐⭐
- **الإحصائيات المطلوبة:**
  - إجمالي المستخدمين (حسب الدور)
  - إجمالي المدارس (نشطة / معطلة)
  - إجمالي المحتوى (قيم، مفاهيم، دروس، أنشطة)
  - آخر 10 مستخدمين مسجلين
  - آخر 10 مدارس مضافة
  - نشاط اليوم (تسجيلات دخول، تسليمات)

#### FR-SUPER-002: إدارة المستخدمين
- **الأولوية:** عالية جداً ⭐⭐⭐
- **العمليات المطلوبة:**
  - ✅ عرض جميع المستخدمين مع فلترة حسب الدور
  - ✅ إنشاء مستخدم جديد (أي دور)
  - ✅ تعديل بيانات المستخدم
  - ✅ تعطيل/تفعيل الحساب
  - ✅ حذف المستخدم (Soft Delete)
  - ✅ إعادة تعيين كلمة المرور
  - ✅ تفعيل/تعطيل 2FA للمستخدم
  - ✅ عرض Activity Log للمستخدم

#### FR-SUPER-003: إدارة المدارس
- **الأولوية:** عالية جداً ⭐⭐⭐
- **العمليات المطلوبة:**
  - ✅ CRUD كامل للمدارس
  - ✅ إدارة فروع المدرسة
  - ✅ توليد روابط تسجيل فريدة (Teacher, Student, Parent)
  - ✅ توليد QR Codes قابلة للطباعة
  - ✅ عرض إحصائيات المدرسة
  - ✅ تصدير قائمة بالمعلمين والطلاب (Excel)

#### FR-SUPER-004: إدارة المحتوى التعليمي
- **الأولوية:** عالية جداً ⭐⭐⭐
- **الهيكل الهرمي:**
```
Value (القيمة)
  ├── Concept (المفهوم)
  │     ├── Meaning (المعنى)
  │     │     ├── Lesson (الدرس)
  │     │     │     ├── Activity (النشاط)
```
- **العمليات:**
  - ✅ CRUD كامل لكل مستوى
  - ✅ ترتيب العناصر (order field)
  - ✅ رفع صور وملفات لكل عنصر
  - ✅ تفعيل/تعطيل العناصر
  - ✅ نسخ المحتوى لمدارس أخرى

#### FR-SUPER-005: Theme Customization
- **الأولوية:** متوسطة ⭐⭐
- **الإعدادات:**
  - اختيار الثيم (فاتح / داكن / مخصص)
  - تخصيص الألوان (Primary, Secondary, Text, Background)
  - اختيار الخط (IBM Plex, Cairo, Tajawal, Almarai)
  - رفع اللوجو والفافيكون
  - رفع صورة خلفية الصفحة الرئيسية

#### FR-SUPER-006: Page Builder
- **الأولوية:** متوسطة ⭐
- **المميزات:**
  - إنشاء صفحات ديناميكية بدون برمجة
  - Drag & Drop للمكونات
  - المكونات المتاحة:
    - Hero Section
    - Heading (H1-H6)
    - Paragraph
    - Button
    - Image
    - Cards Grid
    - Video (YouTube/Vimeo)
    - Spacer
  - معاينة الصفحة قبل النشر
  - إعدادات SEO (Meta Title, Description, OG Image)

#### FR-SUPER-007: التقارير والتحليلات
- **الأولوية:** عالية ⭐⭐⭐
- **التقارير المطلوبة:**
  - تقرير أداء الطلاب (حسب المدرسة/الفصل)
  - تقرير تقدم الطلاب في القيم
  - تقرير تفاعل المعلمين
  - تقرير الأنشطة الأكثر إنجازاً
  - تقرير المدارس الأكثر نشاطاً
  - تصدير التقارير (Excel, PDF)

---

### 2.3 نظام School Admin (FR-SCHOOL)

#### FR-SCHOOL-001: لوحة التحكم
- **الأولوية:** عالية ⭐⭐⭐
- **الإحصائيات:**
  - إجمالي المعلمين/الطلاب/أولياء الأمور
  - إجمالي الفصول
  - طلبات التسجيل المعلقة
  - نشاط اليوم
  - أكثر 5 طلاب تفاعلاً
  - أكثر 5 معلمين نشاطاً

#### FR-SCHOOL-002: إدارة المعلمين
- **الأولوية:** عالية جداً ⭐⭐⭐
- **العمليات:**
  - ✅ CRUD كامل للمعلمين
  - ✅ تعيين الفصول للمعلم
  - ✅ تفعيل/تعطيل الحساب
  - ✅ إرسال كلمة مرور مؤقتة
  - ✅ عرض تقييمات المعلم من الطلاب

#### FR-SCHOOL-003: إدارة الطلاب
- **الأولوية:** عالية جداً ⭐⭐⭐
- **العمليات:**
  - ✅ CRUD كامل للطلاب
  - ✅ نقل الطالب بين الفصول
  - ✅ تعيين ولي أمر للطالب
  - ✅ عرض تقدم الطالب
  - ✅ عرض إحصائيات Gamification

#### FR-SCHOOL-004: إدارة أولياء الأمور
- **الأولوية:** عالية ⭐⭐
- **العمليات:**
  - ✅ CRUD كامل لأولياء الأمور
  - ✅ ربط ولي أمر بعدة أطفال
  - ✅ إرسال إشعارات لولي الأمر

#### FR-SCHOOL-005: إدارة الفصول
- **الأولوية:** عالية ⭐⭐⭐
- **العمليات:**
  - ✅ CRUD كامل للفصول
  - ✅ تعيين معلم للفصل
  - ✅ إضافة/إزالة طلاب من الفصل
  - ✅ عرض إحصائيات الفصل

#### FR-SCHOOL-006: إدارة طلبات التسجيل
- **الأولوية:** عالية جداً ⭐⭐⭐
- **العمليات:**
  - ✅ عرض طلبات التسجيل (معلم/طالب/ولي أمر)
  - ✅ قبول/رفض الطلب
  - ✅ إرسال إشعار بريدي بالقرار
  - ✅ توليد كلمة مرور مؤقتة عند القبول

#### FR-SCHOOL-007: روابط التسجيل الذاتي
- **الأولوية:** عالية ⭐⭐⭐
- **المتطلبات:**
  - ✅ رابط فريد لكل دور (Teacher, Student, Parent)
  - ✅ QR Code قابل للطباعة
  - ✅ Token آمن ومشفر (256-bit)
  - ✅ إمكانية إعادة توليد الرابط
  - ✅ إحصائيات عدد التسجيلات من كل رابط

---

### 2.4 نظام المعلم (FR-TEACHER)

#### FR-TEACHER-001: لوحة التحكم
- **الأولوية:** عالية ⭐⭐⭐
- **الإحصائيات:**
  - عدد الفصول الخاصة بالمعلم
  - إجمالي الطلاب
  - الأنشطة المعلقة (تحتاج مراجعة)
  - متوسط تقييم المعلم
  - أكثر 5 طلاب نشاطاً

#### FR-TEACHER-002: إدارة الفصول
- **الأولوية:** عالية ⭐⭐⭐
- **العمليات:**
  - ✅ عرض الفصول المسندة للمعلم
  - ✅ عرض طلاب كل فصل
  - ✅ عرض إحصائيات الفصل
  - ✅ تصدير قائمة الطلاب (Excel)

#### FR-TEACHER-003: مراجعة الأنشطة
- **الأولوية:** عالية جداً ⭐⭐⭐
- **العمليات:**
  - ✅ عرض الأنشطة المعلقة
  - ✅ عرض تفاصيل التسليم (نص، ملفات)
  - ✅ إعطاء درجة (0-100)
  - ✅ كتابة ملاحظات للطالب
  - ✅ قبول/رفض التسليم
  - ✅ إشعار الطالب بالنتيجة

#### FR-TEACHER-004: التقارير
- **الأولوية:** متوسطة ⭐⭐
- **التقارير:**
  - تقرير تقدم الطالب في القيم
  - تقرير أداء الفصل
  - تقرير الأنشطة الأكثر صعوبة
  - مقارنة أداء الفصول

#### FR-TEACHER-005: إدارة الأنشطة (مطلوب تطوير)
- **الأولوية:** عالية ⭐⭐⭐
- **العمليات المطلوبة:**
  - ❌ إنشاء نشاط جديد
  - ❌ تعديل نشاط
  - ❌ حذف نشاط
  - ❌ تحديد نوع النشاط (Quiz, File Upload, Practical, Team)
  - ❌ إرفاق ملفات مع النشاط
  - ❌ تعيين نشاط لفصول محددة
  - ❌ تحديد تاريخ استحقاق

---

### 2.5 نظام الطالب (FR-STUDENT)

#### FR-STUDENT-001: لوحة التحكم
- **الأولوية:** عالية جداً ⭐⭐⭐
- **العناصر:**
  - خريطة التعلم (Values Tree)
  - التقدم الحالي (%)
  - النقاط والعملات
  - الشارات المكتسبة
  - Streak الحالي
  - الترتيب في Leaderboard
  - الأنشطة المعلقة

#### FR-STUDENT-002: خريطة التعلم (Learning Path)
- **الأولوية:** عالية جداً ⭐⭐⭐
- **المتطلبات:**
  - ✅ عرض القيم بشكل هرمي
  - ✅ فتح القيم بالتسلسل (القيمة التالية مقفلة حتى إنهاء الحالية)
  - ✅ عرض التقدم لكل قيمة (%)
  - ✅ أيقونات توضح الحالة (مقفل 🔒، قيد التنفيذ 📖، مكتمل ✅)
  - ✅ عداد الدروس والأنشطة المنجزة

#### FR-STUDENT-003: عرض الدروس والأنشطة
- **الأولوية:** عالية جداً ⭐⭐⭐
- **المتطلبات:**
  - ✅ عرض الدرس بشكل تفاعلي
  - ✅ دعم النصوص والصور والفيديوهات
  - ✅ عرض الأنشطة المرتبطة بالدرس
  - ✅ تحديد حالة النشاط (لم يبدأ، قيد التنفيذ، مكتمل)

#### FR-STUDENT-004: تسليم الأنشطة
- **الأولوية:** عالية جداً ⭐⭐⭐
- **أنواع الأنشطة:**
  - ✅ نشاط نصي (إجابة كتابية)
  - ✅ رفع ملف
  - ⚠️ اختبار (Quiz) - قيد التطوير
  - ⚠️ نشاط جماعي (Team Activity) - قيد التطوير

#### FR-STUDENT-005: نظام Gamification
- **الأولوية:** عالية جداً ⭐⭐⭐
- **العناصر:**
  - ✅ **Points** (النقاط): 
    - 10 نقاط لكل نشاط مكتمل
    - 50 نقطة لكل قيمة مكتملة
  - ✅ **Coins** (العملات):
    - 5 عملات لكل نشاط
    - 25 عملة لكل قيمة
  - ✅ **Badges** (الشارات):
    - شارة لكل قيمة مكتملة
    - شارات خاصة (تفوق، مثابرة، تعاون)
  - ✅ **Crowns** (التيجان):
    - تاج لكل مستوى (Bronze, Silver, Gold, Platinum)
  - ✅ **Streaks** (الأيام المتتالية):
    - عداد الأيام المتتالية
    - مكافآت للـ Streaks الطويلة

#### FR-STUDENT-006: Leaderboard
- **الأولوية:** متوسطة ⭐⭐
- **المتطلبات:**
  - ✅ ترتيب الطلاب حسب النقاط
  - ✅ فلترة حسب (الفصل، المدرسة، عالمي)
  - ✅ عرض أفضل 10 طلاب
  - ✅ إبراز موقع الطالب الحالي

#### FR-STUDENT-007: المتجر (Shop) - قيد التطوير
- **الأولوية:** منخفضة ⭐
- **المتطلبات:**
  - ❌ عرض العناصر القابلة للشراء
  - ❌ شراء العناصر بالعملات
  - ❌ مخزن العناصر المملوكة
  - ❌ استخدام العناصر (Avatars, Themes)

---

### 2.6 نظام ولي الأمر (FR-PARENT)

#### FR-PARENT-001: لوحة التحكم
- **الأولوية:** متوسطة ⭐⭐
- **الإحصائيات:**
  - قائمة الأبناء
  - تقدم كل ابن (%)
  - آخر الأنشطة المنجزة
  - الشارات الجديدة
  - إشعارات من المعلمين

#### FR-PARENT-002: متابعة الأبناء
- **الأولوية:** عالية ⭐⭐⭐
- **المتطلبات:**
  - ✅ عرض تقدم كل ابن
  - ✅ عرض النقاط والعملات
  - ✅ عرض الشارات المكتسبة
  - ✅ عرض آخر الأنشطة
  - ❌ مقارنة أداء الأبناء

#### FR-PARENT-003: التواصل مع المعلمين
- **الأولوية:** متوسطة ⭐
- **المتطلبات:**
  - ❌ إرسال رسالة للمعلم
  - ❌ عرض الرسائل المستلمة
  - ❌ إشعارات البريد الإلكتروني

#### FR-PARENT-004: التقارير
- **الأولوية:** منخفضة ⭐
- **التقارير:**
  - ❌ تقرير شهري بأداء الابن
  - ❌ تقرير مقارنة مع الفصل
  - ❌ تحميل التقرير (PDF)

---

### 2.7 نظام الفرق (Teams) - قيد التطوير ❌

#### FR-TEAM-001: إدارة الفرق (Teacher)
- **الأولوية:** متوسطة ⭐⭐
- **المتطلبات:**
  - ❌ إنشاء فريق جديد
  - ❌ تسمية الفريق
  - ❌ إضافة/إزالة أعضاء
  - ❌ تعيين قائد للفريق
  - ❌ تعيين أنشطة جماعية

#### FR-TEAM-002: مشاركة الطلاب (Student)
- **الأولوية:** متوسطة ⭐⭐
- **المتطلبات:**
  - ❌ عرض فريق الطالب
  - ❌ عرض أعضاء الفريق
  - ❌ المشاركة في الأنشطة الجماعية
  - ❌ نقاش داخلي للفريق (Chat)

#### FR-TEAM-003: تقييم الفرق (Teacher)
- **الأولوية:** متوسطة ⭐
- **المتطلبات:**
  - ❌ تقييم الفريق ككل
  - ❌ تقييم مساهمة كل عضو
  - ❌ منح نقاط جماعية

---

## 🔒 3. المتطلبات غير الوظيفية (Non-Functional Requirements)

### 3.1 الأداء (Performance)
- **NFR-PERF-001**: زمن تحميل الصفحة أقل من 2 ثانية
- **NFR-PERF-002**: استجابة API أقل من 500ms
- **NFR-PERF-003**: دعم 1000 مستخدم متزامن
- **NFR-PERF-004**: استخدام Caching (Redis) للبيانات المتكررة
- **NFR-PERF-005**: Database Indexing محسّن
- **NFR-PERF-006**: Eager Loading لتجنب N+1 Query Problem

### 3.2 الأمان (Security)
- **NFR-SEC-001**: HTTPS إجباري في Production
- **NFR-SEC-002**: CSRF Protection على جميع النماذج
- **NFR-SEC-003**: SQL Injection Prevention (استخدام Eloquent ORM)
- **NFR-SEC-004**: XSS Protection (تنظيف المدخلات)
- **NFR-SEC-005**: Password Hashing (bcrypt)
- **NFR-SEC-006**: Rate Limiting على جميع APIs
- **NFR-SEC-007**: Session Security (HTTPOnly, Secure, SameSite)
- **NFR-SEC-008**: Activity Logging (Spatie ActivityLog)
- **NFR-SEC-009**: File Upload Validation (نوع، حجم، محتوى)
- **NFR-SEC-010**: Two-Factor Authentication (2FA) للأدوار الحساسة

### 3.3 الموثوقية (Reliability)
- **NFR-REL-001**: Uptime 99.5% (سنوياً)
- **NFR-REL-002**: نظام Backup يومي (قاعدة البيانات + الملفات)
- **NFR-REL-003**: استرجاع النظام في أقل من 4 ساعات
- **NFR-REL-004**: Error Logging شامل (Laravel Log)
- **NFR-REL-005**: Transaction Handling للعمليات الحرجة

### 3.4 قابلية الاستخدام (Usability)
- **NFR-USA-001**: واجهة عربية كاملة (RTL)
- **NFR-USA-002**: Responsive Design (Mobile, Tablet, Desktop)
- **NFR-USA-003**: تصميم Glassmorphism فاخر
- **NFR-USA-004**: خط Cairo للعربية
- **NFR-USA-005**: رسائل خطأ واضحة ومفيدة
- **NFR-USA-006**: تنبيهات وإشعارات فورية (Toast Notifications)
- **NFR-USA-007**: Accessibility (WCAG 2.1 Level AA)

### 3.5 قابلية التوسع (Scalability)
- **NFR-SCA-001**: دعم 50,000 طالب
- **NFR-SCA-002**: دعم 1,000 مدرسة
- **NFR-SCA-003**: معمارية قابلة للتوزيع (Load Balancing)
- **NFR-SCA-004**: Database Sharding للبيانات الكبيرة
- **NFR-SCA-005**: CDN لتسريع تحميل الملفات الثابتة

### 3.6 الصيانة (Maintainability)
- **NFR-MAI-001**: توثيق الكود (PHPDoc)
- **NFR-MAI-002**: معايير Laravel (PSR-12)
- **NFR-MAI-003**: Unit Testing للوظائف الحرجة
- **NFR-MAI-004**: Version Control (Git)
- **NFR-MAI-005**: Environment Management (.env)

### 3.7 التوافقية (Compatibility)
- **NFR-COM-001**: PHP 8.2+
- **NFR-COM-002**: Laravel 12.x
- **NFR-COM-003**: MySQL 8.0+ أو PostgreSQL 14+
- **NFR-COM-004**: متصفحات حديثة (Chrome, Firefox, Safari, Edge)
- **NFR-COM-005**: REST API متوافقة مع الهواتف المحمولة

---

## 📊 4. قاعدة البيانات (Database Schema)

### 4.1 الجداول الرئيسية

#### جدول `users`
```sql
- id (bigint, PK)
- name (varchar 255)
- email (varchar 255, unique)
- password (varchar 255)
- role (enum: super_admin, school_admin, teacher, student, parent)
- school_id (bigint, FK → schools.id)
- phone (varchar 20)
- birth_date (date)
- avatar (varchar 255)
- qr_code (varchar 255)
- status (enum: active, inactive, suspended)
- two_factor_enabled (boolean)
- two_factor_code (varchar 6)
- two_factor_expires_at (timestamp)
- password_change_required (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `schools`
```sql
- id (bigint, PK)
- name (varchar 255)
- email (varchar 255)
- phone (varchar 20)
- address (text)
- logo (varchar 255)
- status (enum: active, inactive)
- teacher_registration_token (varchar 255)
- student_registration_token (varchar 255)
- parent_registration_token (varchar 255)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `classrooms`
```sql
- id (bigint, PK)
- name (varchar 255)
- school_id (bigint, FK → schools.id)
- teacher_id (bigint, FK → users.id)
- grade_level (varchar 50)
- academic_year (varchar 20)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `values` (القيم)
```sql
- id (bigint, PK)
- name (varchar 255)
- description (text)
- icon (varchar 255)
- image (varchar 255)
- order (int)
- status (enum: active, inactive)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `concepts` (المفاهيم)
```sql
- id (bigint, PK)
- value_id (bigint, FK → values.id)
- name (varchar 255)
- description (text)
- order (int)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `meanings` (المعاني)
```sql
- id (bigint, PK)
- concept_id (bigint, FK → concepts.id)
- name (varchar 255)
- description (text)
- order (int)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `lessons` (الدروس)
```sql
- id (bigint, PK)
- meaning_id (bigint, FK → meanings.id)
- title (varchar 255)
- content (longtext)
- image (varchar 255)
- video_url (varchar 255)
- order (int)
- status (enum: active, inactive)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `activities` (الأنشطة)
```sql
- id (bigint, PK)
- lesson_id (bigint, FK → lessons.id)
- title (varchar 255)
- description (text)
- type (enum: text, file_upload, quiz, practical, team)
- content (json) -- محتوى النشاط (أسئلة، تعليمات، إلخ)
- attachments (json) -- ملفات مرفقة
- max_points (int, default: 100)
- due_date (date)
- order (int)
- status (enum: active, inactive)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `activity_submissions` (تسليمات الأنشطة)
```sql
- id (bigint, PK)
- activity_id (bigint, FK → activities.id)
- student_id (bigint, FK → users.id)
- content (text) -- الإجابة النصية
- attachments (json) -- ملفات مرفقة
- score (int) -- الدرجة (0-100)
- feedback (text) -- ملاحظات المعلم
- status (enum: pending, reviewed, approved, rejected)
- submitted_at (timestamp)
- reviewed_at (timestamp)
- reviewed_by (bigint, FK → users.id)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `points` (النقاط)
```sql
- id (bigint, PK)
- user_id (bigint, FK → users.id)
- points (int)
- reason (varchar 255)
- source_type (varchar 100) -- Activity, Value, Badge, etc.
- source_id (bigint)
- created_at (timestamp)
```

#### جدول `coins` (العملات)
```sql
- id (bigint, PK)
- user_id (bigint, FK → users.id)
- coins (int)
- reason (varchar 255)
- source_type (varchar 100)
- source_id (bigint)
- created_at (timestamp)
```

#### جدول `badges` (الشارات)
```sql
- id (bigint, PK)
- name (varchar 255)
- description (text)
- icon (varchar 255)
- condition (json) -- شروط الحصول على الشارة
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `user_badges` (شارات المستخدمين)
```sql
- id (bigint, PK)
- user_id (bigint, FK → users.id)
- badge_id (bigint, FK → badges.id)
- earned_at (timestamp)
```

#### جدول `streaks` (الأيام المتتالية)
```sql
- id (bigint, PK)
- user_id (bigint, FK → users.id)
- current_streak (int)
- longest_streak (int)
- last_activity_date (date)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `teams` (الفرق) - قيد التطوير
```sql
- id (bigint, PK)
- name (varchar 255)
- classroom_id (bigint, FK → classrooms.id)
- created_by (bigint, FK → users.id)
- created_at (timestamp)
- updated_at (timestamp)
```

#### جدول `team_members` (أعضاء الفرق) - قيد التطوير
```sql
- id (bigint, PK)
- team_id (bigint, FK → teams.id)
- user_id (bigint, FK → users.id)
- role (enum: leader, member)
- joined_at (timestamp)
```

---

## 🛠️ 5. التقنيات المستخدمة

### 5.1 Backend
- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: SQLite (Development) / MySQL 8.0+ (Production)
- **ORM**: Eloquent
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission
- **Activity Log**: Spatie Laravel Activity Log
- **Backup**: Spatie Laravel Backup

### 5.2 Frontend
- **Template Engine**: Blade
- **CSS Framework**: Bootstrap 5
- **Icons**: Bootstrap Icons + Heroicons
- **JavaScript**: Vanilla JS + Alpine.js (للتفاعلية الخفيفة)
- **Charts**: Chart.js
- **Rich Text Editor**: Summernote / TinyMCE

### 5.3 الأدوات والمكتبات
- **Excel**: Maatwebsite Excel
- **PDF**: DomPDF
- **QR Code**: SimpleSoftwareIO/simple-qrcode
- **File Upload**: Laravel Storage
- **Queue**: Laravel Queue
- **Notifications**: Laravel Notifications
- **Mail**: Laravel Mail + Mailgun/SendGrid

### 5.4 DevOps
- **Version Control**: Git + GitHub
- **Task Runner**: Laravel Mix / Vite
- **Package Manager**: Composer (PHP) + NPM (JS)
- **Testing**: PHPUnit + Laravel Dusk
- **CI/CD**: GitHub Actions
- **Hosting**: AWS / DigitalOcean / Shared Hosting

---

## 🚀 6. مراحل التطوير (Development Phases)

### المرحلة 1: الأساس (تم إنجازها ✅)
- ✅ نظام المصادقة والأمان
- ✅ أنظمة الأدوار الخمسة
- ✅ المحتوى التعليمي الهرمي
- ✅ نظام Gamification الأساسي
- ✅ لوحة التحكم للـ Super Admin

### المرحلة 2: التحسينات (قيد العمل ⚠️)
- ✅ تحسين الأداء (Indexes, Caching)
- ✅ تحسين الأمان (2FA, Rate Limiting)
- ⚠️ إدارة الأنشطة للمعلمين (مطلوب)
- ⚠️ نظام الفرق (Teams)
- ⚠️ نظام المتجر (Shop)

### المرحلة 3: التوسع (مستقبلاً 🔮)
- ❌ تطبيق Mobile (React Native / Flutter)
- ❌ نظام الإشعارات الفورية (Push Notifications)
- ❌ نظام Chat للفرق
- ❌ نظام Video Conferencing
- ❌ AI Recommendations
- ❌ Advanced Analytics (ML)

---

## 📋 7. معايير النجاح (Success Criteria)

### معايير التقنية
- ✅ جميع الوظائف الأساسية تعمل بدون أخطاء
- ✅ زمن استجابة API أقل من 500ms
- ✅ نسبة أخطاء أقل من 1%
- ✅ Test Coverage أكثر من 70%
- ✅ جميع الصفحات Responsive

### معايير الأمان
- ✅ اجتياز اختبار OWASP Top 10
- ✅ جميع المدخلات منظفة ومعقمة
- ✅ جميع كلمات المرور محمية (Hashed)
- ✅ جميع الجلسات آمنة
- ✅ Activity Log شامل

### معايير الأداء
- ✅ تحميل الصفحة أقل من 2 ثانية
- ✅ دعم 1000 مستخدم متزامن
- ✅ Database Queries محسّنة (لا N+1)
- ✅ استخدام Caching فعال

### معايير الاستخدام
- ✅ واجهة عربية كاملة وواضحة
- ✅ تجربة مستخدم سلسة
- ✅ رسائل خطأ مفهومة
- ✅ Onboarding واضح للمستخدمين الجدد

---

## 📝 8. الملاحظات والتوصيات

### النواقص الحالية
1. **نظام إدارة الأنشطة للمعلمين**: 
   - المعلم لا يستطيع إنشاء أنشطة جديدة
   - يحتاج إلى واجهة CRUD كاملة
   
2. **نظام الفرق (Teams)**:
   - الجداول موجودة في قاعدة البيانات
   - لكن لا توجد واجهة أو Controllers
   
3. **نظام المتجر (Shop)**:
   - الجدول موجود
   - لكن لا توجد واجهة للشراء

4. **التواصل بين المعلمين وأولياء الأمور**:
   - الجدول موجود
   - لكن لا توجد واجهة Chat/Messages

### التوصيات
1. **الأولوية العالية**:
   - إكمال نظام إدارة الأنشطة للمعلمين
   - تفعيل نظام الفرق (Teams)
   - تحسين نظام التقارير

2. **الأولوية المتوسطة**:
   - تطوير نظام المتجر
   - إضافة نظام Chat
   - تطوير تطبيق Mobile

3. **تحسينات الأداء**:
   - نقل قاعدة البيانات من SQLite إلى MySQL
   - إضافة Redis للـ Caching
   - إعداد Queue Workers

4. **الأمان**:
   - مراجعة شاملة للأمان (Security Audit)
   - إضافة WAF (Web Application Firewall)
   - إعداد SSL Certificate

---

## ✍️ المراجع

### الوثائق الداخلية
- [ADMIN_SYSTEM_COMPLETE.md](ADMIN_SYSTEM_COMPLETE.md)
- [PROJECT_ANALYSIS.md](PROJECT_ANALYSIS.md)
- [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- [REPORTS_DOCUMENTATION.md](REPORTS_DOCUMENTATION.md)

### الوثائق الخارجية
- [Laravel Documentation](https://laravel.com/docs)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3)

---

## 📞 جهات الاتصال

| الدور | الاسم | البريد الإلكتروني |
|------|------|-------------------|
| **مدير المشروع** | - | ibrahemelnawsaty@gmail.com |
| **مطور رئيسي** | - | - |
| **مصمم UX/UI** | - | - |

---

**تاريخ آخر تحديث:** ديسمبر 23, 2025  
**رقم الإصدار:** 1.0.0  
**الحالة:** معتمد للتنفيذ ✅
