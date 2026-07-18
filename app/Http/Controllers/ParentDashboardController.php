<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivitySubmission;
use App\Models\Lesson;
use App\Models\Point;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ParentDashboardController extends Controller
{
    public function index()
    {
        $parent = Auth::user();

        // جلب جميع الأبناء مع بياناتهم
        // ملاحظة: استخدمنا select داخل withCount يكسر أحياناً عند join + status متعددة الحالات،
        // لذا نُبسِّط الاستعلام ونحسب الدروس المكتملة لاحقاً يدوياً.
        $children = $parent->children()
            ->with([
                'school',
                'classrooms',
                'streak',
            ])
            ->withCount([
                'activitySubmissions as completed_submissions_count' => function ($q) {
                    $q->whereIn('status', ['completed', 'approved', 'pending', 'needs_review']);
                },
            ])
            ->withSum('points as total_points', 'points')
            ->get();

        // حساب الإحصائيات لكل ابن
        $childrenData = $children->map(function ($child) {
            try {
                // النقاط الكلية - من withSum
                $totalPoints = (int) ($child->total_points ?? 0);

                // عدد الدروس المكتملة (DISTINCT lesson عبر submissions الناجحة)
                $completedLessons = (int) DB::table('activity_submissions')
                    ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
                    ->where('activity_submissions.student_id', $child->id)
                    ->whereIn('activity_submissions.status', \App\Models\ActivitySubmission::DONE_STATUSES)
                    ->distinct('activities.lesson_id')
                    ->count('activities.lesson_id');

                // السلسلة الحالية
                $streakDays = $child->streak ? $child->streak->current_streak : 0;

                // العملات والشارات - سيتم إضافتها لاحقاً
                $totalCoins = 0;
                $badgesCount = 0;

                // الفصل الحالي
                $classroom = $child->classrooms->where('pivot.status', 'active')->first()
                    ?? $child->classrooms->first();

                // الترتيبات — Cache مع مفتاح موحّد يتطابق مع Point::created listener
                // (المفتاح السابق كان يحتوي على totalPoints suffix → ما يُلغى أبدًا بواسطة forget)
                $rankCacheKey = "parent_dashboard:ranks:{$child->id}";
                $ranks = Cache::remember($rankCacheKey, 1800, function () use ($child, $classroom, $totalPoints) {
                    $classRank = null;
                    if ($classroom) {
                        $classRank = DB::table('users')
                            ->join('classroom_student', 'users.id', '=', 'classroom_student.student_id')
                            ->where('classroom_student.classroom_id', $classroom->id)
                            ->where('classroom_student.status', 'active')
                            ->whereRaw('(SELECT COALESCE(SUM(points), 0) FROM points WHERE user_id = users.id) > ?', [$totalPoints])
                            ->count() + 1;
                    }

                    $schoolRank = null;
                    if ($child->school_id) {
                        $schoolRank = DB::table('users')
                            ->where('role', UserRole::Student->value)
                            ->where('school_id', $child->school_id)
                            ->whereRaw('(SELECT COALESCE(SUM(points), 0) FROM points WHERE user_id = users.id) > ?', [$totalPoints])
                            ->count() + 1;
                    }

                    $cityRank = null;
                    if ($child->school && $child->school->city) {
                        $cityRank = DB::table('users')
                            ->join('schools', 'users.school_id', '=', 'schools.id')
                            ->where('users.role', 'student')
                            ->where('schools.city', $child->school->city)
                            ->whereRaw('(SELECT COALESCE(SUM(points), 0) FROM points WHERE user_id = users.id) > ?', [$totalPoints])
                            ->count() + 1;
                    }

                    $countryRank = DB::table('users')
                        ->where('role', UserRole::Student->value)
                        ->whereRaw('(SELECT COALESCE(SUM(points), 0) FROM points WHERE user_id = users.id) > ?', [$totalPoints])
                        ->count() + 1;

                    return compact('classRank', 'schoolRank', 'cityRank', 'countryRank');
                });

                $classRank = $ranks['classRank'];
                $schoolRank = $ranks['schoolRank'];
                $cityRank = $ranks['cityRank'];
                $countryRank = $ranks['countryRank'];

                // آخر الأنشطة - محدودة ب 5 فقط
                $recentActivities = $child->activitySubmissions()
                    ->with('activity')
                    ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'avatar' => $child->avatar,          // المسار الخام (للحفظ)
                    'avatar_url' => $child->avatar_url,  // URL كامل صحيح للعرض
                    'email' => $child->email,
                    'school' => $child->school ? $child->school->name : 'غير محدد',
                    'classroom' => $classroom ? $classroom->name : 'غير محدد',
                    'grade' => $classroom ? $classroom->grade_level : 'غير محدد',
                    'total_points' => $totalPoints,
                    'completed_lessons' => $completedLessons,
                    'streak_days' => $streakDays,
                    'total_coins' => $totalCoins,
                    'badges_count' => $badgesCount,
                    'class_rank' => $classRank,
                    'school_rank' => $schoolRank,
                    'city_rank' => $cityRank,
                    'country_rank' => $countryRank,
                    'recent_activities' => $recentActivities,
                    'relationship' => $child->pivot->relationship ?? 'ولي أمر',
                ];
            } catch (\Exception $e) {
                \Log::error('خطأ في حساب بيانات الابن: ' . $e->getMessage(), [
                    'child_id' => $child->id,
                    'line' => $e->getLine(),
                ]);

                // إرجاع بيانات افتراضية في حالة الخطأ
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'avatar' => $child->avatar,
                    'avatar_url' => $child->avatar_url,
                    'email' => $child->email,
                    'school' => 'غير محدد',
                    'classroom' => 'غير محدد',
                    'grade' => 'غير محدد',
                    'total_points' => 0,
                    'completed_lessons' => 0,
                    'streak_days' => 0,
                    'total_coins' => 0,
                    'badges_count' => 0,
                    'class_rank' => null,
                    'school_rank' => null,
                    'city_rank' => null,
                    'country_rank' => null,
                    'recent_activities' => collect([]),
                    'relationship' => 'ولي أمر',
                    'error' => true,
                ];
            }
        });

        // إحصائيات مقارنة المدرسة مع باقي المدارس — كل القسم محاط بـ try-catch
        // لأن أي خطأ في الاستعلامات الإحصائية لا يجب أن يكسر صفحة ولي الأمر بأكملها (#75).
        $schoolComparison = null;
        $firstChild = $children->first();
        try {
            if ($firstChild && $firstChild->school_id) {
                $childSchool = $firstChild->school; // قد تكون null إن حُذفت المدرسة

                // عدد الطلاب والمعلمين في مدرسة الابن
                $schoolStudents = DB::table('users')->where('school_id', $firstChild->school_id)->where('role', UserRole::Student->value)->where('status', 'active')->count();
                $schoolTeachers = DB::table('users')->where('school_id', $firstChild->school_id)->where('role', UserRole::Teacher->value)->where('status', 'active')->count();

                // مجموع نقاط طلاب المدرسة
                $schoolTotalPoints = (int) DB::table('points')
                    ->join('users', 'points.user_id', '=', 'users.id')
                    ->where('users.school_id', $firstChild->school_id)
                    ->where('users.role', 'student')
                    ->sum('points.points');

                // متوسط النقاط لكل طالب في المدرسة
                $schoolAvgPoints = $schoolStudents > 0 ? round($schoolTotalPoints / $schoolStudents) : 0;

                // ترتيب المدرسة بين جميع المدارس حسب متوسط نقاط الطلاب
                // ملاحظة: نتجنّب subquery يحوي users.id مع GROUP BY لتجنّب ONLY_FULL_GROUP_BY في MySQL
                $allSchoolsStats = DB::table('schools')
                    ->select('schools.id', 'schools.name')
                    ->selectRaw('COUNT(DISTINCT CASE WHEN users.role = "student" AND users.status = "active" THEN users.id END) as students_count')
                    ->selectRaw('COUNT(DISTINCT CASE WHEN users.role = "teacher" AND users.status = "active" THEN users.id END) as teachers_count')
                    ->selectRaw('COALESCE(SUM(CASE WHEN users.role = "student" THEN points_agg.user_points ELSE 0 END), 0) as total_points')
                    ->leftJoin('users', 'schools.id', '=', 'users.school_id')
                    ->leftJoinSub(
                        DB::table('points')
                            ->select('user_id')
                            ->selectRaw('COALESCE(SUM(points), 0) as user_points')
                            ->groupBy('user_id'),
                        'points_agg',
                        'points_agg.user_id',
                        '=',
                        'users.id',
                    )
                    ->groupBy('schools.id', 'schools.name')
                    ->having('students_count', '>', 0)
                    ->orderByDesc('total_points')
                    ->get();

                $totalSchools = $allSchoolsStats->count();
                // التعامل الصحيح مع false عند عدم وجود المدرسة في الإحصائيات
                $schoolIdx = $allSchoolsStats->search(fn ($s) => $s->id === $firstChild->school_id);
                $schoolRankAmongAll = $schoolIdx === false ? null : $schoolIdx + 1;

                // متوسط نقاط جميع المدارس
                $allSchoolsAvgPoints = $totalSchools > 0 ? round($allSchoolsStats->avg(fn ($s) => $s->students_count > 0 ? $s->total_points / $s->students_count : 0)) : 0;
                // متوسط معلمين جميع المدارس
                $allSchoolsAvgTeachers = $totalSchools > 0 ? round($allSchoolsStats->avg('teachers_count')) : 0;
                // متوسط طلاب جميع المدارس
                $allSchoolsAvgStudents = $totalSchools > 0 ? round($allSchoolsStats->avg('students_count')) : 0;

                $schoolComparison = [
                    'school_name' => optional($childSchool)->name ?? 'غير محدد',
                    'school_students' => $schoolStudents,
                    'school_teachers' => $schoolTeachers,
                    'school_total_points' => $schoolTotalPoints,
                    'school_avg_points' => $schoolAvgPoints,
                    'school_rank' => $schoolRankAmongAll,
                    'total_schools' => $totalSchools,
                    'all_avg_points' => $allSchoolsAvgPoints,
                    'all_avg_teachers' => $allSchoolsAvgTeachers,
                    'all_avg_students' => $allSchoolsAvgStudents,
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('parent dashboard schoolComparison failed', [
                'parent_id' => $parent->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            $schoolComparison = null; // fallback صامت — الصفحة تعرض بدون قسم المقارنة
        }

        return view('parent.dashboard', compact('childrenData', 'schoolComparison'));
    }

    public function childDetails($childId)
    {
        $parent = Auth::user();

        // التحقق من أن الطفل تابع لولي الأمر
        $child = $parent->children()->where('users.id', $childId)->first();

        if (! $child) {
            abort(403, 'غير مصرح لك بعرض هذه البيانات');
        }

        // جلب بيانات تفصيلية
        $child->load([
            'school',
            'classrooms',
            'badges',
            'streak',
        ]);

        // === الإحصائيات ===
        $totalPoints = (int) DB::table('points')->where('user_id', $child->id)->sum('points');
        $totalCoins = (int) DB::table('coins')->where('user_id', $child->id)->sum('coins');

        $submissionStats = ActivitySubmission::where('student_id', $child->id)
            ->selectRaw("
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                AVG(CASE WHEN score IS NOT NULL THEN score END) as avg_score
            ")
            ->first();

        $stats = [
            'total_points' => $totalPoints,
            'total_coins' => $totalCoins,
            'completed_activities' => (int) ($submissionStats->completed_count ?? 0),
            'pending_activities' => (int) ($submissionStats->pending_count ?? 0),
            'average_score' => round($submissionStats->avg_score ?? 0, 1),
        ];

        // === المستوى ===
        $level = floor($totalPoints / 100) + 1;
        $levelProgress = $totalPoints % 100;

        // === السلسلة ===
        $streak = $child->streak;

        // === الشارات ===
        $badges = $child->badges ?? collect();

        // === الفصل الحالي ===
        $classroom = $child->classrooms->first();

        // === الترتيب ===
        $classRank = null;
        if ($classroom) {
            $classRank = DB::table('users')
                ->join('classroom_student', 'users.id', '=', 'classroom_student.student_id')
                ->where('classroom_student.classroom_id', $classroom->id)
                ->whereRaw('(SELECT COALESCE(SUM(points), 0) FROM points WHERE user_id = users.id) > ?', [$totalPoints])
                ->count() + 1;
        }

        $schoolRank = null;
        if ($child->school_id) {
            $schoolRank = DB::table('users')
                ->where('role', UserRole::Student->value)
                ->where('school_id', $child->school_id)
                ->whereRaw('(SELECT COALESCE(SUM(points), 0) FROM points WHERE user_id = users.id) > ?', [$totalPoints])
                ->count() + 1;
        }

        $totalStudents = DB::table('users')
            ->where('role', UserRole::Student->value)
            ->where('school_id', $child->school_id)
            ->count();

        // === المعلمون ===
        $teachers = collect();
        if ($classroom) {
            $teachers = User::where('role', 'teacher')
                ->whereHas('teachingClassrooms', function ($q) use ($child) {
                    $q->whereHas('students', function ($sq) use ($child) {
                        $sq->where('classroom_student.student_id', $child->id);
                    });
                })
                ->select('id', 'name', 'email')
                ->get();
        }

        // === آخر الأنشطة ===
        $recentActivities = ActivitySubmission::where('student_id', $child->id)
            ->with(['activity.lesson'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // === بيانات الرسم البياني (آخر 30 يوم) — مُحسَّن (2 queries بدل 60) ===
        $startDate = now()->subDays(29)->startOfDay();

        $pointsByDate = DB::table('points')
            ->where('user_id', $child->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as d, SUM(points) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $activitiesByDate = ActivitySubmission::where('student_id', $child->id)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ActivitySubmission::DONE_STATUSES)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $chartLabels = [];
        $chartPoints = [];
        $chartActivities = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d/m');
            $chartPoints[] = (int) ($pointsByDate[$key] ?? 0);
            $chartActivities[] = (int) ($activitiesByDate[$key] ?? 0);
        }

        $chartData = [
            'labels' => $chartLabels,
            'points' => $chartPoints,
            'activities' => $chartActivities,
        ];

        // === أداء حسب القيم (Values) ===
        try {
            $valuesProgress = DB::table('activity_submissions')
                ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
                ->join('lessons', 'activities.lesson_id', '=', 'lessons.id')
                ->join('concepts', 'lessons.concept_id', '=', 'concepts.id')
                ->join('values', 'concepts.value_id', '=', 'values.id')
                ->where('activity_submissions.student_id', $child->id)
                ->where('activity_submissions.status', 'completed')
                ->select('values.id', 'values.name', 'values.icon')
                ->selectRaw('COUNT(*) as completed_count')
                ->selectRaw('AVG(activity_submissions.score) as avg_score')
                ->groupBy('values.id', 'values.name', 'values.icon')
                ->get();
        } catch (\Exception $e) {
            $valuesProgress = collect();
        }

        // === نشاط الأسبوع — استعلام واحد فقط ===
        $weekStart = now()->subDays(6)->startOfDay();
        $weekActivityByDate = ActivitySubmission::where('student_id', $child->id)
            ->where('created_at', '>=', $weekStart)
            ->whereIn('status', ActivitySubmission::DONE_STATUSES)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $arabicDays = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        $weeklyActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $weeklyActivity[] = [
                'day' => $arabicDays[$date->dayOfWeek],
                'count' => (int) ($weekActivityByDate[$key] ?? 0),
                'date' => $date->format('d/m'),
            ];
        }

        return view('parent.child-detail', compact(
            'child',
            'stats',
            'level',
            'levelProgress',
            'streak',
            'badges',
            'classroom',
            'classRank',
            'schoolRank',
            'totalStudents',
            'teachers',
            'recentActivities',
            'chartData',
            'valuesProgress',
            'weeklyActivity',
        ));
    }

    /**
     * تقرير مقارنة استبيان قبلي/بعدي — مفلتر على أبناء ولي الأمر
     */
    public function surveyComparison($surveyId)
    {
        $user = Auth::user();
        $survey = \App\Models\Survey::findOrFail($surveyId);

        if (! $survey->isAssessment()) {
            return back()->with('error', 'هذا الاستبيان ليس من نوع التقييم القبلي/البعدي');
        }

        $survey->load(['lesson.concept.value', 'value', 'linkedSurvey', 'questions']);

        // فلترة على أبناء ولي الأمر فقط
        $childIds = $user->children()->pluck('users.id')->toArray();

        if (empty($childIds)) {
            return back()->with('error', 'لا يوجد أبناء مرتبطين بحسابك');
        }

        $comparisonData = $survey->getComparisonData(null, $childIds);

        if (isset($comparisonData['error'])) {
            return back()->with('error', $comparisonData['error']);
        }

        return view('parent.surveys.comparison', compact('survey', 'comparisonData'));
    }

    /**
     * قائمة استبيانات التقييم التي شارك فيها أبناء ولي الأمر
     */
    public function surveyComparisonsList()
    {
        $user = Auth::user();
        $childIds = $user->children()->pluck('users.id')->toArray();

        $surveys = \App\Models\Survey::where('survey_type', 'pre_post_assessment')
            ->where('assessment_phase', 'post')
            ->whereHas('responses', function ($q) use ($childIds) {
                $q->whereIn('user_id', $childIds);
            })
            ->with(['lesson.concept.value', 'value', 'linkedSurvey'])
            ->latest()
            ->paginate(20);

        return view('parent.surveys.comparisons-list', compact('surveys'));
    }
}
