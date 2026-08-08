# دليل النشر التشغيليّ — منصّة «أثيل مكة»

**الخادم:** Contabo Cloud VPS 10 SSD · **IP:** `84.247.135.69` · **hostname:** `vmi3412537`
**النطاق:** `atheel-makkah.com` · **المنصّة:** Laravel 12 (PHP 8.2+) · **DB:** MySQL

> هذا الدليل تُنفِّذه أنت عبر SSH خطوةً بخطوة. الأوامر لتوزيعة Ubuntu/Debian (الأكثر شيوعاً على Contabo). عدّل مدير الحِزَم إن كانت التوزيعة مختلفة.

---

## 0. قبل البدء — ما تحتاجه جاهزاً
- وصول SSH لجذر الخادم (`ssh root@84.247.135.69`).
- توجيه DNS: سجلّ **A** لـ`atheel-makkah.com` و`www` → `84.247.135.69` (من مزوّد النطاق). انتظر انتشاره.
- بيانات قاعدة بيانات MySQL (ستُنشئها في الخطوة 2).
- **كلمة مرور بريد `info@atheel-makkah.com` الصحيحة** (من مزوّد البريد) — لإصلاح عطل SMTP.
- **قرار APP_KEY:** هل هذا نشرٌ جديد (بلا بيانات سابقة) أم ترحيل قاعدة بيانات من الخادم القديم؟ (يحدّد الخطوة 4).

---

## 1. تجهيز الخادم (مرّة واحدة)
```bash
apt update && apt upgrade -y

# PHP 8.2 + الإضافات المطلوبة لـLaravel
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y && apt update
apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl php8.2-fileinfo

# MySQL + Nginx + أدوات
apt install -y mysql-server nginx git unzip curl certbot python3-certbot-nginx

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Node.js 20 (لبناء الأصول)
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# رفع حدّ رفع الملفّات في PHP (لفيديو الهيرو ووسائط الأنشطة — كان مصدر عطل سابق)
# عدّل /etc/php/8.2/fpm/php.ini:  upload_max_filesize = 500M ; post_max_size = 500M
sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 500M/' /etc/php/8.2/fpm/php.ini
sed -i 's/^post_max_size = .*/post_max_size = 500M/' /etc/php/8.2/fpm/php.ini
systemctl restart php8.2-fpm
```

## 2. قاعدة البيانات
```bash
mysql -e "CREATE DATABASE atheel_makkah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER 'atheel'@'localhost' IDENTIFIED BY '46A6833jSlqYBb95t';"
mysql -e "GRANT ALL PRIVILEGES ON atheel_makkah.* TO 'atheel'@'localhost'; FLUSH PRIVILEGES;"
```

## 3. جلب الكود
```bash
mkdir -p /var/www && cd /var/www
git clone https://github.com/ibrahemelnawsaty-sys/wahy.git atheel
cd /var/www/atheel
```

## 4. إعداد البيئة (.env)
```bash
cp .env.production.example .env
nano .env   # املأ DB_* و MAIL_* والقيم المُعلَّمة بـ«‹...›»
```
**APP_KEY — انتبه:**
- **نشر جديد (بلا بيانات):** `php artisan key:generate`
- **ترحيل قاعدة بيانات قائمة:** لا تولّد مفتاحاً — انسخ `APP_KEY` نفسه حرفيّاً من `.env` القديم (وإلّا تتلف الجلسات/البيانات المشفَّرة، لأنّ `SESSION_ENCRYPT=true`).

## 5. التبعيّات وبناء الأصول
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

## 6. قاعدة البيانات: الترحيل والبذر
**نشر جديد:**
```bash
php artisan migrate --force
php artisan db:seed --force        # ينشئ الأدوار وحساب مدير أوّليّ (راجِع بيانات الدخول ثمّ غيّرها فوراً)
```
**ترحيل بيانات قائمة:** استورد نسخة SQL من الخادم القديم، ثمّ:
```bash
mysql atheel_makkah < old_dump.sql
php artisan migrate --force        # يطبّق الهجرات الجديدة (pb_*، إعادة التسمية، مسح أقسام landing، …)
```
> الهجرات الجديدة تشمل: جداول محرّر الصفحات `pb_*`، هجرة إعادة التسمية إلى «أثيل مكة»، ومسح تخصيصات `landing_content` القديمة.

## 7. التخزين والأصول
```bash
# تأكّد من وجود مجلّدات إطار العمل القابلة للكتابة (قد تنقص بعد clone نظيف → «View path not found»)
mkdir -p storage/framework/{views,cache/data,sessions} storage/logs bootstrap/cache

php artisan storage:link
# ارفع فيديو الهيرو يدويّاً (مُستثنى من git لحجمه):
#   من جهازك:  scp "hero-main.mp4" root@84.247.135.69:/var/www/atheel/public/videos/hero-main.mp4
mkdir -p public/videos
```

## 8. الكاش والتحسين
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```
> بعد أيّ تعديل على `.env` لاحقاً: `php artisan config:clear && php artisan config:cache`.

## 9. الصلاحيات
```bash
chown -R www-data:www-data /var/www/atheel
find /var/www/atheel -type f -exec chmod 644 {} \;
find /var/www/atheel -type d -exec chmod 755 {} \;
chmod -R 775 /var/www/atheel/storage /var/www/atheel/bootstrap/cache
```

## 10. خادم الويب (Nginx)
أنشئ `/etc/nginx/sites-available/atheel`:
```nginx
server {
    listen 80;
    server_name atheel-makkah.com www.atheel-makkah.com;
    root /var/www/atheel/public;          # ⚠️ الجذر public/ لا مجلّد المشروع
    index index.php;
    client_max_body_size 500M;            # يوافق حدّ رفع PHP

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
```
```bash
ln -s /etc/nginx/sites-available/atheel /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
```

## 11. شهادة SSL (HTTPS)
```bash
certbot --nginx -d atheel-makkah.com -d www.atheel-makkah.com --agree-tos -m info@atheel-makkah.com --redirect
# certbot يجدّد تلقائيّاً؛ تحقّق: certbot renew --dry-run
```

## 12. المجدوِل (Cron) — ⚠️ إلزاميّ للنسخ الاحتياطي والسلاسل والإحصاءات
```bash
crontab -e
# أضف السطر التالي (يشغّل مجدوِل Laravel كلّ دقيقة):
* * * * * cd /var/www/atheel && php artisan schedule:run >> /dev/null 2>&1
```
> بدونه لن تعمل: `backup:run` اليوميّ، تنظيف الإشعارات، تحديث إحصاءات المدارس، فحوص المواعيد.

## 13. عامل الطابور (Queue Worker)
بما أنّ `QUEUE_CONNECTION=database`، شغّل عاملاً دائماً عبر systemd — أنشئ `/etc/systemd/system/atheel-queue.service`:
```ini
[Unit]
Description=Atheel Queue Worker
After=network.target mysql.service

[Service]
User=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/atheel/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```
```bash
systemctl daemon-reload && systemctl enable --now atheel-queue
```
> **بديل أبسط للإطلاق السريع:** ضع `QUEUE_CONNECTION=sync` في `.env` (يُرسَل البريد فوراً بلا عامل) — تجنّب هذه الخطوة كليّاً.

## 14. الإعداد بعد النشر (من لوحة الأدمن)
سجّل دخول السوبر أدمن → **الإعدادات العامة** واضبط:
- **البريد/الجوال الرسميّان**، **رقم الواتساب** (يُظهر الزرّ العائم).
- **تفعيل فيديو الهيرو** (بعد رفعه في الخطوة 7).
- **أعلام الإظهار** (إحصائيات/فوائد/شركاء) كما تريد.
ثمّ تحقّق من البريد:
```bash
php artisan mail:test you@example.com    # يجب أن تصل رسالة الاختبار (تحقّق من السبام أيضاً)
```

## 15. قائمة تحقّق ما بعد النشر
- [ ] `https://atheel-makkah.com` يفتح بقفل HTTPS، بلا أخطاء.
- [ ] `APP_DEBUG=false` (لا يُعرَض أثر أخطاء للزوّار).
- [ ] `/terms` و`/privacy` تعملان.
- [ ] التسجيل/تسجيل الدخول يعملان؛ `mail:test` يصل الوارد.
- [ ] فيديو الهيرو يُشغَّل (إن فُعّل).
- [ ] `php artisan backup:run` ينتج ملفّاً صالحاً (اختبار يدويّ أوّل).
- [ ] الـcron وعامل الطابور يعملان (`systemctl status atheel-queue`).
- [ ] راجِع `storage/logs/laravel.log` — خالٍ من الأخطاء الحرجة.

## 16. التحديثات اللاحقة (إعادة نشر)
```bash
cd /var/www/atheel
git pull origin master
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
systemctl restart atheel-queue
```

## 17. حلّ المشكلات السريع
| العرض | الفحص |
|---|---|
| خطأ 500 أبيض | `storage/logs/laravel.log`؛ الصلاحيات (الخطوة 9)؛ `APP_KEY` مضبوط |
| «419 Page Expired» | تحقّق `SESSION_DOMAIN=.atheel-makkah.com` و`APP_URL` بـhttps |
| البريد لا يصل | كلمة `MAIL_PASSWORD` الصحيحة؛ `php artisan mail:test`؛ سجلّات SMTP؛ SPF/DKIM |
| تغيير `.env` لا يظهر | `php artisan config:clear && php artisan config:cache` |
| لا نسخ احتياطيّ | تأكّد من الـcron (الخطوة 12) وبريد التنبيه |
| الوسائط/الفيديو لا يُرفَع | حدّ `upload_max_filesize`/`post_max_size` و`client_max_body_size` |

> راجِع أيضاً: **دليل النسخ الاحتياطي والاستعادة** و**دليل إعداد البريد** في مجلّد `docs/handover/`.
