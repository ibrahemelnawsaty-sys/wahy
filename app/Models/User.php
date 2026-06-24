<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable;

    /**
     * Activity Log Options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'school_id', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "المستخدم {$eventName}");
    }

    /**
     * Defense-in-depth: prevent privilege escalation via mass assignment.
     * Sensitive fields can only be modified by super_admin/school_admin or from CLI/seeders.
     * Even if a developer accidentally writes ->update($request->all()), this guard blocks it.
     */
    protected static function booted(): void
    {
        static::saving(function (self $user) {
            if (! $user->exists) {
                return; // creation handled by explicit controllers; allow.
            }

            // Allow CLI (seeders, artisan commands), queue workers, and unauthenticated bootstrap.
            if (app()->runningInConsole()) {
                return;
            }

            $actor = auth()->user();
            $isPrivileged = $actor && in_array($actor->role, ['super_admin', 'school_admin'], true);

            // active_role is a SELF-SERVICE selection among the user's already-owned roles
            // (role-switching). Allow it iff the new value is a role the user actually owns;
            // setting it to an UNOWNED role would be escalation -> blocked for non-admins.
            // (Kept OUT of the $sensitive list below so a legitimate switch is not 403'd.)
            if ($user->isDirty('active_role') && ! $isPrivileged) {
                $newActive = $user->active_role;
                if (filled($newActive) && ! in_array($newActive, $user->getAllRoles(), true)) {
                    abort(403, 'لا يمكن تفعيل دور غير مُسنَد للحساب');
                }
            }

            // The privilege-GRANTING fields stay strictly admin-only.
            $sensitive = ['role', 'school_id', 'status', 'secondary_roles', 'password_change_required'];
            if (collect($sensitive)->filter(fn ($field) => $user->isDirty($field))->isEmpty()) {
                return;
            }

            if ($isPrivileged) {
                return;
            }

            abort(403, 'غير مصرح بتعديل حقول حساسة في حساب المستخدم');
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'secondary_roles',
        'active_role',
        'qr_code',
        'avatar',
        'school_id',
        'phone',
        'birth_date',
        'status',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
        'password_change_required',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code', // إخفاء كود 2FA من الـ JSON
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'secondary_roles' => 'array',
        ];
    }

    // ==================== Query Scopes للأداء ====================

    /**
     * Scope للمستخدمين النشطين
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope حسب الدور — يقبل string أو UserRole enum.
     */
    public function scopeRole(Builder $query, string|\App\Enums\UserRole $role): Builder
    {
        return $query->where('role', $role instanceof \App\Enums\UserRole ? $role->value : $role);
    }

    /**
     * يطابق دور المستخدم — يقبل UserRole enum أو string.
     */
    public function hasRoleEnum(\App\Enums\UserRole|string $role): bool
    {
        $value = $role instanceof \App\Enums\UserRole ? $role->value : $role;

        return $this->role === $value;
    }

    /**
     * هل المستخدم إداري (super_admin أو school_admin)؟
     */
    public function isAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->isSchoolAdmin();
    }

    /**
     * Scope حسب المدرسة
     */
    public function scopeInSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * العلاقة مع المدرسة
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * الأبناء (لولي الأمر)
     */
    public function children()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    /**
     * أولياء الأمور (للطالب)
     */
    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    /**
     * طلبات التسجيل
     */
    public function registrationRequests()
    {
        return $this->hasMany(RegistrationRequest::class);
    }

    /**
     * الفصول (للمعلم)
     */
    public function teachingClassrooms()
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    /**
     * الفصول (للطالب)
     */
    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_student', 'student_id', 'classroom_id')
            ->withPivot('enrollment_date', 'status')
            ->withTimestamps();
    }

    /**
     * الفصل الحالي للطالب (أول فصل نشط)
     */
    public function classroom()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_student', 'student_id', 'classroom_id')
            ->wherePivot('status', 'active')
            ->withPivot('enrollment_date', 'status')
            ->withTimestamps()
            ->orderByDesc('classroom_student.enrollment_date')
            ->limit(1);
    }

    /**
     * التقييمات التي أعطاها الطالب للمعلمين
     */
    public function givenRatings()
    {
        return $this->hasMany(TeacherRating::class, 'student_id');
    }

    /**
     * التقييمات التي حصل عليها المعلم
     */
    public function receivedRatings()
    {
        return $this->hasMany(TeacherRating::class, 'teacher_id');
    }

    /**
     * متوسط تقييم المعلم
     */
    public function averageRating()
    {
        return $this->receivedRatings()->avg('rating');
    }

    /**
     * النقاط
     */
    public function points(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Point::class);
    }

    /**
     * مستوى المستخدم الموحّد: floor(إجمالي النقاط / 100) + 1.
     * مصدر حقيقة واحد بدل حساب المستوى بصيغ متعددة متباعدة عبر الكود.
     *
     * مصدر الحقيقة هو SUM(points) من سجل النقاط — لا يُقرأ عمود users.total_points
     * فهو عمود ميت (يساوي 0 دائماً ولا شيء يحدّثه). يفضّل alias المحمّل مسبقاً
     * عبر withSum('points', 'points') وهو points_sum_points لتفادي N+1، وإلا
     * يستعلم من العلاقة المحمّلة أو من قاعدة البيانات مباشرة.
     */
    public function getLevelAttribute(): int
    {
        if (array_key_exists('points_sum_points', $this->attributes)) {
            $total = (int) $this->attributes['points_sum_points'];
        } elseif ($this->relationLoaded('points')) {
            $total = (int) $this->points->sum('points');
        } else {
            $total = (int) $this->points()->sum('points');
        }

        return intdiv($total, 100) + 1;
    }

    /**
     * نقاط أولياء الأمور
     */
    public function parentPoints()
    {
        return $this->hasMany(ParentPoint::class, 'parent_id');
    }

    /**
     * مدح أولياء الأمور للطلاب
     */
    public function praisesGiven()
    {
        return $this->hasMany(ParentPraise::class, 'parent_id');
    }

    /**
     * المدح المستلم من ولي الأمر
     */
    public function praisesReceived()
    {
        return $this->hasMany(ParentPraise::class, 'student_id');
    }

    /**
     * الهدايا المرسلة من ولي الأمر
     */
    public function giftsGiven()
    {
        return $this->hasMany(ParentGift::class, 'parent_id');
    }

    /**
     * الهدايا المستلمة من ولي الأمر
     */
    public function giftsReceived()
    {
        return $this->hasMany(ParentGift::class, 'student_id');
    }

    public function totalPoints()
    {
        return $this->points()->sum('points');
    }

    /**
     * الشارات
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    /**
     * التيجان
     */
    public function crowns()
    {
        return $this->hasMany(Crown::class);
    }

    /**
     * السلسلة اليومية
     */
    public function streak()
    {
        return $this->hasOne(Streak::class);
    }

    /**
     * العملات (قيمات)
     */
    public function coins()
    {
        return $this->hasMany(Coin::class);
    }

    public function totalCoins()
    {
        return $this->coins()->sum('coins');
    }

    /**
     * إرساليات الأنشطة
     */
    public function activitySubmissions()
    {
        return $this->hasMany(ActivitySubmission::class, 'student_id');
    }

    /**
     * الفرق (للطالب)
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members', 'student_id', 'team_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * الفرق المُنشأة (للمعلم)
     */
    public function createdTeams()
    {
        return $this->hasMany(Team::class, 'created_by');
    }

    /**
     * منتجات المتجر المشتراة
     */
    public function purchases()
    {
        return $this->belongsToMany(ShopItem::class, 'user_purchases')
            ->withPivot(['price_paid', 'is_active', 'used_at'])
            ->withTimestamps();
    }

    /**
     * هل اشترى المستخدم هذا العنصر
     */
    public function hasPurchased($shopItemId): bool
    {
        return $this->purchases()->where('shop_item_id', $shopItemId)->exists();
    }

    /**
     * سجلات streak الدروس (للطالب)
     */
    public function lessonStreaks()
    {
        return $this->hasMany(LessonUserStreak::class);
    }

    /**
     * الحصول على streak لدرس معين
     */
    public function getLessonStreak($lessonId): ?LessonUserStreak
    {
        return $this->lessonStreaks()->where('lesson_id', $lessonId)->first();
    }

    /**
     * إنشاء أو تحديث streak لدرس
     */
    public function getOrCreateLessonStreak($lessonId): LessonUserStreak
    {
        return LessonUserStreak::firstOrCreate(
            ['user_id' => $this->id, 'lesson_id' => $lessonId],
            ['completed_days' => 0, 'activity_dates' => []],
        );
    }

    // ==================== Role Helpers ====================
    // تستخدم App\Enums\UserRole قيمها — يبقى السلوك متطابقاً لأن
    // قيم الـ enum هي نفس الـ strings المستخدمة في DB.

    public function isSuperAdmin(): bool
    {
        return $this->role === \App\Enums\UserRole::SuperAdmin->value;
    }

    public function isSchoolAdmin(): bool
    {
        return $this->role === \App\Enums\UserRole::SchoolAdmin->value;
    }

    public function isTeacher(): bool
    {
        return $this->role === \App\Enums\UserRole::Teacher->value;
    }

    public function isStudent(): bool
    {
        return $this->role === \App\Enums\UserRole::Student->value;
    }

    public function isParent(): bool
    {
        return $this->role === \App\Enums\UserRole::Parent->value;
    }

    /**
     * حساب العمر من تاريخ الميلاد
     */
    public function getAgeAttribute(): ?int
    {
        if (! $this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }

    /**
     * الحصول على رابط صورة الملف الشخصي
     *
     * الـ public disk root = storage/app/public/data
     * الـ store() يحفظ مثلاً: avatars/file.jpg (نسبي للـ disk root)
     * الملف الفعلي: storage/app/public/data/avatars/file.jpg
     * الـ URL: asset('storage/app/public/data/avatars/file.jpg')
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            // رابط خارجي مباشر
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }

            // المسار المحفوظ في DB نسبي للـ public disk root (storage/app/public/data/)
            if (Storage::disk('public')->exists($this->avatar)) {
                return asset('storage/app/public/data/' . $this->avatar);
            }

            // محاولة البحث في avatars/ لو المسار اسم الملف فقط
            $avatarPath = 'avatars/' . basename($this->avatar);
            if (Storage::disk('public')->exists($avatarPath)) {
                return asset('storage/app/public/data/' . $avatarPath);
            }
        }

        // Issue #15: صورة افتراضية مضمونة العرض دائماً عبر data URI (SVG)
        // — لا تعتمد على ملف خارجي قد يكون مفقوداً على السيرفر.
        return $this->defaultAvatarDataUri();
    }

    /**
     * توليد avatar افتراضي ك SVG inline data URI — يحتوي الحرف الأول للاسم.
     */
    private function defaultAvatarDataUri(): string
    {
        $letter = mb_strtoupper(mb_substr($this->name ?? '?', 0, 1));
        // اختيار لون من تجزئة الـ id لاتساق ألوان كل مستخدم
        $palette = ['#667eea', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];
        $color = $palette[(int) ($this->id ?? 0) % count($palette)];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">'
            . '<circle cx="50" cy="50" r="50" fill="' . $color . '"/>'
            . '<text x="50" y="62" text-anchor="middle" font-family="Cairo, Arial, sans-serif" font-size="42" font-weight="800" fill="white">' . htmlspecialchars($letter, ENT_XML1) . '</text>'
            . '</svg>';

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    /**
     * الحصول على الدور النشط (الحالي)
     */
    public function getActiveRoleAttribute(): string
    {
        // إذا كان هناك دور نشط محفوظ في الـ session
        if (session()->has('active_role_' . $this->id)) {
            return session('active_role_' . $this->id);
        }

        // إذا كان هناك دور نشط محفوظ في الـ database
        if (isset($this->attributes['active_role']) && $this->attributes['active_role']) {
            return $this->attributes['active_role'];
        }

        // الدور الافتراضي
        return $this->attributes['role'];
    }

    /**
     * الحصول على الدور الحالي (مع دعم تبديل الأدوار)
     */
    public function getCurrentRole(): string
    {
        return session('active_role_' . $this->id, $this->attributes['active_role'] ?? $this->attributes['role']);
    }

    /**
     * الحصول على جميع الأدوار المتاحة للمستخدم
     */
    public function getAllRoles(): array
    {
        $roles = [$this->role];

        if ($this->secondary_roles && is_array($this->secondary_roles)) {
            $roles = array_merge($roles, $this->secondary_roles);
        }

        return array_unique($roles);
    }

    /**
     * التحقق من وجود أدوار متعددة
     */
    public function hasMultipleRoles(): bool
    {
        return count($this->getAllRoles()) > 1;
    }

    /**
     * تبديل الدور النشط
     */
    public function switchRole(string $role): bool
    {
        $availableRoles = $this->getAllRoles();

        if (! in_array($role, $availableRoles)) {
            return false;
        }

        // حفظ في الـ session
        session(['active_role_' . $this->id => $role]);

        // حفظ في الـ database أيضاً (اختياري)
        $this->update(['active_role' => $role]);

        return true;
    }

    /**
     * الحصول على اسم الدور بالعربي
     */
    public static function getRoleNameAr(string $role): string
    {
        $roleNames = [
            'super_admin' => 'مدير النظام',
            'school_admin' => 'مدير مدرسة',
            'teacher' => 'معلم',
            'parent' => 'ولي أمر',
            'student' => 'طالب',
        ];

        return $roleNames[$role] ?? $role;
    }

    /**
     * الحصول على أيقونة الدور
     */
    public static function getRoleIcon(string $role): string
    {
        $roleIcons = [
            'super_admin' => 'fas fa-crown',
            'school_admin' => 'fas fa-school',
            'teacher' => 'fas fa-chalkboard-teacher',
            'parent' => 'fas fa-users',
            'student' => 'fas fa-user-graduate',
        ];

        return $roleIcons[$role] ?? 'fas fa-user';
    }

    /**
     * الحصول على رابط الداشبورد حسب الدور
     */
    public static function getRoleDashboardRoute(string $role): string
    {
        $routes = [
            'super_admin' => '/admin/dashboard',
            'school_admin' => '/school-admin/dashboard',
            'teacher' => '/teacher/dashboard',
            'parent' => '/parent/dashboard',
            'student' => '/student/dashboard',
        ];

        return $routes[$role] ?? '/dashboard';
    }

    /**
     * تقييمات المعلم (التقييمات التي تلقاها كمعلم)
     */
    public function ratings()
    {
        return $this->hasMany(\App\Models\TeacherRating::class, 'teacher_id');
    }
}
