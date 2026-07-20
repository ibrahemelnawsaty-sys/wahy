<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    protected $fillable = [
        'title',
        'description',
        'target_roles',
        'school_id',
        'status',
        'trigger_type',
        'requires_login',
        'is_mandatory',
        'is_popup',
        'start_date',
        'end_date',
        'created_by',
        // حقول التقييم القبلي/البعدي
        'survey_type',
        'lesson_id',
        'value_id',
        'linked_survey_id',
        'assessment_phase',
    ];

    /**
     * تحويل role المستخدم إلى target_type المستخدم في الاستبيانات
     * (الـ form يُرسل: schools, teachers, students, parents)
     * (الـ User::role يكون: school_admin, teacher, student, parent)
     */
    public static function roleToTargetType(string $role): ?string
    {
        return match ($role) {
            'school_admin' => 'schools',
            'teacher' => 'teachers',
            'student' => 'students',
            'parent' => 'parents',
            default => null,
        };
    }

    protected $casts = [
        'target_roles' => 'array',
        'is_mandatory' => 'boolean',
        'is_popup' => 'boolean',
        'requires_login' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * هل هو استبيان تقييم قبلي/بعدي؟
     */
    public function isAssessment(): bool
    {
        return $this->survey_type === 'pre_post_assessment';
    }

    /**
     * هل هو استبيان قبلي؟
     */
    public function isPreAssessment(): bool
    {
        return $this->isAssessment() && $this->assessment_phase === 'pre';
    }

    /**
     * هل هو استبيان بعدي؟
     */
    public function isPostAssessment(): bool
    {
        return $this->isAssessment() && $this->assessment_phase === 'post';
    }

    /**
     * الدرس المرتبط
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * القيمة المرتبطة
     */
    public function value(): BelongsTo
    {
        return $this->belongsTo(Value::class);
    }

    /**
     * الاستبيان المرتبط (القبلي ↔ البعدي)
     */
    public function linkedSurvey(): BelongsTo
    {
        return $this->belongsTo(Survey::class, 'linked_survey_id');
    }

    /**
     * الحصول على بيانات المقارنة بين القبلي والبعدي.
     * يدعم فلترة حسب المدرسة (للـ school-admin) أو حسب IDs طلاب محددين (للمعلم/ولي الأمر).
     *
     * @param  int|null  $schoolId  فلترة على طلاب مدرسة محددة
     * @param  array|null  $userIds  فلترة على قائمة users محددة (للمعلم: طلابه، لولي الأمر: أبناؤه)
     */
    public function getComparisonData(?int $schoolId = null, ?array $userIds = null): array
    {
        // تحديد الاستبيان القبلي والبعدي
        if ($this->assessment_phase === 'pre') {
            $preSurvey = $this;
            $postSurvey = $this->linkedSurvey;
        } else {
            $postSurvey = $this;
            $preSurvey = $this->linkedSurvey;
        }

        if (! $preSurvey || ! $postSurvey) {
            return ['error' => 'لم يتم العثور على الاستبيان المرتبط'];
        }

        $preResponsesQ = $preSurvey->responses()->with('user');
        $postResponsesQ = $postSurvey->responses()->with('user');

        // تطبيق فلترة حسب المدرسة (school-admin)
        if ($schoolId !== null) {
            $preResponsesQ->whereHas('user', fn ($q) => $q->where('school_id', $schoolId));
            $postResponsesQ->whereHas('user', fn ($q) => $q->where('school_id', $schoolId));
        }

        // تطبيق فلترة حسب user IDs (teacher/parent)
        if ($userIds !== null) {
            $preResponsesQ->whereIn('user_id', $userIds);
            $postResponsesQ->whereIn('user_id', $userIds);
        }

        $preResponses = $preResponsesQ->get();
        $postResponses = $postResponsesQ->get();
        // المطابقة بالترتيب/الفهرس بين القبلي والبعدي (المعرّفات مختلفة بين استبيانين منفصلين) — Issue 17
        $questions = $preSurvey->questions()->orderBy('order')->get()->values();
        $postQuestions = $postSurvey->questions()->orderBy('order')->get()->values();

        $comparison = [];
        $totalImprovement = 0;
        $studentCount = 0;

        // حساب المقارنة لكل طالب أجاب على الاثنين
        foreach ($preResponses as $preResponse) {
            $userId = $preResponse->user_id;
            if (! $userId) {
                continue;
            }

            $postResponse = $postResponses->where('user_id', $userId)->first();
            if (! $postResponse) {
                continue;
            }

            $preAnswers = $preResponse->answers ?? [];
            $postAnswers = $postResponse->answers ?? [];

            $preScore = 0;
            $postScore = 0;
            $questionDetails = [];

            foreach ($questions as $i => $question) {
                // السؤال البعدي المقابل بالترتيب (قد يختلف معرّفه عن القبلي)
                $postQuestion = $postQuestions[$i] ?? null;
                $qId = (string) $question->id;
                $postQId = $postQuestion ? (string) $postQuestion->id : $qId;

                $preVal = $preAnswers[$qId] ?? null;
                $postVal = $postAnswers[$postQId] ?? null;

                // حساب النقاط للأسئلة
                if (in_array($question->question_type, ['scale', 'rating'])) {
                    $preNum = is_numeric($preVal) ? (float) $preVal : 0;
                    $postNum = is_numeric($postVal) ? (float) $postVal : 0;
                    $preScore += $preNum;
                    $postScore += $postNum;
                } elseif (in_array($question->question_type, ['radio', 'select'])) {
                    // كل طرف يُسجَّل بخيارات/درجات سؤاله الخاص
                    if (! empty($question->option_scores)) {
                        $preOptions = $question->options ?? [];
                        $preScores = $question->option_scores ?? [];
                        $preIdx = array_search($preVal, $preOptions, true);
                        $preScore += ($preIdx !== false && isset($preScores[$preIdx])) ? (int) $preScores[$preIdx] : 0;
                    }
                    $postScoreSource = $postQuestion ?: $question;
                    if (! empty($postScoreSource->option_scores)) {
                        $postOptions = $postScoreSource->options ?? [];
                        $postScores = $postScoreSource->option_scores ?? [];
                        $postIdx = array_search($postVal, $postOptions, true);
                        $postScore += ($postIdx !== false && isset($postScores[$postIdx])) ? (int) $postScores[$postIdx] : 0;
                    }
                }

                $questionDetails[] = [
                    'question' => $question->question_text,
                    'type' => $question->question_type,
                    'pre_answer' => $preVal,
                    'post_answer' => $postVal,
                ];
            }

            $improvement = $questions->count() > 0 ? (($postScore - $preScore) / max($questions->count(), 1)) * 100 : 0;
            $totalImprovement += $improvement;
            $studentCount++;

            $comparison[] = [
                'user' => $preResponse->user,
                'pre_score' => $preScore,
                'post_score' => $postScore,
                'improvement' => round($improvement, 1),
                'details' => $questionDetails,
                'pre_date' => $preResponse->created_at,
                'post_date' => $postResponse->created_at,
            ];
        }

        // متوسّطات القبلي/البعدي عبر الطلاب — يقرؤها قالب المقارنة (الأشرطة/البطاقات).
        // كانت غائبة عن المُخرَج فيقع «Undefined array key» في القالب → ErrorException → 500.
        $averagePre = $studentCount > 0 ? array_sum(array_column($comparison, 'pre_score')) / $studentCount : 0;
        $averagePost = $studentCount > 0 ? array_sum(array_column($comparison, 'post_score')) / $studentCount : 0;

        return [
            'pre_survey' => $preSurvey,
            'post_survey' => $postSurvey,
            'lesson' => $preSurvey->lesson,
            'value' => $preSurvey->value,
            'questions' => $questions,
            'comparison' => $comparison,
            'average_pre' => round($averagePre, 1),
            'average_post' => round($averagePost, 1),
            'average_improvement' => $studentCount > 0 ? round($totalImprovement / $studentCount, 1) : 0,
            'stats' => [
                'total_pre_responses' => $preResponses->count(),
                'total_post_responses' => $postResponses->count(),
                'completed_both' => $studentCount,
                'avg_improvement' => $studentCount > 0 ? round($totalImprovement / $studentCount, 1) : 0,
                'improved_count' => collect($comparison)->where('improvement', '>', 0)->count(),
                'declined_count' => collect($comparison)->where('improvement', '<', 0)->count(),
                'same_count' => collect($comparison)->where('improvement', '=', 0)->count(),
            ],
        ];
    }

    /**
     * أسئلة الاستبيان
     */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order');
    }

    /**
     * إجابات الاستبيان
     */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * منشئ الاستبيان
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * المدرسة المستهدفة
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * هل الاستبيان نشط حالياً؟
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * هل المستخدم أجاب على هذا الاستبيان؟
     */
    public function hasUserResponded($userId): bool
    {
        return $this->responses()->where('user_id', $userId)->exists();
    }

    /**
     * الاستبيانات المعلقة للمستخدم
     */
    public static function getPendingSurveysForUser($user)
    {
        // تحويل role المستخدم إلى target_type كما هو مخزّن في target_roles
        $targetType = self::roleToTargetType($user->role);

        // إذا لم يوجد mapping (مثل super_admin)، لا نُظهر له استبيانات
        if (! $targetType) {
            return collect();
        }

        // Issue #21: نُظهر أي استبيان إلزامي حتى لو لم يُحدد is_popup صراحةً.
        // التحقق بـ (is_mandatory OR is_popup) بدلاً من إجبار الاثنين معاً.
        return self::where('status', 'active')
            ->where(function ($q) {
                $q->where('is_mandatory', true)
                    ->orWhere('is_popup', true);
            })
            // استبعاد استبيانات الدرس/القيمة واليدوية من النافذة العامة — تُعرض في سياق الدرس/القيمة لا كـ popup عام (Issue 19)
            ->where(function ($q) {
                $q->whereNull('trigger_type')
                    ->orWhereNotIn('trigger_type', ['on_lesson_start', 'on_lesson_complete', 'on_value_start', 'on_value_complete', 'manual']);
            })
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('school_id')
                    ->orWhere('school_id', $user->school_id);
            })
            ->where(function ($query) use ($targetType, $user) {
                // البحث بـ target_type (مثل 'teachers') أو بـ role مباشرة (مثل 'teacher')
                // لدعم كلا تنسيقَي التخزين القديم والجديد
                $query->whereJsonContains('target_roles', $targetType)
                    ->orWhereJsonContains('target_roles', $user->role);
            })
            ->whereDoesntHave('responses', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('questions')
            ->get();
    }

    /**
     * استبيان تقييم (قبلي/بعدي) مُعلَّق لدرس معيّن — يُعرَض في سياق الدرس لا كنافذة عامة.
     * يُرجع الاستبيان النشط لهذا الدرس والطور (pre/post) الذي يستهدف دور المستخدم ولم يُجب عليه بعد.
     */
    public static function pendingLessonSurveyFor($user, int $lessonId, string $phase): ?self
    {
        $targetType = self::roleToTargetType($user->role);
        if (! $targetType) {
            return null;
        }

        return self::where('status', 'active')
            ->where('survey_type', 'pre_post_assessment')
            ->where('assessment_phase', $phase)
            ->where('lesson_id', $lessonId)
            ->where(function ($q) use ($user) {
                $q->whereNull('school_id')->orWhere('school_id', $user->school_id);
            })
            ->where(function ($q) use ($targetType, $user) {
                $q->whereJsonContains('target_roles', $targetType)
                    ->orWhereJsonContains('target_roles', $user->role);
            })
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->whereDoesntHave('responses', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('id')
            ->first();
    }

    /**
     * استبيان تقييم (قبلي/بعدي) مُعلَّق لقيمة معيّنة — يُعرَض في سياق القيمة لا كنافذة عامة.
     * يُرجع الاستبيان النشط لهذه القيمة والطور (pre/post) الذي يستهدف دور المستخدم ولم يُجب عليه بعد.
     */
    public static function pendingValueSurveyFor($user, int $valueId, string $phase): ?self
    {
        $targetType = self::roleToTargetType($user->role);
        if (! $targetType) {
            return null;
        }

        return self::where('status', 'active')
            ->where('survey_type', 'pre_post_assessment')
            ->where('assessment_phase', $phase)
            ->where('value_id', $valueId)
            ->where(function ($q) use ($user) {
                $q->whereNull('school_id')->orWhere('school_id', $user->school_id);
            })
            ->where(function ($q) use ($targetType, $user) {
                $q->whereJsonContains('target_roles', $targetType)
                    ->orWhereJsonContains('target_roles', $user->role);
            })
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->whereDoesntHave('responses', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('id')
            ->first();
    }
}
