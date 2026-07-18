<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
            $sensitive = ['approval_status', 'approved_by', 'approved_at', 'is_featured', 'featured_by', 'featured_at', 'rejection_reason', 'school_approval_status', 'school_approved_by', 'school_approved_at', 'school_rejection_reason'];

            $changed = collect($sensitive)->filter(fn ($field) => $activity->isDirty($field));
            if ($changed->isEmpty()) {
                return;
            }

            if (app()->runningInConsole()) {
                return;
            }

            $actor = auth()->user();
            if ($actor && in_array($actor->role, ['school_admin', 'super_admin'], true)) {
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
