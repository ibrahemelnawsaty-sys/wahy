All infrastructure and trigger points verified against the actual code. The 38 specs are accurate. Assembling the plan now — returning it directly as my output.

---

# خطّة تنفيذ البريد التلقائيّ متعدّد الأدوار — منصّة «أثيل مكة»

> بُنيت هذه الخطّة بعد **قراءة الكود الفعليّ**. أُكِّدت البنية التحتيّة التالية: `MailGate::send()` (توقيع مُتحقَّق منه)، قالب `emails/layouts/base.blade.php`، سِجلّ `EmailSettingsController::$types` (4 مفاتيح فقط اليوم)، تسجيل المستمعين في `AppServiceProvider::boot` عبر `Event::listen`، وعلاقات `User::parents()`/`children()`.

---

## 0. حقائق البنية التحتيّة المُتحقَّق منها (تحكم كل ما يلي)

### 0.1 توقيع `MailGate::send` — **تنبيه حرِج على مفتاح العلم**
```php
MailGate::send(?User $user, string $type, string $category, Mailable $mailable, ?string $toEmail = null): bool
```
`MailGate` يبني المفتاح داخليًّا: `setting('email_type_' . $type)`. لذلك:
- **يُمرَّر `$type` بلا بادئة** — أي `'parent_child_activated'` وليس `'email_type_parent_child_activated'`.
- مفاتيح `EmailSettingsController::$types` هي **النوع المجرَّد** (بلا بادئة) تمامًا كالمفاتيح الأربعة الحاليّة (`welcome`, `activity_graded`, …).
- في هذه الوثيقة، حقل `flagKey` في مواصفات المالك يحمل البادئة `email_type_`؛ **انزعها عند النداء وعند التسجيل**.

### 0.2 دلالة الفئة `$category` تحكم تجاوز إلغاء الاشتراك (`EmailPreference::allows`)
| الفئة | السلوك | الاستخدام |
|---|---|---|
| `transactional` | **حرِجة — تتجاوز `unsubscribed_all` دائمًا** (ضمن `CRITICAL`) | موافقات، إيصالات، بيانات دخول، طلبات إجرائيّة |
| `event` | تحترم تفضيل `events` + `unsubscribed_all` | إشعارات حدثيّة (تصحيح/شارة/تحدّي) |
| `digest` | تحترم تفضيل `digests` + `unsubscribed_all` | ملخّصات دوريّة |

> **قاعدة إلزاميّة:** لا تُصنَّف رسالة تسويقيّة/اختياريّة كـ`transactional` هربًا من الـopt-out. الفئة في المواصفات مضبوطة — التزِمها.
> `MailGate` مع `$user=null` (نداء عبر `$toEmail`) **يتخطّى فحص التفضيلات** (مناسب لبريد الأدمن/النظام مثل «تواصل معنا»).

### 0.3 المفتاح الرئيسيّ + التسجيل
- `email_master_enabled` يوقف كل البريد (زرّ طوارئ) — يحترمه `MailGate` أوّلًا.
- كل نوع جديد **يجب** أن يُضاف لـ`EmailSettingsController::$types` وإلّا لن يظهر في لوحة الأدمن (لكنّه سيعمل افتراضيًّا لأن `setting(...,true)`).

### 0.4 نمط الـMailable الموحّد (متوافق مع `base.blade.php`)
```php
class ParentChildActivatedMail extends Mailable
{
    use Queueable, SerializesModels;          // Queueable ⟵ إجباريّ (يُصفَّف تلقائيًّا)
    public function __construct(public User $student, public User $parent, public School $school) {}
    public function envelope(): Envelope { return new Envelope(subject: 'تم تفعيل حساب ابنك في '.setting('site_name','أثيل مكة')); }
    public function content(): Content {
        return new Content(view: 'emails.parent.child-activated', with: [
            'previewText'   => "تم تفعيل حساب {$this->student->name}",
            'unsubscribeUrl'=> route('email.unsubscribe', /* token */),
        ]);
    }
}
```
والقالب:
```blade
@extends('emails.layouts.base')
@section('title','تم تفعيل حساب ابنك')
@section('content')
    <p>مرحبًا {{ $parent->name }}،</p>
    <p>تم تفعيل حساب <strong>{{ $student->name }}</strong> في مدرسة {{ $school->name }}.</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ route('parent.child.show',$student->id) }}">متابعة ابنك</a></div>
@endsection
```
> **تحصين XSS إلزاميّ:** استخدم `{{ }}` دائمًا داخل قوالب البريد. لا `{!! !!}` على أيّ حقل مُدخَل من مستخدم (عنوان نشاط، اسم، تعليق، رسالة تواصل). دروس مراجعات الأمن السابقة (XSS مخزَّن عبر innerHTML/اقتباس ملاصق) تنطبق هنا أيضًا.

---

## 1. الجدول الشامل (38 بريدًا)

الفئات المختصرة: **T**=transactional، **E**=event، **D**=digest. الخطّاف: **EE**=حدث قائم نُوسّع مستمعه، **AL**=مستمع جديد على حدث، **CC**=نداء في متحكّم، **SC**=أمر مجدوَل.

### وليّ الأمر (Parent)
| # | الحدث | نقطة الإطلاق (الملف::الدالّة::السطر) | المستلِم | فئة | Mailable | خطّاف | أولويّة | جهد |
|---|---|---|---|---|---|---|---|---|
|P1|تفعيل حساب الابن|`SendWelcomeNotification::handle` (على `StudentRegistered` من `SchoolAdminController::approveRequest:844`)|`student->parents` (حلقة)|T|`ParentChildActivatedMail`|EE|high|S|
|P2|تصحيح نشاط الابن|`SendActivityGradedNotification::handle` (على `ActivityGraded` من `TeacherController::gradeSubmission:302`)|`submission->student->parents`|E|`ParentChildActivityGradedMail`|EE|high|S|
|P3|منح شارة للابن|`SendBadgeEarnedNotification::handle` (على `BadgeEarned`)|`user->parents` (إن `isStudent`)|E|`ParentChildBadgeEarnedMail`|EE|medium|S|
|P4|ترقية مستوى الابن|`SendLevelUpNotification::handle` (على `LevelUp` من `GamificationService:101`)|`student->parents`|E|`ParentChildLevelUpMail`|EE|low|S|
|P5|نشاط جديد لفصل الابن|`Admin\ActivityApprovalController::notifyClassroomStudentsOfApprovedActivity:204`|أولياء كل طالب مُشعَر|E|`ParentNewActivityMail`|CC|medium|M|
|P6|تسليم الابن يتطلّب موافقة الوليّ|`StudentController::submitActivity:981/1007`|`submission->student->parents`|T|`ParentApprovalRequiredMail`|CC|high|M|
|P7|غياب الابن يومين متتاليين|أمر مجدوَل جديد يمسح `streaks`|`streak->user->parents`|T|`ParentChildInactiveMail`|SC|medium|M|
|P8|ملخّص أسبوعيّ للابن|أمر مجدوَل جديد (نظير `SendWeeklyDigest`)|`role=parent` → `children()`|D|`ParentWeeklyDigestMail`|SC|low|L|

### مدير المدرسة (School Admin)
| # | الحدث | نقطة الإطلاق | المستلِم | فئة | Mailable | خطّاف | أولويّة | جهد |
|---|---|---|---|---|---|---|---|---|
|SA1|طلب تسجيل معلّم|`PublicRegistrationController::registerTeacher:84-93`|كل `school_admin` بالمدرسة (حاليّ `first()`)|E|`NewRegistrationNotificationMail` (موجود)|CC|high|S|
|SA2|طلب تسجيل طالب|`PublicRegistrationController::registerStudent:184-193`|كل `school_admin`|E|`NewRegistrationNotificationMail`|CC|high|S|
|SA3|طلب تسجيل وليّ أمر|`PublicRegistrationController::registerParent:267-276`|كل `school_admin`|E|`NewRegistrationNotificationMail`|CC|medium|S|
|SA4|نشاط معلّم بانتظار اعتماد المدير|`TeacherController::notifySchoolAdminsOfPendingActivity:2066` (يُنادى من 5 مواضع: 836/943/1092/1136/2012)|كل `school_admin` بالمدرسة|E|`ActivityPendingApprovalMail`|CC|high|M|
|SA5|اكتمال استيراد بالجملة|`SchoolAdminController::importUsers:1261-1305`|المدير الفاعل `Auth::user()`|T|`BulkImportSummaryMail`|CC|low|M|
|SA6|ملخّص أسبوعيّ للمدرسة|أمر مجدوَل جديد (استعلامات `dashboard`)|كل `school_admin`|D|`SchoolWeeklyDigestMail`|SC|low|L|

### المعلّم (Teacher)
| # | الحدث | نقطة الإطلاق | المستلِم | فئة | Mailable | خطّاف | أولويّة | جهد |
|---|---|---|---|---|---|---|---|---|
|T1|تسليم طالب بانتظار المراجعة|`StudentController::submitActivity:~999` + `Api\StudentApiController::store:~415`|معلّمو فصول الطالب|E|`TeacherSubmissionPendingMail`|AL (حدث جديد)|high|M|
|T2|طالب جديد أُضيف لفصل المعلّم|`SchoolAdminController::storeClassroom:684`/`updateClassroom:746` + `BulkUsersImport`|`classroom->teacher`|T|`TeacherNewStudentMail`|CC|medium|M|
|T3|الأدمن اعتمد نشاط المعلّم|`Admin\ActivityApprovalController::approve:108`/`bulkApprove:185`|`activity->created_by`|T|`TeacherActivityApprovedMail`|CC|high|S|
|T4|الأدمن رفض نشاط المعلّم|`Admin\ActivityApprovalController::reject:146`|`activity->created_by`|T|`TeacherActivityRejectedMail`|CC|high|S|
|T5|طالب قيّم المعلّم|`StudentController::submitRating:1778`|`request->teacher_id`|D|`TeacherNewRatingMail`|CC|low|S|
|T6|وليّ أمر رُبط بطالب في فصله|`SchoolAdminController::storeParent:535`/`updateParent:590` + `BulkUsersImport:186`|معلّمو فصول الأطفال المرتبطين|D|`TeacherParentLinkedMail`|SC (digest)|low|L|

### الأدمن (Admin)
| # | الحدث | نقطة الإطلاق | المستلِم | فئة | Mailable | خطّاف | أولويّة | جهد |
|---|---|---|---|---|---|---|---|---|
|A1|نشاط بانتظار الاعتماد النهائيّ|`SchoolAdminController::approveActivity:966-976`|`admin`+`super_admin`|E|`ActivityPendingApprovalMail` (مُعاد استخدامه)|CC|high|M|
|A2|طلب تسجيل مدرسة جديد|`AuthController::register:372-386` (فرع `school_admin`)|`admin`+`super_admin`|E|`NewSchoolRegistrationMail`|CC|high|M|
|A3|رسالة «تواصل معنا»|`ContactController::store:60-82`|`setting('contact_email')` عبر `$toEmail`|T|`ContactMessageMail`|CC|medium|S|

### الدعم الفنيّ / السوبر أدمن / صاحب التذكرة (any)
| # | الحدث | نقطة الإطلاق | المستلِم | فئة | Mailable | خطّاف | أولويّة | جهد |
|---|---|---|---|---|---|---|---|---|
|S1|تذكرة دعم جديدة|`TicketController::store:72-83`|`technical_support`+`super_admin`|E|`NewSupportTicketMail`|CC|high|M|
|S2|ردّ صاحب التذكرة على المفتوحة|`TicketController::reply:143-163`|`assigned_to` أو (support+super)|E|`SupportTicketReplyMail`|CC|medium|M|
|S3|تصعيد تذكرة للسوبر أدمن|`SupportTicketController::escalate:205-219`|`super_admin`|E|`TicketEscalatedMail`|CC|high|S|
|S4|ردّ الدعم على المستخدم (لصاحبها)|`SupportTicketController::reply:115-121`|`ticket->user`|T|`TicketRepliedMail`|CC|high|M|
|S5|حلّ/إغلاق تذكرة (لصاحبها)|`SupportTicketController::resolve:146-152`|`ticket->user`|T|`TicketResolvedMail`|CC|medium|S|

### الطالب (Student)
| # | الحدث | نقطة الإطلاق | المستلِم | فئة | Mailable | خطّاف | أولويّة | جهد |
|---|---|---|---|---|---|---|---|---|
|ST1|نشاط/واجب جديد متاح|`Admin\ActivityApprovalController::notifyClassroomStudentsOfApprovedActivity`|طلاب الفصل (`isAccessibleByStudent`)|E|`NewActivityMail`|CC|high|S|
|ST2|تذكير واجب خلال 24س|`CheckHomeworkDueDates::handle` كتلة `upcomingHomework:40`|طلاب يرون الواجب بلا تسليم|T|`HomeworkDueReminderMail`|SC|high|S|
|ST3|واجب متأخّر|`CheckHomeworkDueDates::handle` كتلة `overdueHomework:89` (حارس `alreadyNotified:123`)|طلاب بلا تسليم (مرّة واحدة)|T|`HomeworkOverdueMail`|SC|medium|S|
|ST4|محطّة سلسلة أيّام|مستمع جديد على `StreakUpdated` (يُطلَق في `StreakService::touch:74` عند milestone)|`event->student`|E|`StreakMilestoneMail`|AL|medium|S|
|ST5|دعوة PvP موجّهة|`StudentController::challengeOpponent`|`opponent` (player2)|E|`PvpInviteMail`|CC|low|S|
|ST6|قبول/رفض دعوة PvP|`StudentController::acceptPvpInvite`/`declinePvpInvite`|`match->player1`|E|`PvpInviteResponseMail`|CC|low|S|
|ST7|نتيجة مباراة PvP|`StudentController::submitPvpAnswers` (فرع `bothSubmitted`، `$newlyAwarded`)|`winner`(+خاسر اختياريّ)|E|`PvpResultMail`|CC|low|M|
|ST8|هديّة عملات من الوليّ|`ParentController::sendGift`|`child`|E|`CoinsGiftMail`|CC|low|S|
|ST9|إيصال شراء/استبدال|`StudentController::purchaseItem`/`redeemReward`|المشتري `Auth::user()`|T|`PurchaseReceiptMail`|CC|low|S|
|ST10|استبيان تقييم متاح|`Admin\SurveyController::store` (فرع `target_type`)|طلاب الجمهور المستهدَف|E|`SurveyAvailableMail`|CC|low|M|

---

## 2. التجميع حسب آليّة التنفيذ

### (أ) أحداث قائمة نُوسّع مستمعها أو نضيف مستمعًا جديدًا
**توسعة مستمع قائم (إضافة حلقة أولياء عبر `parents()`):** P1، P2، P3، P4.
> **الجذر المشترك المُتحقَّق منه:** المستمعون الأربعة يستخدمون `$student->parent` (مفرد) وهو **null** — لا توجد علاقة `parent()` في `User`، بل `parents()` (belongsToMany عبر `parent_student`). فرع الوليّ في P2/P3 **ميّت فعليًّا اليوم**. الإصلاح: استبدال `$parent = $student->parent; if($parent){...}` بـ`foreach ($student->parents as $parent) { NotificationService + MailGate::send($parent, ...) }`.

**مستمع جديد على حدث مُطلَق أصلًا:** ST4 (على `StreakUpdated`، يُطلَق عند milestone فقط)، T1 (**يلزم إطلاق حدث جديد أوّلًا** — لا حدث عند إنشاء التسليم؛ انظر أدناه).

**فجوة T1:** `ActivityCompleted` يُطلَق عند التصحيح لا عند التسليم. الحلّ الأنظف: إنشاء حدث `ActivitySubmitted($submission)` يُطلَق في نهاية `StudentController::submitActivity` و`Api\StudentApiController::store`، ومستمع `SendTeacherSubmissionPending` يحترم شرط `status ∈ {pending,needs_review}` و`parent_approval_status ≠ pending`. (بديل أخفّ: نداء مباشر في المتحكّمين، لكن الحدث يوحّد المسارين web+API ويُصفَّف.)

### (ب) نداء مباشر في المتحكّم عند الفعل (CC)
P5، P6، SA1، SA2، SA3، SA4، SA5، T2، T3، T4، T5، A1، A2، A3، S1، S2، S3، S4، S5، ST1، ST5، ST6، ST7، ST8، ST9، ST10.
> ملاحظات إعادة استخدام مُتحقَّق منها:
> - **SA4/A1** يتشاركان `ActivityPendingApprovalMail` (المرحلة 1 مدير مدرسة، المرحلة 2 أدمن). `notifySchoolAdminsOfPendingActivity` **حلقة مركزيّة واحدة** تُغذّي 5 مواضع — أضِف `MailGate::send` بجوار `NotificationService::send` داخلها ⟵ يُغطّي الخمسة دفعة واحدة.
> - **SA1/SA2/SA3** يتشاركان `NewRegistrationNotificationMail` (موجود) و`flagKey=new_registration`. الإصلاح المطلوب: تحويل `->first()` إلى `->get()` + حلقة، ولفّها بـ`MailGate` (حاليًّا `Mail::to()` مباشر بلا علم/تسجيل).
> - **T3/T4/A1** تُضاف بجوار نداءات `NotificationService::send`/`create` القائمة في `ActivityApprovalController` — نقاط الحقن معروفة.
> - **A3 «تواصل معنا»** يُرسَل حاليًّا `Mail::send` مباشرًا بلا `MailGate` — لفّه عبر `MailGate::send(null, 'contact_message', 'transactional', $mailable, setting('contact_email'))`.

### (ج) أوامر مجدوَلة (SC)
| البريد | الأمر | التردّد | الحارس / مصدر البيانات |
|---|---|---|---|
|P7 غياب يومين|`SendParentChildInactive` (جديد)|يوميّ|`streaks.last_activity_date <= today-2` + كبح تكرار عبر `email_logs`/كاش يوميّ|
|P8 ملخّص وليّ|`SendParentWeeklyDigest` (جديد، نظير `SendWeeklyDigest`)|أسبوعيّ|تجميع 7 أيّام لكل ابن|
|SA6 ملخّص مدرسة|`SendSchoolWeeklyDigest` (جديد)|أسبوعيّ|استعلامات `SchoolAdminController::dashboard`|
|T6 ربط وليّ (digest)|`SendTeacherParentLinkedDigest` (جديد)|أسبوعيّ|تجميع الروابط الجديدة للأسبوع|
|ST2 تذكير واجب|كتلة `upcomingHomework` في `CheckHomeworkDueDates`|(التردّد القائم للأمر)|`whereBetween due_date [now,+24h]` + لا `ActivitySubmission`|
|ST3 واجب متأخّر|كتلة `overdueHomework` في نفس الأمر|(نفسه)|حارس `alreadyNotified` القائم — مرّة واحدة|

> ST2/ST3 **يُدمجان في أمر قائم** (`CheckHomeworkDueDates`) بجوار `NotificationService::homeworkReminder/homeworkOverdue` — لا أمر جديد. الجدولة تُدار في `routes/console.php` (يستعمل `->daily()/->weekly()/->hourly()`).

### (د) رسائل/بنى جاهزة تحتاج ربطًا فقط
- `NewRegistrationNotificationMail` (موجود، قالب `emails/new-registration-notification`) ⟵ SA1/SA2/SA3: يحتاج فقط تحويل الاستقبال لكل المدراء + لفّ `MailGate`.
- `WeeklyDigestMail` (موجود) ⟵ يمكن تعميمه لـSA6 أو إنشاء `SchoolWeeklyDigestMail` مستقلّ (مُفضَّل لفصل المحتوى).
- `CheckHomeworkDueDates` + `SendWeeklyDigest` (أمران قائمان) ⟵ نقاط ربط جاهزة لـST2/ST3 وقالب P8.
- أحداث `ActivityGraded/BadgeEarned/LevelUp/StreakUpdated/StudentRegistered` ومستمعوها ⟵ نقاط ربط جاهزة لـP1–P4 وST4.

---

## 3. نمط التنفيذ الموحّد (Checklist لكل بريد)

لكل واحد من الـ38:
1. **Mailable** في `app/Mail/…` — `extends Mailable`, `use Queueable, SerializesModels`، `envelope()` + `content(view:…, with:[previewText, unsubscribeUrl])`.
2. **قالب** في `resources/views/emails/{role}/{name}.blade.php` — `@extends('emails.layouts.base')` + `@section('title')` + `@section('content')`. **كل الحقول عبر `{{ }}`**.
3. **نداء عبر البوّابة** فقط: `MailGate::send($recipient, '{type_بلا_بادئة}', '{category}', new XMail(...))` (أو `$toEmail` عند غياب `$user`). **ممنوع** `Mail::to()->send()` المباشر لبريد الأحداث.
4. **تسجيل العلم** في `EmailSettingsController::$types` بالنوع المجرَّد + تسمية عربيّة.
5. **حلقة المستلِمين** حيث المستلِم جمع (parents/admins/teachers): حلقة صريحة، `MailGate::send` لكلّ فرد (البوّابة تفحص opt-out فرديًّا).
6. **الأداء:** في المسارات ذات الحلقات الثقيلة (P5/ST1/SA4)، فضِّل استخلاص حدث مُصفَّف بدل إرسال متزامن داخل دورة الطلب.

### التوسعة المطلوبة على `EmailSettingsController::$types`
تُضاف **36 مفتاحًا** جديدًا (الأنواع المجرَّدة الفريدة؛ `new_registration` مشترك بين SA1/SA2/SA3، و`new_activity` للطالب منفصل عن `parent_new_activity`) فوق الأربعة القائمة ⟵ الإجماليّ 40. أمثلة: `parent_child_activated, parent_child_activity_graded, parent_child_badge_earned, parent_child_level_up, parent_new_activity, parent_approval_required, parent_child_inactive, parent_weekly_digest, new_registration, activity_pending, import_summary, school_digest, teacher_submission_pending, teacher_new_student, teacher_activity_approved, teacher_activity_rejected, teacher_new_rating, teacher_parent_linked, activity_pending_admin, school_registration_pending, contact_message, support_ticket_new, support_ticket_reply, support_ticket_escalated, support_ticket_replied_user, support_ticket_resolved, new_activity, homework_reminder, homework_overdue, streak_milestone, pvp_invite, pvp_update, pvp_result, coins_received, purchase_receipt, survey_available`.
> يُفضَّل تجميع الـ`$types` بصريًّا حسب الدور في لوحة الأدمن (تعديل بسيط على القالب + مصفوفة مجموعات).

---

## 4. خارطة التنفيذ المُرحَّلة (7 دفعات)

الترتيب: أعلى قيمة/أقلّ جهد أوّلًا، مع تقديم إصلاح فرع الوليّ الميّت (قيمة مباشرة، جهد S).

### الدفعة 0 — تمكين البنية (نصف يوم)
- توسعة `EmailSettingsController::$types` بالـ36 مفتاحًا + تجميع القالب.
- تأكيد وجود `route('email.unsubscribe', …)` لبناء `$unsubscribeUrl` (أو دالّة مساعدة موحّدة).
- Mailable أساس مجرّد اختياريّ (`AbstractWahyMail`) لتوحيد `with[previewText,unsubscribeUrl]`.
**قبول:** كل الأنواع الجديدة تظهر في لوحة الأدمن وتُخزَّن؛ `email_master_enabled=off` يمنع كل شيء.

### الدفعة 1 — إحياء الفرع الميّت للوليّ (P1–P4) + محطّة السلسلة (ST4) — أولويّة high/medium، جهد S
توسعة المستمعين الأربعة بحلقة `parents()` + `MailGate`، ومستمع جديد `SendStreakMilestone` على `StreakUpdated` مسجَّل في `AppServiceProvider`.
**قبول:** قبول تسجيل طالب يرسل P1 لكل أوليائه؛ تصحيح نشاط يرسل P2؛ لا بريد لطالب بلا وليّ؛ الرسائل تُسجَّل في `email_logs` وتحترم opt-out (`event`) و`transactional` (P1).

### الدفعة 2 — تدفّق اعتماد الأنشطة (SA4، A1، T3، T4، ST1، P5) — high غالبًا، M/S
- SA4+A1: حقن `MailGate` في `notifySchoolAdminsOfPendingActivity` و`approveActivity`.
- T3/T4: بجوار نداءات `NotificationService` في `ActivityApprovalController`.
- ST1+P5: من `notifyClassroomStudentsOfApprovedActivity` (طالب + أولياؤه). **يُفضَّل استخلاص حدث `ActivityPublished` مُصفَّف** لتفادي إرسال متزامن ثقيل.
**قبول:** دورة نشاط كاملة (إنشاء ⟵ اعتماد مدير ⟵ اعتماد أدمن ⟵ نشر) تولّد بريد المدير والأدمن والمعلّم والطلاب وأوليائهم؛ عدم تكرار عند التعديل/إعادة الإرسال (idempotency للاعتماد النهائيّ لكلّ طالب).

### الدفعة 3 — التسجيلات وطلبات الموافقة (SA1–SA3، A2، P6) — high/medium، S/M
- SA1–SA3: `->get()` + حلقة + `MailGate` (حاليّ `first()`/`Mail::to` مباشر).
- A2: سدّ فجوة `AuthController::register` بإشعار الأدمن (اليوم لا إشعار).
- P6: بوّابة موافقة الوليّ في `submitActivity`.
**قبول:** كل تسجيل ذاتيّ يصل لكل مدراء المدرسة؛ تسجيل مدرسة جديد يصل لكل الأدمن؛ تسليم `requires_parent_approval` يرسل P6 لكل وليّ مع رابط الموافقة الصحيح.

### الدفعة 4 — التذاكر (S1–S5) — high/medium، S/M
حقن `MailGate` بجوار `NotificationService` في `TicketController` و`SupportTicketController` (انتبه: **متحكّمان منفصلان**؛ S1/S2 لطاقم الدعم، S4/S5 لصاحب التذكرة `transactional`، S3 تصعيد للسوبر أدمن).
**قبول:** رفع تذكرة/ردّ/تصعيد/حلّ يولّد البريد للطرف الصحيح؛ التصعيد للسوبر أدمن فقط؛ ردّ الدعم يصل لصاحبها أيًّا كان دوره.

### الدفعة 5 — الأوامر المجدوَلة (ST2، ST3، P7) — high/medium، S/M
- ST2/ST3: دمج في `CheckHomeworkDueDates` (حارس `alreadyNotified` قائم لـST3).
- P7: أمر `SendParentChildInactive` يوميّ + **كبح تكرار** (لا بريد يوميّ متكرّر لنفس الغياب).
**قبول:** تشغيل الأمر يدويًّا يرسل التذكيرات مرّة واحدة لكل حالة مؤهّلة؛ إعادة التشغيل في نفس اليوم لا تُكرّر؛ الطالب المُسلِّم لا يُذكَّر.

### الدفعة 6 — بريد الطالب الحدثيّ منخفض الأولويّة (ST5–ST10، T2، T5، A3) — low/medium، S/M
PvP (ST5–ST7)، هديّة (ST8)، إيصال شراء (ST9)، استبيان (ST10)، طالب جديد للمعلّم (T2)، تقييم المعلّم (T5 — **بلا اسم الطالب** التزامًا بخصوصيّة الإشعار)، «تواصل معنا» (A3).
**قبول:** كل فعل يولّد بريده؛ T5 لا يكشف هوية المقيّم؛ ST9 إيصال دقيق للرصيد المتبقّي؛ A3 يمرّ عبر `MailGate` ويُسجَّل.

### الدفعة 7 — الملخّصات الأسبوعيّة (P8، SA6، T6) — low، L
أوامر مجدوَلة أسبوعيّة (`digest`) تحترم تفضيل `digests`.
**قبول:** ملخّص واحد أسبوعيًّا لكل مستلِم؛ لا إرسال لمن ألغى `digests`؛ محتوى مجمَّع صحيح (نقاط/شارات/أنشطة الأسبوع).

---

## 5. ملاحظات المخاطر والضوابط

1. **إغراق البريد (Fan-out):** P5/ST1/SA4/A1 تُرسل داخل حلقات طلّاب/مدراء. لا تُرسل تزامنيًّا داخل دورة الطلب لأعداد كبيرة — **استخلص حدثًا مُصفَّفًا** (`ActivityPublished`) ودَع المستمع المُصفَّف يوزّع. المستمعون الحاليّون `ShouldQueue` بالفعل ⟵ حافِظ على النمط.
2. **Idempotency للأوامر المجدوَلة:** P7 يحتاج كبح تكرار صريح (كاش يوميّ أو فحص `email_logs`)؛ ST3 يعيد استخدام حارس `alreadyNotified` القائم؛ الملخّصات الأسبوعيّة تُقيَّد بنافذة `->weekly()` ويُفضَّل ختم `last_sent` لتفادي تكرار عند إعادة تشغيل الجدول.
3. **احترام opt-out والفئات:** `transactional` **يتجاوز `unsubscribed_all`** — استعمله فقط للبريد الإجرائيّ الحقيقيّ (موافقات/إيصالات/بيانات دخول). لا تُرقِّ بريدًا اختياريًّا لـ`transactional`. `event`/`digest` يحترمان التفضيل — وهذا مقصود.
4. **بيانات حسّاسة:** SA5 (`BulkImportSummaryMail`) يحمل كلمات مرور مولّدة — `transactional`، بلا تخزين المحتوى في سجلّات، وبريد المدير الفاعل فقط. راجِع سياسة الاحتفاظ في `PruneEmailLogs`.
5. **XSS في قوالب البريد:** كل حقل مُدخَل (عنوان نشاط، اسم، تعليق تقييم، رسالة «تواصل معنا»، سبب رفض) عبر `{{ }}` حصريًّا. لا `{!! !!}`. هذا امتداد مباشر لدروس مراجعات الأمن (innerHTML/اقتباس ملاصق).
6. **الفرع الميّت للوليّ:** لا تنسخ نمط `$student->parent`؛ استخدم `$student->parents` (belongsToMany). أيّ بريد وليّ عبر `parent` المفرد **لن يُرسَل أبدًا**.
7. **المفتاح المجرَّد مقابل البادئة:** أكثر خطأ محتمل تكرارًا — تمرير `'email_type_x'` لـ`MailGate` (يصير `email_type_email_type_x`). مرّر النوع المجرَّد دائمًا.
8. **تكرار المستلِم عبر عدّة فصول/معلّمين:** طالب في فصول متعدّدة قد يُنتج معلّمين متكرّرين (T1)، ووليّ لعدّة أبناء في نفس النشاط (P5) — أزِل التكرار (`unique('id')`) قبل الحلقة لتفادي بريد مزدوج.
9. **اختبار قبل الإطلاق:** استخدم أمر `TestMail` القائم + بيئة `MAIL_MAILER=log` للتحقّق من التصيير قبل التفعيل الحيّ. تنبيه بيئيّ موثَّق سابقًا: `MAIL_PASSWORD==DB_PASSWORD` وبريد التواصل على نطاق مهجور — يجب حسمهما قبل go-live وإلّا يفشل الإرسال الحيّ صامتًا.
10. **الجدولة:** تأكّد أن `schedule:run` مُفعَّل على الإنتاج (cron) — كل بريد SC معطَّل بدونه.

---

### ملاحظات مرجعيّة (مسارات مُتحقَّق منها)
- البوّابة: `app/Services/Mail/MailGate.php` (توقيع + بناء المفتاح بالبادئة).
- الإعدادات: `app/Http/Controllers/Admin/EmailSettingsController.php` (4 أنواع اليوم).
- القالب الأساس: `resources/views/emails/layouts/base.blade.php`.
- تسجيل المستمعين: `app/Providers/AppServiceProvider.php` (`Event::listen`, الأسطر 129–182).
- العلاقات: `app/Models/User.php::parents()` (السطر 209)، `children()` (199) — لا `parent()` مفرد.
- الحلقة المركزيّة للأنشطة المعلّقة: `app/Http/Controllers/TeacherController.php::notifySchoolAdminsOfPendingActivity` (السطر 2066).
- أمر الواجبات: `app/Console/Commands/CheckHomeworkDueDates.php` (`upcomingHomework:40`, `overdueHomework:89`, `alreadyNotified:123`).
- التفضيلات: `app/Models/EmailPreference.php` (`CRITICAL=['auth','transactional','security']`, `MAP: digest/event/announcement`, مجهول الفئة ⟵ مسموح).