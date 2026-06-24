# Sprint 1 — Deploy Notes

## 📦 ملخص التغييرات

### ⚡ تحسين أداء (Performance)
- **LeaderboardController**: إعادة كتابة كاملة. صفر N+1، ORDER BY على SQL، Cache TTL 15 دقيقة لكل لوحة + ranks. **التأثير:** من ~150 query إلى 1-3 queries لكل صفحة leaderboard.
- **ParentDashboardController**: Cache 30 دقيقة على حسابات الترتيبات الأربعة (class/school/city/country) لكل ابن. **التأثير:** من ~12 query إلى 1 لكل لوحة ولي أمر بعد warm-up.

### 🛡️ أمان (Security)
- **AuthServiceProvider** جديد + 4 Policies: `Activity`, `ActivitySubmission`, `Lesson`, `Message`.
- **SecurityHeaders** middleware: HSTS (Production+HTTPS فقط)، Permissions-Policy، CSP بـ **Report-Only** (لا يكسر الواجهة، فقط يسجل في Console). راقب console المتصفح أسبوعاً قبل التحويل لـ Enforce.

### 🗄️ قاعدة البيانات (Migrations جديدة)
- `2026_05_11_153711_add_missing_indexes_v3.php` — فهارس morphs على `notifications`, `activity_log`، وفهارس FK على `activities.created_by/classroom_id` و `lessons.concept_id`.
- `2026_05_11_153712_add_school_id_fk_to_users.php` — إضافة FK على `users.school_id` مع تنظيف اليتيمين (يضع `school_id = NULL` بدلاً من حذف المستخدمين).

### 🤖 Console
- Command جديد: `php artisan schools:refresh-stats` يعيد بناء `school_statistics_cache`.
- مجدوَل ساعةً عبر `routes/console.php`.

---

## 🚨 خطوات النشر (يجب اتباعها بالترتيب)

### 1) قبل أي شيء — نسخة احتياطية
```bash
php artisan backup:run
```

### 2) رفع الكود الجديد للسيرفر (Git pull / FTP / إلخ)

### 3) تشغيل المهجرات
```bash
php artisan migrate --force
```
> ⚠️ مهجرة الـ FK ستحدّث المستخدمين اليتيمين تلقائياً (`school_id → NULL`) قبل إضافة FK. ستظهر رسالة في log إذا وُجدوا.

### 4) مسح الـ caches القديمة
```bash
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
```

### 5) إعادة البناء
```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache
composer dump-autoload --optimize
```

### 6) تشغيل الـ scheduler (إن لم يكن مفعّلاً)
أضف في cron:
```
* * * * * cd /home/USER/public_html && php artisan schedule:run >> /dev/null 2>&1
```
هذا يضمن أن `schools:refresh-stats` تشتغل كل ساعة.

### 7) ملء الـ cache لأول مرة (اختياري لكن موصى به)
```bash
php artisan schools:refresh-stats
```

---

## ⚠️ مهام يدوية (لم أنفذها لأسباب أمان)

### A. تحويل Mail إلى Queue (لم يُنفّذ — يحتاج إعداد قبل التفعيل)
**المشكلة:** `QUEUE_CONNECTION=database` لكن لا يوجد `queue:work` daemon. لو فعّلت `ShouldQueue` الآن، الإيميلات ستعلق في DB ولن تُرسل.

**الخطوات لتفعيلها لاحقاً:**
1. في Hostinger أو cPanel: شغّل dedicated worker:
   ```bash
   php artisan queue:work --queue=default --tries=3 --timeout=120 --sleep=3
   ```
   الأفضل عبر Supervisor (إن متاح) أو cron job يعيد التشغيل كل دقيقة.

2. ثم في كل ملف داخل `app/Mail/` (ما عدا `TwoFactorCodeMail.php` — يجب أن يبقى متزامن):
   ```php
   // قبل:
   class RegistrationApprovedMail extends Mailable
   // بعد:
   class RegistrationApprovedMail extends Mailable implements ShouldQueue
   ```
   (الـ `use Queueable` و `use ShouldQueue` موجودين أصلاً في الـ imports.)

### B. تدوير كلمات السر (Sprint 0)
لو لم تتم بعد:
- DB password جديد على Hostinger.
- SMTP password مختلف عن DB.
- حدّث `.env` على السيرفر مباشرة.

### C. تحويل Cache/Queue/Session إلى Redis (موصى به)
المشروع يستخدم `database` للثلاثة. هذا يضرب DB بـ 3 ضغوط متزامنة. لو Hostinger يدعم Redis:
```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### D. CSP Report-Only → Enforce
بعد أسبوع من المراقبة:
- في `SecurityHeaders.php` غيّر `Content-Security-Policy-Report-Only` إلى `Content-Security-Policy`.
- إذا اكتشفت سكربتات inline تكسر، أضف nonce أو حدّث الـ CSP لتسمح بـ المصدر.

---

## 🧪 اختبارات إجبارية بعد النشر

| اختبار | المتوقّع |
|---|---|
| Login (طالب/معلم/أدمن) | يعمل |
| `/leaderboard` للجميع | يعمل، أسرع بكثير من قبل |
| `/parent/dashboard` لولي أمر | يعمل، أسرع بعد cache warm |
| تقديم نشاط كطالب | يعمل، score يُحفظ |
| اعتماد نشاط كأدمن | يعمل |
| شراء من المتجر | يعمل، deductCoins يرفض النقص |
| فتح console المتصفح وتفقّد CSP violations | لا يجب أن يكسر شيئاً (Report-Only فقط) |
| `php artisan schools:refresh-stats` | يكتمل بدون أخطاء، يملأ الجدول |
| `php artisan migrate:status` | كل المهجرات Ran |

---

## 📊 KPIs قبل/بعد (متوقع)

| الصفحة | قبل (queries) | بعد (queries) | تحسن |
|---|---|---|---|
| `/leaderboard` (10 leaders × 4 فئات) | ~200 | ~10 | **20×** |
| `/leaderboard/schools` (50 مدرسة) | ~150 | 1 | **150×** |
| `/parent/dashboard` (3 أبناء) | ~30 | ~5 (warm) | **6×** |
| `/admin/dashboard` | ~50 | ~50 (لم يُحسّن في Sprint 1) | — |

---

أي مشكلة بعد النشر — راجع `storage/logs/laravel.log` و `storage/logs/schools-stats.log`.
