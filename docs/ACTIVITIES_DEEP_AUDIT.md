> تقرير مُولَّد عبر Workflow تدقيق استقصائيّ (17 وكيلاً: 8 تنقيب → 8 تحقّق خصميّ → تركيب)؛ 20 خطأ مؤكَّد. تحليليّ — الإصلاح بعد اعتماد المالك.

# تقرير صحّة نظام الأنشطة — منصّة «أثيل مكة»

## ✅ حالة المعالجة (2026-08-25) — أُصلِح 20 من 20؛ الحزمة 741/741 خضراء

| الدفعة | الأخطاء | الكوميت |
|---|---|---|
| 1 — صحّة التصحيح/التأليف | H1 (تصحيح خاطئ صامت)، L1، L2، L3 | `fbeff4d` |
| 2 — تحصين تسليم الجوّال | H2 (سباق التسليم/منح مزدوج)، M6 (بوّابة التقييم القبليّ) | `d85fc6b` |
| 3 — الاقتصاد/المنح | M1، M2 (ازدواج/ابتلاع منح المراجعة)، M5 (توزيع الديمو) | `bed5eab` |
| 4 — العزل | M3 (تسريب ترتيب الإجابة عبر الجوّال)، M4 (عزل مراجعة المعلّم) | `5900992` |
| 5 — مؤقّت/عرض/توزيع | M7، M8، L5، L6، L7، L8 | `a511eea` |
| 6 — توحيد نقاط المعلّم + مؤقّت الجوّال | L4 (توحيد على updateTeacherPoints)، L9 (ختم بدء الكويز في الكاش) | `733afac` |

**كلّها مُصلَحة.** L4 وُحِّد على `updateTeacherPoints` (المصدر الواحد الحاوي للمكافآت، القيمة الآن ثابتة بلا تذبذب — قرار المالك بقبول تغيّر القيمة). L9 يُفرَض الحدّ الزمنيّ في الجوّال بختم بدءٍ في الكاش عند فتح النشاط (نظير session الويب). لا يتطلّب أيّ إصلاح migrate.

---

## 1. الحكم العامّ

نظام الأنشطة **سليمٌ في جوهره لكنّه ليس خالياً من الأخطاء**. التصحيح الآليّ والويب (المسار الحيّ الأساسيّ) محصَّنان جيّداً بعد الإصلاحات الأخيرة، لكن التدقيق الخصميّ اصطاد **20 خطأً مؤكَّداً** قابلاً للحدوث في الإنتاج، موزّعة:

- **حرِج (Critical): 0**
- **عالٍ (High): 2**
- **متوسّط (Medium): 8**
- **منخفض (Low): 10**

النمط الغالب: **مسار الجوّال/API متخلّف عن مسار الويب** في الحماية (سباق التسليم، بوّابة التقييم القبليّ، الحدّ الزمنيّ، خلط الترتيب، التوزيع)، إضافةً إلى **انحدارات موضعيّة في نماذج الأدمن** (مزامنة الخيارات) و**ازدواج/ابتلاع منحٍ عبر مسارات المراجعة المتعدّدة**. لا يوجد خطأ حرِج، لكنّ الخطأين العاليين يمسّان سلامة التصحيح والاقتصاد مباشرةً.

---

## 2. الأخطاء المؤكَّدة (مرتّبة: الأخطر أوّلاً)

### عالية الخطورة (High)

#### H1 — نموذج تعديل الأدمن: حذف خيار لا يُزامن `correct_index` ⇒ يُصحَّح الخيار الخطأ صحيحاً
- **المحور:** authoring-persistence
- **السيناريو:** الأدمن يعدّل سؤال اختيار متعدّد ويحذف خياراً يسبق الصحيح، فيبقى `correct_index` مشيراً لخيار مختلف. المصحّح يعتمد `correct_index` أوّلاً ويقارن دليل الطالب به متجاهلاً النصّ ⇒ **كلّ تسليمات الطلاب على هذا السؤال تُصحَّح خطأً**. `hasAnswerKey` يمرّ لأنّ `correct_index` موجود.
- **الأدلّة:** `resources/views/admin/activities/edit.blade.php:588` (splice بلا مزامنة — تأكّد يدويّاً) ↔ `resources/views/admin/activities/create.blade.php:552` و`resources/views/teacher/edit-activity.blade.php:450` (كلاهما يحوي المزامنة الصحيحة، فالنقص انحدار موضعيّ) · `app/Services/ActivityGradingService.php:336,382` · `resources/views/student/activity-view.blade.php:1505`
- **الإصلاح:** انسخ بلوك المزامنة من `create.blade.php:552-562` إلى `removeOption` في `edit.blade.php`: عند `oIndex === correct_index` احذف `correct_index+answer`؛ وعند `oIndex < correct_index` أنقص `correct_index`.

#### H2 — سباق تسليم في الجوّال يُنشئ صفّين ويضاعف النقاط/العملات
- **المحور:** submit-flow / attempts-status-display (نفس العطب)
- **السيناريو:** فحص الوجود `->first()` بلا `lockForUpdate` وبلا `DB::transaction` تحيط بالفحص+الإنشاء (بخلاف الويب). لا قيد `UNIQUE(student_id, activity_id)` — الموجود فهرس أداء غير فريد. طلبان POST متزامنان (نقرة مزدوجة/إعادة محاولة شبكيّة) يقرآن كلاهما `existing=null` فيُنشئان صفّين، وكلٌّ منح كامل XP/عملات مقابل `awarded_points=0` ⇒ **منح مزدوج + تجاوز نموذج التسليم الواحد**.
- **الأدلّة:** `app/Http/Controllers/Api/StudentApiController.php:348` (first بلا قفل — تأكّد يدويّاً)، `:417` (create خارج معاملة — تأكّد يدويّاً)، `:440` · `app/Http/Controllers/StudentController.php:1052` (الويب محميّ) · `database/migrations/2025_11_18_140931_create_activity_submissions_table.php:14` و`2026_05_04_000004_add_performance_indexes_v2.php:21` (لا قيد فريد)
- **الإصلاح:** لفّ الفحص (بـ`lockForUpdate`) + `create` في الجوّال داخل `DB::transaction` كالويب، و/أو إضافة `UNIQUE(activity_id, student_id)` مع معالجة `QueryException`.

---

### متوسّطة الخطورة (Medium)

#### M1 — مضاعفة منح عبر مساري مراجعة مختلفَي آليّة idempotency (أدمن ↔ معلّم)
- **المحور:** economy-awarding
- **السيناريو:** تسليمٌ يدويّ (pending) يعتمده السوبر أدمن عبر `PointsService::awardStudentPoints` (بلا `award_ledger`، ولا يُحدّث `awarded_points`)، ثمّ يفتحه المعلّم فيراجعه عبر `AwardService` بمفتاح `activity_submission/{id}` غير المُطالَب ⇒ **الطالب يُمنَح ضعف النقاط**. `isReviewableByTeacher` بلا حارس حالة.
- **الأدلّة:** `app/Http/Controllers/Admin/DashboardController.php:224,199` · `app/Services/PointsService.php:45` · `app/Http/Controllers/TeacherController.php:245` · `app/Models/ActivitySubmission.php:190`
- **الإصلاح:** توحيد مسار الأدمن على `AwardService` بنفس المفتاح مع تحديث `awarded_points`، أو حارس حالة في `submitReview` يرفض مراجعة تسليمٍ محسوم.

#### M2 — ابتلاع فرق تصحيح المعلّم مع رفع `awarded_points` كذباً
- **المحور:** economy-awarding
- **السيناريو:** المعلّم يعتمد تسليماً بدرجة 50 (يُمنَح 50)، ثمّ يُعيد مراجعته رافعاً إلى 90: مفتاح `AwardService` مُطالَبٌ ⇒ المنح no-op فلا يتلقّى الطالب الـ40، **ومع ذلك `awarded_points` يُضبَط 90** ⇒ الطالب مغبون + إفساد أيّ حساب فرقٍ لاحق.
- **الأدلّة:** `app/Http/Controllers/TeacherController.php:245,261` · `app/Services/AwardService.php:86` · `app/Models/ActivitySubmission.php:190`
- **الإصلاح:** ضمّ الدرجة للمفتاح (`activity_submission/{id}/{finalXp}`) أو حصر رفع `awarded_points` بنجاح المنح فعليّاً، مع حارس حالة على إعادة المراجعة.

#### M3 — تسريب الترتيب الصحيح عبر الجوّال في أنشطة ترتيب الكلمات/الجمل
- **المحور:** isolation-security
- **السيناريو:** `questionsForStudent` يحذف `is_correct` لكن **لا يخلط `options`** فتعود بالترتيب المخزَّن (الصحيح)، بينما الويب يخلط خادميّاً. أيّ عميل API مصادَق يقرأ `questions[0].options` بالترتيب الصحيح ويُعيد إرساله ⇒ **100% + سكّ اقتصاد**.
- **الأدلّة:** `app/Models/Activity.php:232` · `app/Services/ActivityGradingService.php:532,55` · `app/Http/Controllers/Api/StudentApiController.php:252` · `resources/views/student/activity-view.blade.php:1003` (الويب يخلط)
- **الإصلاح:** خلط `options` للأنواع الترتيبيّة داخل `questionsForStudent`، أو بثّ حقل `items` مخلوطاً منفصلاً عن مرجع التصحيح.

#### M4 — اختراق عزل المدرسة في مراجعة المعلّم (فرع `created_by` بلا قيد مدرسة)
- **المحور:** isolation-security
- **السيناريو:** `scopeReviewableByTeacher` فرع (ب) `created_by = teacher.id` بلا فحص `school_id`. عبر `referenceFromBank` (إسناد بلا نسخ يُبقي `created_by=A`) يُسلّم طالب مدرسة B على صفّ نشاط معلّم A ⇒ يظهر في طابور A فيكشف **PII عابر للمدارس** ويكتب على اقتصاد طالب مدرسة أخرى. المتحكّم يعتمد `isReviewableByTeacher` لا سياسة `review`.
- **الأدلّة:** `app/Models/ActivitySubmission.php:182` · `app/Http/Controllers/TeacherController.php:158,191,931,945` · `app/Models/Activity.php:405` · `app/Policies/ActivitySubmissionPolicy.php:74`
- **الإصلاح:** إضافة قيد مدرسة لفرع (ب) (`whereHas('student', school_id=teacher.school_id)`)، أو استبدال فحوص `isReviewableByTeacher` بـ`$user->can('review',$submission)`.

#### M5 — تسرّب حساب الديمو إلى تجميعات المعلّم/الوليّ/المدرسة (بلا حارس `is_demo`)
- **المحور:** economy-awarding
- **السيناريو:** `PointsDistributionService::awardTeacher/awardParent/awardSchool` لا تفحص `is_demo` على الطالب المصدر (خلافاً لـ`SchoolPoint::addPoints` و`TeacherPoint::updateTeacherPoints`). طالبٌ حقيقيّ يُوسَم ديموّاً ثمّ يُسلّم ⇒ **تضخّم نقاط معلّمه/وليّه وعدّاد مدرسته بنشاط ديمو**. `awardSchool` يتجاوز الحارس بـ`School::increment` مباشرةً.
- **الأدلّة:** `app/Services/Activity/PointsDistributionService.php:89,106,132` · `app/Models/SchoolPoint.php:46` · `app/Models/TeacherPoint.php:61` · `app/Http/Controllers/LeaderboardController.php:196,245` · `app/Http/Controllers/StudentController.php:1478`
- **الإصلاح:** حارس `is_demo` في `distribute` (تخطّي التوزيع كاملاً إن كان الطالب ديمو).

#### M6 — بوّابة التقييم القبليّ الإجباريّ غير مفروضة في الجوّال
- **المحور:** submit-flow
- **السيناريو:** الويب يستدعي `blockingPreAssessment` ويرجع 403؛ الجوّال يفحص `isAccessibleByStudent` فقط ⇒ طالب الجوّال يسلّم أنشطة درسٍ عليه تقييم قبليّ إجباريّ **دون إكماله** ويكسب النقاط.
- **الأدلّة:** `app/Http/Controllers/StudentController.php:947,835` · `app/Http/Controllers/Api/StudentApiController.php:327`
- **الإصلاح:** استدعاء نفس البوّابة في الجوّال قبل التصحيح/المنح.

#### M7 — الاختبار الموقوت: انتهاء الوقت لا يُرسِل إجابة غير مكتملة
- **المحور:** student-ui
- **السيناريو:** عند `remaining<=0` يُلغى المؤقّت ويُستدعى `requestSubmit`، لكنّ معالج submit المشترك يجد راديو غير محدَّد ⇒ `allAnswered=false` فيُظهر «الرجاء الإجابة» و`return` ⇒ **لا يُنشأ تسليم والمؤقّت أُلغِي**. مع `max_attempts=1` قد تضيع المحاولة الوحيدة (الخادم يرفض 422 بعد `duration*60+10`).
- **الأدلّة:** `resources/views/student/activity-view.blade.php:1444,1519,587` · `app/Http/Controllers/StudentController.php:965`
- **الإصلاح:** علم «قسريّ» عند التوقيت يتجاوز فحص `allAnswered` ويُرسل الجزئيّ (غير المُجاب فارغاً يُصحَّح كخطأ).

#### M8 — بطاقة النشاط «المرفوض» في `lesson-view` غير قابلة للنقر وتُوسَم «▶ ابدأ»
- **المحور:** attempts-status-display
- **السيناريو:** `rejectSubmission` يضبط `status='rejected'` والحالة تُنسَخ للبطاقة، لكن `onclick` يستثني `rejected` من القابليّة للنقر ولا فرع وسمٍ له ⇒ **طريق مسدود عن إعادة الإرسال** من المدخل الأساسيّ، رغم أنّ `resubmittable` يشمل `rejected`.
- **الأدلّة:** `resources/views/student/lesson-view.blade.php:694,720` · `app/Http/Controllers/StudentController.php:521,1061` · `app/Http/Controllers/SchoolAdminController.php:1213`
- **الإصلاح:** أضِف `rejected` لقائمة `onclick` وفرع وسمٍ صريح («يحتاج تعديلاً — أعِد الإرسال»).

---

### منخفضة الخطورة (Low)

| # | العنوان | المحور | الأدلّة الأساسيّة |
|---|---------|--------|------------------|
| L1 | `optionMatches` يمنح 100 لنصٍّ خاطئ عندما `correctIndex=0` (`(int)` أعمى بلا `is_numeric`) — عبر عميل API فقط | grading-engine | `ActivityGradingService.php:381,420,649` · `StudentApiController.php:370` |
| L2 | `normalizeAnswer` يُفسّر إجابة قصيرة صالحة كـJSON (`true/false/null/007/1.0`) فيكسر المطابقة — عبر الويب | grading-engine | `ActivityGradingService.php:262,475` · `activity-view.blade.php:1154` |
| L3 | تحويل نوع السؤال إلى MC في نموذجَي الأدمن لا يحذف `correct_index` المتقادم | authoring-persistence | `edit.blade.php:849,832` · `create.blade.php:838` · `teacher/edit-activity.blade.php:503` |
| L4 | كاتبان متضاربان على `teacher_points.points` بصيغتَي مصدر/تقريب مختلفتين (تذبذب لا مضاعفة) | economy-awarding | `PointsDistributionService.php:89` · `TeacherPoint.php:81,84` · `TeacherController.php:1993` |
| L5 | منحٌ مفقود للمعلّم/الوليّ/المدرسة في مسار موافقة الوليّ وكامل مسار الجوّال (`distributePoints` غير مستدعىً) | economy-awarding | `ParentController.php:551` · `StudentApiController.php:441` · `StudentController.php:1222` |
| L6 | فرع «التمرين» يطبع `{{ $option }}` بلا حارس نوع ⇒ 500 عند خيارات كائنيّة (بيانات غير قياسيّة فقط) | student-ui | `activity-view.blade.php:926,904` · `Activity.php:234` |
| L7 | تسليم مؤجَّل لموافقة الوليّ يعرض «الدرجة/النقاط» رغم `pending` وتأجيل المنح (`hasScore` يسبق `isPending`) | student-ui | `activity-view.blade.php:666,654,580` · `StudentController.php:1030` |
| L8 | `keepsBest` لا يحمي عند تصحيح المحاولة الجديدة `null` ⇒ تدهور `completed→pending` وطمس الإجابة الجيّدة | attempts-status-display | `StudentController.php:1069,1075` · `StudentApiController.php:383` · `ActivityGradingService.php:566` |
| L9 | الحدّ الزمنيّ للاختبار الموقوت (`quiz_duration`) غير مفروض في الجوّال (آليّة session خادميّة غير متاحة لـsanctum) | submit-flow | `StudentController.php:955` · `StudentApiController.php:370` |

---

## 3. حالة كل محور

| المحور | الحكم | ملاحظة |
|--------|-------|--------|
| **grading-engine** | مشاكل طفيفة (2) | العقد الأساسيّ سليم؛ خللان منخفضان في `optionMatches` (API) و`normalizeAnswer` (ويب، فئة إدخال ضيّقة) |
| **submit-flow** | فيه أخطاء (3) | مسار الجوّال متخلّف: سباق تسليم (H2)، بوّابة قبليّ (M6)، حدّ زمنيّ (L9). الويب محصَّن |
| **authoring-persistence** | فيه أخطاء (2) | انحدار موضعيّ في نماذج الأدمن: مزامنة حذف الخيار (H1)، تنظيف `correct_index` عند تحويل النوع (L3) |
| **approval-publishing** | سليم (0) | لم يُصطَد خطأ مؤكَّد جديد في هذا المحور |
| **economy-awarding** | فيه أخطاء (5) | ازدواج/ابتلاع منح عبر مسارات المراجعة (M1,M2)، تسرّب ديمو (M5)، تذبذب نقاط المعلّم (L4)، توزيع مفقود (L5) |
| **student-ui** | فيه أخطاء (3) | قسر المؤقّت المكسور (M7)، حارس نوع مفقود في التمرين (L6)، عرض درجة مضلِّل قبل موافقة الوليّ (L7) |
| **attempts-status-display** | فيه أخطاء (3) | سباق الجوّال (= H2)، مأزق النشاط المرفوض (M8)، تدهور `keepsBest` عند `null` (L8) |
| **isolation-security** | فيه أخطاء (2) | تسريب الترتيب الصحيح عبر الجوّال (M3)، اختراق عزل المدرسة في مراجعة المعلّم (M4) |

---

## 4. ما جرى التحقّق منه وثبت سليماً (طمأنة)

الإصلاحات الأخيرة **صامدة ولم تُبلَّغ كأخطاء جديدة**:

- **ترتيب الجمل/الكلمات:** `gradeOrdering` يُفضّل ترتيب `options` على `correct_answer` النصّيّ المفصول بفواصل، ويتجاهل `answer=''` الفارغ — صحيح وثابت.
- **التوجيه الأحاديّ الخاصّ** (`letter_choice/word_ordering/sentence_ordering/short_answer`) عند `count(questions)===1` إلى مُصحّحه قبل `gradeQuiz` — قائم. `manual_review` يفرض `null`، و`hasAnswerKey` يمنع حفظ MC/TF بلا مفتاح — صحيح.
- **حارس منع دمج أسئلة الترتيب/الحروف في اختبار متعدّد** (`count>1`) — قائم.
- **موافقة الوليّ لا تؤجَّل لطالبٍ بلا وليّ** — مؤكَّد في الجوّال (`StudentApiController:377` يشترط `parents()->exists()`) والويب.
- **عزل الأدوار:** السوبر أدمن لم يعد يخترق `/student`؛ `submitActivity` يشترط دور الطالب؛ استثناء الديمو من الإحصاءات/الصدارة قائم (باستثناء ثغرة توزيع نقاط المعلّم/الوليّ/المدرسة M5).
- **المسار الحيّ للويب** (`StudentController::submitActivity`) هو الأكثر تحصيناً: معاملة + `lockForUpdate` + بوّابة قبليّ + حدّ زمنيّ + توزيع نقاط — الأخطاء المؤكَّدة تتركّز في **القنوات البديلة** (الجوّال/API، ومسار موافقة الوليّ، ونماذج الأدمن) التي لم تُواكِب تحصين الويب.

**الخلاصة للمالك:** لا عطبٌ حرِج يستوجب إيقافاً فوريّاً، لكنّ **H1 (تصحيح خاطئ صامت لكلّ طلاب سؤالٍ مُعدَّل)** و**H2 (منح مزدوج عبر سباق الجوّال)** يستحقّان إصلاحاً عاجلاً لأنّهما يمسّان صحّة الدرجات والاقتصاد مباشرةً. أغلب المتوسّط/المنخفض علاجه **توحيد مسار الجوّال مع الويب** ومزامنة نماذج الأدمن مع نماذج المعلّم.