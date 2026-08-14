# نظام البريد المتكامل لمنصّة «أثيل مكة» — خطّة تنفيذيّة شاملة

> **وثيقة اعتماد وتنفيذ** · مبنيّة على قراءة الكود الفعليّ (Laravel 12، عربيّة RTL، `atheel-makkah.com`) لا على افتراضات · الأدوار: student · teacher · school_admin · super_admin(=admin) · parent · technical_support

---

## 1. الملخّص التنفيذيّ

### ماذا نبني
نظام بريد إلكترونيّ متكامل من خمس طبقات متراكبة، ينقل المنصّة من «بريد متفرّق يُرسَل تزامنيًّا وبعضه لا يصل أصلًا» إلى **منظومة إرسال موثوقة وقابلة للقياس والحوكمة**:

1. **بنية إرسال وتسليم موثوقة** — مزوّد بريد معاملاتيّ مخصّص، عامل طابور دائم، سجلّات DNS للتسليم، وأداة تشخيص شاملة.
2. **كتالوج أحداث بريديّة لكل دور** — تحويل ~35 حدثًا مغطّى داخليًّا فقط إلى بريد مخصّص محكوم بأعلام.
3. **نظام تتبّع ومراقبة** — التقاط كل بريد صادر تلقائيًّا في `email_logs` مع لوحة أدمن وإحصاءات وإعادة إرسال.
4. **مُرسِل جماعيّ/مخصّص للأدمن** — حملات بريديّة مُجزّأة عبر الطابور مع جمهور مستهدف ومتغيّرات ديناميكيّة.
5. **قوالب موحّدة وحوكمة** — قالب آمن لعملاء البريد، أعلام تفعيل لكل نوع، تفضيلات مستخدم، وإلغاء اشتراك قانونيّ.

### لماذا (المشكلة الجوهريّة)
البريد اليوم **معطّل فعليًّا أو غير موثوق** لأربعة أسباب متراكبة، أيّ واحد منها كافٍ لإسقاط الإرسال بصمت:

| # | الجذر | الأثر |
|---|---|---|
| 1 | **لا عامل طابور (`queue:work`) يعمل على الإنتاج** | 3 مستمعين `ShouldQueue` (ترحيب/تصحيح/شارة) يُصفَّون ولا يصلون أبدًا — نفس جذر عطل 2FA (commit `9d8c9a8`) |
| 2 | **جدولا `failed_jobs` و`job_batches` مفقودان** من المستودع (فيه `create_jobs_table` فقط) بينما `config/queue.php` يوجّه الفشل لـ`failed_jobs` | تسجيل أيّ فشل يرمي استثناءً؛ الحملات/الدفعات تتعطّل |
| 3 | **SMTP غير مضبوط** — `.env.production.example` فيه placeholders، و`MAIL_MAILER` الافتراضيّ `log` | البريد يُكتَب في اللوگ ولا يُرسَل، أو يفشل الاتصال |
| 4 | **الإرسال من IP الخادم مباشرة** بلا SPF/DKIM/DMARC مضبوطة | الرسائل تذهب للسبام أو تُرفَض |

هذه الأربعة هي **الفرق بين «بريد يصل» و«بريد يُكتَب في اللوگ بصمت»**، ويجب حسمها في المرحلة الأولى قبل بناء أيّ ميزة جديدة.

> **قرار معماريّ يجب حسمه أوّلًا:** أين يعمل الإنتاج فعلًا؟ الالتزام `9d8c9a8` يذكر **Hostinger مشتركًا** (لا يسمح بـ`systemd`)، بينما سياق التشغيل والـrunbook يذكران **Contabo VPS** (يسمح). القرار يغيّر حلّ الطابور جذريًّا (systemd مقابل cron). الخطّة أدناه تغطّي السيناريوهين وتوصي بحسب كلٍّ منهما.

---

## 2. تقييم الوضع الحاليّ

### 2.1 ما هو موجود فعلاً ويعمل

**عشرة Mailables** في `app/Mail/*`: `TwoFactorCodeMail`, `ResetPasswordMail`, `WelcomeStudentMail`, `ActivityGradedMail`, `BadgeEarnedMail`, `NewRegistrationNotificationMail`, `RegistrationSubmittedMail`, `RegistrationApprovedMail`, `RegistrationRejectedMail`, `RegistrationPendingMail` + قالبا `contact/contact-confirmation`.

- **مغطّى بريديًّا ويعمل (تزامنيّ):** المصادقة (2FA بعد إصلاح `9d8c9a8`، إعادة كلمة المرور)، مسار التسجيل الكامل (submitted/approved/rejected/pending/new-registration)، نموذج التواصل + تأكيده. **أي: الحسابات والتسجيل مغطّاة.**
- **بنية جاهزة للبناء عليها:** جدول `settings` (key/value/type/description/user_id، كاش 86400ث، يدعم `boolean`) كآلية أعلام بلا هجرة؛ المحرّر الموحّد `public/js/rich-editor.js` (`data-rich-editor`)؛ سانِتايزر `safe_html` + `safe_mail_subject` (يمنع حقن CRLF)؛ بوّابة `access-admin` (سوبر أدمن فقط)؛ المجدول في `routes/console.php` (homework/backups/cleanup — نقاط ربط جاهزة)؛ `config/mail.php` يعرّف مسبقًا mailers لـ `smtp`/`ses`/`postmark`.

### 2.2 الفجوات

| الفجوة | التفصيل |
|---|---|
| **بريد مُصفّ لا يصل** | 3 Mailables (ترحيب/تصحيح/شارة) مدفونة في طابور `database` بلا عامل |
| **~35 حدثًا بلا بريد** | كلّها إشعارات داخليّة فقط عبر `NotificationService` — أبرزها: تذكير الواجب المستحقّ، تسليم الطالب للمعلّم، اعتماد/رفض النشاط (مرحلتان)، دورة تذاكر الدعم كاملة، موافقة الوليّ الاقتصاديّة، ملخّصات دوريّة |
| **لا مُرسِل جماعيّ بريديّ** | `BulkMessageController` يكتب صفوفًا داخليّة في `bulk_message_recipients` فقط — **لا يُرسل بريدًا إطلاقًا** |
| **لا تتبّع** | لا جدول `email_logs`، لا التقاط أحداث SMTP، لا رؤية «أُرسِل/فشل/فُتِح» |
| **لا تفضيلات/opt-out** | لا جدول تفضيلات، لا عمود إلغاء اشتراك في `users` — لا يمكن احترام opt-out اليوم (خطر قانونيّ) |
| **قوالب هشّة** | القالب الأمّ `master.blade.php` يستعمل خصائص تنهار في عملاء البريد (`backdrop-filter`, `background-clip:text`, `@keyframes`, تدرّج على `<body>`, خطّ Google خارجيّ) |

### 2.3 المخاطر الحرجة

| # | الخطر (من الكود) | الخطورة |
|---|---|---|
| 1 | `config/mail.php:17` الافتراضيّ `env('MAIL_MAILER','log')` — إن غاب المتغيّر، **كل البريد يُكتَب باللوگ بصمت** | حرِج |
| 2 | 3 مستمعين `implements ShouldQueue` يُصفَّون في `jobs` ويبقون عالقين أبدًا بلا عامل (تأكيد `9d8c9a8`: **لا عامل يعمل على الإنتاج**) | حرِج |
| 3 | `create_jobs_table` فقط — **`failed_jobs` و`job_batches` مفقودان** بينما `config/queue.php:124` يوجّه الفشل لـ`failed_jobs` غير الموجود | حرِج |
| 4 | `DEPLOYMENT_RUNBOOK.md §13` يستهدف **Contabo** بينما `9d8c9a8` يقول الإنتاج **Hostinger مشترك** — التوثيق يصف بيئة مختلفة عن الواقع | عالٍ |
| 5 | `.env.production.example`: كلمة SMTP placeholder، وسابقًا `MAIL_PASSWORD==DB_PASSWORD` | عالٍ |
| 6 | الإرسال من IP الخادم مباشرة (IPات مُدرَجة مسبقًا في قوائم سمعة منخفضة؛ منفذ 25 محجوب غالبًا؛ لا feedback loop) | عالٍ |
| 7 | تسريب بيئة تطوير: `two-factor-code.blade.php` يحوي `http://127.0.0.2:8000` و`mailto:info@sa-salem.com` (نطاق خاطئ)، وعنوانه «منصة قيمّ» بدل «أثيل مكة» | متوسّط |

---

## 3. القسم الأوّل — البنية والتسليم والاختبار

يعالج هذا القسم البُعد الأساسيّ الذي يسبق كل ميزة: **لماذا لا يصل البريد أصلًا**، وكيف نجعل الإرسال موثوقًا وقابلًا للقياس.

### 3.1 السائق ومزوّد الإرسال الموصى به

**الحالة الحاليّة:** السائق `smtp` مُوجَّه لصندوق بريد المزوّد (مثل `smtp.hostinger.com`) — إرسال من صندوق الخادم، لا relay معاملاتيّ مخصّص. يعمل للحجم المنخفض لكنه سيّئ التسليم عند التوسّع.

#### متغيّرات `.env` بقيَم واقعيّة

```dotenv
# ---- الخيار A: Brevo (SMTP relay) — إطلاق سريع، تتبّع مدمج ----
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls          # STARTTLS
MAIL_SCHEME=null
MAIL_USERNAME=8a1b2c001@smtp-brevo.com   # مُعرّف SMTP من لوحة Brevo (ليس بريدك)
MAIL_PASSWORD=xsmtpsib-xxxxxxxx          # SMTP key من Brevo
MAIL_FROM_ADDRESS=no-reply@atheel-makkah.com
MAIL_FROM_NAME="أثيل مكة"

# ---- الخيار B: Amazon SES عبر API (الأفضل تسليمًا/سعرًا للحجم) ----
# composer require aws/aws-sdk-php
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=no-reply@atheel-makkah.com
MAIL_FROM_NAME="أثيل مكة"
AWS_ACCESS_KEY_ID=AKIA...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=me-south-1   # البحرين — أقرب إقليم للسعوديّة (سيادة بيانات + كمون أقل)
```

> عند `MAIL_PORT=465` يجب `MAIL_ENCRYPTION=ssl` (TLS ضمنيّ)، وعند `587` يجب `tls` (STARTTLS). خلط الاثنين سبب شائع لـ`Connection could not be established`. الـexample الحاليّ (465/ssl) متّسق — لا تُغيّر أحدهما دون الآخر.

#### مقارنة المزوّدات (لمنصّة سعوديّة ناشئة)

| المزوّد | السعر (تقريبيّ) | سهولة الإعداد | التتبّع | سمعة التسليم | ملاحظة للسياق السعوديّ |
|---|---|---|---|---|---|
| **Amazon SES** | ~$0.10/1000 رسالة | متوسّطة (خروج من sandbox + DKIM) | Webhooks عبر SNS — يتطلّب بناء | ممتاز (IP معزول) | **إقليم me-south-1** = سيادة بيانات وكمون أدنى؛ **الأفضل للتوسّع** |
| **Postmark** | ~$15/10k | سهلة جدًّا | **الأفضل**: لوحة + Webhooks كاملة | ممتاز (معاملاتيّ فقط) | الأغلى؛ يفصل معاملاتيّ/تسويقيّ (streams) |
| **Brevo** | مجانيّ 300/يوم ثمّ رخيص | **الأسهل** | لوحة + Webhooks + فتح مدمج | جيّد جدًّا | **الأفضل للانطلاق السريع** بلا تكلفة |
| **Zoho Mail** | ~$1/صندوق/شهر | سهلة | محدود | جيّد | مناسب لـ**صناديق الموظّفين** لا للآليّ الجماعيّ |
| **Microsoft 365** | ~$4/صندوق/شهر | متوسّطة | لا تتبّع معاملاتيّ | جيّد لكن **حدود صارمة** (30/دقيقة، 10k/يوم) | غير مناسب للجماعيّ الآليّ |

**الترشيح:**
- **للإطلاق الآن:** **Brevo** (587/STARTTLS، مجانيّ، تتبّع فوريّ، لا حاجة لخروج من sandbox).
- **للتوسّع (الهدف):** **Amazon SES في `me-south-1`** — أرخص، أفضل تسليمًا، سيادة بيانات ملائمة. Postmark بديل ممتاز إن كانت الأولويّة أدوات تتبّع جاهزة.
- **فصلٌ يحمي سمعة النطاق:** الصناديق البشريّة (`info@`, `support@`) تبقى على Zoho/M365؛ **البريد الآليّ** (`no-reply@`) ينفصل على المزوّد المعاملاتيّ.

### 3.2 إصلاح الطابور — الجذر الحقيقيّ لعدم وصول البريد

**لماذا لم يصل 2FA:** كان `Mail::to()->queue(...)`. مع `QUEUE_CONNECTION=database` تُدرَج المهمّة في `jobs` بنجاح (فشل صامت لا يلتقطه `catch`)، لكن **لا عامل يُفرِّغها**. «إعادة الإرسال» كانت `send()` متزامنة فتصل — ومن هنا العرَض المُربِك. أُصلح بتحويل 2FA إلى `send()`. **لكن العطل الجذريّ باقٍ:** الثلاثة listeners `ShouldQueue` ما زالت تُصفَّف.

أمامك **قرار بين ثلاثة مسارات:**

#### المسار 1 (الأبسط للإطلاق) — إلغاء الطابور
```dotenv
QUEUE_CONNECTION=sync
```
كل شيء يُرسَل متزامنًا فورًا. العيب: يُبطئ الطلب الذي يطلق الحدث. مقبول عند الحجم الحاليّ. **الحلّ الفوريّ الموصى به حتى يُشغَّل عامل مستقرّ.**

#### المسار 2 (VPS/Contabo) — عامل `systemd` دائم
`/etc/systemd/system/atheel-queue.service`:
```ini
[Unit]
Description=Atheel Makkah Queue Worker
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=5
# high=المعاملاتيّ الحرِج (2FA)، mail=الحملات، default=الباقي
ExecStart=/usr/bin/php /var/www/atheel/artisan queue:work database \
  --queue=high,mail,default \
  --sleep=3 --tries=3 --backoff=10,60,300 \
  --max-time=3600 --max-jobs=1000 --timeout=90
StandardOutput=append:/var/www/atheel/storage/logs/queue-worker.log
StandardError=append:/var/www/atheel/storage/logs/queue-worker.log

[Install]
WantedBy=multi-user.target
```
```bash
systemctl daemon-reload && systemctl enable --now atheel-queue
systemctl status atheel-queue
journalctl -u atheel-queue -f
# بعد كل نشر (الكود الجديد لا يُلتقَط إلا بإعادة التشغيل):
php artisan queue:restart && systemctl restart atheel-queue
```

#### المسار 3 (استضافة مشتركة/Hostinger — لا systemd) — عبر cron
```cron
* * * * * cd /home/USER/atheel && /usr/bin/php artisan queue:work --stop-when-empty --max-time=55 --tries=3 >> storage/logs/queue-cron.log 2>&1
```

| المعيار | systemd (م.2) | cron `--stop-when-empty` (م.3) |
|---|---|---|
| زمن الاستجابة | فوريّ | حتى 60 ثانية تأخير |
| البيئة | VPS فقط | يعمل على المشترك |
| الإشراف | تلقائيّ (`Restart=always`) | cron يعيد الإطلاق كل دقيقة |
| خطر التداخل | لا | `--max-time=55` (< 60) لتجنّب تراكب نسختين |

> **حاسم:** المجدوِل (`backup:run`, `notifications:cleanup`, `schools:refresh-stats`, `homework:check-due-dates`) **معطّل أيضًا** ما لم يُضَف سطر المجدوِل للـcron:
> ```cron
> * * * * * cd /path/atheel && php artisan schedule:run >> /dev/null 2>&1
> ```

**مراقبة العامل (بغضّ النظر عن المسار):**
```bash
php artisan queue:monitor database:default --max=100   # ينذر إن تجاوز الطابور 100
php artisan queue:failed                                # سرد المهام الفاشلة
```

### 3.3 قابليّة التسليم لنطاق `atheel-makkah.com`

**لماذا الإرسال من IP الخادم سيّئ:** (1) IPات VPS/المشترك **مُدرَجة مسبقًا** في قوائم سمعة منخفضة → سبام/رفض؛ (2) **منفذ 25 صادر محجوب** غالبًا؛ (3) لا feedback loop → السمعة تتدهور. لذلك **استخدم relay مخصّصًا** (SES/Brevo/Postmark) بـIPات دافئة السمعة.

**سجلّات DNS المطلوبة:**

| النوع | الاسم | القيمة (مثال) | الغرض |
|---|---|---|---|
| **SPF** (TXT) | `@` | `v=spf1 include:spf.brevo.com include:amazonses.com -all` | يصرّح لخوادم المزوّد بالإرسال (`-all` = رفض الباقي) |
| **DKIM** (CNAME/TXT) | `mail._domainkey` (أو ما يعطيه المزوّد) | مفتاح عامّ (SES: 3 CNAME؛ Brevo: TXT) | توقيع تشفيريّ يُثبت عدم العبث |
| **DMARC** (TXT) | `_dmarc` | `v=DMARC1; p=quarantine; rua=mailto:dmarc@atheel-makkah.com; fo=1; pct=100` | سياسة عند الفشل + تقارير |
| **MX** | `@` | (يخصّ الاستقبال — Zoho/M365) | لا يخصّ الإرسال الآليّ |
| **Return-Path/BOUNCE** | `bounce` (CNAME للمزوّد) | يوفّره SES/Postmark | محاذاة DMARC + استقبال الارتدادات |

**تدرّج DMARC الآمن:** ابدأ `p=none` (مراقبة عبر `rua`) أسبوعين → `p=quarantine` → `p=reject`. القفز مباشرة لـ`reject` قد يُسقط بريدًا شرعيًّا قبل ضبط SPF/DKIM.

**عمليًّا:** استخدم `no-reply@atheel-makkah.com` مُتحقَّقًا (verified sender/domain)، وطابِق `MAIL_FROM_ADDRESS` معه. عنوان `From` غير مُتحقَّق = فشل DKIM = سبام.

### 3.4 معالجة الفشل وإعادة المحاولة والاختناق

**أوّلًا — سدّ الفجوة الحرجة: جداول الفشل مفقودة.** أنشئ الجداول الناقصة (وإلا تسجيل أيّ فشل يرمي استثناءً):

```php
// database/migrations/xxxx_create_failed_jobs_and_batches_tables.php
public function up(): void
{
    Schema::create('failed_jobs', function (Blueprint $t) {
        $t->id();
        $t->string('uuid')->unique();
        $t->text('connection'); $t->text('queue');
        $t->longText('payload'); $t->longText('exception');
        $t->timestamp('failed_at')->useCurrent();
    });
    Schema::create('job_batches', function (Blueprint $t) {
        $t->string('id')->primary();
        $t->string('name');
        $t->integer('total_jobs'); $t->integer('pending_jobs'); $t->integer('failed_jobs');
        $t->longText('failed_job_ids');
        $t->mediumText('options')->nullable();
        $t->integer('cancelled_at')->nullable();
        $t->integer('created_at'); $t->integer('finished_at')->nullable();
    });
}
```
> بعد النشر: `php artisan migrate --force`. (بديل: `make:queue-failed-table && make:queue-batches-table` ثمّ migrate.)

**سياسة إعادة المحاولة (لكل Mailable/Listener مُصفّف):**
```php
class SendActivityGradedNotification implements ShouldQueue
{
    public int $tries = 3;
    public array $backoff = [30, 120, 600]; // تأخير تصاعديّ (ث)
    public int $timeout = 60;
    public function failed(\Throwable $e): void {
        \Log::error('فشل إشعار تصحيح النشاط نهائيًّا: ' . $e->getMessage());
    }
}
```

**إدارة الفشل تشغيليًّا:**
```bash
php artisan queue:failed           # سرد
php artisan queue:retry <uuid>     # إعادة واحدة
php artisan queue:retry all        # إعادة الكلّ
php artisan queue:flush            # حذف الفاشلة
```

**الاختناق (مهمّ للإرسال الجماعيّ):** SES sandbox = 1/ث و200/يوم؛ Brevo المجانيّ 300/يوم؛ M365 30/دقيقة. **افصل طابور `high` (2FA/إعادة كلمة المرور) عن `mail` (الحملات)** — لا يجوز أن تؤخّر حملةٌ كودَ تحقّق ينتظره مستخدم:
```php
Bus::batch($chunks)->name('bulk-email')
   ->onQueue('mail')->allowFailures()->dispatch();
// مع RateLimiter لضبط المعدّل (§6)
```

### 3.5 ترقية `mail:test` إلى تشخيص كامل

`TestMail.php` الحاليّ يرسل فقط. الترقية تفحص **الإعداد + الجداول + الطابور + SMTP + الإرسال** بأمر واحد:

```php
protected $signature = 'mail:test {to? : عنوان المستقبِل} {--queue : اختبار عبر الطابور}';

public function handle(): int
{
    $this->components->info('تشخيص البريد — ' . setting('site_name', 'أثيل مكة'));

    // 1) عرض الإعداد الفعّال
    $mailer = config('mail.default');
    $host = config('mail.mailers.smtp.host'); $port = config('mail.mailers.smtp.port');
    $this->table(['المفتاح','القيمة'], [
        ['MAIL_MAILER', $mailer],
        ['SMTP', "$host:$port (" . config('mail.mailers.smtp.encryption') . ')'],
        ['FROM', config('mail.from.address')],
        ['QUEUE_CONNECTION', config('queue.default')],
    ]);
    if ($mailer === 'log') $this->components->warn('MAIL_MAILER=log → البريد يُكتب باللوگ ولا يُرسَل!');

    // 2) فحص جداول الطابور (اكتشف failed_jobs المفقود)
    foreach (['jobs','failed_jobs','job_batches'] as $t) {
        $ok = \Schema::hasTable($t);
        $this->line(($ok ? '✓' : '✗') . " جدول $t " . ($ok ? 'موجود' : 'مفقود — نفّذ migrate!'));
    }
    $pending = \Schema::hasTable('jobs') ? \DB::table('jobs')->count() : 0;
    $failed = \Schema::hasTable('failed_jobs') ? \DB::table('failed_jobs')->count() : 0;
    $this->line("مهام منتظرة: $pending | فاشلة: $failed");
    if ($pending > 20) $this->components->warn("$pending مهمّة عالقة — تحقّق أنّ queue:work يعمل.");

    // 3) فحص اتصال SMTP الخام
    if ($mailer === 'smtp') {
        $fp = @fsockopen(($port == 465 ? 'ssl://' : '') . $host, (int) $port, $errno, $errstr, 5);
        if ($fp) { $this->components->info("اتصال TCP بـ$host:$port ناجح"); fclose($fp); }
        else { $this->components->error("تعذّر الاتصال بـ$host:$port — $errstr ($errno)"); return self::FAILURE; }
    }

    // 4) الإرسال (أو عبر الطابور)
    $to = $this->argument('to') ?: config('mail.from.address');
    try {
        $send = fn () => Mail::raw("رسالة تشخيص — " . now()->toDateTimeString(),
            fn ($m) => $m->to($to)->subject('تشخيص البريد — ' . setting('site_name','أثيل مكة')));
        $this->option('queue') ? dispatch(function () use ($send) { $send(); }) : $send();
    } catch (\Throwable $e) {
        $this->components->error('فشل الإرسال: ' . $e->getMessage());
        $this->line('راجِع MAIL_USERNAME/MAIL_PASSWORD والتشفير (465→ssl، 587→tls).');
        return self::FAILURE;
    }
    $this->components->info($this->option('queue')
        ? "أُدرِجت في الطابور — تحقّق أنّ العامل يُفرّغها."
        : "أُرسِلت مباشرةً إلى $to — تحقّق من الوارد والسبام.");
    return self::SUCCESS;
}
```

---

## 4. القسم الثاني — كتالوج الأحداث البريديّة لكل دور (Event → Email Matrix)

الهدف: تحويل نظام الإشعارات الحاليّ — **داخل التطبيق فقط** عبر `NotificationService` (يكتب في جدول `notifications`) — إلى قناة بريديّة موازية محكومة لكل دور.

### 4.1 حقائق البنية قبل المصفوفة
1. **لا Mailable واحد يُطبّق `ShouldQueue`** — كل الـ10 تمتدّ `Mailable` وتستعمل تريت `Queueable` فقط. لذا كل نداءات `Mail::to()->send(...)` **متزامنة (blocking)** داخل طلب الويب.
2. **الطابور مُستعمَل فقط في 3 مستمعين** يطبّقون `ShouldQueue` — يُصفَّون بلا عامل فلا يصلون.
3. **`BulkMessageController` لا يُرسل بريدًا** — رسائل داخليّة فقط.
4. **الدعم/الرسائل/الاقتصاد/الاستبيانات/الترقّي كلّها إشعارات داخليّة فقط.**

### 4.2 المصفوفة الكاملة
الرمز: ✅ يعمل (متزامن) · 🟡 Mailable موجود لكنّه مُصفَّف بلا عامل (لا يصل) · ❌ ناقص (داخليّ فقط أو لا شيء).

| الدور | الحدث | المُطلِق (ملفّ/سطر) | Mailable؟ | الأولويّة | ملاحظة |
|---|---|---|---|---|---|
| student | رمز التحقق 2FA | `AuthController@login:122` / `:310` | ✅ `TwoFactorCodeMail` | حرِج | متزامن (سليم بعد `9d8c9a8`) |
| student | طلب تسجيل مُستلَم | `PublicRegistrationController:74,164` | ✅ `RegistrationSubmittedMail` | عالٍ | متزامن |
| student | قبول التسجيل + كلمة مؤقّتة | `SchoolAdminController@approveRequest:835` | ✅ `RegistrationApprovedMail` | حرِج | كلمة مؤقّتة في البريد |
| student | رفض التسجيل | `SchoolAdminController@rejectRequest:881` | ✅ `RegistrationRejectedMail` | عالٍ | متزامن |
| student | ترحيب بعد الحساب | `event(StudentRegistered)`→`SendWelcomeNotification:53` | 🟡 `WelcomeStudentMail` | عالٍ | **مُصفَّف — لا يصل** |
| student | إعادة تعيين كلمة المرور | `AuthController@sendResetLink:571` | ✅ `ResetPasswordMail` | حرِج | متزامن |
| student | فرض تغيير كلمة المرور | `CheckPasswordChangeRequired` | ❌ | متوسط | لا إشعار |
| student | تصحيح نشاط (درجة+ملاحظة) | `event(ActivityGraded)`→`SendActivityGradedNotification:49` | 🟡 `ActivityGradedMail` | عالٍ | **مُصفَّف — لا يصل** |
| student | رفض تسليم/طلب إعادة | `TeacherController@grade:302` | ❌ | متوسط | in-app فقط |
| student | منح شارة | `event(BadgeEarned)`→`SendBadgeEarnedNotification:45` | 🟡 `BadgeEarnedMail` | متوسط | **مُصفَّف — لا يصل** |
| student | ترقّي مستوى | `GamificationService:101 event(LevelUp)` | ❌ | منخفض | لا مستمع بريد |
| student | إنجاز سلسلة | `StreakService:74 event(StreakUpdated)` | ❌ | منخفض | in-app فقط |
| student | إتقان قيمة | `ValueAssessment`/`AwardService` | ❌ | منخفض | لا حدث بريديّ |
| student | نشاط جديد منشور له | `Admin\ActivityApprovalController@publish:229` | ❌ | متوسط | in-app فقط |
| student | واجب مستحقّ خلال 24س | `CheckHomeworkDueDates`→`homeworkReminder:189` | ❌ | **عالٍ** | البنية المجدولة جاهزة |
| student | واجب متأخّر | `NotificationService@homeworkOverdue:209` | ❌ | متوسط | in-app فقط |
| student | رسالة معلّم جديدة | `NotificationService@teacherMessage:151` | ❌ | متوسط | in-app فقط |
| student | استبيان قبليّ/بعديّ متاح | `CheckPendingSurveys` | ❌ | متوسط | in-app فقط |
| student | ردّ الدعم على تذكرته | `Support\SupportTicketController@reply:115` | ❌ | عالٍ | in-app فقط |
| teacher | تسجيل/قبول حساب | `PublicRegistrationController`/`approveRequest` | ✅ إعادة استخدام | عالٍ | نفس Mailables التسجيل |
| teacher | دعوة تسجيل موجّهة | — | ❌ | متوسط | لا نظام دعوات بالبريد |
| teacher | تسليم طالب بانتظار تصحيح | `StudentController@submit:1230 event(ActivityCompleted)` | ❌ | **عالٍ** | لا مستمع موجّه للمعلّم |
| teacher | اعتماد/رفض نشاطه (مدير المدرسة) | `SchoolAdminController@approveActivity:969/rejectActivity:1010` | ❌ | عالٍ | in-app فقط |
| teacher | اعتماد/رفض نشاطه (الأدمن) | `Admin\ActivityApprovalController@approve:108/reject:146` | ❌ | عالٍ | in-app فقط |
| teacher | رسالة وليّ أمر | `ParentController@sendMessage:261` | ❌ | متوسط | in-app فقط |
| teacher | تقييم معلّم جديد | `TeacherRating` | ❌ | منخفض | لا إشعار |
| teacher | ملخّص أسبوعيّ (معلّقات) | — | ❌ | متوسط | لا موجز |
| school_admin | طلب تسجيل جديد لمدرسته | `PublicRegistrationController:88,188,271` | ✅ `NewRegistrationNotificationMail` | عالٍ | متزامن؛ لكلّ مدير مدرسة |
| school_admin | نشاط معلّم بانتظار اعتماد | `TeacherController` (create activity) | ❌ | عالٍ | in-app فقط |
| school_admin | اعتماد/رفض مدرسته | `Admin\SchoolManagementController` | ❌ | عالٍ | لا إشعار للمدير |
| school_admin | تذكرة دعم من منسوبيه | `TicketController@store:76` | ❌ | متوسط | in-app فقط |
| school_admin | ملخّص أسبوعيّ للمدرسة | `schools:refresh-stats` | ❌ | متوسط | إحصاءات مُخزّنة جاهزة |
| super_admin | رسالة تواصل جديدة | `ContactController@send:76` | ✅ `emails.contact` | عالٍ | متزامن + تأكيد:85 |
| super_admin | طلب تسجيل (مستوى منصّة) | `PublicRegistrationController` | ⚠️ جزئيّ | عالٍ | لمدير المدرسة فقط لا للأدمن |
| super_admin | نشاط بانتظار الاعتماد النهائيّ | `Admin\ActivityApprovalController@index` | ❌ | عالٍ | in-app فقط |
| super_admin | تصعيد تذكرة (`escalated`) | `SupportTicket.escalated=true` | ❌ | عالٍ | لا إشعار تصعيد |
| super_admin | فشل نسخة احتياطيّة | `backup:monitor` (routes/console:43) | ❌ | عالٍ | `BACKUP_NOTIFICATION_EMAIL` معرّف غير مربوط |
| super_admin | فشل وظيفة طابور | جدول `failed_jobs` | ❌ | عالٍ | لا مراقبة بريديّة لصحّة الإرسال |
| super_admin | ملخّص أسبوعيّ للمنصّة | — | ❌ | منخفض | لا موجز |
| parent | تسجيل/ربط بابن | `PublicRegistrationController:173` | ✅ `RegistrationSubmittedMail` | عالٍ | متزامن |
| parent | تسليم الابن يتطلّب موافقة | `Api\StudentApiController:392` | ❌ | **عالٍ** | in-app فقط؛ قيمة عالية (#23) |
| parent | الابن حصل على شارة/إنجاز | `NotificationService@parentNotification:137` | ❌ | متوسط | in-app فقط |
| parent | رسالة معلّم للوليّ | `ParentTeacherMessage` | ❌ | متوسط | in-app فقط |
| parent | ملخّص أسبوعيّ عن الابن | `ParentController@getChildProgressChartData:134` | ❌ | **عالٍ** | بيانات جاهزة؛ لا موجز |
| parent | ردّ الدعم على تذكرته | `Support\SupportTicketController@reply` | ❌ | متوسط | in-app فقط |
| technical_support | تذكرة جديدة مفتوحة | `TicketController@store:76` | ❌ | عالٍ | in-app للأدمن فقط؛ لا بريد للدعم |
| technical_support | ردّ مستخدم على تذكرة | `TicketController@reply:145` | ❌ | عالٍ | in-app فقط |
| technical_support | تصعيد تذكرة | `SupportTicket.escalated` | ❌ | عالٍ | in-app فقط |
| all | رمز 2FA (Force2FAForAdmins) | `AuthController` + `Force2FAForAdmins` | ✅ `TwoFactorCodeMail` | حرِج | يشمل admin/school_admin/tech_support |

### 4.3 خلاصة الفجوة
- **يعمل:** الحسابات والتسجيل والتواصل مغطّاة.
- **مُصفَّف بلا عامل:** 3 Mailables مدفونة (ترحيب/تصحيح/شارة) — أعلى أولويّة تشغيليّة: تشغيل العامل ثمّ تحويل بقيّة الـMailables إلى `ShouldQueue`.
- **ناقص:** ~35 حدثًا، أبرزها عالي الأولويّة: تذكير الواجب، تسليم الطالب للمعلّم، اعتماد/رفض النشاط، دورة تذاكر الدعم، موافقة الوليّ، الملخّصات، ومراقبة الطابور/النسخ.

### 4.4 الـMailables الجديدة المطلوب بناؤها
مقترح 14 Mailable جديدًا (كلّها `implements ShouldQueue`) تُربَط بنقاط التطلّق الموجودة أصلًا — لا حاجة لأحداث جديدة إلا في موضعين:

| Mailable مقترح | يُطلَق من | الحدث الموجود |
|---|---|---|
| `HomeworkDueReminderMail` | `CheckHomeworkDueDates` | نعم — مجدول كل 4س |
| `NewSubmissionForTeacherMail` | مستمع جديد على `event(ActivityCompleted)` | الحدث موجود، أضِف Listener |
| `ActivityApprovalResultMail` | `SchoolAdmin`+`Admin\ActivityApproval` (approve/reject) | 4 نقاط موجودة |
| `TicketReplyMail` | `TicketController@reply` + `Support\...@reply` | نداءات `NotificationService` موجودة |
| `TicketOpenedMail` | `TicketController@store:76` | موجود |
| `TicketEscalatedMail` | عند `escalated=true` | موجود |
| `ParentApprovalRequestMail` | `Api\StudentApiController:392` | موجود |
| `TeacherMessageMail`/`ParentMessageMail` | `NotificationService@teacherMessage`/`ParentController@sendMessage:261` | موجود |
| `LevelUpMail` | مستمع جديد على `event(LevelUp)` | الحدث موجود |
| `SchoolStatusChangedMail` | `Admin\SchoolManagementController` | يحتاج hook |
| `WeeklyParentDigestMail` | أمر مجدول `digest:parents` | يستفيد من `getChildProgressChartData` |
| `WeeklySchoolDigestMail` | أمر `digest:schools` | يستفيد من `SchoolStatisticsCache` |
| `BackupFailedMail`/`QueueHealthMail` | `backup:monitor` + فحص `failed_jobs` | `BACKUP_NOTIFICATION_EMAIL` معرّف |
| `PasswordChangedMail` | `CheckPasswordChangeRequired`/`ProfileController` | موجود |

> **ملاحظة تشغيليّة حاسمة:** أيّ من هذه المُصفَّفة (والثلاثة القائمة) **لن يُرسَل ما لم يعمل عامل الطابور**. هذا البند صفر في التنفيذ، قبل بناء أيّ Mailable جديد.

---

## 5. القسم الثالث — نظام التتبّع والمراقبة (Email Logging & Monitoring)

طبقة تتبّع تلتقط **كل بريد صادر** تلقائيًّا دون لمس أيٍّ من الـ10 Mailables، وتُخزّنه في `email_logs`، وتعرضه في لوحة أدمن مع فلاتر وإحصاءات وإعادة إرسال.

> **ملاحظة معماريّة (توحيد طبقتين):** التتبّع يعمل عبر **مستمعي أحداث Mail الأصليّة** (`MessageSending`/`MessageSent`) — يلتقطان **كل** رسالة تلقائيًّا بما فيها الحملات، بلا تعديل call sites. أمّا **الحوكمة** (أعلام + تفضيلات) فتُطبَّق عند call sites عبر `MailGate` (§7.2). الطبقتان **متكاملتان**: المستمعون = التتبّع الشامل؛ `MailGate` = بوّابة القرار قبل الإرسال. جدول `email_logs` هنا هو **مصدر الحقيقة الوحيد للتتبّع** ويُشار إليه من كل الأقسام.

### 5.1 مخطّط جدول `email_logs` (الموحّد)
`database/migrations/2026_08_13_100000_create_email_logs_table.php`:

```php
Schema::create('email_logs', function (Blueprint $table) {
    $table->id();
    // المُستقبِل
    $table->string('to_email')->index();
    $table->string('to_name')->nullable();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->json('cc')->nullable(); $table->json('bcc')->nullable();
    // التصنيف
    $table->string('event')->nullable()->index();          // two_factor, activity_graded, ... (مفتاح الحدث)
    $table->string('subject', 512)->nullable();
    $table->string('mailable_class')->nullable()->index();  // App\Mail\TwoFactorCodeMail
    $table->string('category', 32)->default('transactional')->index(); // transactional|notifications|reminders|digest|marketing|campaign|system|auth
    $table->string('mailer', 32)->default('smtp');
    // الحالة
    $table->enum('status', ['queued','sending','sent','failed','bounced','complained','opened'])
          ->default('queued')->index();
    $table->text('error_message')->nullable();
    $table->unsignedTinyInteger('attempts')->default(0);
    // الربط متعدّد الأشكال (نشاط/استبيان/تذكرة/تسليم…)
    $table->nullableMorphs('related');
    // الحملات والإرسال اليدويّ
    $table->foreignId('campaign_id')->nullable()->constrained('email_campaigns')->nullOnDelete();
    $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
    // معرّفات المزوّد والتتبّع
    $table->string('message_id')->nullable()->index();      // Message-ID / SES MessageId
    $table->string('track_token', 40)->nullable()->unique(); // بكسل الفتح
    $table->string('failed_job_uuid')->nullable();          // ربط بـ failed_jobs لإعادة المحاولة
    $table->longText('body_html')->nullable();              // للمعاينة/إعادة الإرسال (اختياريّ)
    // الطوابع الزمنية
    $table->timestamp('queued_at')->nullable(); $table->timestamp('sent_at')->nullable();
    $table->timestamp('opened_at')->nullable(); $table->timestamp('failed_at')->nullable();
    $table->timestamps();
    // فهارس مركّبة
    $table->index(['status','created_at']);
    $table->index(['category','status']);
    $table->index(['event','status','created_at']);
    $table->index(['user_id','created_at']);
});
```

النموذج `app/Models/EmailLog.php`:
```php
class EmailLog extends Model
{
    protected $guarded = [];
    protected $casts = [
        'cc'=>'array','bcc'=>'array',
        'queued_at'=>'datetime','sent_at'=>'datetime','opened_at'=>'datetime','failed_at'=>'datetime',
    ];
    public function user()    { return $this->belongsTo(User::class); }
    public function sender()  { return $this->belongsTo(User::class,'sent_by'); }
    public function related() { return $this->morphTo(); }
    public function campaign(){ return $this->belongsTo(EmailCampaign::class); }
    public function scopeFailed($q)  { return $q->whereIn('status',['failed','bounced']); }
    public function scopeInCategory($q,$c){ return $q->where('category',$c); }
}
```

### 5.2 التكامل المركزيّ — التقاط كل إرسال بلا تعديل أيّ Mailable
المفتاح: أحداث `MessageSending`/`MessageSent`. نربطهما بترويسة `X-Wahy-Log`.

`app/Listeners/RecordEmailActivity.php`:
```php
use Illuminate\Mail\Events\{MessageSending, MessageSent};
use App\Models\EmailLog;

class RecordEmailActivity
{
    public function sending(MessageSending $event): void
    {
        $m = $event->message; $to = $m->getTo()[0] ?? null;
        $log = EmailLog::create([
            'to_email' => $to?->getAddress(),
            'to_name'  => $to?->getName() ?: null,
            'user_id'  => \App\Models\User::where('email',$to?->getAddress())->value('id'),
            'cc'  => array_map(fn($a)=>$a->getAddress(),$m->getCc()),
            'bcc' => array_map(fn($a)=>$a->getAddress(),$m->getBcc()),
            'subject' => $m->getSubject(),
            'event'        => $event->data['__event'] ?? null,
            'mailable_class'=> $event->data['__mailable'] ?? null,
            'category'     => $event->data['__category'] ?? 'transactional',
            'related_type' => $event->data['__related_type'] ?? null,
            'related_id'   => $event->data['__related_id'] ?? null,
            'campaign_id'  => $event->data['__campaign_id'] ?? null,
            'sent_by'      => $event->data['__sent_by'] ?? null,
            'mailer' => config('mail.default'),
            'status' => 'sending',
            'track_token' => \Illuminate\Support\Str::random(40),
            'queued_at' => now(),
        ]);
        $m->getHeaders()->addTextHeader('X-Wahy-Log', (string) $log->id);
    }

    public function sent(MessageSent $event): void
    {
        $id = $event->message->getOriginalMessage()->getHeaders()->get('X-Wahy-Log')?->getBodyAsString();
        if (! $id) return;
        EmailLog::whereKey($id)->update([
            'status'=>'sent', 'message_id'=>$event->sent->getMessageId(),
            'sent_at'=>now(), 'attempts'=>\DB::raw('attempts + 1'),
        ]);
    }
}
```

**التسجيل** — لا يوجد `EventServiceProvider` حاليًّا (`bootstrap/providers.php` = `AppServiceProvider`+`AuthServiceProvider` فقط)، فنربط في `AppServiceProvider::boot()`:
```php
Event::listen(MessageSending::class, [RecordEmailActivity::class, 'sending']);
Event::listen(MessageSent::class,    [RecordEmailActivity::class, 'sent']);
```

**التقاط الفشل:**
- المتزامن يرفع `TransportException` → يُلتقط في `MailGate` (§7.2) الذي يحدّث السجلّ إلى `failed`. + مهمّة تسوية دوريّة: صفوف `sending` أقدم من 10 دقائق → `failed`.
- المُصفَّف: نربط `Queue::failing` في `AppServiceProvider`:
```php
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
Queue::failing(function (JobFailed $e) {
    EmailLog::where('status','sending')->where('created_at','>',now()->subMinutes(15))
        ->latest()->first()?->update([
            'status'=>'failed',
            'error_message'=>\Illuminate\Support\Str::limit($e->exception->getMessage(),1000),
            'failed_at'=>now(),
        ]);
});
```

**الإثراء الاختياريّ** (category/related/event) بلا تعديل الـMailables — تِرَيْت `app/Mail/Concerns/LogsEmail.php`:
```php
trait LogsEmail {
    public function logAs(string $category, string $event = null, $related = null, $meta = []): static {
        $this->with(array_filter([
            '__mailable'=>static::class, '__category'=>$category, '__event'=>$event,
            '__related_type'=>$related ? $related::class : null,
            '__related_id'=>$related?->getKey(),
        ] + $meta));
        return $this;
    }
}
```
هكذا يعمل التتبّع الأساسيّ (المُستقبِل/الموضوع/الحالة/message_id) **فورًا لكل الرسائل العشر**، والتصنيف يُضاف تدريجيًّا.

### 5.3 تتبّع الفتح (بكسل شفّاف) — اختياريّ
يُحقن بكسل 1×1 في نهاية `<body>` عبر `MessageSending`:
```php
$html = $m->getHtmlBody();
if ($html) {
    $pixel = '<img src="'.route('email.track.open',$log->track_token).'" width="1" height="1" alt="" style="display:none">';
    $m->html(str_ireplace('</body>',$pixel.'</body>',$html) ?: $html.$pixel);
}
```
المسار (عامّ بلا auth): `Route::get('/email/track/open/{token}', [EmailTrackingController::class,'open'])->name('email.track.open');` — يُحدّث `opened_at`/`status=opened` (مرّة، لا يتراجع عن `bounced`) ويُعيد GIF شفّافًا.

**اعتبارات للمالك:** Apple Mail Privacy وGmail proxy يجلبان الصور مسبقًا → **تضخيم** معدّل الفتح؛ حجب الصور افتراضيًّا → **نقص**. يُعرَض «فُتِح» كمؤشّر تقريبيّ. **يُستثنى بريد المصادقة** (`category IN ('auth','transactional')` للحرِج) — لا بكسل على 2FA/إعادة كلمة المرور.

### 5.4 استقبال Bounce/Complaint webhooks
المسارات (خارج CSRF عبر `validateCsrfTokens(except: […])` في `bootstrap/app.php`):
```php
Route::post('/webhooks/email/postmark', [EmailWebhookController::class,'postmark']);
Route::post('/webhooks/email/ses',      [EmailWebhookController::class,'ses']);
```
Postmark:
```php
public function postmark(Request $r) {
    $this->verifyBasicAuth($r);   // إلزاميّ — وإلّا تزوير حالات ارتداد
    $mid = $r->input('MessageID');
    $status = match($r->input('RecordType')) {
        'Bounce'=>'bounced', 'SpamComplaint'=>'complained', 'Open'=>'opened', default=>null,
    };
    if ($status && $mid) {
        EmailLog::where('message_id','like',"%$mid%")->update([
            'status'=>$status, 'error_message'=>$r->input('Description'),
            'opened_at'=>$status==='opened' ? now() : null,
        ]);
    }
    return response()->noContent();
}
```
لـ SES: فكّ تغليف SNS (تأكيد `SubscriptionConfirmation` تلقائيًّا)، قراءة `notificationType` و`mail.messageId`. **التحقّق إلزاميّ:** توقيع SNS لـSES، Basic-Auth/سرّ مشترك لـPostmark.

### 5.5 لوحة مراقبة الأدمن
نُحاكي نمط «سجلّ الرسائل» القائم (`Admin\MessagesLogController` + `SanitizesCsvOutput`)، داخل مجموعة `admin.` تحت `can:access-admin`:
```php
Route::prefix('email-logs')->name('email-logs.')->group(function () {
    Route::get('/',            [Admin\EmailLogController::class,'index'])->name('index');
    Route::get('/statistics',  [Admin\EmailLogController::class,'statistics'])->name('statistics');
    Route::get('/export',      [Admin\EmailLogController::class,'export'])->name('export');
    Route::get('/{log}',       [Admin\EmailLogController::class,'show'])->name('show');
    Route::post('/{log}/resend',[Admin\EmailLogController::class,'resend'])->name('resend');
});
```
> المتحكّم يستخدم `use SanitizesCsvOutput;` لتفادي حقن CSV (درس موثّق في تدقيقات الأدمن).

**الفلاتر:** الدور (`whereHas('user',role=?)`)، الحالة، التاريخ (افتراضيّ 30 يومًا)، الفئة، البحث (`to_email`/`subject`/`message_id`)، الحملة.

**الإحصاءات** (استعلام تجميعيّ واحد):
```php
EmailLog::selectRaw("
   COUNT(*) total,
   SUM(status='sent') sent,
   SUM(status IN('failed','bounced')) failed,
   SUM(status='queued') stuck,
   SUM(status='opened') opened
")->where('created_at','>',now()->subDays(30))->first();
```
تُعرَض: المُرسَل، الفاشل، **معدّل الفشل**، **معدّل الفتح**، و«**عالق في الطابور**» (`stuck`) — إنذار مباشر لعطل العامل.

**التفاصيل `show()`:** كل الحقول + `error_message` + خطّ زمنيّ + معاينة `body_html` في iframe معزول + رابط الكيان المرتبط + رابط المهمّة الفاشلة في `failed_jobs`.

**إعادة الإرسال `resend()`:** (1) بريد مخزَّن `body_html`: `Mail::html(...)` مع تسجيل صفّ جديد مربوط بـ`sent_by`؛ (2) إعادة تشغيل مهمّة فاشلة عبر `failed_job_uuid` المخزَّن (منطق `queue:retry {uuid}`).

**التنقّل:** عنصر «سجلّ البريد» في شريط الأدمن بجانب «سجلّ الرسائل».

---

## 6. القسم الرابع — المُرسِل الجماعيّ/المخصّص للأدمن (Campaigns)

مبنيّ على البنية القائمة: بوّابة `access-admin`، طابور، محرّر `data-rich-editor`، قالب `emails.layouts.master`، نمط `Mail::to()->send()`.

> **شرط حاكم (Blocker):** لا يجوز إطلاق حملة قبل تشغيل عامل دائم ومضبط SMTP فعّال (راجع §3).

### 6.1 البنية والملفّات

| الطبقة | الملفّ |
|---|---|
| المتحكّم | `app/Http/Controllers/Admin/EmailCampaignController.php` |
| النماذج | `EmailCampaign`, `EmailCampaignRecipient`, `EmailTemplate` |
| المُرسَلة | `app/Mail/CampaignMail.php` (`implements ShouldQueue`) |
| الخدمات | `AudienceResolver`, `VariableRenderer` |
| المهام | `SendCampaignRecipientJob` (مستلِم واحد)، `DispatchCampaignJob` (بناء الدفعة) |
| الأمر | `app/Console/Commands/DispatchDueCampaigns.php` (`campaigns:dispatch-due`) |
| القوالب | `resources/views/admin/email-campaigns/{index,create,show,recipients}.blade.php` + `emails/campaign.blade.php` |

المسارات داخل مجموعة الأدمن القائمة (`routes/web.php:191`):
```php
$ec = \App\Http\Controllers\Admin\EmailCampaignController::class;
Route::prefix('email-campaigns')->name('email-campaigns.')->group(function () use ($ec) {
    Route::get('/',                 [$ec,'index'])->name('index');
    Route::get('/create',           [$ec,'create'])->name('create');
    Route::post('/audience-count',  [$ec,'audienceCount'])->name('audience-count');
    Route::post('/user-search',     [$ec,'userSearch'])->name('user-search');
    Route::post('/preview',         [$ec,'preview'])->name('preview');
    Route::post('/test-send',       [$ec,'testSend'])->name('test-send');
    Route::post('/',                [$ec,'store'])->name('store');
    Route::post('/{campaign}/send', [$ec,'send'])->name('send');
    Route::post('/{campaign}/cancel',[$ec,'cancel'])->name('cancel');
    Route::get('/{campaign}',       [$ec,'show'])->name('show');
    Route::get('/{campaign}/recipients',[$ec,'recipients'])->name('recipients');
    Route::delete('/{campaign}',    [$ec,'destroy'])->name('destroy');
});
```

### 6.2 الجمهور المستهدف
يُخزَّن في `audience_type`+`audience_filter`(JSON)، يحوّله `AudienceResolver` لاستعلام `User` مُقيَّد بـ`active()` + `email_opt_out=false` + بريد صالح:

| النوع | الاستعلام |
|---|---|
| `all` | `User::active()->where('email_opt_out',false)` |
| `role` | `->role($filter['role'])` (`User::scopeRole:146`، +`secondary_roles`) |
| `school` | `->inSchool($filter['school_id'])` (`:172`) |
| `classroom` | `->whereHas('classrooms', fn($q)=>$q->where('classrooms.id',$filter['classroom_id']))` |
| `manual` | `->whereIn('id',$filter['user_ids'])` |
| `import` | مطابقة `users.email`؛ الخارج يُدرَج مباشرةً في recipients (بلا user_id) |

```php
public function resolve(EmailCampaign $c): Builder
{
    $f = $c->audience_filter ?? [];
    $q = User::query()->active()->where('email_opt_out',false)
        ->whereNotNull('email')->where('email','!=','');
    return match ($c->audience_type) {
        'all'=>$q, 'role'=>$q->role($f['role']),
        'school'=>$q->inSchool((int)$f['school_id']),
        'classroom'=>$q->whereHas('classrooms', fn($x)=>$x->where('classrooms.id',(int)$f['classroom_id'])),
        'manual'=>$q->whereIn('id', array_map('intval',$f['user_ids'] ?? [])),
        'import'=>$q->whereIn('email', collect($f['emails'] ?? [])->map(fn($e)=>mb_strtolower(trim($e)))),
        default=>$q->whereRaw('1 = 0'), // fail-closed: لا جمهور مجهول
    };
}
```
> **عزل:** القسم كلّه سوبر أدمن فقط. أيّ توسعة لمدير المدرسة يجب أن تمرّ عبر `managedSchoolIds()` (`:729`) لا `school_id` المفرد. **تجزئة القراءة:** `->select('id','name','email')->lazyById(1000)` عند سكب المستلِمين — لا تحميل كامل في الذاكرة.

### 6.3 الإنشاء — العنوان والمتن والمتغيّرات
- **العنوان:** `<input name="subject">` يقبل متغيّرات.
- **المتن:** نفس المحرّر الموحّد:
```blade
<div data-rich-editor="campaignBody" data-target="bodyHidden" dir="rtl" hidden>{!! safe_html(old('body')) !!}</div>
<textarea name="body" id="bodyHidden" hidden>{!! safe_html(old('body')) !!}</textarea>
@push('scripts')<script src="{{ asset('js/rich-editor.js') }}"></script>@endpush
```
- **المتغيّرات** يستبدلها `VariableRenderer` لكلّ مستلِم:

| الرمز | المصدر |
|---|---|
| `{{name}}`/`{{email}}` | `$user->name`/`$user->email` |
| `{{role_ar}}` | `User::getRoleNameAr($user->role)` (`:800`) |
| `{{school}}` | `$user->school?->name` (`:180`) |
| `{{level}}`/`{{points}}` | `$user->level` (`:296`)/`$user->totalPoints()` (`:349`) |
| `{{dashboard_url}}` | `url(User::getRoleDashboardRoute($user->role))` (`:834`) |
| `{{unsubscribe_url}}` | رابط موقَّع (إلزاميّ في التذييل) |

```php
public function render(string $tpl, User $u): string
{
    $map = [
        'name'=>e($u->name), 'email'=>e($u->email),
        'role_ar'=>User::getRoleNameAr($u->role),
        'school'=>e(optional($u->school)->name ?? ''),
        'level'=>(string)$u->level, 'points'=>(string)$u->totalPoints(),
        'dashboard_url'=>url(User::getRoleDashboardRoute($u->role)),
        'unsubscribe_url'=>URL::signedRoute('email.unsubscribe',['user'=>$u->id]),
    ];
    return preg_replace_callback('/\{\{\s*([a-z_]+)\s*\}\}/', fn($m)=>$map[$m[1]] ?? $m[0], $tpl);
}
```
> **أمن XSS:** المتن عبر `{!! safe_html($rendered) !!}`؛ قيم المتغيّرات تُهرَّب بـ`e()` قبل الحقن. **القوالب المحفوظة:** جدول `email_templates` `(name,subject,body)` يُملأ عبر AJAX.

### 6.4 الأمان قبل الإرسال

| الضمانة | التنفيذ |
|---|---|
| المعاينة | `POST /preview` يُصيّر `emails.campaign` بمستخدم وهميّ في `<iframe sandbox>` |
| إرسال تجريبيّ | `POST /test-send` → `->sendNow()` لبريد الأدمن نفسه قبل الحملة |
| عدّاد المستلمين | `POST /audience-count` → `AudienceResolver::resolve()->count()` حيًّا |
| تأكيد نهائيّ | مودال بالعدد + الزمن المُقدَّر، يطلب كتابة العدد يدويًّا لحملة > 500 |

### 6.5 التنفيذ عبر الطابور بتجزئة
```php
// DispatchCampaignJob
public function handle(): void
{
    $c = $this->campaign->fresh();
    $c->update(['status'=>'sending','started_at'=>now()]);
    $jobs = $c->recipients()->where('status','pending')->lazyById(500)
        ->map(fn($r)=>new SendCampaignRecipientJob($r->id))->all();
    Bus::batch($jobs)->name("campaign:{$c->id}")->onQueue('mail')
        ->allowFailures()
        ->then(fn()=>$c->update(['status'=>'sent','finished_at'=>now()]))
        ->catch(fn()=>$c->update(['status'=>'failed']))
        ->dispatch();
}
```
```php
// SendCampaignRecipientJob
public function middleware(): array { return [new RateLimited('email-campaign')]; }
public function handle(VariableRenderer $vr): void
{
    $r = EmailCampaignRecipient::with('campaign')->find($this->recipientId);
    if (!$r || $r->status !== 'pending') return;            // idempotent
    $user = $r->user_id ? User::find($r->user_id) : null;
    if ($user && $user->email_opt_out) { $r->update(['status'=>'skipped']); return; }
    try {
        Mail::to($r->email)->send(new CampaignMail($r->campaign,$user,$r));
        $r->update(['status'=>'sent','sent_at'=>now()]);
    } catch (\Throwable $e) {
        $r->update(['status'=>'failed','error'=>mb_substr($e->getMessage(),0,500)]);
        throw $e;
    }
}
```
```php
// AppServiceProvider (بجانب access-admin)
RateLimiter::for('email-campaign', fn()=>Limit::perMinute(20));
```
**المتابعة:** `show.blade.php` يقرأ `Bus::findBatch($c->batch_id)` (`->progress()`, `->failedJobs()`) + تجميعة recipients حسب `status`.

### 6.6 الجدولة
عمود `scheduled_at`؛ إن مستقبلًا → حالة `scheduled` بلا دفعة. الأمر:
```php
EmailCampaign::where('status','scheduled')->where('scheduled_at','<=',now())
    ->each(fn($c)=>DispatchCampaignJob::dispatch($c));
```
```php
$schedule->command('campaigns:dispatch-due')->everyMinute()->withoutOverlapping();
```
> يتطلّب `schedule:run` كل دقيقة — منفصل عن عامل الطابور. كلاهما مطلوب.

### 6.7 الصلاحيات والتدقيق
- المجموعة كلّها `can:access-admin` = سوبر أدمن فقط.
- `EmailCampaign` يستخدم `Spatie\Activitylog\Traits\LogsActivity` + عمود `created_by` (FK) — «من أرسل ماذا».

### 6.8 إلغاء الاشتراك (opt-out) — يُدار بجدول التفضيلات الموحّد (§7.3)
- عمود `users.email_opt_out` (boolean، default false) + في `$fillable`/`$casts`.
- كل بريد حملة يحمل `{{unsubscribe_url}}` موقَّع (`URL::signedRoute`).
- المعالِج يضبط عبر `forceFill()->saveQuietly()` (تجنّبًا لحارس `booted` في `User:38`).
- **الإنفاذ مزدوج:** `AudienceResolver` يستثني وقت السكب، و`SendCampaignRecipientJob` يُعيد الفحص وقت الإرسال. **الحرِج المعامليّ يتجاوز opt-out.**

### 6.9 مخطّطات الجداول (SQL)
```sql
CREATE TABLE email_campaigns (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    body LONGTEXT NOT NULL,                    -- HTML (مُعقَّم بـ safe_html عند العرض)
    audience_type VARCHAR(20) NOT NULL,        -- all|role|school|classroom|manual|import
    audience_filter JSON NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft', -- draft|scheduled|sending|sent|failed|cancelled
    recipients_total INT UNSIGNED NOT NULL DEFAULT 0,
    sent_count INT UNSIGNED NOT NULL DEFAULT 0,
    failed_count INT UNSIGNED NOT NULL DEFAULT 0,
    batch_id CHAR(36) NULL,
    scheduled_at TIMESTAMP NULL, started_at TIMESTAMP NULL, finished_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    INDEX idx_status_scheduled (status, scheduled_at),
    CONSTRAINT fk_campaign_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE email_campaign_recipients (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,               -- NULL للمستورَد خارج users
    email VARCHAR(255) NOT NULL, name VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending|sent|failed|skipped
    error VARCHAR(500) NULL, sent_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    INDEX idx_campaign_status (campaign_id, status),
    UNIQUE KEY uq_campaign_email (campaign_id, email),  -- يمنع الازدواج
    CONSTRAINT fk_rcpt_campaign FOREIGN KEY (campaign_id) REFERENCES email_campaigns(id) ON DELETE CASCADE,
    CONSTRAINT fk_rcpt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE email_templates (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL, subject VARCHAR(255) NOT NULL,
    body LONGTEXT NOT NULL, created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
);
ALTER TABLE users ADD COLUMN email_opt_out TINYINT(1) NOT NULL DEFAULT 0 AFTER notifications_enabled;
```

### 6.10 تدفّق الشاشات
```
index: قائمة الحملات + الحالة + created_by + sent/failed + "حملة جديدة"
  ▼
create: [1] الجمهور (all/role/school/classroom/manual/import) + عدّاد حيّ
        [2] العنوان + المحرّر + متغيّرات {{name}} + قالب محفوظ
        [3] معاينة (iframe) + "إرسال تجريبيّ لبريدي"
        [4] جدولة (scheduled_at) → "حفظ كمسوّدة" أو "إطلاق"
  ▼ (store → مسوّدة، ثم {campaign}/send)
مودال تأكيد (العدد + الزمن، كتابة العدد للحملات الكبيرة)
  ▼
show: شريط تقدّم Bus::batch + تجميعة recipients + معدّل التسليم/الفتح (email_logs) + "إلغاء"
  ▼
recipients: جدول (email,status,sent_at,error) + تصدير CSV
```

---

## 7. القسم الخامس — القوالب والإعدادات والحوكمة (Templates, Settings & Governance)

### 7.1 القالب الموحّد الآمن لعملاء البريد
المشكلة: القالب الحاليّ صُمِّم كصفحة ويب. الحلّ: **تخطيط جدوليّ (table-based) بـCSS مضمَّن** مع احتياطيّات ألوان صلبة، وسحب الألوان من `settings`.

`resources/views/emails/layouts/base.blade.php` (يستبدل `master.blade.php` تدريجيًّا):
```blade
@php
    $primary  = setting('email_primary_color', setting('primary_color', '#2BA55D'));
    $siteName = setting('site_name', 'أثيل مكة');
    $logo     = setting('site_logo') ? asset('storage/data/'.setting('site_logo')) : null;
    $contact  = setting('contact_email', config('mail.from.address'));
    $unsub    = $unsubscribeUrl ?? null;
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="x-apple-disable-message-reformatting">
  <title>@yield('title', $siteName)</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:'Segoe UI',Tahoma,Arial,sans-serif;direction:rtl;">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;">@yield('preheader', $siteName)</div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;">
    <tr><td align="center" style="padding:24px 12px;">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:14px;overflow:hidden;">
        <tr><td align="center" style="background:{{ $primary }};padding:32px 24px;">
          @if($logo)<img src="{{ $logo }}" alt="{{ $siteName }}" width="150" style="display:block;border:0;max-width:150px;">
          @else<span style="color:#fff;font-size:26px;font-weight:800;">{{ $siteName }}</span>@endif
        </td></tr>
        <tr><td style="padding:36px 32px;color:#1f2937;font-size:16px;line-height:1.9;">@yield('content')</td></tr>
        <tr><td style="background:#f9fafb;padding:24px 32px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;line-height:1.7;" align="center">
          <p style="margin:0 0 8px;">&copy; {{ date('Y') }} {{ $siteName }}. جميع الحقوق محفوظة.</p>
          <p style="margin:0 0 8px;">للاستفسار: <a href="mailto:{{ $contact }}" style="color:{{ $primary }};">{{ $contact }}</a></p>
          @if($unsub)<p style="margin:8px 0 0;">لا ترغب بهذا النوع؟ <a href="{{ $unsub }}" style="color:#6b7280;text-decoration:underline;">إلغاء الاشتراك</a></p>@endif
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
```

**مكوّنات Blade** — `resources/views/emails/components/`:
```blade
{{-- button.blade.php --}}
@props(['url', 'color' => setting('email_primary_color', '#2BA55D')])
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px auto;"><tr><td align="center" style="border-radius:10px;background:{{ $color }};">
  <a href="{{ $url }}" style="display:inline-block;padding:14px 38px;color:#fff;font-weight:700;font-size:16px;text-decoration:none;border-radius:10px;">{{ $slot }}</a>
</td></tr></table>
```
```blade
{{-- alert.blade.php --}}
@props(['type' => 'info'])
@php $c = ['info'=>['#eff6ff','#3b82f6','#1e40af'],'success'=>['#ecfdf5','#10b981','#065f46'],
         'warning'=>['#fffbeb','#f59e0b','#92400e'],'danger'=>['#fef2f2','#ef4444','#991b1b']][$type]; @endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:22px 0;">
 <tr><td style="background:{{ $c[0] }};border-right:4px solid {{ $c[1] }};border-radius:10px;padding:16px 20px;color:{{ $c[2] }};font-size:15px;line-height:1.7;">{{ $slot }}</td></tr></table>
```
+ `data-table.blade.php` (مفتاح/قيمة). الاستدعاء: `<x-emails.button :url="$actionUrl">عرض التفاصيل</x-emails.button>`.

> **قاعدة إلزاميّة:** بعد أيّ تعديل قالب، فحص التصريف على المُصرَّف (`@php` قبل `@extends` يكسر التصريف بلا أن يكشفه `view:cache`):
> ```bash
> php artisan view:clear && php artisan view:cache
> find storage/framework/views -name "*.php" -exec php -l {} \;
> ```
> **إصلاحات إلزاميّة:** حذف روابط `127.0.0.2:8000` و`sa-salem.com` من `two-factor-code.blade.php`، وتصحيح عنوانه من «قيمّ» إلى «أثيل مكة»، وجعله يمتدّ القالب الأمّ.

### 7.2 البوّابة المركزيّة `MailGate` وأعلام التفعيل
تُخزَّن الأعلام في `settings` بنوع `boolean` بمفتاح `email_enabled.<event>` + علم رئيسيّ `email_master_enabled`. لا هجرة — فقط بذرة.

`app/Services/Mail/MailGate.php` (تُلفّ كل استدعاءات `Mail::to`، وهي **بوّابة القرار** المكمّلة لطبقة التتبّع في §5):
```php
namespace App\Services\Mail;
use App\Models\{EmailLog, User};
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailGate
{
    private const CRITICAL = ['two_factor','reset_password','registration_approved','registration_rejected'];

    public static function dispatch(string $event, ?User $user, string $email, Mailable $mailable, string $category = 'transactional'): bool
    {
        if (! setting('email_master_enabled', true))  return self::skip($event,$email,'master_off');
        if (! setting("email_enabled.$event", true))  return self::skip($event,$email,'event_off');
        if (! in_array($event, self::CRITICAL, true) && $user && ! EmailPreference::allows($user, $category))
            return self::skip($event,$email,'user_optout');

        // تمرير الميتاداتا لطبقة التتبّع (RecordEmailActivity يلتقطها من data)
        if (method_exists($mailable,'logAs')) $mailable->logAs($category, $event);
        try {
            Mail::to($email)->send($mailable);   // ← ->queue بعد المرحلة 1
            return true;
        } catch (\Throwable $e) {
            // تحديث آخر سجلّ sending إلى failed (طبقة التتبّع أنشأته)
            EmailLog::where('to_email',$email)->where('status','sending')
                ->latest()->first()?->update(['status'=>'failed','error_message'=>$e->getMessage(),'failed_at'=>now()]);
            report($e);
            return false;
        }
    }
}
```
كل موقع إرسال يتحوّل من `Mail::to($student->email)->send(new ActivityGradedMail($submission))` إلى:
```php
MailGate::dispatch('activity_graded', $student, $student->email, new ActivityGradedMail($submission), 'notifications');
```

**واجهة الأدمن:** بطاقة «إعدادات البريد» في `admin/settings` (المسار `admin.settings.update` موجود)، تحفظ عبر `set_setting("email_enabled.$k", $bool, 'boolean')`:

| المفتاح | الحدث | الفئة | افتراضيّ |
|---|---|---|---|
| `email_master_enabled` | القاطع العامّ | — | true |
| `email_enabled.two_factor` | كود 2FA | transactional (حرِج) | true |
| `email_enabled.reset_password` | استعادة | transactional (حرِج) | true |
| `email_enabled.welcome_student` | ترحيب | notifications | true |
| `email_enabled.activity_graded` | تصحيح | notifications | true |
| `email_enabled.badge_earned` | شارة | notifications | true |
| `email_enabled.homework_due` | تذكير واجب | reminders | true |
| `email_enabled.registration_*` | مراحل التسجيل | transactional | true |
| `email_enabled.weekly_digest` | ملخّص أسبوعيّ | digest | false |

### 7.3 تفضيلات المستخدم و opt-out (الجدول الموحّد)
`database/migrations/2026_08_13_000001_create_user_email_preferences_table.php`:
```php
Schema::create('user_email_preferences', function (Blueprint $t) {
    $t->id();
    $t->foreignId('user_id')->constrained()->cascadeOnDelete();
    $t->boolean('transactional')->default(true);  // حرِج — لا يُعطَّل عمليًّا
    $t->boolean('notifications')->default(true);   // تصحيح/شارة/رسائل
    $t->boolean('reminders')->default(true);       // واجبات/مواعيد
    $t->boolean('digest')->default(false);         // ملخّصات دوريّة
    $t->boolean('marketing')->default(false);      // opt-in صريح
    $t->string('unsubscribe_token', 64)->unique();
    $t->timestamp('unsubscribed_all_at')->nullable();
    $t->timestamps();
    $t->unique('user_id');
});
```
`app/Models/EmailPreference.php`:
```php
public static function allows(User $user, string $category): bool
{
    $p = static::firstOrCreate(['user_id'=>$user->id],
        ['unsubscribe_token'=>\Illuminate\Support\Str::random(48)]);
    if ($p->unsubscribed_all_at) return false;
    return (bool) ($p->{$category} ?? true);
}
```
**رابط الإلغاء + ترويسة List-Unsubscribe** (للفئات غير الحرِجة فقط):
```php
Route::get('/email/unsubscribe/{token}', UnsubscribeController::class)->name('email.unsubscribe'); // بلا مصادقة
```
```php
public function headers(): \Illuminate\Mail\Mailables\Headers {
    return new \Illuminate\Mail\Mailables\Headers(text: [
        'List-Unsubscribe'      => '<'.route('email.unsubscribe',$this->token).'>',
        'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
    ]);
}
```
شاشة تفضيلات لكل دور: `Route::get('/settings/notifications', ...)` — خانة `transactional` معروضة كـ«لا يمكن تعطيلها لأسباب أمنيّة».

> **توحيد التفضيلات:** هذا الجدول (per-category) هو **المصدر الموحّد** لـopt-out عبر كل الأقسام (يحلّ محلّ أيّ `email_preferences` per-event مقترح سابقًا). التحكّم الحَدَثيّ الدقيق يُغطّى بأعلام `settings` في §7.2 على مستوى المنصّة.

### 7.4 From/Reply-To والتوطين والمرفقات
اليوم `config/mail.php` يوفّر `from` عالميًّا فقط. نُضيف خرائط فئات:

| الفئة | From | Reply-To |
|---|---|---|
| transactional | `no-reply@atheel-makkah.com` | `support@atheel-makkah.com` |
| notifications | `no-reply@atheel-makkah.com` | (بلا) |
| support | `support@atheel-makkah.com` | `support@atheel-makkah.com` |
| digest/marketing | `hello@atheel-makkah.com` | `support@atheel-makkah.com` |

```php
public function envelope(): Envelope {
    return new Envelope(
        from: new Address(setting('mail_from.transactional', config('mail.from.address')), setting('site_name','أثيل مكة')),
        replyTo: [new Address('support@atheel-makkah.com')],
        subject: safe_mail_subject('كود التحقق — '.setting('site_name','أثيل مكة')), // إصلاح «قيمّ»
    );
}
```
- **التوطين:** عربيّة RTL؛ العناوين عبر `safe_mail_subject()` (يمنع حقن CRLF)؛ النصوص المتكرّرة تُستخرج إلى `resources/lang/ar/emails.php`.
- **المرفقات:** `Attachment::fromStorageDisk('public', $path)` عند الحاجة (شهادة/تقرير PDF).

### 7.5 الخصوصيّة والاحتفاظ
- لا تُخزَّن أجسام الرسائل (فقط event/subject/status) — تقليل بيانات. (`body_html` يُخزَّن فقط للحملات/اليدويّ عند الحاجة لإعادة الإرسال.)
- `marketing` = opt-in صريح؛ `digest` افتراضيّ false.
- الاحتفاظ: توسعة `notifications:cleanup` ليشمل `email_logs` أقدم من 180 يومًا.
- سجلّ الإلغاء يُحفظ بـ`unsubscribed_all_at` (إثبات سحب الموافقة).

---

## 8. خارطة الطريق التنفيذيّة المُرحَّلة

الترتيب يحترم التبعيّات: **لا فائدة من مُرسِل جماعيّ قبل عمل SMTP، ولا من ملخّصات قبل وجود العامل والتتبّع.**

| المرحلة | المخرجات | الجهد | التبعيّات | معايير القبول |
|---|---|:---:|---|---|
| **P1 — تشغيل وتأكيد الآلية** (الأساس، حرِج) | (1) حسم بيئة الإنتاج (Hostinger/Contabo)؛ (2) هجرة `failed_jobs`+`job_batches`؛ (3) ضبط SMTP الحقيقيّ (تصحيح `MAIL_PASSWORD`، توافق port/encryption، رفع `MAIL_TIMEOUT`~15)؛ (4) تشغيل عامل دائم (systemd/cron/`sync`) + `queue:restart` بالنشر + `schedule:run`؛ (5) نشر SPF/DKIM/DMARC؛ (6) ترقية `mail:test` | **M** | — | `mail:test` ينجح للإنتاج (لا `log`)؛ `failed_jobs`+`job_batches` موجودان؛ صفّ `jobs` يُفرَّغ خلال ثوانٍ؛ فشل SMTP يظهر في `failed_jobs` لا للمستخدم؛ mail-tester ≥ 9/10؛ SPF/DKIM/DMARC متحقَّقة |
| **P2 — القالب الموحّد وإصلاح القوالب** | `layouts/base.blade.php` + `components/{button,alert,data-table}`؛ ترحيل 10 قوالب؛ حذف الخطّ الخارجيّ و`backdrop-filter`؛ توحيد الألوان من `settings`؛ إصلاح روابط/عنوان 2FA | **M** | P1 | كل قالب يمرّ `php -l` بعد `view:cache`؛ عرض سليم في Gmail+Outlook؛ لا صور بعيدة محجوبة؛ لا `127.0.0.2`/`sa-salem.com` |
| **P3 — التتبّع والحوكمة** | هجرة `email_logs`؛ مستمعا `MessageSending/MessageSent` + `Queue::failing`؛ `MailGate` بأعلام `email_enabled.*`+`email_master_enabled`؛ تحويل الـ18 موقع إرسال إليه؛ لوحة `/admin/email-logs` (فلاتر+إحصاءات+إعادة إرسال، `SanitizesCsvOutput`) | **M** | P1 | كل إرسال يُسجَّل بحالته؛ تعطيل علم يوقف نوعه فورًا؛ بطاقة «عالق في الطابور» تكشف عطل العامل؛ إعادة الإرسال تعمل |
| **P4 — تفضيلات المستخدم و opt-out** | هجرة `user_email_preferences` + `EmailPreference::allows()`؛ `UnsubscribeController` + مسار موقَّع؛ ترويسة `List-Unsubscribe`؛ شاشة تفضيلات لكل دور؛ `MailGate` يحترم opt-out | **S** | P3 | رابط الإلغاء يعمل بلا تسجيل؛ الحرِج (2FA/استعادة/اعتماد) يصل رغم الإلغاء؛ زرّ Gmail الأصليّ يظهر |
| **P5 — المُرسِل الجماعيّ/المخصّص** | هجرات `email_campaigns`/`recipients`/`templates` + `email_opt_out`؛ `EmailCampaignController` + `AudienceResolver`/`VariableRenderer`؛ `Bus::batch` مُقطَّع `lazyById` + `RateLimited`؛ الجدولة (`campaigns:dispatch-due`)؛ شاشات الحملة | **L** | P3, P4 | حملة لـN مستخدم تُقطَّع بلا استنفاد ذاكرة؛ الملغون مستبعَدون (سكب+إرسال)؛ كل رسالة مُسجَّلة؛ حقن CRLF محيَّد؛ `high` معزول عن `mail` |
| **P6 — توسعة الأحداث لكل دور** | بناء الـ14 Mailable الجديدة (`ShouldQueue`) خلف أعلام `email_enabled.*`: مدير مدرسة (تسجيل/نشاط بانتظار اعتماد)، وليّ أمر (موافقة اقتصاد/ملخّص)، معلّم (تسليم بانتظار مراجعة)، دعم (تذكرة/ردّ/تصعيد)، تذكير الواجب، LevelUp | **M** | P2, P3 | كل حدث خلف علمه وفئته؛ يُسجَّل ويحترم التفضيلات؛ لا يعلق في `jobs` |
| **P7 — الملخّصات الدوريّة** | أوامر `emails:digest-weekly` (طالب/وليّ)، `emails:digest-admin/school`؛ مجدولة `->weekly()`؛ توسعة `notifications:cleanup` لـ`email_logs` (180 يومًا) | **L** | P6 | يُرسَل للمشتركين (`digest=true`) فقط؛ رسالة واحدة مجمّعة؛ يظهر كفئة `digest` في `email_logs` |

---

## 9. قائمة تحقّق التشغيل والاختبار النهائيّة

### 9.1 أوامر التشغيل خطوة بخطوة
```bash
# 1) اختر المزوّد واملأ .env (Brevo للإطلاق أو SES للحجم) — تأكّد أنّ MAIL_MAILER ليس log
grep MAIL_ .env

# 2) أنشئ جداول الطابور الناقصة ثمّ رحّل
php artisan migrate --force
php artisan tinker --execute="echo Schema::hasTable('failed_jobs') ? 'ok' : 'MISSING';"

# 3) أعِد بناء الكاش بعد تعديل .env (وإلّا config:cache يُبقي القيَم القديمة!)
php artisan config:clear && php artisan config:cache

# 4) شخّص الإعداد والاتصال والطابور بأمر واحد
php artisan mail:test ibrahemelnawsaty@gmail.com

# 5) اختبر مسار الطابور صراحةً (يكشف إن كان العامل يعمل)
php artisan mail:test ibrahemelnawsaty@gmail.com --queue
php artisan queue:work --once            # لو وصلت الآن فالطابور سليم والعامل هو الناقص

# 6) فعّل الطابور بثبات:
#    VPS  → systemd:  systemctl enable --now atheel-queue ; systemctl status atheel-queue
#    مشترك→ cron:     queue:work --stop-when-empty كل دقيقة
#    أو للإطلاق الفوريّ: QUEUE_CONNECTION=sync ثمّ config:cache

# 7) فعّل المجدوِل (لازم للنسخ/التنظيف/التذكيرات/الحملات المجدولة)
crontab -l | grep schedule:run || echo 'أضِف: * * * * * php artisan schedule:run'

# 8) تحقّق من DNS (SPF/DKIM/DMARC منشورة ومتحقَّقة في لوحة المزوّد)
dig TXT atheel-makkah.com +short          # يجب أن يظهر v=spf1
dig TXT _dmarc.atheel-makkah.com +short   # يجب أن يظهر v=DMARC1
dig TXT mail._domainkey.atheel-makkah.com +short  # مفتاح DKIM

# 9) اختبار تسليم حقيقيّ: أرسل لـ mail-tester.com واستهدف 10/10
php artisan mail:test <العنوان-من-mail-tester>

# 10) اختبر الأحداث الحيّة الثلاثة (ترحيب/شارة/تصحيح) — تأكّد أنّها تصل لا تعلق
php artisan queue:monitor database:default --max=50
php artisan queue:failed                   # يجب أن يبقى فارغًا

# 11) فحص القوالب بعد أيّ تعديل (يكشف @php قبل @extends)
php artisan view:clear && php artisan view:cache
find storage/framework/views -name "*.php" -exec php -l {} \;
```

### 9.2 سجلّات DNS المطلوب نشرها والتحقّق منها

| النوع | الاسم | القيمة (استبدل بقيَم مزوّدك) |
|---|---|---|
| SPF (TXT) | `@` | `v=spf1 include:spf.brevo.com include:amazonses.com -all` |
| DKIM (CNAME/TXT) | `mail._domainkey` (أو ما يعطيه المزوّد) | مفتاح المزوّد (SES: 3 CNAME؛ Brevo: TXT) |
| DMARC (TXT) | `_dmarc` | `v=DMARC1; p=quarantine; rua=mailto:dmarc@atheel-makkah.com; fo=1; pct=100` |
| Return-Path | `bounce` (CNAME) | يوفّره المزوّد (محاذاة DMARC + استقبال ارتدادات) |

> تدرّج DMARC: `p=none` أسبوعين (مراقبة `rua`) → `p=quarantine` → `p=reject`.

### 9.3 بوّابة القبول النهائيّة (Done = كل هذه تتحقّق)
- [ ] `MAIL_MAILER=smtp/ses` (ليس `log`) على الإنتاج.
- [ ] `failed_jobs` و`job_batches` موجودان (migrate نُفِّذ).
- [ ] `mail:test` يصل الوارد (لا السبام) بنتيجة mail-tester ≥ 9/10.
- [ ] `mail:test --queue` يصل → يثبت أنّ العامل يُفرّغ الطابور.
- [ ] عامل يعمل بثبات (systemd `active` / cron كل دقيقة / `sync`) **مع** إعادة تشغيل تلقائيّة.
- [ ] SPF + DKIM + DMARC منشورة ومتحقَّقة.
- [ ] الأحداث الثلاثة (`SendWelcome/BadgeEarned/ActivityGraded`) تصل ولا تتراكم في `jobs`.
- [ ] `queue:restart` مُضاف لسكربت النشر.
- [ ] لا روابط `127.0.0.2` أو `sa-salem.com`، وعنوان 2FA = «أثيل مكة».
- [ ] `email_logs` يسجّل كل إرسال بحالته؛ لوحة `/admin/email-logs` تعمل.
- [ ] رابط إلغاء الاشتراك يعمل بلا تسجيل، والحرِج يتجاوزه.

### 9.4 قائمة المهامّ القابلة للتنفيذ (Checklist مرتبط بالمراحل)
- [ ] **P1** ضبط `.env` SMTP + توافق port/encryption + رفع timeout + `mail:test`.
- [ ] **P1** هجرة `failed_jobs`+`job_batches`.
- [ ] **P1** عامل `queue:work` (systemd/cron/sync) + `queue:restart` بالنشر + `schedule:run`.
- [ ] **P1** ترقية `TestMail.php` إلى تشخيص كامل.
- [ ] **P2** `layouts/base.blade.php` + `components/{button,alert,data-table}` + ترحيل 10 قوالب + `php -l`.
- [ ] **P3** هجرة `email_logs` + `EmailLog` + `RecordEmailActivity` + `Queue::failing`.
- [ ] **P3** `MailGate` + تحويل الـ18 موقع إرسال + بطاقة أعلام في `admin/settings` + لوحة `/admin/email-logs`.
- [ ] **P3** (اختياريّ) بكسل الفتح + webhooks الارتداد (SES/Postmark) مع تحقّق التوقيع.
- [ ] **P4** هجرة `user_email_preferences` + `EmailPreference::allows()` + `UnsubscribeController` + `List-Unsubscribe` + شاشة تفضيلات.
- [ ] **P5** هجرات الحملات + `email_opt_out` + `EmailCampaignController` + `AudienceResolver`/`VariableRenderer` + `Bus::batch` + `campaigns:dispatch-due`.
- [ ] **P6** الـ14 Mailable الجديدة خلف أعلام `email_enabled.*`.
- [ ] **P7** أوامر الملخّص + جدولتها + توسعة `notifications:cleanup` لـ`email_logs`.

**ملفّات/جداول/مسارات مرجعيّة:** `settings`(موجود)، `email_logs`/`user_email_preferences`/`email_campaigns`/`email_campaign_recipients`/`email_templates`(جديدة)، `failed_jobs`/`job_batches`(ناقصة — تُنشأ في P1)؛ `resources/views/emails/layouts/base.blade.php`، `app/Services/Mail/MailGate.php`، `app/Models/{EmailLog,EmailPreference,EmailCampaign}.php`، `routes/web.php` (`admin.settings.update` موجود؛ `admin.email-logs`, `admin.email-campaigns.*`, `email.unsubscribe`, `email.track.open` جديدة)، `routes/console.php`، `config/mail.php` (خرائط from/reply-to).

---

> **خلاصة الأولويّات الحرجة قبل أيّ ميزة جديدة:** (1) حسم بيئة الإنتاج الفعليّة؛ (2) إنشاء `failed_jobs`+`job_batches`؛ (3) تشغيل عامل مستقرّ أو `sync` لإنقاذ الـ3 listeners العالقة؛ (4) نقل الإرسال إلى relay مخصّص (Brevo الآن، SES `me-south-1` للتوسّع) مع SPF/DKIM/DMARC. هذه الأربعة أساس كل ما بعدها.