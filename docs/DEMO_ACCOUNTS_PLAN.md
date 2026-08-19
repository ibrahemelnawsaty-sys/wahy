> خطّة مُولَّدة عبر Workflow حصر خصميّ (9 وكلاء، 182 موقع تجميع مرصود، مثبّتة بـfile:line). تحليليّة — لا تنفيذ حتى اعتماد المالك.

# خطّة تنفيذ ميزة «حسابات الديمو» — منصّة وحي (أثيل مكة)

> حساباتٌ يعلّمها السوبر أدمن بعلمٍ واحد، تعمل بكامل وظائفها (تسجيل دخول، كلّ الميزات)، لكنّها **تُستثنى من كلّ تجميع/صدارة/تقرير على مستوى المنصّة**. الاستثناء للتجميعات فقط، لا حجبٌ عامّ.

---

## ✅ قرارات المالك المعتمدة (2026-08-19) — تُلغي البنود المفتوحة المقابلة في §8

1. **النطاق:** مدرسة ديمو كاملة **+** أفراد → نعتمد `users.is_demo` (الحاسم) **و**`schools.is_demo` (وراثة تلقائيّة + تبديل جماعيّ). الدفاع العميق على مستوى المستخدم مطلوب (قد يُدسّ فرد ديمو في مدرسة حقيقيّة).
2. **بريد الديمو:** تُرسَل الرسائل الأسبوعيّة/التذكيرات لحسابات الديمو **عاديّاً** → **يُحذف** حارس MailGate ومفتاح `email_send_to_demo` من الخطّة (§4.10 الشقّ الأوّل). **يبقى** الشقّ الثاني (تنقية الأرقام): لا يُحتسَب طالب ديمو في أرقام ملخّص شخصٍ آخر (دفاع عميق ضدّ الديمو المتفرّق).
3. **العدّاد الحيّ `schools.total_points`:** **حارس كتابة** في `SchoolPoint::addPoints` (تخطَّ الزيادة/الإدراج لمستخدم ديمو) + قراءة العمود المخزَّن تبقى. **يتطلّب:** إعادة تشغيل `RefreshSchoolStatistics` يدويّاً بعد النشر لإعادة حساب اللقطة الملوَّثة.
4. **التنظيف اللاحق:** يُضاف أمر artisan **`demo:purge`** (مسح حسابات الديمو ونقاطها/تسليماتها مع تأكيد) ضمن نطاق التنفيذ (دفعة ختاميّة).

---

## 1. الفكرة والقرار المعماريّ

**المصدر الواحد للحقيقة: `users.is_demo`** (boolean، `default false`، `not null`). كلّ استثناء يُشتقّ من هذا العمود على مستوى **صفّ المستخدم**، لأنّ جداول النقاط كلّها (`points` / `teacher_points` / `parent_points` / `school_points`) مرتبطة بـ`user_id`، فاستبعاد صفّ المستخدم يُسقِط مساهماته من كلّ `withSum`/`SUM(points)` تلقائيّاً.

**`schools.is_demo` عمود راحة ثانويّ** (اختياريّ، `default false`): يُستخدم لـ(أ) استبعاد مدرسة الديمو نفسها من صدارة/عدّ/مقامات المدارس، (ب) وراثة العلم تلقائيّاً للمستخدمين المُنشَئين تحتها، (ج) تبديل جماعيّ. لكنّه **ليس** مصدر الحقيقة للاستثناء في التجميعات — لأنّ مستخدم ديمو قد يقع داخل مدرسة حقيقيّة فيلوّث نطاقها، والعكس. **الحاسم دائماً `users.is_demo`.**

**لماذا لا Global Scope؟** لأنّ المتطلّب صريح: الديمو حساب **كامل الوظيفة** — يسجّل دخوله، يظهر لمعلّمه، يراسل، يستخدم كلّ الميزات. Global Scope على `User` سيحجبه من الاستعلامات العاديّة (تسجيل الدخول، العلاقات، الرسائل) فيكسر وظيفته. القرار: **نطاق صريح `->notDemo()` يُلحَق في مواقع التجميع فقط** — لا حجبٌ ضمنيّ.

**قاعدة العزل الذهبيّة:** التجميعات المقصورة على مدرسة واحدة ليست آمنة تلقائيّاً. حتى `single_school` يتلوّث إن دُسّ مستخدم ديمو داخل مدرسة حقيقيّة → نطبّق الفلترة على مستوى `users.is_demo` كدفاع عميق، لا نعتمد على انفصال المدرسة وحده.

---

## 2. نموذج البيانات

### 2.1 الهجرة
```
migration: add_is_demo_to_users_table
  $table->boolean('is_demo')->default(false)->index()->after('status');

migration: add_is_demo_to_schools_table   (اختياريّ لكن موصى به)
  $table->boolean('is_demo')->default(false)->index();
```
`default false` يجعل كلّ الحسابات القائمة غير-ديمو تلقائيّاً (سلوك آمن، بلا كسر).

### 2.2 `app/Models/User.php`
- **fillable** (سطر 83-102): أضِف `'is_demo'`.
- **casts()** (سطر 120-131): أضِف `'is_demo' => 'boolean'`.
- **الحارس الأمنيّ** — لا تضِف `is_demo` إلى مصفوفة `$sensitive` (سطر 65) لأنّها تعتبر `school_admin` مصرّحاً (سطر 51 `$isPrivileged`)، بينما `is_demo` **للسوبر أدمن حصراً**. أضِف فرعاً منفصلاً داخل `static::saving` (بعد سطر 62، قبل 64):
```php
if ($user->isDirty('is_demo')
    && ! app()->runningInConsole()
    && ! auth()->user()?->hasSuperAdminRole()) {
    abort(403, 'تعديل علم الديمو مقصور على السوبر أدمن');
}
```
`hasSuperAdminRole()` موجود (سطر ~643) ويشمل السوبر أدمن كدور ثانويّ.

- **الوراثة كمصدر واحد** — أضِف خطّاف `creating` داخل `booted()` (سطر 38-76) يغطّي **مواقع الإنشاء الـ11 كلّها** دفعةً واحدة:
```php
static::creating(function (self $u) {
    if (empty($u->is_demo) && $u->school_id
        && \App\Models\School::whereKey($u->school_id)->value('is_demo')) {
        $u->is_demo = true;
    }
});
```
يتطلّب `schools.is_demo`. إن رُفض عمود المدرسة، يُحذف الخطّاف وتُضبط نقاط التفعيل يدويّاً.

### 2.3 `app/Models/School.php` (إن اعتُمد schools.is_demo)
- `$fillable` (سطر 26-47): أضِف `'is_demo'`.
- `$casts` (سطر 49-53): أضِف `'is_demo' => 'boolean'`.
- لا حارس `booted` على School؛ التقييد يقع على الراوت (SchoolManagement مقصور على السوبر أدمن).

### 2.4 نقاط الإنشاء/الاستيراد/التسجيل (تُغطَّى بالوراثة التلقائيّة `creating`)
| الموقع | ملاحظة |
|---|---|
| `UserManagementController::store` (سطر 65/109)، `update` (134/183) | + checkbox صريح للسوبر أدمن (المصدر الرئيسيّ) |
| `StudentManagementController::store` (81)، `TeacherManagementController::store` (96)، `ParentManagementController::store` (82) | checkbox + وراثة عند اختيار مدرسة ديمو |
| `SchoolAdminController::storeTeacher` (263)، `storeStudent` (371)، `storeParent` (526)، `approveRequest` (804) | وراثة تلقائيّة فقط (لا checkbox — مدير المدرسة ليس سوبر أدمن) |
| `BulkUsersImport::buildUserAttributes` (201/213) | `school_id` ثابت → وراثة تلقائيّة عبر `creating` |
| `StudentsImport` (87) | **مستورِد ثانٍ موازٍ — تحقّق أنّ `school_id` مضبوط قبل `save()`** فيرث تلقائيّاً |
| `AuthController::register` (356) | التسجيل الذاتيّ: مدرسة جديدة غير-ديمو → `false` صحيح، لا checkbox |

---

## 3. آليّة الاستثناء الموحّدة

### 3.1 نطاق Eloquent على `User` (قرب `scopeActive` سطر 138)
```php
public function scopeNotDemo(Builder $q): Builder
{
    return $q->where($q->getModel()->getTable().'.is_demo', false); // تأهيل الاسم لينجو داخل الـ join
}
```
ونظيره على `School`: `scopeNotDemo => where('schools.is_demo', false)`.
يُلحَق مباشرةً بكلّ استعلام موضوعه `users` (صدارة الطلاب/المعلّمين/الأولياء، رتبة المستخدم، عدّادات اللوحات).

### 3.2 Helper للاستعلامات الخام (join/subquery التي لا تمرّ بـEloquent)
أنشئ `app/Support/DemoScope.php`:
```php
class DemoScope {
    public static function excludeUsers($query, string $alias = 'users') {
        return $query->where("$alias.is_demo", false);
    }
    public static function excludeSchools($query, string $alias = 'schools') {
        return $query->where("$alias.is_demo", false);
    }
    // لتنقية قائمة معرّفات قادمة من جدول خام (classroom_student/streaks)
    public static function notDemoIds(array $ids): array {
        return \App\Models\User::whereIn('id', $ids)->where('is_demo', false)->pluck('id')->all();
    }
    // قصاصة SQL موحّدة لـ whereRaw داخل subquery: AND u.is_demo = 0
    public static function sqlExclude(string $alias = 'u'): string {
        return " AND $alias.is_demo = 0 ";
    }
}
```

### 3.3 تطبيق على جداول النقاط
- `points` / `teacher_points` / `parent_points` **لا تحمل** `is_demo` → تُفلتَر عبر مالكها:
  - `withSum('points')` على صفّ User: يكفي `->notDemo()` على الاستعلام الخارجيّ (صفّ المستخدم مُستبعَد فتسقط نقاطه).
  - `DB::table('points')->join('users',…)`: أضِف `DemoScope::excludeUsers($sub)` داخل الـsubquery.
  - `whereRaw` subquery: أدرِج `DemoScope::sqlExclude('u')`.
- `school_points` والعدّاد الحيّ `schools.total_points`: **لا يُنقّى بعد التخزين** → يُحرَس عند الكتابة (§4.3).

---

## 4. قائمة تحقّق شاملة للمواقع (جوهر الخطّة — مجمَّعة، بلا تكرار)

> بعد إصلاح المواقع المخبَّأة: `optimize:clear` + رفع `lb:ver` وإبطال `parent_dashboard:ranks`.

### 4.1 الصدارة الحيّة — `LeaderboardController.php` (المصدر الأساسيّ)
| سطر | ماذا | التعديل |
|---|---|---|
| 140 | صدارة الطلاب (student, active, withSum points) | `->notDemo()` بعد شرط status (سطر ~142/155) |
| 192 | صدارة المعلّمين (teacher + teacher_points) | `->notDemo()` على استعلام User |
| 240 | صدارة الأولياء (parent + parent_points) | `->notDemo()` على استعلام User |
| **278** | `totalPointsSub` صدارة المدارس (join users role=student) | `DemoScope::excludeUsers($totalPointsSub)` |
| 284 | `studentsCountSub` | `->where('users.is_demo', false)` |
| 289 | `teachersCountSub` | `->where('users.is_demo', false)` |
| 294 | `School::where('status','active')` قائمة المدارس | `->where('schools.is_demo', false)` (School::notDemo) |
| 333/342 | `getUserRankInCategory` (المقام: من نقاطه أعلى) | `->where('users.is_demo', false)` على العادّ (338 نقاط المستخدم نفسه تبقى) |
| 374 | `getSchoolRank` (join + whereRaw) | `excludeUsers` في join (374-378) + `sqlExclude('u')` داخل whereRaw (381-384) + `schools.is_demo=0` على العادّ الخارجيّ |

### 4.2 نسخة PointsService المكرّرة — `PointsService.php`
دوالّ الصدارة (199 teacher / 242 parent / 288 school / 325 student) **بلا مستدعٍ في `app/`** (LeaderboardController يستخدم دوالّه الخاصّة) → **الأنظف حذفها**. إن أُبقيت: `->notDemo()` على كلٍّ + عدّادات school (298-306) `->where('is_demo',false)` + `getSchoolLeaderboard` (286) يقرأ عمود `total_points` المخبّأ فيعتمد على حراسة §4.3.

### 4.3 إحصاءات المدرسة + العدّاد الحيّ (الأخطر)
| ملف:سطر | ماذا | التعديل |
|---|---|---|
| `RefreshSchoolStatistics.php:52` | `totalPoints` (join points+users) | `excludeUsers` في الـjoin |
| `:58` | `monthlyPoints` | `excludeUsers` |
| `:66` | `platformRank` (whereRaw subquery) | `schools.is_demo=0` خارجيّاً + `sqlExclude('u')` داخل whereRaw |
| `:73` | `platformTotal` (School active count) | `->where('is_demo', false)` |
| `:79/82` | `cityRank`/`cityTotal` | `schools.is_demo=0` + `sqlExclude('u')` |
| `SchoolPoint.php:43` | `addPoints` يزيد `schools.total_points` الحيّ | **حارس كتابة:** إن كان `user_id` ديمو → تخطَّ الإدراج والزيادة (لا يمكن الترشيح بعد التخزين). **بديل موصى:** توقّف عن قراءة العدّاد الحيّ في أيّ صدارة واحسِب SUM المُرشَّح دائماً |
| `SchoolPoint.php:52` | `getTotalPoints` | إن أُبقيت صفوف الديمو: `whereHas('user', notDemo)`؛ الأفضل منعها عند الكتابة |
| `TeacherPoint.php:61` | `updateTeacherPoints` (10% من نقاط طلاب فصول المعلّم) | استثنِ طلاب الديمو من `studentIds` (54-58) عبر join users `is_demo=0`؛ ولا تُحدِّث لمعلّم ديمو |
| `SchoolAdminController.php:1372/1377` | `schoolPoints`/`monthly` (نقاط مدرسة المدير) | `excludeUsers` (دفاع عميق ضدّ ديمو داخل مدرسة حقيقيّة) |
| `SchoolAdminController.php:1384` | `allSchoolsRanked` (أوسع تسريب — يغذّي platform/country/city rank + top_schools 1478 + SchoolStatisticsCache) | `->where('schools.is_demo', false)` + `sqlExclude('u')` داخل selectRaw (1386) |

### 4.4 لوحات المنصّة/السوبر أدمن + KPI — `Admin/DashboardController.php`
| سطر | ماذا | التعديل |
|---|---|---|
| 22 | `User::count()` | `->notDemo()` |
| 23 | `School::count()` | `School::notDemo()` |
| 24-26 | عدّ teacher/student/parent | `->notDemo()` لكلٍّ |
| 29-30 | total/pending submissions | `whereHas('student', fn($q)=>$q->notDemo())` |
| 31 | active_students | `->notDemo()` |
| 47-48 | new_users / new_submissions اليوم | `->notDemo()` / `whereHas('student')->notDemo()` |
| 52 | recent_users (عرض) | `->notDemo()` أو وسم بصريّ |
| 68-80 | نموّ شهريّ (users + submissions) | `->notDemo()` / `whereHas('student')->notDemo()` |
| 96/103 | رسوم بيانيّة 7 أيّام | `->notDemo()` داخل الحلقة |
| 112 | top_students (صدارة مصغّرة) | `->notDemo()` قبل withSum |
| 119 | top_schools (withCount users role=student) | `School::notDemo()` + `->where('is_demo',false)` داخل إغلاق withCount |
| 155-159 | pendingSubmissions stats | `whereHas('student')->notDemo()` |

### 4.5 التقارير والتصدير — `Admin/ReportsController.php` + `app/Exports/*`
| ملف:سطر | ماذا | التعديل |
|---|---|---|
| ReportsController:70-71/80-82 | total students/teachers/schools | `->notDemo()` على الاستعلامات + `School::notDemo()` |
| :86/93 | total_submissions / active_students (فترة) | `whereHas('student')->notDemo()` / `->notDemo()` |
| :103 | topStudents (صدارة 10) | `->notDemo()` قبل withSum |
| :116 | activeSchools (سوبر أدمن فقط) | `School::notDemo()` + إغلاق withCount `is_demo=false` |
| :169/190 | `students()` قائمة | قائمة إدارة → **وسم بعمود «ديمو»** لا حذف؛ للتقرير الإجماليّ النقيّ `->notDemo()` |
| :271 | `schools()` withCount | إغلاقات withCount `is_demo=false` + `School::notDemo()` |
| :312/319 | `schoolDetail` (single) | `excludeUsers` في join النقاط + `->notDemo()` في topStudents (دفاع عميق) |
| :350/470 | `activities()` + exportPdf activities (عدّ/متوسّط submissions) | `whereHas('student', is_demo=false)` داخل withCount/withAvg |
| :440-459 | exportPdf students/teachers/schools | `->notDemo()` + `School::notDemo()` + إغلاقات withCount |
| `SchoolsExport.php:22/59` | عدّ + totalPoints (join points→users) | إغلاقات withCount `is_demo=false` + `excludeUsers` في join |
| `StudentsExport.php:26` / `ParentsExport.php:26` | قوائم صفّيّة | **عمود «حساب ديمو» + بارامتر `includeDemo`** لا حذف |
| `TeachersExport.php:56` | `studentsCount` مضمَّن | `->where('is_demo',false)` داخل `students()->count()` |
| `ActivitiesExport.php:81-83` | عدّ submissions | `with(['submissions'=>fn($q)=>$q->whereHas('student', is_demo=false)])` |
| `ValuesExport.php` | محتوى فقط | **لا استثناء** (لا يمسّ مستخدمين) |
| نقاط الاستدعاء `SuperAdminController:590-638` / `SchoolAdminController:1328` | — | لا تعديل مباشر، ترث من ملفّات Export |

### 4.6 سوبر أدمن + شارات رأس + Polling
| ملف:سطر | ماذا | التعديل |
|---|---|---|
| `SuperAdminController.php:1118/1168` | المتصلون أونلاين (join users + عدّ لكلّ دور) | `->where('users.is_demo', false)` في join الجلسات (1121) |
| `SuperAdminController.php:1062` | featuredActivities stats (distinct student) | `whereHas('student', notDemo)` على $base والعرض (1056) |
| `Support/DashboardController.php:24` | `User::count()` لوحة الدعم | `User::notDemo()->count()` |
| `HeaderDataComposer.php:29` | newUsersCount (شارة كلّ صفحات الأدمن) | `->notDemo()` داخل استعلام User (لا يمسّ RegistrationRequest) |
| `:39` | newSubmissionsCount | `whereHas('student')->notDemo()` |
| `:54` | pendingActivitiesCount (whereHas creator teacher) | `->where('is_demo', false)` داخل whereHas('creator') |
| `LiveUpdatesController.php:39` | pendingSubs Polling | `whereHas('student')->notDemo()` |
| `:49-58` | adminPendingActivities (whereHas creator) | `->where('is_demo', false)` داخل whereHas — **طابِق الرأس** |

### 4.7 تحليلات المعلّم + لوحة الوليّ (كسر العزل عبر المدارس)
| ملف:سطر | ماذا | التعديل |
|---|---|---|
| `ParentDashboardController.php:95` | countryRank للابن (منصّة) | `->where('is_demo', false)` على العادّ |
| `:87` | cityRank (multi-school join) | `excludeUsers` في join |
| `:78/68` | schoolRank/classRank (single) | `->where('is_demo', false)` دفاع عميق |
| `:193/198` | allSchoolsStats / schoolComparison | `schools.is_demo=0` + `users.is_demo=0` داخل كلّ CASE WHEN وفي points_agg |
| `TeacherController.php:2104/2124` | teacherLeaderboard global | `whereHas('teacher', notDemo)` على $query (2108) والرتبة (2143) — فرع local معزول |
| `:2154/2161/2177` | studentLeaderboard (city/country) | `->notDemo()` على القاعدة (2161) → يغطّي كلّ النطاقات دفعةً واحدة |
| `:1856/377` | analytics + student-reports (طلاب فصول المعلّم) | نقِّ `$studentIds` بـ`DemoScope::notDemoIds()` مرّة واحدة (دفاع عميق) |
| `:2252` | activityBank shared_activities | عدّ **محتوى** لا مستخدمين — ثانويّ؛ `whereHas('creator', notDemo)` إن أُريد |
| `SchoolAdminController.php:66/76` | لوحة المدير + top5 (single) | دفاع عميق فقط؛ داخل مدرسة الديمو يُعرض طبيعيّاً |
| `SchoolAdminController.php:1547-1604` | schoolStudents/city/country/grade/platform students + allStudentsCount | `->notDemo()` (1556 allStudentsCount = مقام الرتب، حرِج) — **ملاحظة: gradeStudents 1604 عابر للمدارس فعليّاً** (classroomIds 1596 بلا فلتر مدرسة) |
| `SchoolAdminController.php:1484-1499` | allTeachersRanked/monthly (join طلاب) | `->notDemo()` على المعلّمين + `sqlExclude` لطلاب الديمو داخل selectRaw |

### 4.8 API + تحليلات صدارة الطالب (single school قابل للتلوّث)
| ملف:سطر | التعديل |
|---|---|
| `Api/StudentApiController.php:498/516` | `->where('users.is_demo', false)` على القائمة والرتبة |
| `StudentController.php:667` | `->where('users.is_demo', false)` على $studentsQuery (myRank 714 يُصلَح تلقائيّاً) |

### 4.9 التلعيب/الشارات/المتجر
| ملف:سطر | التعديل |
|---|---|
| `Admin/BadgeController.php:51` | `withCount(['users as users_count' => fn($q)=>$q->where('users.is_demo', false)])` |
| `:121` | `loadCount(['users as users_count' => …is_demo=false])` |
| `:170` (حارس destroy) | **قرار منتج** — اتركه (سلامة أعلى) أو `is_demo=false` للسماح بحذف شارة لا يحملها إلّا ديمو |
| `Admin/ShopManagementController.php:20` | `total_purchases`: join user_purchases→users + `where('users.is_demo', false)` |
| `AwardService::award` / `SpendService::spend` / `GamificationService` (addXP/Coins) / `CheckBadgeEligibility::awardBadge` / `BadgeMetrics::compute` / `GamificationService::getStudentStats` | **لا استثناء** — منح/خصم/تقييم فرديّ، يعمل للديمو كطبيعيّ. التلوّث عند القراءة/التجميع لا الكتابة |

### 4.10 الرسائل الأسبوعيّة والحملات (شقّان: إرسال + أرقام)
**شقّ «لا تراسِل الديمو» — حارس مركزيّ واحد** في `app/Services/Mail/MailGate.php:20` (بعد جلب `$user`):
```php
if ($user && $user->is_demo && ! setting('email_send_to_demo', false)) return false;
```
يغطّي دفعةً واحدة: `SendWeeklyDigest:25`، `SendSchoolWeeklyDigest:27`، `SendTeacherWeeklyDigest:31`، `SendParentWeeklyDigest:24`، `NotifyInactiveChildren:45` (كلّها تمرّ عبر MailGate). مفتاح `email_send_to_demo` يتيح للمالك تفعيل الإرسال أثناء العرض دون تغيير كود.

**شقّ «لا تحسب الديمو في أرقام غيره» — لا يعالجه الحارس** (الرقم يُبنى قبل الإرسال):
| ملف:سطر | التعديل |
|---|---|
| `SendSchoolWeeklyDigest:31` | `->notDemo()` على استعلام طلاب المدير قبل pluck (ينظّف streaks 36 + count) |
| `SendTeacherWeeklyDigest:39` | `studentIds` من جدول خام → `DemoScope::notDemoIds($studentIds)` |
| `SendParentWeeklyDigest:33` | `->with(['children'=>fn($q)=>$q->notDemo()])` أو تخطِّ `$child->is_demo` |
| `NotifyInactiveChildren:40` | `->notDemo()` على `whereIn(studentIds)->where('role','student')` |

**الحملات تتجاوز MailGate** (تستخدم `Mail::to` مباشرةً):
| ملف:سطر | التعديل |
|---|---|
| `EmailCampaign.php:52` (`usersQuery` — المصدر الوحيد) | `->notDemo()` → ينظّف تلقائيّاً estimateRecipients (75/77) + DispatchCampaignJob (51) + total_recipients (72). `audience_type=custom` (إيميلات خام 62-71) لا يُفلتَر — يُترك مع ملاحظة |
| `DispatchCampaignJob:51/72` | تابع للمصدر أعلاه، لا تغيير مستقلّ |

**لا استثناء:** `CheckHomeworkDueDates:63` (تذكير per-user لطالب الديمو نفسه = وظيفة طبيعيّة).

---

## 5. زرّ التفعيل (واجهة السوبر أدمن)

- **نقطة تبديل فرديّ للمستخدم:** نظير `UserManagementController::toggleStatus` (سطر 220) →
  `toggleDemo(User $user) { $user->update(['is_demo' => ! $user->is_demo]); }` + راوت `admin.users.toggle-demo` (middleware سوبر أدمن). يمرّ عبر حارس `booted` فيتحقّق من الدور.
- **checkbox في نماذج create/edit** (`admin/users/*`, `admin/students/*`, `admin/teachers/*`, `admin/parents/*`) — **للسوبر أدمن فقط**، مع `'is_demo' => 'nullable|boolean'` في validate و`$request->boolean('is_demo')` قبل create/update.
- **وسم «ديمو» في القوائم:** شارة/badge بصريّة (لون مميّز) بجوار اسم المستخدم في قوائم إدارة المستخدمين/الطلاب/المعلّمين، وعمود «حساب ديمو» في ملفّات التصدير الصفّيّة.
- **تبديل جماعيّ للمدرسة:** نظير `SchoolManagementController::toggleStatus` (سطر 212) →
  `toggleDemo(School $school)` يقلب `schools.is_demo` + داخل معاملة `School::users()->update(['is_demo' => $newValue])` (query builder لا يمرّ بحارس model events — آمن من 403، ومقبول لأنّ الراوت مقصور على السوبر أدمن). + راوت `admin.schools.toggle-demo`. وأضِف checkbox في نماذج مدرسة create/edit (59/162).

---

## 6. الأمن والعزل

1. **حقل حسّاس للسوبر أدمن حصراً:** الحارس في §2.2 (`isDirty('is_demo')` → 403 لغير `hasSuperAdminRole()`). **لا** يوضع في `$sensitive` العامّة (تمنح school_admin الحقّ).
2. **المستخدم لا يفكّ علمه:** التسجيل الذاتيّ (`AuthController::register`, `PublicRegistrationController`) لا يوفّر checkbox ولا يشتقّ العلم إلّا من مدرسة موجودة `is_demo`؛ فلا يستطيع مستخدم تعليم/فكّ نفسه.
3. **لا يظهر للعملاء أثناء العرض:** وسم «ديمو» يُعرض في لوحات الإدارة فقط، لا في واجهات الطالب/المعلّم/الوليّ العامّة. الحساب يبدو طبيعيّاً تماماً للعميل الذي يُعرَض له.
4. **العزل الحاسم على مستوى المستخدم** (`users.is_demo`) لا المدرسة — يحمي حتى المدرسة الحقيقيّة من حساب ديمو مدسوس، ويمنع مدرسة الديمو من تلويث مقامات المنصّة.

---

## 7. دفعات التنفيذ المرتّبة

> **مبدأ الترتيب:** البنية التحتيّة أوّلاً (لا استثناء يعمل بلا عمود + scope)، ثمّ المواقع من الأعلى أثراً للأدنى، ثمّ العدّاد الحيّ (يتطلّب إعادة حساب)، ثمّ الواجهة.

**الدفعة 0 — البنية التحتيّة (migrate):**
هجرة `users.is_demo` (+`schools.is_demo`) + `fillable`/`casts` + `scopeNotDemo` (User+School) + `DemoScope` helper + حارس `booted` الأمنيّ + خطّاف الوراثة `creating`.
_اختبار:_ الهجرة default false؛ 403 عند تعديل غير-سوبر-أدمن؛ وراثة تلقائيّة عند إنشاء تحت مدرسة ديمو؛ التسجيل الذاتيّ = false.

**الدفعة 1 — الصدارة الحيّة (§4.1 + §4.8):**
LeaderboardController كامل + StudentApiController + StudentController. حذف/إصلاح دوالّ PointsService الميتة (§4.2).
_اختبار:_ طالب ديمو بنقاط عالية **لا** يظهر في أيّ صدارة؛ رتبة طالب حقيقيّ لا تتأثّر بديمو؛ مدرسة ذات طلاب ديمو لا يتضخّم مجموعها. `optimize:clear` + رفع `lb:ver`.

**الدفعة 2 — إحصاءات المدرسة + العدّاد الحيّ (§4.3، migrate/cron):**
RefreshSchoolStatistics + SchoolPoint حارس الكتابة + TeacherPoint + SchoolAdminController statistics/ranked.
_اختبار:_ `schools.total_points` لا يزداد بمنح لطالب ديمو؛ `platformRank`/`cityRank` تستثني مدرسة الديمو؛ **أعِد تشغيل `RefreshSchoolStatistics` يدويّاً بعد النشر لإعادة حساب اللقطة الملوَّثة**.

**الدفعة 3 — لوحات المنصّة + الرأس + Polling (§4.4، §4.6):**
DashboardController + HeaderDataComposer + LiveUpdatesController + SuperAdminController. **طابِق الرأس مع Polling** (نفس الاستثناء وإلّا اختلفت الأرقام).
_اختبار:_ عدّادات اللوحة والشارات تطابق بعضها وتستثني الديمو.

**الدفعة 4 — التقارير والتصدير (§4.5):**
ReportsController + كلّ ملفّات Exports (عمود «ديمو» + إغلاقات withCount).
_اختبار:_ topStudents في التقرير الرسميّ بلا ديمو؛ عمود «ديمو» يظهر في التصدير الصفّيّ؛ عدّادات المدارس لا تتضخّم.

**الدفعة 5 — تحليلات الأدوار + لوحة الوليّ (§4.7):**
ParentDashboardController + TeacherController + SchoolAdminController mini-leaderboards.
_اختبار:_ countryRank/cityRank للابن تستثني الديمو؛ teacherLeaderboard global نظيف؛ إبطال `parent_dashboard:ranks`.

**الدفعة 6 — التلعيب/المتجر + البريد/الحملات (§4.9، §4.10):**
BadgeController + ShopManagement + MailGate حارس + digest studentIds + EmailCampaign::usersQuery + مفتاح `email_send_to_demo`.
_اختبار:_ عدّ حائزي الشارة يستثني الديمو؛ حساب ديمو لا يتلقّى بريد digest؛ تقدير مستلِمي الحملة يستثنيه؛ الشراء الفرديّ للديمو **يعمل**.

**الدفعة 7 — الواجهة (§5):**
checkboxes + toggle routes + وسم «ديمو» في القوائم + تبديل جماعيّ للمدرسة.
_اختبار:_ زرّ التبديل الفرديّ/الجماعيّ؛ الوسم لا يظهر في واجهات العملاء؛ 403 لغير السوبر أدمن.

**اختبار شامل ختاميّ:** حساب ديمو كامل الوظيفة (تسجيل دخول، أنشطة، رسائل، شراء) **بينما** غائب عن كلّ صدارة/تقرير/عدّاد منصّة. اختبار «ديمو داخل مدرسة حقيقيّة لا يلوّث نطاقها».

---

## 8. حالات حافّة وقرارات معلّقة للمالك

1. **مدرسة ديمو كاملة أم مستخدمون متفرّقون؟** الموصى: **كلاهما مدعوم** — `schools.is_demo` للراحة (وراثة + تبديل جماعيّ) و`users.is_demo` هو الحاسم. القرار: هل تريد مدرسة ديمو مخصّصة واحدة للعروض، أم وسم أفراد داخل مدارس حقيقيّة؟ (الثاني يتطلّب الدفاع العميق في مواقع single_school).
2. **الملخّصات الأسبوعيّة:** هل يتلقّاها حساب الديمو؟ الافتراض **لا** (`email_send_to_demo=false`)، لكن المفتاح يتيح تفعيلها أثناء عرضٍ حيّ للعميل. **قرارك.**
3. **صدارة المدرسة الذاتيّة:** داخل مدرسة الديمو نفسها، هل تُحسب نقاط طلابها في صدارتها الداخليّة؟ **نعم** (يجب أن تبدو طبيعيّة لمديرها والعميل) — الاستثناء للمقامات المنصّيّة فقط.
4. **العدّاد الحيّ `schools.total_points`:** قرار تقنيّ — إمّا حارس كتابة في `SchoolPoint::addPoints` (يبقي العمود نظيفاً)، أو التوقّف عن قراءته والاعتماد على SUM المُرشَّح دائماً. الموصى: **الثاني أمتن** (لا يعتمد على تسلسل تاريخيّ للكتابات).
5. **حذف بيانات الديمو لاحقاً:** هل تريد أمر artisan (`demo:purge`) يمسح حسابات الديمو ونقاطها/تسليماتها بعد انتهاء دورة العروض؟ (خارج نطاق هذه الخطّة — يُقترح كتحسين لاحق).
6. **`audience_type=custom` في الحملات + `StudentsImport` الثاني:** الأولى (إيميلات خام) لا تُفلتَر بموثوقيّة؛ الثاني مستورِد موازٍ يجب التحقّق من ضبطه `school_id` قبل `save()`. **بندان يتطلّبان انتباهاً يدويّاً.**
7. **حارس حذف الشارة** (`BadgeController:170`): هل تسمح بحذف شارة لا يحملها إلّا حسابات ديمو؟ الافتراض: لا (سلامة أعلى).