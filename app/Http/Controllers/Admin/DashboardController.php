<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use App\Models\Lesson;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\QuestionBank;
use App\Models\Value;
use App\Models\Message;
use App\Models\Point;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // إحصائيات عامة
        $stats = [
            'total_users' => User::count(),
            'total_schools' => School::count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_parents' => User::where('role', 'parent')->count(),
            'total_lessons' => Lesson::count(),
            'total_activities' => Activity::count(),
            'total_submissions' => ActivitySubmission::count(),
            'pending_submissions' => ActivitySubmission::where('status', 'pending')->count(),
            'active_students' => User::where('role', 'student')->where('status', 'active')->count(),
            'total_values' => Value::count(),
        ];
        
        // إحصائيات بنك الأسئلة
        $question_stats = [
            'total' => QuestionBank::count(),
            'pending' => QuestionBank::where('status', 'pending')->count(),
            'approved' => QuestionBank::where('status', 'approved')->count(),
        ];
        
        // إحصائيات القيم
        $values_count = Value::count();
        
        // إحصائيات اليوم
        $today_stats = [
            'new_users' => User::whereDate('created_at', Carbon::today())->count(),
            'new_submissions' => ActivitySubmission::whereDate('created_at', Carbon::today())->count(),
        ];

        // آخر المستخدمين
        $recent_users = User::latest()->take(5)->get();

        // آخر المدارس
        $recent_schools = School::latest()->take(5)->get();
        
        // آخر التقديمات المعلقة
        $pending_reviews = ActivitySubmission::with(['student', 'activity'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();
        
        // الرسائل غير المقروءة
        $unread_messages_count = Message::where('is_read', false)->count();
        
        // إحصائيات الشهر الحالي vs الشهر السابق
        $current_month_users = User::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $last_month_users = User::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();
        
        $current_month_submissions = ActivitySubmission::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $last_month_submissions = ActivitySubmission::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();
        
        $growth_stats = [
            'users_current' => $current_month_users,
            'users_last' => $last_month_users,
            'users_growth' => $last_month_users > 0 ? round((($current_month_users - $last_month_users) / $last_month_users) * 100, 1) : 0,
            'submissions_current' => $current_month_submissions,
            'submissions_last' => $last_month_submissions,
            'submissions_growth' => $last_month_submissions > 0 ? round((($current_month_submissions - $last_month_submissions) / $last_month_submissions) * 100, 1) : 0,
        ];
        
        // بيانات الرسم البياني - المستخدمين الجدد خلال آخر 7 أيام
        $users_chart_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $users_chart_data[] = [
                'date' => $date->format('m/d'),
                'count' => User::whereDate('created_at', $date)->count(),
            ];
        }
        
        // بيانات الرسم البياني - التقديمات خلال آخر 7 أيام
        $submissions_chart_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $submissions_chart_data[] = [
                'date' => $date->format('m/d'),
                'count' => ActivitySubmission::whereDate('created_at', $date)->count(),
            ];
        }
        
        // لوحة الصدارة - أفضل 5 طلاب
        $top_students = User::where('role', 'student')
            ->withSum('points', 'points')
            ->orderByDesc('points_sum_points')
            ->take(5)
            ->get();
        
        // أكثر المدارس نشاطاً
        $top_schools = School::withCount(['users' => function($query) {
                $query->where('role', 'student');
            }])
            ->orderByDesc('users_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 
            'recent_users', 
            'recent_schools', 
            'question_stats',
            'values_count',
            'today_stats',
            'pending_reviews',
            'unread_messages_count',
            'growth_stats',
            'users_chart_data',
            'submissions_chart_data',
            'top_students',
            'top_schools'
        ));
    }

    /**
     * عرض التقديمات المعلقة بالتفصيل
     */
    public function pendingSubmissions()
    {
        $submissions = ActivitySubmission::with(['student.school', 'activity.lesson.concept.value'])
            ->where('status', 'pending')
            ->latest('submitted_at')
            ->paginate(20);

        // إحصائيات
        $stats = [
            'total_pending' => ActivitySubmission::where('status', 'pending')->count(),
            'today_pending' => ActivitySubmission::where('status', 'pending')
                ->whereDate('submitted_at', Carbon::today())->count(),
            'week_pending' => ActivitySubmission::where('status', 'pending')
                ->where('submitted_at', '>=', Carbon::now()->subWeek())->count(),
        ];

        return view('admin.pending-submissions', compact('submissions', 'stats'));
    }

    /**
     * مراجعة تقديم معين
     */
    public function reviewSubmission($id)
    {
        $submission = ActivitySubmission::with(['student.school', 'activity.lesson.concept.value'])
            ->findOrFail($id);

        return view('admin.review-submission', compact('submission'));
    }

    /**
     * حفظ المراجعة
     */
    public function saveReview($id)
    {
        $submission = ActivitySubmission::with(['student', 'activity'])->findOrFail($id);

        $validated = request()->validate([
            'status'   => 'required|in:approved,rejected',
            'score'    => 'nullable|integer|min:0|max:100',
            'feedback' => 'nullable|string|max:1000',
        ]);

        // حفظ سجل قبل التحديث: إن كان النشاط مُقيَّماً تلقائياً سابقاً،
        // فقد مُنحت النقاط له وقت التسليم. لا نمنحها مرة ثانية (Issue #49).
        $wasAlreadyAutoGraded = $submission->score !== null && $submission->score > 0;
        $previouslyEarnedPoints = (int) round(($submission->score ?? 0) / 100 * (optional($submission->activity)->points ?? 10));

        try {
            $submission->update([
                'status'      => $validated['status'],
                'score'       => $validated['score'] ?? 0,
                'feedback'    => $validated['feedback'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // إضافة نقاط للطالب فقط إذا:
            //  1) تمت الموافقة بدرجة > 0
            //  2) لم تكن مُمنحة سابقاً (يعني لم يُصحَّح تلقائياً)
            // فإن كان مُقيَّماً سابقاً، نمنح الفرق فقط (إن كان موجباً) لتفادي المضاعفة.
            if ($validated['status'] === 'approved'
                && ($validated['score'] ?? 0) > 0
                && $submission->student
            ) {
                $newPoints = (int) round(($validated['score'] / 100) * (optional($submission->activity)->points ?? 10));
                $delta = $wasAlreadyAutoGraded
                    ? max(0, $newPoints - $previouslyEarnedPoints)
                    : $newPoints;

                if ($delta > 0) {
                    $activityTitle = optional($submission->activity)->title ?? 'نشاط';

                    try {
                        \App\Services\PointsService::awardStudentPoints(
                            $submission->student->id,
                            $delta,
                            'activity_review',
                            "مراجعة نشاط: {$activityTitle}"
                        );
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('saveReview: awardStudentPoints failed', [
                            'submission_id' => $submission->id,
                            'error'         => $e->getMessage(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('saveReview failed', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
                'line'          => $e->getLine(),
            ]);
            return redirect()->back()
                ->withErrors(['general' => 'تعذّر حفظ المراجعة: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.pending-submissions')
            ->with('success', 'تمت المراجعة بنجاح');
    }
}

