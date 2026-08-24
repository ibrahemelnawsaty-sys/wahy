<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ActivitySubmission extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'score', 'feedback'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "تقديم النشاط {$eventName}");
    }

    protected $fillable = [
        'activity_id', 'student_id', 'answer', 'file_path',
        'score', 'awarded_points', 'status', 'attempts', 'reviewed_by', 'feedback',
        'submitted_at', 'reviewed_at',
        'parent_approval_status', 'parent_approved_by', 'parent_approved_at',
        // تمييز التسليم المتميّز (لعرضه للإدارة — تقارير/تكريم)
        'is_featured', 'featured_by', 'featured_at', 'featured_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'parent_approved_at' => 'datetime',
        'is_featured' => 'boolean',
        'featured_at' => 'datetime',
    ];

    /**
     * المعلّم الذي ميّز هذا التسليم.
     */
    public function featuredBy()
    {
        return $this->belongsTo(User::class, 'featured_by');
    }

    /**
     * تسليمات اجتازت موافقة وليّ الأمر (أو لا تتطلّبها) — أي المؤهّلة لطابور المعلّم.
     * تسليم بانتظار موافقة الوليّ (parent_approval_status='pending') يُستبعَد من المعلّم.
     */
    public function scopeParentCleared($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('parent_approval_status')
                ->orWhere('parent_approval_status', 'approved');
        });
    }

    /**
     * وليّ الأمر الذي وافق على التسليم (ميزة #23).
     */
    public function approvingParent()
    {
        return $this->belongsTo(User::class, 'parent_approved_by');
    }

    /**
     * Defense-in-depth: prevent students from tampering with score/status/feedback
     * AFTER the submission has been created. The initial CREATE is trusted because
     * the controller computes score/status server-side from auto-grading logic.
     */
    protected static function booted(): void
    {
        static::updating(function (self $submission) {
            $sensitive = ['score', 'reviewed_by', 'feedback', 'reviewed_at', 'status'];

            $changed = collect($sensitive)->filter(fn ($field) => $submission->isDirty($field));
            if ($changed->isEmpty()) {
                return;
            }

            if (app()->runningInConsole()) {
                return;
            }

            $actor = auth()->user();

            // الطالب يعيد إرسال نشاطه (محاولة جديدة) — الدرجة والحالة تُحسبان خادمياً في
            // StudentController::submitActivity ولا يتحكّم بهما العميل، فالتحديث مشروع لمالك التسليم.
            if ($actor && ($actor->role ?? null) === 'student' && (int) $actor->getKey() === (int) $submission->student_id) {
                return;
            }

            // نطابق CheckRole: نعتبر الأدوار الفعليّة (الأساسيّ ∪ الثانويّ) لا العمود الأساسيّ وحده —
            // فمديرُ مدرسة/معلّم بدورٍ ثانويّ يمرّ الـmiddleware ويجب ألّا يُصدّ هنا بـ403 (تراجع صامت).
            $actorRoles = $actor
                ? (method_exists($actor, 'getAllRoles') ? $actor->getAllRoles() : [$actor->role])
                : [];
            if (count(array_intersect(['teacher', 'school_admin', 'super_admin'], $actorRoles)) > 0) {
                return;
            }

            abort(403, 'غير مصرح بتعديل درجة أو حالة التسليم');
        });
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * الحالات المعتبرة "إنجازاً نهائياً للنشاط" — فقط ما تم اعتماده.
     * يُستخدم في التقارير والإحصاءات النهائية و الإنجاز المؤكد.
     */
    public const DONE_STATUSES = ['completed', 'approved'];

    /**
     * الحالات المعتبرة "تم تسليمها" — تشمل المعلّق للمراجعة.
     * تُستخدم فقط في عدّ "ما أرسله الطالب" وليس في الإنجاز النهائي.
     */
    public const SUBMITTED_STATUSES = ['completed', 'approved', 'pending', 'needs_review'];

    /**
     * الحالات التي تتطلّب مراجعة/إجراءً من المعلّم:
     *  - pending: تصحيح يدوي (رفع/مقالي)
     *  - needs_review: لم يجتَز التصحيح الآلي (يمكن للمعلّم السماح بإعادة المحاولة)
     * مصدر وحيد لعدّاد «تحتاج مراجعة» وطابور المراجعة (تفادي انحراف الفلترة).
     */
    public const PENDING_REVIEW_STATUSES = ['pending', 'needs_review'];

    /**
     * Scope: تسليمات تم إنجازها نهائيًا (مُعتمدة فقط).
     * استخدام: ActivitySubmission::done() أو $student->activitySubmissions()->done()
     */
    public function scopeDone($query)
    {
        return $query->whereIn('status', self::DONE_STATUSES);
    }

    /**
     * Scope: تسليمات أُرسلت بالفعل (تشمل المعلّقة للمراجعة).
     */
    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', self::SUBMITTED_STATUSES);
    }

    /**
     * Scope: التسليمات «العالقة» في مدرسةٍ بعينها والتي يجوز لمدير المدرسة حسمها كخيارٍ احتياطيّ
     * عند عدم تجاوب المعلّم أو وليّ الأمر — اتحادٌ حصريّ لدلوَين منفصلين:
     *  (أ) بانتظار تصحيح المعلّم: status ∈ PENDING_REVIEW ومُخلاةٌ من بوّابة الوليّ (parentCleared)،
     *  (ب) بانتظار موافقة الوليّ: parent_approval_status = 'pending'.
     * النطاق عبر الطالب (لا school_id مباشر على activity_submissions).
     */
    /**
     * Scope: تسليمات يجوز لهذا المعلّم مراجعتها — **مصدرٌ وحيد** يوحّد القوائم وفحص الصلاحية.
     * الاتحاد: (أ) طلاب فصول المعلّم، **أو** (ب) تسليمات على نشاطٍ أنشأه المعلّم نفسه.
     * الفرع (ب) شبكة أمان: طالبٌ بلا فصل (أو فصلٍ بلا معلّم) كان تسليمه على نشاط المعلّم
     * يبقى بلا مراجِع للأبد — فيظهر «صفراً/معلّقاً» بلا أن يصل أحد.
     */
    public function scopeReviewableByTeacher($query, User $teacher)
    {
        $classroomIds = $teacher->teachingClassrooms()->pluck('id')->all();
        $studentIds = \Illuminate\Support\Facades\DB::table('classroom_student')
            ->whereIn('classroom_id', $classroomIds)
            ->pluck('student_id')->all();

        return $query->where(function ($q) use ($studentIds, $teacher) {
            $q->whereIn('student_id', $studentIds)
                // فرع «نشاطٌ أنشأه المعلّم»: مُقيَّدٌ بطلاب مدرسة المعلّم (M4). بلا هذا القيد كان
                // تسليمُ طالبٍ من مدرسةٍ أخرى على نشاطِ بنكٍ للمعلّم يظهر في طابوره فيكشف PII عابراً
                // للمدارس ويكتب على اقتصاد طالب مدرسة أخرى (النشر عبر المدارس يُبقي created_by).
                ->orWhere(function ($q2) use ($teacher) {
                    $q2->whereHas('activity', fn ($a) => $a->where('created_by', $teacher->id))
                        ->whereHas('student', fn ($s) => $s->where('school_id', $teacher->school_id));
                });
        });
    }

    /**
     * هل يجوز لهذا المعلّم مراجعة هذا التسليم؟ يعيد استخدام scopeReviewableByTeacher
     * (نفس التعريف تماماً — لا انحراف بين القائمة وفحص الوصول المباشر).
     */
    public function isReviewableByTeacher(User $teacher): bool
    {
        return static::whereKey($this->getKey())->reviewableByTeacher($teacher)->exists();
    }

    public function scopeAwaitingSchoolResolution($query, int $schoolId)
    {
        return $query
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            // غير منتهٍ فقط: يمنع عودة تسليمٍ حُسِم (مُعتمَد/مرفوض) — فتسليمٌ رُفض تبقى بوّابة وليّه
            // 'pending' لولا هذا الحارس فيُعاد إدراجه عبر دلو الوليّ أدناه.
            ->whereNotIn('status', array_merge(self::DONE_STATUSES, ['rejected']))
            ->where(function ($q) {
                $q->where(function ($teacher) {
                    $teacher->whereIn('status', self::PENDING_REVIEW_STATUSES)
                        ->where(function ($pc) {
                            $pc->whereNull('parent_approval_status')
                                ->orWhere('parent_approval_status', 'approved');
                        });
                })->orWhere('parent_approval_status', 'pending');
            });
    }
}
