<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Value;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * الحصول على school_id إذا لم يكن Super Admin
     */
    protected function getSchoolFilter()
    {
        $user = auth()->user();
        
        // إذا كان Super Admin يرى كل شيء
        if ($user->isSuperAdmin()) {
            return null;
        }
        
        // باقي الأدوار يشوفون بيانات مدرستهم فقط
        return $user->school_id;
    }
    
    /**
     * تطبيق فلتر المدرسة على Query
     */
    protected function applySchoolFilter($query)
    {
        $schoolId = $this->getSchoolFilter();
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        
        return $query;
    }
    
    /**
     * الصفحة الرئيسية للتقارير
     */
    public function index(Request $request)
    {
        return $this->dashboard($request);
    }
    
    /**
     * لوحة التحكم الإحصائية الرئيسية
     */
    public function dashboard(Request $request)
    {
        // تطبيع التاريخ — Carbon instances للسماح بفلترة جميع الإحصائيات
        $startDate = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->subMonth()->startOfDay();
        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();
        $schoolId = $this->getSchoolFilter();

        // إحصائيات عامة — تحترم date range
        $studentsQuery = User::where('role', 'student');
        $teachersQuery = User::where('role', 'teacher');

        if ($schoolId) {
            $studentsQuery->where('school_id', $schoolId);
            $teachersQuery->where('school_id', $schoolId);
        }

        $stats = [
            // الأرقام الإجمالية all-time (لا تتقيّد بالفترة) — مقاييس الفترة تبقى أدناه
            'total_students' => (clone $studentsQuery)->count(),
            'total_teachers' => (clone $teachersQuery)->count(),
            'total_schools' => $schoolId ? 1 : School::count(),
            'total_activities' => Activity::query()
                ->when($schoolId, fn($q) => $q->whereHas('creator', fn($cq) => $cq->where('school_id', $schoolId)))
                ->count(),
            'total_submissions' => ActivitySubmission::whereBetween('created_at', [$startDate, $endDate])
                ->when($schoolId, function($q) use ($schoolId) {
                    $q->whereHas('student', function($sq) use ($schoolId) {
                        $sq->where('school_id', $schoolId);
                    });
                })
                ->count(),
            'active_students' => User::where('role', 'student')
                ->where('status', 'active')
                ->when($schoolId, function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
        ];

        // أفضل 10 طلاب — النقاط ضمن الفترة المُختارة + tie-break ثابت
        $topStudents = User::where('role', 'student')
            ->when($schoolId, function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->withSum(['points as total_points' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])], 'points')
            ->orderByDesc('total_points')
            ->orderBy('id')
            ->limit(10)
            ->get();

        // أنشط المدارس (فقط للـ Super Admin)
        $activeSchools = collect([]);
        if (!$schoolId) {
            $activeSchools = School::withCount(['users as active_students' => function($q) {
                    $q->where('role', 'student')->where('status', 'active');
                }])
                ->orderByDesc('active_students')
                ->limit(10)
                ->get();
        }

        // إحصائيات الأنشطة حسب النوع — ضمن الفترة المُختارة
        $activitiesByType = Activity::whereBetween('created_at', [$startDate, $endDate])
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();
        
        // معدل الإنجاز اليومي (آخر 30 يوم) — مع ملء الأيام الفارغة بـ 0 لمنع فجوات الرسم
        $rawDaily = ActivitySubmission::where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dailyProgress = collect();
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $dailyProgress->push((object) [
                'date' => $day,
                'count' => (int) ($rawDaily[$day]->count ?? 0),
            ]);
        }
        
        // أكثر القيم تطبيقاً
        $topValues = Value::withCount('concepts')
            ->orderByDesc('concepts_count')
            ->limit(5)
            ->get();
        
        return view('admin.reports.dashboard', compact(
            'stats',
            'topStudents',
            'activeSchools',
            'activitiesByType',
            'dailyProgress',
            'topValues',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * تقارير الطلاب - محسّن
     */
    public function students(Request $request)
    {
        $query = User::where('role', 'student');
        
        // فلتر حسب المدرسة
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        
        // فلتر حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // فلتر حسب التاريخ
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }
        
        // تحسين: تحديد الحقول المطلوبة فقط مع Eager Loading محسّن
        $students = $query->select(['id', 'name', 'email', 'school_id', 'status', 'created_at'])
            ->with(['school:id,name'])
            ->withSum('points as total_points', 'points')
            ->withCount('activitySubmissions')
            ->paginate(20);
        
        // تحسين: جلب المدارس النشطة فقط
        $schools = School::select(['id', 'name'])->where('status', 'active')->get();
        
        return view('admin.reports.students', compact('students', 'schools'));
    }
    
    /**
     * تفاصيل طالب معين
     */
    public function studentDetail($id)
    {
        $student = User::where('role', 'student')
            ->with(['school', 'points', 'badges', 'streak', 'activitySubmissions.activity'])
            ->withSum('points as total_points', 'points')
            ->withCount('activitySubmissions')
            ->findOrFail($id);
        
        // إحصائيات الطالب
        $stats = [
            'total_points' => $student->total_points ?? 0,
            'total_submissions' => $student->activity_submissions_count,
            'total_badges' => $student->badges->count(),
            'current_streak' => $student->streak?->current_streak ?? 0,
            'completed_activities' => $student->activitySubmissions()
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                ->count(),
            'average_score' => $student->activitySubmissions()
                ->whereNotNull('score')
                ->avg('score'),
        ];
        
        // آخر الأنشطة
        $recentActivities = $student->activitySubmissions()
            ->with('activity')
            ->latest()
            ->limit(10)
            ->get();
        
        // التقدم حسب القيمة — تم إزالة values.emoji (العمود غير موجود)
        try {
            $progressByValue = DB::table('activity_submissions')
                ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
                ->join('lessons', 'activities.lesson_id', '=', 'lessons.id')
                ->join('concepts', 'lessons.concept_id', '=', 'concepts.id')
                ->join('values', 'concepts.value_id', '=', 'values.id')
                ->where('activity_submissions.student_id', $id)
                // استبعاد الـ rejected/pending/needs_review من عدّ "إنجاز" تحت قيم
                ->whereIn('activity_submissions.status', \App\Models\ActivitySubmission::DONE_STATUSES)
                ->select('values.id', 'values.name', DB::raw('count(*) as activities_count'))
                ->groupBy('values.id', 'values.name')
                ->get();
        } catch (\Exception $e) {
            $progressByValue = collect([]);
        }

        return view('admin.reports.student-detail', compact('student', 'stats', 'recentActivities', 'progressByValue'));
    }
    
    /**
     * تقارير المدارس
     */
    public function schools(Request $request)
    {
        $query = School::query();
        
        // فلتر حسب المدينة
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        
        // فلتر حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $schools = $query->withCount([
                'users as students_count' => function($q) {
                    $q->where('role', 'student');
                },
                'users as teachers_count' => function($q) {
                    $q->where('role', 'teacher');
                },
                'users as active_students_count' => function($q) {
                    $q->where('role', 'student')->where('status', 'active');
                }
            ])
            ->paginate(20);
        
        // جمع المدن المتاحة
        $cities = School::distinct()->pluck('city');
        
        return view('admin.reports.schools', compact('schools', 'cities'));
    }
    
    /**
     * تفاصيل مدرسة معينة
     */
    public function schoolDetail($id)
    {
        $school = School::with(['branches', 'users'])
            ->withCount([
                'users as students_count' => function($q) {
                    $q->where('role', 'student');
                },
                'users as teachers_count' => function($q) {
                    $q->where('role', 'teacher');
                }
            ])
            ->findOrFail($id);
        
        // إحصائيات المدرسة
        $stats = [
            'total_students' => $school->students_count,
            'total_teachers' => $school->teachers_count,
            'total_branches' => $school->branches->count(),
            'active_students' => $school->users()->where('role', 'student')->where('status', 'active')->count(),
            'total_points' => DB::table('points')
                ->join('users', 'points.user_id', '=', 'users.id')
                ->where('users.school_id', $id)
                ->sum('points.points'),
        ];
        
        // أفضل الطلاب في المدرسة
        $topStudents = User::where('role', 'student')
            ->where('school_id', $id)
            ->withSum('points as total_points', 'points')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();
        
        // المعلمون
        $teachers = $school->users()->where('role', 'teacher')->get();
        
        return view('admin.reports.school-detail', compact('school', 'stats', 'topStudents', 'teachers'));
    }
    
    /**
     * تقارير الأنشطة
     */
    public function activities(Request $request)
    {
        $query = Activity::query();
        
        // فلتر حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // فلتر حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // متوسط الدرجة: يستبعد الـ rejected لمنع جرّ المتوسط نحو 0
        $activities = $query->with('lesson.concept.value')
            ->withCount('submissions')
            ->withAvg(['submissions as average_score' => function ($q) {
                $q->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                  ->whereNotNull('score');
            }], 'score')
            ->paginate(20);
        
        return view('admin.reports.activities', compact('activities'));
    }
    
    /**
     * تقارير القيم
     */
    public function values(Request $request)
    {
        $values = Value::with('concepts.lessons.activities')
            ->withCount([
                'concepts',
                'concepts as total_lessons' => function($q) {
                    $q->join('lessons', 'concepts.id', '=', 'lessons.concept_id');
                },
                'concepts as total_activities' => function($q) {
                    $q->join('lessons', 'concepts.id', '=', 'lessons.concept_id')
                        ->join('activities', 'lessons.id', '=', 'activities.lesson_id');
                }
            ])
            ->get();
        
        return view('admin.reports.values', compact('values'));
    }
    
    /**
     * تصدير التقرير بصيغة Excel
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'students');
        $schoolId = $this->getSchoolFilter();
        $filename = $type . '_report_' . date('Y-m-d_His') . '.xlsx';

        switch ($type) {
            case 'students':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\StudentsExport($schoolId), $filename
                );
            case 'teachers':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\TeachersExport($schoolId), $filename
                );
            case 'parents':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ParentsExport($schoolId), $filename
                );
            case 'schools':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\SchoolsExport(), $filename
                );
            case 'activities':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ActivitiesExport($schoolId), $filename
                );
            case 'values':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ValuesExport(), $filename
                );
            default:
                return back()->with('error', 'نوع التقرير غير مدعوم');
        }
    }

    /**
     * تصدير التقرير بصيغة PDF (Issue 13/108).
     * يستخدم Barryvdh\DomPDF بقالب admin/reports/pdf/{type}.blade.php.
     */
    public function exportPdf(Request $request)
    {
        $type = $request->input('type', 'students');
        $schoolId = $this->getSchoolFilter();
        $filename = $type . '_report_' . date('Y-m-d_His') . '.pdf';

        // البيانات المُمرَّرة لكل نوع تقرير
        $data = match ($type) {
            'students' => [
                'rows' => User::where('role', 'student')
                    ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                    ->with('school:id,name')
                    ->withSum('points as total_points', 'points')
                    ->withCount('activitySubmissions')
                    ->limit(500)
                    ->get(),
                'title' => 'تقرير الطلاب',
            ],
            'teachers' => [
                'rows' => User::where('role', 'teacher')
                    ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                    ->with('school:id,name')
                    ->limit(500)
                    ->get(),
                'title' => 'تقرير المعلمين',
            ],
            'schools' => [
                // مدير المدرسة يرى مدرسته فقط — منع تسرب بيانات مدارس أخرى
                'rows' => School::when($schoolId, fn($q) => $q->where('id', $schoolId))
                    ->withCount([
                        'users as students_count' => fn($q) => $q->where('role', 'student'),
                        'users as teachers_count' => fn($q) => $q->where('role', 'teacher'),
                    ])
                    ->limit(500)
                    ->get(),
                'title' => 'تقرير المدارس',
            ],
            'activities' => [
                // فلتر المدرسة: عرض فقط الأنشطة التي أنشأها معلمو نفس المدرسة
                'rows' => Activity::with('lesson.concept.value:id,name')
                    ->when($schoolId, fn($q) => $q->whereHas('creator', fn($cq) => $cq->where('school_id', $schoolId)))
                    ->withCount('submissions')
                    ->withAvg(['submissions as average_score' => fn($q) => $q->whereIn('status', ['completed','approved'])->whereNotNull('score')], 'score')
                    ->limit(500)
                    ->get(),
                'title' => 'تقرير الأنشطة',
            ],
            'values' => [
                'rows' => Value::withCount('concepts')->get(),
                'title' => 'تقرير القيم',
            ],
            default => null,
        };

        if (!$data) {
            return back()->with('error', 'نوع التقرير غير مدعوم');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.pdf.report', [
            'type'       => $type,
            'title'      => $data['title'],
            'rows'       => $data['rows'],
            'generatedAt'=> now()->format('Y-m-d H:i'),
            'generatedBy'=> auth()->user()->name ?? '',
        ]);

        $pdf->setPaper('a4', 'portrait');
        return $pdf->download($filename);
    }
}

