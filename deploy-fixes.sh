#!/usr/bin/env bash
# Wahy Platform — تطبيق إصلاحات ملاحظات العميل (Linux/Mac/cPanel SSH)
set -euo pipefail

echo "=== Wahy Platform: Apply Fixes ==="

if ! command -v php >/dev/null 2>&1; then
    echo "PHP غير موجود في PATH"
    exit 1
fi

echo
echo "[1/6] تشغيل الهجرات الجديدة..."
php artisan migrate --force

echo
echo "[2/6] إنشاء تحدي افتراضي طالب-ضد-طالب..."
php artisan db:seed --class=PvpChallengesSeeder --force || true

echo
echo "[3/6] مسح الكاش..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo
echo "[4/6] التأكد من رابط storage..."
if [ ! -e public/storage ]; then
    php artisan storage:link
fi

echo
echo "[5/6] تحسينات الإنتاج..."
composer dump-autoload --optimize 2>/dev/null || true
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

echo
echo "[6/6] فحص التحقق..."
php artisan migrate:status | grep -c "Ran" || true

echo
echo "=== جاهز ==="
echo "خطوات التحقق اليدوي:"
echo "  1. /admin/schools/{school}/active-values"
echo "  2. تسليم نشاط بإجابة خاطئة → 'إجابة غير صحيحة' و 0 نقاط"
echo "  3. /admin/activity-bank بدون 500"
echo "  4. /teacher/messages بدون 500"
echo "  5. /student/leaderboard يبدأ من #4"
echo "  6. زر تبديل الوضع في حساب الطالب"
