# دليل التشغيل والصيانة — منصّة «أثيل مكة»

هذا الدليل يشرح العمليّات اليوميّة والدوريّة لإبقاء منصّة **أثيل مكة** (`atheel-makkah.com`) صحّيّةً بعد أن تصبح على الهواء. هو مكمِّل لدليل النشر ولا يُكرّره: لأوّل تنصيب للخادم أو لخطوات إعادة النشر التفصيليّة راجِع **`docs/DEPLOYMENT_RUNBOOK.md`**. أمّا هنا فنركّز على **المراقبة، السجلّات، المهامّ المجدولة، عامل الطابور، الشهادة الأمنيّة، التحديثات الروتينيّة، وضع الصيانة، وحلّ الأعطال الشائعة**.

> **الخادم:** Contabo VPS · **IP:** `84.247.135.69` · **مسار المشروع:** `/var/www/atheel` · **جذر الويب:** `public/` · **المنصّة:** Laravel 12 (PHP 8.2) على Nginx + MySQL.
>
> كلّ الأوامر أدناه تُنفَّذ عبر SSH من داخل مجلّد المشروع ما لم يُذكر غير ذلك:
> ```bash
> cd /var/www/atheel
> ```

---

## 1. لمحة سريعة — الفحص اليوميّ

نظرة صباحيّة سريعة (دقيقتان) تكشف معظم المشكلات مبكّراً:

| الفحص | الأمر | المتوقّع |
|---|---|---|
| الموقع يفتح | افتح `https://atheel-makkah.com` | يفتح بقفل HTTPS بلا خطأ |
| خدمة الويب | `systemctl status nginx php8.2-fpm` | `active (running)` |
| قاعدة البيانات | `systemctl status mysql` | `active (running)` |
| عامل الطابور | `systemctl status atheel-queue` | `active (running)` |
| المجدوِل يعمل | `crontab -l` ثمّ راجِع سجلّات المهامّ | سطر `schedule:run` موجود |
| أخطاء اليوم | راجِع `storage/logs/laravel.log` | خالٍ من الأخطاء الحرجة |
| مساحة القرص | `df -h /` | استخدام أقلّ من ~80% |

---

## 2. مراقبة الخدمات (Services)

المنصّة تعتمد على أربع خدمات نظاميّة يجب أن تكون جميعها فعّالة:

```bash
systemctl status nginx          # خادم الويب
systemctl status php8.2-fpm     # مفسّر PHP
systemctl status mysql          # قاعدة البيانات
systemctl status atheel-queue   # عامل الطابور (systemd — أُنشئ في runbook §13)
```

**إعادة تشغيل خدمة** عند تعثّرها:
```bash
systemctl restart nginx
systemctl restart php8.2-fpm
systemctl restart atheel-queue
```

> نصيحة: بعد أيّ تعديل على ملفّ إعداد Nginx نفّذ `nginx -t` للتحقّق من الصياغة قبل `systemctl reload nginx`.

### مؤشّرات الموارد
```bash
df -h /                 # مساحة القرص (النسخ الاحتياطيّ والسجلّات والوسائط تستهلكها)
free -m                 # الذاكرة
uptime                  # الحِمل ومدّة التشغيل
du -sh storage/logs storage/app/backup-temp   # حجم السجلّات والنسخ المؤقّتة
```

---

## 3. السجلّات (`storage/logs`)

قناة السجلّ في الإنتاج هي `stack` بنمط **يوميّ** (`LOG_STACK=daily`) ومستوى **الأخطاء فقط** (`LOG_LEVEL=error`)، أي أنّ ملفّاً جديداً يُنشأ كلّ يوم ولا تُسجَّل رسائل التنقيح.

| الملفّ | المحتوى |
|---|---|
| `storage/logs/laravel-YYYY-MM-DD.log` | سجلّ التطبيق اليوميّ الرئيس (أخطاء PHP/Laravel) |
| `storage/logs/laravel.log` | السجلّ العامّ (قد يظهر حسب الإعداد) |
| `storage/logs/backups.log` | مخرجات النسخ الاحتياطيّ اليوميّ |
| `storage/logs/backup-cleanup.log` | مخرجات تنظيف النسخ القديمة |
| `storage/logs/backup-monitor.log` | مخرجات مراقبة صحّة النسخ |
| `storage/logs/homework-checks.log` | فحص مواعيد الواجبات |
| `storage/logs/schools-stats.log` | تحديث إحصائيات المدارس |
| `storage/logs/notifications-cleanup.log` | تنظيف الإشعارات القديمة |

**متابعة حيّة للأخطاء** (مفيد أثناء تشخيص عطل):
```bash
tail -f storage/logs/laravel-$(date +%F).log
```

**آخر الأخطاء الحرجة فقط:**
```bash
grep -iE "ERROR|CRITICAL|EXCEPTION" storage/logs/laravel-$(date +%F).log | tail -n 40
```

> السجلّات اليوميّة تتراكم؛ Laravel يحتفظ افتراضيّاً بـ14 يوماً من ملفّات `daily`. تابِع حجم المجلّد بـ`du -sh storage/logs` واحذف اليدويّ القديم إن لزم.

---

## 4. المجدوِل (Cron) وما يشغّله

المجدوِل هو العمود الفقريّ للمهامّ الدوريّة. سطرٌ واحدٌ في `crontab` يشغّل مجدوِل Laravel كلّ دقيقة، وLaravel بدوره يقرّر أيّ مهمّة يحين وقتها:

```bash
crontab -l    # يجب أن يظهر السطر التالي (أُضيف في runbook §12):
# * * * * * cd /var/www/atheel && php artisan schedule:run >> /dev/null 2>&1
```

> **بدون هذا السطر تتوقّف كلّ المهامّ أدناه صامتةً** — لا نسخ احتياطيّ، لا تنظيف، لا تحديث إحصاءات.

### المهامّ المجدولة (المصدر: `routes/console.php`)

| المهمّة | الأمر | التوقيت | الغرض | سجلّها |
|---|---|---|---|---|
| فحص مواعيد الواجبات | `homework:check-due-dates` | كلّ 4 ساعات | تنبيهات اقتراب مواعيد التسليم | `homework-checks.log` |
| النسخ الاحتياطيّ | `backup:run` | يوميّاً 02:00 | نسخة كاملة (قاعدة بيانات + ملفّات) | `backups.log` |
| تنظيف النسخ القديمة | `backup:clean` | الأحد 03:00 | حذف النسخ وفق سياسة الاستبقاء | `backup-cleanup.log` |
| مراقبة صحّة النسخ | `backup:monitor` | يوميّاً 09:00 | تنبيه عند نسخة قديمة/غائبة | `backup-monitor.log` |
| تحديث إحصائيات المدارس | `schools:refresh-stats` | كلّ ساعة | تحديث جداول الإحصاءات المجمّعة | `schools-stats.log` |
| تنظيف الإشعارات | `notifications:cleanup --days=90` | الأحد 04:00 | حذف الإشعارات الأقدم من 90 يوماً | `notifications-cleanup.log` |

جميع هذه المهامّ تعمل بخاصيّة `withoutOverlapping` (لا تتداخل نسختان) و`runInBackground` (لا تعطّل بعضها).

**التحقّق من عمل المجدوِل يدويّاً:**
```bash
php artisan schedule:list        # يعرض المهامّ ومواعيدها القادمة
php artisan schedule:run         # يشغّل المستحقّ الآن (للاختبار)
```

> **النسخ الاحتياطيّ والاستعادة** لها تفاصيلها الخاصّة (سياسة الاستبقاء، الاستعادة، off-site). سياسة الاستبقاء الحاليّة في `config/backup.php`: الاحتفاظ الكامل 7 أيّام، ثمّ يوميّ 16 يوماً، أسبوعيّ 8 أسابيع، شهريّ 4 أشهر، سنويّ سنتان، بسقفٍ 5000 ميغابايت. راجِع دليل النسخ الاحتياطيّ المستقلّ إن وُجد.

---

## 5. عامل الطابور (Queue Worker)

اتّصال الطابور في الإنتاج هو **`database`** (`QUEUE_CONNECTION=database` في `.env`)، أي أنّ المهامّ المؤجّلة — وأبرزها **إرسال البريد** — تُخزَّن في جدول قاعدة البيانات وينفّذها عاملٌ دائم. أُعِدّ هذا العامل كخدمة systemd باسم **`atheel-queue`** (راجِع runbook §13).

### المراقبة والتحكّم
```bash
systemctl status atheel-queue          # الحالة الحاليّة
journalctl -u atheel-queue -n 100      # آخر 100 سطر من سجلّ العامل
journalctl -u atheel-queue -f          # متابعة حيّة
systemctl restart atheel-queue         # إعادة تشغيل (إلزاميّة بعد كلّ نشر/تحديث كود)
```

> **مهمّ:** كود الطابور يُحمَّل في ذاكرة العامل عند بدئه، فلا يرى تغييرات الكود حتّى تعيد تشغيله. **أعِد تشغيل `atheel-queue` بعد كلّ `git pull`.**

### فحص المهامّ الفاشلة
```bash
php artisan queue:failed         # قائمة المهامّ الفاشلة
php artisan queue:retry all      # إعادة محاولة كلّ الفاشلة
php artisan queue:flush          # حذف الفاشلة نهائيّاً (بعد التأكّد)
```

> إن بدا أنّ **البريد لا يصل**، فأوّل ما يُفحَص هو أنّ عامل الطابور يعمل — رسالة مُدرَجة في الطابور بلا عامل تبقى معلّقة إلى الأبد. (السبب الثاني الشائع هو كلمة مرور SMTP؛ راجِع §9.)

---

## 6. تجديد شهادة SSL

الشهادة صادرة من **Let's Encrypt** عبر **Certbot** (أُعِدّت في runbook §11)، وتُجدَّد **تلقائيّاً** عبر مؤقّت systemd الخاصّ بـcertbot. مهمّتك هي التحقّق الدوريّ لا التجديد اليدويّ.

```bash
certbot certificates            # يعرض الشهادات وتواريخ انتهائها
certbot renew --dry-run         # اختبار التجديد دون تنفيذه فعليّاً (يجب أن ينجح)
systemctl status certbot.timer  # مؤقّت التجديد التلقائيّ
```

**تجديد يدويّ فوريّ** (نادراً ما يلزم):
```bash
certbot renew
systemctl reload nginx          # لتحميل الشهادة الجديدة
```

> شهادات Let's Encrypt صالحة 90 يوماً ويحاول certbot تجديدها قبل 30 يوماً من الانتهاء. افحص `certbot renew --dry-run` مرّةً شهريّاً للاطمئنان.

---

## 7. إجراء التحديثات (إعادة النشر)

للتحديث الروتينيّ (سحب أحدث كود وتطبيقه) اتّبع التسلسل التالي. هذه نسخة تشغيليّة مختصرة؛ التفاصيل الكاملة في **`DEPLOYMENT_RUNBOOK.md` §16**.

```bash
cd /var/www/atheel

# 1) (موصى به) فعّل وضع الصيانة أثناء التحديث — انظر §8
php artisan down --render="errors::503"

# 2) اسحب الكود
git pull origin master

# 3) حدّث التبعيّات وابنِ الأصول
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 4) طبّق هجرات قاعدة البيانات
php artisan migrate --force

# 5) أعِد بناء الكاشات (config/route/view/event)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6) أعِد تشغيل عامل الطابور (يحمّل الكود الجديد)
systemctl restart atheel-queue

# 7) أنهِ وضع الصيانة
php artisan up
```

> **تنبيه الهجرات:** كثير من ميزات المنصّة أُطلقت بأعمدة/جداول جديدة تتطلّب `migrate` على الإنتاج (مثل جداول محرّر الصفحات `pb_*`، أعمدة الأنشطة والتمييز، توسعة أنواع بعض الحقول). لا تتخطَّ الخطوة 4.
>
> **تنبيه الكاش:** بعد أيّ تعديل يدويّ على `.env` نفّذ دائماً `php artisan config:clear && php artisan config:cache` وإلّا فلن يُقرأ التغيير.

---

## 8. وضع الصيانة (Maintenance Mode)

سائق الصيانة في الإنتاج هو **`file`** (`APP_MAINTENANCE_DRIVER=file`)، ما يعني أنّ تفعيله لا يعتمد على قاعدة البيانات ويعمل حتّى لو تعطّلت.

```bash
php artisan down        # تفعيل: الزوّار يرون صفحة 503 «تحت الصيانة»
php artisan up          # إلغاء: عودة الموقع للعمل
```

**السماح لك بالوصول أثناء الصيانة** عبر رمز تجاوز سرّيّ:
```bash
php artisan down --secret="atheel-maintenance-2026"
# ثمّ افتح مرّةً:  https://atheel-makkah.com/atheel-maintenance-2026
# فتُمنَح كوكيّ تتيح لك التصفّح الطبيعيّ بينما يرى الزوّار صفحة الصيانة.
```

**خيارات مفيدة:**
```bash
php artisan down --retry=60                 # يضبط ترويسة Retry-After بـ60 ثانية
php artisan down --render="errors::503"     # عرض صفحة 503 مصمّمة بدل الافتراضيّة
```

> استخدم وضع الصيانة أثناء الهجرات الكبيرة أو التحديثات لتجنّب رؤية المستخدمين لحالة وسطيّة. إن بقي الموقع عالقاً في وضع الصيانة بعد خطأ، نفّذ `php artisan up` يدويّاً، أو احذف ملفّ `storage/framework/down` إن تعذّر ذلك.

---

## 9. مؤشّرات الصحّة وحلّ المشكلات الشائعة

### فحوص صحّة سريعة
```bash
php artisan about               # ملخّص البيئة والإصدارات والكاشات
php artisan migrate:status      # حالة الهجرات (هل يوجد ما لم يُطبَّق؟)
php artisan queue:failed        # مهامّ فاشلة؟
php artisan mail:test you@example.com   # اختبار وصول البريد
```

### جدول الأعطال الشائعة

| العرض | الفحص / الحلّ |
|---|---|
| خطأ 500 (شاشة بيضاء) | راجِع `storage/logs/laravel-$(date +%F).log`؛ تحقّق من صلاحيات `storage` و`bootstrap/cache` (يجب `775` ومالكها `www-data`)؛ تأكّد أنّ `APP_KEY` مضبوط |
| «419 Page Expired» | تأكّد `SESSION_DOMAIN=.atheel-makkah.com` و`APP_URL` يبدأ بـ`https`؛ الوقت على الخادم صحيح |
| البريد لا يصل | تأكّد أنّ `atheel-queue` يعمل (§5)؛ كلمة `MAIL_PASSWORD` الصحيحة؛ `php artisan mail:test`؛ افحص السبام؛ SPF/DKIM |
| تغيير `.env` لا يظهر | `php artisan config:clear && php artisan config:cache` |
| تعديل كود لا يظهر | أعِد بناء الكاشات (§7 خطوة 5) وأعِد تشغيل `php8.2-fpm` و`atheel-queue` |
| لا توجد نسخة احتياطيّة جديدة | تأكّد من سطر cron (§4)؛ راجِع `storage/logs/backups.log`؛ افحص مساحة القرص |
| الوسائط/الفيديو لا يُرفَع | حدود الرفع: `upload_max_filesize`/`post_max_size` في php.ini و`client_max_body_size` في Nginx (كلّها 500M) |
| بطء عامّ / أخطاء متقطّعة | `df -h` (امتلاء القرص)، `free -m` (الذاكرة)، `systemctl status mysql`، حجم `storage/logs` |
| الموقع عالق «تحت الصيانة» | `php artisan up`؛ إن فشل احذف `storage/framework/down` |

### الأوامر المخصّصة المتاحة (للصيانة اليدويّة)
إضافةً للمهامّ المجدولة، تتوفّر أوامر يمكن تشغيلها يدويّاً عند الحاجة:

- `php artisan backup:run` — نسخة احتياطيّة فوريّة.
- `php artisan schools:refresh-stats` — إعادة حساب إحصائيات المدارس فوراً.
- `php artisan notifications:cleanup --days=90` — تنظيف إشعارات فوريّ.
- `php artisan homework:check-due-dates` — فحص مواعيد فوريّ.
- `php artisan images:optimize` — ضغط/تحسين الصور المرفوعة.
- `php artisan mail:test <بريد>` — اختبار إعداد البريد.

---

## 10. جدول الصيانة الموصى به

| الدوريّة | المهمّة |
|---|---|
| يوميّاً | فحص §1 السريع؛ نظرة على أخطاء اليوم في السجلّ |
| أسبوعيّاً | التحقّق من نجاح النسخ الاحتياطيّ (`backups.log` + بريد التنبيه)؛ مراجعة `queue:failed`؛ مساحة القرص |
| شهريّاً | `certbot renew --dry-run`؛ مراجعة حجم السجلّات والنسخ؛ `apt update && apt upgrade` (بحذر، مع نسخة احتياطيّة مسبقة) |
| عند كلّ تحديث كود | اتّبع تسلسل §7 كاملاً، وأعِد تشغيل عامل الطابور |

---

> **مراجع ذات صلة:** لخطوات التنصيب الأوّل وإعادة النشر التفصيليّة راجِع `docs/DEPLOYMENT_RUNBOOK.md`؛ وللتوثيق الشامل للمنصّة راجِع `docs/WAHY_MASTER_BLUEPRINT.md`.
