# Wahy Platform — تطبيق إصلاحات ملاحظات العميل (2026-04-14)
# يُشغَّل من جذر المشروع: powershell -ExecutionPolicy Bypass -File deploy-fixes.ps1

$ErrorActionPreference = 'Stop'

Write-Host "=== Wahy Platform: Apply Fixes ===" -ForegroundColor Cyan

# 1. تأكد من وجود php
$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Host "PHP غير موجود في PATH. يُرجى تثبيت PHP أو ضبط المسار أولاً." -ForegroundColor Red
    exit 1
}

# 2. تشغيل الهجرات الجديدة
Write-Host "`n[1/6] تشغيل الهجرات الجديدة..." -ForegroundColor Yellow
& php artisan migrate --force
if ($LASTEXITCODE -ne 0) { Write-Host "فشل تشغيل الهجرات." -ForegroundColor Red; exit 1 }

# 3. تشغيل seeder تحدي PvP
Write-Host "`n[2/6] إنشاء تحدي افتراضي طالب-ضد-طالب..." -ForegroundColor Yellow
& php artisan db:seed --class=PvpChallengesSeeder --force

# 4. مسح الـ caches
Write-Host "`n[3/6] مسح الكاش..." -ForegroundColor Yellow
& php artisan config:clear
& php artisan cache:clear
& php artisan route:clear
& php artisan view:clear

# 5. إنشاء link للـ storage إن لم يوجد (لرفع الصور)
Write-Host "`n[4/6] التأكد من رابط storage..." -ForegroundColor Yellow
if (-not (Test-Path "public\storage")) {
    & php artisan storage:link
}

# 6. تحسين autoload و routes للإنتاج
Write-Host "`n[5/6] تحسينات الإنتاج..." -ForegroundColor Yellow
& composer dump-autoload --optimize 2>$null
& php artisan config:cache 2>$null
& php artisan route:cache 2>$null
& php artisan view:cache 2>$null

# 7. تشخيص نهائي
Write-Host "`n[6/6] فحص التحقق..." -ForegroundColor Yellow
$migCount = (& php artisan migrate:status 2>&1 | Select-String "Ran").Count
Write-Host "   - عدد الهجرات المطبّقة: $migCount" -ForegroundColor Gray

Write-Host "`n=== جاهز ===" -ForegroundColor Green
Write-Host "خطوات التحقق اليدوي:" -ForegroundColor Cyan
Write-Host "  1. سجّل الدخول كأدمن → /admin/schools/{school}/active-values" -ForegroundColor Gray
Write-Host "  2. اختبر تسليم نشاط (اختيار من متعدد) بإجابة خاطئة → يجب أن تظهر 'إجابة غير صحيحة' و 0 نقاط" -ForegroundColor Gray
Write-Host "  3. افحص /admin/activity-bank → يجب أن يفتح بدون 500" -ForegroundColor Gray
Write-Host "  4. افحص /teacher/messages → يجب أن يفتح بدون 500" -ForegroundColor Gray
Write-Host "  5. افحص /student/leaderboard → يجب أن يبدأ الترتيب من #4" -ForegroundColor Gray
Write-Host "  6. افحص زر تبديل الوضع الليلي/النهاري في حساب الطالب" -ForegroundColor Gray
