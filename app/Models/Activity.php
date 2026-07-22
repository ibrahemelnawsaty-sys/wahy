<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Activity extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'type', 'difficulty', 'status', 'is_team_activity'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "النشاط {$eventName}");
    }

    protected $fillable = [
        'lesson_id',
        'created_by',
        'classroom_id',
        'title',
        'description',
        'type',
        'question_type',
        'difficulty',
        'coins',
        'is_homework',
        'is_team_activity',
        'is_family_activity',
        'is_creative',
        'is_activity_bank',
        'is_featured',
        'featured_by',
        'featured_at',
        'featured_reason',
        'bonus_points',
        'min_team_size',
        'max_team_size',
        'allow_team_formation',
        'due_date',
        'questions',
        'attachment',
        'points',
        'passing_score',
        'manual_review',
        'requires_parent_approval',
        'duration_minutes',
        'quiz_duration',
        'max_attempts',
        'allowed_file_types',
        'max_file_size',
        'order',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'school_approval_status',
        'school_approved_by',
        'school_approved_at',
        'school_rejection_reason',
        // نشر «لكل المدارس» (none/bank/direct) — يُضبط عند اعتماد الأدمن
        'all_schools_mode',
    ];

    protected $casts = [
        'questions' => 'array',
        'allowed_file_types' => 'array',
        'due_date' => 'datetime',
        'featured_at' => 'datetime',
        'approved_at' => 'datetime',
        'school_approved_at' => 'datetime',
        'is_homework' => 'boolean',
        'is_team_activity' => 'boolean',
        'is_family_activity' => 'boolean',
        'is_creative' => 'boolean',
        'is_activity_bank' => 'boolean',
        'is_featured' => 'boolean',
        'manual_review' => 'boolean',
        'requires_parent_approval' => 'boolean',
        'bonus_points' => 'integer',
        'allow_team_formation' => 'boolean',
    ];

    /**
     * Defense-in-depth: prevent teachers from self-approving or self-featuring
     * AFTER the activity is created. CREATE is trusted because controllers force
     * approval_status='pending' for teacher-submitted activities.
     */
    protected static function booted(): void
    {
        static::updating(function (self $activity) {
            $sensitive = ['approval_status', 'approved_by', 'approved_at', 'is_featured', 'featured_by', 'featured_at', 'rejection_reason', 'school_approval_status', 'school_approved_by', 'school_approved_at', 'school_rejection_reason', 'all_schools_mode'];

            $changed = collect($sensitive)->filter(fn ($field) => $activity->isDirty($field));
            if ($changed->isEmpty()) {
                return;
            }

            if (app()->runningInConsole()) {
                return;
            }

            // نستخدم مجموعة أدوار المستخدم (الأساسيّ + الثانويّة القابلة للتبديل) لا العمود الخام
            // فقط — كي لا يُحجب مدير مدرسة يحمل الدور كدور ثانويّ/مُبدَّل (يوافق حارس المسار CheckRole).
            $actor = auth()->user();
            $roles = $actor ? (method_exists($actor, 'getAllRoles') ? $actor->getAllRoles() : [$actor->role]) : [];
            if ($actor && array_intersect($roles, ['school_admin', 'super_admin'])) {
                return;
            }

            abort(403, 'غير مصرح باعتماد أو تمييز النشاط');
        });
    }

    /**
     * المعتمد من قبل
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * هل النشاط معتمد؟
     */
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * هل النشاط في انتظار الموافقة؟
     */
    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending';
    }

    /**
     * المعتمد من مدير المدرسة
     */
    public function schoolApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'school_approved_by');
    }

    /**
     * هل النشاط معتمد من مدير المدرسة؟
     */
    public function isSchoolApproved(): bool
    {
        return $this->school_approval_status === 'approved';
    }

    /**
     * هل النشاط في انتظار موافقة مدير المدرسة؟
     */
    public function isPendingSchoolApproval(): bool
    {
        return $this->school_approval_status === 'pending';
    }

    // ==================== النشر متعدّد المدارس (المرحلة 1) ====================

    /**
     * المدارس التي نُشِر لها النشاط (نشر موجَّه) + وضع النشر لكلٍّ (بنك/مباشر).
     */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'activity_school')
            ->withPivot(['publish_mode', 'published_by', 'published_at'])
            ->withTimestamps();
    }

    /**
     * الفصول التي أُسنِد لها النشاط من البنك «بلا نسخ» (المرحلة 4ب — مرجع).
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'activity_classroom')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    /**
     * هل النشاط منشور «لكل المدارس»؟ (بأيّ وضع)
     */
    public function isPublishedToAllSchools(): bool
    {
        return in_array($this->all_schools_mode, ['bank', 'direct'], true);
    }

    /**
     * هل النشاط «مباشر للطلاب» في المدرسة المعطاة؟
     * مباشر لكل المدارس، أو صفّ موجَّه لهذه المدرسة بوضع direct.
     */
    public function isDirectToSchool(int $schoolId): bool
    {
        if ($this->all_schools_mode === 'direct') {
            return true;
        }

        return $this->schools()
            ->where('schools.id', $schoolId)
            ->wherePivot('publish_mode', 'direct')
            ->exists();
    }

    /**
     * هل النشاط متاح في «بنك» المدرسة المعطاة؟ (يشمل direct لأنّه ⊇ bank)
     */
    public function isAvailableInBankToSchool(int $schoolId): bool
    {
        if ($this->isPublishedToAllSchools()) {
            return true;
        }

        return $this->schools()->where('schools.id', $schoolId)->exists();
    }

    /**
     * Scope موحّد لرؤية الطالب: النشاط «مباشر للطلاب» في مدرسة الطالب.
     * مصدر وحيد يستبدل الفلتر القديم where('approval_status','approved') في كل مسارات
     * قراءة الطالب — يمنع الانحراف ويفرض عزل المدرسة بنية (§4/§12). النشر المباشر
     * (all_schools_mode='direct' أو صفّ activity_school بوضع direct لهذه المدرسة) يتضمّن
     * ضمناً أنّ النشاط معتمَد (يُضبط فقط عند الاعتماد)، فلا حاجة لفحص approval_status هنا.
     */
    public function scopeVisibleToStudent($query, ?int $schoolId, array $classroomIds = [])
    {
        return $query->where(function ($q) use ($schoolId, $classroomIds) {
            // منشور مباشرةً لكل المدارس
            $q->where('all_schools_mode', 'direct');
            // أو منشور مباشرةً لمدرسة الطالب تحديداً (subquery صريح على الـpivot — أمتن من whereHas)
            if ($schoolId) {
                $q->orWhereIn('id', function ($sub) use ($schoolId) {
                    $sub->select('activity_id')
                        ->from('activity_school')
                        ->where('school_id', $schoolId)
                        ->where('publish_mode', 'direct');
                });
            }
            // أو نشاط بنك **معتمَد** أُسنِد مرجعيًّا لأحد فصول الطالب (المرحلة 4ب — عزل بعضويّة الفصل).
            // شرط approval_status='approved' دفاعٌ في العمق: تعديل المعلّم للنشاط يُصفّر الاعتماد،
            // فلا يبقى محتوى مُعدَّل غير مُراجَع مرئيًّا عبر صفّ الإسناد حتى لو لم يُحذَف بعد.
            if (! empty($classroomIds)) {
                $q->orWhere(function ($q2) use ($classroomIds) {
                    $q2->where('approval_status', 'approved')
                        ->whereIn('id', function ($sub) use ($classroomIds) {
                            $sub->select('activity_id')
                                ->from('activity_classroom')
                                ->whereIn('classroom_id', $classroomIds);
                        });
                });
            }
        });
    }

    /**
     * هل هذا النشاط مرئيّ للطالب؟ (نسخة كائن — لبوّابة الوصول المباشر)
     * تُغلق الثغرة: نشاط بلا درس (نشاط بنك) لم يعد يُفتَح بتخمين id، إلا إن كان منشورًا
     * مباشرةً لمدرسته أو مُسنَدًا مرجعيًّا لأحد فصوله (المرحلة 4ب).
     */
    public function isVisibleToStudentSchool(?int $schoolId, array $classroomIds = []): bool
    {
        if ($this->all_schools_mode === 'direct') {
            return true;
        }

        if ($schoolId && $this->isDirectToSchool($schoolId)) {
            return true;
        }

        // إسناد مرجعيّ لأحد فصول الطالب — يُشترَط أن يبقى معتمَدًا (لا محتوى مُعدَّل غير مُراجَع)
        if (! empty($classroomIds) && $this->approval_status === 'approved') {
            return $this->classrooms()->whereIn('classrooms.id', $classroomIds)->exists();
        }

        return false;
    }

    /**
     * بوّابة وصول الطالب الكاملة: نشط + منشور مباشرةً لمدرسته (isVisibleToStudentSchool)
     * + ضمن قيمة مفعّلة لمدرسته (Value::visibleForSchool). مصدرٌ وحيد يستدعيه الويب والـAPI
     * معًا لمنع الانحراف — كان الجوّال (StudentApiController) يتجاوز بوّابة القيمة فيكشف الأسئلة
     * والإجابات ويقبل التسليم على قيمة أخفتها المدرسة عمدًا.
     */
    public function isAccessibleByStudent(User $student): bool
    {
        if (($this->status ?? 'active') !== 'active') {
            return false;
        }

        if (! $this->isVisibleToStudentSchool($student->school_id, $student->classrooms->pluck('id')->all())) {
            return false;
        }

        $valueId = optional(optional($this->lesson)->concept)->value_id;
        if (! $valueId || ! $student->school_id) {
            return true; // بلا قيمة أو بلا مدرسة (كأدمن اختبار) → لا قيد قيمة
        }

        return in_array($valueId, Value::visibleForSchool($student->school_id)->pluck('id')->all(), true);
    }

    /**
     * الدرس الأساسي
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * المعلم الذي أنشأ النشاط
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * الفصل المرتبط بالنشاط
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * الإرساليات (الطلاب اللي حلوا النشاط)
     */
    public function submissions()
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    /**
     * الفرق المرتبطة بالنشاط
     */
    public function teamActivities()
    {
        return $this->hasMany(TeamActivity::class);
    }

    /**
     * المعلم الذي ميّز النشاط
     */
    public function featuredBy()
    {
        return $this->belongsTo(User::class, 'featured_by');
    }

    /**
     * تسليمات الأنشطة العائلية
     */
    public function familySubmissions()
    {
        return $this->hasMany(FamilyActivitySubmission::class);
    }

    /**
     * هل النشاط جماعي؟
     */
    public function isTeamActivity(): bool
    {
        return $this->is_team_activity === true;
    }
}
