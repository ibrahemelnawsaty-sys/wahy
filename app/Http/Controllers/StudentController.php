<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\ActivityUserStreak;
use App\Models\Badge;
use App\Models\Coin;
use App\Models\Lesson;
use App\Models\Point;
use App\Models\Setting;
use App\Models\User;
use App\Models\Value;
use App\Services\AwardService;
use App\Support\BadgeMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * لوحة التحكم الرئيسية للطالب - محسّنة
     */
    public function dashboard()
    {
        $user = Auth::user()->load('streak');
        $school = $user->school;

        // التاج يُمنح عند إتقان القيمة — مزامنة قبل حساب الإحصائيات ليكون عدّ التيجان محدّثاً (Issue 52)
        $this->syncCrowns($user);

        // استخدام helper للإحصائيات
        $stats = $this->getStudentStats($user);

        // Streak اليومي
        $streak = $user->streak;

        // الشارات المكتسبة - مع تحديد الحقول
        $badges = $user->badges()
            ->select(['badges.id', 'badges.name', 'badges.icon', 'badges.description'])
            ->latest('earned_at')
            ->take(6)
            ->get();

        // آخر الأنشطة المنجزة - تحسين Eager Loading
        $recentActivities = ActivitySubmission::where('student_id', $user->id)
            ->with(['activity:id,title,lesson_id', 'activity.lesson:id,title'])
            ->select(['id', 'activity_id', 'status', 'score', 'created_at'])
            ->latest()
            ->take(5)
            ->get();

        // الواجبات المنزلية القادمة (لم يتم تسليمها بعد)
        $classroomIds = $user->classrooms()->pluck('classrooms.id')->toArray();

        $upcomingHomework = Activity::where('is_homework', true)
            ->where('status', 'active')
            // النشر المباشر لمدرسة الطالب يستبدل بوّابة الاعتماد ويُغلق تسريب
            // orWhereNull(classroom_id) العالميّ: لا يظهر إلا ما نُشِر مباشرةً لمدرسته.
            ->visibleToStudent($user->school_id, $user->classrooms->pluck('id')->all())
            ->where(function ($query) use ($classroomIds) {
                $query->whereIn('classroom_id', $classroomIds)
                    ->orWhereNull('classroom_id');
            })
            ->whereDoesntHave('submissions', function ($q) use ($user) {
                $q->where('student_id', $user->id);
            })
            ->whereNotNull('due_date')
            ->where('due_date', '>', now())
            ->orderBy('due_date', 'asc')
            ->take(3)
            ->get();

        // إضافة حالة (عادي / قريب / متأخر)
        foreach ($upcomingHomework as $homework) {
            $hoursLeft = now()->diffInHours($homework->due_date);
            if ($hoursLeft < 24) {
                $homework->urgency = 'urgent'; // أحمر
            } elseif ($hoursLeft < 48) {
                $homework->urgency = 'soon'; // برتقالي
            } else {
                $homework->urgency = 'normal'; // عادي
            }
        }

        // قيم المدرسة المرئيّة — بطاقة «الدرس الحاليّ» يجب أن تقع ضمنها، وإلّا فتحُها يُرجِع 404
        // (نفس بوّابة lesson()). كان currentLesson غير مقيّد بها فتُنتَج بطاقة بطلٍ لبابٍ مغلق.
        $visibleValueIds = $user->school_id
            ? \App\Models\Value::visibleForSchool($user->school_id)->pluck('id')->all()
            : \App\Models\Value::pluck('id')->all();

        // الدرس الحالي (آخر درس بدأ فيه)
        $currentLesson = Lesson::where('status', 'active')
            ->whereHas('concept', fn ($q) => $q->whereIn('value_id', $visibleValueIds))
            ->whereHas('activities', function ($query) use ($user) {
                $query->whereHas('submissions', function ($q) use ($user) {
                    $q->where('student_id', $user->id)
                        ->where('status', '!=', 'completed');
                });
            })
            ->with(['concept.value'])
            ->first();

        // إذا لم يكن هناك درس جاري، نجيب أول درس متاح ضمن قيم المدرسة المرئيّة
        if (! $currentLesson) {
            $currentLesson = Lesson::where('status', 'active')
                ->whereHas('concept', fn ($q) => $q->whereIn('value_id', $visibleValueIds))
                ->with(['concept.value'])
                ->first();
        }

        // حساب تقدم الدرس الحالي (نعتبر أي تسليم إنجازاً للنشاط)
        if ($currentLesson) {
            $activityIds = $currentLesson->activities()->where('status', 'active')->visibleToStudent($user->school_id, $user->classrooms->pluck('id')->all())->pluck('id')->toArray();
            $totalActivities = count($activityIds);
            $completedActivities = ActivitySubmission::where('student_id', $user->id)
                ->whereIn('activity_id', $activityIds)
                ->whereIn('status', ['completed', 'approved', 'pending', 'needs_review'])
                ->count();
            $currentLesson->progress = $totalActivities > 0 ? round(($completedActivities / $totalActivities) * 100) : 0;
        }

        // جلب القيم المفعّلة لمدرسة الطالب (Issue 11/105) — دروس وأنشطة نشطة فقط
        $values = Value::visibleForSchool($user->school_id)
            ->with([
                'concepts.lessons' => fn ($q) => $q->where('status', 'active'),
                'concepts.lessons.activities' => fn ($q) => $q->where('status', 'active')->visibleToStudent($user->school_id, $user->classrooms->pluck('id')->all()),
            ])
            ->orderBy('order')
            ->get();

        // تعريف موحّد للإكمال: completed/approved فقط (لا pending) — متّسق مع الإحصائيات والإتقان
        $doneStatuses = ActivitySubmission::DONE_STATUSES;

        $completedLessonIds = ActivitySubmission::where('student_id', $user->id)
            ->whereIn('activity_submissions.status', $doneStatuses)
            ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
            ->distinct()
            ->pluck('activities.lesson_id')
            ->toArray();

        // حساب الأنشطة المكتملة
        $completedActivityIds = ActivitySubmission::where('student_id', $user->id)
            ->whereIn('status', $doneStatuses)
            ->pluck('activity_id')
            ->toArray();

        // حساب التقدم لكل قيمة
        // Issue #59-#60: كل القيم المرئية مفتوحة — لا قفل تتابعي. كل قيمة عرضها مستقل
        // فيتجنب حالة "نشاط مكتمل لكن القيمة تظهر مقفلة".
        foreach ($values as $index => $value) {
            // حساب إجمالي الدروس في القيمة
            $totalLessons = $value->concepts->sum(function ($concept) {
                return $concept->lessons->where('status', 'active')->count();
            });

            // حساب الدروس المكتملة في القيمة — strict comparison لمنع type juggling
            $completedLessons = $value->concepts->sum(function ($concept) use ($completedLessonIds) {
                return $concept->lessons->where('status', 'active')->filter(function ($lesson) use ($completedLessonIds) {
                    return in_array($lesson->id, $completedLessonIds, true);
                })->count();
            });

            // حساب النسبة المئوية
            $value->progress_percent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

            // تحديد حالة القيمة
            $value->is_completed = $totalLessons > 0 && $value->progress_percent >= 100;
            $value->is_unlocked = true; // كل القيم المرئية متاحة

            // حساب حالة كل مفهوم
            foreach ($value->concepts as $concept) {
                $conceptTotalLessons = $concept->lessons->where('status', 'active')->count();

                $conceptCompletedLessons = $concept->lessons->where('status', 'active')->filter(function ($lesson) use ($completedLessonIds) {
                    return in_array($lesson->id, $completedLessonIds, true);
                })->count();

                $concept->is_completed = $conceptTotalLessons > 0 && $conceptCompletedLessons >= $conceptTotalLessons;

                // حساب حالة كل درس — strict comparison
                foreach ($concept->lessons as $lesson) {
                    $lesson->is_completed = in_array($lesson->id, $completedLessonIds, true);

                    // حساب حالة كل نشاط
                    foreach ($lesson->activities as $activity) {
                        $activity->is_completed = in_array($activity->id, $completedActivityIds, true);
                    }
                }
            }
        }

        // ترتيب القيم: القيمة "قيد التقدم" أولاً، ثم المكتملة، ثم المقفلة
        $values = $values->sortBy(function ($value) {
            if ($value->is_unlocked && ! $value->is_completed) {
                return 0; // القيمة قيد التقدم - الأولوية الأولى
            } elseif ($value->is_completed) {
                return 1; // القيمة المكتملة - الأولوية الثانية
            } else {
                return 2; // القيمة المقفلة - الأولوية الثالثة
            }
        })->values();

        // استخدام نفس الـ view مع المتغير الصحيح
        $totalPoints = $stats['total_points'] ?? 0;

        // streak الالتزام لكل درس — تحميل واحد keyBy(lesson_id) لتجنّب N+1 في القائمة
        $lessonStreaks = $user->lessonStreaks->keyBy('lesson_id');

        return view('student.dashboard', compact(
            'user',
            'school',
            'stats',
            'badges',
            'streak',
            'recentActivities',
            'upcomingHomework',
            'currentLesson',
            'values',
            'totalPoints',
            'lessonStreaks',
        ));
    }

    /**
     * صفحة خريطة التعلم
     */
    public function learningPath()
    {
        $user = Auth::user();

        // جلب القيم المرئية لمدرسة الطالب فقط (Issue #105)
        $values = Value::visibleForSchool($user->school_id)
            ->with(['concepts' => function ($query) {
                $query->orderBy('order')
                    ->with(['lessons' => function ($l) {
                        $l->where('status', 'active')
                            ->orderBy('order');
                    }]);
            }])
            ->orderBy('order')
            ->get();

        // حساب حالة كل درس (completed, current, locked)
        $completedLessonIds = ActivitySubmission::where('student_id', $user->id)
            ->where('activity_submissions.status', 'completed')
            ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
            ->distinct()
            ->pluck('activities.lesson_id')
            ->toArray();

        // P2-F: قراءة إعداد القفل التتابعي — افتراضي معطّل (كل الدروس متاحة).
        // admin يقدر يفعّله من settings إن أراد سلوك تتابعي صارم.
        $sequentialLock = optional(Setting::where('key', 'sequential_lesson_lock')->whereNull('user_id')->first())->value === '1';

        $allLessons = [];
        $currentLessonId = null;
        $foundCurrent = false;

        foreach ($values as $value) {
            foreach ($value->concepts as $concept) {
                foreach ($concept->lessons as $lesson) {
                    $allLessons[] = $lesson->id;

                    // تحديد الحالة
                    if (in_array($lesson->id, $completedLessonIds, true)) {
                        $lesson->is_completed = true;
                        $lesson->is_current = false;
                        $lesson->is_locked = false;
                    } else {
                        $lesson->is_completed = false;

                        // أول درس غير مكتمل = الدرس الحالي
                        if (! $foundCurrent) {
                            $lesson->is_current = true;
                            $lesson->is_locked = false;
                            $currentLessonId = $lesson->id;
                            $foundCurrent = true;
                        } else {
                            $lesson->is_current = false;
                            // إذا الإعداد مفعّل → القفل التتابعي، وإلا الدرس مفتوح
                            $lesson->is_locked = $sequentialLock;
                        }
                    }
                }
            }
        }

        // إحصائيات التقدم
        $totalLessons = count($allLessons);
        $completedLessons = count($completedLessonIds);
        $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

        // الحصول على stats للـ layout
        $user->load('streak');
        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        return view('student.path', compact(
            'values',
            'totalLessons',
            'completedLessons',
            'progressPercent',
            'stats',
            'streak',
        ));
    }

    /**
     * Helper method للحصول على إحصائيات الطالب
     */
    /**
     * cache على مستوى الـ request — يمنع تكرار حساب الإحصاءات في نفس الصفحة.
     */
    private array $studentStatsCache = [];

    private function getStudentStats($user)
    {
        if (isset($this->studentStatsCache[$user->id])) {
            return $this->studentStatsCache[$user->id];
        }

        // تعريف موحّد للإكمال: completed/approved فقط (لا pending) — متّسق مع تقدّم القيمة/الدرس.
        // pending/needs_review تُعرض منفصلة كـ "قيد المراجعة". (Issues 51, 59, 60)
        $submissionStats = ActivitySubmission::where('student_id', $user->id)
            ->selectRaw("
                SUM(CASE WHEN status IN ('completed','approved') THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status IN ('pending','needs_review') THEN 1 ELSE 0 END) as pending_count,
                AVG(CASE WHEN score IS NOT NULL THEN score END) as avg_score,
                SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as completed_today
            ", [now()->toDateString()])
            ->first();

        // ❗ استخدام subqueries منفصلة لتجنّب cartesian product بين points و coins
        // الذي كان يُضاعف القيم (سبب الفرق بين 690 في الواجهة و 345 في الترتيب)
        $totalPoints = (int) DB::table('points')
            ->where('user_id', $user->id)
            ->sum('points');
        $totalCoins = (int) DB::table('coins')
            ->where('user_id', $user->id)
            ->sum('coins');
        $totals = (object) [
            'total_points' => $totalPoints,
            'total_coins' => $totalCoins,
        ];

        // Get streak with null check - ensure user always has a streak record
        try {
            if (! $user->relationLoaded('streak')) {
                $user->load('streak');
            }

            if (! $user->streak) {
                $user->streak = \App\Models\Streak::create([
                    'user_id' => $user->id,
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'last_activity_date' => null,
                ]);
            }

            $currentStreak = $user->streak->current_streak ?? 0;
        } catch (\Exception $e) {
            Log::error('Streak error for user ' . $user->id . ': ' . $e->getMessage());
            $currentStreak = 0;
        }

        $result = [
            'total_points' => (int) ($totals->total_points ?? 0),
            'total_coins' => (int) ($totals->total_coins ?? 0),
            'total_badges' => $user->badges()->count(),
            'total_crowns' => $user->crowns()->count(),
            'current_streak' => (int) $currentStreak,
            'completed_activities' => (int) ($submissionStats->completed_count ?? 0),
            'pending_activities' => (int) ($submissionStats->pending_count ?? 0),
            'average_score' => round($submissionStats->avg_score ?? 0, 1),
            'completed_today' => (int) ($submissionStats->completed_today ?? 0),
        ];

        $this->studentStatsCache[$user->id] = $result;

        return $result;
    }

    /**
     * معرّفات القيم المتقَنة: القيمة المرئية التي اكتملت كل دروسها النشطة فعلياً.
     * تعريف موحّد للإكمال (completed/approved فقط — لا pending) يُستخدم للتيجان والإتقان.
     * (Issues 52, 59, 60)
     */
    private function masteredValueIds($user): array
    {
        $completedActivityIds = ActivitySubmission::where('student_id', $user->id)
            ->whereIn('status', ActivitySubmission::DONE_STATUSES)
            ->pluck('activity_id')->unique()->all();

        if (empty($completedActivityIds)) {
            return [];
        }

        $values = Value::visibleForSchool($user->school_id)
            ->with(['concepts.lessons.activities' => fn ($q) => $q->where('status', 'active')->visibleToStudent($user->school_id, $user->classrooms->pluck('id')->all())])
            ->get();

        $mastered = [];
        foreach ($values as $value) {
            $total = 0;
            $done = 0;
            foreach ($value->concepts as $concept) {
                foreach ($concept->lessons->where('status', 'active') as $lesson) {
                    $actIds = $lesson->activities->where('status', 'active')->pluck('id')->all();
                    if (empty($actIds)) {
                        continue;
                    }
                    $total++;
                    if (count(array_diff($actIds, $completedActivityIds)) === 0) {
                        $done++;
                    }
                }
            }
            if ($total > 0 && $done >= $total) {
                $mastered[] = $value->id;
            }
        }

        return $mastered;
    }

    /**
     * إنشاء صفوف Crown للقيم المتقَنة (idempotent): التاج يُمنح عند إتقان القيمة (Issue 52).
     * يُوحّد مصدر التيجان بين صفحتي التيجان والشارات والإحصائيات.
     */
    private function syncCrowns($user): void
    {
        try {
            foreach ($this->masteredValueIds($user) as $vid) {
                \App\Models\Crown::firstOrCreate(
                    ['user_id' => $user->id, 'value_id' => $vid],
                    ['earned_at' => now()],
                );
            }
        } catch (\Throwable $e) {
            Log::warning('syncCrowns failed for user ' . ($user->id ?? '?') . ': ' . $e->getMessage());
        }
    }

    /**
     * يضمن وجود تحدٍّ عام فعّال (طالب ضد طالب) متى توفّرت أسئلة معتمدة — تفعيل أساسي لميزة PvP (Issue 70).
     * يُنشئ صفّاً حقيقياً (لا كائناً وهمياً) ليظهر في صفحة التمارين والصالة معاً ويعمل معه joinPvpMatch.
     * محميّ بـ hasTable حتى لا ينكسر في بيئات لم تُنشأ فيها جداول PvP بعد.
     */
    private function ensureDefaultPvpChallenge(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('pvp_challenges')) {
            return;
        }
        try {
            if (\App\Models\PvpChallenge::where('is_active', true)->exists()) {
                return;
            }
            $approvedIds = \App\Models\QuestionBank::where('status', 'approved')
                ->limit(10)->pluck('id')->toArray();
            if (count($approvedIds) < 3) {
                return; // لا توجد أسئلة معتمدة كافية بعد
            }
            \App\Models\PvpChallenge::create([
                'title' => 'تحدي القيم — السرعة والدقة',
                'value_id' => null, // تحدٍّ عام لكل المدارس
                'questions' => $approvedIds,
                'time_limit' => 30,
                'difficulty' => 'medium',
                'is_active' => true,
                'created_by' => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ensureDefaultPvpChallenge failed: ' . $e->getMessage());
        }
    }

    /**
     * صفحة الدرس
     */
    public function lesson($id)
    {
        $user = Auth::user();

        // جلب الدرس مع العلاقات
        $lesson = Lesson::with(['concept.value', 'activities'])
            ->findOrFail($id);

        // Pass-4 Batch 3 (BOLA): الدرس يتبع concept->value. امنع فتح محتوى
        // مدرسة أخرى — يجب أن تكون القيمة مرئية لمدرسة الطالب.
        $value = optional($lesson->concept)->value;
        if (! $value || ! Value::visibleForSchool($user->school_id)->whereKey($value->id)->exists()) {
            abort(404);
        }

        // درس غير نشط لا يُفتح للطالب (حتى بالرابط المباشر)
        if (($lesson->status ?? 'active') !== 'active') {
            abort(404);
        }

        // جلب الأنشطة النشطة فقط مرتّبة — لا تُعرض الأنشطة غير النشطة للطالب
        $activities = $lesson->activities()->where('status', 'active')->visibleToStudent($user->school_id, $user->classrooms->pluck('id')->all())->orderBy('order')->get();

        // ✅ استعلام واحد فقط لكل تسليمات الطالب على هذه الأنشطة (إصلاح N+1)
        $submissionsByActivity = ActivitySubmission::where('student_id', $user->id)
            ->whereIn('activity_id', $activities->pluck('id'))
            ->get()
            ->keyBy('activity_id');

        // قاموس مرجعي للأنشطة بترتيبها
        $activitiesByOrder = $activities->keyBy('order');

        $activities = $activities->map(function ($activity) use ($submissionsByActivity, $activitiesByOrder) {
            $submission = $submissionsByActivity->get($activity->id);

            if ($submission) {
                $activity->status = $submission->status;
                $activity->score = $submission->score;

                return $activity;
            }

            // P2-F: قفل تتابعي للأنشطة — افتراضي معطّل
            $sequentialActivityLock = optional(Setting::where('key', 'sequential_activity_lock')->whereNull('user_id')->first())->value === '1';

            if (! $sequentialActivityLock) {
                $activity->status = 'available';

                return $activity;
            }

            // النشاط السابق مباشرة عبر الترتيب — بدون query جديد
            $previousOrder = $activity->order - 1;
            while ($previousOrder >= 0 && ! $activitiesByOrder->has($previousOrder)) {
                $previousOrder--;
            }
            $previousActivity = $previousOrder >= 0 ? $activitiesByOrder->get($previousOrder) : null;

            if ($previousActivity) {
                $previousDone = $submissionsByActivity->has($previousActivity->id);
                $activity->status = $previousDone ? 'available' : 'locked';
            } else {
                $activity->status = 'available';
            }

            return $activity;
        });

        // حساب التقدم
        $totalActivities = $activities->count();
        $completedActivities = $activities->whereIn('status', ['completed', 'pending', 'approved', 'needs_review'])->count();
        $completionPercent = $totalActivities > 0 ? round(($completedActivities / $totalActivities) * 100) : 0;

        // النشاط التالي
        $nextActivity = $activities->whereIn('status', ['available', 'completed', 'pending'])->first();

        // الحصول على stats للـ layout
        $user->load('streak');
        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        // بيانات streak الدرس
        $lessonStreak = null;
        if ($lesson->hasStreakEnabled()) {
            $lessonStreak = $user->getOrCreateLessonStreak($lesson->id);
        }

        // استبيانات التقييم (قبلي/بعدي) في سياق الدرس — تُعرض هنا لا كنافذة عامة
        $preSurvey = \App\Models\Survey::pendingLessonSurveyFor($user, $lesson->id, 'pre');
        $postSurvey = ($totalActivities > 0 && $completedActivities >= $totalActivities)
            ? \App\Models\Survey::pendingLessonSurveyFor($user, $lesson->id, 'post')
            : null;

        // استبيانات التقييم (قبلي/بعدي) على مستوى القيمة — تلقائي مع تقدّم القيمة
        // القبلي: عند بدء الطالب أيّ درس في القيمة (أوّل تفاعل). البعدي: عند إتقان كل دروس القيمة.
        $__value = optional(optional($lesson->concept)->value);
        $valuePreSurvey = $__value->id
            ? \App\Models\Survey::pendingValueSurveyFor($user, $__value->id, 'pre')
            : null;
        // نُقدّم الاستعلام الرخيص (استبيان قيمة بعديّ معلّق) قبل مسح الإتقان الثقيل، فلا يُشغَّل
        // masteredValueIds (تحميل شجرة المحتوى كاملة) إلا عند وجود مرشّح فعليّ — تفادياً لانحدار
        // الأداء على أكثر صفحة زيارةً (عرض الدرس).
        $valuePostSurvey = null;
        if ($__value->id) {
            $__valuePostCandidate = \App\Models\Survey::pendingValueSurveyFor($user, $__value->id, 'post');
            if ($__valuePostCandidate && in_array($__value->id, $this->masteredValueIds($user), true)) {
                $valuePostSurvey = $__valuePostCandidate;
            }
        }

        return view('student.lesson-view', compact(
            'lesson',
            'activities',
            'totalActivities',
            'completedActivities',
            'completionPercent',
            'nextActivity',
            'stats',
            'streak',
            'lessonStreak',
            'preSurvey',
            'postSurvey',
            'valuePreSurvey',
            'valuePostSurvey',
        ));
    }

    // practice() method moved to bottom with exercise system

    /**
     * صفحة الترتيب
     */
    public function leaderboard()
    {
        $user = Auth::user();
        $period = request('period', 'week');

        // تحديد فلتر الفترة الزمنية
        $dateFrom = null;
        if ($period === 'week') {
            $dateFrom = now()->startOfWeek();
        } elseif ($period === 'month') {
            $dateFrom = now()->startOfMonth();
        }
        // 'all' = no date filter

        // جلب الطلاب النشطين فقط — توحيد مع LeaderboardController
        // مع subquery لجمع النقاط في استعلام واحد بدلًا من N+1
        $studentsQuery = User::where('role', 'student')
            ->where('school_id', $user->school_id)
            ->where('status', 'active')
            ->with('school:id,name');

        $allStudents = $studentsQuery
            ->withSum(['points as total_xp' => function ($q) use ($dateFrom) {
                if ($dateFrom) {
                    $q->where('created_at', '>=', $dateFrom);
                }
            }], 'points')
            ->orderByDesc('total_xp')
            ->orderBy('id') // tie-break ثابت لمنع تذبذب الترتيب
            ->get()
            ->map(function ($student) {
                $student->total_xp = (int) ($student->total_xp ?? 0);

                return $student;
            });

        // إضافة الترتيب الفعلي قبل أي slicing لتفادي اختلال الأرقام
        $allStudents = $allStudents->values()->map(function ($student, $i) {
            $student->actual_rank = $i + 1;

            return $student;
        });

        // Top 3 + باقي القائمة — عند وجود أقل من 3 طلاب لا نعرض منصة ونضع الجميع في القائمة
        // (يمنع فجوة/قائمة فارغة كانت تحدث مع slice(3) الثابت — Issue 50)
        if ($allStudents->count() >= 3) {
            $topThree = $allStudents->take(3)->values();
            $leaderboard = $allStudents->slice(3, 20)->values();
        } else {
            $topThree = collect();
            $leaderboard = $allStudents->take(20)->values();
        }

        // يستخدمها الـ view كرقم بدء افتراضي إن لم يجد actual_rank في الكائن
        $leaderboardStartRank = $topThree->count() + 1;

        // ترتيب الطالب الحالي — التعامل الصحيح مع false (غير موجود)
        $myRankIndex = $allStudents->search(function ($student) use ($user) {
            return $student->id === $user->id;
        });
        $myRank = $myRankIndex === false ? null : $myRankIndex + 1;

        // نقاط الطالب الحالي ضمن نفس الفترة المعروضة — لتتطابق بطاقة "ترتيبك" مع القائمة
        // بدل خلط "كل الأوقات" (stats) مع نقاط الفترة (Issue 49)
        if ($myRankIndex !== false) {
            $myPeriodXp = (int) $allStudents[$myRankIndex]->total_xp;
        } else {
            $myPeriodXp = (int) Point::where('user_id', $user->id)
                ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->sum('points');
        }

        $user->load('streak');
        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        return view('student.leaderboard', compact('topThree', 'leaderboard', 'leaderboardStartRank', 'myRank', 'myPeriodXp', 'stats', 'streak', 'period'));
    }

    /**
     * صفحة الملف الشخصي
     */
    public function profile()
    {
        try {
            $student = Auth::user();

            // Ensure streak exists before anything else
            $streak = \App\Models\Streak::firstOrCreate(
                ['user_id' => $student->id],
                [
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'last_activity_date' => null,
                ],
            );

            // Load relationships
            $student->load('streak', 'badges');

            // Get stats
            $stats = $this->getStudentStats($student);

            // Calculate level (every 100 XP = 1 level)
            $level = floor($stats['total_points'] / 100) + 1;

            // Get all badges earned
            $badges = Badge::whereHas('users', function ($query) use ($student) {
                $query->where('user_id', $student->id);
            })->get();

            return view('student.profile-view', compact('student', 'stats', 'level', 'badges', 'streak'));

        } catch (\Exception $e) {
            Log::error('Profile error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());

            // Return safe defaults
            $student = Auth::user();
            $stats = [
                'total_points' => 0,
                'total_coins' => 0,
                'total_badges' => 0,
                'current_streak' => 0,
                'completed_activities' => 0,
                'pending_activities' => 0,
                'average_score' => 0,
                'completed_today' => 0,
            ];
            $level = 1;
            $badges = collect([]);
            $streak = null;

            return view('student.profile-view', compact('student', 'stats', 'level', 'badges', 'streak'));
        }
    }

    /**
     * عرض المتجر
     */
    public function shop()
    {
        $user = Auth::user();
        $user->load('streak');
        $streak = $user->streak;

        // Get student stats
        $stats = $this->getStudentStats($user);

        // Get shop items from database
        $shopItems = \App\Models\ShopItem::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('available_until')
                    ->orWhere('available_until', '>', now());
            })
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.shop-view', compact('stats', 'shopItems', 'streak'));
    }

    /**
     * صفحة النشاط الفردي
     */
    public function activity($id)
    {
        $user = Auth::user();
        $user->load('streak');

        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        $activity = Activity::findOrFail($id);
        $lesson = $activity->lesson;

        // ✅ Authorization: تحقق أن النشاط ضمن قيمة مفعّلة لمدرسة الطالب
        if (! $this->isActivityAccessibleByStudent($activity, $user)) {
            abort(403, 'هذا النشاط غير متاح لك');
        }

        // Check if already submitted
        $submission = ActivitySubmission::where('student_id', $user->id)
            ->where('activity_id', $id)
            ->first();

        // بدء مؤقّت الاختبار الموقوت خادمياً (مرة واحدة لكل جلسة/نشاط) — أساس فرض الحد الزمني
        if (($activity->quiz_duration ?? null) && $activity->type === 'quiz' && ! $submission) {
            $key = "quiz_started_{$activity->id}";
            if (! session()->has($key)) {
                session()->put($key, now()->timestamp);
            }
        }

        // Find next active activity in same lesson (النشاط قد يكون بلا درس)
        $nextActivity = $lesson
            ? Activity::where('lesson_id', $lesson->id)
                ->where('status', 'active')
                ->visibleToStudent($user->school_id, $user->classrooms->pluck('id')->all())
                ->where('id', '>', $id)
                ->orderBy('id')
                ->first()
            : null;

        return view('student.activity-view', compact('activity', 'lesson', 'nextActivity', 'stats', 'streak', 'submission'));
    }

    /**
     * يتحقق أن النشاط ضمن قيمة مفعّلة لمدرسة الطالب.
     * يمنع طالباً من مدرسة A الوصول لأنشطة من قيمة غير مفعّلة لمدرسته.
     */
    private function isActivityAccessibleByStudent(Activity $activity, $student): bool
    {
        // مصدر وحيد موحّد مع الجوّال (Activity::isAccessibleByStudent): نشط + منشور مباشرةً
        // لمدرسته + ضمن قيمة مفعّلة لها. يمنع انحراف بوّابة القيمة بين الويب والـAPI.
        return $activity->isAccessibleByStudent($student);
    }

    /**
     * إرسال إجابة النشاط
     */
    public function submitActivity(Request $request, $id)
    {
        try {
            $student = Auth::user();
            $activity = Activity::findOrFail($id);

            // ✅ Authorization: تحقق أن النشاط ضمن قيمة مفعّلة لمدرسة الطالب
            if (! $this->isActivityAccessibleByStudent($activity, $student)) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا النشاط غير متاح لك',
                ], 403);
            }

            // حدّ زمني للاختبار الموقوت — يُفرَض بوقت الجلسة الخادمي (لا يتحكّم به العميل)
            if (($activity->quiz_duration ?? null) && $activity->type === 'quiz') {
                $startedAt = session("quiz_started_{$activity->id}");
                if ($startedAt && (now()->timestamp - (int) $startedAt) > (((int) $activity->quiz_duration) * 60 + 10)) {
                    session()->forget("quiz_started_{$activity->id}");

                    return response()->json([
                        'success' => false,
                        'message' => 'انتهى الوقت المحدد لهذا الاختبار.',
                    ], 422);
                }
            }

            // التحقق + دعم رفع الملفات (Issue 55)
            // الأنواع التي يكون الملفّ فيها إلزامياً (مطابق لـ$isFileRequired في القالب) — نفرضه
            // خادمياً بدل الاعتماد على required في input مخفيّ (كان زرّاً ميّتاً صامتاً).
            $requiresFile = $activity->type === 'upload' || ($activity->question_type ?? null) === 'file_upload';
            $rules = [
                'answer' => 'required',
                'xp' => 'nullable|integer',
            ];
            if ($requiresFile) {
                // قاعدة موحّدة (الموديل) تُطبَّق على الويب والـAPI معاً — تشتقّ الامتدادات من
                // الفئات المخزَّنة والسقف من max_file_size (كان mimes:image يرفض كلَّ ملفّ).
                $rules['answer_file'] = 'required|' . $activity->submissionFileRule();
            } elseif ($request->hasFile('answer_file')) {
                $rules['answer_file'] = $activity->submissionFileRule();
            }
            $request->validate($rules);

            $activityPoints = (int) ($activity->points ?? 10);

            // معالجة الملف المرفوع وتخزينه ضمن الإجابة (خارج المعاملة لأن I/O قد يستغرق وقتًا)
            $uploadedPath = null;
            if ($request->hasFile('answer_file')) {
                $uploadedPath = $request->file('answer_file')->store(
                    'activity-submissions/' . $student->id,
                    'public',
                );
            }

            $rawAnswer = $request->input('answer');

            // حساب الدرجة عبر الـ Service الموحّد لمنطق التصحيح
            $score = \App\Services\ActivityGradingService::grade($activity, $rawAnswer);

            // تطبيق حقول النشاط: درجة النجاح وعدد المحاولات
            $passing = \App\Services\ActivityGradingService::passingScoreFor($activity);
            $passed = ($score !== null && $score >= $passing);
            $maxAttempts = max(1, (int) ($activity->max_attempts ?? 1));

            // حالة التسليم:
            //  - score === null → pending (مراجعة/تصحيح يدوي من المعلم)
            //  - اجتاز درجة النجاح → completed (إنجاز نهائي)
            //  - لم يجتَز → needs_review: خارج حالات الإنجاز (لا يُضخّم الإتقان)،
            //    وقابل لإعادة المحاولة ضمن max_attempts، وليس ضمن طابور مراجعة المعلم (pending).
            $status = $score === null ? 'pending' : ($passed ? 'completed' : 'needs_review');

            // ميزة #23: نشاط يتطلّب موافقة وليّ الأمر — لا يدخل طابور المعلّم إلا بعد موافقته.
            $parentApprovalStatus = $activity->requires_parent_approval ? 'pending' : null;

            // تخزين مسار الملف ضمن الإجابة كـ JSON إن وجد
            $answerToStore = is_array($rawAnswer) ? json_encode($rawAnswer, JSON_UNESCAPED_UNICODE) : $rawAnswer;
            if ($uploadedPath) {
                $answerToStore = json_encode([
                    'note' => $rawAnswer,
                    'file' => $uploadedPath,
                    // نفس اصطلاح رفع صور الأنشطة العامل (القرص العام جذره storage/app/public/data)
                    'file_url' => asset('storage/app/public/data/' . ltrim($uploadedPath, '/')),
                ], JSON_UNESCAPED_UNICODE);
            }

            // تنفيذ ذرّي: فحص duplicate تحت قفل + إنشاء submission
            // #13 عدد المحاولات: يُسمح بإعادة الإرسال ما دامت المحاولات متبقية والنشاط لم يُعتمَد
            // نهائيًّا من المعلّم (approved). تشمل الإعادة needs_review/rejected/pending/completed
            // (الأخير لتحسين الدرجة). لا استغلال: المكافأة على «أفضل محاولة» (فرق تصاعديّ عبر
            // awarded_points) فإعادة نشاطٍ ناجح لا تُضاعف النقاط/العملات. approved وحده نهائيّ.
            try {
                $submissionResult = \Illuminate\Support\Facades\DB::transaction(function () use ($student, $id, $answerToStore, $status, $score, $maxAttempts, $parentApprovalStatus) {
                    // فحص duplicate تحت قفل صفّي — يمنع double-submit race
                    $existing = ActivitySubmission::where('student_id', $student->id)
                        ->where('activity_id', $id)
                        ->lockForUpdate()
                        ->first();

                    if ($existing) {
                        $attemptsUsed = (int) ($existing->attempts ?? 1);
                        $resubmittable = in_array($existing->status, ['needs_review', 'rejected', 'pending', 'completed'], true);

                        // إعادة المحاولة مسموحة إن لم يُعتمد بعد ولم تُستنفد المحاولات
                        if ($resubmittable && $attemptsUsed < $maxAttempts) {
                            // #13 «أفضل محاولة»: لا نُدهوِر أفضل نتيجة. إن كانت المحاولة الجديدة
                            // مُصحَّحة آليًّا وأقلّ من الدرجة المحفوظة، نُبقي الإجابة/الحالة/الدرجة
                            // الأفضل ونزيد المحاولات فقط — موافقةً لوعد الواجهة «تحتفظ بأفضل درجة»
                            // (وإلا هبطت completed→needs_review وتدهورت الدرجة المرئية وعدّاد الإنجاز).
                            $keepsBest = $score !== null && $existing->score !== null && $score < $existing->score;

                            $payload = [
                                'attempts' => $attemptsUsed + 1,
                                'submitted_at' => now(),
                            ];
                            if (! $keepsBest) {
                                $payload['answer'] = $answerToStore;
                                $payload['status'] = $status;
                                $payload['score'] = $score;
                                $payload['feedback'] = null;
                                // إعادة إرسال نشاطٍ يتطلّب موافقة الوليّ ⇒ يحتاج موافقة جديدة
                                $payload['parent_approval_status'] = $parentApprovalStatus;
                                $payload['parent_approved_by'] = null;
                                $payload['parent_approved_at'] = null;
                            }
                            $existing->update($payload);

                            return ['duplicate' => false, 'submission' => $existing, 'exhausted' => false];
                        }

                        // استُنفدت المحاولات دون اجتياز
                        if ($resubmittable && $attemptsUsed >= $maxAttempts) {
                            return ['duplicate' => true, 'submission' => null, 'exhausted' => true];
                        }

                        // مُعتمد نهائيًّا من المعلّم (approved) → لا إعادة إرسال
                        return ['duplicate' => true, 'submission' => null, 'exhausted' => false];
                    }

                    $submission = ActivitySubmission::create([
                        'student_id' => $student->id,
                        'activity_id' => $id,
                        'answer' => $answerToStore,
                        'status' => $status,
                        'score' => $score,
                        'attempts' => 1,
                        'submitted_at' => now(),
                        'parent_approval_status' => $parentApprovalStatus,
                    ]);

                    return ['duplicate' => false, 'submission' => $submission, 'exhausted' => false];
                }, 3);
            } catch (\Throwable $e) {
                if ($uploadedPath) {
                    try {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($uploadedPath);
                    } catch (\Throwable $ignore) {
                    }
                }
                throw $e;
            }

            if ($submissionResult['duplicate']) {
                if ($uploadedPath) {
                    try {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($uploadedPath);
                    } catch (\Throwable $ignore) {
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => ! empty($submissionResult['exhausted'])
                        ? 'استنفدت عدد محاولاتك لهذا النشاط (' . $maxAttempts . ').'
                        : 'تم إرسال هذا النشاط مسبقاً',
                ]);
            }

            // اجتياز موقوت مكتمل → امسح مؤقّت الجلسة
            if (($activity->quiz_duration ?? null) && $activity->type === 'quiz') {
                session()->forget("quiz_started_{$activity->id}");
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Activity submission failed [activity_id=' . $id . ']: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الإجابة',
            ], 500);
        }

        // === كل الكود التالي ثانوي — التسليم تم حفظه بنجاح ===
        // نلف الكل في try-catch واحد لضمان رجوع success دائماً
        $streakBonus = 0;
        $streakMessage = null;

        // النقاط الفعلية بناءً على الدرجة (درجة% × نقاط النشاط)؛ null (مراجعة يدوية) → 0 الآن.
        $xp = $score !== null ? (int) round(($score / 100) * $activityPoints) : 0;

        // #13: منح المكافأة على «أفضل محاولة» مرّة واحدة — نمنح فقط الفرق التصاعديّ عن awarded_points
        // المُسجَّل على التسليم، فلا تُضاعَف النقاط/العملات بإعادة نشاطٍ ناجح (completed retry) ولا
        // يخسر الطالب أفضل نتيجة إن تراجع. المراجعة اليدويّة (score=null) لا تُمنح الآن — تُمنح عند
        // تصحيح المعلّم. العملات تُشتقّ من XP فتتبعه بلا استغلال (كانت max(1,⌊0/2⌋)=1 على كل إعادة).
        $xpDelta = 0;
        $coinDelta = 0;

        try {
            if ($score !== null && ! empty($submissionResult['submission'])) {
                // قفل صفّيّ ذرّيّ: نحسب الفرق مقابل أفضل XP سابق ونُحدّثه (ضدّ سباق تسليمين)
                [$xpDelta, $coinDelta] = \Illuminate\Support\Facades\DB::transaction(function () use ($submissionResult, $xp) {
                    $sub = ActivitySubmission::lockForUpdate()->find($submissionResult['submission']->id);
                    if (! $sub) {
                        return [0, 0];
                    }
                    $priorXp = (int) ($sub->awarded_points ?? 0);
                    $priorCoins = $priorXp > 0 ? max(1, (int) floor($priorXp / 2)) : 0;
                    $curCoins = $xp > 0 ? max(1, (int) floor($xp / 2)) : 0;
                    if ($xp > $priorXp) {
                        $sub->update(['awarded_points' => $xp]); // أفضل XP حتى الآن
                    }

                    return [max(0, $xp - $priorXp), max(0, $curCoins - $priorCoins)];
                }, 3);
            }

            // Add XP (الفرق التصاعديّ فقط)
            if ($xpDelta > 0) {
                try {
                    Point::create([
                        'user_id' => $student->id,
                        'points' => $xpDelta,
                        'reason' => 'إكمال نشاط: ' . $activity->title,
                        'activity_id' => $activity->id,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Point creation failed: ' . $e->getMessage());
                }

                try {
                    $this->distributePoints($student, $xpDelta, 'activity_completion', $activity->title);
                } catch (\Throwable $e) {
                    Log::error('Distribute points failed: ' . $e->getMessage());
                }
            }

            // Add coins (الفرق التصاعديّ فقط)
            if ($coinDelta > 0) {
                try {
                    Coin::create([
                        'user_id' => $student->id,
                        'coins' => $coinDelta,
                        'reason' => 'إكمال نشاط: ' . $activity->title . ' | الدرجة: ' . $score . '%',
                        'transaction_type' => 'earn',
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Coin creation failed: ' . $e->getMessage());
                }
            }

            // === نظام Streak الأنشطة (عام) ===
            try {
                $streakEnabled = $this->getStreakSetting('streak_enabled', $student) === '1';

                if ($streakEnabled) {
                    $minDays = (int) $this->getStreakSetting('streak_min_days', $student, 3);
                    $maxDays = (int) $this->getStreakSetting('streak_max_days', $student, 7);
                    $bonusPoints = (int) $this->getStreakSetting('streak_bonus_points', $student, 50);

                    $activityStreak = ActivityUserStreak::getOrCreate($student->id);
                    $newDayRecorded = $activityStreak->recordActivityDay();

                    if ($newDayRecorded) {
                        $bonusResult = $activityStreak->checkAndClaimBonus($minDays, $maxDays, $bonusPoints);

                        if ($bonusResult['success']) {
                            $streakBonus = $bonusResult['bonus'];

                            Point::create([
                                'user_id' => $student->id,
                                'points' => $streakBonus,
                                'reason' => 'مكافأة الالتزام اليومي: ' . $bonusResult['days'] . ' أيام',
                            ]);

                            $streakMessage = $bonusResult['message'];
                            $activityStreak->resetStreak();
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Streak processing failed: ' . $e->getMessage());
            }

            // === streak الدرس (مرتبط بنشاط داخل درس) ===
            // مكافأة نهائية تُمنح مرّة واحدة عند بلوغ عدد الأيام المطلوب للدرس.
            try {
                if ($activity->lesson_id) {
                    $lessonStreak = \App\Models\LessonUserStreak::firstOrCreate(
                        ['user_id' => $student->id, 'lesson_id' => $activity->lesson_id],
                        ['completed_days' => 0, 'activity_dates' => [], 'bonus_claimed' => false],
                    );
                    $newLessonDay = $lessonStreak->recordActivityDay();

                    if ($newLessonDay && ! $lessonStreak->bonus_claimed) {
                        // checkAndClaimBonus يحرس streak_disabled/min_not_reached ويقلب bonus_claimed مرّة واحدة فقط
                        $lessonBonus = $lessonStreak->checkAndClaimBonus();
                        if (! empty($lessonBonus['eligible'])) {
                            $lessonBonusPoints = (int) ($lessonBonus['bonus_points'] ?? 0);
                            if ($lessonBonusPoints > 0) {
                                Point::create([
                                    'user_id' => $student->id,
                                    'points' => $lessonBonusPoints,
                                    'reason' => 'مكافأة الالتزام النهائية للدرس: ' . ($activity->lesson?->title ?? ''),
                                ]);
                                $streakBonus += $lessonBonusPoints;
                                $lessonMsg = '🏆 مكافأة الالتزام النهائية: +' . $lessonBonusPoints . ' نقطة!';
                                $streakMessage = $streakMessage ? ($streakMessage . ' • ' . $lessonMsg) : $lessonMsg;
                            }
                        } else {
                            // يوم جديد سُجّل لكن المكافأة النهائية لم تُمنح بعد —
                            // رسالة تحفيز تُشعِر الطالب أنه «بدأ» ويرى تقدّمه (بلا تكرار مع رسالة المكافأة).
                            $lesson = $activity->lesson;
                            if ($lesson && $lesson->hasStreakEnabled()) {
                                $progressMsg = '🔥 يوم ' . $lessonStreak->completed_days . ' من ' . $lesson->streak_min_days . ' في التزام الدرس — استمرّ!';
                                $streakMessage = $streakMessage ? ($streakMessage . ' • ' . $progressMsg) : $progressMsg;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Lesson streak processing failed: ' . $e->getMessage());
            }

            // === إشعار الطالب بإكمال النشاط ===
            try {
                if ($score !== null) {
                    \App\Services\NotificationService::activityCompleted(
                        $student->id,
                        $activity->title,
                        $score,
                        $xp,
                        max(1, (int) floor($xp / 2)),
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('Activity completed notification failed', ['error' => $e->getMessage()]);
            }

            // === إشعار ولي الأمر ===
            try {
                if ($score !== null && method_exists($student, 'parents')) {
                    foreach ($student->parents as $parent) {
                        \App\Services\NotificationService::create(
                            $parent->id,
                            'child_activity',
                            '📚 ابنك أكمل نشاطًا',
                            "أكمل {$student->name} نشاط: {$activity->title} - النتيجة: {$score}%",
                            ['student_id' => $student->id, 'activity_id' => $activity->id, 'score' => $score],
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Parent notification failed', ['error' => $e->getMessage()]);
            }

            // === إطلاق ActivityCompleted event — للشارات و streak listener و خدمات أخرى ===
            try {
                if ($score !== null) {
                    event(new \App\Events\ActivityCompleted($student, $activity, $score, $xp, max(1, (int) floor($xp / 2))));
                }
            } catch (\Throwable $e) {
                Log::warning('ActivityCompleted event dispatch failed', ['error' => $e->getMessage()]);
            }
        } catch (\Throwable $e) {
            // Master catch — التسليم محفوظ، المكافآت فشلت
            Log::error('Post-submission processing failed [activity_id=' . $id . ']: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }

        // الإجابة الصحيحة تُعرض تعليمياً للطالب فقط عند إجابة خاطئة/جزئية مُصحَّحة آلياً
        // (لا تُكشف قبل الإرسال، ولا للأنشطة اليدوية التي ينتظر تصحيحها المعلم).
        $correctAnswer = ($score !== null && $score < 100)
            ? \App\Services\ActivityGradingService::correctAnswerText($activity)
            : null;

        return response()->json([
            'success' => true,
            'xp_earned' => $xp,
            'activity_points' => $activityPoints,
            'streak_bonus' => $streakBonus,
            'streak_message' => $streakMessage,
            'total_xp' => $xp + $streakBonus,
            'score' => $score ?? null,
            'passing_score' => $passing,
            'passed' => $passed,
            'correct_answer' => $correctAnswer,
        ]);
    }

    /**
     * حساب درجة النشاط تلقائياً بناءً على النوع والإجابة
     */
    private function calculateScore($activity, $answer): ?int
    {
        // للـ quiz فقط: نقارن إجابات الطالب بالإجابات الصحيحة
        if ($activity->type === 'quiz' && ! empty($activity->questions)) {
            $answers = json_decode($answer, true);
            if (! is_array($answers) || empty($answers)) {
                return 0;
            }

            $correct = 0;
            $total = 0;

            foreach ($activity->questions as $i => $q) {
                // Skip image_order questions within a quiz — they don't have correct_answer
                if (isset($q['type']) && $q['type'] === 'image_order') {
                    continue;
                }

                $correctAnswer = $q['correct_answer'] ?? null;
                if ($correctAnswer === null) {
                    continue;
                }

                $total++;

                if (isset($answers[$i]) && (int) $answers[$i] === (int) $correctAnswer) {
                    $correct++;
                }
            }

            return $total > 0 ? (int) round(($correct / $total) * 100) : null;
        }

        // للـ image_order: نبقيه pending للمراجعة اليدوية
        if (in_array($activity->type, ['image_order', 'creative', 'project'])) {
            return null; // null = بانتظار المراجعة
        }

        // للـ exercise بخيارات: نحسب أيضاً
        if ($activity->type === 'exercise' && ! empty($activity->questions)) {
            $answers = json_decode($answer, true);
            if (! is_array($answers)) {
                return null;
            }

            $correct = 0;
            $scored = 0;
            foreach ($activity->questions as $i => $q) {
                if (! isset($q['correct_answer'])) {
                    continue;
                }
                $scored++;
                if (isset($answers[$i]) && (int) $answers[$i] === (int) $q['correct_answer']) {
                    $correct++;
                }
            }

            return $scored > 0 ? (int) round(($correct / $scored) * 100) : null;
        }

        // upload, practical, discussion → بانتظار مراجعة المعلم (null)
        return null;
    }

    /**
     * جلب إعداد streak من المعلم أو الإعدادات العامة
     */
    private function getStreakSetting(string $key, $student, $default = null)
    {
        // أولاً: محاولة جلب من معلم الطالب
        $classroom = $student->classrooms()->with('teacher')->first();
        $teacherId = $classroom?->teacher?->id;

        if ($teacherId) {
            $setting = Setting::where('key', $key)->where('user_id', $teacherId)->first();
            if ($setting) {
                return $setting->value;
            }
        }

        // ثانياً: الإعدادات العامة
        $globalSetting = Setting::where('key', $key)->whereNull('user_id')->first();
        if ($globalSetting) {
            return $globalSetting->value;
        }

        return $default;
    }

    /**
     * توزيع النقاط على المعلم وولي الأمر والمدرسة (delegate إلى service).
     * حافظنا على الـ signature لتوافق كل المواقع التي تنادي $this->distributePoints().
     */
    private function distributePoints($student, int $points, string $source, string $description)
    {
        app(\App\Services\Activity\PointsDistributionService::class)
            ->distribute($student, $points, $source, $description);
    }

    /**
     * استبدال مكافأة من المتجر
     */
    public function redeemReward(Request $request)
    {
        // Pass-4 Batch 2: 'cost' is NO LONGER accepted — price is derived server-side
        // from ShopItem (closes the forged-client-cost Major). The debit goes through
        // SpendService (users-row mutex + idempotent + overdraft-proof).
        $validated = $request->validate([
            'reward_id' => 'required|integer|exists:shop_items,id',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        $student = Auth::user();
        $item = \App\Models\ShopItem::findOrFail((int) $validated['reward_id']);

        if (! $item->isAvailable()) {
            return response()->json(['success' => false, 'message' => 'هذه المكافأة غير متاحة حالياً']);
        }

        // Rewards are repeatable, so the idempotency key is the per-intent client token
        // (resent unchanged on retry => no double charge; a new intentional redeem uses a
        // new token => not swallowed). Absent a token, each redeem is distinct (documented).
        $token = $validated['idempotency_key'] ?? (string) \Illuminate\Support\Str::uuid();

        $result = \App\Services\SpendService::spend(
            (int) $student->id,
            'reward_redemption',
            $token,
            (int) $item->price,                 // ← server-authoritative price
            'استبدال مكافأة: ' . $item->name,
        );

        if (! $result['success'] && $result['reason'] === 'insufficient_balance') {
            return response()->json(['success' => false, 'message' => 'عملاتك غير كافية']);
        }
        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => 'تعذّر إتمام الاستبدال'], 422);
        }

        return response()->json([
            'success' => true,
            'new_balance' => $result['balance'],
            'duplicate' => $result['duplicate'],
        ]);
    }

    /**
     * تحديث البروفايل
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        try {
            // Update name and email
            $user->name = $request->name;
            $user->email = $request->email;

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar && \Storage::exists('public/' . $user->avatar)) {
                    \Storage::delete('public/' . $user->avatar);
                }

                // Store new avatar
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            // Handle password change
            if ($request->filled('current_password')) {
                if (! \Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'كلمة المرور الحالية غير صحيحة',
                    ]);
                }

                $user->password = \Hash::make($request->new_password);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث البيانات بنجاح',
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Student profile update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث',
            ]);
        }
    }

    /**
     * سجل العملات والنقاط
     */
    public function coinsHistory()
    {
        $user = Auth::user();

        $history = Coin::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($coin) {
                return [
                    'amount' => $coin->coins,
                    'source' => $coin->transaction_type,
                    'description' => $coin->reason ?? 'نقاط',
                    'date' => $coin->created_at->locale('ar')->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * شراء منتج
     */
    public function purchaseItem(Request $request)
    {
        $user = Auth::user();
        $itemId = (int) $request->item_id;

        $item = \App\Models\ShopItem::findOrFail($itemId);

        // فحوص أولية خفيفة قبل المعاملة
        if (! $item->isAvailable()) {
            return response()->json(['success' => false, 'message' => 'هذا العنصر غير متاح حالياً']);
        }
        if ($user->hasPurchased($itemId)) {
            return response()->json(['success' => false, 'message' => 'لقد اشتريت هذا العنصر مسبقاً']);
        }

        // تنفيذ ذرّي: فحص الرصيد + خصم + شراء + مخزون كل ذلك تحت قفل
        // (سابقًا كان فحص الرصيد قبل المعاملة → race يسمح بالرصيد السالب)
        try {
            $result = DB::transaction(function () use ($user, $item, $itemId) {
                // الشراء مرة واحدة لكل عنصر — الحارس الحقيقي مفتاح SpendService (shop_purchase, itemId)
                if ($user->purchases()->where('shop_item_id', $itemId)->exists()) {
                    return ['success' => false, 'message' => 'لقد اشتريت هذا العنصر مسبقاً'];
                }

                // الخصم عبر SpendService: يقفل صف users، idempotent على (shop_purchase, itemId)،
                // ولا رصيد سالب. السعر مشتق من الخادم ($item->price) — لا قيمة من العميل.
                $spend = \App\Services\SpendService::spend(
                    (int) $user->id,
                    'shop_purchase',
                    (string) $itemId,
                    (int) $item->price,
                    'شراء ' . $item->name,
                );

                if (! empty($spend['duplicate'])) {
                    return ['success' => false, 'message' => 'لقد اشتريت هذا العنصر مسبقاً'];
                }
                if (! $spend['success']) {
                    return ['success' => false, 'message' => 'رصيدك غير كافٍ. تحتاج ' . $item->price . ' عملة'];
                }

                // المخزون + سجل الملكية ذرّياً مع الخصم (نفس المعاملة الخارجية)؛ فشل المخزون
                // يرمي فيتراجع الخصم كاملاً والمفتاح يتحرّر لإعادة محاولة شريفة.
                if (! $item->decrementStock()) {
                    throw new \DomainException('out_of_stock');
                }

                $user->purchases()->attach($itemId, [
                    'price_paid' => $item->price,
                    'is_active' => true,
                ]);

                return [
                    'success' => true,
                    'message' => 'تم الشراء بنجاح! 🎉',
                    'remaining_coins' => $spend['balance'],
                ];
            }, 3);

            return response()->json($result);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => 'نفد المخزون. حاول شراء عنصر آخر.']);
        } catch (\Throwable $e) {
            \Log::error('Shop purchase failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'item_id' => $itemId,
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الشراء. يرجى المحاولة مرة أخرى.',
            ], 500);
        }
    }

    /**
     * صفحة «مقتنياتي» — كل مشتريات الطالب مع حالة التجهيز/الاستخدام، مجمّعة حسب النوع.
     */
    public function myItems()
    {
        $user = Auth::user();

        $purchases = $user->purchases()
            ->orderBy('user_purchases.created_at', 'desc')
            ->get()
            ->groupBy('type');

        return view('student.my-items', compact('purchases'));
    }

    /**
     * تجهيز عنصر قابل للتجهيز (avatar/theme/badge) — عنصر واحد فعّال لكل نوع.
     * إعادة الطلب على العنصر المُجهَّز نفسه = إلغاء التجهيز.
     */
    public function equipItem(Request $request)
    {
        $user = Auth::user();
        $itemId = (int) $request->input('item_id');

        $item = \App\Models\ShopItem::find($itemId);
        if (! $item || ! $user->hasPurchased($itemId)) {
            return response()->json(['success' => false, 'message' => 'لا تملك هذا العنصر']);
        }
        if (! in_array($item->type, ['avatar', 'theme', 'badge'], true)) {
            return response()->json(['success' => false, 'message' => 'هذا النوع لا يُجهَّز']);
        }

        $current = $user->purchases()->where('shop_item_id', $itemId)->first();
        $isEquipped = $current && $current->pivot->is_active;
        $equipNow = ! $isEquipped;

        // عنصر واحد فعّال لكل نوع: ألغِ تجهيز كل عناصر النوع نفسه أولاً
        $sameTypeIds = \App\Models\ShopItem::where('type', $item->type)->pluck('id')->all();
        DB::table('user_purchases')->where('user_id', $user->id)
            ->whereIn('shop_item_id', $sameTypeIds)->update(['is_active' => false]);

        if ($equipNow) {
            DB::table('user_purchases')->where('user_id', $user->id)
                ->where('shop_item_id', $itemId)->update(['is_active' => true]);
        }

        // أثر التجهيز: الأفاتار يصبح صورة الملف الشخصي فعلياً
        if ($item->type === 'avatar') {
            $user->avatar = $equipNow
                ? ($item->image ?: ('emoji:' . ($item->icon ?: '🙂')))
                : null; // إلغاء التجهيز → العودة للأفاتار الافتراضي
            $user->save();
        }

        return response()->json([
            'success' => true,
            'equipped' => $equipNow,
            'type' => $item->type,
            'message' => $equipNow ? 'تم التجهيز ✓' : 'أُلغي التجهيز',
        ]);
    }

    /**
     * استخدام عنصر استهلاكي (power_up/special) — يُعلَّم بـ used_at (استخدام واحد).
     */
    public function useItem(Request $request)
    {
        $user = Auth::user();
        $itemId = (int) $request->input('item_id');

        $item = \App\Models\ShopItem::find($itemId);
        if (! $item || ! $user->hasPurchased($itemId)) {
            return response()->json(['success' => false, 'message' => 'لا تملك هذا العنصر']);
        }

        // بوّابة نوع: العناصر الاستهلاكيّة فقط تُستخدَم. بدونها كان عنصرٌ تجميليّ (أڤاتار/ثيم/شارة)
        // يحمل metadata.effect يمنح نقاطاً/عملات عند «الاستخدام» = سكّ اقتصاد. (يطابق حارس equipItem.)
        if (! in_array($item->type, ['power_up', 'special'], true)) {
            return response()->json(['success' => false, 'message' => 'هذا العنصر لا يُستخدَم']);
        }

        $current = $user->purchases()->where('shop_item_id', $itemId)->first();
        if ($current && $current->pivot->used_at) {
            return response()->json(['success' => false, 'message' => 'استخدمت هذا العنصر مسبقاً']);
        }

        // حساب أثر القوة من metadata
        $meta = is_array($item->metadata) ? $item->metadata : [];
        $points = 0;
        $coins = 0;
        $effect = $meta['effect'] ?? null;
        if ($effect === 'points') {
            $points = (int) ($meta['amount'] ?? 0);
        } elseif ($effect === 'coins') {
            $coins = (int) ($meta['amount'] ?? 0);
        } elseif ($effect === 'mystery') {
            $min = (int) ($meta['min'] ?? 10);
            $max = (int) ($meta['max'] ?? 100);
            $points = random_int(min($min, $max), max($min, $max));
        } elseif (isset($meta['multiplier'])) { // توافق مع القوى القديمة (duration-based)
            $points = (int) $meta['multiplier'] * 25;
        } elseif (isset($meta['bonus_percentage'])) {
            $coins = (int) $meta['bonus_percentage'];
        } else {
            $points = 20; // مكافأة افتراضية بسيطة لأي عنصر قابل للاستخدام
        }

        DB::table('user_purchases')->where('user_id', $user->id)
            ->where('shop_item_id', $itemId)->update(['used_at' => now()]);

        $rewardMsg = '';
        if ($points > 0 || $coins > 0) {
            try {
                \App\Services\AwardService::award(
                    (int) $user->id,
                    'power_up_use',
                    (string) $itemId,
                    $points,
                    $coins,
                    'استخدام قوة: ' . $item->name,
                );
            } catch (\Throwable $e) {
                \Log::warning('Power-up award failed: ' . $e->getMessage());
            }
            $parts = [];
            if ($points > 0) {
                $parts[] = "+{$points} نقطة";
            }
            if ($coins > 0) {
                $parts[] = "+{$coins} عملة";
            }
            $rewardMsg = ' — حصلت على ' . implode(' و ', $parts);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم استخدام «' . $item->name . '»' . $rewardMsg . ' 🎉',
            'points' => $points,
            'coins' => $coins,
        ]);
    }

    /**
     * عرض صفحة تقييم المعلمين
     */
    public function rateTeachers()
    {
        $user = Auth::user();

        // جلب المعلمين الذين يدرسون الطالب
        $teachers = User::where('role', 'teacher')
            ->where('school_id', $user->school_id)
            ->whereHas('teachingClassrooms.students', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->with(['ratings' => function ($q) use ($user) {
                $q->where('student_id', $user->id);
            }])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->get();

        // الحصول على stats للـ layout
        $user->load('streak');
        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        return view('student.rate-teachers', compact('teachers', 'stats', 'streak'));
    }

    /**
     * تقييم معلم
     */
    public function submitRating(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        // التحقق من أن المعلم يدرس الطالب
        $teacherValid = User::where('id', $request->teacher_id)
            ->where('role', 'teacher')
            ->where('school_id', $user->school_id)
            ->whereHas('teachingClassrooms.students', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->exists();

        if (! $teacherValid) {
            return response()->json(['error' => 'المعلم غير صالح'], 403);
        }

        // إنشاء أو تحديث التقييم
        $rating = \App\Models\TeacherRating::updateOrCreate(
            [
                'teacher_id' => $request->teacher_id,
                'student_id' => $user->id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ],
        );

        // إشعار للمعلم
        \App\Services\NotificationService::create(
            $request->teacher_id,
            'new_rating',
            '⭐ تقييم جديد',
            "تلقيت تقييم {$request->rating} نجوم من طالب",
            ['rating' => $request->rating, 'student' => $user->name],
            route('teacher.ratings'),
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال التقييم بنجاح',
        ]);
    }

    /**
     * عرض صفحة التحليلات والرسوم البيانية
     */
    public function analytics()
    {
        $user = Auth::user();

        // بيانات التقدم خلال آخر 30 يوم
        $progressData = $this->getProgressChartData($user->id, 30);

        // بيانات الأنشطة حسب الحالة
        $activityStatusData = ActivitySubmission::where('student_id', $user->id)
            ->selectRaw("
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
            ")
            ->first();

        // بيانات النقاط حسب القيمة
        $pointsByValue = DB::table('points')
            ->join('activities', 'points.activity_id', '=', 'activities.id')
            ->join('lessons', 'activities.lesson_id', '=', 'lessons.id')
            ->join('concepts', 'lessons.concept_id', '=', 'concepts.id')
            ->join('values', 'concepts.value_id', '=', 'values.id')
            ->where('points.user_id', $user->id)
            ->select('values.name', DB::raw('SUM(points.points) as total'))
            ->groupBy('values.id', 'values.name')
            ->get();

        // بيانات الأنشطة اليومية - آخر 7 أيام
        $weeklyActivityData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = ActivitySubmission::where('student_id', $user->id)
                ->whereDate('created_at', $date)
                ->count();
            $weeklyActivityData[] = [
                'date' => $date,
                'label' => now()->subDays($i)->locale('ar')->dayName,
                'count' => $count,
            ];
        }

        // معدل الدرجات - آخر 10 أنشطة
        $recentScores = ActivitySubmission::where('student_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->pluck('score')
            ->reverse()
            ->values();

        // الحصول على stats للـ layout
        $user->load('streak');
        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        return view('student.analytics', compact(
            'progressData',
            'activityStatusData',
            'pointsByValue',
            'weeklyActivityData',
            'recentScores',
            'stats',
            'streak',
        ));
    }

    /**
     * Helper: بيانات الرسم البياني للتقدم — مُحسَّن (2 queries بدل 60).
     */
    private function getProgressChartData($studentId, $days = 30)
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        // استعلام واحد لكل النقاط مجمّعة حسب اليوم
        $pointsByDate = Point::where('user_id', $studentId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as d, SUM(points) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        // استعلام واحد لكل الأنشطة المنجزة مجمّعة حسب اليوم
        $activitiesByDate = ActivitySubmission::where('student_id', $studentId)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ActivitySubmission::DONE_STATUSES)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $labels = [];
        $pointsData = [];
        $activitiesData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $pointsData[] = (int) ($pointsByDate[$key] ?? 0);
            $activitiesData[] = (int) ($activitiesByDate[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'points' => $pointsData,
            'activities' => $activitiesData,
        ];
    }

    /**
     * صفحة الشارات
     */
    public function badges()
    {
        $user = Auth::user();
        $user->load('streak', 'badges');

        // التاج يُمنح عند إتقان القيمة — نضمن المزامنة قبل العرض ليتطابق العدّ مع صفحة التيجان (Issue 52)
        $this->syncCrowns($user);

        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        // معرّفات الشارات المكتسبة + تاريخ الكسب (لعرض المكتسبة ملوّنة + earned_at)
        $earnedPivot = $user->badges()->get();
        $earnedIds = $earnedPivot->pluck('id')->all();
        $earnedAtById = $earnedPivot->mapWithKeys(
            fn ($b) => [$b->id => optional($b->pivot)->earned_at]
        );

        // التيجان — مصدر موحّد: جدول crowns (نفس مصدر صفحة التيجان والإحصائيات)
        $crowns = $user->crowns()->count();

        // شارات هذا الشهر
        $recentBadges = $user->badges()
            ->wherePivot('earned_at', '>=', now()->subMonth())
            ->count();

        // القيم المتقنة المعروضة كتيجان — تُقرأ من نفس جدول crowns لمنع التناقض مع صفحة التيجان
        $masteredValues = $user->crowns()->with('value')->get()
            ->map(fn ($crown) => $crown->value)
            ->filter();

        // كل الشارات النشطة القابلة للكسب (لها شرط) — نستبعد الشارات القديمة عديمة الشرط
        // كي لا تظهر للطالب كبطاقات مقفلة دائمة بلا طريقة كسب.
        $allBadges = Badge::where('status', 'active')
            ->whereNotNull('condition_type')
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        // العدّاد يعتمد المجموعة المعروضة نفسها (المكتسبة ∩ المعروضة) فلا يتجاوز البسطُ المقامَ أبداً
        $totalBadges = $allBadges->whereIn('id', $earnedIds)->count();

        // مقاييس الطالب الستّة الحاليّة — من المصدر الموحّد نفسه لمحرّك المنح (تطابق شريط التقدّم مع عتبة المنح)
        $studentMetrics = BadgeMetrics::compute($user, array_keys(Badge::CONDITION_TYPES));

        // عدد الشارات المكتسبة النادرة/الخاصة (type=special) — لبطاقة الإحصائيات
        $rareBadges = $allBadges->whereIn('id', $earnedIds)
            ->where('type', 'special')->count();

        return view('student.badges', compact(
            'allBadges',
            'earnedIds',
            'earnedAtById',
            'studentMetrics',
            'totalBadges',
            'rareBadges',
            'crowns',
            'recentBadges',
            'masteredValues',
            'stats',
            'streak',
        ));
    }

    /**
     * صفحة التعلم
     */
    public function learn()
    {
        $user = Auth::user();
        $user->load('streak');

        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        // قيم المدرسة المرئيّة — «الدرس الحاليّ» يجب أن يقع ضمنها وإلّا فتحُه 404 (بوّابة lesson())
        $visibleValueIds = $user->school_id
            ? \App\Models\Value::visibleForSchool($user->school_id)->pluck('id')->all()
            : \App\Models\Value::pluck('id')->all();

        // جلب الدرس الحالي
        $currentLesson = Lesson::where('status', 'active')
            ->whereHas('concept', fn ($q) => $q->whereIn('value_id', $visibleValueIds))
            ->whereHas('activities', function ($query) use ($user) {
                $query->whereHas('submissions', function ($q) use ($user) {
                    $q->where('student_id', $user->id)
                        ->where('status', '!=', 'completed');
                });
            })
            ->with(['concept.value'])
            ->first();

        // إذا لم يكن هناك درس جاري، نجيب أول درس متاح ضمن قيم المدرسة المرئيّة
        if (! $currentLesson) {
            $currentLesson = Lesson::where('status', 'active')
                ->whereHas('concept', fn ($q) => $q->whereIn('value_id', $visibleValueIds))
                ->with(['concept.value'])
                ->first();
        }

        // حساب تقدم الدرس الحالي (نعتبر أي تسليم إنجازاً للنشاط)
        if ($currentLesson) {
            $activityIds = $currentLesson->activities()->where('status', 'active')->visibleToStudent($user->school_id, $user->classrooms->pluck('id')->all())->pluck('id')->toArray();
            $totalActivities = count($activityIds);
            $completedActivities = ActivitySubmission::where('student_id', $user->id)
                ->whereIn('activity_id', $activityIds)
                ->whereIn('status', ['completed', 'approved', 'pending', 'needs_review'])
                ->count();
            $currentLesson->progress = $totalActivities > 0 ? round(($completedActivities / $totalActivities) * 100) : 0;
        }

        // streak الالتزام للدرس الحالي — يُمرَّر فقط عند تفعيل streak الدرس، وإلا null
        $currentLessonStreak = ($currentLesson && $currentLesson->hasStreakEnabled())
            ? $user->getLessonStreak($currentLesson->id)
            : null;

        return view('student.learn', compact('currentLesson', 'stats', 'streak', 'currentLessonStreak'));
    }

    /**
     * صفحة شجرة القيم
     */
    public function valuesTree()
    {
        $user = Auth::user();
        $user->load('streak');

        // التاج يُمنح عند إتقان القيمة — مزامنة قبل العرض
        $this->syncCrowns($user);

        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        $totalPoints = $stats['total_points'];
        $badges = $user->badges()->count();
        $crowns = $user->crowns()->count();

        // الأنشطة المكتملة فعلياً (completed/approved) لحساب اكتمال الدروس والقيم
        $completedActivityIds = ActivitySubmission::where('student_id', $user->id)
            ->whereIn('status', ActivitySubmission::DONE_STATUSES)
            ->pluck('activity_id')->unique()->all();

        // جلب القيم المرئية لمدرسة الطالب فقط (Issue #105) — دروس وأنشطة نشطة فقط
        $values = Value::visibleForSchool($user->school_id)
            ->with([
                'concepts.lessons' => fn ($q) => $q->where('status', 'active'),
                'concepts.lessons.activities' => fn ($q) => $q->where('status', 'active')->visibleToStudent($user->school_id, $user->classrooms->pluck('id')->all()),
            ])
            ->orderBy('order')
            ->get();

        // ضبط خصائص العرض التي يعتمدها القالب (Issue 60): progress/is_mastered/is_unlocked/is_completed
        $completedLessons = 0;
        foreach ($values as $value) {
            $valTotal = 0;
            $valDone = 0;
            foreach ($value->concepts as $concept) {
                $concept->is_unlocked = true; // كل المفاهيم المرئية متاحة — لا قفل تتابعي
                foreach ($concept->lessons as $lesson) {
                    if (($lesson->status ?? 'active') !== 'active') {
                        $lesson->is_completed = false;

                        continue;
                    }
                    $actIds = $lesson->activities->where('status', 'active')->pluck('id')->all();
                    $lesson->is_completed = ! empty($actIds) && count(array_diff($actIds, $completedActivityIds)) === 0;
                    if (! empty($actIds)) {
                        $valTotal++;
                        if ($lesson->is_completed) {
                            $valDone++;
                            $completedLessons++;
                        }
                    }
                }
            }
            $value->progress = $valTotal > 0 ? (int) round(($valDone / $valTotal) * 100) : 0;
            $value->is_mastered = $valTotal > 0 && $valDone >= $valTotal;
        }

        return view('student.values-tree', compact(
            'totalPoints',
            'completedLessons',
            'badges',
            'crowns',
            'values',
            'stats',
            'streak',
        ));
    }

    /**
     * صفحة التيجان
     */
    public function crowns()
    {
        $user = Auth::user();
        $user->load('streak');

        // التاج يُمنح عند إتقان القيمة — نضمن إنشاء التيجان المستحقة قبل العرض (Issue 52)
        $this->syncCrowns($user);

        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        // جميع التيجان المكتسبة
        $crowns = $user->crowns()->with('value')->orderBy('earned_at', 'desc')->get();
        $totalCrowns = $crowns->count();

        // القيم المتاحة للتتويج — فقط المرئية للمدرسة (#105)
        $availableCrowns = Value::visibleForSchool($user->school_id)
            ->whereDoesntHave('crowns', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get();

        return view('student.crowns', compact(
            'crowns',
            'totalCrowns',
            'availableCrowns',
            'stats',
            'streak',
        ));
    }

    /**
     * صفحة الهدايا والمدح من ولي الأمر
     */
    public function gifts()
    {
        $user = Auth::user();
        $user->load('streak');

        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        // المدح المستلم من ولي الأمر
        $praises = $user->praisesReceived()
            ->with('parent')
            ->orderBy('created_at', 'desc')
            ->get();

        // الهدايا المستلمة من ولي الأمر
        $gifts = $user->giftsReceived()
            ->with('parent')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPraises = $praises->count();
        $totalGifts = $gifts->count();

        return view('student.gifts', compact(
            'praises',
            'gifts',
            'totalPraises',
            'totalGifts',
            'stats',
            'streak',
        ));
    }

    /**
     * صفحة الفرق
     */
    public function teams()
    {
        $user = Auth::user();
        $user->load('streak', 'teams');

        $stats = $this->getStudentStats($user);
        $streak = $user->streak;

        // الفرق التي ينتمي إليها الطالب
        $teams = $user->teams()
            ->with(['members', 'creator'])
            ->orderByPivot('joined_at', 'desc')
            ->get();

        $totalTeams = $teams->count();

        // حساب إنجازات كل فريق
        foreach ($teams as $team) {
            $memberIds = $team->members->pluck('id')->toArray();
            $team->total_points = Point::whereIn('user_id', $memberIds)->sum('points');
            $team->total_activities = ActivitySubmission::whereIn('student_id', $memberIds)
                ->where('status', 'completed')
                ->count();
        }

        return view('student.teams', compact(
            'teams',
            'totalTeams',
            'stats',
            'streak',
        ));
    }

    // ==================== نظام التمارين ====================

    /**
     * صفحة التمارين الرئيسية
     */
    public function practice()
    {
        $student = Auth::user();

        // صفحة التحديات فقط (أُزيلت التمارين بطلب المالك) — تحديات PvP النشطة.
        // نضمن وجود تحدٍّ عام حقيقي (لا كائن وهمي id=0) ليعمل الدخول للصالة وjoinPvpMatch.
        $this->ensureDefaultPvpChallenge();
        $pvpChallenges = \Illuminate\Support\Facades\Schema::hasTable('pvp_challenges')
            ? \App\Models\PvpChallenge::where('is_active', true)->withCount('matches')->get()
            : collect();

        $hasPvpMatches = \Illuminate\Support\Facades\Schema::hasTable('pvp_matches');

        // إحصائيات التحديات
        $practiceStats = [
            'pvp_wins' => $hasPvpMatches ? \App\Models\PvpMatch::where('winner_id', $student->id)->count() : 0,
            'matches_played' => $hasPvpMatches
                ? \App\Models\PvpMatch::where(function ($q) use ($student) {
                    $q->where('player1_id', $student->id)->orWhere('player2_id', $student->id);
                })->where('status', 'completed')->count()
                : 0,
            'available' => $pvpChallenges->count(),
        ];

        return view('student.practice-view', compact('pvpChallenges', 'practiceStats'));
    }

    /**
     * Pass-4 Batch 3 (BOLA): هل هذا التمرين متاح لهذا الطالب؟
     * متاح إن كان عاماً (classroom_id = null) أو ضمن فصول الطالب — نفس منطق practice().
     */
    private function exerciseBelongsToStudent(\App\Models\PracticeExercise $exercise, User $student): bool
    {
        if ($exercise->classroom_id === null) {
            // Public exercise has NO classroom anchor — bind it to the CREATOR'S school so a
            // school-B teacher's public exercise is not reachable (or point-farmable) by a
            // school-A student. A teacherless public exercise is reachable by nobody (safe default).
            $exercise->loadMissing('teacher:id,school_id');

            return $exercise->teacher
                && (int) $exercise->teacher->school_id === (int) $student->school_id;
        }

        return $student->classrooms()
            ->where('classrooms.id', $exercise->classroom_id)
            ->exists();
    }

    /**
     * بدء تمرين
     */
    public function startExercise($id)
    {
        $student = Auth::user();
        $exercise = \App\Models\PracticeExercise::findOrFail($id);

        // Pass-4 Batch 3 (BOLA): التمرين يخص الطالب فقط إن كان classroom_id ضمن
        // فصوله، أو عاماً (classroom_id = null). يطابق نفس فلترة practice().
        if (! $this->exerciseBelongsToStudent($exercise, $student)) {
            abort(403);
        }

        // بوّابة الإتاحة (is_active + نافذة starts_at/ends_at) — كان يمكن اللعب/حصد النقاط
        // خارج نافذة المعلّم أو على تمرين مُعطَّل.
        if (! $exercise->is_available) {
            return back()->with('error', 'هذا التمرين غير متاح حالياً');
        }

        // التحقق من عدد المحاولات
        $attemptsCount = \App\Models\PracticeAttempt::where('student_id', $student->id)
            ->where('exercise_id', $id)
            ->whereNotNull('completed_at')
            ->count();

        if ($attemptsCount >= $exercise->max_attempts) {
            return back()->with('error', 'لقد استنفدت جميع المحاولات المتاحة');
        }

        // جلب الأسئلة
        $questionIds = $exercise->questions ?? [];
        $questions = \App\Models\QuestionBank::whereIn('id', $questionIds)->get()->shuffle();

        return view('student.practice-start', compact('exercise', 'questions', 'attemptsCount'));
    }

    /**
     * إرسال إجابات التمرين
     */
    public function submitExercise(Request $request, $id)
    {
        $student = Auth::user();
        $exercise = \App\Models\PracticeExercise::findOrFail($id);

        // Pass-4 Batch 3 (BOLA): submit يجب ألا يكون متاحاً لتمرين أجنبي حتى لو
        // حُجب start — نفس فحص الملكية على المسارين.
        if (! $this->exerciseBelongsToStudent($exercise, $student)) {
            abort(403);
        }

        // بوّابة الإتاحة على الإرسال أيضاً (لا حصد نقاط خارج نافذة المعلّم عبر POST مباشر)
        if (! $exercise->is_available) {
            return back()->with('error', 'هذا التمرين غير متاح حالياً');
        }

        // Pass-4 Batch 2: enforce max_attempts on SUBMIT too. startExercise gated this,
        // submitExercise did not — the unlimited-points-farming bug (replay the POST →
        // re-award forever). Sequential replay is now blocked; the concurrent
        // double-submit race needs a unique (student, exercise, attempt) constraint
        // (held schema batch).
        $completedAttempts = \App\Models\PracticeAttempt::where('student_id', $student->id)
            ->where('exercise_id', $id)
            ->whereNotNull('completed_at')
            ->count();
        if ($completedAttempts >= $exercise->max_attempts) {
            return back()->with('error', 'لقد استنفدت جميع المحاولات المتاحة');
        }

        $answers = $request->input('answers', []);
        $timeTaken = (int) $request->input('time_taken', 0);

        // تصحيح الإجابات
        $questionIds = $exercise->questions ?? [];
        $questions = \App\Models\QuestionBank::whereIn('id', $questionIds)->get()->keyBy('id');

        $correctCount = 0;
        $totalQuestions = count($questionIds);
        $gradedAnswers = [];

        // helper مرن لـ true_false: يقبل "true/false" و "صح/خطأ" و "1/0" و "نعم/لا"
        $boolish = function ($v) {
            $s = mb_strtolower(trim((string) $v));
            if (in_array($s, ['1', 'true', 'yes', 'صح', 'صحيح', 'نعم'], true)) {
                return true;
            }
            if (in_array($s, ['0', 'false', 'no', 'خطأ', 'خاطئ', 'لا'], true)) {
                return false;
            }

            return null;
        };

        foreach ($answers as $qId => $answer) {
            $question = $questions->get($qId);
            if (! $question) {
                continue;
            }

            $isCorrect = false;
            if ($question->question_type === 'multiple_choice') {
                $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                foreach ($options as $i => $opt) {
                    if (isset($opt['is_correct']) && $opt['is_correct'] && $answer == $i) {
                        $isCorrect = true;
                        break;
                    }
                }
            } elseif ($question->question_type === 'true_false') {
                // تطبيع كلا الجانبين لقبول "صح/خطأ" و "true/false" بأي صياغة
                $studentBool = $boolish($answer);
                $correctBool = $boolish($question->correct_answer ?? '');
                $isCorrect = $studentBool !== null && $studentBool === $correctBool;
            } else {
                $isCorrect = mb_strtolower(trim($answer)) === mb_strtolower(trim($question->correct_answer ?? ''));
            }

            if ($isCorrect) {
                $correctCount++;
            }
            $gradedAnswers[$qId] = ['answer' => $answer, 'correct' => $isCorrect];
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

        // حفظ المحاولة
        $attempt = \App\Models\PracticeAttempt::create([
            'student_id' => $student->id,
            'exercise_id' => $id,
            'answers' => $gradedAnswers,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'time_taken' => $timeTaken,
            'completed_at' => now(),
        ]);

        // إضافة نقاط — عبر AwardService (ذرّي + idempotent مفتاحه المحاولة).
        $points = max(1, (int) round($score / 10));
        try {
            \App\Services\AwardService::award(
                $student->id,
                'practice_attempt',
                (string) $attempt->id,
                $points,
                0,
                'إكمال تمرين: ' . $exercise->title,
            );
        } catch (\Throwable $e) {
            Log::warning('Practice exercise award failed', [
                'student_id' => $student->id,
                'exercise_id' => $exercise->id ?? null,
                'attempt_id' => $attempt->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('student.practice.result', $attempt->id);
    }

    /**
     * نتيجة التمرين
     */
    public function exerciseResult($attemptId)
    {
        $student = Auth::user();
        $attempt = \App\Models\PracticeAttempt::where('student_id', $student->id)->findOrFail($attemptId);
        $exercise = $attempt->exercise;

        $questionIds = $exercise->questions ?? [];
        $questions = \App\Models\QuestionBank::whereIn('id', $questionIds)->get()->keyBy('id');

        return view('student.practice-result', compact('attempt', 'exercise', 'questions'));
    }

    // ==================== نظام PvP ====================

    /**
     * صالة انتظار PvP
     */
    public function pvpLobby()
    {
        $student = Auth::user();

        // حماية ضد بيئة لم تُنشأ فيها جداول PvP بعد — تفادي خطأ 500
        if (! \Illuminate\Support\Facades\Schema::hasTable('pvp_challenges')
            || ! \Illuminate\Support\Facades\Schema::hasTable('pvp_matches')) {
            return view('student.pvp-lobby', [
                'challenges' => collect(),
                'myMatches' => collect(),
                'pendingInvites' => collect(),
                'readyMatches' => collect(),
                // اسم مستقل عن 'stats' الذي يشاركه View composer لـ student.* (تضارب أسماء)
                'pvpStats' => ['total_matches' => 0, 'wins' => 0],
            ]);
        }

        // تفعيل أساسي: نضمن وجود تحدٍّ عام فعلي ليظهر في الصالة بزر انضمام حقيقي (Issue 70)
        $this->ensureDefaultPvpChallenge();

        // التحديات المتاحة للطالب — فقط:
        //  • تحديات عامة (value_id = null)
        //  • أو تحديات مرتبطة بقيمة مفعّلة لمدرسة الطالب
        $challenges = \App\Models\PvpChallenge::availableForSchool($student->school_id)
            ->with('value:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        // مبارياتي الأخيرة
        $myMatches = \App\Models\PvpMatch::where(function ($q) use ($student) {
            $q->where('player1_id', $student->id)->orWhere('player2_id', $student->id);
        })
            ->where('status', 'completed')
            ->with(['player1', 'player2', 'winner', 'challenge'])
            ->latest()
            ->limit(10)
            ->get();

        // دعوات واردة معلّقة: أنا المدعوّ (player2) بانتظار قبولي/رفضي.
        $pendingInvites = \App\Models\PvpMatch::where('player2_id', $student->id)
            ->where('status', 'invited')
            ->with(['player1:id,name', 'challenge:id,title'])
            ->latest()
            ->get();

        // مباريات جاهزة للعب (playing) لم أُرسل إجابتي فيها بعد — استئناف غير متزامن
        // (يشمل التحديات العشوائية والموجّهة بعد القبول).
        $readyMatches = \App\Models\PvpMatch::where('status', 'playing')
            ->where(function ($q) use ($student) {
                $q->where(function ($q2) use ($student) {
                    $q2->where('player1_id', $student->id)->whereNull('player1_answers');
                })->orWhere(function ($q2) use ($student) {
                    $q2->where('player2_id', $student->id)->whereNull('player2_answers');
                });
            })
            ->with(['player1:id,name', 'player2:id,name', 'challenge:id,title'])
            ->latest()
            ->get();

        // اسم مستقل عن 'stats' الذي يشاركه View composer لـ student.* (تفادي تضارب الأسماء)
        $pvpStats = [
            'total_matches' => \App\Models\PvpMatch::where(function ($q) use ($student) {
                $q->where('player1_id', $student->id)->orWhere('player2_id', $student->id);
            })->where('status', 'completed')->count(),
            'wins' => \App\Models\PvpMatch::where('winner_id', $student->id)->count(),
        ];

        return view('student.pvp-lobby', compact('challenges', 'myMatches', 'pvpStats', 'pendingInvites', 'readyMatches'));
    }

    /**
     * الانضمام لمباراة PvP
     */
    public function joinPvpMatch($challengeId)
    {
        $student = Auth::user();

        $challenge = \App\Models\PvpChallenge::where('is_active', true)->findOrFail($challengeId);

        // Pass-4 Batch 3 (BOLA): قصر الانضمام على التحديات المتاحة لمدرسة الطالب
        // فقط (عامة أو مرتبطة بقيمة مفعّلة لمدرسته) — نفس scope صالة الانتظار.
        // يمنع الانضمام لتحدٍّ مقيّد بقيمة مدرسة أخرى عبر تمرير id مباشرة.
        if (! \App\Models\PvpChallenge::availableForSchool($student->school_id)
            ->whereKey($challenge->id)->exists()) {
            abort(403);
        }

        // البحث عن مباراة بحالة "waiting" (طالب ينتظر)
        $match = \App\Models\PvpMatch::where('challenge_id', $challengeId)
            ->where('status', 'waiting')
            ->where('player1_id', '!=', $student->id)
            ->first();

        if ($match) {
            // الانضمام كلاعب 2
            $match->update([
                'player2_id' => $student->id,
                'status' => 'playing',
                'started_at' => now(),
            ]);
        } else {
            // إنشاء مباراة جديدة
            $match = \App\Models\PvpMatch::create([
                'challenge_id' => $challengeId,
                'player1_id' => $student->id,
                'status' => 'waiting',
            ]);
        }

        return response()->json([
            'success' => true,
            'match_id' => $match->id,
            'status' => $match->status,
        ]);
    }

    /**
     * بحث عن طلاب لتحدّيهم (منافس محدّد) — أي طالب فعّال في المنصة عدا النفس.
     * يُعيد id/name فقط (لا بيانات حساسة).
     */
    public function pvpSearchOpponents(Request $request)
    {
        $student = Auth::user();
        $q = trim((string) $request->query('q', ''));

        $opponents = \App\Models\User::query()
            ->where('role', 'student')
            ->where('status', 'active')
            ->where('id', '!=', $student->id)
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%' . $q . '%'))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json(['success' => true, 'opponents' => $opponents]);
    }

    /**
     * تحدٍّ موجّه: دعوة طالب محدّد لمباراة (بإشعار، يقبل/يرفض).
     * يُنشئ مباراة بحالة "invited" (player2 معيّن) بدل مطابقة عشوائية.
     */
    public function challengeOpponent(Request $request, $challengeId)
    {
        $student = Auth::user();

        $data = $request->validate([
            'opponent_id' => 'required|integer',
        ]);

        $challenge = \App\Models\PvpChallenge::where('is_active', true)->findOrFail($challengeId);

        // المتحدّي يجب أن يملك وصولاً للتحدي (نفس نطاق مدرسته) — كبقية مسارات PvP.
        if (! \App\Models\PvpChallenge::availableForSchool($student->school_id)
            ->whereKey($challenge->id)->exists()) {
            abort(403);
        }

        // المنافس: طالب فعّال وليس النفس (أي طالب في المنصة).
        $opponent = \App\Models\User::where('role', 'student')
            ->where('status', 'active')
            ->find($data['opponent_id']);
        if (! $opponent || $opponent->id === $student->id) {
            return response()->json(['success' => false, 'message' => 'المنافس غير صالح'], 422);
        }

        // مكافحة الإغراق (1): سقف للدعوات المعلّقة الصادرة — يمنع نثر دعوات على كل الطلاب.
        $pendingOut = \App\Models\PvpMatch::where('player1_id', $student->id)
            ->where('status', 'invited')->count();
        if ($pendingOut >= 15) {
            return response()->json(['success' => false, 'message' => 'لديك دعوات معلّقة كثيرة. انتظر ردّها أولاً.'], 429);
        }

        // مكافحة الإغراق (2): تهدئة بعد الرفض — يمنع حلقة «ارفض ثم أعِد الدعوة» المضايِقة.
        $recentlyDeclined = \App\Models\PvpMatch::where('challenge_id', $challenge->id)
            ->where('player1_id', $student->id)
            ->where('player2_id', $opponent->id)
            ->where('status', 'declined')
            ->where('completed_at', '>=', now()->subMinutes(10))
            ->exists();
        if ($recentlyDeclined) {
            return response()->json(['success' => false, 'message' => 'اعتذر منافسك مؤخراً. جرّب لاحقاً أو اختر منافساً آخر.'], 429);
        }

        // منع تكرار الدعوات المعلّقة لنفس المنافس ونفس التحدي.
        $match = \App\Models\PvpMatch::where('challenge_id', $challenge->id)
            ->where('player1_id', $student->id)
            ->where('player2_id', $opponent->id)
            ->where('status', 'invited')
            ->first();

        if (! $match) {
            $match = \App\Models\PvpMatch::create([
                'challenge_id' => $challenge->id,
                'player1_id' => $student->id,
                'player2_id' => $opponent->id,
                'status' => 'invited',
            ]);

            try {
                \App\Services\NotificationService::create(
                    $opponent->id,
                    'pvp_invite',
                    '⚔️ تحدٍّ جديد!',
                    $student->name . ' يتحدّاك في «' . $challenge->title . '»',
                    ['match_id' => $match->id],
                    route('student.pvp.lobby'),
                );
            } catch (\Throwable $e) {
                \Log::warning('PvP invite notification failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'success' => true,
            'match_id' => $match->id,
            'status' => $match->status,
            'opponent' => $opponent->name,
        ]);
    }

    /**
     * قبول دعوة تحدٍّ موجّه — المدعوّ (player2) فقط. تبدأ المباراة (playing).
     */
    public function acceptPvpInvite($matchId)
    {
        $student = Auth::user();
        $match = \App\Models\PvpMatch::findOrFail($matchId);

        // IDOR: فقط المدعوّ يقبل، وفقط عند حالة invited.
        if ($match->player2_id !== $student->id || $match->status !== 'invited') {
            abort(403);
        }

        // تحديث ذرّي مشروط بالحالة: يمنع القبول المزدوج (سباق) من ختم/إشعار مكرّر.
        $accepted = \App\Models\PvpMatch::where('id', $match->id)
            ->where('status', 'invited')
            ->update(['status' => 'playing', 'started_at' => now()]);

        if ($accepted) {
            try {
                \App\Services\NotificationService::create(
                    $match->player1_id,
                    'pvp_accepted',
                    '✅ قُبِل تحدّيك!',
                    $student->name . ' قبل التحدي — المباراة جاهزة!',
                    ['match_id' => $match->id],
                    route('student.pvp.play', $match->id),
                );
            } catch (\Throwable $e) {
                \Log::warning('PvP accept notification failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'success' => true,
            'match_id' => $match->id,
            'redirect' => route('student.pvp.play', $match->id),
        ]);
    }

    /**
     * رفض دعوة تحدٍّ موجّه — المدعوّ (player2) فقط.
     */
    public function declinePvpInvite($matchId)
    {
        $student = Auth::user();
        $match = \App\Models\PvpMatch::findOrFail($matchId);

        if ($match->player2_id !== $student->id || $match->status !== 'invited') {
            abort(403);
        }

        // تحديث ذرّي مشروط بالحالة (اتّساقاً مع القبول ومنعاً لإشعار مكرّر).
        $declined = \App\Models\PvpMatch::where('id', $match->id)
            ->where('status', 'invited')
            ->update(['status' => 'declined', 'completed_at' => now()]);

        if ($declined) {
            try {
                \App\Services\NotificationService::create(
                    $match->player1_id,
                    'pvp_declined',
                    'اعتذر منافسك',
                    $student->name . ' لم يقبل التحدي هذه المرة.',
                    ['match_id' => $match->id],
                );
            } catch (\Throwable $e) {
                \Log::warning('PvP decline notification failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * حالة المباراة (للـ polling)
     */
    public function pvpMatchStatus($matchId)
    {
        $student = Auth::user();
        $match = \App\Models\PvpMatch::with(['player1:id,name', 'player2:id,name'])->findOrFail($matchId);

        // حماية IDOR: فقط المشاركون في المباراة يستطيعون قراءة الحالة
        if ($match->player1_id !== $student->id && $match->player2_id !== $student->id) {
            abort(403);
        }

        return response()->json([
            'status' => $match->status,
            'player1' => $match->player1?->name,
            'player2' => $match->player2?->name,
            'started_at' => $match->started_at,
        ]);
    }

    /**
     * صفحة اللعب PvP
     */
    public function pvpPlay($matchId)
    {
        $student = Auth::user();
        $match = \App\Models\PvpMatch::with('challenge')->findOrFail($matchId);

        if ($match->player1_id !== $student->id && $match->player2_id !== $student->id) {
            abort(403);
        }

        if ($match->status !== 'playing') {
            return redirect()->route('student.pvp.lobby')->with('error', 'المباراة غير متاحة');
        }

        // أسئلة موحّدة (تدعم الصيغة الجديدة inline والصيغة القديمة بمعرّفات البنك)
        $questions = $match->challenge->normalizedQuestions();

        return view('student.pvp-play', compact('match', 'questions'));
    }

    /**
     * إرسال إجابات PvP
     */
    public function submitPvpAnswers(Request $request, $matchId)
    {
        $student = Auth::user();
        $match = \App\Models\PvpMatch::with('challenge')->findOrFail($matchId);

        $isPlayer1 = $match->player1_id === $student->id;
        $isPlayer2 = $match->player2_id === $student->id;
        if (! $isPlayer1 && ! $isPlayer2) {
            abort(403);
        }

        // بوّابة حالة: لا قبول إلا أثناء اللعب، ولا إعادة إرسال بعد التقديم. بدونها كان يمكن
        // «الإرسال المسبق» أثناء الانتظار (waiting) لضمان الفوز، أو إعادة الإرسال لمباراة مكتملة
        // فتُعاد determineWinner وتُفسَد النتيجة.
        if ($match->status !== 'playing') {
            abort(403, 'المباراة غير جارية حالياً');
        }
        $alreadySubmitted = $isPlayer1 ? $match->player1_answers !== null : $match->player2_answers !== null;
        if ($alreadySubmitted) {
            abort(403, 'لقد أرسلت إجاباتك بالفعل لهذه المباراة');
        }

        $answers = $request->input('answers', []);
        $times = $request->input('times', []);
        $timeTaken = (int) $request->input('time_taken', 0);

        // helper مرن لـ true_false: يقبل "true/false" و "صح/خطأ"
        $pvpBoolish = function ($v) {
            $s = mb_strtolower(trim((string) $v));
            if (in_array($s, ['1', 'true', 'yes', 'صح', 'صحيح', 'نعم'], true)) {
                return true;
            }
            if (in_array($s, ['0', 'false', 'no', 'خطأ', 'خاطئ', 'لا'], true)) {
                return false;
            }

            return null;
        };

        // أسئلة موحّدة بمفاتيحها (تدعم الصيغة الجديدة inline والقديمة بالمعرّفات).
        // التسجيل حسب السرعة: درجة السؤال كاملة عند الإجابة الفورية، وتتناقص خطياً
        // حتى حدّ أدنى (25%) بعد نافذة 20 ثانية.
        $questions = $match->challenge->normalizedQuestions()->keyBy('key');
        $decayWindow = 20.0;
        $minFactor = 0.25;

        $score = 0;
        foreach ($questions as $key => $q) {
            $answer = $answers[$key] ?? null;
            if ($answer === null || $answer === '') {
                continue;
            }

            if (($q['type'] ?? '') === 'true_false') {
                $studentBool = $pvpBoolish($answer);
                $correctBool = $pvpBoolish($q['correct'] ?? '');
                $isCorrect = $studentBool !== null && $studentBool === $correctBool;
            } else {
                // اختيار من متعدد: الإجابة = فهرس الخيار، تُقارن بفهرس الصحيح
                $isCorrect = (string) $answer === (string) ($q['correct'] ?? '');
            }

            if ($isCorrect) {
                $rt = isset($times[$key]) ? (float) $times[$key] : $decayWindow;
                $factor = max($minFactor, 1.0 - ($rt / $decayWindow));
                $score += (int) round(((int) ($q['points'] ?? 100)) * $factor);
            }
        }

        if ($isPlayer1) {
            $match->update([
                'player1_answers' => $answers,
                'player1_score' => $score,
                'player1_time' => $timeTaken,
            ]);
        } else {
            $match->update([
                'player2_answers' => $answers,
                'player2_score' => $score,
                'player2_time' => $timeTaken,
            ]);
        }

        // إذا أرسل كلا اللاعبين — تحديد الفائز
        $match->refresh();
        $bothSubmitted = ($match->player1_answers !== null && $match->player2_answers !== null);

        if ($bothSubmitted) {
            // determineWinner ذرّي (lockForUpdate) — ينتقل من playing → completed مرة واحدة
            $match->determineWinner();
            $match->refresh();

            // منح نقاط/عملات للفائز — idempotent عبر AwardService: مفتاح = pvp_match/match.id
            // فائز واحد فقط يُدفع له مرة واحدة لكل مباراة. AwardService يملك المعاملة (ذرّية:
            // مطالبة دفتر القيد + Point + Coin معًا) ويعيد true فقط عند المنح الأول.
            // الحدود: إتمام المباراة (determineWinner) يُلتزم بشكل منفصل قبل المنح؛ التدفق
            // idempotent من طرف لطرف — إعادة الاستدعاء بعد completed لا تعيد المنح
            // (insertOrIgnore على match.id يقصر الدائرة) ولا تعيد إتمام المباراة (بوابة الحالة).
            if ($match->winner_id) {
                try {
                    $newlyAwarded = AwardService::award(
                        $match->winner_id,
                        'pvp_match',
                        (string) $match->id,
                        20,
                        10,
                        'فوز في تحدي PvP — مباراة #' . $match->id,
                    );

                    // إشعار للفائز — مرة واحدة فقط، عند المنح الأول
                    if ($newlyAwarded) {
                        try {
                            \App\Services\NotificationService::create(
                                $match->winner_id,
                                'pvp_win',
                                '🏆 مبروك! فزت بتحدي PvP',
                                'حصلت على 20 نقطة و 10 عملات',
                                ['match_id' => $match->id],
                            );
                        } catch (\Throwable $e) {
                            \Log::warning('PvP win notification failed', ['error' => $e->getMessage()]);
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error('PvP winner reward failed', [
                        'winner_id' => $match->winner_id,
                        'match_id' => $match->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'both_submitted' => $bothSubmitted,
            'match_id' => $match->id,
        ]);
    }

    /**
     * نتيجة مباراة PvP
     */
    public function pvpResult($matchId)
    {
        $student = Auth::user();
        $match = \App\Models\PvpMatch::with(['player1', 'player2', 'winner', 'challenge'])->findOrFail($matchId);

        if ($match->player1_id !== $student->id && $match->player2_id !== $student->id) {
            abort(403);
        }

        return view('student.pvp-result', compact('match', 'student'));
    }
}
