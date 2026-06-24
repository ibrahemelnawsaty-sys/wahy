# دليل استخدام لوحة التحكم - Super Admin Panel

## نظرة عامة
تم بناء لوحة تحكم شاملة للمدير العام (Super Admin) مع إمكانيات كاملة لتخصيص الموقع وإدارة المحتوى.

## ✅ المميزات المكتملة

### 1. تخصيص الثيم (Theme Customization)
- **الألوان**:
  - اللون الرئيسي (Primary Color)
  - اللون الثانوي (Secondary Color)
  - لون النص (Text Color)
  - لون الخلفية (Background Color)
  
- **الخطوط العربية**:
  - IBM Plex Sans Arabic (الافتراضي)
  - Tajawal ✅
  - Cairo
  - Almarai
  - Noto Sans Arabic

- **الصور**:
  - شعار الموقع (Logo)
  - أيقونة الموقع (Favicon)
  - صورة Hero للصفحة الرئيسية

### 2. بناء الصفحات (Page Builder)
محرر Drag & Drop بـ 8 أنواع من البلوكات:
- Hero Section (قسم البطل)
- Text Block (كتلة نصية)
- Image Block (كتلة صورة)
- Features Grid (شبكة المميزات)
- Call to Action (دعوة لاتخاذ إجراء)
- Statistics (إحصائيات)
- Team Members (أعضاء الفريق)
- Contact Form (نموذج اتصال)

### 3. الإعدادات العامة (General Settings)
- معلومات الموقع (الاسم والوصف)
- معلومات الاتصال (البريد والهاتف)
- روابط وسائل التواصل الاجتماعي
- نص التذييل

### 4. لوحة المعلومات (Dashboard)
إحصائيات حية:
- عدد المستخدمين
- عدد المدارس
- عدد الفصول
- عدد القيم
- عدد الدروس
- عدد الأنشطة

## 🔧 كيفية الاستخدام

### تغيير ألوان الموقع

1. سجل دخول كـ Super Admin
2. اذهب إلى **تخصيص الثيم** من القائمة الجانبية
3. اختر اللون الذي تريد تغييره
4. اضغط على **حفظ التغييرات**
5. قم بتحديث الصفحة (F5) لرؤية التغييرات

**مثال**: تم تغيير اللون الرئيسي من الأخضر `#3CCB8A` إلى البنفسجي `#cc3eb2` ✅

### تغيير الخط

1. اذهب إلى **تخصيص الثيم**
2. اختر الخط من القائمة المنسدلة
3. اضغط على **حفظ التغييرات**
4. الخط سيتم تحميله تلقائياً من Google Fonts

**مثال**: تم تغيير الخط من IBM Plex Sans Arabic إلى Tajawal ✅

### رفع الشعار

1. اذهب إلى **تخصيص الثيم**
2. في قسم "رفع الملفات"
3. اختر ملف الشعار (JPG, PNG, SVG)
4. اضغط **رفع**
5. الشعار سيظهر مباشرة في الـ Sidebar

**مثال**: تم رفع شعار بنجاح ✅

### إنشاء صفحة جديدة

1. اذهب إلى **بناء الصفحات**
2. اضغط **إنشاء صفحة جديدة**
3. اكتب اسم الصفحة والـ Slug
4. اسحب البلوكات من اليسار إلى اليمين
5. املأ محتوى كل بلوك
6. اضغط **حفظ الصفحة**
7. الصفحة ستكون متاحة على: `yoursite.com/pages/{slug}`

## 🗄️ البنية التقنية

### الجداول في قاعدة البيانات

#### جدول `settings`
```sql
- id (bigint)
- key (varchar 255)
- value (text)
- type (varchar 50)
- description (text)
- created_at
- updated_at
```

#### جدول `page_builder`
```sql
- id (bigint)
- page_name (varchar 255)
- slug (varchar 255)
- json_data (json)
- meta_title (varchar 255)
- meta_description (text)
- is_active (boolean)
- created_at
- updated_at
```

### الملفات الرئيسية

#### Models
- `app/Models/Setting.php` - مع نظام Cache
- `app/Models/PageBuilder.php` - مع JSON Casting

#### Controllers
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Admin/ThemeController.php`
- `app/Http/Controllers/Admin/PageBuilderController.php`
- `app/Http/Controllers/Admin/SettingsController.php`

#### Middleware
- `app/Http/Middleware/ApplyTheme.php` - يشارك الإعدادات مع جميع الـ Views

#### Helpers
- `app/Helpers/SettingsHelper.php` - دوال مساعدة:
  - `setting($key, $default)` - جلب إعداد
  - `set_setting($key, $value, $type, $description)` - حفظ إعداد
  - `hexToRgba($hex, $opacity)` - تحويل Hex إلى RGBA
  - `adjustBrightness($hex, $steps)` - تفتيح/تغميق لون

#### Views
- `resources/views/layouts/admin.blade.php` - Layout الإداري
- `resources/views/layouts/auth.blade.php` - Layout صفحات التسجيل
- `resources/views/landing.blade.php` - الصفحة الرئيسية
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/theme.blade.php`
- `resources/views/admin/settings.blade.php`
- `resources/views/admin/pages/index.blade.php`
- `resources/views/admin/pages/create.blade.php`
- `resources/views/admin/pages/edit.blade.php`

#### CSS
- `public/css/admin.css` - 800+ أسطر من CSS مخصص

### Routes
```php
Route::middleware(['auth', 'can:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/theme', [ThemeController::class, 'index'])->name('theme');
    Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');
    Route::post('/theme/upload', [ThemeController::class, 'upload'])->name('theme.upload');
    
    Route::resource('pages', PageBuilderController::class);
    
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
```

## 🎨 كيف يعمل النظام الديناميكي؟

### 1. حفظ الإعدادات
عندما تغير لون أو خط في لوحة التحكم:
```php
Setting::set('primary_color', '#cc3eb2', 'color', 'اللون الرئيسي');
```

### 2. قراءة الإعدادات
في الـ Blade Templates:
```php
@php
    $primaryColor = setting('primary_color', '#3CCB8A');
    $fontFamily = setting('font_family', 'IBM Plex Sans Arabic');
@endphp
```

### 3. تطبيق CSS الديناميكي
```html
<style>
    :root {
        --color-primary: {{ $primaryColor }};
        --color-primary-hover: {{ adjustBrightness($primaryColor, -20) }};
        --color-primary-light: {{ hexToRgba($primaryColor, 0.1) }};
        --font-family-base: '{{ $fontFamily }}', sans-serif;
    }
</style>
```

### 4. استخدام المتغيرات في CSS
```css
.btn-primary {
    background: var(--color-primary);
    font-family: var(--font-family-base);
}

.btn-primary:hover {
    background: var(--color-primary-hover);
}
```

## 🔒 الصلاحيات

### Super Admin
- الإيميل: `ibrahemelnawsaty@gmail.com`
- الصلاحيات: **كل شيء** (Gate::before يعطي تصريح مطلق)

```php
Gate::before(function ($user, $ability) {
    return $user->role === 'super_admin' ? true : null;
});
```

## 🚀 أوامر مفيدة

### مسح الكاش
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### إعادة تحميل Autoload
```bash
composer dump-autoload
```

### التحقق من الإعدادات
```bash
php artisan tinker
>>> Setting::all()
>>> setting('primary_color')
```

### إنشاء Symbolic Link للتخزين
```bash
php artisan storage:link
```

## ✅ ماذا تم اختباره؟

1. ✅ حفظ الألوان في قاعدة البيانات
2. ✅ حفظ الخطوط في قاعدة البيانات
3. ✅ رفع الشعار والأيقونة
4. ✅ قراءة الإعدادات من Helper Functions
5. ✅ تحويل Hex إلى RGBA
6. ✅ تفتيح/تغميق الألوان
7. ✅ تطبيق CSS الديناميكي في Layouts
8. ✅ عرض الشعار المرفوع
9. ✅ تحميل الخطوط من Google Fonts

## 🎯 النتيجة النهائية

الآن عندما تقوم بـ:
- تغيير اللون الرئيسي → يتغير في كل الموقع
- تغيير الخط → يتغير في كل الصفحات
- رفع شعار → يظهر في الـ Header والـ Sidebar
- إنشاء صفحة → تظهر مباشرة على الموقع

**كل شيء يعمل بشكل ديناميكي 100%!** ✨

## 📝 ملاحظات مهمة

1. **Cache**: الإعدادات مخزنة في Cache لمدة ساعة لتحسين الأداء
2. **File Uploads**: الملفات محفوظة في `storage/app/public/theme/`
3. **Symbolic Link**: يجب تشغيل `php artisan storage:link` مرة واحدة
4. **Browser Cache**: قد تحتاج لتحديث الصفحة بـ Ctrl+F5 لرؤية التغييرات

---

**تم التطوير بواسطة**: GitHub Copilot  
**التاريخ**: نوفمبر 2024  
**الحالة**: ✅ مكتمل وجاهز للاستخدام
