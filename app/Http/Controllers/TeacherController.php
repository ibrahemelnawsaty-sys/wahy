<?php

namespace App\Http\Controllers;

use App\Events\ActivityCompleted;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Classroom;
use App\Models\QuestionBank;
use App\Models\TeacherPoint;
use App\Models\Team;
use App\Models\TeamActivity;
use App\Models\User;
use App\Services\AwardService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    /**
     * لوحة التحكم الرئيسية للمعلم - محسّنة
     */
    public function dashboard()
    {
        $user = Auth::user();
        $school = $user->school;

        if (! $school) {
            abort(403, 'لا يوجد مدرسة مرتبطة بحسابك');
        }

        // الفصول التي يدرسها المعلم مع Eager Loading محسّن
        $classrooms = $user->teachingClassrooms()
            ->where('school_id', $school->id)
            ->select(['id', 'name', 'school_id', 'teacher_id', 'grade_level'])
            ->withCount('students')
            ->get();

        // جمع IDs الطلاب بطريقة أكثر كفاءة
        $studentIds = DB::table('classroom_student')
            ->whereIn('classroom_id', $classrooms->pluck('id'))
            ->distinct()
            ->pluck('student_id')
            ->toArray();

        // حساب إجمالي الأنشطة مرة واحدة خارج الـ loop (إصلاح N+1)
        $totalActivities = Activity::count();

        // حساب البيانات الحقيقية لكل فصل
        foreach ($classrooms as $classroom) {
            // عدد الأنشطة المرسلة للفصل
            $classroom->total_activities = $totalActivities;

            // الطلاب في هذا الفصل
            $classroomStudentIds = DB::table('classroom_student')
                ->where('classroom_id', $classroom->id)
                ->pluck('student_id')
                ->toArray();

            // الأنشطة التي تحتاج مراجعة لهذا الفصل (تصحيح يدوي + فشل تصحيح آلي)
            $classroom->pending_count = ActivitySubmission::whereIn('student_id', $classroomStudentIds)
                ->whereIn('status', ActivitySubmission::PENDING_REVIEW_STATUSES)->parentCleared()
                ->count();

            // الأنشطة المكتملة لهذا الفصل — عدد أزواج (طالب × نشاط) لا DISTINCT
            // المعتمد فقط (completed/approved) ليتطابق مع المعنى الحقيقي للإنجاز
            $completedCount = ActivitySubmission::whereIn('student_id', $classroomStudentIds)
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                ->count();

            // نسبة التفاعل: العدد الفعلي مقسومًا على الإمكانية الكاملة
            $studentCount = count($classroomStudentIds);
            $totalPossibleActivities = $totalActivities * $studentCount;
            $classroom->engagement_percent = $totalPossibleActivities > 0
                ? min(100, round(($completedCount / $totalPossibleActivities) * 100))
                : 0;
        }

        // الأنشطة المعلقة (تحتاج مراجعة) - مع تحديد الحقول
        $pendingSubmissions = ActivitySubmission::whereIn('student_id', $studentIds)
            ->whereIn('status', ActivitySubmission::PENDING_REVIEW_STATUSES)->parentCleared()
            ->with(['student:id,name,avatar', 'activity:id,title'])
            ->select(['id', 'student_id', 'activity_id', 'submitted_at', 'status'])
            ->latest()
            ->take(10)
            ->get();

        // إحصائيات سريعة - استعلام واحد محسّن
        $stats = [
            'total_classrooms' => $classrooms->count(),
            'total_students' => count($studentIds),
            'pending_submissions' => ActivitySubmission::whereIn('student_id', $studentIds)
                ->whereIn('status', ActivitySubmission::PENDING_REVIEW_STATUSES)->parentCleared()->count(),
            'reviewed_today' => ActivitySubmission::whereIn('student_id', $studentIds)
                ->where('reviewed_by', $user->id)
                ->whereDate('reviewed_at', today())
                ->count(),
            'average_rating' => 0, // يمكن إضافة نظام التقييم لاحقاً
        ];

        // آخر التقييمات من الطلاب (سيتم إضافتها لاحقاً)
        $recentRatings = collect();

        // إجمالي الطلاب للـ Dashboard
        $totalStudents = count($studentIds);

        return view('teacher.dashboard', compact(
            'user',
            'school',
            'classrooms',
            'pendingSubmissions',
            'stats',
            'recentRatings',
            'totalStudents',
        ));
    }

    /**
     * عرض الأنشطة المعلقة للمراجعة
     */
    public function reviewSubmissions()
    {
        $user = Auth::user();
        $classrooms = $user->teachingClassrooms()->pluck('id');

        $studentIds = DB::table('classroom_student')
            ->whereIn('classroom_id', $classrooms)
            ->distinct()
            ->pluck('student_id');

        // pending = بانتظار تصحيح يدوي (رفع/مقالي)، needs_review = لم يجتَز التصحيح الآلي
        // (إجابة خاطئة) — كلاهما يظهر للمعلم ليصحّح أو يسمح بإعادة المحاولة.
        $submissions = ActivitySubmission::whereIn('student_id', $studentIds)
            ->whereIn('status', ActivitySubmission::PENDING_REVIEW_STATUSES)->parentCleared()
            ->with(['student', 'activity.lesson.concept.value'])
            ->latest('submitted_at')
            ->paginate(20);

        return view('teacher.review-submissions', compact('submissions'));
    }

    /**
     * مراجعة نشاط محدد
     */
    public function reviewSubmission($id)
    {
        $submission = ActivitySubmission::with([
            'student',
            'activity.lesson.concept.value',
        ])->findOrFail($id);

        // التحقق من أن الطالب تابع للمعلم
        $user = Auth::user();
        $hasAccess = DB::table('classroom_student')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.teacher_id', $user->id)
            ->where('classroom_student.student_id', $submission->student_id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'ليس لديك صلاحية لمراجعة هذا النشاط');
        }

        return view('teacher.review-single', compact('submission'));
    }

    /**
     * حفظ تقييم النشاط
     */
    public function submitReview(Request $request, $id)
    {
        // المعلّم يُقيّم فقط (الدرجة + ملاحظة). النقاط/العملات محدَّدة مسبقاً بالنشاط
        // وتُحسب تلقائياً من درجته — لا يُدخلها المعلّم يدوياً.
        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        // تنفيذ ذرّي: قفل صفّي على التسليم + تحقق الصلاحية + تحديث
        // لمنع race بين المعلم والـ auto-grader (تجنّب last-write-wins)
        try {
            $submission = DB::transaction(function () use ($id, $user, $request) {
                $submission = ActivitySubmission::lockForUpdate()->findOrFail($id);

                $hasAccess = DB::table('classroom_student')
                    ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
                    ->where('classrooms.teacher_id', $user->id)
                    ->where('classroom_student.student_id', $submission->student_id)
                    ->exists();

                if (! $hasAccess) {
                    throw new \Illuminate\Auth\Access\AuthorizationException('ليس لديك صلاحية');
                }

                $submission->update([
                    'status' => 'approved',
                    'score' => $request->score,
                    'feedback' => $request->feedback,
                    'teacher_feedback' => $request->feedback,
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                ]);

                return $submission;
            }, 3);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            \Log::warning('Teacher review authorization failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'غير مصرح بهذا الإجراء'], 403);
        }

        // كل العمليات الثانوية (XP/Coins/إشعارات) ملفوفة بـ try-catch
        // لأن submission update تم بنجاح قبلها — لا نريد كسر الرد لو فشل listener/خدمة (P1-D).
        try {
            $student = User::find($submission->student_id);
            $activityTitle = optional($submission->activity)->title ?? 'نشاط';

            // المكافأة محدَّدة مسبقاً بنقاط النشاط وتُقاس بدرجة المعلّم — نفس صيغة
            // التصحيح التلقائي (StudentController): XP = round(score/100 × activity.points)،
            // coins = max(1, ⌊XP/2⌋). فنشاطٌ يُصحَّح يدوياً وآخر آلياً بنفس الدرجة/النقاط
            // يمنحان المكافأة ذاتها. المعلّم يُدخل الدرجة فقط ولا يمنح النقاط يدوياً.
            $activityPoints = (int) (optional($submission->activity)->points ?? 10);
            $finalXp = (int) round(((float) $request->score / 100) * $activityPoints);
            $finalCoins = $finalXp > 0 ? max(1, (int) floor($finalXp / 2)) : 0;

            // مصالحة مع المنح الآليّ: تسليمٌ لم يجتَز التصحيح الآليّ (needs_review) يكون قد مُنِح
            // مكافأةً جزئيّةً آلياً (عمود awarded_points، دفتر Point منفصل). نمنح هنا **الفرق
            // التصاعديّ** فوقها فقط كي لا يحصل الطالب على مكافأتين (جزئيّ آليّ + كامل من المعلّم).
            // تسليمُ المراجعة اليدويّة (pending، score=null) لم يُمنَح آلياً فـawarded_points=0 → المنح كامل.
            $autoXp = (int) ($submission->awarded_points ?? 0);
            $autoCoins = $autoXp > 0 ? max(1, (int) floor($autoXp / 2)) : 0;
            $xpAward = max(0, $finalXp - $autoXp);
            $coinsAward = max(0, $finalCoins - $autoCoins);

            // Pass-4 Batch 2: ONE atomic + idempotent award keyed on this submission.
            // Re-grading the same submission is a no-op — no double XP/coins and no
            // re-distribution. Student XP+coins and the teacher/parent/school fan-out
            // commit (or roll back) together inside AwardService.
            try {
                \App\Services\AwardService::award(
                    $submission->student_id,
                    'activity_submission',
                    (string) $submission->id,
                    $xpAward,
                    $coinsAward,
                    'إكمال نشاط: ' . $activityTitle,
                    distribute: true,
                );
            } catch (\Throwable $e) {
                \Log::warning('activity award failed: ' . $e->getMessage());
            }

            try {
                NotificationService::activityGraded($submission->student_id, $activityTitle, $request->score, $request->feedback);
            } catch (\Throwable $e) {
                \Log::warning('activityGraded notification failed: ' . $e->getMessage());
            }

            if ($student && $student->parents && $student->parents->count() > 0) {
                foreach ($student->parents as $parent) {
                    try {
                        NotificationService::parentNotification($parent->id, $student->name, "تم تقييم نشاط '{$activityTitle}' - حصل على {$request->score}%", 'child_activity');
                    } catch (\Throwable $e) {
                        \Log::warning('parent notification failed: ' . $e->getMessage());
                    }
                }
            }

            try {
                event(new \App\Events\ActivityGraded($submission, $request->score, $request->feedback));
            } catch (\Throwable $e) {
                \Log::warning('ActivityGraded event failed: ' . $e->getMessage());
            }

            if ($student && $submission->activity) {
                try {
                    event(new ActivityCompleted($student, $submission->activity, $request->score, $xpAward, $coinsAward));
                } catch (\Throwable $e) {
                    \Log::warning('ActivityCompleted event failed: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            \Log::error('submitReview post-processing failed: ' . $e->getMessage());
            // المراجعة محفوظة — نعتبرها نجحت رغم فشل الخدمات الثانوية
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تقييم النشاط بنجاح',
        ]);
    }

    /**
     * السماح للطالب بإعادة محاولة النشاط — يعيد التسليم لحالة قابلة لإعادة الإرسال
     * ويصفّر عدّاد المحاولات ليحصل على مجموعة محاولات جديدة.
     */
    public function allowRetry(Request $request, $id)
    {
        $user = Auth::user();

        try {
            $submission = DB::transaction(function () use ($id, $user, $request) {
                $submission = ActivitySubmission::lockForUpdate()->findOrFail($id);

                $hasAccess = DB::table('classroom_student')
                    ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
                    ->where('classrooms.teacher_id', $user->id)
                    ->where('classroom_student.student_id', $submission->student_id)
                    ->exists();

                if (! $hasAccess) {
                    throw new \Illuminate\Auth\Access\AuthorizationException('ليس لديك صلاحية');
                }

                $submission->update([
                    'status' => 'needs_review',
                    'attempts' => 0,
                    'feedback' => $request->input('feedback') ?: 'سمح لك المعلم بإعادة المحاولة.',
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                ]);

                return $submission;
            }, 3);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['error' => 'غير مصرح بهذا الإجراء'], 403);
        }

        try {
            $activityTitle = optional($submission->activity)->title ?? 'نشاط';
            NotificationService::create(
                $submission->student_id,
                'activity_retry',
                '🔄 يمكنك إعادة المحاولة',
                "سمح لك المعلم بإعادة محاولة نشاط: {$activityTitle}",
                ['activity_id' => $submission->activity_id],
            );
        } catch (\Throwable $e) {
            \Log::warning('retry notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم السماح للطالب بإعادة المحاولة',
        ]);
    }

    /**
     * تقارير الطلاب
     */
    public function studentReports()
    {
        $user = Auth::user();
        $classrooms = $user->teachingClassrooms()->with('students')->get();

        $students = $classrooms->flatMap(function ($classroom) {
            return $classroom->students->map(function ($student) use ($classroom) {
                $student->classroom_name = $classroom->name;

                return $student;
            });
        })->unique('id');

        $studentIds = $students->pluck('id')->toArray();

        // إصلاح N+1: 4 queries موحَّدة بدلاً من N×5
        $totalXp = DB::table('points')
            ->whereIn('user_id', $studentIds)
            ->select('user_id', DB::raw('SUM(points) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $totalCoins = DB::table('coins')
            ->whereIn('user_id', $studentIds)
            ->select('user_id', DB::raw('SUM(coins) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $completedStats = ActivitySubmission::whereIn('student_id', $studentIds)
            ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
            ->select('student_id', DB::raw('COUNT(*) as cnt'), DB::raw('AVG(score) as avg_score'))
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $streaks = DB::table('streaks')
            ->whereIn('user_id', $studentIds)
            ->pluck('current_streak', 'user_id');

        // تعيين البيانات لكل طالب من النتائج المجمّعة
        foreach ($students as $student) {
            $student->total_xp = $totalXp[$student->id] ?? 0;
            $student->total_coins = $totalCoins[$student->id] ?? 0;
            $student->completed_activities = $completedStats[$student->id]->cnt ?? 0;
            $student->average_score = $completedStats[$student->id]->avg_score ?? null;
            $student->streak_days = $streaks[$student->id] ?? 0;
        }

        return view('teacher.student-reports', compact('students', 'classrooms'));
    }

    /**
     * تفاصيل طالب محدد
     */
    public function studentDetail($id)
    {
        $user = Auth::user();
        $student = \App\Models\User::findOrFail($id);

        // التحقق من الصلاحية
        $hasAccess = DB::table('classroom_student')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.teacher_id', $user->id)
            ->where('classroom_student.student_id', $id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'ليس لديك صلاحية لعرض هذا الطالب');
        }

        // الإحصائيات
        $stats = [
            'total_xp' => DB::table('points')->where('user_id', $id)->sum('points'),
            'total_coins' => DB::table('coins')->where('user_id', $id)->sum('coins'),
            'current_level' => floor(DB::table('points')->where('user_id', $id)->sum('points') / 100) + 1,
            'streak_days' => $student->streak->current_streak ?? 0,
            'badges_count' => DB::table('user_badges')->where('user_id', $id)->count(),
            'completed_activities' => ActivitySubmission::where('student_id', $id)
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)->count(),
            'pending_activities' => ActivitySubmission::where('student_id', $id)
                ->where('status', 'pending')->count(),
            'average_score' => ActivitySubmission::where('student_id', $id)
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)->avg('score'),
        ];

        // آخر الأنشطة
        $recentActivities = ActivitySubmission::where('student_id', $id)
            ->with('activity.lesson.concept.value')
            ->latest('submitted_at')
            ->take(10)
            ->get();

        // تقدم XP الشهري — مع whereYear لمنع تلوث بين السنوات
        $xpProgress = DB::table('points')
            ->where('user_id', $id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('DATE(created_at) as date, SUM(points) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('teacher.student-detail', compact('student', 'stats', 'recentActivities', 'xpProgress'));
    }

    /**
     * عرض الفصول الدراسية
     */
    public function classrooms()
    {
        $user = Auth::user();
        $school = $user->school;

        if (! $school) {
            abort(403, 'لا يوجد مدرسة مرتبطة بحسابك');
        }

        // الفصول التي يدرسها المعلم
        $classrooms = $user->teachingClassrooms()
            ->where('school_id', $school->id)
            ->withCount('students')
            ->with('students:id,name,email,avatar')
            ->get();

        // حساب البيانات الحقيقية لكل فصل
        foreach ($classrooms as $classroom) {
            // الطلاب في هذا الفصل
            $classroomStudentIds = DB::table('classroom_student')
                ->where('classroom_id', $classroom->id)
                ->pluck('student_id')
                ->toArray();

            // الأنشطة المكتملة لهذا الفصل
            $completedCount = ActivitySubmission::whereIn('student_id', $classroomStudentIds)
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                ->distinct('activity_id')
                ->count('activity_id');

            // إجمالي الأنشطة المتاحة
            $totalActivities = Activity::count();

            // نسبة التقدم (الأنشطة المكتملة / إجمالي الأنشطة * عدد الطلاب)
            $totalPossibleActivities = $totalActivities * count($classroomStudentIds);
            $classroom->progress_percent = $totalPossibleActivities > 0 && count($classroomStudentIds) > 0
                ? round(($completedCount / $totalPossibleActivities) * 100)
                : 0;
        }

        // إحصائيات عامة
        $stats = [
            'total_classrooms' => $classrooms->count(),
            'total_students' => $classrooms->sum('students_count'),
            'active_classrooms' => $classrooms->filter(fn ($c) => $c->students_count > 0)->count(),
        ];

        return view('teacher.classrooms', compact('classrooms', 'stats', 'school'));
    }

    /**
     * تفاصيل فصل محدد
     */
    public function classroomDetail($id)
    {
        $user = Auth::user();
        $classroom = Classroom::where('teacher_id', $user->id)
            ->where('id', $id)
            ->withCount('students')
            ->with('students')
            ->firstOrFail();

        // إحصائيات الفصل
        $studentIds = $classroom->students->pluck('id');

        $stats = [
            'total_students' => $classroom->students_count,
            'average_performance' => ActivitySubmission::whereIn('student_id', $studentIds)
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                ->avg('score') ?? 0,
            'completed_activities' => ActivitySubmission::whereIn('student_id', $studentIds)
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                ->count(),
            'pending_activities' => ActivitySubmission::whereIn('student_id', $studentIds)
                ->where('status', 'pending')
                ->count(),
        ];

        return view('teacher.classroom-detail', compact('classroom', 'stats'));
    }

    /**
     * صفحة الإعدادات
     */
    public function settings()
    {
        $user = Auth::user();
        $school = $user->school;

        return view('teacher.settings', compact('user', 'school'));
    }

    /**
     * تحديث الإعدادات
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
            'avatar' => 'sometimes|image|max:2048',
            'bio' => 'sometimes|string|max:500',
            'notifications_enabled' => 'sometimes|boolean',
        ]);

        // تحديث الصورة الشخصية
        if ($request->hasFile('avatar')) {
            // حذف الصورة القديمة إن وجدت
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return redirect()->route('teacher.settings')
            ->with('success', 'تم تحديث الإعدادات بنجاح');
    }

    /**
     * عرض صفحة إعدادات مكافأة الالتزام اليومي
     */
    public function streakSettings()
    {
        $teacher = auth()->user();

        // جلب الإعدادات الحالية من جدول settings
        $settings = \App\Models\Setting::where('key', 'like', 'streak_%')
            ->get()
            ->keyBy('key');

        // الإعدادات الافتراضية
        $streakSettings = [
            'enabled' => $settings->get('streak_enabled')?->value ?? true,
            'min_days' => $settings->get('streak_min_days')?->value ?? 3,
            'max_days' => $settings->get('streak_max_days')?->value ?? 7,
            'bonus_points' => $settings->get('streak_bonus_points')?->value ?? 50,
        ];

        // إحصائيات الطلاب
        $classroomIds = $teacher->teachingClassrooms()->pluck('classrooms.id');
        $studentIds = \App\Models\User::whereHas('classrooms', function ($q) use ($classroomIds) {
            $q->whereIn('classrooms.id', $classroomIds);
        })->where('role', 'student')->pluck('id');

        // الطلاب الذين حصلوا على مكافأة streak
        $streakBonusCount = \App\Models\ActivityUserStreak::whereIn('user_id', $studentIds)
            ->where('bonus_claimed', true)
            ->count();

        // الطلاب في منتصف streak
        $activeStreakCount = \App\Models\ActivityUserStreak::whereIn('user_id', $studentIds)
            ->where('bonus_claimed', false)
            ->where('completed_days', '>', 0)
            ->count();

        return view('teacher.streak-settings', compact('streakSettings', 'streakBonusCount', 'activeStreakCount'));
    }

    /**
     * تحديث إعدادات مكافأة الالتزام
     */
    public function updateStreakSettings(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'min_days' => 'required|integer|min:1|max:30',
            'max_days' => 'required|integer|min:1|max:60',
            'bonus_points' => 'required|integer|min:0|max:500',
        ]);

        $teacher = auth()->user();

        // حفظ الإعدادات
        $settings = [
            'streak_enabled' => $request->has('enabled') ? '1' : '0',
            'streak_min_days' => $validated['min_days'],
            'streak_max_days' => $validated['max_days'],
            'streak_bonus_points' => $validated['bonus_points'],
        ];

        foreach ($settings as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        return redirect()
            ->route('teacher.streak.settings')
            ->with('success', 'تم حفظ إعدادات مكافأة الالتزام بنجاح!');
    }

    /**
     * عرض جميع الأنشطة التي أنشأها المعلم
     */
    public function activities()
    {
        $user = Auth::user();

        // الأنشطة التي أنشأها هذا المعلم
        $activities = Activity::where('created_by', $user->id)
            ->with(['lesson.concept.value', 'classroom', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // إحصائيات
        $stats = [
            'total_activities' => Activity::where('created_by', $user->id)->count(),
            'homework_count' => Activity::where('created_by', $user->id)->where('is_homework', true)->count(),
            'active_count' => Activity::where('created_by', $user->id)->where('status', 'active')->count(),
            'submissions_pending' => ActivitySubmission::whereIn(
                'activity_id',
                Activity::where('created_by', $user->id)->pluck('id'),
            )->where('status', 'pending')->count(),
        ];

        return view('teacher.activities', compact('activities', 'stats'));
    }

    /**
     * عرض صفحة إنشاء نشاط جديد
     */
    public function createActivity()
    {
        $user = Auth::user();

        // الفصول التي يدرسها المعلم
        $classrooms = $user->teachingClassrooms()->get();

        // الدروس المتاحة
        // تقييد الدروس بالقيم المفعّلة لمدرسة المعلم (اتساق مع القيم المفعّلة على مستوى المدرسة)
        $visibleValueIds = \App\Models\Value::visibleForSchool($user->school_id)->pluck('id');
        $lessons = \App\Models\Lesson::with('concept.value')
            ->whereHas('concept', fn ($q) => $q->whereIn('value_id', $visibleValueIds))
            ->orderBy('order')
            ->get();

        return view('teacher.create-activity', compact('classrooms', 'lessons'));
    }

    /**
     * حفظ نشاط جديد
     */
    public function storeActivity(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,exercise,project,creative,upload,practical,discussion,image_order',
            'question_type' => 'nullable|string',
            'questions' => 'nullable|string',
            'points' => 'required|integer|min:1|max:100',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'manual_review' => 'nullable|boolean',
            'requires_parent_approval' => 'nullable|boolean',
            'status' => 'required|in:active,inactive,draft',
            'order' => 'nullable|integer|min:0',
            'quiz_duration' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'in:document,image,video,audio',
            'max_file_size' => 'nullable|integer|min:1|max:100',
            'is_homework' => 'nullable|boolean',
            'due_date' => 'nullable|date',
        ]);

        // إضافة المعلم الحالي كمنشئ + مرحلتا الاعتماد (مدير المدرسة ثم الأدمن)
        $validated['created_by'] = $user->id;
        $validated['is_activity_bank'] = false;
        $validated['school_approval_status'] = 'pending';
        $validated['approval_status'] = 'pending';

        // مفتاح "يتطلب موافقة/تصحيح المعلم يدوياً" (checkbox غير المُرسل = false)
        $validated['manual_review'] = $request->boolean('manual_review');
        $validated['requires_parent_approval'] = $request->boolean('requires_parent_approval');

        // منع إسناد النشاط لفصل لا يدرّسه المعلم (IDOR / تسرب بين المدارس)
        if (! empty($validated['classroom_id'])) {
            abort_unless(
                $user->teachingClassrooms()->whereKey($validated['classroom_id'])->exists(),
                403,
                'هذا الفصل ليس ضمن فصولك',
            );
        }

        // تحويل الأسئلة من JSON string إلى array للحفظ + حارس سلامة الأسئلة
        if (! empty($validated['questions'])) {
            $decoded = json_decode($validated['questions'], true);
            $validated['questions'] = $decoded ?? null;
            $this->validateActivityQuestions($validated['questions']);
        } else {
            unset($validated['questions']); // حذف المفتاح لتجنب تعيينه null
        }

        // allowed_file_types مصبوب array في الموديل فيُشفَّر تلقائياً؛ json_encode اليدويّ
        // كان يُنتج تشفيراً مزدوجاً (يُقرأ نصًّا لا مصفوفة → accept=".pdf") فحُذف.

        // حفظ «الوسائط المتعددة» المرفوعة (فيديو/صوت/صورة/مستند) في عمود media
        $media = $this->collectUploadedActivityMedia($request);
        if (! empty($media)) {
            $validated['media'] = $media;
        }

        // إنشاء النشاط
        $activity = Activity::create($validated);

        // لا نُشعِر طلاب الفصل بعد — النشاط غير معتمد حتى الآن. الإشعار يتمّ عند اعتماد الأدمن النهائي.
        // بدلاً من ذلك نُشعِر مدير/مديري المدرسة بوجود نشاط بانتظار اعتمادهم.
        $this->notifySchoolAdminsOfPendingActivity($user, $activity);

        return redirect()->route('teacher.activities')
            ->with('success', 'تم إرسال النشاط للاعتماد. سيراجعه مدير المدرسة ثم الإدارة قبل ظهوره للطلاب.');
    }

    /**
     * عرض صفحة تعديل نشاط
     */
    public function editActivity($id)
    {
        $user = Auth::user();

        $activity = Activity::where('id', $id)
            ->where('created_by', $user->id)
            ->firstOrFail();

        // الفصول التي يدرسها المعلم
        $classrooms = $user->teachingClassrooms()->get();

        // الدروس المتاحة — مقيّدة بالقيم المفعّلة لمدرسة المعلم
        $visibleValueIds = \App\Models\Value::visibleForSchool($user->school_id)->pluck('id');
        $lessons = \App\Models\Lesson::with('concept.value')
            ->whereHas('concept', fn ($q) => $q->whereIn('value_id', $visibleValueIds))
            ->orderBy('order')
            ->get();

        return view('teacher.edit-activity', compact('activity', 'classrooms', 'lessons'));
    }

    /**
     * معاينة نشاط كما يراه الطالب (زر العين)
     */
    public function previewActivity($id)
    {
        $user = Auth::user();

        // تسريب واجهة عبر الأدوار: السوبر أدمن يتجاوز حارس role:teacher (CheckRole) فيصل هنا،
        // فتظهر له طبقة المعلّم. لكلٍّ صفحة تفاصيل في طبقته: نُحوّل الأدمن/السوبر أدمن لصفحته
        // ومدير المدرسة لصفحته (بحسب الدور النشط) — كي لا يرى واجهة دورٍ آخر.
        $currentRole = method_exists($user, 'getCurrentRole') ? $user->getCurrentRole() : $user->role;
        if (in_array($currentRole, ['super_admin', 'admin'], true)) {
            return redirect()->route('admin.activities.show', $id);
        }
        if ($currentRole === 'school_admin') {
            return redirect()->route('school-admin.activities.show', $id);
        }

        // يسمح بمعاينة: نشاط المعلّم نفسه (أياً كان، بنكاً أو درساً) — للتوافق مع صفحة
        // «إدارة الأنشطة»؛ أو نشاط بنك مشترك معتمد؛ أو نشاط عامّ (بلا منشئ). هذه هي
        // نفس قاعدة رؤية بنك الأنشطة تماماً — لا تسريب (مرئيّة أصلاً في القائمة).
        $activity = Activity::with(['creator', 'lesson.concept.value', 'schools'])->findOrFail($id);

        // بوّابة القراءة الموحّدة (ActivityPolicy@view): يملكه المعلّم أو نشاط بنك معتمَد.
        // 404 (لا 403) للحفاظ على عقد «عدم تسريب وجود النشاط» كما كان الاستعلام المُقيَّد سابقًا.
        abort_unless($user->can('view', $activity), 404);

        // الصفحة الموحّدة (تستبدل teacher.preview-activity المكسورة) — نفس عرض الأدمن + أزرار حسب الدور.
        return view('teacher.activities.show', compact('activity'));
    }

    /**
     * «اختيار من البنك — نسخة»: نسخة قابلة للتعديل من نشاط بنك معتمَد، مُسنَدة لفصل المعلّم،
     * تدخل دورة الاعتماد من جديد. النسخ عبر replicate/save (create مُعفى من حارس النموذج).
     */
    public function cloneFromBank(Request $request, $id)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $source = Activity::findOrFail($id);
        // بوّابة القراءة الموحّدة + قيد «بنك معتمَد فقط» (لا يُنسخ نشاط غير معتمَد أو خاصّ)
        abort_unless($user->can('view', $source), 403);
        abort_unless($source->is_activity_bank && $source->approval_status === 'approved', 403, 'يُنسخ من البنك المعتمَد فقط');
        // عزل صارم (§12.1): لا يُنسخ إلا نشاطٌ عامّ (بلا منشئ) أو متاح في بنك مدرسة المعلّم
        // فعلاً (منشور لكل المدارس أو لمدرسته صراحةً) — يمنع الالتفاف على تقييد «مدارس محدّدة».
        abort_unless(
            $source->created_by === null || ($user->school_id && $source->isAvailableInBankToSchool((int) $user->school_id)),
            403,
            'هذا النشاط غير متاح في بنك مدرستك',
        );

        // منع الإسناد لفصل لا يدرّسه المعلّم (IDOR / تسرّب بين المدارس)
        abort_unless(
            $user->teachingClassrooms()->whereKey($validated['classroom_id'])->exists(),
            403,
            'هذا الفصل ليس ضمن فصولك',
        );

        $clone = $source->replicate([
            'is_featured', 'featured_by', 'featured_at', 'featured_reason',
            'approval_status', 'approved_by', 'approved_at', 'rejection_reason',
            'school_approval_status', 'school_approved_by', 'school_approved_at', 'school_rejection_reason',
            'all_schools_mode',
        ]);
        $clone->created_by = $user->id;
        $clone->is_activity_bank = false;
        $clone->classroom_id = $validated['classroom_id'];
        $clone->title = $source->title . ' (نسخة)';
        $clone->school_approval_status = 'pending';
        $clone->approval_status = 'pending';
        $clone->all_schools_mode = 'none';
        $clone->save();

        $this->notifySchoolAdminsOfPendingActivity($user, $clone);

        return redirect()->route('teacher.activities.edit', $clone->id)
            ->with('success', 'تم إنشاء نسخة قابلة للتعديل. عدّلها ثم ستُرسَل للاعتماد قبل ظهورها للطلاب.');
    }

    /**
     * «اختيار من البنك — مرجع بلا نسخ»: يُسنِد المعلّم نشاط بنك معتمَد لأحد فصوله دون نسخ.
     * طلاب الفصل يرونه عبر بوّابة الرؤية (activity_classroom) — لا يُعدَّل النشاط الأصليّ.
     */
    public function referenceFromBank(Request $request, $id)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'classroom_ids' => 'required|array|min:1',
            'classroom_ids.*' => 'integer|exists:classrooms,id',
        ]);

        $source = Activity::findOrFail($id);
        abort_unless($user->can('view', $source), 403);
        abort_unless($source->is_activity_bank && $source->approval_status === 'approved', 403, 'يُسنَد من البنك المعتمَد فقط');
        // عزل صارم (§12.1): لا يُسنَد إلا نشاطٌ عامّ (بلا منشئ) أو متاح في بنك مدرسة المعلّم
        // فعلاً — يمنع تسريب نشاطٍ قصره الأدمن على مدارس أخرى لطلاب مدرسته عبر activity_classroom.
        abort_unless(
            $source->created_by === null || ($user->school_id && $source->isAvailableInBankToSchool((int) $user->school_id)),
            403,
            'هذا النشاط غير متاح في بنك مدرستك',
        );

        // كل فصل مستهدف يجب أن يكون ضمن فصول المعلّم (IDOR / عزل)
        $ownClassroomIds = $user->teachingClassrooms()->pluck('classrooms.id')->map(fn ($v) => (int) $v)->all();
        $targets = array_values(array_intersect(array_map('intval', $validated['classroom_ids']), $ownClassroomIds));
        abort_if(empty($targets), 403, 'اختر فصلاً من فصولك');

        $payload = [];
        foreach ($targets as $cid) {
            $payload[$cid] = ['assigned_by' => $user->id];
        }
        $source->classrooms()->syncWithoutDetaching($payload);

        return back()->with('success', 'تم إسناد النشاط لفصولك. سيظهر لطلابها مباشرةً.');
    }

    public function updateActivity(Request $request, $id)
    {
        $user = Auth::user();

        $activity = Activity::where('id', $id)
            ->where('created_by', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,exercise,project,creative,upload,practical,discussion,image_order',
            'question_type' => 'nullable|string',
            'questions' => 'nullable|string',
            'points' => 'required|integer|min:1|max:100',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'manual_review' => 'nullable|boolean',
            'requires_parent_approval' => 'nullable|boolean',
            'status' => 'required|in:active,inactive,draft',
            'order' => 'nullable|integer|min:0',
            'quiz_duration' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'in:document,image,video,audio',
            'max_file_size' => 'nullable|integer|min:1|max:100',
            'is_homework' => 'nullable|boolean',
            'due_date' => 'nullable|date',
        ]);

        // مفتاح "يتطلب موافقة/تصحيح المعلم يدوياً" (checkbox غير المُرسل = false)
        $validated['manual_review'] = $request->boolean('manual_review');
        $validated['requires_parent_approval'] = $request->boolean('requires_parent_approval');

        // منع إسناد النشاط لفصل لا يدرّسه المعلم (IDOR)
        if (! empty($validated['classroom_id'])) {
            abort_unless(
                $user->teachingClassrooms()->whereKey($validated['classroom_id'])->exists(),
                403,
                'هذا الفصل ليس ضمن فصولك',
            );
        }

        // حماية questions: إذا أُرسل JSON string يُفسّره، وإذا لم يُرسَل يُبقي كما هو
        if ($request->filled('questions')) {
            $decoded = json_decode($request->input('questions'), true);
            $validated['questions'] = $decoded ?? $activity->questions; // fallback للقديمة
            $this->validateActivityQuestions($validated['questions']); // حارس مفاتيح الإجابة (كالإنشاء)
        } else {
            // لم يُرسل الحقل → لا تمس البيانات الموجودة
            unset($validated['questions']);
        }

        // allowed_file_types مصبوب array في الموديل فيُشفَّر تلقائياً؛ json_encode اليدويّ
        // كان يُنتج تشفيراً مزدوجاً (يُقرأ نصًّا لا مصفوفة → accept=".pdf") فحُذف.

        // الوسائط المتعددة: احذف المحدَّدة للحذف (remove_media[] = مؤشّرات) ثم أضِف المرفوعة الجديدة
        $removeIdx = array_map('intval', (array) $request->input('remove_media', []));
        $newMedia = $this->collectUploadedActivityMedia($request);
        if ($removeIdx || $newMedia) {
            $kept = [];
            foreach (($activity->media ?? []) as $i => $item) {
                if (in_array($i, $removeIdx, true)) {
                    if (! empty($item['path']) && \Storage::disk('public')->exists($item['path'])) {
                        \Storage::disk('public')->delete($item['path']);
                    }

                    continue;
                }
                $kept[] = $item;
            }
            $validated['media'] = array_merge($kept, $newMedia);
        }

        // إلغاء تأشير كل أنواع الملفّات يجب أن يُلغي القيد (يُكتب []) لا أن يكون عملية لاغية صامتة
        // (المربّعات غير المؤشَّرة لا تُرسَل فيغيب المفتاح عن $validated). نفرض القيمة — ولو فارغة —
        // لأنواع الرفع فقط كي لا نمسّ أنواعاً أخرى لا تعرض القسم.
        if (in_array($validated['type'] ?? $activity->type, ['project', 'upload', 'creative', 'practical'], true)) {
            $validated['allowed_file_types'] = array_values((array) $request->input('allowed_file_types', []));
        }

        // تحديث النشاط
        $activity->update($validated);

        // أمن الاعتماد: أيّ تعديل من المعلّم يُعيد النشاط للمرحلة الأولى (اعتماد مدير المدرسة ثم
        // الأدمن) فلا يُنشَر محتوى غير مُراجَع للطلاب — يمنع «غسل الاعتماد» بتعديل نشاط معتمَد.
        // (تجاوز حارس الموديل عبر saveQuietly كما في إعادة الإرسال.)
        $activity->forceFill([
            'school_approval_status' => 'pending',
            'school_approved_by' => null,
            'school_approved_at' => null,
            'school_rejection_reason' => null,
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            // تصفير النشر: لا يبقى محتوى مُعدَّل/مرفوض منشورًا للطلاب (§3 «الفارغ ≠ نشر»)
            'all_schools_mode' => 'none',
        ])->saveQuietly();
        $activity->schools()->detach();      // إزالة نشر المدارس
        $activity->classrooms()->detach();   // وإزالة الإسناد المرجعيّ للفصول (منع غسل الاعتماد بالتعديل)
        $this->notifySchoolAdminsOfPendingActivity($user, $activity);

        return redirect()->route('teacher.activities')
            ->with('success', 'تم تحديث النشاط، وأُعيد للاعتماد (مدير المدرسة ثم الإدارة) قبل ظهوره للطلاب.');
    }

    /**
     * إعادة إرسال نشاط مرفوض للاعتماد — يعيده للمرحلة الأولى (اعتماد مدير المدرسة).
     * يتجاوز حارس الموديل عبر saveQuietly بعد التحقّق من ملكية المعلم.
     */
    public function resubmitActivity($id)
    {
        $user = Auth::user();

        $activity = Activity::where('id', $id)
            ->where('created_by', $user->id)
            ->firstOrFail();

        // يُعاد الإرسال فقط لنشاط مرفوض في إحدى المرحلتين
        $isRejected = $activity->school_approval_status === 'rejected'
            || $activity->approval_status === 'rejected';

        if (! $isRejected) {
            return redirect()->route('teacher.activities')
                ->with('error', 'لا يمكن إعادة إرسال نشاط غير مرفوض.');
        }

        // إعادة الضبط للمرحلة الأولى ومسح أسباب الرفض (تجاوز الحارس عبر saveQuietly)
        $activity->forceFill([
            'school_approval_status' => 'pending',
            'school_approved_by' => null,
            'school_approved_at' => null,
            'school_rejection_reason' => null,
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            // تصفير النشر: لا يبقى محتوى مُعدَّل/مرفوض منشورًا للطلاب (§3 «الفارغ ≠ نشر»)
            'all_schools_mode' => 'none',
        ])->saveQuietly();
        $activity->schools()->detach();      // إزالة نشر المدارس
        $activity->classrooms()->detach();   // وإزالة الإسناد المرجعيّ للفصول (منع غسل الاعتماد بالتعديل)

        // إشعار مدير/مديري المدرسة بإعادة الإرسال
        $this->notifySchoolAdminsOfPendingActivity($user, $activity);

        return redirect()->route('teacher.activities')
            ->with('success', 'تم إعادة إرسال النشاط للاعتماد بنجاح.');
    }

    /**
     * حذف نشاط
     */
    public function deleteActivity($id)
    {
        $user = Auth::user();

        $activity = Activity::where('id', $id)
            ->where('created_by', $user->id)
            ->firstOrFail();

        // حذف المرفق
        if ($activity->attachment && \Storage::disk('public')->exists($activity->attachment)) {
            \Storage::disk('public')->delete($activity->attachment);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف النشاط بنجاح',
        ]);
    }

    /**
     * تصدير تقرير طالب بصيغة PDF
     */
    public function exportStudentReport($studentId)
    {
        $user = Auth::user();
        $student = User::findOrFail($studentId);

        // التحقق من الصلاحية
        $hasAccess = DB::table('classroom_student')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.teacher_id', $user->id)
            ->where('classroom_student.student_id', $student->id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'ليس لديك صلاحية الوصول لهذا الطالب');
        }

        // جمع البيانات
        $stats = [
            'level' => $student->level,
            'total_points' => $student->points()->sum('points'),
            'total_coins' => $student->coins()->sum('coins'),
            'total_badges' => $student->badges()->count(),
        ];

        $recentActivities = ActivitySubmission::where('student_id', $student->id)
            ->with(['activity'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $badges = $student->badges;

        $valueStats = DB::table('activity_submissions')
            ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
            ->join('lessons', 'activities.lesson_id', '=', 'lessons.id')
            ->join('concepts', 'lessons.concept_id', '=', 'concepts.id')
            ->join('values', 'concepts.value_id', '=', 'values.id')
            ->where('activity_submissions.student_id', $student->id)
            ->where('activity_submissions.status', 'completed')
            ->select(
                'values.name as value_name',
                DB::raw('COUNT(*) as activities_count'),
                DB::raw('AVG(activity_submissions.score) as avg_score'),
            )
            ->groupBy('values.id', 'values.name')
            ->get();

        $pdf = \PDF::loadView('reports.student-progress', compact('student', 'stats', 'recentActivities', 'badges', 'valueStats'));

        return $pdf->download('تقرير_' . $student->name . '_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * عرض تقرير الفصل كصفحة HTML
     */
    public function exportClassroomReport($classroomId)
    {
        $user = Auth::user();
        $classroom = Classroom::where('id', $classroomId)
            ->where('teacher_id', $user->id)
            ->with(['teacher', 'students'])
            ->firstOrFail();

        $studentIds = $classroom->students->pluck('id');

        // إحصائيات الفصل
        $classStats = [
            'average_performance' => ActivitySubmission::whereIn('student_id', $studentIds)
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                ->avg('score') ?? 0,
            'completed_activities' => ActivitySubmission::whereIn('student_id', $studentIds)
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                ->count(),
            'pending_activities' => ActivitySubmission::whereIn('student_id', $studentIds)
                ->where('status', 'pending')
                ->count(),
            'completion_rate' => 0,
        ];

        if ($classStats['completed_activities'] + $classStats['pending_activities'] > 0) {
            $classStats['completion_rate'] = ($classStats['completed_activities'] /
                ($classStats['completed_activities'] + $classStats['pending_activities'])) * 100;
        }

        // معلومات الطلاب
        $students = User::whereIn('id', $studentIds)
            ->get()
            ->map(function ($student) {
                $student->completed_activities = ActivitySubmission::where('student_id', $student->id)
                    ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                    ->count();

                $student->average_score = ActivitySubmission::where('student_id', $student->id)
                    ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                    ->avg('score') ?? 0;

                $student->total_points = $student->points()->sum('points');
                $student->total_badges = $student->badges()->count();

                return $student;
            })
            ->sortByDesc('total_points');

        // الأنشطة
        $activities = Activity::where('classroom_id', $classroom->id)
            ->withCount('submissions')
            ->with('submissions')
            ->get()
            ->map(function ($activity) {
                $completedSubmissions = $activity->submissions->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES);
                $activity->average_score = $completedSubmissions->avg('score');

                return $activity;
            });

        return view('teacher.classroom-report', compact('classroom', 'classStats', 'students', 'activities'));
    }

    /**
     * عرض قائمة الفرق
     */
    public function teams()
    {
        $user = Auth::user();

        // الفرق التي فصولها تابعة للمعلم
        $teacherClassroomIds = $user->teachingClassrooms()->pluck('classrooms.id');

        $teams = Team::whereIn('classroom_id', $teacherClassroomIds)
            ->withCount('members')
            ->with(['leader', 'classroom'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('teacher.teams', compact('teams'));
    }

    /**
     * صفحة إنشاء فريق جديد
     */
    public function createTeam()
    {
        $user = Auth::user();

        // الفصول التي يدرسها المعلم
        $classrooms = Classroom::where('teacher_id', $user->id)->get();

        // الطلاب المتاحين (ليسوا في فرق أخرى)
        $students = User::where('role', 'student')
            ->where('school_id', $user->school_id)
            ->whereIn('id', function ($query) use ($user) {
                $query->select('student_id')
                    ->from('classroom_student')
                    ->whereIn('classroom_id', function ($subQuery) use ($user) {
                        $subQuery->select('id')
                            ->from('classrooms')
                            ->where('teacher_id', $user->id);
                    });
            })
            ->whereNotIn('id', function ($q) {
                $q->select('student_id')->from('team_members');
            })
            ->get();

        return view('teacher.create-team', compact('classrooms', 'students'));
    }

    /**
     * حفظ فريق جديد
     */
    public function storeTeam(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classroom_id' => 'required|exists:classrooms,id',
            'leader_id' => 'required|exists:users,id',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:users,id',
            'description' => 'nullable|string|max:500',
        ]);

        // التحقق من أن الفصل للمعلم
        $classroom = Classroom::where('id', $validated['classroom_id'])
            ->where('teacher_id', $user->id)
            ->firstOrFail();

        // التحقق على مستوى الكائن: القائد والأعضاء طلاب في مدرسة المعلم فقط (منع IDOR عبر المدارس)
        $allowed = User::where('school_id', $user->school_id)
            ->where('role', 'student')
            ->whereIn('id', array_merge([$validated['leader_id']], $validated['member_ids']))
            ->pluck('id');

        abort_unless($allowed->contains((int) $validated['leader_id']), 422, 'قائد غير صالح');

        DB::beginTransaction();
        try {
            // إنشاء الفريق
            $team = Team::create([
                'name' => $validated['name'],
                'classroom_id' => $validated['classroom_id'],
                'created_by' => $user->id,
                'description' => $validated['description'],
                'status' => 'active',
            ]);

            // إضافة الأعضاء (مقيّدة بطلاب مدرسة المعلم فقط)
            $memberIds = $allowed->intersect($validated['member_ids'])
                ->push((int) $validated['leader_id'])
                ->unique();

            foreach ($memberIds as $studentId) {
                DB::table('team_members')->insert([
                    'team_id' => $team->id,
                    'student_id' => $studentId,
                    'role' => $studentId == $validated['leader_id'] ? 'leader' : 'member',
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('teacher.teams')->with('success', 'تم إنشاء الفريق بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء إنشاء الفريق')->withInput();
        }
    }

    /**
     * عرض تفاصيل فريق
     */
    public function showTeam($id)
    {
        $user = Auth::user();

        $team = Team::where('id', $id)
            ->whereIn('classroom_id', $user->teachingClassrooms()->pluck('classrooms.id'))
            ->with(['classroom', 'members', 'activities'])
            ->firstOrFail();

        // الحصول على القائد
        $leader = $team->members->filter(function ($member) {
            return $member->pivot->role === 'leader';
        })->first();

        // الأعضاء العاديين
        $members = $team->members->filter(function ($member) {
            return $member->pivot->role === 'member';
        });

        return view('teacher.show-team', compact('team', 'leader', 'members'));
    }

    /**
     * شاشة تعديل فريق
     */
    public function editTeam($id)
    {
        $user = Auth::user();

        $team = Team::where('id', $id)
            ->whereIn('classroom_id', $user->teachingClassrooms()->pluck('classrooms.id'))
            ->with(['classroom', 'members'])
            ->firstOrFail();

        $classrooms = $user->teachingClassrooms()->with('students')->get();

        return view('teacher.edit-team', compact('team', 'classrooms'));
    }

    /**
     * تحديث فريق
     */
    public function updateTeam(Request $request, $id)
    {
        $user = Auth::user();

        $team = Team::where('id', $id)
            ->whereIn('classroom_id', $user->teachingClassrooms()->pluck('classrooms.id'))
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'leader_id' => 'required|exists:users,id',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:users,id',
            'status' => 'nullable|in:active,inactive',
        ]);

        // التحقق على مستوى الكائن: القائد والأعضاء طلاب في مدرسة المعلم فقط (منع IDOR عبر المدارس)
        $allowed = User::where('school_id', $user->school_id)
            ->where('role', 'student')
            ->whereIn('id', array_merge([$validated['leader_id']], $validated['member_ids']))
            ->pluck('id');

        abort_unless($allowed->contains((int) $validated['leader_id']), 422, 'قائد غير صالح');

        DB::beginTransaction();
        try {
            $team->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);

            // إعادة بناء الأعضاء
            DB::table('team_members')->where('team_id', $team->id)->delete();

            // مقيّدة بطلاب مدرسة المعلم فقط (لا تُدرَج أبداً المعرّفات الخام من الطلب)
            $memberIds = $allowed->intersect($validated['member_ids'])
                ->push((int) $validated['leader_id'])
                ->unique();

            foreach ($memberIds as $studentId) {
                DB::table('team_members')->insert([
                    'team_id' => $team->id,
                    'student_id' => $studentId,
                    'role' => $studentId == $validated['leader_id'] ? 'leader' : 'member',
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('teacher.teams.show', $team->id)
                ->with('success', 'تم تحديث الفريق بنجاح');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Team update failed: ' . $e->getMessage());

            return back()->with('error', 'حدث خطأ أثناء تحديث الفريق')->withInput();
        }
    }

    /**
     * حذف فريق (alias لاسم الـ route teams.destroy).
     */
    public function destroyTeam($id)
    {
        return $this->deleteTeam($id);
    }

    /**
     * حذف فريق
     */
    public function deleteTeam($id)
    {
        $user = Auth::user();

        $team = Team::where('id', $id)
            ->whereIn('classroom_id', $user->teachingClassrooms()->pluck('classrooms.id'))
            ->firstOrFail();

        // التحقق من الصلاحية
        $hasAccess = Classroom::where('id', $team->classroom_id)
            ->where('teacher_id', $user->id)
            ->exists();

        if (! $hasAccess) {
            return response()->json(['error' => 'ليس لديك صلاحية'], 403);
        }

        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الفريق بنجاح',
        ]);
    }

    /**
     * تعيين نشاط للفريق
     */
    public function assignTeamActivity(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'activity_id' => 'required|exists:activities,id',
        ]);

        // التحقق من صلاحية النشاط
        $activity = Activity::where('id', $validated['activity_id'])
            ->where('created_by', $user->id)
            ->where('is_team_activity', true)
            ->firstOrFail();

        // التحقق من الفريق
        $team = Team::where('id', $validated['team_id'])
            ->whereIn('classroom_id', $user->teachingClassrooms()->pluck('classrooms.id'))
            ->firstOrFail();

        // التحقق من عدم تكرار التعيين
        $exists = TeamActivity::where('team_id', $team->id)
            ->where('activity_id', $activity->id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'النشاط معين مسبقاً لهذا الفريق'], 400);
        }

        // إنشاء التعيين (assigned_by مطلوب NOT NULL، و'assigned' قيمة enum صحيحة)
        TeamActivity::create([
            'team_id' => $team->id,
            'activity_id' => $activity->id,
            'assigned_by' => $user->id,
            'status' => 'assigned',
        ]);

        // إرسال إشعارات لأعضاء الفريق
        foreach ($team->members as $member) {
            NotificationService::create(
                $member->id,
                'team_activity_assigned',
                '🤝 نشاط فريق جديد',
                "تم تعيين نشاط '{$activity->title}' لفريقك {$team->name}",
                "/student/teams/{$team->id}",
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تعيين النشاط للفريق بنجاح',
        ]);
    }

    /**
     * تقييم نشاط فريق
     */
    public function gradeTeamActivity(Request $request, $id)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'total_score' => 'required|integer|min:0|max:100',
            'teacher_feedback' => 'nullable|string|max:1000',
        ]);

        $teamActivity = TeamActivity::with(['team.members', 'activity'])
            ->findOrFail($id);

        // التحقق من الصلاحية
        if ($teamActivity->activity->created_by != $user->id) {
            return response()->json(['error' => 'ليس لديك صلاحية'], 403);
        }

        // حماية ضد التكرار: لا نمنح النقاط/العملات مرتين لو سبق تقييم النشاط (idempotency)
        if ($teamActivity->status === 'completed') {
            return response()->json(['error' => 'تم تقييم هذا النشاط مسبقاً'], 409);
        }

        // تحديث التقييم
        $teamActivity->update([
            'status' => 'completed',
            'total_score' => $validated['total_score'],
            'teacher_feedback' => $validated['teacher_feedback'],
            'submitted_at' => $teamActivity->submitted_at ?? now(),
        ]);

        // منح نقاط لكل عضو في الفريق
        $pointsPerMember = (int) floor($validated['total_score'] / 2); // نصف الدرجة كنقاط
        $coinsPerMember = (int) floor($validated['total_score'] / 4); // ربع الدرجة كعملات

        // كل عضو فريق محاط بـ try-catch — فشل عضو واحد لا يكسر تقييم البقية (P1-D)
        $activityTitle = optional($teamActivity->activity)->title ?? 'نشاط';
        foreach ($teamActivity->team->members as $member) {
            // مفتاح idempotency = (team_activity_id, member_user_id): إعادة تقييم نفس النشاط = لا شيء،
            // لكن كل عضو يُدفع له مرة واحدة بالضبط؛ نشاط فريق جديد (id مختلف) يدفع من جديد.
            try {
                AwardService::award(
                    $member->id,
                    'team_activity',
                    $teamActivity->id . ':' . $member->id,
                    $pointsPerMember,
                    $coinsPerMember,
                    "إكمال نشاط فريق: {$activityTitle}",
                );
            } catch (\Throwable $e) {
                \Log::warning("team award failed for member {$member->id}: " . $e->getMessage());
            }

            try {
                NotificationService::create(
                    $member->id,
                    'team_activity_graded',
                    '⭐ تم تقييم نشاط الفريق',
                    "حصل فريقك على {$validated['total_score']} نقطة في نشاط '{$activityTitle}'",
                    "/student/teams/{$teamActivity->team_id}",
                );
            } catch (\Throwable $e) {
                \Log::warning("team notification failed for member {$member->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تقييم النشاط وتوزيع المكافآت على الأعضاء',
        ]);
    }

    /**
     * عرض صفحة المراسلات مع أولياء الأمور
     */
    public function messages()
    {
        $user = Auth::user();

        // جلب قائمة أولياء الأمور الذين لديهم أطفال في فصول المعلم + أبناؤهم (لتعبئة قائمة الطالب)
        $teachingIds = $user->teachingClassrooms()->pluck('id');
        $parents = User::where('role', 'parent')
            ->where('school_id', $user->school_id)
            ->whereHas('children.classrooms', function ($q) use ($teachingIds) {
                $q->whereIn('classroom_id', $teachingIds);
            })
            ->with(['children' => function ($q) use ($teachingIds) {
                $q->whereHas('classrooms', fn ($c) => $c->whereIn('classroom_id', $teachingIds))
                    ->select('users.id', 'users.name');
            }])
            ->select('id', 'name', 'email')
            ->get();

        // محادثة واحدة لكل (ولي أمر، طالب): نأخذ آخر رسالة فقط بدل صف لكل رسالة
        $latestIds = \App\Models\ParentTeacherMessage::where('teacher_id', $user->id)
            ->selectRaw('MAX(id) as id')
            ->groupBy('parent_id', 'student_id')
            ->pluck('id');

        $conversations = \App\Models\ParentTeacherMessage::whereIn('id', $latestIds)
            ->with(['parent:id,name', 'student:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('teacher.messages', compact('parents', 'conversations'));
    }

    /**
     * عرض محادثة محددة مع ولي أمر
     */
    public function getConversation(Request $request)
    {
        $user = Auth::user();

        $messages = \App\Models\ParentTeacherMessage::where(function ($q) use ($user, $request) {
            $q->where('teacher_id', $user->id)
                ->where('parent_id', $request->parent_id);
        })
            ->when($request->student_id, function ($q) use ($request) {
                $q->where('student_id', $request->student_id);
            })
            ->with(['parent:id,name', 'student:id,name'])
            ->orderBy('created_at', 'asc')
            ->get();

        // تحديد الرسائل كمقروءة
        \App\Models\ParentTeacherMessage::where('teacher_id', $user->id)
            ->where('parent_id', $request->parent_id)
            ->where('sender_type', 'parent')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json($messages);
    }

    /**
     * إرسال رسالة جديدة لولي أمر
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:users,id',
            'student_id' => 'nullable|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        // التحقق من أن ولي الأمر لديه طفل في فصول المعلم
        $parentValid = User::where('id', $request->parent_id)
            ->where('role', 'parent')
            ->where('school_id', $user->school_id)
            ->whereHas('children.classrooms', function ($q) use ($user) {
                $q->whereIn('classroom_id', $user->teachingClassrooms()->pluck('id'));
            })
            ->exists();

        if (! $parentValid) {
            return response()->json(['error' => 'ولي الأمر غير صالح'], 403);
        }

        $message = \App\Models\ParentTeacherMessage::create([
            'parent_id' => $request->parent_id,
            'teacher_id' => $user->id,
            'student_id' => $request->student_id,
            'message' => $request->message,
            'sender_type' => 'teacher',
        ]);

        // إرسال إشعار لولي الأمر — [] لموضع data والرابط في موضع actionUrl السادس
        \App\Services\NotificationService::create(
            $request->parent_id,
            'new_teacher_message',
            '💬 رسالة جديدة من المعلم',
            "لديك رسالة جديدة من {$user->name}",
            [],
            route('parent.messages'),
        );

        return response()->json([
            'success' => true,
            'message' => $message->load(['parent:id,name', 'student:id,name']),
        ]);
    }

    /**
     * عرض صفحة التقييمات من الطلاب
     */
    public function ratings()
    {
        $user = Auth::user();

        // جلب التقييمات مع تفاصيل الطلاب
        $ratings = \App\Models\TeacherRating::where('teacher_id', $user->id)
            ->with(['student:id,name'])
            ->latest()
            ->paginate(20);

        // حساب المتوسط
        $averageRating = \App\Models\TeacherRating::where('teacher_id', $user->id)
            ->avg('rating');

        // عدد التقييمات لكل نجمة
        $ratingDistribution = \App\Models\TeacherRating::where('teacher_id', $user->id)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        return view('teacher.ratings', compact('ratings', 'averageRating', 'ratingDistribution'));
    }

    /**
     * عرض صفحة التحليلات والإحصائيات
     */
    public function analytics()
    {
        $user = Auth::user();

        // جلب IDs الطلاب في فصول المعلم
        $studentIds = DB::table('classroom_student')
            ->whereIn('classroom_id', $user->teachingClassrooms()->pluck('id'))
            ->distinct()
            ->pluck('student_id')
            ->toArray();

        // إحصائيات الأنشطة
        $activityStats = ActivitySubmission::whereIn('student_id', $studentIds)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('completed','approved') THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status IN ('pending','needs_review') THEN 1 ELSE 0 END) as pending,
                AVG(CASE WHEN score IS NOT NULL THEN score END) as avg_score
            ")
            ->first();

        // بيانات التسليمات - آخر 30 يوم
        $submissionsData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $submissionsData[] = [
                'date' => $date->format('d/m'),
                'count' => ActivitySubmission::whereIn('student_id', $studentIds)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count(),
            ];
        }

        // أفضل 10 طلاب (حسب النقاط)
        $topStudents = User::whereIn('id', $studentIds)
            ->withSum('points as total_points', 'points')
            ->orderBy('total_points', 'desc')
            ->limit(10)
            ->get();

        // توزيع الدرجات
        $gradeDistribution = ActivitySubmission::whereIn('student_id', $studentIds)
            ->whereNotNull('score')
            ->selectRaw('
                SUM(CASE WHEN score >= 90 THEN 1 ELSE 0 END) as excellent,
                SUM(CASE WHEN score >= 80 AND score < 90 THEN 1 ELSE 0 END) as very_good,
                SUM(CASE WHEN score >= 70 AND score < 80 THEN 1 ELSE 0 END) as good,
                SUM(CASE WHEN score >= 60 AND score < 70 THEN 1 ELSE 0 END) as acceptable,
                SUM(CASE WHEN score < 60 THEN 1 ELSE 0 END) as weak
            ')
            ->first();

        // الأنشطة الأكثر تفاعلاً — متوسط الدرجة يستبعد الـ rejected و النصوص الفارغة
        $topActivities = Activity::where('created_by', $user->id)
            ->withCount(['submissions as submissions_count'])
            ->withAvg(['submissions as avg_score' => function ($q) {
                $q->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                    ->whereNotNull('score');
            }], 'score')
            ->orderBy('submissions_count', 'desc')
            ->limit(5)
            ->get();

        // معدل التفاعل الأسبوعي - آخر 8 أسابيع
        $weeklyEngagement = [];
        for ($i = 7; $i >= 0; $i--) {
            $startDate = now()->subWeeks($i)->startOfWeek();
            $endDate = now()->subWeeks($i)->endOfWeek();
            $weeklyEngagement[] = [
                'week' => $startDate->format('d/m'),
                'count' => ActivitySubmission::whereIn('student_id', $studentIds)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                    ->count(),
            ];
        }

        return view('teacher.analytics', compact(
            'activityStats',
            'submissionsData',
            'topStudents',
            'gradeDistribution',
            'topActivities',
            'weeklyEngagement',
        ));
    }

    /**
     * إضافة نشاط إلى بنك الأنشطة — يمرّ بمرحلتَي اعتماد (مدير المدرسة ثم الأدمن).
     */
    public function addActivityToBank(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'lesson_id' => 'nullable|exists:lessons,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,exercise,project,creative,image_order,upload,practical,discussion',
            'questions' => 'nullable|string',
            'points' => 'required|integer|min:1|max:100',
            'bonus_points' => 'nullable|integer|min:0|max:50',
            'is_creative' => 'boolean',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'manual_review' => 'nullable|boolean',
            'requires_parent_approval' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'quiz_duration' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'in:document,image,video,audio',
            'max_file_size' => 'nullable|integer|min:1|max:100',
            'status' => 'required|in:active,inactive,draft',
        ]);

        // إضافة المعلم الحالي كمنشئ + مرحلتا الاعتماد (مدير المدرسة ثم الأدمن)
        $validated['created_by'] = $user->id;
        $validated['is_activity_bank'] = true;
        $validated['school_approval_status'] = 'pending';
        $validated['approval_status'] = 'pending';

        // مفتاح "يتطلب موافقة/تصحيح المعلم يدوياً" (checkbox غير المُرسل = false)
        $validated['manual_review'] = $request->boolean('manual_review');
        $validated['requires_parent_approval'] = $request->boolean('requires_parent_approval');

        // منع إسناد النشاط لفصل لا يدرّسه المعلم (IDOR / تسرب بين المدارس)
        if (! empty($validated['classroom_id'])) {
            abort_unless(
                $user->teachingClassrooms()->whereKey($validated['classroom_id'])->exists(),
                403,
                'هذا الفصل ليس ضمن فصولك',
            );
        }

        // إذا كان نشاط إبداعي، يجب أن يكون لكل الفصل
        if ($validated['is_creative'] ?? false) {
            if (! ($validated['classroom_id'] ?? null)) {
                return redirect()->back()->with('error', 'يجب تحديد الفصل للنشاط الإبداعي')->withInput();
            }
            $validated['bonus_points'] = $validated['bonus_points'] ?? 10;
        }

        // تحويل الأسئلة من JSON string إلى array للحفظ + حارس سلامة الأسئلة
        if (! empty($validated['questions'])) {
            $decoded = json_decode($validated['questions'], true);
            $validated['questions'] = $decoded ?? null;
            $this->validateActivityQuestions($validated['questions']);
        } else {
            unset($validated['questions']);
        }

        // allowed_file_types مصبوب array في الموديل فيُشفَّر تلقائياً؛ json_encode اليدويّ
        // كان يُنتج تشفيراً مزدوجاً (يُقرأ نصًّا لا مصفوفة → accept=".pdf") فحُذف.

        // حفظ «الوسائط المتعددة» المرفوعة (فيديو/صوت/صورة/مستند) في عمود media
        $media = $this->collectUploadedActivityMedia($request);
        if (! empty($media)) {
            $validated['media'] = $media;
        }

        // إنشاء النشاط
        $activity = Activity::create($validated);

        // إشعار مدير/مديري المدرسة بوجود نشاط بانتظار اعتمادهم
        $this->notifySchoolAdminsOfPendingActivity($user, $activity);

        // تحديث نقاط المعلم
        TeacherPoint::updateTeacherPoints($user->id);

        return redirect()->route('teacher.activity-bank.index')->with('success', 'تم إرسال النشاط للاعتماد. سيراجعه مدير المدرسة ثم الإدارة قبل ظهوره للطلاب.');
    }

    /**
     * يجمع كل ملفّات «الوسائط المتعددة» المرفوعة (فيديو/صوت/صورة/مستند) عبر مدخلات النموذج —
     * يقبل المدخل المفرد أو المتعدّد (name="video" أو name="video[]") — ويخزّنها في القرص العامّ.
     * يُرجِع مصفوفة [{type, path, name}]. مدخل attachment العامّ (نموذج التعديل) يُستنتَج نوعه.
     */
    private function collectUploadedActivityMedia(Request $request): array
    {
        $specs = [
            // mimes: (بالامتداد ↔ خريطة MIME) لا mimetypes: (MIME خام صارم) — كثير من ملفّات
            // الفيديو/الصوت السليمة يُكتشَف MIMEها مختلفاً فتُرفَض بصمت. mimes أوسع وأمتن للرفع.
            'video' => ['rule' => 'mimes:mp4,m4v,mov,avi,webm,mkv,ogv,3gp,3g2,mpeg,mpg', 'max' => 102400, 'type' => 'video'],
            'image' => ['rule' => 'image', 'max' => 10240, 'type' => 'image'],
            'audio' => ['rule' => 'mimes:mp3,wav,ogg,oga,m4a,aac,weba,opus,mpga', 'max' => 20480, 'type' => 'audio'],
            'document' => ['rule' => 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', 'max' => 20480, 'type' => 'document'],
            // مدخل عامّ (نموذج التعديل name="attachment[]") — يُستنتَج النوع من الامتداد
            'attachment' => ['rule' => 'mimes:mp4,mov,avi,webm,m4v,mp3,wav,ogg,m4a,aac,jpg,jpeg,png,gif,webp,pdf,doc,docx,ppt,pptx,xls,xlsx', 'max' => 102400, 'type' => null],
        ];

        $media = [];
        foreach ($specs as $field => $spec) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $files = $request->file($field);
            $isMulti = is_array($files);

            // تحقّق يدعم المفرد والمتعدّد
            $request->validate([
                ($isMulti ? "{$field}.*" : $field) => "file|{$spec['rule']}|max:{$spec['max']}",
            ]);

            foreach (($isMulti ? $files : [$files]) as $file) {
                $media[] = [
                    'type' => $spec['type'] ?? $this->guessMediaType($file->getClientOriginalExtension()),
                    'path' => $file->store('activity-media', 'public'),
                    'name' => $file->getClientOriginalName(),
                ];
            }
        }

        return $media;
    }

    /**
     * يستنتج نوع الوسيط من امتداد الملفّ (للمدخل العامّ attachment).
     */
    private function guessMediaType(string $ext): string
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['mp4', 'mov', 'avi', 'webm', 'm4v', 'ogv'], true)) {
            return 'video';
        }
        if (in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac'], true)) {
            return 'audio';
        }
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
            return 'image';
        }

        return 'document';
    }

    /**
     * حارس خادمي لسلامة الأسئلة — يمنع تخزين نشاط بلا مفتاح إجابة صالح
     * (مطابق لحارس لوحة المشرف: الإجابة القصيرة واختيار الحروف).
     */
    private function validateActivityQuestions($questions): void
    {
        if (! is_array($questions)) {
            return;
        }

        foreach ($questions as $i => $q) {
            if (! is_array($q)) {
                continue;
            }
            $type = $q['type'] ?? $q['question_type'] ?? null;
            $n = $i + 1;

            if ($type === 'short_answer') {
                $answer = trim((string) ($q['correct_answer'] ?? $q['answer'] ?? ''));
                if ($answer === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'questions' => "السؤال رقم {$n}: يجب إدخال الإجابة الصحيحة لسؤال الإجابة القصيرة.",
                    ]);
                }
            }

            if ($type === 'letter_choice') {
                $word = trim((string) ($q['word'] ?? $q['target_word'] ?? ''));
                $letters = is_array($q['options'] ?? null)
                    ? array_filter(array_map(
                        fn ($o) => trim((string) (is_array($o) ? ($o['text'] ?? $o['label'] ?? '') : $o)),
                        $q['options'],
                    ))
                    : [];
                if ($word === '' && empty($letters)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'questions' => "السؤال رقم {$n}: أدخل حروف الكلمة لسؤال اختيار الحروف.",
                    ]);
                }
            }
        }
    }

    /**
     * إشعار مدير/مديري مدرسة المعلم بنشاط بانتظار اعتمادهم.
     */
    private function notifySchoolAdminsOfPendingActivity(User $teacher, Activity $activity): void
    {
        if (! $teacher->school_id) {
            return;
        }

        $admins = User::where('school_id', $teacher->school_id)
            ->where('role', 'school_admin')
            ->pluck('id');

        foreach ($admins as $adminId) {
            NotificationService::send(
                $adminId,
                '📝 نشاط بانتظار اعتمادك',
                "أرسل المعلم {$teacher->name} النشاط \"{$activity->title}\" لاعتماده.",
                'activity_pending',
                route('school-admin.activity-approvals'),
            );
        }
    }

    // addQuestionToBank أُزيل (المرحلة 5): لم يعد المعلّم يُنشئ أسئلة بنك؛ إدارة الأسئلة للأدمن.

    /**
     * لوحة صدارة المعلمين (محلي ودولي)
     */
    public function teacherLeaderboard(Request $request)
    {
        $scope = $request->get('scope', 'local'); // local أو global

        $query = TeacherPoint::with('teacher.school')
            ->whereHas('teacher', function ($q) {
                $q->where('role', 'teacher');
            });

        if ($scope === 'local') {
            // محلي: حسب المدرسة
            $user = Auth::user();
            if ($user->school_id) {
                $query->whereHas('teacher', function ($q) use ($user) {
                    $q->where('school_id', $user->school_id);
                });
            }
        }
        // global: جميع المعلمين

        $leaders = $query->orderBy('points', 'desc')
            ->paginate(50);

        // ترتيب المعلم الحالي
        $currentTeacher = null;
        $currentTeacherRank = null;
        if ($scope === 'local' && Auth::user()->school_id) {
            $currentTeacher = TeacherPoint::where('teacher_id', Auth::id())->first();
            if ($currentTeacher) {
                $currentTeacherRank = TeacherPoint::whereHas('teacher', function ($q) {
                    $q->where('role', 'teacher')
                        ->where('school_id', Auth::user()->school_id);
                })
                    ->where('points', '>', $currentTeacher->points)
                    ->count() + 1;
            }
        } elseif ($scope === 'global') {
            $currentTeacher = TeacherPoint::where('teacher_id', Auth::id())->first();
            if ($currentTeacher) {
                $currentTeacherRank = TeacherPoint::where('points', '>', $currentTeacher->points)
                    ->count() + 1;
            }
        }

        return view('teacher.leaderboard', compact('leaders', 'scope', 'currentTeacher', 'currentTeacherRank'));
    }

    /**
     * لوحة صدارة الطلاب
     */
    public function studentLeaderboard(Request $request)
    {
        $scope = $request->get('scope', 'classroom'); // classroom, school, city, country

        $user = Auth::user();

        // لكل scope نبدأ من قاعدة منفصلة بدلًا من تقاطع فصول المعلم فقط
        $query = User::where('role', 'student')
            ->where('status', 'active')
            ->withSum('points as total_points', 'points')
            ->with(['school', 'classrooms']);

        if ($scope === 'classroom') {
            $classroomIds = $user->teachingClassrooms()->pluck('id');
            $studentIds = DB::table('classroom_student')
                ->whereIn('classroom_id', $classroomIds)
                ->where('status', 'active')
                ->distinct()
                ->pluck('student_id');
            $query->whereIn('id', $studentIds);
        } elseif ($scope === 'school') {
            // كل طلاب المدرسة (وليس فصول المعلم فقط)
            $query->where('school_id', $user->school_id);
        } elseif ($scope === 'city' && $user->school && $user->school->city) {
            $query->whereHas('school', function ($q) use ($user) {
                $q->where('city', $user->school->city);
            });
        } elseif ($scope === 'country' && $user->school && $user->school->country) {
            $query->whereHas('school', function ($q) use ($user) {
                $q->where('country', $user->school->country);
            });
        } else {
            // fallback: classroom scope
            $classroomIds = $user->teachingClassrooms()->pluck('id');
            $studentIds = DB::table('classroom_student')
                ->whereIn('classroom_id', $classroomIds)
                ->where('status', 'active')
                ->distinct()
                ->pluck('student_id');
            $query->whereIn('id', $studentIds);
        }

        $leaders = $query->orderBy('total_points', 'desc')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view('teacher.student-leaderboard', compact('leaders', 'scope'));
    }

    /**
     * بنك الأنشطة (موحد: أنشطة + أسئلة)
     */
    public function activityBank()
    {
        $user = Auth::user();

        // الأنشطة في بنك الأنشطة
        $schoolId = (int) ($user->school_id ?? 0);
        $activities = Activity::where('is_activity_bank', true)
            ->where(function ($q) use ($user, $schoolId) {
                $q->where('created_by', $user->id) // أنشطة المعلم
                    ->orWhereNull('created_by')     // الأنشطة العامة (بلا منشئ)
                    // نشاط معلّمٍ آخر معتمَد: يظهر فقط إن كان متاحًا في بنك مدرسة هذا المعلّم
                    // (منشور لكل المدارس bank/direct، أو له صفّ activity_school لمدرسته) —
                    // يمنع تسريب نشاطٍ قصره الأدمن على مدارس أخرى (§12.1). مطابق لـisAvailableInBankToSchool.
                    ->orWhere(function ($subQ) use ($schoolId) {
                        $subQ->where('approval_status', 'approved')
                            ->whereNotNull('created_by')
                            ->where(function ($avail) use ($schoolId) {
                                $avail->whereIn('all_schools_mode', ['bank', 'direct']);
                                if ($schoolId) {
                                    $avail->orWhereIn('id', function ($sub) use ($schoolId) {
                                        $sub->select('activity_id')
                                            ->from('activity_school')
                                            ->where('school_id', $schoolId);
                                    });
                                }
                            });
                    });
            })
            ->with(['creator', 'lesson.concept.value', 'classroom', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // إحصائيات الأنشطة
        $stats = [
            'total' => Activity::where('is_activity_bank', true)->where('created_by', $user->id)->count(),
            'pending' => Activity::where('is_activity_bank', true)->where('created_by', $user->id)->where('approval_status', 'pending')->count(),
            'approved' => Activity::where('is_activity_bank', true)->where('created_by', $user->id)->where('approval_status', 'approved')->count(),
            'rejected' => Activity::where('is_activity_bank', true)->where('created_by', $user->id)->where('approval_status', 'rejected')->count(),
            'shared_activities' => Activity::where('is_activity_bank', true)->where('approval_status', 'approved')->whereNotNull('created_by')->where('created_by', '!=', $user->id)->count(),
        ];

        // تبويب أسئلة المعلّم أُزيل (المرحلة 5) — لا حاجة لجلب $questions/$questionStats.

        // فصول المعلّم لمُنتقيات «استخدام من البنك» (نسخة/مرجع)
        $classrooms = $user->teachingClassrooms()->get();

        return view('teacher.activity-bank', compact('activities', 'stats', 'classrooms'));
    }

    // createQuestion + questionBank أُزيلا (المرحلة 5): واجهة بنك أسئلة المعلّم مُزالة، والبيانات باقية.

    // ==================== الأنشطة المميزة ====================

    /**
     * تمييز **تسليم طالبٍ متميّز** (#22 — على مستوى التسليم لا تعريف النشاط): يميّز المعلّم
     * عملَ أحد طلّابه المتميّز فتستعرضه الإدارة ضمن «الأنشطة المميّزة» للتقارير وتكريم الطلاب.
     */
    public function featureSubmission(Request $request, $submissionId)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $submission = ActivitySubmission::findOrFail($submissionId);

        // للمعلّم الذي يراجع التسليم فقط (طالبه في أحد فصوله)
        if (! $this->teacherReviewsSubmission($submission, $user)) {
            return back()->with('error', 'لا يمكنك تمييز هذا التسليم');
        }

        // is_featured/featured_* ليست ضمن حقول التسليم المحروسة (booted يحرس score/status/…) —
        // فالتحديث العاديّ يمرّ (لا يمسّ حقلاً حسّاسًا).
        $submission->update([
            'is_featured' => true,
            'featured_by' => $user->id,
            'featured_at' => now(),
            'featured_reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'تم تمييز تسليم الطالب وسيظهر ضمن التسليمات المميّزة لدى الإدارة');
    }

    /**
     * إلغاء تمييز تسليم — لمن ميّزه أو لمن يراجعه (طالبه في فصله).
     */
    public function unfeatureSubmission($submissionId)
    {
        $user = Auth::user();
        $submission = ActivitySubmission::findOrFail($submissionId);

        if ((int) $submission->featured_by !== (int) $user->id && ! $this->teacherReviewsSubmission($submission, $user)) {
            return back()->with('error', 'لا يمكنك إلغاء تمييز هذا التسليم');
        }

        $submission->update([
            'is_featured' => false,
            'featured_by' => null,
            'featured_at' => null,
            'featured_reason' => null,
        ]);

        return back()->with('success', 'تم إلغاء تمييز التسليم');
    }

    /**
     * هل يراجع هذا المعلّم هذا التسليم؟ (طالب التسليم عضوٌ في أحد فصول المعلّم).
     */
    private function teacherReviewsSubmission(ActivitySubmission $submission, User $user): bool
    {
        return DB::table('classroom_student')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.teacher_id', $user->id)
            ->where('classroom_student.student_id', $submission->student_id)
            ->exists();
    }

    // ==================== نظام التمارين ====================

    /**
     * عرض قائمة التمارين
     */
    public function practiceExercises()
    {
        $user = Auth::user();

        $exercises = \App\Models\PracticeExercise::where('teacher_id', $user->id)
            ->withCount('attempts')
            ->with('classroom')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => \App\Models\PracticeExercise::where('teacher_id', $user->id)->count(),
            'active' => \App\Models\PracticeExercise::where('teacher_id', $user->id)->where('is_active', true)->count(),
            'total_attempts' => \App\Models\PracticeAttempt::whereHas('exercise', fn ($q) => $q->where('teacher_id', $user->id))->count(),
        ];

        return view('teacher.practice-exercises', compact('exercises', 'stats'));
    }

    /**
     * صفحة إنشاء تمرين جديد
     */
    public function createExercise()
    {
        $user = Auth::user();

        $classrooms = Classroom::where('teacher_id', $user->id)->get();
        // منتقي أسئلة التمرين من الأسئلة المعتمَدة فقط (المرحلة 5 — المعلّم لا يُنشئ أسئلة)
        $questions = QuestionBank::where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('teacher.create-exercise', compact('classrooms', 'questions'));
    }

    /**
     * حفظ تمرين جديد
     */
    public function storeExercise(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:quiz,review,challenge',
            'difficulty' => 'required|in:easy,medium,hard',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'time_limit' => 'nullable|integer|min:1|max:120',
            'max_attempts' => 'required|integer|min:1|max:10',
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'exists:question_bank,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        \App\Models\PracticeExercise::create([
            'teacher_id' => $user->id,
            'classroom_id' => $validated['classroom_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'difficulty' => $validated['difficulty'],
            'time_limit' => $validated['time_limit'] ?? null,
            'max_attempts' => $validated['max_attempts'],
            'questions' => $validated['question_ids'],
            'is_active' => true,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return redirect()->route('teacher.exercises')->with('success', 'تم إنشاء التمرين بنجاح ✅');
    }

    /**
     * تعديل تمرين
     */
    public function editExercise($id)
    {
        $user = Auth::user();

        $exercise = \App\Models\PracticeExercise::where('teacher_id', $user->id)->findOrFail($id);
        $classrooms = Classroom::where('teacher_id', $user->id)->get();
        // المعتمَدة + أيّ أسئلة مخزَّنة سلفًا في هذا التمرين (وإن لم تعد معتمَدة) كي لا يُسقِطها
        // التعديل صامتًا عند الحفظ (المرحلة 5 — المعلّم لا يُنشئ أسئلة جديدة).
        $questions = QuestionBank::where('status', 'approved')
            ->orWhereIn('id', (array) ($exercise->questions ?? []))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('teacher.create-exercise', compact('exercise', 'classrooms', 'questions'));
    }

    /**
     * تحديث تمرين
     */
    public function updateExercise(Request $request, $id)
    {
        $user = Auth::user();

        $exercise = \App\Models\PracticeExercise::where('teacher_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:quiz,review,challenge',
            'difficulty' => 'required|in:easy,medium,hard',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'time_limit' => 'nullable|integer|min:1|max:120',
            'max_attempts' => 'required|integer|min:1|max:10',
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'exists:question_bank,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);

        $exercise->update([
            'classroom_id' => $validated['classroom_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'difficulty' => $validated['difficulty'],
            'time_limit' => $validated['time_limit'] ?? null,
            'max_attempts' => $validated['max_attempts'],
            'questions' => $validated['question_ids'],
            'is_active' => $validated['is_active'] ?? true,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return redirect()->route('teacher.exercises')->with('success', 'تم تحديث التمرين بنجاح ✅');
    }

    /**
     * حذف تمرين
     */
    public function deleteExercise($id)
    {
        $user = Auth::user();
        $exercise = \App\Models\PracticeExercise::where('teacher_id', $user->id)->findOrFail($id);
        $exercise->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف التمرين بنجاح']);
    }

    /**
     * نتائج تمرين
     */
    public function exerciseResults($id)
    {
        $user = Auth::user();

        $exercise = \App\Models\PracticeExercise::where('teacher_id', $user->id)
            ->withCount('attempts')
            ->findOrFail($id);

        $attempts = \App\Models\PracticeAttempt::where('exercise_id', $id)
            ->with('student')
            ->whereNotNull('completed_at')
            ->orderBy('score', 'desc')
            ->get();

        $stats = [
            'total_attempts' => $attempts->count(),
            'avg_score' => round($attempts->avg('score') ?? 0, 1),
            'avg_time' => round($attempts->avg('time_taken') ?? 0),
            'highest_score' => $attempts->max('score') ?? 0,
            'pass_rate' => $attempts->count() > 0
                ? round($attempts->where('score', '>=', 60)->count() / $attempts->count() * 100)
                : 0,
        ];

        return view('teacher.exercise-results', compact('exercise', 'attempts', 'stats'));
    }

    /**
     * تفاعل أولياء الأمور مع طلاب المعلم (Issue 14/file2 + 109).
     * يعرض: عدد المدح، الهدايا، الرسائل، آخر تفاعل، نقاط ولي الأمر.
     */
    public function parentEngagement()
    {
        $teacher = Auth::user();

        // طلاب فصول المعلم
        $studentIds = DB::table('classroom_student')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.teacher_id', $teacher->id)
            ->where('classroom_student.status', 'active')
            ->pluck('classroom_student.student_id')
            ->unique();

        // أولياء أمور هؤلاء الطلاب
        $parents = User::where('role', 'parent')
            ->whereExists(function ($q) use ($studentIds) {
                $q->select(DB::raw(1))
                    ->from('parent_student')
                    ->whereColumn('parent_student.parent_id', 'users.id')
                    ->whereIn('parent_student.student_id', $studentIds);
            })
            ->select('id', 'name', 'avatar', 'email')
            ->get();

        $parentIds = $parents->pluck('id');

        // إحصاءات لكل ولي أمر — استعلامات مجمّعة
        $praiseByParent = DB::table('parent_praises')
            ->whereIn('parent_id', $parentIds)
            ->whereIn('student_id', $studentIds)
            ->selectRaw('parent_id, COUNT(*) as cnt, MAX(created_at) as last_at')
            ->groupBy('parent_id')
            ->get()->keyBy('parent_id');

        $giftsByParent = DB::table('parent_gifts')
            ->whereIn('parent_id', $parentIds)
            ->whereIn('student_id', $studentIds)
            ->selectRaw('parent_id, COUNT(*) as cnt, COALESCE(SUM(points_cost),0) as pts, MAX(created_at) as last_at')
            ->groupBy('parent_id')
            ->get()->keyBy('parent_id');

        $messagesByParent = DB::table('parent_teacher_messages')
            ->where('teacher_id', $teacher->id)
            ->whereIn('parent_id', $parentIds)
            ->selectRaw('parent_id, COUNT(*) as cnt, MAX(created_at) as last_at')
            ->groupBy('parent_id')
            ->get()->keyBy('parent_id');

        $rows = $parents->map(function ($p) use ($praiseByParent, $giftsByParent, $messagesByParent) {
            $praise = $praiseByParent->get($p->id);
            $gifts = $giftsByParent->get($p->id);
            $messages = $messagesByParent->get($p->id);

            $lastDates = collect([
                optional($praise)->last_at,
                optional($gifts)->last_at,
                optional($messages)->last_at,
            ])->filter()->map(fn ($d) => \Carbon\Carbon::parse($d));

            $praiseCnt = (int) optional($praise)->cnt;
            $giftsCnt = (int) optional($gifts)->cnt;
            $messagesCnt = (int) optional($messages)->cnt;

            return [
                'id' => $p->id,
                'name' => $p->name,
                'avatar' => $p->avatar,
                'email' => $p->email,
                'praises_count' => $praiseCnt,
                'gifts_count' => $giftsCnt,
                'gifts_points' => (int) optional($gifts)->pts,
                'messages_count' => $messagesCnt,
                'last_engagement' => $lastDates->isNotEmpty() ? $lastDates->max() : null,
                'engagement_score' => $praiseCnt * 2 + $giftsCnt * 3 + $messagesCnt,
            ];
        })->sortByDesc('engagement_score')->values();

        $totals = [
            'parents_count' => $rows->count(),
            'active_parents' => $rows->where('engagement_score', '>', 0)->count(),
            'total_praises' => $rows->sum('praises_count'),
            'total_gifts' => $rows->sum('gifts_count'),
            'total_messages' => $rows->sum('messages_count'),
        ];

        return view('teacher.parent-engagement', compact('rows', 'totals'));
    }

    /**
     * تقرير مقارنة استبيان قبلي/بعدي — مفلتر على طلاب فصول المعلم
     */
    public function surveyComparison($surveyId)
    {
        $user = Auth::user();
        $survey = \App\Models\Survey::findOrFail($surveyId);

        if (! $survey->isAssessment()) {
            return back()->with('error', 'هذا الاستبيان ليس من نوع التقييم القبلي/البعدي');
        }

        // التحقق من الصلاحية: لو الاستبيان لمدرسة، يجب أن يكون المعلم في نفس المدرسة
        if ($survey->school_id && $survey->school_id !== $user->school_id) {
            abort(403, 'ليس لديك صلاحية الاطلاع على هذا الاستبيان');
        }

        $survey->load(['lesson.concept.value', 'value', 'linkedSurvey', 'questions']);

        // فلترة على طلاب فصول هذا المعلم فقط
        $studentIds = DB::table('classroom_student')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.teacher_id', $user->id)
            ->where('classroom_student.status', 'active')
            ->distinct()
            ->pluck('classroom_student.student_id')
            ->toArray();

        $comparisonData = $survey->getComparisonData(null, $studentIds);

        if (isset($comparisonData['error'])) {
            return back()->with('error', $comparisonData['error']);
        }

        return view('teacher.surveys.comparison', compact('survey', 'comparisonData'));
    }

    /**
     * قائمة استبيانات التقييم
     */
    public function surveyComparisonsList()
    {
        $user = Auth::user();

        // طلاب فصول المعلم — لفلترة المقارنة على طلابه فقط
        $studentIds = DB::table('classroom_student')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.teacher_id', $user->id)
            ->where('classroom_student.status', 'active')
            ->distinct()
            ->pluck('classroom_student.student_id')
            ->toArray();

        // نعتمد الاستبيان البعدي كمرساة (يرتبط بالقبلي عبر linkedSurvey)
        $postSurveys = \App\Models\Survey::where('survey_type', 'pre_post_assessment')
            ->where('assessment_phase', 'post')
            ->where(function ($q) use ($user) {
                $q->whereNull('school_id')->orWhere('school_id', $user->school_id);
            })
            ->with(['lesson.concept.value', 'value', 'linkedSurvey'])
            ->latest()
            ->get();

        $rows = $postSurveys->map(function ($post) use ($studentIds) {
            // نفلتر على طلاب المعلم فقط (مصفوفة فارغة → لا نتائج، لا نعرض طلاب غيره)
            $data = $post->getComparisonData(null, $studentIds);
            $stats = is_array($data) && ! isset($data['error'])
                ? $data['stats']
                : [
                    'total_pre_responses' => 0,
                    'total_post_responses' => 0,
                    'completed_both' => 0,
                    'avg_improvement' => 0,
                    'improved_count' => 0,
                    'declined_count' => 0,
                    'same_count' => 0,
                ];

            return [
                'pre' => $post->linkedSurvey,
                'post' => $post,
                'lesson' => $post->lesson,
                'value' => $post->value,
                'stats' => $stats,
            ];
        })->values();

        // KPIs إجمالية للقبلي والبعدي والمقارنة
        $withBoth = $rows->filter(fn ($r) => ($r['stats']['completed_both'] ?? 0) > 0);
        $kpis = [
            'pairs' => $rows->count(),
            'total_pre' => (int) $rows->sum(fn ($r) => $r['stats']['total_pre_responses'] ?? 0),
            'total_post' => (int) $rows->sum(fn ($r) => $r['stats']['total_post_responses'] ?? 0),
            'completed_both' => (int) $rows->sum(fn ($r) => $r['stats']['completed_both'] ?? 0),
            'avg_improvement' => $withBoth->count() > 0
                ? round($withBoth->avg(fn ($r) => $r['stats']['avg_improvement'] ?? 0), 1)
                : 0,
            'improved' => (int) $rows->sum(fn ($r) => $r['stats']['improved_count'] ?? 0),
            'declined' => (int) $rows->sum(fn ($r) => $r['stats']['declined_count'] ?? 0),
        ];

        return view('teacher.surveys.comparisons-list', compact('rows', 'kpis'));
    }
}
