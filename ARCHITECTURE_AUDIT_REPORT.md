# تقرير التحليل المعماري الثالث — المشاكل المؤكَّدة (88)


# ===== HIGH (11) =====

## [HIGH/settings-coverage] ألوان/خط الثيم تُطبَّق في 4 لايوتات فقط ويُتجاهَل في الباقي — مع تكرار منطق CSS متباعد
- النطاق: تطبّق: admin, teacher, student-app, auth (+ landing). تتجاهل: super-admin, school-admin, parent, auth-clean, student
- الدليل: Grep لـ font_family|primary_color|setting( داخل مجلد layouts يطابق 4 ملفات فقط بالضبط: admin، teacher، student-app، auth — مؤكِّداً أن الستة الباقية لا تقرأ أي إعداد ثيم. تفصيل: super-admin.blade.php:1-9 بلا أي كتلة @php أو setting()، خط IBM Plex ثابت :7، و#667eea ثابت :54 (Grep: لا توجد أي --color-primary أو var(--color أو setting() في الملف). school-admin.blade.php:23 font 'Cairo' ثابت و:42 تدرّ
- الحل: إنشاء resources/views/partials/theme-head.blade.php يجمع: كتلة @php لقراءة الإعدادات (يفضّل عبر Setting::getMany للأداء كما في auth)، وكتلة <style> :root بالمتغيرات (--color-primary/secondary/text/bg و--font-family عبر hexToRgba/hexToRgb/adjustBrightness الموجودة)، وكتلة @if/@elseif لتحميل خط Google الصحيح. ثم @include('partials.theme-head') في رأس اللايوتات العشرة جميعاً، وتحويل القيم الثابتة (#6

## [HIGH/settings-coverage] الشعار واسم الموقع والهوية مكتوبة يدوياً (hardcoded) في 8 من 10 لايوتات — إعدادات site_name/site_logo تُحفظ ولا تنعكس
- النطاق: layouts/admin.blade.php, super-admin.blade.php, teacher.blade.php, school-admin.blade.php, parent.blade.php, student.blade.php, student-app.blade.php, auth-clean.blade.php مقابل layouts/auth.blade.php و landing.blade.php (الوحيدان اللذان يقرآن site_name/site_logo)
- الدليل: تأكدت المشكلة بالكامل عبر القراءة. الإعداد فعلاً مُحفوظ ومُتجاهَل:

الحفظ (الإعداد مُعرَّف ومُطبَّق فعلياً):
- app/Http/Controllers/Admin/SettingsController.php:54,71 — يحفظ site_name عبر Setting::set('site_name', ...).
- app/Http/Controllers/Admin/ThemeController.php:17,28 — يحفظ/يجلب site_logo.
- resources/views/admin/settings.blade.php:23-24 — حقل إدخال اسم الموقع.

القراءة (المرجع الصحيح، 2 فق
- الحل: إنشاء partial مركزي resources/views/partials/brand.blade.php يقرأ site_name/site_logo من الإعدادات (نمط auth.blade.php:3-21 مع Setting::getMany وقيمة افتراضية موحّدة)، ويعرض الشعار إن وُجد وإلا اسم الموقع، ثم استدعاؤه في رؤوس اللايوتات الثمانية بدل القيم الثابتة. الأفضل معمارياً: View Composer أو middleware يشارك $siteName/$siteLogo لجميع الـ views (View::share) لتجنّب تكرار منطق الجلب. وتوحيد الق

## [HIGH/config-gap] تعارض مساحتي مفاتيح للسوشال: الأدمن يحفظ *_url بينما السيدر وقالب الإيميل يستخدمان social_* — روابط سوشال الإيميل ميتة دائماً
- النطاق: Admin/SettingsController.php + admin/settings.blade.php (يكتبان facebook_url/twitter_url/instagram_url/linkedin_url) مقابل database/seeders/DefaultSettingsSeeder.php + emails/layouts/master.blade.php (يقرآن social_facebook/social_twitter/social_instagram/social_youtube/social_linkedin)
- الدليل: مساحتا مفاتيح غير متوافقتين مؤكدتان بالكود. ما يكتبه/يقرؤه الأدمن (*_url): SettingsController.php:58-61 يحفظ facebook_url/twitter_url/instagram_url/linkedin_url؛ admin/settings.blade.php:93,103,113,123 حقول إدخال لنفس الأربعة فقط (لا youtube، لا social_*). ما يقرؤه الإيميل/الـ landing (social_*): emails/layouts/master.blade.php:461-474 يقرأ social_facebook/social_twitter/social_instagram/social_yo
- الحل: توحيد مساحة مفاتيح واحدة للسوشال كمصدر حقيقة واحد. المسار الأنظف: تبنّي social_* (لأنه الأشمل ويغطّي youtube/whatsapp) وتحديث: (1) SettingsController.php:20-23 وindex + 53-65 update ليقرأ/يكتب social_facebook/social_twitter/social_instagram/social_linkedin/social_youtube/social_whatsapp مع قواعد التحقق المقابلة في 39-42؛ (2) admin/settings.blade.php:93-123 لتغيير name/id الحقول إلى social_* وإضافة

## [HIGH/settings-coverage] نصف اللايوتات لا تقرأ إعدادات الثيم إطلاقاً — تغيير الأدمن للألوان/الخط لا ينعكس فيها
- النطاق: تقرأ الإعدادات: app/admin.blade.php، auth.blade.php، teacher.blade.php، student-app.blade.php (4/10). لا تقرأها مطلقاً: layouts/super-admin.blade.php، layouts/parent.blade.php، layouts/school-admin.blade.php، layouts/auth-clean.blade.php، layouts/student.blade.php، layouts/app.blade.php (6/10)
- الدليل: ThemeController::update يحفظ primary_color/secondary_color/text_color/background_color/font_family/site_theme (ThemeController.php:37-53). 4 لايوتات تقرأها وتبني :root: admin.blade.php:1-9، teacher.blade.php:1-8 و29-35، auth.blade.php:3-22، student-app.blade.php:1-8. بينما 6 لايوتات لا تستدعي setting()/themeSettings/:root إطلاقاً (grep أعاد No matches في الستة جميعاً): super-admin.blade.php:1-9 (ي
- الحل: إنشاء partial مركزي resources/views/partials/theme-vars.blade.php يقرأ المفاتيح الستة عبر Setting::getMany ويُخرج كتلة :root موحّدة (--color-primary/--color-secondary/--color-text/--color-bg/--font-family + اختيار رابط Google Font حسب font_family)، ثم @include('partials.theme-vars') في <head> لكل اللايوتات العشرة. وللأدوار التي تستخدم CSS خارجي (super-admin-glass.css) تحويل قيم :root الثابتة فيه إ

## [HIGH/intent-mismatch] انقسام أسماء مفاتيح السوشيال: social_* (السيدر/الإيميل) مقابل *_url (الإعدادات/الفوتر/landing) — البيانات لا تتقابل
- النطاق: DefaultSettingsSeeder.php + emails/layouts/master.blade.php (social_*) مقابل Admin/SettingsController.php + admin/settings.blade.php + components/footer.blade.php + landing.blade.php (*_url)
- الدليل: مفهوم واحد (روابط السوشيال) مُخزَّن بمجموعتي مفاتيح متباينتين بلا أي طبقة mapping (setting() مجرد غلاف رقيق حول Setting::get بلا aliasing — app/Helpers/SettingsHelper.php:13-16).

مجموعة social_* (الإدخال = السيدر فقط):
- database/seeders/DefaultSettingsSeeder.php:124,130,136,142,148 ينشئ social_facebook/twitter/instagram/linkedin/youtube.
- يقرأها: resources/views/emails/layouts/master.blade.php:
- الحل: توحيد مجموعة مفاتيح واحدة. الأفضل اعتماد social_* لأنها متسقة مع السيدر والإيميل وcontact partial وتدعم youtube وwhatsapp أصلاً:
- app/Http/Controllers/Admin/SettingsController.php: تبديل facebook_url→social_facebook ... في index() (20-23)، update() validation (39-42)، settingsToSave (58-61)، وgetSettingDescription (99-102)؛ وإضافة social_youtube وsocial_whatsapp.
- resources/views/admin/settings.

## [HIGH/duplication] <head> مكرّر يدوياً بـ 10 نسخ متباينة: تحميل الخطوط وFont Awesome وAPI الثيم غير موحّد
- النطاق: كل الـ 10 layouts + landing + landing-dynamic
- الدليل: تأكدت كل الادعاءات الجوهرية بالكود:

(1) قراءة الثيم غير موحّدة:
- admin.blade.php:1-9 و teacher.blade.php:1-8 و student-app.blade.php:1-8 تقرأ عبر setting() الفردية (font_family/primary_color/...).
- auth.blade.php:3-22 تقرأ عبر \App\Models\Setting::getMany (نمط مختلف تماماً).
- app.blade.php:1-26 و auth-clean.blade.php (تحقق Grep: لا setting/getMany/font_family/data-theme إطلاقاً) و super-admin.
- الحل: استخراج تهيئة <head> المشتركة إلى partials مركزية و@include في كل اللايوتات العشرة: (1) partial واحد لجلب الثيم (كتلة @php أو Setting::getMany موحّدة) يُنتج $fontFamily/$primaryColor/...، (2) partial لكتلة <style> :root بمتغيّرات الألوان والخط، (3) partial لروابط الخطوط مع منطق @if($fontFamily) موحّد يحمّل الخط المختار فعلاً (يحلّ ثغرة auth)، (4) رابط Font Awesome واحد بإصدار موحّد (مثلاً 6.5.1) ل

## [HIGH/settings-coverage] إعدادات الثيم (ألوان/خط/سمة) تُقرأ في 3 لايوتات فقط من 10، فبقية اللوحات تتجاهل ما يحفظه الأدمن
- النطاق: تقرأ: admin, teacher, student-app. لا تقرأ: super-admin, school-admin, parent, student, app (وauth يقرأ جزئياً)
- الدليل: ThemeController.php:37-53 يحفظ primary_color/secondary_color/text_color/background_color/font_family عبر Setting::set. القرّاء: admin.blade.php:3-8، teacher.blade.php:3-7، student-app.blade.php:3-7 (+ auth.blade.php:3-19,56-72 قارئ كامل يولّد :root CSS vars). المُتجاهِلون (لا يستدعون setting()/Setting:: إطلاقاً — تأكّد بالـgrep): super-admin.blade.php (0 نتائج للإعداد)، school-admin.blade.php (0)،
- الحل: استخراج كتلة قراءة الثيم وتوليد متغيّرات :root{--color-primary;--color-secondary;--color-text;--color-bg;--font-family} إلى partial واحد (مثل layouts.partials.theme-vars) يُضمَّن في <head> لكل اللايوتات الفعّالة (super-admin, school-admin, parent, app إضافةً إلى الموحَّدة admin/teacher/student-app/auth)، ثم استبدال التدرّجات والألوان الثابتة (#667eea/#764ba2/#f093fb) بمتغيّرات CSS تستند إلى الإعدا

## [HIGH/settings-coverage] إعدادات الثيم (الألوان/الخط/site_theme) تُقرأ في 4 لايوتات فقط من 10
- النطاق: تقرأ: admin.blade.php، auth.blade.php، teacher.blade.php، student-app.blade.php. لا تقرأ: app.blade.php، auth-clean.blade.php، parent.blade.php، school-admin.blade.php، super-admin.blade.php، student.blade.php
- الدليل: ThemeController يحفظ primary_color/secondary_color/text_color/background_color/font_family/site_theme/layout_style (app/Http/Controllers/Admin/ThemeController.php:37-60). تقرأها فعلياً 4 لايوتات فقط: admin.blade.php:3-8، auth.blade.php:3-19، teacher.blade.php:3-7، student-app.blade.php:3-7. والـ6 الباقية لا تستدعي setting()/Setting:: إطلاقاً وتُثبّت القيم يدوياً: app.blade.php:18 (background:linea
- الحل: إنشاء partial واحد resources/views/partials/theme-vars.blade.php يقرأ إعدادات الثيم (يُفضَّل عبر Setting::getMany دفعة واحدة كما في auth) ويُصدِر كتلة <style>:root{...}</style> بأسماء متغيرات CSS موحّدة (مثلاً --color-primary/-hover/-light، --color-secondary، --color-text، --color-bg، --color-primary-rgb، --font-family)، ثم @include له في كل اللايوتات العشرة قبل بقية الـ<style>، وإزالة الألوان/الخ

## [HIGH/duplication] أربعة أنظمة Theme-Toggle مختلفة ومتعارضة عبر اللايوتات بدل نظام موحّد
- النطاق: partials/theme-toggle.blade.php (مفتاح wahy-theme): admin, parent, school-admin, teacher. js/theme.min.js: auth, auth-clean. نظام inline wahy-theme مكرّر: student-app. نظام inline admin-theme (localStorage مختلف): admin.blade.php:685-700 + super-admin.blade.php:249-264. app/student: لا شيء
- الدليل: تأكَّد التعارض داخل نفس الصفحة في admin.blade.php: السطر 82 يضمّن partials.theme-toggle (الذي يقرأ ويكتب مفتاح 'wahy-theme' في theme-toggle.blade.php:104 و:135 ويملك زراً عائماً wahyThemeFab)، بينما السكربت inline في admin.blade.php:691 و:699 يقرأ/يكتب مفتاح 'admin-theme' لزرٍ آخر sidebarThemeToggle. كلاهما يضبط data-theme على نفس عنصر <html>؛ سكربت FOUC الخاص بالـ partial يعمل في <head> ثم يطغى ع
- الحل: توحيد نظام واحد: اعتماد partials/theme-toggle.blade.php كمصدر وحيد بمفتاح localStorage موحّد واحد. إزالة سكربت 'admin-theme' المتوازي من admin.blade.php:685-700 وتضمين الـ partial فقط (وربط زر sidebarThemeToggle بنفس منطق الـ partial أو إزالته)، وإزالة سكربت 'admin-theme' المكرّر من super-admin.blade.php:249-264 وتضمين الـ partial، وتحويل نظام student-app.blade.php:164-173/631-652 لاستخدام الـ par

## [HIGH/intent-mismatch] قراءة إعدادات الثيم (الألوان/الخط) من DB مطبَّقة في 5 لايوتات فقط من 10
- النطاق: تقرأ DB: admin.blade.php:3-8, auth.blade.php:3-22, teacher.blade.php:3-7, student-app.blade.php:3-7, (partial). لا تقرأ: super-admin.blade.php, parent.blade.php (ألوان hardcoded #667eea/#764ba2), school-admin.blade.php (Cairo + #667eea ثابتة), app.blade.php, auth-clean.blade.php, student.blade.php
- الدليل: الأدمن يحفظ primary_color/secondary_color/text_color/background_color/font_family/site_theme فعلياً عبر app/Http/Controllers/Admin/ThemeController.php:39-53 (validate ثم Setting::set لكل مفتاح) من نموذج admin/theme.blade.php. هذه الإعدادات تُقرأ وتُطبَّق كمتغيرات :root فقط في: layouts/admin.blade.php:1-9,42-51 + layouts/teacher.blade.php:1-8,29-35 + layouts/student-app.blade.php:1-8 + layouts/auth
- الحل: استخراج كتلة @php setting()/Setting::getMany + :root الديناميكية إلى partials/theme-vars.blade.php واحدة و@include في كل اللايوتات العشرة (مع توحيد defaults المتباينة: admin يستخدم #667eea/#764ba2 بينما auth/ThemeController يستخدمان #3CCB8A/#3B82F6). ثم استبدال الألوان/الخطوط الـ hardcoded: في super-admin.blade.php (إضافة قراءة setting() واستبدال #667eea بـ var(--color-primary))، parent.blade.php:

## [HIGH/intent-mismatch] انفصام كامل في مفاتيح روابط التواصل: ثلاثة أنظمة تسمية متعارضة لا تتقاطع (الأدمن يحفظ مفاتيح لا يقرؤها أحد)
- النطاق: app/Http/Controllers/Admin/SettingsController.php · resources/views/admin/settings.blade.php · resources/views/components/footer.blade.php · resources/views/landing.blade.php · resources/views/landing/partials/contact.blade.php · resources/views/emails/layouts/master.blade.php · database/seeders/Def
- الدليل: مخططان متعارضان مؤكَّدان بالكود: (الكاتب/القارئ المتوافق) SettingsController.php:20-23,39-42,54-61,99-102 يحفظ ويقرأ facebook_url/twitter_url/instagram_url/linkedin_url، وadmin/settings.blade.php:93,103,113,123 حقول name=*_url، ويقرأها footer.blade.php:5,22-25 وlanding.blade.php:4,15-18,32-35 — فهذه السلسلة تعمل. (القارئ المعزول) landing/partials/contact.blade.php:88-91 يقرأ setting('social_twitte
- الحل: التوحيد على مخطط social_* لأنه الأشمل (يغطي youtube/whatsapp). عملياً: (1) في SettingsController.php تغيير المفاتيح في index/validator/settingsToSave/getSettingDescription من *_url إلى social_* وإضافة social_youtube وsocial_whatsapp؛ (2) في admin/settings.blade.php تغيير name/id/value الحقول لـ social_* وإضافة حقلَي youtube وwhatsapp وتحديث urlInputs في سكربت التحقق؛ (3) في footer.blade.php وlandi


# ===== MEDIUM (57) =====

## [MEDIUM/settings-coverage] اسم الموقع/الشعار (site_name + site_logo) مُتجاهَلان في 6 من 10 لايوتات — مكتوبان يدوياً
- النطاق: resources/views/layouts/admin.blade.php, super-admin.blade.php, school-admin.blade.php, teacher.blade.php, parent.blade.php, student-app.blade.php, student.blade.php (مقابل auth.blade.php الذي يقرأهما)
- الدليل: Grep على resources/views/layouts أثبت أن auth.blade.php هو اللايوت الوحيد الذي يقرأ site_name/site_logo (الأسطر 4,11-12,20-21,90-94 مع <img> + fallback أيقونة). بقية اللايوتات لا تذكر المفتاحين إطلاقاً. والشعار/الاسم ثابت يدوياً: super-admin.blade.php:16-17 (🌟 + "قيمّ")، admin.blade.php:95-96 (🌟 + "قيمّ") رغم قراءته الألوان من الإعدادات في الأسطر 3-8 من نفس الملف، school-admin.blade.php:646 ("بناء
- الحل: إنشاء partial موحّد resources/views/partials/brand.blade.php يقرأ site_name وsite_logo مرة واحدة (عبر setting() أو Setting::getMany) مع منطق @if($siteLogo) <img> @else أيقونة + اسم @endif كما في auth.blade.php:90-94، ثم استبداله بالنصوص الثابتة في الـ4 لوحات الحيّة فقط: super-admin.blade.php:16-17، admin.blade.php:95-96، school-admin.blade.php:646، teacher.blade.php:276. الأفضل دفع $siteName/$site

## [MEDIUM/settings-coverage] أيقونة الموقع (site_favicon) مُعرَّفة ويُمكن رفعها لكن لا تُعرَض في أي <head> إطلاقاً (orphaned)
- النطاق: كل اللايوتات العشرة + resources/views/landing.blade.php (لا أحد يخرج <link rel="icon">)
- الدليل: بحث regex دقيق عن `<link[^>]*rel=["'](shortcut )?icon["']` عبر كامل شجرة resources/views أعاد "No matches found" — أي لا يوجد أي رابط favicon في أيٍّ من اللايوتات العشرة ولا في landing.blade.php. المفتاح يُقرأ ويُخزَّن فعلاً: ThemeController.php:17,29 (قراءة) و93-105,105 (رفع/حذف/حفظ Setting::set("site_favicon")), والمفتاح مُعرَّف في DefaultSettingsSeeder.php:90-93. الإشارة الوحيدة لـ site_favicon
- الحل: الحل الجذري: إنشاء partial رأس مشترك (مثلاً resources/views/partials/head-meta.blade.php) يُضمَّن في كل اللايوتات العشرة وlanding، ويحتوي سطر الفافيكون الموحّد: @if(setting('site_favicon')) &lt;link rel="icon" href="{{ asset('storage/app/public/data/'.setting('site_favicon')) }}"&gt; @endif — مع احتياطي ثابت &lt;link rel="icon" href="{{ asset('favicon.ico') }}"&gt; عند غياب الإعداد. المسار في الحل

## [MEDIUM/intent-mismatch] انقسام ثلاثي لمفاتيح روابط التواصل: social_* مقابل *_url — الأدمن يحفظ مفاتيح لا يقرأها نصف الواجهة
- النطاق: app/Http/Controllers/Admin/SettingsController.php مقابل DefaultSettingsSeeder.php وemails/layouts/master.blade.php وlanding/partials/contact.blade.php مقابل landing.blade.php وcomponents/footer.blade.php
- الدليل: تأكدت كل الأدلة بالكود. الأدمن يحفظ namespace مختلف عن نصف الواجهة، ولا يوجد أي aliasing يوفّق بينهما (دالة setting() تمريرية بحتة):

(1) الأدمن يحفظ *_url فقط: app/Http/Controllers/Admin/SettingsController.php:54-65 يحفظ facebook_url/twitter_url/instagram_url/linkedin_url (4 فقط). والنموذج resources/views/admin/settings.blade.php:93,103,113,123 فيه هذه الحقول الأربعة فقط (لا youtube ولا whatsapp)
- الحل: التوحيد على نطاق واحد (social_* وفق seeder) عبر طبقة مركزية واحدة:
1) تعديل SettingsController::update وindex وadmin/settings.blade.php لتسمية الحقول social_facebook..social_linkedin، وإضافة حقلَي social_youtube وsocial_whatsapp ليُحفظا (سد فجوة القابلية).
2) تحويل قراءات *_url في landing.blade.php:32-35 وcomponents/footer.blade.php:5-32 إلى social_* لتطابق seeder والإيميلات وcontact partial.
3) إ

## [MEDIUM/settings-coverage] footer_text مُعرَّف ويُحفظ لكن لا يُقرأ في أي فوتر — نص الحقوق مكتوب يدوياً
- النطاق: resources/views/components/footer.blade.php, resources/views/landing/partials/footer.blade.php (مقابل SettingsController الذي يحفظه)
- الدليل: جوهر النتيجة صحيح: SettingsController يحفظ footer_text مع افتراض ذكي بالسنة (app/Http/Controllers/Admin/SettingsController.php:24 الافتراض، :43 التحقق، :62 الحفظ)، وصفحة الإعدادات تعرض حقلاً له (resources/views/admin/settings.blade.php:41)، لكن grep على footer_text وعلى setting('footer'/setting(\"footer داخل resources/views أعاد صفر قراءات — لا يقرأ أي فوتر هذا الإعداد إطلاقاً. كل أسطر الحقوق في ا
- الحل: في الفوتر الموحّد الحيّ components/footer.blade.php:187 استبدال السطر الثابت بقراءة الإعداد مع افتراض ذكي يحافظ على السلوك الحالي: {{ setting('footer_text', '© '.date('Y').' '.$siteName.'. جميع الحقوق محفوظة') }}. وبما أنّ نفس الفوتر يُستخدم في auth وauth-clean فسيُوحَّد الأثر تلقائياً. أمّا landing/partials/footer.blade.php و welcome.blade.php فالأنسب حذفهما/أرشفتهما كأصول يتيمة بدل إصلاح سنتهما 

## [MEDIUM/settings-coverage] contact_email/contact_phone/العنوان مُعرَّفة لكن مُتجاهَلة في الفوتر والقسم العام — قيم اتصال مكتوبة يدوياً ومتضاربة
- النطاق: resources/views/landing/partials/footer.blade.php, resources/views/landing.blade.php (قسم contact), مقابل contact_email/contact_phone/contact_address المُعرَّفة
- الدليل: resources/views/landing.blade.php:30-31 يجلب $contactEmail/$contactPhone من الإعدادات، لكن قسم التواصل في نفس الملف يكتب قيماً ثابتة: السطر 683 (mailto:support@qiyamm.sa) والسطر 691 (tel:+966500000000) بدل استخدام تلك المتغيرات. الصفحة حيّة (web.php:50 الجذر / → PagesController::landing → return view('landing') عند غياب صفحة PageBuilder بـ slug=home). أمّا contact_address فمُعرَّف في database/seed
- الحل: في قسم التواصل بـ resources/views/landing.blade.php استبدل القيم الثابتة بالمتغيرات المتاحة فعلاً في النطاق: السطر 683 → href="mailto:{{ $contactEmail }}" والنص {{ $contactEmail }}، والسطر 691 → href="tel:{{ $contactPhone }}" والنص {{ $contactPhone }}، مع وضع قيمة احتياطية موحّدة عند الفراغ (?? 'info@qiyamm.sa'). وحّد مفتاح العنوان (اعتمد contact_address) ثم: (1) أضف حقل إدخال له في resources/view

## [MEDIUM/settings-coverage] site_theme (light/dark/custom) يُحفظ لكن يُطبَّق فعلياً في admin فقط؛ بقية اللوحات تكتب data-theme ثابتاً أو تتجاهله
- النطاق: admin.blade.php (يقرأ)، super-admin.blade.php (data-theme="dark" ثابت)، landing.blade.php (يأخذه من localStorage لا من الإعداد)، بقية اللايوتات لا data-theme
- الدليل: resources/views/layouts/admin.blade.php:8 يقرأ $siteTheme = setting('site_theme','dark') و:11 يضعه في <html data-theme="{{ $siteTheme }}">، لكن سكربت السطر 691-692 يستبدله فوراً بـ localStorage.getItem('admin-theme') || 'dark'. resources/views/layouts/super-admin.blade.php:2 يثبّت data-theme="dark" والسطر 255 يستبدله بـ localStorage('admin-theme') || 'dark'. resources/views/landing.blade.php:46 يس
- الحل: توحيد مصدر الثيم: تمرير setting('site_theme') من مكان مركزي (مثلاً View Composer أو متغيّر مشترك) إلى data-theme في كل اللايوتات بدل القيم الثابتة (super-admin) أو الغياب (app/teacher/parent/school-admin). في سكربتات تهيئة الثيم (admin:691، super-admin:255، landing:46، student-app:169) استخدام قيمة site_theme المحفوظة كقيمة ابتدائية عند غياب اختيار المستخدم في localStorage، بدل الثابت 'dark'/'ligh

## [MEDIUM/duplication] تكرار وتشظٍّ في طبقة قراءة الإعدادات: ثلاث آليات مختلفة لنفس الغرض
- النطاق: landing.blade.php, layouts/auth.blade.php, components/footer.blade.php (Setting::getMany مع قوائم افتراضات مكررة) مقابل بقية اللايوتات (setting() مفردة)
- الدليل: التكرار مؤكَّد بالكود: (1) resources/views/landing.blade.php:3-20 تقرأ 14 مفتاحاً عبر Setting::getMany بقائمة افتراضات مضمّنة. (2) resources/views/layouts/auth.blade.php:3-14 نسخة getMany ثانية بـ7 مفاتيح وقائمة افتراضات منفصلة. (3) resources/views/components/footer.blade.php:4-16 نسخة getMany ثالثة (مشروطة) بـ8 مفاتيح وافتراضات منفصلة. بقية اللايوتات تستخدم setting() مفردة: admin.blade.php:3-8 و 
- الحل: إنشاء مصدر حقيقة واحد للثيم/الهوية: خدمة مثل SiteSettings::theme() (أو ثابت مركزي THEME_DEFAULTS) تُرجع مصفوفة موحّدة لكل مفاتيح الثيم/الهوية/السوشيال بقيم افتراضية واحدة، وحقنها في كل اللايوتات عبر View::composer عام (مثلاً على 'layouts.*' و 'landing' و components.footer) بدل تكرار getMany وقوائم الافتراضات في الـBlade والكونترولرات. هذا يوحّد primary_color وأخواته ويزيل التضارب بين '#3CCB8A' و'#

## [MEDIUM/duplication] تكرار مفهوم الفوتر: فوتر عام نسختان (components/footer مقابل landing/partials/footer) مع منطق سوشيال متباين، أحدهما مرشّح للموت
- النطاق: resources/views/components/footer.blade.php (المُستخدَم فعلاً) مقابل resources/views/landing/partials/footer.blade.php
- الدليل: يوجد فوترّان عامّان متوازيان فعلاً ومتباينان في المنطق:
1) الحيّ: resources/views/components/footer.blade.php — مُضمَّن في 3 مواضع حيّة: resources/views/landing.blade.php:830، resources/views/layouts/auth.blade.php:128، resources/views/layouts/auth-clean.blade.php:67. يقرأ الإعدادات عبر Setting::getMany (سطر 4)، يطبّع الروابط بدالة $normalizeUrl (سطر 36-47)، ويرسم السوشيال بكتل @if منفصلة بأيقونات
- الحل: حذف صفحة الهبوط البديلة اليتيمة welcome.blade.php وكامل شجرتها landing/partials/* (بما فيها footer.blade.php) بعد التأكد من عدم وجود أي توجيه لها (مؤكَّد هنا). إبقاء components/footer.blade.php كمصدر فوتر وحيد يقرأ الإعدادات مركزياً. إن كان هناك رغبة في الإبقاء على تصميم landing/partials/footer البديل، يجب ربطه بمسار حيّ وتحويل بياناته المكتوبة يدوياً (info@qiyam.edu.sa، +966 50 123 4567، © 2025) 

## [MEDIUM/dead-code] welcome.blade.php + landing/partials/{header,footer}.blade.php كود ميت/يتيم ومُكرِّر للهوية بقيم ثابتة
- النطاق: resources/views/welcome.blade.php + landing/partials/header.blade.php + landing/partials/footer.blade.php (غير مستخدمة) مقابل المسار الفعلي PagesController@landing → pages.show أو landing.blade.php
- الدليل: routes/web.php:50 يربط '/' بـ PagesController@landing فقط؛ وPagesController.php:21-31 يعرض view('pages.show') أو view('landing') — لا يعرض 'welcome' إطلاقاً. grep لكلمة welcome عبر كامل الكود لا يجد أي view('welcome') أو route/controller — كل النتائج إما emails.welcome-student أو SendWelcomeNotification listener أو أصناف CSS أو vendor. والملف الوحيد الذي يضمّن landing.partials.header/footer هو res
- الحل: حذف resources/views/welcome.blade.php وكامل مجلد resources/views/landing/partials/ (header, footer, hero, about, values, features, how-it-works, statistics, contact) لأنها غير مستخدمة فعلاً ولا يصلها أي مسار، مع تصحيح سطري TESTING-REPORT.md:61,72 لينسبا السلوك الديناميكي لمصدره الحقيقي components/footer.blade.php و landing.blade.php بدل landing/partials/footer. بديل آمن إن أُريد الإبقاء على شيء: إ

## [MEDIUM/config-gap] لا يوجد <link rel=icon> في أي لايوت — إعداد site_favicon يُرفع ويُحفظ لكن بلا أثر
- النطاق: كل layouts/*.blade.php + landing.blade.php (لا أحد يصدر favicon) مقابل ThemeController.upload + DefaultSettingsSeeder (يعرّفان/يحفظان site_favicon)
- الدليل: تأكيد كامل عبر الكود:

1) الكتابة/التعريف موجودة فعلاً:
- app/Http/Controllers/Admin/ThemeController.php:104-105 يحفظ site_favicon عند type=favicon عبر Setting::set("site_favicon", $path).
- ThemeController.php:69 يقبل type=favicon في الـ validation، و:93-97 يحذف القديم.
- database/seeders/DefaultSettingsSeeder.php:89-94 يعرّف المفتاح site_favicon (value=null, "أيقونة الموقع").
- resources/views/a
- الحل: إنشاء partial مشترك للـ <head> (مثلاً resources/views/partials/head-meta.blade.php) يُصدر:
<link rel="icon" href="{{ setting('site_favicon') ? asset('storage/app/public/data/'.setting('site_favicon')) : asset('favicon.ico') }}">
مع fallback لأيقونة ثابتة، وتضمينه عبر @include في كل اللايوتات العشرة و landing.blade.php بدل تكرار <head> يدوياً. هذا يصلح فجوة التخصيص ويوحّد الـ <head> المكرّر في آن و

## [MEDIUM/settings-coverage] footer_text يُحفظ ويُحقّق لكن لا يُعرض في أي فوتر — نص الفوتر ثابت بدلاً منه
- النطاق: Admin/SettingsController.php + admin/settings.blade.php (يحفظان/يعرضان footer_text) مقابل components/footer.blade.php + landing/partials/footer.blade.php (يكتبان حقوق النشر يدوياً)
- الدليل: بحث عبر كامل الكود عن footer_text يُرجِع فقط: SettingsController.php (24 افتراضي، 43 تحقّق، 62 حفظ، 103 وصف) + admin/settings.blade.php (40-41 حقل الإدخال، 859 تحقّق JS). لا يوجد ولا استدعاء واحد لـ setting('footer_text') أو $settings['footer_text'] لأغراض العرض في أي view/component. الفوتر الحي components/footer.blade.php:186-188 يكتب يدوياً: &copy; {{ date('Y') }} {{ $siteName }}. جميع الحقوق مح
- الحل: في components/footer.blade.php:187 اجعل سطر الحقوق يقرأ {{ setting('footer_text', '© ' . date('Y') . ' ' . $siteName . '. جميع الحقوق محفوظة') }} مع الإبقاء على fallback للنص الحالي. ولتوحيد المفهوم: استبدال السطور الثابتة في pages/landing-dynamic.blade.php:437 و emails/layouts/master.blade.php:479 (التي تبني الحقوق يدوياً) بنفس قراءة setting('footer_text'). وحذف القالب اليتيم landing/partials/foo

## [MEDIUM/duplication] بنية <head> + تحميل الخطوط + CSS متغيرات الثيم مكرّرة ومتباينة عبر اللايوتات بلا مصدر موحّد
- النطاق: كل layouts/*.blade.php (تكرار <head>، @font-face/Google Fonts، :root color vars، meta) — admin/teacher/student-app/auth ... إلخ
- الدليل: تأكدت كل أدلة الإبلاغ بالكود حرفياً، والتباين أوسع مما ذُكر (5 تطبيقات متمايزة لمنطق الثيم في الـ&lt;head&gt;):
- admin.blade.php:1-9 يقرأ الإعدادات، 27-35 تحميل خط شرطي @if، 42-51 :root كامل عبر adjustBrightness()/hexToRgba().
- teacher.blade.php:18-26 نفس الخط الشرطي، 29-35 نسخة :root مبسّطة (بلا primary-hover/light، بلا data-theme).
- student-app.blade.php:1-8 يقرأ الإعدادات، 20-31 خط شرطي + فر
- الحل: استخراج partial موحّد resources/views/partials/head-theme.blade.php يجلب الإعدادات دفعة واحدة عبر Setting::getMany([...]) بافتراضيات موحّدة (حسم لون primary واحد بدل #667eea/#3CCB8A)، ويُصدِر: (1) كتلة تحميل الخط الموحّدة (نفس @if/@elseif مع فرع @else الاحتياطي الموجود في student-app)، (2) :root موحّد يولّد كل المتغيّرات عبر adjustBrightness/hexToRgba/hexToRgb مرة واحدة. ثم @include('partials.head

## [MEDIUM/duplication] ست قيم افتراضية مختلفة للون الأساسي عبر الملفات — لا مصدر حقيقة واحد
- النطاق: app/admin.blade.php، teacher.blade.php، student-app.blade.php، auth.blade.php، ThemeController.php، ApplyTheme.php، footer/SettingsController
- الدليل: resources/views/layouts/admin.blade.php:4 و teacher.blade.php:4 و student-app.blade.php:4 = setting('primary_color', '#667eea'); بينما auth.blade.php:7 و landing.blade.php:7 و pages/landing-dynamic.blade.php:7 و pages/show.blade.php:693 و app/Http/Controllers/Admin/ThemeController.php:23 و app/Http/Middleware/ApplyTheme.php:35,44 و SuperAdminController.php:937 و Admin/LandingPageController.php:18 
- الحل: إنشاء مصدر حقيقة واحد للافتراضيات: ثابت Setting::THEME_DEFAULTS (أو config/theme.php) يحوي primary_color/secondary_color/text_color/background_color/font_family، واستهلاكه في كل اللايوتات والكنترولرات والميدلوير بدل تكرار سلاسل HEX. توحيد القيمة على #3CCB8A (المستخدمة في ThemeController و ApplyTheme و صفحات الهبوط) وتصحيح defaultColors في resources/views/admin/theme.blade.php:412 (و زر الريست السط

## [MEDIUM/config-gap] site_theme (light/dark) لا يُطبَّق فعلياً — يُكتب data-theme ثم يدهسه JavaScript فوراً من localStorage
- النطاق: admin.blade.php، super-admin.blade.php، student-app.blade.php، partials/theme-toggle.blade.php مقابل بقية اللايوتات
- الدليل: ThemeController يحفظ site_theme (app/Http/Controllers/Admin/ThemeController.php:40 validate 'in:light,dark,custom' و:49-51 Setting::set). لكن القيمة لا تنعكس دائمياً:

(1) admin.blade.php:8 $siteTheme = setting('site_theme','dark') ثم :11 <html data-theme="{{ $siteTheme }}">. بعدها سكربتان يدهسانه: السكربت المُضمَّن عبر @include('partials.theme-toggle') عند :82 (الذي يقرأ wahy-theme في partials/th
- الحل: توحيد مفتاح تخزين واحد (wahy-theme) في كل اللايوتات وحذف منطق admin-theme المكرّر من admin.blade.php:685-700 و super-admin.blade.php:249-264. مركزة تهيئة الثيم في partials/theme-toggle.blade.php وتضمينه في العشرة لايوتات بدل السكربتات الـ inline المتكررة، مع إزالة زر sidebarThemeToggle المكرر في admin أو ربطه بنفس منطق wahyThemeFab. جعل الافتراضي عند غياب القيمة المخزّنة = قيمة الخادم: تمرير setti

## [MEDIUM/consistency] الوضع الليلي مدعوم فعلياً للطالب فقط؛ بقية الأدوار لديها زر تبديل لكن بلا أنماط dark حقيقية أو بلا زر
- النطاق: student-app (دعم كامل) مقابل admin/teacher/parent/school-admin (دعم جزئي عبر theme-toggle) مقابل super-admin/auth/auth-clean/app/student (بلا دعم)
- الدليل: جوهر النتيجة (عدم اتّساق الوضع الليلي) حقيقي ومؤكَّد، لكن أدلة النتيجة الأصلية فيها أخطاء جوهرية في تحديد مَن يدعم ومَن لا يدعم:

مؤكَّد كما ورد:
- student-app.blade.php:75-97 يعرّف منظومة dark كاملة عبر --color-* ومفتاح localStorage 'wahy-theme'... (انظر السطر 626 للزر).
- partials/theme-toggle.blade.php:16-72 يستخدم منظومة ثالثة --w-* ومفتاح 'wahy-theme'، لكن محدِّدات dark ضيّقة فقط على .admin-c
- الحل: توحيد طبقة الوضع الليلي في مصدر واحد: (1) توحيد مفتاح localStorage عبر كل اللايوتات (مفتاح واحد بدل wahy-theme/admin-theme/theme) كي ينتقل تفضيل المستخدم بين الأدوار. (2) نقل تعريف متغيرات light/dark إلى ملف CSS مشترك واحد بأسماء متغيرات موحّدة بدل ثلاث منظومات (--color-*/--w-*/--bg-*). (3) توسيع محدّدات [data-theme=dark] لتشمل المكوّنات المخصّصة غير المغطّاة حالياً، خصوصاً .parent-header (parent.

## [MEDIUM/duplication] كتلة :root وروابط خطوط Google متطابقة منسوخة في 4 لايوتات بدل partial واحد
- النطاق: admin.blade.php، teacher.blade.php، student-app.blade.php، auth.blade.php
- الدليل: تأكّد التكرار والانحراف بالكود: كتلة :root{--color-primary...--font-family} inline مكتوبة يدوياً في 4 لايوتات — admin.blade.php:42-51، teacher.blade.php:29-35، student-app.blade.php:40-46، auth.blade.php:56-72 (grep لـ ':root {' أعاد هذه الأربعة بالضبط). وبلوك @if($fontFamily === 'IBM Plex Sans Arabic') ... لاختيار رابط Google Font منسوخ حرفياً في 3 منها — admin:27-35، teacher:18-26، student-app:2
- الحل: استخراج partial واحد resources/views/partials/theme-head.blade.php يحوي: (1) @php لتحميل إعدادات الثيم batch، (2) بلوك @if اختيار رابط الخط مع @else احتياطي IBM Plex، (3) كتلة :root موحّدة تشمل كل المتغيّرات (--color-primary + -hover عبر adjustBrightness + -light عبر hexToRgba + -rgb عبر hexToRgb + secondary/text/bg/font)، ثم @include('partials.theme-head') في اللايوتات الأربعة. هذا يلغي الانحراف 

## [MEDIUM/config-gap] footer_text يُحفظ في الإعدادات لكن لا يُقرأ أبداً — الفوتر يبني نص الحقوق يدوياً
- النطاق: Admin/SettingsController.php و admin/settings.blade.php مقابل components/footer.blade.php
- الدليل: grep على كامل المستودع لـ footer_text يُظهر أنه يظهر فقط في: app/Http/Controllers/Admin/SettingsController.php (index:24، validate:43، save:62، description:103) وفي resources/views/admin/settings.blade.php (الحقل:40-41 + تحقق JS:859). لا يوجد أي setting('footer_text') ولا $settings['footer_text'] في أي قالب يُعرَض. بالمقابل النص الفعلي للحقوق مثبّت يدوياً في resources/views/components/footer.blade
- الحل: في resources/views/components/footer.blade.php عند .copyright (سطر 186-188) اقرأ القيمة المحفوظة مع fallback للنص الحالي، مثل: {{ setting('footer_text') ?: '© '.date('Y').' '.$siteName.'. جميع الحقوق محفوظة' }}. ويُفضَّل توحيد بقية نسخ الفوتر المكرّرة (landing-dynamic.blade.php:437، landing/partials/footer.blade.php:115، emails/layouts/master.blade.php:479) لتقرأ نفس المصدر أو مكوّن مشترك. بديل أب

## [MEDIUM/intent-mismatch] عدم تطابق أسماء مفاتيح السوشيال: الموثّق social_* مقابل المُطبَّق *_url
- النطاق: وثيقة المفاتيح المعرّفة (social_facebook…) مقابل SettingsController/footer/seeder الفعلية (facebook_url…)
- الدليل: يوجد مخططان متوازيان منفصلان لروابط التواصل: (أ) مخطط *_url هو الوحيد القابل للتحرير من الأدمن — حقول الإدخال في resources\views\admin\settings.blade.php:93,103,113,123 (facebook_url/twitter_url/instagram_url/linkedin_url فقط)، يُتحقّق ويُحفظ في app\Http\Controllers\Admin\SettingsController.php:39-42 و58-61، ويُعرض في resources\views\components\footer.blade.php:5,22-25. (ب) مخطط social_* مزروع ومُ
- الحل: توحيد روابط التواصل في مخطط واحد عبر كامل المكدس. الخيار الأنظف: اعتماد مخطط social_* (لأنه الأشمل ومستهلَك في أكثر من مكان) وإضافة حقول إدخال له في admin\settings.blade.php مع التحقق والحفظ المقابل في SettingsController (index+update+getSettingDescription)، ثم تحويل footer.blade.php لقراءة social_* بدل *_url، وإضافة social_whatsapp إلى السيدر والحقول، وإضافة facebook إلى contact.blade.php لتوحيد 

## [MEDIUM/settings-coverage] site_logo/site_name مثبّتان في 7 لايوتات؛ يُقرآن فقط في auth + footer
- النطاق: auth.blade.php و footer (يقرآن) مقابل admin/teacher/parent/school-admin/super-admin/student/app/student-app (شعار/اسم مثبّت)
- الدليل: الأدمن يحفظ site_name فعلاً عبر SettingsController.php:54 ('site_name' => $request->site_name، يُحفظ بـ Setting::set في السطر 71) ويُعرض في صفحة الإعدادات admin/settings.blade.php:24. هذا الإعداد ينعكس فقط في موضعين عامّين: (1) auth.blade.php:90-95 يعرض الشعار/الاسم من القاعدة، و(2) components/footer.blade.php:59-60,187 يعرض $siteName. أمّا لايوتات لوحات التحكم فتثبّت اسمها/شعارها يدوياً ولا تقرأ 
- الحل: عرض site_name/site_logo من مصدر موحّد في رؤوس كل لايوتات لوحات التحكم بدل النصوص/الإيموجي المثبّتة. الأنظف: إنشاء partial علامة مشترك (مثل resources/views/partials/brand-logo.blade.php) يقرأ من themeSettings المُشارَك أصلاً عبر ApplyTheme (أو من دالة setting())، ثم تضمينه في admin.blade.php:94-97، super-admin.blade.php:15-18، teacher.blade.php:274-277، school-admin.blade.php:640-648، parent.blade.

## [MEDIUM/architecture] ملفات CSS لكل دور تُثبّت ألوان الهوية بدل استهلاك متغيرات الثيم
- النطاق: public/css/super-admin-glass.css، teacher-glass.css، student-glass.css، admin.css، auth-glass.css، school-admin/parent inline
- الدليل: teacher.blade.php:29-35 يبني :root { --color-primary: {{ $primaryColor }} ... } من الإعدادات، لكن public/css/teacher-glass.css:6-7 يُعرّف توكناً مستقلاً --teacher-primary:#667eea / --teacher-secondary:#764ba2 ويستخدمه في أغلب الواجهة (الأسطر 148,260,346,423,505,578,580,665,724,815) — وهذا التوكن لا تلمسه الإعدادات أبداً، فيبقى لون المعلم #667eea مهما تغيّر primary_color. كذلك teacher.blade.php:131
- الحل: توحيد طبقة الثيم: (1) حقن :root موحّدة من الإعدادات عبر partial/مكوّن مشترك واحد يُدرَج في وسم <head> لكل اللايوتات العشرة (بدل التكرار في 3 فقط) تُعرّف --color-primary/--color-secondary/--color-text/--color-bg من setting(). (2) جعل ملفات public/css حيادية اللون: استبدال كل الحرفيات اللونية (#667eea, #764ba2, #10B981, #3B82F6 ...) و التوكنات الخاصة (--teacher-primary) بـ var(--color-primary)/var(-

## [MEDIUM/settings-coverage] favicon/manifest/apple-touch-icon غائبة تماماً من كل الـ 10 layouts (موجودة فقط في landing.blade.php المنفصل)
- النطاق: كل layouts: admin, app, auth, auth-clean, parent, school-admin, student, student-app, super-admin, teacher — مقابل resources/views/landing.blade.php وحدها
- الدليل: Grep لـ rel="icon"/manifest/apple-touch داخل resources/views/layouts = "No matches found". قراءة رؤوس الـ10 layouts تؤكّد الغياب: admin.blade.php:12-39، student-app.blade.php:11-37 (فيه theme-color فقط لا favicon/manifest)، super-admin.blade.php:3-9، app.blade.php:3-9، teacher.blade.php:11-15، parent.blade.php:3-8، auth.blade.php:3-8، auth-clean.blade.php:3-8، student.blade.php:3-6، school-admin.b
- الحل: إنشاء partial موحّد resources/views/partials/head-meta.blade.php يحوي charset/viewport/csrf + <link rel="icon" href="{{ setting('site_favicon') ? asset('storage/app/public/data/'.setting('site_favicon')) : asset('favicon.ico') }}"> + <link rel="manifest" href="{{ asset('manifest.json') }}"> + apple-touch-icon + theme-color، و@include له في الـ10 layouts وفي landing. شرط لازم: توفير ملفات الأيقونات

## [MEDIUM/config-gap] إعداد site_favicon يُرفَع ويُخزَّن لكنه لا يُرسَم كأيقونة في أي مكان
- النطاق: admin/theme.blade.php (الرفع) + SuperAdminController.php:941 + Admin/LandingPageController.php:22 (قراءة للمعاينة فقط) مقابل كل الـ layouts
- الدليل: grep لـ "site_favicon" عبر المشروع كامل يُرجِع فقط: database/seeders/DefaultSettingsSeeder.php:90 (تعريف المفتاح)، app/Http/Controllers/Admin/ThemeController.php:17,29 (الرفع/التجميع)، app/Http/Controllers/SuperAdminController.php:941 و Admin/LandingPageController.php:22 (تحميل بيانات للمحرّر)، و resources/views/admin/theme.blade.php:175-176 (معاينة الرفع فقط). grep لـ rel="icon"/favicon/apple-tou
- الحل: إنشاء head partial مركزي مشترك (resources/views/partials/head-meta.blade.php) وتضمينه في الـ <head> لكل اللايوتات العشرة، يحوي: @if(setting('site_favicon')) <link rel="icon" href="{{ asset('storage/data/'.setting('site_favicon')) }}"> @else <link rel="icon" href="{{ asset('favicon.ico') }}"> @endif — مع ملاحظة استخدام المسار العام storage/data/ (عبر public/storage symlink) لا storage/app/public/da

## [MEDIUM/dead-code] أيقونات PWA المرجعية مفقودة/يتيمة: manifest يشير إلى .svg و landing يشير إلى .png لكن مجلد public/images/icon-* فارغ
- النطاق: public/manifest.json + resources/views/landing.blade.php مقابل public/images/
- الدليل: public/manifest.json:14,20,26 يعرّف ثلاث أيقونات بمسارات /images/icon-72x72.svg و /images/icon-192x192.svg و /images/icon-512x512.svg (type image/svg+xml). بينما resources/views/landing.blade.php:71 يعرّف apple-touch-icon بمسار images/icon-192x192.png (امتداد png مختلف). والبحث عبر الشجرة كلها (**/icon-72x72.*, **/icon-192x192.*, **/icon-512x512.*) أعاد "No files found"، وسرد public/images/ يُظهر 
- الحل: توحيد الامتداد بين manifest.json و apple-touch-icon (اختيار png للتوافق الأوسع مع iOS أو الإبقاء على svg في الاثنين) وإضافة ملفات الأيقونات الفعلية icon-72x72 / icon-192x192 / icon-512x512 إلى public/images/، أو توليدها تلقائياً من site_favicon/site_logo المرفوع عبر الإعدادات. ويُفضَّل أيضاً نقل روابط manifest و apple-touch-icon إلى partial مشترك في <head> تستهلكه كل اللايوتات بدل اقتصارها على lan

## [MEDIUM/settings-coverage] og:* و twitter:* و canonical غائبة عن كل اللايوتات وأغلب الصفحات (SEO فقط في landing)
- النطاق: landing.blade.php (لديها og/twitter) مقابل كل اللايوتات وصفحات pages/show
- الدليل: وسوم Open Graph/Twitter موجودة حصرياً في resources/views/landing.blade.php:56-65. بحث شامل في كامل resources/views أرجع تطابقات og:/twitter: في هذا الملف فقط، ولا canonical في أي ملف إطلاقاً. اللايوت resources/views/layouts/auth.blade.php:25-80 (الذي تمتدّ منه pages/show عبر @extends('layouts.auth')) يحتوي head فيه فقط: charset/viewport/description(@yield)/csrf/title(@yield) — بلا og/twitter/canon
- الحل: إنشاء head partial مركزي (مثل resources/views/partials/seo-meta.blade.php) يُضمَّن في كل اللايوتات، يصدّر og:title/og:description/og:image/og:url + twitter:* + <link rel="canonical"> بقيم افتراضية مشتقّة من إعدادات الموقع (site_name/site_description/site_logo) وعنوان الصفحة الحالي (url()->current() للـ canonical/og:url بدل url('/') الثابت)، مع جعل القيم قابلة للتجاوز عبر @yield/@section كي تضبط ال

## [MEDIUM/duplication] حساب المستوى (level) مكرّر بـ 6+ صيغ مختلفة عبر الكود، وبعضها يعطي نتائج مختلفة
- النطاق: app/Services/GamificationService.php، app/Http/Controllers/StudentController.php، ParentDashboardController.php، TeacherController.php، resources/views/teacher/student-reports.blade.php، resources/views/layouts/student-app.blade.php
- الدليل: التكرار مؤكَّد: صيغة floor(xp/100)+1 منسوخة يدوياً في 8 مواضع، ولا يوجد مصدر موحّد (grep لم يجد getLevelAttribute ولا levelFor؛ User.php:1-630 لا يحتوي accessor للمستوى): GamificationService.php:24,36,127 | StudentController.php:656 | ParentDashboardController.php:291 | TeacherController.php:346 | teacher/student-reports.blade.php:88 | layouts/student-app.blade.php:350 (الصيغة الصحيحة floor(.../10
- الحل: إضافة مصدر موحّد واحد: إمّا accessor getLevelAttribute() على App\Models\User يحسب floor($this->totalPoints()/100)+1، أو دالة ساكنة GamificationService::levelFor(int $xp): int. ثم استبدال جميع المواضع الثمانية لتستدعيه: GamificationService.php:24,36,127، StudentController.php:656، ParentDashboardController.php:291، TeacherController.php:346، teacher/student-reports.blade.php:88، و student-app.blade

## [MEDIUM/intent-mismatch] ‎$student->level مستخدم لكن لا يوجد accessor له على نموذج User (مستوى يُعرض فارغاً)
- النطاق: app/Http/Controllers/TeacherController.php:841، app/Models/User.php
- الدليل: app/Http/Controllers/TeacherController.php:841 يبني 'level' => $student->level ويُمرَّر $stats للقالب reports/student-progress.blade.php:171 الذي يطبع {{ $stats['level'] }} تحت عنوان "المستوى". لكن app/Models/User.php (قُرئ كاملاً 1-630) لا يحتوي getLevelAttribute ولا 'level' في $fillable (71-88) ولا في casts() (106-116)، وبحث grep عن "level" في الموديل بلا تطابق. ومهاجرة المستخدمين 2025_11_18_134
- الحل: تعريف accessor موحّد على User: public function getLevelAttribute(): int { return (int) floor($this->totalPoints() / 100) + 1; } — يصلح السطر 841 فوراً ويوحّد الحساب. والأفضل معمارياً تمرير الحساب عبر GamificationService::getStudentStats() (current_level) في كل المواضع الستة لإزالة التكرار من الجذر بدل تكراره يدوياً.

## [MEDIUM/duplication] إحصاءات الطالب محسوبة في 3 مسارات مستقلة بقيم/مفاتيح غير متطابقة
- النطاق: app/Services/GamificationService.php::getStudentStats، app/Http/Controllers/StudentController.php::getStudentStats، app/Providers/AppServiceProvider.php (View composer)، app/Livewire/Student/QuickStats.php
- الدليل: app/Services/GamificationService.php:123-145 يعيد total_xp/current_level/xp_for_next_level/progress_percentage/badges_count — لكنه orphan: لا يُستدعى من أي مكان (grep getStudentStats: التعريف فقط في السطر 123 دون أي ->getStudentStats() على instance من الخدمة). | app/Http/Controllers/StudentController.php:300-365 دالة private منفصلة تعيد total_points (لا current_level ولا progress_percentage) + tot
- الحل: اعتماد GamificationService::getStudentStats مصدراً وحيداً (أو accessors/methods على نموذج User: totalXp/currentLevel/progressPercentage/badgesCount)، واستدعاؤه من StudentController::getStudentStats وMessagesController::getStudentStats وAppServiceProvider composer وLivewire QuickStats وParentDashboardController وTeacherController، مع استخراج عتبة المستوى floor($xp/100)+1 إلى دالة/ثابت واحد (مثل lev

## [MEDIUM/settings-coverage] اسم الموقع/الشعار مكتوبان يدوياً (hardcoded) في 7 لايوتات بينما site_name/site_logo إعدادات يحفظها الأدمن
- النطاق: resources/views/layouts/{admin,super-admin,school-admin,teacher,parent,student,student-app}.blade.php مقابل layouts/auth.blade.php و auth-clean (والأخيرة أيضاً hardcoded)
- الدليل: auth.blade.php هو اللايوت الوحيد الذي يقرأ الإعداد ويعرضه ديناميكياً: السطر 4 يجلب site_logo/site_name عبر Setting::getMany، والسطور 90-94 تعرض <img src=site_logo> أو {{ $siteName }}. بالمقابل تكتب باقي اللايوتات العلامة نصاً ثابتاً: admin.blade.php:96 <span class="admin-logo-text">قيمّ</span> | super-admin.blade.php:17 قيمّ و:219 'إدارة كاملة لمنصة بناء القيم' | school-admin.blade.php:646 <div cl
- الحل: إنشاء View composer واحد (أو توسعة HeaderDataComposer وتسجيله على ['layouts.*'] في AppServiceProvider.php بدل [layouts.admin, layouts.super-admin] فقط) يشارك $siteName = setting('site_name','قيمّ') و$siteLogo = setting('site_logo'). ثم استبدال النصوص الثابتة: admin.blade.php:96 و super-admin.blade.php:17 و school-admin.blade.php:646 و auth-clean.blade.php:37 و student-app/teacher/parent بكتلة شرطي

## [MEDIUM/consistency] تضارب القيم الافتراضية لنفس مفاتيح الثيم بين ThemeController واللايوتات
- النطاق: app/Http/Controllers/Admin/ThemeController.php مقابل layouts/{admin,teacher,student-app}.blade.php
- الدليل: المفتاح نفسه له افتراضان مختلفان حسب الموضع، مؤكَّد بالكود حرفياً:
- ThemeController::index() — app/Http/Controllers/Admin/ThemeController.php:22-25: site_theme='light'، primary_color='#3CCB8A'، secondary_color='#3B82F6'، text_color='#334155'.
- اللايوتات (نفس fallback عبر الثلاثة) — admin.blade.php:4-8، teacher.blade.php:4-7، student-app.blade.php:4-7: primary_color='#667eea'، secondary_color='#7
- الحل: توحيد الافتراضات في مصدر وحيد، إمّا config/theme.php (مثل ['site_theme'=>'light','primary_color'=>'#3CCB8A',...]) أو ثوابت/مصفوفة ثابتة على نموذج Setting، ثم استهلاكها في: (أ) ThemeController::index() بدل القيم المكتوبة في :22-31، (ب) الـ @php في رؤوس admin/teacher/student-app.blade.php بدل القيم في :3-8، (ج) كلا السيدرَين — ويُفضَّل حذف أحدهما لإزالة الازدواج وتسجيل الباقي في DatabaseSeeder حتى ت

## [MEDIUM/config-gap] footer_text إعداد يُحفظ من لوحة الأدمن لكنه لا يُقرأ في أي مكان (خيار بلا أثر)
- النطاق: app/Http/Controllers/Admin/SettingsController.php مقابل resources/views/components/footer.blade.php
- الدليل: SettingsController persists/validates/describes footer_text: app/Http/Controllers/Admin/SettingsController.php:24 (default), :43 (validation 'nullable|string|max:500'), :62 (save $request->footer_text), :103 (description 'نص الفوتر'). Admin actively edits it via a required input in resources/views/admin/settings.blade.php:40-42 (label 'نص الفوتر', help 'النص الذي سيظهر في أسفل الموقع'). Yet a proj
- الحل: In resources/views/components/footer.blade.php:187 replace the programmatic line with the saved setting, falling back to the computed default: &copy; {!! setting('footer_text') ?: '&copy; '.date('Y').' '.e($siteName).'. جميع الحقوق محفوظة' !!} (or render footer_text directly since it already includes the © symbol per its placeholder/default). Apply the same read to resources/views/landing/partials

## [MEDIUM/config-gap] مفاتيح إعدادات معرّفة في صفحة الثيم لكن بلا أثر فعلي: site_favicon، hero_background، layout_style
- النطاق: app/Http/Controllers/Admin/ThemeController.php و admin/theme.blade.php مقابل كل اللايوتات
- الدليل: ThemeController.php:18,29-31,46 يحمّل/يتحقّق/يحفظ المفاتيح الثلاثة، وtheme.blade.php:12 يثبّت layout_style="wide" في input مخفي. تأكيد عبر grep شامل على resources/ وapp/: (1) layout_style لا يُقرأ في أي من اللايوتات العشرة — يظهر فقط في ThemeController وtheme.blade.php والـseeders. (2) hero_background لا يُطبّق كخلفية في أي مكان؛ hero في landing.blade.php:275 مُنسَّق بـCSS فقط، والمفتاح يظهر حصراً
- الحل: إمّا التفعيل: إضافة <link rel="icon" href="{{ asset('storage/app/public/data/'.setting('site_favicon')) }}"> داخل partial موحّد للـ<head> يُضمَّن في كل اللايوتات (مع fallback للأيقونة الثابتة)، وتطبيق hero_background كـbackground-image على قسم hero في landing.blade.php (وربطه بـthemeSettings)، وقراءة layout_style لضبط حاوية الصفحة (وإزالة التثبيت hidden value="wide" في theme.blade.php:12 واستبداله

## [MEDIUM/intent-mismatch] روابط التواصل الاجتماعي: مفاتيح صفحة الإعدادات (social_*) لا تطابق مفاتيح القراءة (facebook_url)، وقناتان متوازيتان للحفظ
- النطاق: وصف صفحة الإعدادات (social_facebook/social_twitter/...) مقابل SettingsController + footer.blade.php (facebook_url/twitter_url/...)
- الدليل: قناة الكتابة (الأدمن) موحَّدة داخلياً على *_url: admin/settings.blade.php:93,103,113,123 (name="facebook_url"/"twitter_url"/"instagram_url"/"linkedin_url") = SettingsController.php:39-42,58-61 = footer.blade.php:5,22-25,44-47. لكن قناة القراءة الأخرى تستخدم social_*: DefaultSettingsSeeder.php:124-152 يُعرِّف social_facebook/social_twitter/social_instagram/social_linkedin/social_youtube؛ emails/lay
- الحل: توحيد مساحة أسماء واحدة لكل روابط التواصل عبر النظام بأكمله. الخيار الأنظف: اعتماد social_* كقياسي (لأنه المُعرَّف في DefaultSettingsSeeder)، ثم: (1) تغيير name= في admin/settings.blade.php و$request->* في SettingsController.php وقراءات footer.blade.php من *_url إلى social_*؛ (2) إضافة حقلَي social_youtube وsocial_whatsapp إلى صفحة الإعدادات والكنترولر (validation+save) حتى تصبح قابلة للضبط بدل بق

## [MEDIUM/intent-mismatch] تضارب أسماء مفاتيح روابط التواصل: social_* (Seeder) مقابل *_url (الإعدادات/الفوتر) — مفاتيح وهمية مزدوجة
- النطاق: database/seeders/DefaultSettingsSeeder.php (social_facebook/social_twitter/social_instagram/social_linkedin/social_youtube) مقابل app/Http/Controllers/Admin/SettingsController.php + resources/views/admin/settings.blade.php + resources/views/components/footer.blade.php + resources/views/landing.blade
- الدليل: عائلتا مفاتيح متوازيتان لا تتقاطعان: (1) الأدمن يكتب/يقرأ *_url حصراً: app/Http/Controllers/Admin/SettingsController.php:20-23 (قراءة) و58-61 (كتابة) و39-42 (تحقق)، والحقول في resources/views/admin/settings.blade.php:93,103,113,123 هي facebook_url/twitter_url/instagram_url/linkedin_url فقط (لا يوجد youtube/whatsapp — grep داخل الملف أعاد No matches). (2) الفوتر resources/views/components/footer.bl
- الحل: توحيد عائلة مفاتيح واحدة لكل شبكة. الأفضل اعتماد social_* لأنها الأشمل وتغطي youtube/whatsapp المقروءَين فعلاً: (1) تعديل app/Http/Controllers/Admin/SettingsController.php لقراءة/كتابة/تحقق social_facebook/social_twitter/social_instagram/social_linkedin/social_youtube/social_whatsapp بدل *_url. (2) إضافة حقول youtube وwhatsapp في resources/views/admin/settings.blade.php وإعادة تسمية الحقول الأربعة

## [MEDIUM/config-gap] footer_text يُحفظ من الأدمن لكنه غير مُستخدم في أي فوتر — نص حقوق النشر hardcoded
- النطاق: resources/views/components/footer.blade.php:186-188 (hardcoded date('Y') + siteName) مقابل app/Http/Controllers/Admin/SettingsController.php:62 + resources/views/admin/settings.blade.php:40-42 (حقل footer_text إلزامي)
- الدليل: grep على كامل المشروع لـ "footer_text" يُرجع 7 مطابقات فقط، جميعها داخل دورة الإعدادات نفسها ولا شيء منها في أي قالب فوتر/لايوت: app/Http/Controllers/Admin/SettingsController.php:24 (إعادة تعبئة النموذج)، :43 (تحقق)، :62 (الحفظ)، :103 (الوصف)؛ resources/views/admin/settings.blade.php:40-42 (الحقل، help="النص الذي سيظهر في أسفل الموقع")، :859 (قائمة required في JS). في المقابل الفوتر الفعلي يبني ال
- الحل: في resources/views/components/footer.blade.php:187 استبدال السطر اليدوي بقراءة الإعداد: {{ setting('footer_text', '© '.date('Y').' '.$siteName.'. جميع الحقوق محفوظة') }} (أو تمرير $footerText من اللايوت كما تُمرَّر باقي متغيرات الفوتر). وكذلك توحيد فوتر الهبوط resources/views/landing/partials/footer.blade.php:115 لقراءة نفس الإعداد بدل النص الثابت "© 2025 بناء القيم..." (ويزيل في الوقت نفسه السنة 

## [MEDIUM/config-gap] site_favicon و site_tagline و meta_title/meta_description لا تُطبَّق في أي layout — لا favicon ولا meta من الإعدادات
- النطاق: كل resources/views/layouts/*.blade.php (لا أحد يقرأ site_favicon/site_tagline/meta_*) مقابل ThemeController (يرفع favicon) + SuperAdminController.php:936,941 + LandingPageController.php:17,22 (يقرآنها لصفحة هبوط فقط)
- الدليل: تأكيد بالكود عبر العشرة layouts كلها (resources/views/layouts/*.blade.php): لا يوجد أي <link rel="icon"> / favicon في أي layout — الوحيد هو معاينة في admin/theme.blade.php:175-186. ولا يقرأ أي layout setting('site_favicon')/setting('meta_title')/setting('meta_description')/setting('site_tagline'). الـ <title> دائماً @yield('title', '...') ثابت لا يقرأ meta_title. الـ meta description موجود فقط في 
- الحل: إنشاء partial مشترك واحد في <head> (مثل resources/views/partials/head-meta.blade.php) يصدر: <link rel="icon"> من setting('site_favicon') عند وجوده، و<title> يستخدم setting('meta_title')، و<meta name="description"> من setting('meta_description') (أو site_tagline احتياطياً)، ثم تضمينه عبر @include في كل الـ 10 layouts بدل التكرار الثابت في auth.blade.php:28 وauth-clean.blade.php:6 وبدل غياب favicon/

## [MEDIUM/config-gap] site_theme (light/dark/custom) يُطبَّق في admin فقط؛ بقية اللايوتات تتجاهله وتعتمد localStorage/قيمة ثابتة
- النطاق: admin.blade.php:8,11 (data-theme={{$siteTheme}}) مقابل super-admin.blade.php:2 (data-theme="dark" ثابت) + student-app.blade.php:170 (data-theme من localStorage) + teacher/student-app (تقرأ الألوان فقط لا site_theme)
- الدليل: site_theme يُحفظ ويُتحقق منه (in:light,dark,custom) في app/Http/Controllers/Admin/ThemeController.php:40,51 لكنه فعلياً غير مطبّق: المكان الوحيد الذي يقرؤه هو resources/views/layouts/admin.blade.php:8,11 (data-theme="{{ $siteTheme }}")، وحتى هناك تُلغيه JS فوراً عند التحميل في admin.blade.php:691-692 (localStorage.getItem('admin-theme') || 'dark'). بقية اللايوتات تتجاهله: super-admin.blade.php:2 ي
- الحل: قراءة site_theme مركزياً في partial ثيم مشترك وضبطه كقيمة ابتدائية على <html data-theme> في كل اللايوتات، مع تعريف قواعد CSS لكل من [data-theme="light"|"dark"|"custom"] (أو ربط custom بمتغيّرات الألوان المحفوظة). وقبل ذلك إصلاح الجذر: استبدال الحقل المخفي الثابت في resources/views/admin/theme.blade.php:11 بمنتقي حقيقي (light/dark/custom)، وتوحيد منطق JS وتفضيلات المستخدم بحيث لا يتجاوز localStorag

## [MEDIUM/dead-code] feature flags: enable_registration و enable_2fa مزروعة لكن لا تُقرأ في أي منطق — خياران وهميان
- النطاق: database/seeders/DefaultSettingsSeeder.php:154-166 فقط (لا قارئ في أي Controller/Middleware/Route/View)
- الدليل: database/seeders/DefaultSettingsSeeder.php:156 يعرّف 'enable_registration' و:162 يعرّف 'enable_2fa' (boolean). بحث شامل عبر app/ وroutes/ وresources/views/ يُرجع صفر مطابقة لأي قارئ: لا setting('enable_registration')/setting('enable_2fa') ولا Setting:: lookup. التسجيل في AuthController::register (app/Http/Controllers/AuthController.php:311-362) ومسارات register في routes/web.php:71-89 تعمل دائماً 
- الحل: أحد خيارين: (1) ربط المفاتيح بمنطق فعلي — حماية مسارات register في routes/web.php:71-89 بـ middleware/abort_unless مشروط بـ setting('enable_registration')، وجعل فرض/إتاحة 2FA يستشير setting('enable_2fa')، مع إضافة toggle لكليهما في صفحة الإعدادات (admin/settings.blade.php وSettingsController). (2) أو حذف القيدين من DefaultSettingsSeeder.php:154-166 إن لم يكونا مقصودين، لتفادي خيارين وهميين. ملاحظة

## [MEDIUM/duplication] Seeders للإعدادات مكرّران ومتناقضان وغير مسجَّلين في DatabaseSeeder — قيم افتراضية لا تُزرع
- النطاق: database/seeders/DefaultSettingsSeeder.php و database/seeders/SettingsSeeder.php مقابل database/seeders/DatabaseSeeder.php:18-24
- الدليل: database/seeders/DatabaseSeeder.php:18-24 يستدعي فقط [UsersSeeder, ValuesSeeder, ConceptsSeeder, LessonsSeeder, BadgesSeeder] — لا DefaultSettingsSeeder ولا SettingsSeeder. وجود Seeder-ين متناقضين: primary_color = #667eea في DefaultSettingsSeeder.php:33 مقابل #3CCB8A في SettingsSeeder.php:26؛ contact_phone '+966 50 000 0000' (DefaultSettingsSeeder.php:111) مقابل '0112345678' (SettingsSeeder.php:81
- الحل: دمج الـSeeder-ين في Seeder واحد للإعدادات وتسجيله في DatabaseSeeder::run() ضمن call([...]). وأهم من ذلك: جعل القيم الافتراضية مصدراً واحداً (config/settings.php أو ثوابت في Setting model) يقرأ منه كل من الـSeeder ودالة setting() حتى لا تتكرر الافتراضات يدوياً عبر اللايوتات/الكنترولرز بقيم متضاربة (#667eea مقابل #3CCB8A). ثم حذف الـSeeder المهجور بعد الدمج.

## [MEDIUM/dead-code] صفحة الهبوط welcome.blade.php + كامل مجلد landing/partials يتيمة — لا مسار يستدعيها
- النطاق: resources/views/welcome.blade.php و 9 ملفات في resources/views/landing/partials/ (header, hero, about, values, features, how-it-works, statistics, contact, footer) مقابل resources/views/landing.blade.php الفعلية
- الدليل: المسار الحي للهبوط: routes/web.php:50 (Route::get('/', [PagesController::class, 'landing'])) → PagesController.php:30 يعيد view('landing') (ملف landing.blade.php الضخم 1235 سطراً) أو pages.show. لا يوجد أي مسار/تحكم يعيد view('welcome'): البحث عن view('welcome') عبر كل *.php لم يعطِ أي نتيجة (المطابقة الوحيدة لـ view هي PagesController.php:30 → view('landing')). welcome.blade.php (resources/views/
- الحل: حسم النية بإزالة الازدواجية: بما أن المعتمَد فعلياً هو landing.blade.php (هو ما يصله المسار /)، الخيار الأبسط هو حذف الكود اليتيم: resources/views/welcome.blade.php + كامل resources/views/landing/partials/ (9 ملفات). أما إن كانت النية اعتماد البنية المجزّأة، فيجب أولاً جعلها قابلة للإعداد فعلاً (header/footer يقرآن setting('site_name'/'site_logo'/'footer_text'/'contact_email'/'contact_phone'/'addr

## [MEDIUM/intent-mismatch] تضارب مفاتيح وسائل التواصل: contact partial وقالب البريد يقرآن social_* التي لا يكتبها أحد
- النطاق: resources/views/landing/partials/contact.blade.php و resources/views/emails/layouts/master.blade.php مقابل app/Http/Controllers/Admin/SettingsController.php
- الدليل: منظومتا مفاتيح متوازيتان لنفس المفهوم:
1) ما يحفظه الأدمن فعلاً = *_url: app/Http/Controllers/Admin/SettingsController.php:54-62 يكتب facebook_url/twitter_url/instagram_url/linkedin_url، وحقول الإدخال في resources/views/admin/settings.blade.php:93-123 هي *_url حصراً (لا يوجد أي حقل social_*). هذه المفاتيح تُستهلَك في الفوتر (resources/views/components/footer.blade.php:5,22-25) وصفحة الهبوط (resour
- الحل: توحيد فضاء أسماء واحد للمفاتيح. الأبسط: تعديل القالبين contact.blade.php و emails/layouts/master.blade.php لقراءة *_url (facebook_url/twitter_url/instagram_url/linkedin_url) — وهي ما يحفظه الأدمن فعلاً ويستهلكه الفوتر وصفحة الهبوط — بدل social_*. أو عكسياً: إضافة حقول social_* في admin/settings.blade.php وتعديل SettingsController.php ليحفظها، مع إزالة *_url. كذلك معالجة social_youtube و social_wha

## [MEDIUM/config-gap] مفتاح footer_text يُحفَظ من لوحة الأدمن لكن لا يقرأه أي قالب فوتر
- النطاق: app/Http/Controllers/Admin/SettingsController.php + resources/views/admin/settings.blade.php مقابل resources/views/components/footer.blade.php
- الدليل: SettingsController.php:24/43/62/103 يقرأ ويتحقق ويحفظ ويصف footer_text؛ admin/settings.blade.php:40-41 حقل إدخال required + JS validation عند :859. لكن grep على footer_text عبر المشروع بأكمله يُرجِع نتائج في هذين الملفين فقط — صفر في أي قالب فوتر أو layout. وبالفعل components/footer.blade.php:187 يبني سطر الحقوق يدوياً (&copy; {{ date('Y') }} {{ $siteName }}. جميع الحقوق محفوظة) ولا يقرأ footer_te
- الحل: إغلاق الفجوة بأحد مسارين: (أ) عرض القيمة المحفوظة في القالب الموحّد عبر استبدال السطر اليدوي في components/footer.blade.php:187 بـ {{ setting('footer_text', '© ' . date('Y') . ' ' . $siteName . '. جميع الحقوق محفوظة') }} (مع إضافة footer_text إلى مفاتيح Setting::getMany)، وتطبيق نفس الشيء على landing/partials/footer.blade.php:115 لتوحيد المصدر وإزالة السنة/الاسم الثابتين؛ أو (ب) إن لم يكن النص قاب

## [MEDIUM/dead-code] قوالب Page Builder متعددة يتيمة: create/edit/create-simple/create-visual/edit-simple/edit-visual/create-pro-backup/builder-component
- النطاق: resources/views/admin/pages/ (create.blade.php, edit.blade.php, create-simple, create-visual, edit-simple, edit-visual, create-pro-backup, builder-component) مقابل PageBuilderController.php
- الدليل: PageBuilderController.php لا يعرض سوى 4 قوالب: index (سطر 20)، create-pro (سطر 25)، edit-pro (سطر 80)، وpages.show للمعاينة (سطر 201). كل مسارات الباني في routes/web.php:190-197 تشير لدوال الكنترولر فقط؛ المساران المسمّيان admin.pages.create (web.php:191) وadmin.pages.edit (web.php:193) يستدعيان create()/edit() اللتين تُرجعان create-pro/edit-pro، لا الملفات اليتيمة. بحث grep شامل عبر app/ وroutes/
- الحل: حذف القوالب اليتيمة الثمانية من resources/views/admin/pages/: create.blade.php, edit.blade.php, create-simple.blade.php, create-visual.blade.php, edit-simple.blade.php, edit-visual.blade.php, create-pro-backup.blade.php, builder-component.blade.php. الإبقاء فقط على index/create-pro/edit-pro المستخدمة فعلياً. يُفضَّل التحقق من سجل Git قبل الحذف للتأكد من عدم وجود مرجع ديناميكي عبر متغير اسم قالب (ل

## [MEDIUM/consistency] غياب توحيد <head>/meta/الشعار/الثيم عبر اللايوتات
- النطاق: resources/views/layouts/*.blade.php (10 لايوتات) + app/Http/Middleware/ApplyTheme.php
- الدليل: قرأتُ اللايوتات العشرة كلها + ApplyTheme + footer + settings + seeder، والمشكلة مؤكَّدة وأوسع مما وُصف:

(1) لا يوجد layout أساس مشترك — كل ملف يكرّر <head> مستقلاً: auth.blade.php:23-80، auth-clean.blade.php:1-29، admin.blade.php:10-83، teacher.blade.php:9-265، app.blade.php:1-26، student-app.blade.php:9-176، parent.blade.php:1-316، school-admin.blade.php:1-635، super-admin.blade.php:1-9، student
- الحل: إنشاء layouts/base.blade.php (أو partial واحد partials/head.blade.php) يقرأ مركزياً: site_name, site_logo, site_favicon (<link rel="icon">), meta_title/meta_description, الألوان والخط من الثيم، ويعرض @yield('content')؛ ثم تُعاد كتابة اللايوتات العشرة لترث منه أو @include الـpartial، مع نقل منطق الشعار إلى partial مشترك (partials/brand) يقرأ site_logo/site_name. كذلك تعديل footer.blade.php:186-188 

## [MEDIUM/config-gap] site_favicon و site_tagline يُحفَظان عبر Theme/Landing لكن غير مُطبّقين في أي layout
- النطاق: app/Http/Controllers/Admin/ThemeController.php + LandingPageController.php + SuperAdminController.php مقابل resources/views/layouts/*
- الدليل: الحفظ مؤكد: ThemeController.php:69 يقبل type=favicon ثم :93-107 يحذف القديم ويحفظ Setting::set("site_favicon", $path)؛ و site_tagline يُحفظ/يُتحقق في LandingPageController.php:39 و SuperAdminController.php:966. التحميل للعرض في الإدارة: LandingPageController.php:17,22 و SuperAdminController.php:936,941. لكن grep على resources/views/layouts لـ site_favicon|site_tagline = لا نتائج إطلاقاً. وعبر كامل
- الحل: توحيد الـ<head> في layout أساس مشترك وإضافة <link rel="icon" href="{{ setting('site_favicon') ? asset('storage/app/public/data/'.setting('site_favicon')) : asset('favicon.ico') }}"> عند توفر site_favicon (مع fallback ثابت)، واستهلاك site_tagline في عنوان/وصف الصفحة العامة landing.blade.php (مثلاً ضمن og:description أو سطر العنوان) بدل/بجانب site_description. أو — إن لم يكونا مطلوبين فعلاً — إزالة 

## [MEDIUM/consistency] meta CSRF غير متّسق عبر اللايوتات — AJAX/رفع الأفاتار يفشل في app/parent/student
- النطاق: موجود: admin.blade.php:15, auth.blade.php:29, auth-clean.blade.php:7, school-admin.blade.php:6, teacher.blade.php:14, student-app(غير موجود فعلياً). مفقود: app.blade.php (لا csrf meta), super-admin.blade.php (لا meta لكن JS يعتمد fallback)، parent.blade.php، student.blade.php، student-app.blade.php
- الدليل: الجزء المعماري صحيح ومؤكَّد بالكود: وسم <meta name="csrf-token"> مكتوب يدوياً في كل لايوت على حدة لا عبر partial مركزي. موجود في: admin.blade.php:15، teacher.blade.php:14، school-admin.blade.php:6، auth.blade.php:29، auth-clean.blade.php:7. مفقود في: app.blade.php:3-6، parent.blade.php:3-6، student.blade.php:3-6، student-app.blade.php:11-15. وsuper-admin.blade.php لا يحوي الوسم لكنه يضيف fallback 
- الحل: إنشاء partial واحد resources/views/partials/head-meta.blade.php يحوي <meta charset> + viewport + <meta name="csrf-token" content="{{ csrf_token() }}">، و@include في رؤوس اللايوتات العشرة (خاصة app/parent/student/student-app/super-admin). هذا يلغي الـ fallback النقطي في super-admin.blade.php:299 ويضمن وجود الوسم لكل المستهلكين. الإصلاح العاجل الأدق للكسر المؤكَّد: تحصين messages/bulk/inbox.blade.ph

## [MEDIUM/settings-coverage] إعداد footer_text يُحفَظ في صفحة الإعدادات لكنه لا يُعرَض في أي مكان (configurability gap)
- النطاق: يُحفظ: SettingsController.php:24,55,62,103 + admin/settings.blade.php:40-41. يُعرَض: لا مكان. components/footer.blade.php:184-189 يكتب النص يدوياً
- الدليل: app/Http/Controllers/Admin/SettingsController.php:62 يحفظ footer_text في DB، و resources/views/admin/settings.blade.php:40-41 حقل الإدخال (مع validation:43 ووصف:103). لكن grep على footer_text عبر المستودع كله لا يُرجع إلا الكنترولر وصفحة الإعدادات نفسها (settings.blade.php:40,41,859) — لا قالب يقرؤه. الفوتر components/footer.blade.php:186-188 يبني سطر الحقوق يدوياً من date('Y') + $siteName ويتجاهل
- الحل: في components/footer.blade.php استبدل سطر الحقوق المبني يدوياً (186-188) بقراءة setting('footer_text') مع fallback للصياغة الحالية ('© {date} {siteName} جميع الحقوق محفوظة')، وكذلك في landing/partials/footer.blade.php:115 استبدل النص الثابت بـ footer_text مع fallback. أو، إن لم يكن الإعداد مقصوداً، احذف الحقل من admin/settings.blade.php:40-43 ومن الكنترولر (السطور 24,43,62,103) ومن JS:859 لإزالة خ

## [MEDIUM/settings-coverage] site_favicon يُعرَّف ويُحفَظ لكن لا <link rel=icon> في أي لايوت (configurability gap)
- النطاق: مفتاح site_favicon ضمن مفاتيح الإعدادات؛ لا أي لايوت من العشرة يضع <link rel="icon">
- الدليل: grep على favicon|rel="icon"|shortcut icon|apple-touch-icon عبر كل resources/views/layouts/*.blade.php => لا نتيجة في أي لايوت. فحص رؤوس <head> يدوياً يؤكّد الغياب: admin.blade.php:12-83، app.blade.php:3-26، auth-clean.blade.php:3-29، auth.blade.php:25-80، parent.blade.php:3-316، school-admin.blade.php:3-635، student-app.blade.php:11-176، student.blade.php:3-30، super-admin.blade.php:3-9، teacher.b
- الحل: إنشاء partial رأس مشترك (مثلاً resources/views/partials/site-head.blade.php) يُضمَّن داخل <head> في اللايوتات العشرة ويضع: <link rel="icon" href="{{ setting('site_favicon') ? asset('storage/app/public/data/' . setting('site_favicon')) : asset('favicon.ico') }}">. ملاحظة: استخدم مسار asset('storage/app/public/data/' . ...) ليطابق التقليد الفعلي المستخدم في theme.blade.php:176 وThemeController.php:1

## [MEDIUM/consistency] messages-realtime.js (الإشعارات اللحظية) محمّل في 5 لايوتات دون أخرى — إشعارات تعمل لدور دون آخر
- النطاق: محمّل: admin.blade.php:1049, parent.blade.php:432, teacher.blade.php:483, school-admin.blade.php:1126, student.blade.php:139. غير محمّل: super-admin.blade.php, student-app.blade.php, app.blade.php, auth, auth-clean
- الدليل: التضمين اليدوي المتكرر مؤكَّد بالكود في 5 لايوتات: admin.blade.php:1049 / parent.blade.php:432 / teacher.blade.php:483 / school-admin.blade.php:1126 (الوحيد بـ ?v={{ time() }}) / student.blade.php:139. ولا يوجد src في super-admin.blade.php و student-app.blade.php و app/auth/auth-clean.

النيّة المعمارية للسكربت (public/js/messages-realtime.js, 549 سطر): poller إشعارات عام يفترض أن يعمل على كل صفحة
- الحل: استخراج التضمين إلى partial واحد resources/views/partials/realtime-scripts.blade.php و@include في كل لايوت مُصادَق عليه فعلياً مستخدَم: admin, school-admin, teacher, parent, student-app، مع توحيد سياسة cache-busting (@vite بـ hash أو ?v={{ filemtime(public_path('js/messages-realtime.js')) }} الثابت بدل time() المتقلّب الذي يبطل التخزين المؤقت كل طلب). توحيد كلاس الـ badge في student-app (nav-item-

## [MEDIUM/consistency] skip-to-content (a11y baseline) ناقص في 4 لايوتات + 3 صيغ/أنماط مختلفة
- النطاق: موجود: admin.blade.php:85, parent.blade.php:318, teacher.blade.php:267, student-app.blade.php:178 (نمط inline موحّد)، auth.blade.php:83 (class=skip-link، نمط مختلف). مفقود: super-admin.blade.php, school-admin.blade.php, app.blade.php, auth-clean.blade.php, student.blade.php
- الدليل: تأكيد كامل بالكود لكل ادعاءات النتيجة. النمط inline الموحّد حرفياً (نفس الـ style ونفس النص "تخطي إلى المحتوى الرئيسي") موجود في: admin.blade.php:85-87، teacher.blade.php:267-269، parent.blade.php:318-320، student-app.blade.php:178-180. النمط المختلف في auth.blade.php:83 (class="skip-link"، نص مختلف "الانتقال إلى المحتوى الرئيسي"، لون أخضر #3CCB8A مع CSS مُعرَّف في auth.blade.php:40 بدل inline). غ
- الحل: إنشاء partial واحد resources/views/partials/skip-link.blade.php يقبل متغيّر هدف (مثلاً @props/@include with target) ويُضمَّن كأول عنصر داخل <body> في كل اللايوتات العشرة، مع توحيد النص العربي ونقل النمط إلى قاعدة CSS مشتركة (مثل ضمن app.css أو ملف a11y.css) بدل تكرار style inline. توحيد id الهدف على main واحد متّسق (مثلاً id="main-content") عبر جميع اللايوتات بما فيها إضافة id لـ school-admin's <m

## [MEDIUM/consistency] عرض الفلاش (session success/error) مُنفَّذ في لايوت واحد فقط — رسائل مفقودة في باقي الأدوار
- النطاق: school-admin.blade.php:985-997 يعرض session('success')/session('error'). بقية اللايوتات (admin, super-admin, teacher, parent, student, student-app, app) لا تعرض الفلاش على مستوى اللايوت
- الدليل: resources/views/layouts/school-admin.blade.php:985-997 هو اللايوت الوحيد الذي يعرض session('success')/session('error') مركزياً (تأكيد بالـ grep: لا لايوت آخر من الـ10 يحوي session('success')). بقية اللايوتات فيها @yield('content') فقط بلا فلاش: admin.blade.php:491-493، teacher.blade.php:404-406، parent.blade.php:427-429، super-admin.blade.php:244، app.blade.php:28، student-app.blade.php:246، stude
- الحل: إنشاء partial موحّد واحد للفلاش وتضمينه قبل @yield('content') في كل اللايوتات العشرة بدل التكرار. الأفضل عملياً: تفعيل المكوّن اليتيم القائم بإضافة @include('components.glass-notifications') (الذي يغطّي success/error/warning/info و$errors->all عبر التوست الزجاجي) داخل كل لايوت قبل المحتوى، ثم إزالة الـ divs المضمّنة في school-admin.blade.php:985-997 لتجنّب الازدواج، والاستغناء عن عرض الفلاش المتفر

## [MEDIUM/settings-coverage] اللوغو/اسم الموقع مكتوب يدوياً (hardcoded) في معظم اللايوتات رغم وجود site_name/site_logo
- النطاق: يقرأ DB: auth.blade.php:90-95 (site_logo/site_name). يكتب يدوياً 'قيمّ'/'بناء القيم': admin.blade.php:96, super-admin.blade.php:17, auth-clean.blade.php:37, school-admin.blade.php:646-647 ('بناء القيم'), teacher.blade.php:276 ('مركز التدريس'), student.blade.php:40 ('بناء القيم'), parent.blade.php:32
- الدليل: بحث grep عبر كامل مجلد layouts يؤكّد أن site_name/site_logo يُقرآن حصرياً في auth.blade.php (الأسطر 3-21 جلب عبر Setting::getMany، والعرض في 90-95). جميع الأدلة الأخرى مؤكَّدة سطراً بسطر: admin.blade.php:96 'قيمّ' ثابت | super-admin.blade.php:17 'قيمّ' ثابت | auth-clean.blade.php:37 'قيمّ' ثابت | school-admin.blade.php:646 'بناء القيم' ثابت | teacher.blade.php:276 'مركز التدريس' | student.blade.ph
- الحل: إنشاء partial مشترك resources/views/partials/brand.blade.php يقرأ site_name/site_logo (يُفضَّل عبر setting() أو Setting::getMany مع نفس مسار الأصل asset('storage/app/public/data/'.$siteLogo) كما في auth.blade.php:91)، ويعرض الشعار إن وُجد وإلا اسم الموقع، مع وسيط اختياري $subtitle لعنوان الدور (مثل 'مدير المدرسة'/'معلم'). ثم استبدال الكتل الثابتة في كل لايوت: admin:94-97، super-admin:15-18 + title

## [MEDIUM/settings-coverage] إعداد footer_text مُعرَّف ومحفوظ لكنه ميت تماماً: لا يقرؤه أي قالب — الفوتر يكتب النص يدوياً
- النطاق: app/Http/Controllers/Admin/SettingsController.php · resources/views/admin/settings.blade.php · resources/views/components/footer.blade.php · resources/views/emails/layouts/master.blade.php
- الدليل: grep لـ footer_text عبر كامل المشروع يُظهر القراءة الوحيدة في SettingsController.php:24 (داخل index() لإعادة ملء حقل النموذج فقط)، ولا توجد أي setting('footer_text') في طبقة العرض. الحفظ: SettingsController.php:62 'footer_text' => $request->footer_text. الحقل: admin/settings.blade.php:41 input name="footer_text" ... required. الفوترات تكتب النص ثابتاً: components/footer.blade.php:187 &copy; {{ dat
- الحل: توحيد نص الفوتر في مصدر واحد يقرأ الإعداد مع fallback: في components/footer.blade.php:187 وemails/layouts/master.blade.php:479 وlanding/partials/footer.blade.php:115 استبدال النص الثابت بـ {{ setting('footer_text') ?: ('© '.date('Y').' '.$siteName.'. جميع الحقوق محفوظة') }}. ويُفضَّل تمرير النص عبر مكوّن/View Composer مركزي واحد لإزالة التكرار الثلاثي المتباعد. وإن لم يكن الحقل مقصوداً للتحكم فعلا

## [MEDIUM/intent-mismatch] الشعار/اسم الموقع/الأيقونة (logo) مكتوبة يدوياً في لايوتات الأدوار الداخلية: site_logo/site_name يُقرآن في الواجهة العامة فقط دون أي لوحة تحكم
- النطاق: resources/views/layouts/admin.blade.php · super-admin.blade.php · teacher.blade.php · school-admin.blade.php · parent.blade.php · student.blade.php · student-app.blade.php (مقابل auth.blade.php · landing.blade.php · emails/layouts/master.blade.php التي تقرأها)
- الدليل: grep لمفاتيح site_logo/site_name/siteLogo/siteName عبر كل ملفات resources/views/layouts/ يُظهر تطابقات في auth.blade.php فقط (الأسطر 4,11,12,20,21,90-94). أما اللايوتات الداخلية فتكتب العلامة ثابتة: admin.blade.php:95-96 (🌟 + «قيمّ»)، super-admin.blade.php:16-17 (🌟 + «قيمّ»)، teacher.blade.php:275-276 (👨‍🏫 + «مركز التدريس»)، school-admin.blade.php:642-647 (<i class="fas fa-school"> + «بناء القيم»)
- الحل: إنشاء مكوّن مركزي <x-brand-logo/> (أو partial: partials.brand-logo) يطبّق منطق auth.blade.php مرّة واحدة: يقرأ site_logo/site_name عبر Setting::getMany بقيم افتراضية، فإن وُجد site_logo يعرض <img src="{{ asset('storage/app/public/data/'.$siteLogo) }}" alt="{{ $siteName }}"> وإلا يعرض الأيقونة + {{ $siteName }}. ثم استبدال النصوص الثابتة في رؤوس admin.blade.php:95-96، super-admin.blade.php:16-17، t

## [MEDIUM/consistency] تعارض القيم الافتراضية للثيم بين مصادر متعددة: الأدمن يرى/يطبّق ألواناً وخطوطاً مختلفة حسب الشاشة قبل أول حفظ
- النطاق: app/Http/Controllers/Admin/ThemeController.php · database/seeders/DefaultSettingsSeeder.php · resources/views/layouts/admin.blade.php · teacher.blade.php · student-app.blade.php · auth.blade.php · app/Http/Controllers/Admin/LandingPageController.php
- الدليل: primary_color الافتراضي متضارب: ThemeController.php:23 و LandingPageController.php:18 و auth.blade.php:7,16 = '#3CCB8A' (أخضر) — بينما admin.blade.php:4 و teacher.blade.php:4 و student-app.blade.php:4 و DefaultSettingsSeeder.php:33 = '#667eea' (بنفسجي). كذلك site_theme: admin.blade.php:8='dark' مقابل ThemeController.php:22='light' و DefaultSettingsSeeder.php:19='light'. و background_color: auth.bl
- الحل: إنشاء مصدر واحد للافتراضيات (config/theme.php أو ثوابت في App\Models\Setting) ودالة مساعدة theme_default('primary_color')، واستهلاكها في كل setting($key, theme_default($key)) عبر الملفات الستة بدل تكرار الأرقام السحرية. وتوحيد قيمة site_theme الافتراضية (light) عبر admin.blade.php:8 مع البقية، وتوحيد primary_color/background_color/text_color على قيمة واحدة، وجعل DefaultSettingsSeeder يقرأ من نفس ا

## [MEDIUM/duplication] بناء مسار ملفات التخزين 'storage/app/public/data/' مكرّر ونمطي وغير قياسي في عشرات القوالب
- النطاق: resources/views/**/*.blade.php (30+ موضع: layouts/auth.blade.php, landing.blade.php, emails/layouts/master.blade.php, admin/theme.blade.php, teacher/*, student/*, admin/* ...) · app/Http/Controllers/Admin/ThemeController.php
- الدليل: التكرار مؤكَّد: 35 موضعاً عبر 24 قالباً يبني الرابط يدوياً بـ asset('storage/app/public/data/' . $path) (مثلاً layouts/auth.blade.php:91، landing.blade.php:61,219، emails/layouts/master.blade.php:432، admin/theme.blade.php:157,176,195، teacher/review-single.blade.php:38,90، student/teams.blade.php:117 …). والأهم: نفس البادئة مُكرَّرة داخل accessor مركزي getAvatarUrlAttribute في app/Models/User.php
- الحل: إنشاء دالة مساعدة مركزية واحدة (مثلاً media_url($path) في app/Helpers/SettingsHelper.php) تبني الرابط من Storage::disk('public')->url($path) أو من بادئة موحّدة واحدة، واستبدال كل التكرارات الـ35 بها بما فيها داخل getAvatarUrlAttribute (User.php:473,479) و ThemeController.php:112. وقبل ذلك حسم البادئة الصحيحة الواحدة بناءً على كيفية تنفيذ storage:link في الإنتاج: إمّا تثبيت '/storage/data/' (إذا كا

## [MEDIUM/config-gap] إعدادات enable_registration و enable_2fa مبذورة لكن لا تُقرأ في أي منطق — خيارات بلا أثر فعلي
- النطاق: database/seeders/DefaultSettingsSeeder.php · (لا قارئ في app/ أو resources/)
- الدليل: database/seeders/DefaultSettingsSeeder.php:155-166 يُعرّف enable_registration (value '1', type boolean, "تفعيل التسجيل للمستخدمين الجدد") و enable_2fa (value '1', type boolean, "تفعيل المصادقة الثنائية"). grep على enable_registration|enable_2fa عبر **/*.php وresources يُرجع ملفاً واحداً فقط: الـ seeder نفسه. لا قارئ في أي مكان. + AuthController.php:311-362 (register) ينشئ المستخدم مباشرة بلا أي فح
- الحل: أحد خيارين: (1) ربطهما فعلياً — إضافة فحص setting('enable_registration') كحارس على مسار register (في AuthController::register أو middleware على routes/web.php:71-74) يردّ/يعيد توجيه عند التعطيل، وفحص setting('enable_2fa') كبوابة عامة في تدفّق المصادقة قبل الاعتماد على two_factor_enabled؛ مع إضافتهما لواجهة الإعدادات (SettingsController + admin/settings.blade.php) أسوة بـ maintenance_mode. (2) أو ح

## [MEDIUM/duplication] كتلة <head> + الخطوط + متغيرات الثيم CSS مكرّرة ومتباعدة عبر اللايوتات بدل partial مركزي واحد
- النطاق: resources/views/layouts/admin.blade.php · teacher.blade.php · student-app.blade.php · school-admin.blade.php · auth.blade.php (مقابل app.blade.php · student.blade.php · parent.blade.php · super-admin.blade.php · auth-clean.blade.php التي لا تقرأ الثيم)
- الدليل: تأكّد التكرار وفجوة المطابقة بالكود:
- قارئو الثيم (كتلة @php لجلب الإعدادات + سلسلة @if/@elseif لروابط جوجل فونتس + <style>:root{--color-primary...}): admin.blade.php:1-9 و41-51 | teacher.blade.php:1-8 و28-35 | student-app.blade.php:1-8 و39-46 | auth.blade.php:1-22 (عبر Setting::getMany) و54-77. أربعة لايوتات فقط، الكتلة شبه حرفية مع اختلافات طفيفة في الافتراضيات (مثلاً auth يفترض primary #3CCB8A
- الحل: استخلاص partial واحد resources/views/partials/theme-head.blade.php يحتوي: (1) كتلة @php واحدة تجلب font_family/primary_color/secondary_color/text_color/background_color/site_theme عبر Setting::getMany مع افتراضات موحّدة، (2) سلسلة روابط جوجل فونتس مع fallback عربي مضمون (كما في student-app:28-31)، (3) كتلة <style>:root{} موحّدة تستخدم adjustBrightness/hexToRgba/hexToRgb. ثم @include('partials.them


# ===== LOW (20) =====

## [LOW/dead-code] لايوت student.blade.php مهجور (dead layout) لكنه ما زال يكرّر علامة تجارية ثابتة
- النطاق: resources/views/layouts/student.blade.php (مقابل student-app.blade.php الحيّ)
- الدليل: resources/views/layouts/student.blade.php:6 و:40 يثبّتان 'بناء القيم' (title وh1)، والملف خالٍ تماماً من أي استدعاء setting() أو قراءة ألوان/خط/site_name (تأكيد بالبحث: لا تطابق سوى سطري النص الثابت). بالمقابل student-app.blade.php:3-7 يقرأ الثيم عبر setting('font_family'/'primary_color'/...). الكود ميت فعلياً: لا يوجد @extends('layouts.student') في أي view، ولا view('layouts.student') في app/ أو 
- الحل: قبل الحذف يجب تعديل الـfallback غير القابل للوصول في الملفات الثلاثة، لا الاكتفاء بالبحث عن @extends. الخطوات: (1) في messages/index.blade.php:1، messages/show.blade.php:1، messages/bulk/inbox.blade.php:1 استبدل الـfallback الأخير 'layouts.student' بـ 'layouts.student-app' (أو الأفضل: استخراج اختيار اللايوت إلى دالة/خدمة موحّدة بدل تكرار سلسلة ternary طويلة في 3 أماكن). (2) ثم احذف resources/views

## [LOW/duplication] كتلة تحميل خطوط Google (@if fontFamily) مكررة حرفياً وبصيغ ناقصة الـfallback في عدة لايوتات
- النطاق: admin.blade.php:27-35, teacher.blade.php:18-26, student-app.blade.php:20-31, school-admin (Cairo ثابت لا يدعم font_family)
- الدليل: سلسلة @if($fontFamily === 'IBM Plex Sans Arabic')/@elseif(...Cairo/Tajawal/Almarai) لاختيار رابط Google Fonts مكررة حرفياً في: admin.blade.php:27-35، teacher.blade.php:18-26، student-app.blade.php:20-31. فرع @else الاحتياطي موجود فقط في student-app.blade.php:28-31 وغائب في admin وteacher. أمّا school-admin.blade.php:13 فيثبّت family=Cairo ثابتاً ويكرّره في CSS عند السطر 23 (font-family: 'Cairo') م
- الحل: استخراج اختيار خط Google إلى partial واحد (مثل resources/views/layouts/partials/font-link.blade.php) يتضمّن خريطة font_family => رابط مع فرع fallback افتراضي، وتضمينه عبر @include في كل اللايوتات العشرة بدل التكرار. وفي school-admin وparent/super-admin/student/app: استبدال الرابط الثابت بـ$fontFamily الديناميكي عبر الـpartial نفسه ليتّسق الجميع. اختيارياً: تقييد التحقق في ThemeController.php:45 وS

## [LOW/consistency] لايوتات الأدوار الداخلية (teacher/parent/student/student-app/super-admin/app/admin/school-admin) بلا فوتر إطلاقاً — الفوتر حصري على auth/landing
- النطاق: components/footer.blade.php مُضمَّن فقط في auth.blade.php + auth-clean.blade.php + landing.blade.php مقابل بقية اللايوتات (لا فوتر)
- الدليل: grep لـ @include('components.footer') يُرجِع 3 مواضع فقط: resources/views/layouts/auth.blade.php:128، resources/views/layouts/auth-clean.blade.php:67، resources/views/landing.blade.php:830. أما لايوتات الأدوار الثمانية فتنتهي بـ </main> ثم scripts/nav بلا أي فوتر هوية/حقوق: teacher.blade.php:404-406 (</main> ثم script)، parent.blade.php:427-429، admin.blade.php:490-494، super-admin.blade.php:244-2
- الحل: اتخاذ قرار موحّد موثّق: (أ) إنشاء مكوّن footer مصغّر مشترك (حقوق + روابط privacy/terms) يُضمَّن في لايوتات الأدوار الثمانية لإزالة العشوائية، أو (ب) توثيق صريح أن اللوحات الداخلية SPA-like بلا footer عمداً. وعند التنفيذ يجب أيضاً جعل سطر الحقوق يقرأ مفتاح footer_text الموجود فعلاً في الإعدادات (بدلاً من النص الثابت في footer.blade.php:186-188) كي ينعكس ما يحفظه الأدمن، وتوحيد أسماء مفاتيح روابط ال

## [LOW/intent-mismatch] رابط 'الإعدادات' في قوائم avatar لـ super-admin/school-admin يشير إلى dashboard بدل صفحة إعدادات — عدم مطابقة للنية
- النطاق: layouts/super-admin.blade.php + layouts/school-admin.blade.php (قائمة avatar) مقابل layouts/admin.blade.php + teacher.blade.php (تشير لصفحة settings فعلية)
- الدليل: resources/views/layouts/school-admin.blade.php:667-668 عنصر قائمة الأفاتار المعنون "⚙️ الإعدادات" href={{ route('school-admin.dashboard') }} بينما المسار الصحيح route('school-admin.settings') موجود (routes/web.php:447 ضمن مجموعة prefix('school-admin')->name('school-admin.') عند السطر 384) ومستخدَم فعلاً في السايدبار school-admin.blade.php:844 وزر الهيدر:889. للمقارنة: admin.blade.php:470 و teacher
- الحل: تصحيح فوري: في resources/views/layouts/school-admin.blade.php:667 استبدل route('school-admin.dashboard') بـ route('school-admin.settings'). إصلاح جذري (يلغي فئة الانحراف): استخراج مكوّن Blade مشترك مثل x-avatar-dropdown ببارامترات (settingsRoute, ids/prefix للـ JS, موضع القائمة top/bottom) واستهلاكه في اللايوتات الأربعة بدل تكرار البنية و inline styles يدوياً، فيصبح settingsRoute صريحاً لكل لايوت 

## [LOW/config-gap] site_keywords و contact_address يُبذران/يُعرّفان لكن لا يقرؤهما أي قالب واجهة (إعدادات بلا أثر)
- النطاق: DefaultSettingsSeeder.php (يعرّف site_keywords, contact_address) مقابل غياب أي قارئ في layouts/landing/footer
- الدليل: المفتاحان معرّفان حصرياً في السيدر ولا يقرأهما أي كود. بحث على مستوى المستودع كله: 'site_keywords' يظهر فقط في database/seeders/DefaultSettingsSeeder.php:78، و'contact_address' يظهر فقط في DefaultSettingsSeeder.php:116. لا يوجد أي <meta name="keywords"> في أي ملف (grep على name=["']keywords["'] = صفر نتائج). لا يوجد أي ملف PHP داخل app/ يشير إلى أي من المفتاحين (grep = صفر). الفوتر الديناميكي comp
- الحل: الخيار الأنظف معمارياً: توحيد القراءة في head/footer مشترك. (1) إضافة <meta name="keywords" content="{{ setting('site_keywords','') }}"> في الـ<head> المشترك لقوالب الواجهة العامة (landing.blade.php / auth / auth-clean / pages/landing-dynamic) إن كان الـSEO مقصوداً. (2) جلب contact_address ضمن getMany في components/footer.blade.php وعرضه شرطياً بجوار البريد/الهاتف، واستبدال السلسلة الثابتة في land

## [LOW/dead-code] layouts/student.blade.php و layouts/app.blade.php يتيمان/مهجوران — ألوان وخط ثابتة لا علاقة لها بالثيم
- النطاق: layouts/student.blade.php (موثّق كمهجور)، layouts/app.blade.php
- الدليل: resources/views/layouts/student.blade.php:12-22 يثبّت font-family:'IBM Plex Sans Arabic' و background:linear-gradient(135deg,#667eea 0%,#764ba2 100%) inline، مع رأس كامل (سطور 33-130) بألوان مثبّتة، وصفر استدعاءات setting()/Setting::. resources/views/layouts/app.blade.php:11-23 يكرّر نفس الخط ونفس التدرّج #667eea→#764ba2، وصفر استدعاءات setting(). بحث @extends('layouts.app') عبر resources/views = 
- الحل: التعامل مع كل ملف على حدة بدل التوحيد المقترح: (1) layouts/app.blade.php: بما أنه لا @extends له ومرجعه الوحيد config/livewire.php:34 الذي لا يُستثار (لا مكوّن Livewire يُرسم كصفحة كاملة)، يُحذف الملف ويُحدَّث config/livewire.php ليشير إلى تخطيط حقيقي (مثل layouts.auth) أو يُترك مع توثيق أنه افتراضي Livewire غير مُفعّل. (2) layouts/student.blade.php: يُحذف فقط بعد تعديل الفرع else 'layouts.student

## [LOW/consistency] theme-color و apple-mobile-web-app موجودة في بعض الواجهات فقط
- النطاق: student-app + landing (لديها theme-color) مقابل admin/teacher/school-admin/super-admin/parent/auth/app (بلا theme-color)
- الدليل: تأكد بالكود: <meta name="theme-color"> موجود فقط في resources/views/layouts/student-app.blade.php:14 و resources/views/landing.blade.php:50 (وأيضاً resources/views/pages/landing-dynamic.blade.php:35 لم تُذكر في النتيجة). أما meta الخاصة بـ apple-mobile-web-app فموجودة فقط في landing.blade.php:52-53. اللايوتات الثمانية الأخرى بلا theme-color: admin.blade.php:12-16، parent.blade.php:3-8، teacher.bla
- الحل: بما أنه لا يوجد head partial مشترك، الخيار الأنظف: إنشاء resources/views/partials/head-meta.blade.php يحوي meta الموحّدة (theme-color و apple-mobile-web-app-capable و apple-mobile-web-app-status-bar-style) معتمداً على setting('primary_color') ثم @include له داخل <head> في اللايوتات العشرة (مع إزالة النسخ المكررة من student-app و landing و landing-dynamic لتجنّب الازدواج). كحدٍّ أدنى: إضافة <meta n

## [LOW/dead-code] layout student.blade.php مهجور لكنه ما زال يحمل أصولاً ويحسب رسائل
- النطاق: resources/views/layouts/student.blade.php (مهجور حسب السياق) مقابل student-app.blade.php (الفعّال)
- الدليل: الملف قائم بالكامل: resources/views/layouts/student.blade.php:1-141. أدلة الأصول/المنطق القديم مؤكَّدة: FA 6.4.0 (student.blade.php:10) بينما الفعّال student-app.blade.php:32 يستخدم 6.5.1 (وكذلك admin/school-admin/teacher). منطق عدّ الرسائل مكرَّر: Message::where(...)->count() (student.blade.php:50) و BulkMessageRecipient::where(...) (student.blade.php:61) مقابل student-app.blade.php:301. لا يوجد 
- الحل: أولاً وحِّد الـ fallback في الـ ternary الثلاثة (messages/index.blade.php:1, messages/show.blade.php:1, messages/bulk/inbox.blade.php:1) من 'layouts.student' إلى 'layouts.student-app' (أو بسِّط الـ ternary لأنه يكرّر خريطة الأدوار الموجودة في NotificationController.php:33-36 — يُفضَّل استخراجها لِـ helper/composer موحّد). بعدها احذف resources/views/layouts/student.blade.php نهائياً لإزالة FA 6.4.0

## [LOW/duplication] بناء رابط الأفاتار مكرّر يدوياً في عشرات الـ Blade ويتجاوز الـ accessor الموحّد (فقدان الصورة الافتراضية)
- النطاق: app/Models/User.php::getAvatarUrlAttribute مقابل ~20 ملف blade تستخدم asset('storage/app/public/data/'.$x->avatar) مباشرة
- الدليل: التكرار حقيقي ومؤكَّد: app/Models/User.php:463-504 يعرّف accessor موحّد (فحص وجود الملف + رابط خارجي http + SVG افتراضي)، بينما 7 قوالب تبني الرابط يدوياً asset('storage/app/public/data/'.$x->avatar): student/teams.blade.php:117، teacher/student-leaderboard.blade.php:99، teacher/review-submissions.blade.php:70، teacher/review-single.blade.php:38، admin/pending-submissions.blade.php:71، admin/revie
- الحل: قيمة الإصلاح الحقيقية هنا توحيد المنطق لا إصلاح عطل ظاهر. استبدال البناء اليدوي بـ {{ $user->avatar_url }} (أو مكوّن مشترك <x-avatar :user=.../>) في القوالب السبعة، مع حذف فروع @else البديلة لأن avatar_url يولّد الـ SVG الافتراضي بنفسه. وأهم خطوة فعلية: توحيد LeaderboardController::avatarUrl (app/Http/Controllers/LeaderboardController.php:384) مع منطق User::getAvatarUrlAttribute لأنه يستخدم قاعدة 

## [LOW/duplication] تنسيق التاريخ منفّذ بثلاث طرق متباينة عبر القوالب دون مساعد موحّد
- النطاق: عشرات ملفات resources/views (->format(...)، diffForHumans()، Carbon::parse()->translatedFormat())
- الدليل: التكرار مؤكَّد كميّاً وغياب المساعد مؤكَّد بالكود: ~73 استدعاء ->format() عبر 48 ملفاً، و37 استدعاء diffForHumans() عبر 29 ملفاً، مقابل translatedFormat() في موضعين فقط. لا يوجد أي مساعد تاريخ في app/Helpers/SettingsHelper.php (الدوال الموجودة: setting, safe_mail_subject, set_setting, hexToRgba, hexToRgb, adjustBrightness, html_excerpt, safe_html — لا شيء للتاريخ)، ولا يوجد مكوّن <x-date> في resou
- الحل: إضافة مساعد موحّد (مثل date_display($value, $withTime=false) في app/Helpers/SettingsHelper.php) أو مكوّن Blade <x-date :value :withTime /> يعتمد translatedFormat بصيغة عربية واحدة متّسقة (مثلاً 'j F Y' و'j F Y - h:i A')، ثم استبدال ->format('Y-m-d'|'Y/m/d'...) و diffForHumans() العرضية به تدريجياً مع استثناء قيم datetime-local (Y-m-d\\TH:i) التي تبقى رقمية. يُصلِح هذا تباين الفواصل ويوحّد التعريب 

## [LOW/duplication] مكافأة XP→Coins محسوبة بنِسب مختلفة في مواضع مختلفة
- النطاق: app/Actions/Activity/SubmitActivityAction.php، app/Http/Controllers/StudentController.php، app/Http/Controllers/TeacherController.php
- الدليل: قاعدة floor($xp/2) مكررة حرفياً في كل المواضع المُستشهد بها وأكثر: app/Actions/Activity/SubmitActivityAction.php:166 'coins' => max(1,(int)floor($xp/2))؛ app/Http/Controllers/StudentController.php:940 max(1, floor($xp/2))، :1002، :1029 (نفس الصيغة في إنشاء العملة والإشعار وحدث ActivityCompleted). وفي TeacherController.php:1275-1276 صيغة مختلفة بنيوياً: $pointsPerMember = floor($total_score/2) و $c
- الحل: إضافة دالة مركزية في GamificationService مثل coinsForXp(int $xp): int { return max(1,(int)floor($xp/2)); } واستدعاؤها من SubmitActivityAction:166 وStudentController:940,1002,1029. ولأن نسخ الواجهة (practice-*.blade.php) لن تتوحّد بدالة PHP، يُفضَّل أن تُرجع الـ API قيمة العملات المحسوبة في الاستجابة بدل إعادة حسابها بـ JS. أما مسار درجة الفريق (TeacherController:1275-1276) فمدخله total_score لا xp

## [LOW/config-gap] زر كتم إشعارات المدرسة (school-admin settings) بلا أي حفظ خادمي — حالة في localStorage فقط
- النطاق: resources/views/school-admin/settings.blade.php:185-233 (toggleNotificationMute عبر localStorage 'messages_muted')
- الدليل: resources/views/school-admin/settings.blade.php:185-208 يعرض toggle داخل بطاقة "إعدادات الإشعارات" يستدعي toggleNotificationMute() الذي ينادي فقط window.messagesRealTime.toggleMute() (سطر 205) بلا form/route. الدالة في public/js/messages-realtime.js:470-476 تكتب فقط localStorage.setItem('messages_muted', ...) بلا أي fetch/ajax/طلب خادمي. واستعادة الحالة عند التحميل من localStorage.getItem('message
- الحل: إمّا حفظ تفضيل الكتم كإعداد مستخدم خادمي عبر إضافة فرع section=='notifications' في SchoolAdminController::updateSettings (وعمود/إعداد على المستخدم) وإرسال POST من البطاقة ليُزامَن عبر الأجهزة، أو — إن كان المقصود تفضيل جهاز محلي فقط — نقل الزر خارج بطاقة "إعدادات" أو إضافة وسم صريح "تفضيل لهذا الجهاز/المتصفح فقط" لتفادي الإيحاء بحفظ دائم متسق مع باقي البطاقات.

## [LOW/config-gap] خيارات layout_style و hero_background لا أثر لها خارج صفحة الهبوط/نموذج الثيم
- النطاق: app/Http/Controllers/Admin/ThemeController.php:31,46 (layout_style: full-width/boxed/wide) + DefaultSettingsSeeder hero_background مقابل layouts (لا تقرأ layout_style)
- الدليل: layout_style: مُتحقَّق منه في app/Http/Controllers/Admin/ThemeController.php:46 ('layout_style' => 'nullable|in:full-width,boxed,wide')، ومُحمَّل بقيمة افتراضية 'wide' في ThemeController.php:31، ومزروع في database/seeders/DefaultSettingsSeeder.php:24 و SettingsSeeder.php:55. لكن grep على كل resources/views/layouts/*.blade.php لا يُظهر أي قراءة لـ layout_style إطلاقاً، وعرض الحاوية مكتوب يدوياً (مث
- الحل: إما (أ) ربط layout_style فعلياً: قراءة setting('layout_style') في partial ثيم/لايوت مشترك وتطبيق صنف حاوية مركزي (مثلاً container--boxed بحد أقصى للعرض، container--wide، container--full-width) عبر اللايوتات العشرة بدل max-width المكتوبة يدوياً، وجعل القيمة قابلة للاختيار في admin/theme.blade.php بدل الحقل المخفي الثابت؛ و قراءة hero_background في قسم الهيرو بصفحة الهبوط (background-image / src دين

## [LOW/dead-code] layout يتيم: layouts/app.blade.php لا يُستخدم في أي مكان
- النطاق: resources/views/layouts/app.blade.php مقابل بقية اللايوتات العشرة
- الدليل: grep '@extends(...layouts.app...)' في resources/views → لا نتائج إطلاقاً. المرجع الوحيد للسلسلة "layouts.app" في كامل المستودع هو config/livewire.php:34 ('layout' => 'layouts.app') كـ layout افتراضي لمكوّنات Livewire ملء الصفحة. لكن Livewire غير مثبَّت أصلاً: composer.json (الأسطر 11-22) لا يذكره، grep -ci livewire في composer.lock → 0، ولا يوجد مجلد vendor/livewire. كما لا يوجد أي مكوّن Livewire 
- الحل: معالجة السقالة كوحدة لا ملف واحد: إمّا (أ) إن كان الانتقال إلى Livewire مقصوداً مستقبلاً، أضِف livewire/livewire إلى composer.json وأبقِ layouts/app.blade.php كلايوت ملء-صفحة بعد توحيد <head>/الثيم/الفوتر فيه مع باقي اللايوتات بدل التكرار؛ أو (ب) إن لم يكن مقصوداً، احذف الأربعة معاً (resources/views/layouts/app.blade.php، config/livewire.php، app/Livewire/Student/QuickStats.php، resources/views/li

## [LOW/duplication] theme.js و theme.min.js نسختان متباعدتان لا توأم min/مصدر
- النطاق: public/js/theme.js (يستخدمه landing.blade.php) مقابل public/js/theme.min.js (يستخدمه layouts/auth.blade.php و auth-clean.blade.php)
- الدليل: public/js/theme.js (1219B) محتواه فقط منطق التبديل: قراءة data-theme من localStorage + حدث نقر #themeToggle + دوران + استدعاء وصول window.qiyammAnnouncer (السطور 5-31). أما public/js/theme.min.js (8849B) فيحوي دالة updateThemeColors() تحقن كتلة <style> ضخمة لتجاوزات ألوان auth/header/footer للوضع الفاتح/الداكن، ثم منطق تبديل شبه مطابق لكنه يستدعي updateThemeColors() ويُسقِط استدعاء qiyammAnnouncer
- الحل: توحيد منطق التبديل النواة في ملف مصدري واحد (resources/js/theme.js مثلاً) يحوي قراءة/كتابة localStorage + حدث النقر + استدعاء qiyammAnnouncer للوصول، مع جعل كتلة CSS الخاصة بـ auth (updateThemeColors) جزءاً اختيارياً يُفعَّل عبر معطى أو يُنقَل إلى ملف CSS ثابت (auth-glass.css) بدل حقنه عبر JS. ثم إدخال الملف في Vite للحصول على نسخة مصغّرة حقيقية، واستعماله في القوالب الثلاثة، وحذف theme.min.js الم

## [LOW/duplication] دالة عدّ الرسائل غير المقروءة مكرّرة بصيغ متباعدة عبر 6+ لايوتات
- النطاق: admin.blade.php:121, teacher.blade.php:321, parent.blade.php:352, school-admin.blade.php:746, student-app.blade.php:301, student.blade.php:50 — كلها تكرّر Message::where('receiver_id',auth()->id())->where('is_read',false)->count()
- الدليل: استعلام عدّ الرسائل غير المقروءة المتطابق حرفياً Message::where('receiver_id', auth()->id())->where('is_read', false)->count() مكرّر داخل بلوكات @php في 6 لايوتات بالضبط: resources/views/layouts/admin.blade.php:121، teacher.blade.php:321، parent.blade.php:352، school-admin.blade.php:746، student-app.blade.php:301، student.blade.php:50 (تأكيد عددي عبر grep: 6 مطابقات). كذلك BulkMessageRecipient::wh
- الحل: توسعة HeaderDataComposer الموجود (أو composer جديد يُربط بـ ['layouts.admin','layouts.super-admin','layouts.teacher','layouts.parent','layouts.school-admin','layouts.student-app']) ليحقن $unreadCount و$bulkUnreadCount و$unreadNotifications لكل اللايوتات، وحذف بلوكات @php الستة من الرسائل والأربعة من الرسائل الجماعية. الأفضل وضع المنطق في دوال على User model (unreadMessagesCount/unreadBulkCount/unr

## [LOW/architecture] صفحات الأخطاء كلها ترث layouts.auth — فتفشل/تبدو غريبة لو كُسر مسار الإعدادات/الأصول
- النطاق: errors/403,404,429,500.blade.php جميعها @extends('layouts.auth'); layouts.auth يقرأ DB (Setting::getMany) ويضمّن components/footer (الذي يستعلم DB أيضاً)
- الدليل: resources/views/errors/500.blade.php:1 و403/404/429.blade.php:1 جميعها @extends('layouts.auth'). و layouts/auth.blade.php:3-14 تُنفّذ \App\Models\Setting::getMany(...) عند الرندر، و auth.blade.php:128 تُضمّن components.footer الذي يستعلم DB مجدداً في footer.blade.php:4 (لأن الـlayout يضبط $siteName فقط ولا يضبط $siteDescription، فيبقى الشرط !isset($siteDescription) صحيحاً دائماً ويعيد الاستعلام). 
- الحل: إنشاء resources/views/layouts/error.blade.php مستقل بلا أي استعلام DB ولا @include('components.footer') ولا Setting::getMany: <head> ثابت + CSS مضمّن inline (يمكن إعادة استخدام نفس critical CSS الموجود في auth.blade.php:37-41) + اسم موقع ثابت احتياطي، وجعل errors/403,404,429,500.blade.php ترثه بدلاً من layouts.auth. هذا يضمن ظهور صفحة الخطأ المُمَيَّزة حتى عند فشل DB/Cache. بديل أخف: لفّ قراءات ال

## [LOW/config-gap] site_tagline و meta_title/meta_description كإعدادات عامة: تُحرَّر/تُجلب في الأدمن لكنها لا تُعرَض في أي <head> عام
- النطاق: app/Http/Controllers/Admin/LandingPageController.php · app/Http/Controllers/SuperAdminController.php · resources/views/super-admin/landing-page.blade.php · (لا قارئ للعرض)
- الدليل: site_tagline: يُجلب في app/Http/Controllers/Admin/LandingPageController.php:17 و app/Http/Controllers/SuperAdminController.php:936، ويُحرَّر في resources/views/super-admin/landing-page.blade.php:459 و resources/views/admin/landing-page.blade.php:219، ويُحفظ (validate+Setting::set) في LandingPageController.php:39 و SuperAdminController.php:966 — لكن grep لـ setting('site_tagline') / 'tagline' في كل
- الحل: 1) إزالة الالتباس: site_tagline إما يُعرض (مثلاً تحت اسم الموقع في landing.blade.php أو شعار الهيدر) أو يُحذف من فورمي landing-page بما أن لا قارئ له. 2) في layouts/auth.blade.php:28 و auth-clean.blade.php:6 استبدال السلسلة الثابتة بـ @yield('meta_description', setting('site_description','...')) لتوحيد مصدر الوصف مع الصفحة العامة. 3) تصحيح فهم meta_title/meta_description: هما عمودا PageBuilder ولي

## [LOW/duplication] تكرار كامل لـ Avatar dropdown + رفع الصورة + منطق الإشعارات في كل لايوت دور بمعرّفات مختلفة
- النطاق: resources/views/layouts/admin.blade.php · teacher.blade.php · school-admin.blade.php (ومثيلاتها)
- الدليل: كتلة قائمة الأفاتار المنسدلة + رفع الصورة (fetch إلى profile.update-avatar) مكرّرة شبه حرفياً في 4 لايوتات أدوار ببادئات معرّفات مختلفة فقط: admin (avatarToggleBtn/avatarUploadInput) في admin.blade.php:447-486 (markup) و702-758 (JS)؛ teacher (tch) في teacher.blade.php:279-310 و429-474؛ school-admin (sch) في school-admin.blade.php:650-681 و1071-1116؛ super-admin (sa) في super-admin.blade.php:172-19
- الحل: استخلاص component موحّد <x-user-avatar-menu/> (markup القائمة + input الرفع) مع ملف JS مشترك واحد يقرأ المعرّفات عبر data-attributes بدل البادئات المتعددة، يُستدعى في اللايوتات الأربعة. ونقل حساب عدد الرسائل غير المقروءة إلى HeaderDataComposer الموجود (توسيع نطاق ربطه ليشمل layouts.teacher / school-admin / parent / student-app) أو إلى accessor مركزي في User (مثل unread_messages_count)، بحيث يُحسب 

## [LOW/dead-code] لايوت student.blade.php مهجور لكن لا يزال يحتوي منطقاً ومراجع — كود يتيم
- النطاق: resources/views/layouts/student.blade.php (مقابل student-app.blade.php الفعّال)
- الدليل: resources/views/layouts/student.blade.php موجود فعلاً ويحتوي: ألوان ثابتة بلا setting() — السطر 19 `background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)` (والسطر 36 نفس التدرج للشعار الدائري)؛ وهيدر/شعار ثابت — السطر 37 `<span>🏆</span>` والسطر 40 `<h1>بناء القيم</h1>`. بالمقابل layouts/student-app.blade.php:1-8 يقرأ من الإعدادات: `setting('font_family',...)`, `setting('primary_color',...)
- الحل: إزالة الكود الميت وتوحيد هوية الطالب: (1) حذف resources/views/layouts/student.blade.php بعد التأكد (مؤكّد هنا) من غياب أي `@extends`/`view`/`include` ثابت له. (2) تبسيط الـfallback في الملفات الثلاثة resources/views/messages/{index,show,bulk/inbox}.blade.php:1 بجعل الفرع الأخير يشير إلى 'layouts.student-app' بدل 'layouts.student' حتى لا يبقى مرجع لاسم محذوف ولا يُفعَّل لايوت ثابت لو أُضيف دور جديد
