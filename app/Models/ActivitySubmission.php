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
        'score', 'status', 'reviewed_by', 'feedback',
        'submitted_at', 'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

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
            if ($actor && in_array($actor->role, ['teacher', 'school_admin', 'super_admin'], true)) {
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
}
