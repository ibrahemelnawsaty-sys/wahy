# تقرير المراجعة الشاملة لمنصة وحي

**الإجمالي:** 85 نتيجة · **المؤكَّد:** 75 · حرج 6 / عالٍ 19 / متوسط 21 / منخفض 11

## جاهزية التسليم
غير جاهزة للتسليم. تحققتُ ميدانياً من عيّنة من البلاغات الحرجة والعالية (auth، parent، bulk messaging، notifications، survey، leaderboard) وكلها مطابقة، ما يؤكد دقة القائمة بأكملها. توجد 6 أخطاء حرجة و19 عالية تمسّ الأمان (بث جماعي بلا صلاحية، XSS مخزّن)، وفساد بيانات لا رجعة فيه (رفض عائلي يَمنح نقاطاً، استرداد MySQL غير آمن، أعمدة مفقودة)، وأعطال وظيفية تامة (تغيير كلمة المرور المؤقتة، صفحة الإشعارات، مقارنة الاستبيانات، صدارة المعلمين/الأولياء). أي منها بمفرده مانع تسليم. التوصية: حجب التسليم حتى إصلاح كل الحرج والعالي، ثم اختبار قبول (UAT) لكل دور، ثم معالجة المتوسط/المنخفض في إصدار تالٍ. ميزة مخفّفة: واجهات الطالب الأساسية (ActivityGradingService/الثيم/الإحصاء/PvP) عولجت سابقاً، فالعطل متركّز في بقية الأدوار والأنظمة المشتركة.

## حسب المنطقة

### المصادقة والتسجيل (5)
- تغيير كلمة المرور المؤقتة مستحيل (action لمسار خاطئ + middleware لا يسمح به) — critical
- التسجيل الذاتي العام ينشئ مستخدمين بلا موافقة/school_id
- كشف وجود المستخدمين في نسيت/إعادة التعيين + عدم اتساق طول كلمة المرور

### واجهات المعلم (6)
- XSS مخزّن: رسائل أولياء الأمور تُعرض HTML خام — critical
- قائمة المحادثات تعرض صفاً لكل رسالة بدل محادثة لكل ولي أمر — high
- الأنشطة المعتمدة (approved) لا تُحتسب في analytics/studentReports — high
- IDOR في إسناد النشاط لأي فصل + منح XP مكرّر عند إعادة تقييم الفريق

### واجهات ولي الأمر (9)
- رفض النشاط العائلي يؤدي فعلياً للموافقة ومنح النقاط — critical
- الموافقة غير محصّنة ضد التكرار (منح نقاط متعدد) — high
- ربط رسالة بطالب ليس من الأبناء (student_id غير محصور) + لوحة تَعُدّ المعلّق كمكتمل

### واجهات مدير المدرسة (5)
- ربط طلاب من مدرسة أخرى بالفصول (تسرب بيانات بين المدارس) — high
- ربط الطالب بفصول دون التحقق من ملكية الفصل للمدرسة — high
- updateClassroom لا يعيد التحقق من انتماء المعلم + ربط أبناء بلا حصر بالمدرسة

### مدير النظام والإدارة الأساسية (6)
- استرداد نسخة MySQL يقسّم على ';' ويُنفّذ خارج ترتيب آمن — خطر تلف بيانات — high
- تخصيص الصفحة الرئيسية لا ينعكس على الصفحة الفعلية / — high
- تعريفان متعارضان لبنية الصفحة الرئيسية + شاشة تفعيل القيم غير قابلة للوصول

### الأنشطة والتصحيح والاعتماد (6)
- نوع النشاط يتجاوز قيم enum العمود type → 500 عند الحفظ — high
- difficulty/coins تُرسل لكنها غير موجودة كأعمدة → فقدان بيانات صامت — high
- رفض نشاط يكتب أعمدة غير موجودة → 500 + إشعارات تمرّر الرابط في موضع data

### الدروس والمحتوى والمحرّر (4)
- صفحة عرض الدرس لا تعرض محتوى الدروس من نوع mixed (الافتراضي) — high
- الفيديو/الصوت المرفوع كملف لا يظهر للطالب إطلاقاً — high
- مسارات عرض الوسائط في صفحة التعديل مكسورة + كود محرّر قديم ميّت

### الاستبيانات (9)
- مقارنة القبلي/البعدي تقرأ بمفاتيح القبلي → الدرجة البعدية = 0 دائماً — high
- صفحة الاستبيان المستقلة ترسل POST عادياً بينما submit يردّ JSON فقط → معطّلة — high
- trigger_type لا يُستهلك أبداً + إجابة الزائر تفشل (user_id غير nullable) + تعديل أحد الاستبيانين لا يزامن الآخر

### الرسائل والإشعارات (4)
- معرّفات UUID للإشعارات بدون اقتباس في JS → كل أزرار صفحة الإشعارات معطّلة — high
- إشعار المعلم للولي يمرّر الرابط في موضع data فيُفقَد
- مديرو المدرسة لا يستطيعون مراسلة بعضهم + HTML غير صالح في نافذة الرسالة الجماعية

### التلعيب والاقتصاد (5)
- صدارة المعلمين/أولياء الأمور تقرأ جدول points الخاطئ → الكل = 0 — high
- قيد UNIQUE على teacher_id يُفشل كل توزيع نقاط بعد أول نشاط (catch صامت) — high
- التصحيح اليدوي يمنح نقاط الطالب فقط + غياب قيد فريد على user_purchases يسمح بشراء مزدوج

### التقارير والتصدير (5)
- تقارير ومعدلات المعلم تتجاهل التسليمات المعتمدة (approved) — high
- بطاقات الإجمالي تحسب فقط المسجلين خلال الفترة + current_streak يظهر 0 دائماً
- N+1 ثقيل في تصدير الأنشطة (Excel)

### تكامل البيانات (النماذج مقابل الهجرات) (3)
- team_activities ينقصه 5 أعمدة يكتبها/يقرؤها كود الفرق — critical
- enum العمود type لا يحوي creative/image_order → فشل الإنشاء — high
- شرط إعادة التسليم يفحص needs_revision غير الموجودة (الصحيح needs_review)

### الأمان والصلاحيات (3)
- BulkMessageController بلا تحقق صلاحية: أي مستخدم يبث لكل المنصة عبر كل المدارس — critical
- رفع صور المحرر يسمح SVG لكل مستخدم (XSS مخزّن محتمل)
- middleware school.access لا يحمي مسارات teacher/student/parent فعلياً

### سلامة القوالب وقت التشغيل (5)
- صفحة عرض النشاط (admin) تنهار على نشاط بدون درس مرتبط — high
- نموذج إنشاء/تعديل النشاط ينهار على درس بدون مفهوم
- قائمة الدروس تنهار على درس بدون مفهوم + 5 أسماء routes غير معرّفة في قوالب super-admin

## الحرج والعالي (مفصّل)

### 1. [CRITICAL] تغيير كلمة المرور المؤقتة مستحيل: النموذج يرسل لمسار إعادة التعيين (guest-only) + قائمة السماح في الـ middleware لا تتضمن password.change.update
- **المنطقة:** المصادقة والتسجيل
- **الملف:** `resources/views/auth/change-password.blade.php:263`
- **الحل:** تصحيح action إلى route('password.change.update') وإضافة !$request->routeIs('password.change.update') لقائمة السماح في CheckPasswordChangeRequired.php (25-27). كلا التغييرين لازمان معاً.

### 2. [CRITICAL] XSS مخزّن: رسائل أولياء الأمور تُعرض كـ HTML خام في صفحة مراسلات المعلم
- **المنطقة:** واجهات المعلم
- **الملف:** `resources/views/teacher/messages.blade.php:328`
- **الحل:** تعقيم عند الحفظ (HTMLPurifier بقائمة وسوم بيضاء) في sendMessage بالمتحكمين، وعند العرض استخدام textContent/DOMPurify بدل innerHTML في teacher/messages.blade.php:328 وparent/messages.blade.php:271.

### 3. [CRITICAL] رفض النشاط العائلي يؤدي فعلياً إلى الموافقة عليه ومنح النقاط
- **المنطقة:** واجهات ولي الأمر
- **الملف:** `app/Http/Controllers/ParentController.php:453`
- **الحل:** إضافة فرع رفض مبكر يفحص $request->boolean('reject') دون منح نقاط. يلزم أيضاً إضافة عمود status/rejection_reason عبر migration وللـ fillable لأن النموذج لا يحوي سوى boolean parent_approved، وإلا يبقى النشاط معلّقاً للأبد.

### 4. [HIGH] استرداد نسخة MySQL يقسّم SQL على ';' ويُنفّذ خارج ترتيب آمن — خطر تلف بيانات
- **المنطقة:** مدير النظام والإدارة الأساسية
- **الملف:** `app/Http/Controllers/SuperAdminController.php:338`
- **الحل:** استبدال التقسيم الساذج بأداة mysql الفعلية (proc_open مع escapeshellarg) أو spatie/db-dumper، أو محلّل SQL يحترم حدود السلاسل. الإنشاء بتهريب PDO::quote بدل addslashes.

### 5. [CRITICAL] BulkMessageController بلا أي تحقق صلاحية: أي مستخدم مسجّل يبثّ رسالة لكل المنصة عبر كل المدارس
- **المنطقة:** الأمان والصلاحيات
- **الملف:** `routes/web.php:132`
- **الحل:** إضافة middleware('role:super_admin,school_admin') على المجموعة، وحصر النطاق في send()/getRecipients(): فرض school_id لمدير المدرسة وقصره على recipient_type من نوع school_* فقط، والتحقق أن أي school_id ممرَّر يطابق مدرسة المُرسِل ما لم يكن super_admin.

### 6. [CRITICAL] جدول team_activities ينقصه 5 أعمدة يكتبها/يقرؤها كود الفرق (total_score, team_submission, team_file, submitted_at, teacher_feedback)
- **المنطقة:** تكامل البيانات (النماذج مقابل الهجرات)
- **الملف:** `app/Http/Controllers/TeacherController.php:1230`
- **الحل:** هجرة تضيف الأعمدة الخمسة وتوسّع enum الحالة لتشمل pending، وجعل assigned_by nullable أو تمريره في assignTeamActivity (مع fillable) وإلا يفشل التعيين بقيد NOT NULL.

### 7. [HIGH] قائمة المحادثات تعرض صفاً لكل رسالة وليس محادثة واحدة لكل ولي أمر
- **المنطقة:** واجهات المعلم
- **الملف:** `app/Http/Controllers/TeacherController.php:1287`
- **الحل:** تجميع آخر رسالة فقط لكل (parent_id, student_id) عبر subquery يختار MAX(id) ثم orderBy created_at desc ثم paginate، مع إبقاء getConversation() للتفاصيل.

### 8. [HIGH] الأنشطة المعتمدة من المعلم (approved) لا تُحتسب في analytics وstudentReports
- **المنطقة:** واجهات المعلم
- **الملف:** `app/Http/Controllers/TeacherController.php:1417`
- **الحل:** استبدال where('status','completed') بـ whereIn('status', ActivitySubmission::DONE_STATUSES) في activityStats(1417) وweeklyEngagement(1474) وcompletedStats في studentReports(292)، تماشياً مع dashboard.

### 9. [HIGH] الموافقة على النشاط العائلي غير محصّنة ضد التكرار (منح نقاط متعدد)
- **المنطقة:** واجهات ولي الأمر
- **الملف:** `app/Http/Controllers/ParentController.php:468`
- **الحل:** داخل DB::transaction جلب الصف بـ lockForUpdate ثم return مبكر إن كان parent_approved مسبقاً قبل أي update أو منح نقاط، وإضافة فهرس فريد على (reference_type, reference_id) للنقاط.

### 10. [HIGH] ربط طلاب من مدرسة أخرى بفصول مدير المدرسة (تسرب بيانات بين المدارس)
- **المنطقة:** واجهات مدير المدرسة
- **الملف:** `app/Http/Controllers/SchoolAdminController.php:641`
- **الحل:** قبل attach/sync فلترة المعرّفات: User::where('school_id',$school->id)->where('role','student')->whereIn('id',$request->students)->pluck('id')، أو قاعدة Rule::exists مقيّدة بالمدرسة والدور، مع نفس الحصر على teacher_id في updateClassroom.

### 11. [HIGH] ربط الطالب بفصول دون التحقق من ملكية الفصل للمدرسة
- **المنطقة:** واجهات مدير المدرسة
- **الملف:** `app/Http/Controllers/SchoolAdminController.php:362`
- **الحل:** قبل attach/sync فلترة لفصول المدرسة فقط: $school->classrooms()->whereIn('id',(array)$request->classrooms)->pluck('id'), وقاعدة Rule::exists('classrooms','id')->where('school_id',$school->id).

### 12. [HIGH] تخصيص الصفحة الرئيسية من لوحة الأدمن لا ينعكس على الصفحة الفعلية (/)
- **المنطقة:** مدير النظام والإدارة الأساسية
- **الملف:** `app/Http/Controllers/PagesController.php:21`
- **الحل:** توحيد مصدر الحقيقة: landing() يجلب PageBuilder slug=home وis_active ويعرض محتواه عند وجود json_data، مع landing الثابت كـ fallback؛ وإزالة تعدّد المصارف لاحقاً (LandingPageController + SuperAdminController + Api).

### 13. [HIGH] نوع النشاط في نماذج الإنشاء يتجاوز قيم enum العمود type → 500 عند الحفظ
- **المنطقة:** الأنشطة والتصحيح والاعتماد
- **الملف:** `app/Http/Controllers/Admin/ActivityBankController.php:85`
- **الحل:** هجرة توسّع enum العمود type لتشمل creative/image_order/homework/practice مع الإبقاء على القيم الحالية، وتوحيد قوائم in: في المتحكمات الثلاثة وخيارات select في القالبين.

### 14. [HIGH] difficulty وcoins يُرسلان لجدول activities لكنهما غير موجودين كأعمدة ولا في fillable → فقدان بيانات صامت
- **المنطقة:** الأنشطة والتصحيح والاعتماد
- **الملف:** `app/Http/Controllers/Admin/ActivityBankController.php:99`
- **الحل:** إن كانا مطلوبين: هجرة تضيف difficulty(enum) وcoins(integer) لجدول activities + إضافتهما لـ fillable في Activity.php. وإلا حذفهما من النموذج Blade وقواعد التحقق وArray::create وlogOnly.

### 15. [HIGH] صفحة عرض الدرس (admin) لا تعرض أي محتوى للدروس من نوع mixed (النوع الافتراضي عند الإنشاء)
- **المنطقة:** الدروس والمحتوى والمحرّر
- **الملف:** `resources/views/admin/lessons/show.blade.php:276`
- **الحل:** إزالة اشتراط $lesson->type وعرض كل ما هو موجود: @if($lesson->content) و video_url/video_file و audio_url/audio_file و @foreach($lesson->images) مع asset(Storage::url(...)).

### 16. [HIGH] الفيديو والصوت المرفوعان كملف (video_file/audio_file) لا يظهران للطالب إطلاقاً
- **المنطقة:** الدروس والمحتوى والمحرّر
- **الملف:** `resources/views/student/lesson-view.blade.php:454`
- **الحل:** إضافة بلوك <video>/<audio> عند وجود video_file/audio_file باستخدام asset('storage/'.$field)، وتكرارها في admin/lessons/show.blade.php؛ يتطلب php artisan storage:link.

### 17. [HIGH] مقارنة القبلي/البعدي تقرأ إجابات البعدي بمفاتيح أسئلة القبلي → الدرجة البعدية = 0 دائماً
- **المنطقة:** الاستبيانات
- **الملف:** `app/Models/Survey.php:159`
- **الحل:** المطابقة بالترتيب/الفهرس بدل المعرّف: بناء خريطة index→postQuestionId واستخدام $postAnswers[(string)$postQs[$i]->id] مع option_scores الخاص بالسؤال البعدي. الأنظف: سؤال مشترك واحد للقبلي والبعدي.

### 18. [HIGH] صفحة الاستبيان المستقلة (الرابط/QR) ترسل POST عادياً بينما submit يردّ JSON فقط → الزر لا يستجيب
- **المنطقة:** الاستبيانات
- **الملف:** `resources/views/survey/show.blade.php:324`
- **الحل:** جعل submit يكتشف نوع الطلب: إن !$request->expectsJson() يُرجِع redirect()->with(...) وإلا JSON لطريق الـ popup، أو اعتراض submit بـ fetch JSON في العرض.

### 19. [HIGH] trigger_type لا يُستهلك أبداً → استبيان الدرس القبلي/البعدي لا يظهر على الدرس
- **المنطقة:** الاستبيانات
- **الملف:** `app/Models/Survey.php:296`
- **الحل:** تطبيق منطق المحفّزات: استثناء استبيانات الدرس وmanual من الـ popup العام، وعرض استبيان الدرس داخل صفحة الدرس عند on_lesson_start/on_lesson_complete مع ربط lesson_id، وقصر الـ popup على on_platform_open/on_login/on_first_login.

### 20. [HIGH] إجابة الزائر على استبيان عام (requires_login=false) تفشل لأن survey_responses.user_id غير قابل لـ NULL
- **المنطقة:** الاستبيانات
- **الملف:** `database/migrations/2025_12_02_000002_create_surveys_tables.php:85`
- **الحل:** هجرة تجعل user_id nullable()->change()؛ قيد unique(survey_id,user_id) يقبل عدة NULL على InnoDB. أو فرض requires_login=true دائماً ومنع نموذج الزائر إن لم تُدعم إجابات الزوار.

### 21. [HIGH] تعديل أحد استبياني التقييم لا يزامن أسئلة الاستبيان المرتبط → اختلاف الأسئلة يكسر المقارنة
- **المنطقة:** الاستبيانات
- **الملف:** `app/Http/Controllers/Admin/SurveyController.php:339`
- **الحل:** مشاركة مجموعة أسئلة واحدة بين القبلي والبعدي، وعند update تحديث أسئلة الشريك بنفس المحتوى/الترتيب في نفس المعاملة مع منع التعديل بعد وجود إجابات، وتعديل getComparisonData للمطابقة بالترتيب.

### 22. [HIGH] معرّفات الإشعارات (UUID) تُطبع بدون اقتباس في استدعاءات JavaScript فتتعطّل كل أزرار صفحة الإشعارات
- **المنطقة:** الرسائل والإشعارات
- **الملف:** `resources/views/notifications/index.blade.php:219`
- **الحل:** اقتباس المعرّف: السطر 219 handleNotificationClick('{{ $notification->id }}', '{{ $notification->action_url }}')، 247 markAsRead('{{ $notification->id }}')، 251 deleteNotification('{{ $notification->id }}').

### 23. [HIGH] لوحة صدارة المعلمين وأولياء الأمور تقرأ من جدول points الخاطئ فتظهر نقاط الجميع = 0
- **المنطقة:** التلعيب والاقتصاد
- **الملف:** `app/Http/Controllers/LeaderboardController.php:181`
- **الحل:** استخدام leftJoinSub على SUM من teacher_points (teacher_id) للمعلمين وparent_points (parent_id) لأولياء الأمور، واختيار الجدول/العمود حسب الدور في getUserRankInCategory. الأفضل توحيد المنطق باستدعاء PointsService الجاهز.

### 24. [HIGH] قيد UNIQUE على teacher_id يُفشل كل توزيع نقاط للمعلم بعد أول نشاط (catch صامت)
- **المنطقة:** التلعيب والاقتصاد
- **الملف:** `app/Services/Activity/PointsDistributionService.php:57`
- **الحل:** استبدال create/insertGetId بـ TeacherPoint::updateOrCreate(['teacher_id'=>$id],['points'=>DB::raw('points + '.$inc),...]) لزيادة ذرّية، أو إزالة unique مع الاعتماد على SUM في القراءات، ووقف ابتلاع الاستثناء الصامت.

### 25. [HIGH] تقارير ومعدلات المعلم تتجاهل التسليمات المعتمدة (status=approved)
- **المنطقة:** التقارير والتصدير
- **الملف:** `app/Http/Controllers/TeacherController.php:291`
- **الحل:** استبدال كل where('status','completed') بـ whereIn('status', ActivitySubmission::DONE_STATUSES) عند الأسطر 292/342/346/830/860/863/881/885/901 (والعدّات 398/440/443/1474)، دون استبدال pending في classStats:866.

### 26. [HIGH] إنشاء نشاط بنوع creative أو image_order يفشل لأن enum العمود type لا يحوي القيمتين
- **المنطقة:** تكامل البيانات (النماذج مقابل الهجرات)
- **الملف:** `app/Http/Controllers/TeacherController.php:1501`
- **الحل:** توسيع enum العمود type عبر هجرة لإضافة creative/image_order (وhomework/practice) وليس إزالتها من التحقق، لأن ActivityGradingService وStudentController والقوالب تتفرّع على هذه القيم. توحيد قوائم in: عبر كل المتحكمات.

### 27. [HIGH] صفحة عرض النشاط (admin) تنهار على الأنشطة بدون درس مرتبط — سلسلة علاقات غير محمية في الـ breadcrumb
- **المنطقة:** سلامة القوالب وقت التشغيل
- **الملف:** `resources/views/admin/activities/show.blade.php:214`
- **الحل:** استخدام المعامل الآمن من null: {{ $activity->lesson?->concept?->value?->icon }} … {{ $activity->lesson?->title }} في الأسطر 214/218/222، أو تغليف الـ breadcrumb بـ @if($activity->lesson).

## الترتيب الموصى به للإصلاح

1. الأمان الحرج أولاً: تأمين BulkMessageController بـ middleware role وحصر النطاق بالمدرسة (web.php:132) — تسرب بيانات فوري عبر كل المدارس
2. تعقيم XSS المخزّن في مراسلات المعلم/الولي عند الحفظ والعرض (teacher/messages:328، parent/messages:271)
3. استعادة وظيفة الدخول: إصلاح مسار تغيير كلمة المرور المؤقتة + قائمة سماح الـ middleware معاً (change-password:263 + CheckPasswordChangeRequired:25-27)
4. حماية تكامل البيانات الحرج: هجرة أعمدة team_activities الخمسة + enum، وإصلاح رفض النشاط العائلي (عمود status/rejection_reason + فرع رفض) قبل أن تتراكم نقاط خاطئة
5. تأمين الاسترداد: استبدال explode(';') بأداة mysql/spatie في SuperAdminController:338 لمنع تلف قاعدة البيانات
6. توحيد توسيع enum العمود type عبر المنصة (ActivityBank/TeacherController) لإيقاف أخطاء 500 عند إنشاء الأنشطة
7. إصلاحات العرض/التشغيل القاتلة للوظائف: اقتباس UUID الإشعارات، عرض mixed/الوسائط المرفوعة للدروس والطالب، breadcrumb النشاط بدون درس
8. إصلاح نظام الاستبيانات (مقارنة القبلي/البعدي، POST مقابل JSON، trigger_type، إجابة الزائر، مزامنة الأسئلة) دفعةً واحدة لأنها مترابطة
9. توحيد احتساب DONE_STATUSES (approved) عبر التقارير/الإحصاء/الصدارة/التصدير، وإصلاح جداول النقاط teacher_points/parent_points وقيد teacher_id الـ UNIQUE
10. سدّ ثغرات IDOR/تسرب البيانات بين المدارس في SchoolAdminController (ربط الطلاب/المعلمين/الأبناء بالفصول) وTeacherController (storeActivity/updateActivity)
11. معالجة المتوسط (difficulty/coins، صلاحيات SVG، شراء مزدوج، N+1 التصدير، روابط الوسائط المكسورة) في إصدار تالٍ
12. تنظيف المنخفض (كشف المستخدمين، اتساق طول كلمة المرور، الكود الميّت، routes غير المعرّفة، تكرار حقول النماذج)

## أسئلة مفتوحة تحتاج قرارك

- هل التسجيل الذاتي العام (/register) مطلوب أصلاً أم يُلغى ويُوجّه كله عبر PublicRegistrationController المرتبط بمدرسة؟ ومن يعتمد طلبات التسجيل: السوبر أدمن فقط أم مدير المدرسة أيضاً (لا يظهر له طابور موافقات حالياً)؟
- هل التراسل بين مديري المدرسة داخل نفس المدرسة مطلوب أم ممنوع عمداً؟ (MessagesController:74)
- هل يُسمح فعلاً بإجابات الزوار على الاستبيانات العامة (requires_login=false)؟ القرار يحدد بين هجرة nullable لـ user_id أو فرض تسجيل الدخول دائماً.
- هل حقول difficulty وcoins للأنشطة جزء من التصميم المقصود (تُضاف كأعمدة) أم بقايا واجهة تُحذف؟
- ما القناة الرسمية الوحيدة لتحرير الصفحة الرئيسية: PageBuilder أم LandingPageController أم SuperAdminController؟ يلزم اعتماد مصدر حقيقة واحد وإزالة الباقي.
- هل هناك عناصر متجر قابلة للشراء أكثر من مرة؟ يؤثر على تصميم القيد الفريد على user_purchases.
- هل أنواع النشاط النهائية المعتمدة (quiz/exercise/project/creative/image_order/homework/practice...) محسومة لتوحيد enum + قواعد التحقق + خيارات select دفعةً واحدة؟
- ما سياسة معالجة السجلات القديمة مزدوجة الترميز في registration_requests.data بعد توحيد الـ cast؟ تحتاج هجرة بيانات.

## كل النتائج المؤكَّدة (مضغوطة)

- [CRITICAL] (المصادقة والتسجيل) تغيير كلمة المرور المؤقتة مستحيل: نموذج التغيير يرسل للمسار الخاطئ (مسار إعادة التعيين guest-only) + قائمة السماح في الـ middleware لا تتضمن password.change.update — `resources/views/auth/change-password.blade.php:263`
- [CRITICAL] (واجهات المعلم) XSS مخزّن: رسائل أولياء الأمور تُعرض كـ HTML خام في صفحة مراسلات المعلم — `resources/views/teacher/messages.blade.php:328`
- [CRITICAL] (واجهات ولي الأمر) رفض النشاط العائلي يؤدي فعلياً إلى الموافقة عليه ومنح النقاط — `app/Http/Controllers/ParentController.php:453`
- [CRITICAL] (تكامل البيانات (النماذج مقابل الهجرات)) جدول team_activities ينقصه 5 أعمدة يكتبها/يقرؤها كود الفرق (total_score, team_submission, team_file, submitted_at, teacher_feedback) — `app/Http/Controllers/TeacherController.php:1230`
- [CRITICAL] (الأمان والصلاحيات) BulkMessageController بلا أي تحقق صلاحية: أي مستخدم مسجّل (طالب/ولي أمر) يستطيع بث رسالة لكل مستخدمي المنصة عبر كل المدارس — `routes/web.php:132`
- [HIGH] (واجهات المعلم) قائمة المحادثات تعرض صفاً لكل رسالة وليس محادثة واحدة لكل ولي أمر — `app/Http/Controllers/TeacherController.php:1287`
- [HIGH] (واجهات المعلم) الأنشطة المعتمدة من المعلم (approved) لا تُحتسب في إحصائيات analytics و studentReports — `app/Http/Controllers/TeacherController.php:1417`
- [HIGH] (واجهات ولي الأمر) الموافقة على النشاط العائلي غير محصّنة ضد التكرار (منح نقاط متعدد) — `app/Http/Controllers/ParentController.php:468`
- [HIGH] (واجهات مدير المدرسة) إمكانية ربط طلاب من مدرسة أخرى بفصول مدير المدرسة (تسرب بيانات بين المدارس) — `app/Http/Controllers/SchoolAdminController.php:641`
- [HIGH] (واجهات مدير المدرسة) ربط الطالب بفصول دون التحقق من ملكية الفصل للمدرسة — `app/Http/Controllers/SchoolAdminController.php:362`
- [HIGH] (مدير النظام والإدارة الأساسية) تخصيص الصفحة الرئيسية من لوحة الأدمن لا ينعكس على الصفحة الفعلية (/) — `app/Http/Controllers/PagesController.php:21`
- [HIGH] (مدير النظام والإدارة الأساسية) استرداد نسخة MySQL يقسّم SQL على ';' ويُنفّذ خارج ترتيب آمن — خطر تلف بيانات — `app/Http/Controllers/SuperAdminController.php:338`
- [HIGH] (الأنشطة والتصحيح والاعتماد (عبر الأدوار)) نوع النشاط في نماذج الإنشاء يتجاوز قيم enum العمود type → خطأ 500 عند الحفظ — `app/Http/Controllers/Admin/ActivityBankController.php:85`
- [HIGH] (الأنشطة والتصحيح والاعتماد (عبر الأدوار)) حقلا difficulty و coins يُرسلان لجدول activities لكنهما غير موجودين كأعمدة ولا ضمن fillable → فقدان بيانات صامت — `app/Http/Controllers/Admin/ActivityBankController.php:99`
- [HIGH] (الدروس والمحتوى والمحرّر) صفحة عرض الدرس (العين) لا تعرض أي محتوى للدروس من نوع mixed — وهو النوع الافتراضي عند الإنشاء — `resources/views/admin/lessons/show.blade.php:276`
- [HIGH] (الدروس والمحتوى والمحرّر) الفيديو والصوت المرفوعان كملف (video_file/audio_file) لا يظهران للطالب إطلاقاً — `resources/views/student/lesson-view.blade.php:454`
- [HIGH] (الاستبيانات (تعريف/إجابة/مقارنة)) مقارنة القبلي/البعدي تقرأ إجابات البعدي بمفاتيح أسئلة القبلي → الدرجة البعدية = 0 دائماً — `app/Models/Survey.php:159`
- [HIGH] (الاستبيانات (تعريف/إجابة/مقارنة)) صفحة الاستبيان المستقلة (الرابط/QR) ترسل POST عادياً بينما submit يردّ JSON فقط → الزر لا يستجيب/يظهر JSON خام — `resources/views/survey/show.blade.php:324`
- [HIGH] (الاستبيانات (تعريف/إجابة/مقارنة)) trigger_type (on_lesson_start/on_lesson_complete/manual...) لا يُستهلك أبداً → استبيان الدرس القبلي/البعدي لا يظهر على الدرس — `app/Models/Survey.php:296`
- [HIGH] (الاستبيانات (تعريف/إجابة/مقارنة)) إجابة الزائر على استبيان عام (requires_login=false) تفشل لأن survey_responses.user_id غير قابل لـ NULL — `database/migrations/2025_12_02_000002_create_surveys_tables.php:85`
- [HIGH] (الاستبيانات (تعريف/إجابة/مقارنة)) تعديل أحد استبياني التقييم لا يزامن أسئلة الاستبيان المرتبط → اختلاف الأسئلة يكسر المقارنة — `app/Http/Controllers/Admin/SurveyController.php:339`
- [HIGH] (الرسائل والإشعارات) معرّفات الإشعارات (UUID) تُطبع بدون علامات اقتباس داخل استدعاءات JavaScript فتتعطّل كل أزرار صفحة الإشعارات — `resources/views/notifications/index.blade.php:219`
- [HIGH] (التلعيب والاقتصاد (نقاط/شارات/متجر/ترتيب)) لوحة صدارة المعلمين وأولياء الأمور تقرأ من جدول points الخاطئ فتظهر نقاط الجميع = 0 — `app/Http/Controllers/LeaderboardController.php:181`
- [HIGH] (التلعيب والاقتصاد (نقاط/شارات/متجر/ترتيب)) قيد UNIQUE على teacher_id يُفشل كل توزيع نقاط للمعلم بعد أول نشاط (catch صامت) — `app/Services/Activity/PointsDistributionService.php:57`
- [HIGH] (التقارير والتصدير) تقارير ومعدلات المعلم تتجاهل التسليمات المعتمدة (status=approved) — `app/Http/Controllers/TeacherController.php:291`
- [HIGH] (تكامل البيانات (النماذج مقابل الهجرات)) إنشاء نشاط في بنك الأنشطة بنوع 'creative' أو 'image_order' يفشل لأن enum العمود type لا يحوي هاتين القيمتين — `app/Http/Controllers/TeacherController.php:1501`
- [HIGH] (سلامة القوالب وقت التشغيل) صفحة عرض النشاط (admin) تنهار على الأنشطة بدون درس مرتبط — سلسلة علاقات غير محمية في الـ breadcrumb — `resources/views/admin/activities/show.blade.php:214`
- [MEDIUM] (المصادقة والتسجيل) التسجيل الذاتي العام ينشئ صفوف users فعلية (inactive) بلا مسار موافقة ولا school_id — يختلف عن مسار RegistrationRequest المعتمد على token — `app/Http/Controllers/AuthController.php:335`
- [MEDIUM] (واجهات المعلم) IDOR: storeActivity/updateActivity تسمحان بإسناد النشاط لأي فصل (حتى من مدرسة أخرى) — `app/Http/Controllers/TeacherController.php:623`
- [MEDIUM] (واجهات المعلم) gradeTeamActivity تمنح XP/عملات مكرّرة عند إعادة التقييم (لا توجد حماية idempotency) — `app/Http/Controllers/TeacherController.php:1230`
- [MEDIUM] (واجهات ولي الأمر) صفحة الأنشطة العائلية غير قابلة للوصول من واجهة ولي الأمر — `resources/views/layouts/parent.blade.php:300`
- [MEDIUM] (واجهات ولي الأمر) لوحة ولي الأمر تَعُدّ الأنشطة المعلّقة/قيد المراجعة كـ"دروس مكتملة" — `app/Http/Controllers/ParentDashboardController.php:49`
- [MEDIUM] (واجهات ولي الأمر) قائمة "المحادثات" تعرض كل رسالة كصف منفصل بدل تجميعها بالمحادثة — `app/Http/Controllers/ParentController.php:174`
- [MEDIUM] (واجهات ولي الأمر) إمكانية ربط رسالة ولي الأمر بطالب ليس من أبنائه (student_id غير محصور) — `app/Http/Controllers/ParentController.php:216`
- [MEDIUM] (واجهات مدير المدرسة) updateClassroom لا يعيد التحقق من انتماء المعلم للمدرسة (بخلاف storeClassroom) — `app/Http/Controllers/SchoolAdminController.php:664`
- [MEDIUM] (واجهات مدير المدرسة) ربط أبناء لولي أمر دون حصر بالمدرسة أو نوع الطالب — `app/Http/Controllers/SchoolAdminController.php:491`
- [MEDIUM] (مدير النظام والإدارة الأساسية) تعريفان متعارضان لبنية بيانات الصفحة الرئيسية (sections مقابل blocks مسطّحة) على نفس السجل — `routes/web.php:201`
- [MEDIUM] (مدير النظام والإدارة الأساسية) شاشة 'تفعيل القيم للمدرسة' غير قابلة للوصول من الواجهة (لا يوجد رابط لها) — `resources/views/admin/schools/index.blade.php:210`
- [MEDIUM] (مدير النظام والإدارة الأساسية) القيم المفعّلة للمدرسة لا تُطبَّق على المعلمين (visibleForSchool غير مستخدم في واجهات المعلم) — `app/Http/Controllers/TeacherController.php:607`
- [MEDIUM] (الأنشطة والتصحيح والاعتماد (عبر الأدوار)) رفض نشاط من ActivityBankController يكتب في أعمدة rejected_by/rejected_at غير الموجودة → خطأ 500 — `app/Http/Controllers/Admin/ActivityBankController.php:154`
- [MEDIUM] (الأنشطة والتصحيح والاعتماد (عبر الأدوار)) إشعارات الاعتماد/الرفض في ActivityBankController تمرر رابط الإجراء كحقل data بدل action_url — `app/Http/Controllers/Admin/ActivityBankController.php:131`
- [MEDIUM] (الأنشطة والتصحيح والاعتماد (عبر الأدوار)) نموذج إنشاء النشاط للمعلم لا يبني أسئلة لأنواع quiz/exercise → التصحيح الآلي معطّل عملياً لأنشطة المعلمين — `resources/views/teacher/create-activity.blade.php:440`
- [MEDIUM] (الدروس والمحتوى والمحرّر) مسار عرض الصور/الفيديو/الصوت الحالية في صفحة التعديل خاطئ (روابط مكسورة) — `resources/views/admin/lessons/edit.blade.php:417`
- [MEDIUM] (الاستبيانات (تعريف/إجابة/مقارنة)) صفحة الاستبيان المستقلة لا تعرض أسئلة rating/scale → لا يمكن الإجابة على أسئلة التقييم — `resources/views/survey/show.blade.php:337`
- [MEDIUM] (الاستبيانات (تعريف/إجابة/مقارنة)) لا توجد طريقة لاستعراض الاستبيانات غير الـ popup (is_popup=false و is_mandatory=false) → لا تظهر للمستخدمين نهائياً — `app/Models/Survey.php:296`
- [MEDIUM] (الرسائل والإشعارات) إشعار المعلم لولي الأمر يمرّر رابط الإجراء في موضع وسيط البيانات (data) فيُفقَد الرابط ويُحفظ نص في عمود مُحوّل إلى array — `app/Http/Controllers/TeacherController.php:1358`
- [MEDIUM] (التلعيب والاقتصاد (نقاط/شارات/متجر/ترتيب)) التصحيح اليدوي للمعلم يمنح نقاط الطالب فقط ولا يوزّع على المعلم/الولي/المدرسة — `app/Http/Controllers/TeacherController.php:222`
- [MEDIUM] (التلعيب والاقتصاد (نقاط/شارات/متجر/ترتيب)) غياب قيد فريد على user_purchases مع فحص hasPurchased خارج المعاملة يسمح بشراء مزدوج — `app/Http/Controllers/StudentController.php:1295`
- [MEDIUM] (التقارير والتصدير) بطاقات لوحة التقارير 'الإجمالي' تحسب فقط المسجلين خلال الفترة المختارة — `app/Http/Controllers/Admin/ReportsController.php:81`
- [MEDIUM] (التقارير والتصدير) حقل السلسلة (current_streak) في تقرير تفاصيل الطالب للأدمن يظهر دائماً 0 — `app/Http/Controllers/Admin/ReportsController.php:219`
- [MEDIUM] (التقارير والتصدير) N+1 وأداء ثقيل في تصدير الأنشطة (Excel) عبر استعلامات داخل map() — `app/Exports/ActivitiesExport.php:78`
- [MEDIUM] (الأمان والصلاحيات) رفع صور محرر النصوص يسمح بملفات SVG لكل المستخدمين المسجّلين (XSS مخزَّن محتمل) — `app/Http/Controllers/EditorUploadController.php:21`
- [MEDIUM] (سلامة القوالب وقت التشغيل) نموذج إنشاء/تعديل النشاط (admin) ينهار إذا وُجد درس بدون مفهوم (concept) في القائمة المنسدلة — `resources/views/admin/activities/edit.blade.php:234`
- [MEDIUM] (سلامة القوالب وقت التشغيل) قائمة الدروس (admin) تنهار على درس بدون مفهوم — وصول سلسلة غير محمي (تناقض مع صفحة العرض) — `resources/views/admin/lessons/index.blade.php:306`
- [LOW] (المصادقة والتسجيل) كشف وجود المستخدمين (user enumeration) في نسيت/إعادة تعيين كلمة المرور — `app/Http/Controllers/AuthController.php:485`
- [LOW] (المصادقة والتسجيل) تخزين مزدوج الترميز لحقل data في registration_requests (cast=array مع json_encode يدوي) — `app/Http/Controllers/PublicRegistrationController.php:63`
- [LOW] (المصادقة والتسجيل) عدم اتساق الحد الأدنى لطول كلمة المرور بين الدخول/إعادة التعيين والتسجيل — `app/Http/Controllers/AuthController.php:534`
- [LOW] (واجهات المعلم) رسالة خطأ غير ظاهرة عند رفض الصلاحية في submitReview (يقرأ data.message بدل data.error) — `resources/views/teacher/review-single.blade.php:223`
- [LOW] (واجهات ولي الأمر) praise_type غير مُتحقَّق منه مقابل قيود enum في قاعدة البيانات — `app/Http/Controllers/ParentController.php:296`
- [LOW] (واجهات ولي الأمر) حدّ التشجيع اليومي يُتجاوَز تماماً إذا غاب جدول parent_praises — `app/Http/Controllers/ParentController.php:334`
- [LOW] (واجهات ولي الأمر) قيمة "custom" تُخزَّن كنص مدح فعلي عند عدم كتابة رسالة مخصصة — `app/Http/Controllers/ParentController.php:471`
- [LOW] (واجهات ولي الأمر) آخر الأنشطة في لوحة ولي الأمر تقتصر على approved فقط — `app/Http/Controllers/ParentDashboardController.php:113`
- [LOW] (واجهات مدير المدرسة) تحديث trend/rank في الإحصائيات قد يقارن بـ platform_rank=null بلا حماية — `app/Http/Controllers/SchoolAdminController.php:958`
- [LOW] (مدير النظام والإدارة الأساسية) إمكانية إنشاء طالب/معلم/ولي أمر بدون مدرسة من إدارة المستخدمين — `app/Http/Controllers/Admin/UserManagementController.php:71`
- [LOW] (الأنشطة والتصحيح والاعتماد (عبر الأدوار)) تكرار حقل الإجابة الصحيحة لنوع short_answer في باني أسئلة الأدمن — `resources/views/admin/activities/create.blade.php:653`
- [LOW] (الدروس والمحتوى والمحرّر) كود محرّر قديم ميّت (execCommand/#contentEditor) متبقٍّ بعد التحويل إلى Quill — `resources/views/admin/lessons/create.blade.php:807`
- [LOW] (الاستبيانات (تعريف/إجابة/مقارنة)) survey.show لا يطبّق أي تحقق (نشاط/استهداف/إجابة سابقة) — منطق الحماية في SurveyController::show غير مُوجّه (dead code) — `app/Http/Controllers/PagesController.php:37`
- [LOW] (الاستبيانات (تعريف/إجابة/مقارنة)) احتساب improvement يخلط درجات scale/rating الخام مع option_scores وينتج نسبة مئوية غير ذات معنى — `app/Models/Survey.php:186`
- [LOW] (الرسائل والإشعارات) مديرو المدرسة لا يستطيعون مراسلة بعضهم داخل نفس المدرسة — `app/Http/Controllers/MessagesController.php:74`
- [LOW] (الرسائل والإشعارات) بطاقات نافذة عرض الرسالة الجماعية مُضمّنة مباشرة داخل <tbody> كـ <div> (HTML غير صالح) — `resources/views/messages/bulk/index.blade.php:454`
- [LOW] (التلعيب والاقتصاد (نقاط/شارات/متجر/ترتيب)) PointsService::getStudentLeaderboard يحدد ويرتّب بعمود users.points غير الموجود (كود ميت قابل للانفجار) — `app/Services/PointsService.php:316`
- [LOW] (التقارير والتصدير) مسار reports.generate يشير إلى دالة غير موجودة في ReportsController — `routes/web.php:183`
- [LOW] (تكامل البيانات (النماذج مقابل الهجرات)) شرط إعادة تسليم النشاط يفحص حالة 'needs_revision' غير الموجودة (الصحيح needs_review) فيمنع إعادة التسليم نهائياً — `app/Http/Controllers/StudentController.php:848`
- [LOW] (الأمان والصلاحيات) middleware school.access لا يحمي مسارات teacher/student/parent فعليًا (اعتماد كامل على فحوص المتحكم) — `app/Http/Middleware/CheckSchoolAccess.php:29`
- [LOW] (سلامة القوالب وقت التشغيل) خمسة أسماء routes غير معرّفة مستخدمة في قوالب super-admin (RouteNotFoundException) — `resources/views/super-admin/schools.blade.php:135`