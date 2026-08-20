> تقرير مُولَّد عبر Workflow تدقيق عزل الأدوار (6 وكلاء، 23 نتيجة، مثبّتة بـfile:line). تحليليّ — لا تنفيذ حتى اعتماد المالك.

# تقرير أمنيّ: تسرّب واجهة الطالب لحساب مدير النظام (super_admin)

**المنصّة:** أثيل مكة — Laravel 12 (إنتاج) · **البلاغ:** الدخول كأدمن ثمّ تغيير الرابط إلى `/student/dashboard` أو `/student/lesson/1` يعرض واجهة الطالب كاملةً بحساب الأدمن (XP 100، المستوى 1). · **الخلاصة:** البلاغ **مؤكَّد**، وجذره تصميميّ مقصود لكنّه معطوب الأثر. لا يتسرّب أيّ دورٍ آخر.

---

## 1. الجذر المباشر للبلاغ

التسرّب ناتجٌ عن **ثلاثة تجاوزات متطابقة الغرض للسوبر أدمن**، تعمل معاً على الطبقات الثلاث للتفويض:

| # | الملفّ:السطر | التجاوز | الأثر |
|---|---|---|---|
| 1 | `app/Http/Middleware/CheckRole.php:41-43` | `if (! $hasPermission && in_array('super_admin', $userRoles, true)) { $hasPermission = true; }` — تجاوزٌ **شامل** لأيّ مسار محروس بـ`role:` أيّاً كان الدور المطلوب | يعبر السوبر أدمن `role:student`، `role:teacher`، `role:parent`، `role:school_admin`، `role:technical_support` |
| 2 | `app/Http/Middleware/CheckSchoolAccess.php:19-21` | `if ($user && $user->isSuperAdmin()) { return $next($request); }` — يُمرّره بلا نطاق مدرسة | يتجاوز الحارس المدرسيّ على كل المجموعات المُنطَّقة |
| 3 | `app/Providers/AppServiceProvider.php:84-86` | `Gate::before(fn => $user->role==='super_admin' ? true : null)` — يمنحه كل `can:`/`@can`/`authorize()` | التوأم على طبقة الصلاحيات |

**آليّة الظهور المؤكَّدة:** بعد عبور الحارسين، متحكّم الطالب يعمل على `Auth::user()` بلا أيّ إعادة فحص دور. `StudentController::dashboard` (`StudentController.php:29`) يقرأ إحصاءات السوبر أدمن نفسه — فتظهر **XP 100 / المستوى 1** (قيم التلعيب الافتراضيّة لحساب بلا نشاط). و`StudentController::lesson` (`StudentController.php:487`) يُصيَّر لأنّ بوّابة القيمة تعتمد `Value::visibleForSchool(null)` التي تُرجِع **كلّ القيم النشِطة** عند غياب المدرسة (`StudentController.php:91` + `Value.php:64`).

**السطر 40 يوثّق النيّة صراحةً:** التعليق «السوبر آدمن لديه صلاحية الوصول لكل المسارات (يستطيع إنشاء/مراجعة محتوى المعلم)» — أي أنّ التجاوز **مقصود بالتصميم**، لكنّه يُدخِل السوبر أدمن تجاربَ الأدوار بحسابه هو بدل توفير معاينة معزولة. المالك يعتبره عطلاً، وهو محقّ: المراجعة الحقيقيّة تعيش أصلاً تحت `/admin`.

---

## 2. الخطورة الفعليّة — ليست مجرّد عرضٍ مربِك

التسريب **قراءةً** غير ضارّ بالمستخدمين الآخرين (لا يكشف بيانات طالب معيّن؛ يرى بياناته هو على قيم عامّة). لكنّ الخطر الحقيقيّ في **الفعل**، وهو مؤكَّد:

### مؤكَّد — السوبر أدمن يستطيع الفعل كطالب ويسكّ اقتصاداً لحسابه
- `submitActivity` **لا يحوي أيّ فحص `role==='student'`** (`StudentController.php:925`). الحارس الوحيد هو `isActivityAccessibleByStudent`.
- تلك البوّابة **تنهار عند `school_id=null`**: `Activity.php:489` — `if (! $valueId || ! $student->school_id) return true;` (تعليق «كأدمن اختبار»)، و`Activity.php:456` تُرجِع `true` لأيّ نشاط `all_schools_mode='direct'`.
- النتيجة: POST إلى مسار تسليم نشاطٍ منشور لكلّ المدارس ⇒ يُنشأ `ActivitySubmission` بـ`student_id=معرّف السوبر أدمن` ثمّ `Point::create`/`Coin::create` (`StudentController.php:1092, 1204, 1224`) — **سكّ XP/عملات على حساب السوبر أدمن وتلويث بيانات**.
- **حدّ الأثر:** محصورٌ بحساب السوبر أدمن؛ لا يظهر في صدارة الطلاب لأنّها تفلتر `role='student'`. فهو تلويث اقتصاد/بيانات ذاتيّ، لا تصعيد ضدّ مستخدمين. **الخطورة: متوسّطة.**

### مؤكَّد — تلويث طابور اعتماد المدرسة عبر متحكّم المعلّم
`storeActivity` بلا حارس `(! $school)` (بخلاف `dashboard` في `TeacherController.php:36`). السوبر أدمن يستطيع POST فيُنشأ نشاطٌ يتيم `created_by=معرّفه`, `school_approval_status='pending'` يشوّش طابور مدير المدرسة (`TeacherController.php:741, 773, 783`). **الخطورة: منخفضة** (يُرفَض بشريّاً).

### مؤكَّد — لا انهيار (لا 500) في أيّ متحكّم دور
- الطالب/الوليّ يُصيَّران بسلاسة (معالجة صريحة لـ`school_id=null`).
- المعلّم/مدير المدرسة يُوقَفان بـ`abort(403)` نظيف (`TeacherController.php:36`، `SchoolAdminController.php:39`).

### مؤكَّد — دفاع العمق قائمٌ على أفعال الملكيّة
أفعال وليّ الأمر (مدح/موافقة عبر `children()->exists()`) وتقييم المعلّم (`isReviewableByTeacher`) **تصدّ السوبر أدمن بـ403** لأنّه بلا أبناء/فصول (`ParentController.php:332, 521`، `TeacherController.php:191`). فلا يستطيع سكّ نقاط وليّ ولا اعتماد تسليمات.

### مؤشّر وعيٍ سابق بالتسريب
`TeacherController::previewActivity` (`TeacherController.php:851-855`) يحوي أصلاً تحويلاً خاصاً يكشف السوبر أدمن ويعيده إلى `admin.activities.show` — **سدٌّ نقطيّ لمسارٍ واحد** بينما التجاوز العامّ يترك البقيّة مكشوفة. دليلٌ أنّ المطوّرين رأوا التسريب وسدّوا ثقباً واحداً فقط.

---

## 3. مصفوفة الوصول عبر كل الأدوار

سلسلة الحرّاس على كل مجموعة (`routes/web.php`)، ومن يعبرها فعليّاً:

| مجموعة المسارات | الحرّاس | يصلها بحقّ | يتسرّب إليها |
|---|---|---|---|
| `/admin` (198-475) | `can:access-admin` | super_admin فقط | **لا أحد** (صعودٌ مسدود) |
| `/super-admin` (478) | `role:super_admin` | super_admin | — (تحويلات فقط) |
| `/school-admin` (493) | `role:school_admin` + `school.access` | school_admin | super_admin (عبر التجاوز) |
| `/teacher` (585) | `role:teacher` | teacher | super_admin |
| `/student` (673) | `role:student` + `school.access` | student | super_admin |
| `/parent` (730) | `role:parent` + `school.access` | parent | super_admin |
| `/support` (765) | `role:technical_support` | technical_support | super_admin |

**المسارات اليتيمة (تحت `auth` فقط، بلا `role:`/`can:`) — كلّها مشتركة بالتصميم أو مُقيَّدة داخليّاً:**

| المسار | الحالة |
|---|---|
| `messages/bulk/*` (156) | **مُقيَّد داخل المتحكّم:** `BulkMessageController.php:23` يُسقط 403 إن لم يكن الدور super_admin/school_admin (ملاحظة: يقرأ `$user->role` الأساسيّ فقط، fail-closed) |
| `messages/*` (166), `leaderboard/*` (183), `notifications/*` (723), `tickets/*` (754) | مشتركة عبر الأدوار عمداً — لا تكشف لوحة/وظيفة دورٍ خاصّ |
| `/editor/upload-image` (145), `/profile/update-avatar` (142), `/live/summary` (43), `survey submit` (52) | مشتركة لأيّ مستخدم مُوثَّق — رفعٌ/ملفّ شخصيّ، لا كشف وظيفة دور |

**النتيجة:** لا مسار يتيم يكشف لوحةً أو وظيفةً خاصّة بدورٍ لأيّ مستخدم. البلاغ (ب) سلبيّ.

---

## 4. هل تسرّب دورٌ آخر؟ — لا

**عزل الأدوار الأحاديّة سليم (مؤكَّد):** `CheckRole.php:38` يمنح المرور فقط إن كان `active_role` ضمن المطلوب أو تقاطع `getAllRoles` معه. لمستخدمٍ أحاديّ الدور، `getAllRoles=[دوره فقط]` فلا يتقاطع مع مجموعة غيره ⇒ 403. teacher لا يصل `/student`؛ student لا يصل `/teacher` ولا `/admin`؛ parent وtechnical_support معزولان. و`/admin` محروس بـ`access-admin`=العمود الأساسيّ super_admin حصراً (`AppServiceProvider.php:90`).

**لا تصعيد أدوار حيّ (مؤكَّد):** لا يمنح مستخدمٌ نفسه دوراً ثانويّاً — مسدودٌ بثلاث طبقات: `RoleSwitchController.php:18` (لا تبديل لدورٍ لا تملكه) + `User.php:691` (`switchRole` يعيد الفحص) + حارس `booted`. و`secondary_roles` لا يُكتب إلا من `UserManagementController` داخل `/admin`.

**لكن ثمّة خللان تصميميّان في منظومة الأدوار الثانويّة (محتمل الأثر، ليسا البلاغ الحاليّ):**

1. **«تبديل الدور» ليس عازلاً (متوسّط):** الطرف `array_intersect(getAllRoles, roles)` في `CheckRole.php:38` **يتجاهل `active_role` كلّياً**. فمستخدم بدورين شرعيّين (teacher+parent) يعبر حارسَي `/teacher` و`/parent` معاً أيّاً كان الدور «النشط». التبديل يغيّر لوحة التوجيه والعرض فقط، لا نطاق الوصول. ليس تصعيداً (الدوران مُسنَدان)، لكنّه يخالف توقّع العزل — وهو جوهر شكوى المالك معمّماً.

2. **تعريفٌ متضارب لـ«سوبر أدمن» (متوسّط):** تجاوز `CheckRole.php:41` يفحص `in_array('super_admin', getAllRoles())` (يشمل الثانويّ)، بينما `Gate::before` و`access-admin` يفحصان `$user->role` (الأساسيّ فقط). و`UserManagementController.php:76` يسمح بإدراج super_admin ضمن `secondary_roles`. النتيجة: حسابٌ أساسيّه teacher وثانويّه super_admin **يخترق كلّ مجموعات `role:` لكن يُرفَض من `/admin` وكلّ Gate** — التباسٌ يوسّع سطح التجاوز عبر ناقلٍ لا يراقبه المالك. **أيّ إصلاح للجذر يجب أن يحسم هذا التضارب صراحةً.**

3. **حارس `booted` يعتبر school_admin مُصرَّحاً بتعديل `role`/`secondary_roles` (منخفض، كامنٌ لا حيّ):** `User.php:51` يُدرج school_admin في `isPrivileged`، والسطر 71 يضمّ `role`/`secondary_roles`/`status` ضمن الحقول التي يتجاوزها المُصرَّح. لا مسار متحكّم يعرّض هذه الحقول لـschool_admin حاليّاً، لكنّ طبقة الموديل أوسع من طبقة المتحكّمات.

---

## 5. أثر الإصلاح — ما الذي يكسره تقييد التجاوز، وكيف نتجنّبه

**آمنٌ تماماً (مؤكَّد):**
- **لوحة `/admin` كلّها** محروسة بـ`can:access-admin` (`Gate::before` + `access-admin`)، لا بـ`role:`/`school.access` — فإزالة التجاوزَين لا تمسّها.
- **تدفّقات مراجعة محتوى المعلّم** (اعتماد الأنشطة/التسليمات) كلّها تحت `/admin`: `admin.pending-submissions` (249-252)، `admin.activity-approval` (441-447)، `admin.featured-activities` (294) — لا تحت `/teacher`. رغبة السوبر أدمن في «مراجعة محتوى المعلّم» مُلبّاة أصلاً بلا تجاوز.
- **تبديل الأدوار** لا يعتمد التجاوز: يمرّ عبر `CheckRole.php:38` مباشرةً؛ ومستخدمٌ أحاديّ الدور super_admin لا يملك أصلاً واجهة تبديل (`role-switcher` مخفيّ لمن ليس `hasMultipleRoles`).
- عند دخول السوبر أدمن مساراً `school.access` بعد الإصلاح: يلتقطه `abort(403)` في `CheckSchoolAccess.php:26` — **403 نظيف لا 500** (هو السلوك المرغوب).

**⚠️ لا تحذف `Gate::before` (كارثيّ):** حذفه يكسر كلّ `@can`/`authorize` داخل لوحة الأدمن. الإصلاح يستهدف الأسطر 41-43 و19-21 فقط، ويترك `Gate::before` و`access-admin` كما هما.

**الكسر الوحيد المؤكَّد — تافه:** `resources/views/layouts/super-admin.blade.php:213` يحوي رابطاً إلى `route('school-admin.test-notifications')` (مسار اختبار داخل `role:school_admin`، `routes/web.php:502`). بعد الإصلاح يعطي 403. هذا قالبٌ شبه ميّت (يستعمله عرضان فقط؛ التجربة الرئيسيّة على `layouts.admin`) والمسار صفحة تصحيح لا ميزة إنتاج.

**قرارٌ مطلوب من المالك:** هل يحتاج السوبر أدمن دخول `/support` فعليّاً؟ إن نعم، يُضاف الدور صراحةً لقائمة `role:` لتلك المجموعة بدل التجاوز الشامل.

---

## 6. خطّة الإصلاح المرتّبة (الأدقّ أوّلاً)

### الأولويّة 1 — سدّ الجذر: تقييد تجاوز السوبر أدمن في `CheckRole` (يُصلح web + API بضربة واحدة)
`bootstrap/app.php:50` يربط نفس `CheckRole` بـweb وapi، فالإصلاح يغطّي المسارين معاً (بما فيها `POST /api/v1/student/activities/{id}/submit`، `routes/api.php:40`).

**الملفّ:** `app/Http/Middleware/CheckRole.php:41-43` — **احذف** كتلة التجاوز الشامل:
```php
// احذف:
if (! $hasPermission && in_array('super_admin', $userRoles, true)) {
    $hasPermission = true;
}
```
إن أراد المالك إبقاء وصول السوبر أدمن لمجموعةٍ بعينها (مثل `/support`)، يُضاف الدور صراحةً لتلك المجموعة في `routes/web.php` عبر `role:technical_support,super_admin` بدل التجاوز الأعمى.

### الأولويّة 2 — سدّ التوأم المدرسيّ: `CheckSchoolAccess`
**الملفّ:** `app/Http/Middleware/CheckSchoolAccess.php:19-21`. بعد إصلاح الأولويّة 1، لن يصل السوبر أدمن أصلاً لمسارات `school.access` (الطالب/الوليّ/المعلّم/مدير المدرسة)، فيصير هذا التجاوز غير ذي أثرٍ على تلك المسارات. **يُترَك كما هو** إن لم يُطلَب عزلٌ إضافيّ؛ أو يُقيَّد لاحقاً إن أُريد منع السوبر أدمن حتى بعد تبديلٍ مستقبليّ. لا يُحذف مع الأولويّة 1 دون قرار — قد يُبقيه المالك لمسارات مدرسيّة تحت `/admin`.

### الأولويّة 3 — حسم تضارب تعريف «سوبر أدمن» (يمنع ناقل تصعيد الثانويّ)
وحّد التعريف عبر الطبقات. الأبسط والأأمن: بعد حذف تجاوز `CheckRole` (أولويّة 1)، يزول الاعتماد على `getAllRoles` للتجاوز تلقائيّاً. أبقِ `Gate::before`/`access-admin` على العمود الأساسيّ (`AppServiceProvider.php:85, 90` — كما هما). هذا يجعل «super_admin ثانويّ» بلا أيّ امتياز خفيّ. **قرار مصاحب:** راجع سماح `UserManagementController.php:76` بإدراج `super_admin` في `secondary_roles` — يُفضَّل منعه إن لم تكن له حالة استعمال.

### الأولويّة 4 — دفاع في العمق داخل متحكّم الطالب (اختياريّ لكن موصى به)
حتى بعد سدّ الجذر، أضِف تأكيد دورٍ صريحاً في نقاط الفعل:
- `StudentController.php:925` (`submitActivity`): تحقّق `active_role==='student'` قبل البوّابة.
- `TeacherController.php:741` (`storeActivity`) و`updateActivity`: أضِف `if (! $school) abort(403);` كبقيّة دوالّ المعلّم.
- شدّد `Activity.php:489`: لا تمنح `true` عند `school_id=null` إلا لمسار اختبار مقصود صراحةً.

### الأولويّة 5 — تنظيف الكود الميت والرابط المكسور
- احذف رابط `resources/views/layouts/super-admin.blade.php:213` (`school-admin.test-notifications`)، أو انقل المسار/العرض تحت مجموعة `/admin`.
- بعد أولويّة 1 يصير تحويل `TeacherController.php:851-860` (`previewActivity`) **كوداً ميتاً** (السوبر أدمن لن يصل المسار) — يُحذف بأمان.

### قرار تصميميّ منفصل (ليس عاجلاً) — عزل تبديل الأدوار
إن أراد المالك أن يعزل «تبديل الدور» فعلاً تجربةَ كلّ دور: اجعل `CheckRole.php:38` يعتمد `active_role` حصريّاً مع السماح بالتبديل بين الأدوار المملوكة، بدل الاتّحاد الدائم `array_intersect`. **تحذير:** يكسر أيّ اعتماد حاليّ على الوصول المتزامن لكل أدوار المستخدم متعدّد الأدوار — يحتاج مراجعةً مستقلّة، فلا يُدمَج مع إصلاح البلاغ.

---

**ملاحظة تحقّق:** ثلاثة أسطر الجذر تحقّقتُ منها مباشرةً في هذه الجلسة (`CheckRole.php:38-43`، `CheckSchoolAccess.php:19-35`، `AppServiceProvider.php:84-91`) وهي مطابقة تماماً. بقيّة الاستشهادات (المتحكّمات والمسارات) مستندة لتدقيق المحاور الخمسة بأدلّة `file:line`، وتُصنَّف **مؤكَّدة** حيث نصّ التقرير على ذلك.