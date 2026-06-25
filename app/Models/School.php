<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class School extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'address', 'phone', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "المدرسة {$eventName}");
    }

    protected $fillable = [
        'name',
        'logo',
        'description',
        'address',
        'city',
        'country',
        'contact_email',
        'contact_phone',
        'qr_code',
        'created_by',
        'status',
        'teacher_token',
        'student_token',
        'parent_token',
        'teacher_qr',
        'student_qr',
        'parent_qr',
        'enable_teacher_registration',
        'enable_student_registration',
        'enable_parent_registration',
    ];

    protected $casts = [
        'enable_teacher_registration' => 'boolean',
        'enable_student_registration' => 'boolean',
        'enable_parent_registration' => 'boolean',
    ];

    /**
     * العلاقة مع الفروع
     */
    public function branches(): HasMany
    {
        return $this->hasMany(SchoolBranch::class);
    }

    /**
     * العلاقة مع المستخدمين (الطلاب، المعلمين...)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * العلاقة مع الطلاب فقط
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }

    /**
     * العلاقة مع المعلمين فقط
     */
    public function teachers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'teacher');
    }

    /**
     * العلاقة مع الفصول الدراسية
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * السوبر أدمن اللي أنشأ المدرسة
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * طلبات التسجيل للمدرسة
     */
    public function registrationRequests(): HasMany
    {
        return $this->hasMany(RegistrationRequest::class);
    }

    /**
     * المراحل الدراسية المرتبطة بالمدرسة
     */
    public function educationLevels()
    {
        return $this->belongsToMany(EducationLevel::class, 'school_education_level')->withTimestamps();
    }

    /**
     * القيم المفعّلة لهذه المدرسة (من قِبَل أدمن المنصة).
     * إذا لم تُحدَّد قيم لمدرسة → تُعتبر جميع القيم النشطة مفعّلة (افتراضياً).
     */
    public function activeValues()
    {
        return $this->belongsToMany(Value::class, 'school_active_values')
            ->withPivot(['activated_by', 'activated_at'])
            ->withTimestamps();
    }

    /**
     * هل المدرسة قد قامت بتخصيص قيم محددة؟
     */
    public function hasCustomActiveValues(): bool
    {
        return $this->activeValues()->exists();
    }

    /**
     * IDs القيم الظاهرة لهذه المدرسة (مع fallback للجميع).
     */
    public function visibleValueIds(): array
    {
        if ($this->hasCustomActiveValues()) {
            return $this->activeValues()->pluck('values.id')->toArray();
        }

        return Value::where('status', 'active')->pluck('id')->toArray();
    }

    /**
     * توليد tokens فريدة للتسجيل
     */
    public function generateRegistrationTokens(): void
    {
        $this->update([
            'teacher_token' => $this->teacher_token ?? Str::random(32),
            'student_token' => $this->student_token ?? Str::random(32),
            'parent_token' => $this->parent_token ?? Str::random(32),
        ]);
    }

    /**
     * الحصول على رابط تسجيل المعلمين
     */
    public function getTeacherRegistrationUrlAttribute(): string
    {
        return route('public.register.teacher', $this->teacher_token);
    }

    /**
     * الحصول على رابط تسجيل الطلاب
     */
    public function getStudentRegistrationUrlAttribute(): string
    {
        return route('public.register.student', $this->student_token);
    }

    /**
     * الحصول على رابط تسجيل أولياء الأمور
     */
    public function getParentRegistrationUrlAttribute(): string
    {
        return route('public.register.parent', $this->parent_token);
    }
}
