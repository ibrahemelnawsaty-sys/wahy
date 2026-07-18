<?php

namespace App\Http\Controllers;

use App\Exports\ActivitiesExport;
use App\Exports\BulkUsersTemplateExport;
use App\Exports\ParentsExport;
use App\Exports\StudentsExport;
use App\Exports\TeachersExport;
use App\Imports\BulkUsersImport;
use App\Mail\RegistrationApprovedMail;
use App\Mail\RegistrationRejectedMail;
use App\Models\ActivitySubmission;
use App\Models\Classroom;
use App\Models\RegistrationRequest;
use App\Models\School;
use App\Models\SchoolStatisticsCache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SchoolAdminController extends Controller
{
    /**
     * لوحة التحكم لمدير المدرسة
     */
    public function dashboard()
    {
        $user = Auth::user();
        $school = $user->school;

        if (! $school) {
            abort(403, 'لا يوجد مدرسة مرتبطة بحسابك');
        }

        // طلبات التسجيل المعلقة مع Eager Loading
        $pendingRequests = RegistrationRequest::where('school_id', $school->id)
            ->where('status', 'pending')
            ->select(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->latest()
            ->take(5)
            ->get();

        // إحصائيات المدرسة - استعلامات محسّنة
        $userCounts = $school->users()
            ->selectRaw('role, status, COUNT(*) as count')
            ->groupBy('role', 'status')
            ->get()
            ->groupBy('role');

        $stats = [
            'teachers' => $userCounts->get('teacher', collect())->where('status', 'active')->sum('count'),
            'students' => $userCounts->get('student', collect())->where('status', 'active')->sum('count'),
            'parents' => $userCounts->get('parent', collect())->where('status', 'active')->sum('count'),
            'classrooms' => $school->classrooms()->where('status', 'active')->count(),
            'pending_requests' => $pendingRequests->count(),
            'total_points' => DB::table('points')
                ->join('users', 'points.user_id', '=', 'users.id')
                ->where('users.school_id', $school->id)
                ->sum('points.points'),
            'completed_activities' => ActivitySubmission::whereHas('student', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)->count(),
            'inactive_teachers' => $userCounts->get('teacher', collect())->where('status', 'inactive')->sum('count'),
            'inactive_students' => $userCounts->get('student', collect())->where('status', 'inactive')->sum('count'),
        ];

        // أفضل 5 طلاب في المدرسة - مع تحديد الحقول المطلوبة فقط
        $topStudents = User::where('school_id', $school->id)
            ->where('role', 'student')
            ->select(['id', 'name', 'avatar', 'school_id'])
            ->withSum('points as total_points', 'points')
            ->orderByDesc('total_points')
            ->limit(5)
            ->get();

        // أحدث الفصول - مع تحديد الحقول
        $recentClassrooms = $school->classrooms()
            ->with(['teacher:id,name'])
            ->withCount('students')
            ->select(['id', 'name', 'teacher_id', 'school_id', 'created_at'])
            ->latest()
            ->take(5)
            ->get();

        // إحصائيات الأنشطة لآخر 30 يوم - استعلام محسّن
        $dailyActivities = DB::table('activity_submissions')
            ->join('users', 'activity_submissions.student_id', '=', 'users.id')
            ->where('users.school_id', $school->id)
            ->where('activity_submissions.created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(activity_submissions.created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // أحدث المعلمين
        $recentTeachers = $school->users()
            ->where('role', 'teacher')
            ->select(['id', 'name', 'email', 'status', 'created_at'])
            ->latest()
            ->take(5)
            ->get();

        // الطلاب النشطين حالياً (الأون لاين) - آخر 5 دقائق
        $onlineThreshold = now()->subMinutes(5)->timestamp;
        $onlineStudentIds = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $onlineThreshold)
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->where('users.role', 'student')
            ->where('users.school_id', $school->id)
            ->where('users.status', 'active')
            ->distinct()
            ->pluck('users.id');

        // استعلام واحد يجمع بيانات جلسات اليوم لكل الطلاب المتصلين
        // (سابقًا كان N+1: استعلامان لكل طالب)
        $todayStart = now()->startOfDay()->timestamp;
        $sessionsAgg = DB::table('sessions')
            ->whereIn('user_id', $onlineStudentIds)
            ->where('last_activity', '>=', $todayStart)
            ->selectRaw('user_id, MIN(last_activity) AS first_activity, MAX(last_activity) AS last_activity')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $onlineStudents = User::whereIn('id', $onlineStudentIds)
            ->select(['id', 'name', 'email', 'avatar'])
            ->withSum('points as total_points', 'points')
            ->get()
            ->map(function ($student) use ($sessionsAgg, $onlineThreshold) {
                $agg = $sessionsAgg->get($student->id);

                if ($agg && $agg->last_activity >= $onlineThreshold) {
                    $totalSeconds = max(0, $agg->last_activity - $agg->first_activity) + 300;
                    $hours = floor($totalSeconds / 3600);
                    $minutes = floor(($totalSeconds % 3600) / 60);
                    $student->session_time = $hours > 0 ? "{$hours} ساعة و {$minutes} دقيقة" : "{$minutes} دقيقة";

                    $minutesAgo = floor((now()->timestamp - $agg->last_activity) / 60);
                    $student->online_since = $minutesAgo <= 1 ? 'الآن' : "منذ {$minutesAgo} دقيقة";
                } else {
                    $student->session_time = '0 دقيقة';
                    $student->online_since = 'غير متصل';
                }

                return $student;
            });

        return view('school-admin.dashboard', compact(
            'user',
            'school',
            'pendingRequests',
            'stats',
            'topStudents',
            'recentClassrooms',
            'dailyActivities',
            'recentTeachers',
            'onlineStudents',
        ));
    }

    /**
     * عرض روابط التسجيل و QR Codes
     */
    public function registrationLinks()
    {
        $school = Auth::user()->school;

        // توليد tokens إذا لم تكن موجودة
        if (! $school->teacher_token || ! $school->student_token || ! $school->parent_token) {
            $school->generateRegistrationTokens();
            $school->refresh();
        }

        return view('school-admin.registration-links', compact('school'));
    }

    /**
     * تجديد token معين
     */
    public function regenerateToken(Request $request)
    {
        $school = Auth::user()->school;
        $role = $request->input('role');

        if (in_array($role, ['teacher', 'student', 'parent'])) {
            $tokenField = $role . '_token';
            $school->update([
                $tokenField => \Illuminate\Support\Str::random(32),
            ]);
        }

        return redirect()->back()->with('success', 'تم تجديد الرابط بنجاح');
    }

    /**
     * تفعيل/تعطيل التسجيل لدور معين
     */
    public function toggleRegistration(Request $request)
    {
        $school = Auth::user()->school;
        $role = $request->input('role');

        if (in_array($role, ['teacher', 'student', 'parent'])) {
            $enableField = 'enable_' . $role . '_registration';
            $school->update([
                $enableField => ! $school->$enableField,
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث الإعدادات بنجاح');
    }

    // ==================== إدارة المعلمين ====================

    public function teachers()
    {
        $school = Auth::user()->school;
        $teachers = $school->users()
            ->where('role', 'teacher')
            ->with(['teachingClassrooms' => function ($query) {
                $query->withCount('students');
            }])
            ->withCount('teachingClassrooms')
            ->latest()
            ->paginate(20);

        return view('school-admin.teachers.index', compact('teachers', 'school'));
    }

    public function createTeacher()
    {
        $school = Auth::user()->school;

        return view('school-admin.teachers.create', compact('school'));
    }

    public function storeTeacher(Request $request)
    {
        $school = Auth::user()->school;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
        ]);

        $validated['school_id'] = $school->id;
        $validated['role'] = 'teacher';
        $validated['status'] = 'active';
        $validated['password'] = Hash::make($validated['password']);
        $validated['qr_code'] = 'TCH-' . strtoupper(uniqid());

        User::create($validated);

        return redirect()->route('school-admin.teachers')->with('success', 'تم إضافة المعلم بنجاح');
    }

    public function editTeacher($id)
    {
        $school = Auth::user()->school;
        $teacher = User::where('school_id', $school->id)
            ->where('role', 'teacher')
            ->findOrFail($id);

        return view('school-admin.teachers.edit', compact('teacher', 'school'));
    }

    public function updateTeacher(Request $request, $id)
    {
        $school = Auth::user()->school;
        $teacher = User::where('school_id', $school->id)
            ->where('role', 'teacher')
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $validated['password'] = Hash::make($request->password);
        }

        $teacher->update($validated);

        return redirect()->route('school-admin.teachers')->with('success', 'تم تحديث بيانات المعلم');
    }

    public function deleteTeacher($id)
    {
        $school = Auth::user()->school;
        $teacher = User::where('school_id', $school->id)
            ->where('role', 'teacher')
            ->findOrFail($id);

        $teacher->delete();

        return redirect()->route('school-admin.teachers')->with('success', 'تم حذف المعلم');
    }

    // ==================== إدارة الطلاب ====================

    public function students()
    {
        $school = Auth::user()->school;
        $students = $school->users()
            ->where('role', 'student')
            ->with(['classrooms' => function ($query) {
                $query->select('classrooms.id', 'name', 'grade_level', 'academic_year', 'teacher_id')
                    ->with('teacher:id,name');
            }, 'parents' => function ($query) {
                $query->select('users.id', 'name', 'phone', 'email')
                    ->withPivot('relationship');
            }])
            ->withSum('points as total_points', 'points')
            ->withCount('activitySubmissions')
            ->latest()
            ->paginate(20);

        return view('school-admin.students.index', compact('students', 'school'));
    }

    public function createStudent()
    {
        $school = Auth::user()->school;
        $classrooms = $school->classrooms()->where('status', 'active')->get();

        return view('school-admin.students.create', compact('school', 'classrooms'));
    }

    public function storeStudent(Request $request)
    {
        $school = Auth::user()->school;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'birth_date' => 'nullable|date|before:today',
            'classrooms' => 'nullable|array',
        ]);

        $validated['school_id'] = $school->id;
        $validated['role'] = 'student';
        $validated['status'] = 'active';
        $validated['password'] = Hash::make($validated['password']);
        $validated['qr_code'] = 'STU-' . strtoupper(uniqid());

        $student = User::create($validated);

        // إضافة الطالب للفصول — محصورة بفصول مدرسة الطالب فقط (منع IDOR/تسرب بين المدارس)
        if ($request->has('classrooms')) {
            $validClassroomIds = Classroom::where('school_id', $student->school_id)
                ->whereIn('id', (array) $request->classrooms)
                ->pluck('id')->all();
            $student->classrooms()->attach($validClassroomIds, [
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
        }

        return redirect()->route('school-admin.students')->with('success', 'تم إضافة الطالب بنجاح');
    }

    public function editStudent($id)
    {
        $school = Auth::user()->school;
        $student = User::where('school_id', $school->id)
            ->where('role', 'student')
            ->with('classrooms')
            ->findOrFail($id);
        $classrooms = $school->classrooms()->where('status', 'active')->get();

        return view('school-admin.students.edit', compact('student', 'school', 'classrooms'));
    }

    /**
     * عرض تفاصيل طالب (Issue: school-admin.students.show route مفقود).
     */
    public function showStudent($id)
    {
        $school = Auth::user()->school;
        $student = User::where('school_id', $school->id)
            ->where('role', 'student')
            ->with(['classrooms', 'school', 'streak', 'badges'])
            ->withSum('points as total_points', 'points')
            ->withCount([
                'activitySubmissions',
                'activitySubmissions as completed_count' => function ($q) {
                    $q->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES);
                },
            ])
            ->findOrFail($id);

        // آخر 10 تسليمات
        $recentSubmissions = \App\Models\ActivitySubmission::where('student_id', $student->id)
            ->with('activity:id,title,type')
            ->latest()
            ->limit(10)
            ->get();

        return view('school-admin.students.show', compact('student', 'school', 'recentSubmissions'));
    }

    public function updateStudent(Request $request, $id)
    {
        $school = Auth::user()->school;
        $student = User::where('school_id', $school->id)
            ->where('role', 'student')
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'status' => 'required|in:active,inactive',
            'birth_date' => 'nullable|date|before:today',
            'classrooms' => 'nullable|array',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $validated['password'] = Hash::make($request->password);
        }

        $student->update($validated);

        // تحديث الفصول — محصورة بفصول مدرسة الطالب فقط (منع IDOR)
        if ($request->has('classrooms')) {
            $validClassroomIds = Classroom::where('school_id', $student->school_id)
                ->whereIn('id', (array) $request->classrooms)
                ->pluck('id')->all();
            $student->classrooms()->sync($validClassroomIds);
        }

        return redirect()->route('school-admin.students')->with('success', 'تم تحديث بيانات الطالب');
    }

    public function deleteStudent($id)
    {
        $school = Auth::user()->school;
        $student = User::where('school_id', $school->id)
            ->where('role', 'student')
            ->findOrFail($id);

        $student->delete();

        return redirect()->route('school-admin.students')->with('success', 'تم حذف الطالب');
    }

    // ==================== إدارة أولياء الأمور ====================

    public function parents()
    {
        $school = Auth::user()->school;
        $parents = $school->users()
            ->where('role', 'parent')
            ->with(['children' => function ($query) {
                $query->select('users.id', 'name', 'email')
                    ->withPivot('relationship')
                    ->with(['classrooms' => function ($q) {
                        $q->select('classrooms.id', 'name', 'grade_level')
                            ->with('teacher:id,name');
                    }]);
            }])
            ->withCount('children')
            ->latest()
            ->paginate(20);

        return view('school-admin.parents.index', compact('parents', 'school'));
    }

    public function createParent()
    {
        $school = Auth::user()->school;
        $students = $school->users()->where('role', 'student')->where('status', 'active')->get();

        return view('school-admin.parents.create', compact('school', 'students'));
    }

    public function storeParent(Request $request)
    {
        $school = Auth::user()->school;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
            'children' => 'nullable|array',
            'relationship' => 'nullable|string|max:50',
        ]);

        $validated['school_id'] = $school->id;
        $validated['role'] = 'parent';
        $validated['status'] = 'active';
        $validated['password'] = Hash::make($validated['password']);
        $validated['qr_code'] = 'PAR-' . strtoupper(uniqid());

        $parent = User::create($validated);

        // ربط الأبناء — محصور بطلاب نفس المدرسة فقط (منع IDOR/تسرب)
        if ($request->has('children')) {
            $validChildIds = User::where('school_id', $parent->school_id)
                ->where('role', 'student')
                ->whereIn('id', (array) $request->children)
                ->pluck('id')->all();
            foreach ($validChildIds as $studentId) {
                $parent->children()->attach($studentId, [
                    'relationship' => $request->relationship ?? 'parent',
                ]);
            }
        }

        return redirect()->route('school-admin.parents')->with('success', 'تم إضافة ولي الأمر بنجاح');
    }

    public function editParent($id)
    {
        $school = Auth::user()->school;
        $parent = User::where('school_id', $school->id)
            ->where('role', 'parent')
            ->with('children')
            ->findOrFail($id);
        $students = $school->users()->where('role', 'student')->where('status', 'active')->get();

        return view('school-admin.parents.edit', compact('parent', 'school', 'students'));
    }

    public function updateParent(Request $request, $id)
    {
        $school = Auth::user()->school;
        $parent = User::where('school_id', $school->id)
            ->where('role', 'parent')
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'children' => 'nullable|array',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $validated['password'] = Hash::make($request->password);
        }

        $parent->update($validated);

        // تحديث الأبناء — محصور بطلاب نفس المدرسة فقط (منع IDOR/تسرب)
        if ($request->has('children')) {
            $validChildIds = User::where('school_id', $parent->school_id)
                ->where('role', 'student')
                ->whereIn('id', (array) $request->children)
                ->pluck('id')->all();
            $parent->children()->sync($validChildIds);
        }

        return redirect()->route('school-admin.parents')->with('success', 'تم تحديث بيانات ولي الأمر');
    }

    public function deleteParent($id)
    {
        $school = Auth::user()->school;
        $parent = User::where('school_id', $school->id)
            ->where('role', 'parent')
            ->findOrFail($id);

        $parent->delete();

        return redirect()->route('school-admin.parents')->with('success', 'تم حذف ولي الأمر');
    }

    // ==================== إدارة الفصول ====================

    public function classrooms()
    {
        $school = Auth::user()->school;
        $classrooms = $school->classrooms()
            ->with('teacher')
            ->withCount('students')
            ->latest()
            ->paginate(20);

        return view('school-admin.classrooms.index', compact('classrooms', 'school'));
    }

    public function createClassroom()
    {
        $school = Auth::user()->school;
        $teachers = $school->users()->where('role', 'teacher')->where('status', 'active')->get();
        $students = $school->users()->where('role', 'student')->where('status', 'active')->get();

        // Issue #38: تمرير المراحل الدراسية المرتبطة بالمدرسة + سنواتها
        // لتظهر في dropdown بدلاً من نص حر.
        $educationLevels = $school
            ? $school->educationLevels()->with('academicYears')->where('status', true)->get()
            : collect();

        return view('school-admin.classrooms.create', compact('school', 'teachers', 'students', 'educationLevels'));
    }

    public function storeClassroom(Request $request)
    {
        $school = Auth::user()->school;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:users,id',
            'grade_level' => 'nullable|string|max:50',
            'academic_year' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1|max:100',
            'description' => 'nullable|string|max:1000',
            'students' => 'nullable|array',
            'students.*' => 'exists:users,id',
        ]);

        // التحقق من أن المعلم ينتمي لنفس المدرسة وأنه معلم فعلاً
        $teacher = User::where('id', $validated['teacher_id'])
            ->where('school_id', $school->id)
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->first();

        if (! $teacher) {
            return back()->withErrors(['teacher_id' => 'المعلم المحدد غير صالح'])->withInput();
        }

        $validated['school_id'] = $school->id;
        $validated['status'] = 'active';

        // التحقق من أن عدد الطلاب لا يتجاوز السعة
        if ($request->has('students') && ! empty($validated['capacity'])) {
            $studentsCount = count($request->students);
            if ($studentsCount > $validated['capacity']) {
                return back()->withErrors([
                    'students' => "عدد الطلاب المحددين ({$studentsCount}) يتجاوز سعة الفصل ({$validated['capacity']}). يرجى تقليل عدد الطلاب أو زيادة السعة.",
                ])->withInput();
            }
        }

        $classroom = Classroom::create($validated);

        // إضافة الطلاب المحددين — محصورة بطلاب نفس المدرسة فقط (منع IDOR/تسرب بين المدارس)
        if ($request->has('students')) {
            $validStudentIds = User::where('school_id', $classroom->school_id)
                ->where('role', 'student')
                ->whereIn('id', (array) $request->students)
                ->pluck('id')->all();
            $classroom->students()->attach($validStudentIds);
        }

        return redirect()->route('school-admin.classrooms')->with('success', 'تم إنشاء الفصل بنجاح');
    }

    public function editClassroom($id)
    {
        $school = Auth::user()->school;
        $classroom = Classroom::where('school_id', $school->id)->findOrFail($id);
        $teachers = $school->users()->where('role', 'teacher')->where('status', 'active')->get();
        $students = $school->users()->where('role', 'student')->where('status', 'active')->get();

        return view('school-admin.classrooms.edit', compact('classroom', 'school', 'teachers', 'students'));
    }

    public function updateClassroom(Request $request, $id)
    {
        $school = Auth::user()->school;
        $classroom = Classroom::where('school_id', $school->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:users,id',
            'grade_level' => 'nullable|string|max:50',
            'academic_year' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'students' => 'nullable|array',
            'students.*' => 'exists:users,id',
        ]);

        // التحقق من أن عدد الطلاب لا يتجاوز السعة
        $capacity = $validated['capacity'] ?? $classroom->capacity;
        if ($request->has('students') && ! empty($capacity)) {
            $studentsCount = count($request->students);
            if ($studentsCount > $capacity) {
                return back()->withErrors([
                    'students' => "عدد الطلاب المحددين ({$studentsCount}) يتجاوز سعة الفصل ({$capacity}). يرجى تقليل عدد الطلاب أو زيادة السعة.",
                ])->withInput();
            }
        }

        // التحقق أن المعلم من نفس المدرسة (منع إسناد معلم من مدرسة أخرى — IDOR)
        $validTeacher = User::where('id', $validated['teacher_id'])
            ->where('school_id', $school->id)
            ->where('role', 'teacher')
            ->exists();
        if (! $validTeacher) {
            return back()->withErrors(['teacher_id' => 'المعلم المحدد ليس من مدرستك'])->withInput();
        }

        $classroom->update($validated);

        // Sync students — محصورة بطلاب نفس المدرسة فقط (منع IDOR)
        if ($request->has('students')) {
            $validStudentIds = User::where('school_id', $school->id)
                ->where('role', 'student')
                ->whereIn('id', (array) $request->students)
                ->pluck('id')->all();
            $classroom->students()->sync($validStudentIds);
        } else {
            // إذا لم يتم اختيار أي طالب، احذف جميع الطلاب من الفصل
            $classroom->students()->detach();
        }

        return redirect()->route('school-admin.classrooms')->with('success', 'تم تحديث الفصل بنجاح');
    }

    public function deleteClassroom($id)
    {
        $school = Auth::user()->school;
        $classroom = Classroom::where('school_id', $school->id)->findOrFail($id);

        $classroom->delete();

        return redirect()->route('school-admin.classrooms')->with('success', 'تم حذف الفصل');
    }

    // ==================== طلبات التسجيل ====================

    public function registrationRequests()
    {
        $school = Auth::user()->school;
        $requests = RegistrationRequest::where('school_id', $school->id)
            ->latest()
            ->paginate(20);

        return view('school-admin.requests.index', compact('requests', 'school'));
    }

    public function approveRequest($id)
    {
        $school = Auth::user()->school;
        $request = RegistrationRequest::where('school_id', $school->id)->findOrFail($id);

        // توليد كلمة مرور مؤقتة
        $temporaryPassword = 'Temp-' . strtoupper(Str::random(8));

        // تنفيذ ذرّي: إنشاء المستخدم + تحديث الطلب في معاملة واحدة
        try {
            $user = DB::transaction(function () use ($request, $school, $temporaryPassword) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($temporaryPassword),
                    'role' => $request->role,
                    'school_id' => $school->id,
                    'phone' => $request->phone,
                    'status' => 'active',
                    'qr_code' => strtoupper($request->role) . '-' . strtoupper(uniqid()),
                    'password_change_required' => true,
                ]);

                $request->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'user_id' => $user->id,
                ]);

                return $user;
            });
        } catch (\Throwable $e) {
            \Log::error('فشل قبول طلب التسجيل', ['request_id' => $id, 'error' => $e->getMessage()]);

            return back()->with('error', 'حدث خطأ أثناء قبول الطلب. يرجى المحاولة مجددًا.');
        }

        // إرسال إيميل الموافقة — مع تحقق صيغة الإيميل
        $emailSent = false;
        if (! empty($request->email) && filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($request->email)->send(new RegistrationApprovedMail($request, $temporaryPassword));
                $emailSent = true;
            } catch (\Exception $e) {
                \Log::error('فشل إرسال إيميل الموافقة', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        // إطلاق Event للترحيب بالطالب (سيرسل إشعار تلقائي)
        if ($user->role === 'student') {
            event(new \App\Events\StudentRegistered($user, Auth::user()));
        }

        $msg = 'تم قبول الطلب وإنشاء الحساب' . ($emailSent
            ? ' وإرسال كلمة المرور المؤقتة للمستخدم.'
            : '. تنبيه: لم يتم إرسال البريد، يرجى تزويد المستخدم بكلمة المرور يدويًا: ' . $temporaryPassword);

        return back()->with('success', $msg);
    }

    public function rejectRequest(Request $request, $id)
    {
        $school = Auth::user()->school;
        $registrationRequest = RegistrationRequest::where('school_id', $school->id)->findOrFail($id);

        $validated = $request->validate([
            'rejected_reason' => 'nullable|string|max:500',
        ]);

        $registrationRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejected_reason' => $validated['rejected_reason'] ?? null,
        ]);

        // إرسال إيميل الرفض
        try {
            Mail::to($registrationRequest->email)->send(new RegistrationRejectedMail($registrationRequest));
        } catch (\Exception $e) {
            \Log::error('Failed to send rejection email: ' . $e->getMessage());
        }

        return back()->with('success', 'تم رفض الطلب وإخطار المستخدم');
    }

    // ==================== Excel Import/Export ====================

    /**
     * تحميل Excel template للمستخدمين
     */
    public function downloadTemplate(Request $request)
    {
        $role = $request->input('role', 'students'); // students, teachers, parents

        $filename = 'template_' . $role . '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new BulkUsersTemplateExport($role), $filename);
    }

    /**
     * رفع Excel وتسجيل المستخدمين
     */
    public function importUsers(Request $request)
    {
        $school = Auth::user()->school;

        $validated = $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120', // 5MB max
            'role' => 'required|in:students,teachers,parents',
        ]);

        try {
            $import = new BulkUsersImport($school->id, $validated['role']);
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $errors = $import->getErrors();

            $message = "تم تسجيل {$successCount} {$validated['role']} بنجاح";

            if (! empty($errors)) {
                $errorCount = count($errors);
                $message .= " مع {$errorCount} خطأ";

                // إضافة تفاصيل الأخطاء
                $errorDetails = [];
                foreach ($errors as $error) {
                    $errorDetails[] = "الصف {$error['row']}: {$error['message']}";
                }

                return redirect()->back()
                    ->with('success', $message)
                    ->with('import_errors', $errorDetails);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('School admin import failed', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الاستيراد');
        }
    }

    /**
     * عرض صفحة إدارة Excel (استيراد وتصدير)
     */
    public function excelManagement()
    {
        $school = Auth::user()->school;

        return view('school-admin.excel-management', compact('school'));
    }

    /**
     * تصدير البيانات إلى Excel
     */
    public function exportData(Request $request)
    {
        $school = Auth::user()->school;
        $type = $request->input('type'); // students, teachers, parents, activities

        $filename = $type . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        switch ($type) {
            case 'students':
                return Excel::download(new StudentsExport($school->id), $filename);
            case 'teachers':
                return Excel::download(new TeachersExport($school->id), $filename);
            case 'parents':
                return Excel::download(new ParentsExport($school->id), $filename);
            case 'activities':
                return Excel::download(new ActivitiesExport($school->id), $filename);
            default:
                return redirect()->back()->with('error', 'نوع البيانات غير صحيح');
        }
    }

    /**
     * صفحة الإحصائيات والتصنيف
     */
    public function statistics()
    {
        $school = Auth::user()->school;
        $now = now();

        // ======== حساب إحصائيات المدرسة ========
        $schoolPoints = DB::table('points')
            ->join('users', 'points.user_id', '=', 'users.id')
            ->where('users.school_id', $school->id)
            ->sum('points.points');

        $monthlySchoolPoints = DB::table('points')
            ->join('users', 'points.user_id', '=', 'users.id')
            ->where('users.school_id', $school->id)
            ->where('points.created_at', '>=', $now->copy()->startOfMonth())
            ->sum('points.points');

        // تصنيف جميع المدارس بالنقاط
        $allSchoolsRanked = DB::table('schools')
            ->select('schools.id', 'schools.name', 'schools.country', 'schools.city')
            ->selectRaw('COALESCE((SELECT SUM(p.points) FROM points p JOIN users u ON p.user_id = u.id WHERE u.school_id = schools.id), 0) as total_points')
            ->orderByDesc('total_points')
            ->get();

        $platformTotal = $allSchoolsRanked->count();
        // التعامل الصحيح مع false عند عدم وجود المدرسة في الترتيب
        // مقارنة بعد التحويل لـ int — يمنع عدم تطابق نوع المعرّف (string DB مقابل int) الذي يُنتج rank=null
        $platformIdx = $allSchoolsRanked->search(fn ($s) => (int) $s->id === (int) $school->id);
        $platformRank = $platformIdx === false ? null : $platformIdx + 1;

        $sameCountry = $allSchoolsRanked->where('country', $school->country);
        $countryTotal = $sameCountry->count();
        $countryIdx = $sameCountry->values()->search(fn ($s) => (int) $s->id === (int) $school->id);
        $countryRank = $countryIdx === false ? null : $countryIdx + 1;

        $sameCity = $allSchoolsRanked->where('city', $school->city);
        $cityTotal = $sameCity->count();
        $cityIdx = $sameCity->values()->search(fn ($s) => (int) $s->id === (int) $school->id);
        $cityRank = $cityIdx === false ? null : $cityIdx + 1;

        // جلب السجل السابق للمقارنة
        $previousSchoolCache = SchoolStatisticsCache::where('entity_type', 'school')
            ->where('entity_id', $school->id)
            ->first();

        $schoolTrend = 'same';
        $schoolRankChange = 0;
        if ($previousSchoolCache && ! is_null($platformRank)) {
            if ($previousSchoolCache->platform_rank && $platformRank < $previousSchoolCache->platform_rank) {
                $schoolTrend = 'up';
                $schoolRankChange = $previousSchoolCache->platform_rank - $platformRank;
            } elseif ($previousSchoolCache->platform_rank && $platformRank > $previousSchoolCache->platform_rank) {
                $schoolTrend = 'down';
                $schoolRankChange = $platformRank - $previousSchoolCache->platform_rank;
            }
        }

        // Badges للمدرسة
        $schoolBadges = [];
        if ($platformTotal > 0 && ! is_null($platformRank) && $platformRank <= max(1, ceil($platformTotal * 0.01))) {
            $schoolBadges[] = ['icon' => '🥇', 'label' => 'ضمن أفضل 1% على المنصة', 'color' => '#f59e0b'];
        }
        if ($cityTotal > 0 && $cityRank === 1) {
            $schoolBadges[] = ['icon' => '🏆', 'label' => 'المتصدر على مستوى المدينة', 'color' => '#8b5cf6'];
        }
        if ($countryTotal > 0 && $countryRank <= 3) {
            $schoolBadges[] = ['icon' => '⭐', 'label' => 'ضمن أفضل 3 على مستوى الدولة', 'color' => '#10b981'];
        }

        // حفظ/تحديث الكاش للمدرسة — فقط مرة واحدة كل 24 ساعة
        // (سابقًا كان يُحدّث في كل page load → trend دائمًا 'same' لأن previous = current)
        $shouldUpdateSnapshot = ! $previousSchoolCache
            || ! $previousSchoolCache->calculated_at
            || $previousSchoolCache->calculated_at->diffInHours(now()) >= 24;

        if ($shouldUpdateSnapshot) {
            SchoolStatisticsCache::updateOrCreate(
                ['entity_type' => 'school', 'entity_id' => $school->id],
                [
                    'school_id' => $school->id,
                    'total_points' => $schoolPoints,
                    'previous_points' => $previousSchoolCache->total_points ?? 0,
                    'points_change' => $schoolPoints - ($previousSchoolCache->total_points ?? 0),
                    'monthly_points' => $monthlySchoolPoints,
                    'platform_rank' => $platformRank,
                    'country_rank' => $countryRank,
                    'city_rank' => $cityRank,
                    'platform_total' => $platformTotal,
                    'country_total' => $countryTotal,
                    'city_total' => $cityTotal,
                    'trend' => $schoolTrend,
                    'rank_change' => $schoolRankChange,
                    'country' => $school->country,
                    'city' => $school->city,
                    'badges' => $schoolBadges,
                    'calculated_at' => $now,
                ],
            );
        }

        $schoolStats = [
            'total_points' => $schoolPoints,
            'monthly_points' => $monthlySchoolPoints,
            'platform_rank' => $platformRank,
            'platform_total' => $platformTotal,
            'country_rank' => $countryRank,
            'country_total' => $countryTotal,
            'city_rank' => $cityRank,
            'city_total' => $cityTotal,
            'trend' => $schoolTrend,
            'rank_change' => $schoolRankChange,
            'badges' => $schoolBadges,
            'top_schools_platform' => $allSchoolsRanked->take(5),
            'top_schools_country' => $sameCountry->values()->take(5),
            'top_schools_city' => $sameCity->values()->take(5),
        ];

        // ======== إحصائيات المعلمين ========
        $allTeachersRanked = DB::table('users')
            ->where('users.role', 'teacher')
            ->where('users.status', 'active')
            ->select('users.id', 'users.name', 'users.school_id')
            ->selectRaw('COALESCE((
                SELECT SUM(p.points) FROM points p 
                JOIN users s ON p.user_id = s.id 
                JOIN classroom_student cs ON s.id = cs.student_id 
                JOIN classrooms c ON cs.classroom_id = c.id 
                WHERE c.teacher_id = users.id
            ), 0) as total_points')
            ->orderByDesc('total_points')
            ->get();

        // Monthly points for teachers
        $monthlyTeachersRanked = DB::table('users')
            ->where('users.role', 'teacher')
            ->where('users.status', 'active')
            ->select('users.id', 'users.name', 'users.school_id')
            ->selectRaw('COALESCE((
                SELECT SUM(p.points) FROM points p 
                JOIN users s ON p.user_id = s.id 
                JOIN classroom_student cs ON s.id = cs.student_id 
                JOIN classrooms c ON cs.classroom_id = c.id 
                WHERE c.teacher_id = users.id AND p.created_at >= ?
            ), 0) as monthly_points', [$now->copy()->startOfMonth()])
            ->orderByDesc('monthly_points')
            ->get();

        $schoolTeachers = $allTeachersRanked->where('school_id', $school->id);
        $topTeacherThisMonth = $monthlyTeachersRanked->where('school_id', $school->id)->first();

        // Teacher badges
        $teacherBadges = [];
        if ($topTeacherThisMonth && $topTeacherThisMonth->monthly_points > 0) {
            $teacherBadges[] = [
                'icon' => '⭐',
                'label' => 'أفضل معلم هذا الشهر: ' . $topTeacherThisMonth->name,
                'color' => '#f59e0b',
            ];
        }

        // Get school's teachers ranked with their city/country context
        $cityTeachers = collect();
        $countryTeachers = collect();

        $schoolCityIds = DB::table('schools')->where('city', $school->city)->pluck('id');
        $schoolCountryIds = DB::table('schools')->where('country', $school->country)->pluck('id');

        $cityTeachers = $allTeachersRanked->whereIn('school_id', $schoolCityIds->toArray())->values();
        $countryTeachers = $allTeachersRanked->whereIn('school_id', $schoolCountryIds->toArray())->values();

        $teacherStats = [
            'school_teachers' => $schoolTeachers->values()->take(10),
            'city_teachers' => $cityTeachers->take(10),
            'country_teachers' => $countryTeachers->take(10),
            'platform_teachers' => $allTeachersRanked->take(10),
            'badges' => $teacherBadges,
            'top_teacher_month' => $topTeacherThisMonth,
        ];

        // ======== إحصائيات الطلاب ========
        $schoolStudentsRanked = User::where('school_id', $school->id)
            ->where('role', 'student')
            ->where('status', 'active')
            ->select('id', 'name', 'school_id')
            ->withSum('points as total_points', 'points')
            ->orderByDesc('total_points')
            ->get();

        // Platform-level student ranking
        $allStudentsCount = User::where('role', 'student')->where('status', 'active')->count();

        // City students
        $cityStudents = User::whereIn('school_id', $schoolCityIds)
            ->where('role', 'student')
            ->where('status', 'active')
            ->select('id', 'name', 'school_id')
            ->withSum('points as total_points', 'points')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();

        // Country students
        $countryStudents = User::whereIn('school_id', $schoolCountryIds)
            ->where('role', 'student')
            ->where('status', 'active')
            ->select('id', 'name', 'school_id')
            ->withSum('points as total_points', 'points')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();

        // Platform students
        $platformStudents = User::where('role', 'student')
            ->where('status', 'active')
            ->select('id', 'name', 'school_id')
            ->withSum('points as total_points', 'points')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();

        // Grade-level rankings
        $gradeGroups = DB::table('classrooms')
            ->where('school_id', $school->id)
            ->whereNotNull('grade_level')
            ->distinct()
            ->pluck('grade_level');

        $gradeRankings = [];
        foreach ($gradeGroups as $grade) {
            $classroomIds = DB::table('classrooms')
                ->where('grade_level', $grade)
                ->pluck('id');

            $studentIds = DB::table('classroom_student')
                ->whereIn('classroom_id', $classroomIds)
                ->pluck('student_id');

            $gradeStudents = User::whereIn('id', $studentIds)
                ->where('role', 'student')
                ->where('status', 'active')
                ->select('id', 'name', 'school_id')
                ->withSum('points as total_points', 'points')
                ->orderByDesc('total_points')
                ->limit(10)
                ->get();

            $gradeRankings[$grade] = $gradeStudents;
        }

        $studentStats = [
            'school_students' => $schoolStudentsRanked->take(10),
            'city_students' => $cityStudents,
            'country_students' => $countryStudents,
            'platform_students' => $platformStudents,
            'grade_rankings' => $gradeRankings,
            'total_school' => $schoolStudentsRanked->count(),
            'total_platform' => $allStudentsCount,
        ];

        return view('school-admin.statistics', compact(
            'school',
            'schoolStats',
            'teacherStats',
            'studentStats',
        ));
    }

    // ==================== إعدادات المدرسة ====================

    /**
     * عرض صفحة إعدادات المدرسة
     */
    public function settings()
    {
        $school = Auth::user()->school;
        $user = Auth::user();

        return view('school-admin.settings', compact('school', 'user'));
    }

    /**
     * تحديث إعدادات المدرسة
     */
    public function updateSettings(Request $request)
    {
        $school = Auth::user()->school;
        $user = Auth::user();

        $section = $request->input('section', 'school');

        if ($section === 'school') {
            $validated = $request->validate([
                'school_name' => 'required|string|max:255',
                'school_description' => 'nullable|string|max:1000',
                'school_phone' => 'nullable|string|max:20',
                'school_email' => 'nullable|email|max:255',
                'school_address' => 'nullable|string|max:500',
            ]);

            $school->update([
                'name' => $validated['school_name'],
                'description' => $validated['school_description'] ?? $school->description,
                'phone' => $validated['school_phone'] ?? $school->phone,
                'email' => $validated['school_email'] ?? $school->email,
                'address' => $validated['school_address'] ?? $school->address,
            ]);

            return redirect()->route('school-admin.settings')->with('success', 'تم تحديث بيانات المدرسة بنجاح! ✅');
        }

        if ($section === 'account') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
            ]);

            if ($request->filled('current_password')) {
                $request->validate([
                    'current_password' => 'required',
                    'new_password' => 'required|min:8|confirmed',
                ]);

                if (! Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة']);
                }

                $user->password = Hash::make($request->new_password);
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'] ?? $user->phone;
            $user->save();

            return redirect()->route('school-admin.settings')->with('success', 'تم تحديث بيانات الحساب بنجاح! ✅');
        }

        return redirect()->route('school-admin.settings')->with('error', 'قسم غير معروف');
    }

    /**
     * تفاعل أولياء الأمور مع طلاب المدرسة (Issue 14/file2 + 109).
     */
    public function parentEngagement()
    {
        $admin = Auth::user();
        $schoolId = $admin->school_id;

        // طلاب المدرسة
        $studentIds = User::where('role', 'student')
            ->where('school_id', $schoolId)
            ->pluck('id');

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

        $praiseByParent = DB::table('parent_praises')
            ->whereIn('parent_id', $parentIds)
            ->whereIn('student_id', $studentIds)
            ->selectRaw('parent_id, COUNT(*) as cnt, MAX(created_at) as last_at')
            ->groupBy('parent_id')->get()->keyBy('parent_id');

        $giftsByParent = DB::table('parent_gifts')
            ->whereIn('parent_id', $parentIds)
            ->whereIn('student_id', $studentIds)
            ->selectRaw('parent_id, COUNT(*) as cnt, COALESCE(SUM(points_cost),0) as pts, MAX(created_at) as last_at')
            ->groupBy('parent_id')->get()->keyBy('parent_id');

        $messagesByParent = DB::table('parent_teacher_messages')
            ->whereIn('parent_id', $parentIds)
            ->selectRaw('parent_id, COUNT(*) as cnt, MAX(created_at) as last_at')
            ->groupBy('parent_id')->get()->keyBy('parent_id');

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

        return view('school-admin.parent-engagement', compact('rows', 'totals'));
    }

    /**
     * تقرير مقارنة استبيان قبلي/بعدي على مستوى المدرسة
     */
    public function surveyComparison($surveyId)
    {
        $user = Auth::user();
        $survey = \App\Models\Survey::findOrFail($surveyId);

        if (! $survey->isAssessment()) {
            return back()->with('error', 'هذا الاستبيان ليس من نوع التقييم القبلي/البعدي');
        }

        // التحقق من الصلاحية: يجب أن يكون الاستبيان موجّه لمدرسة هذا المدير
        // (للاستبيانات الخاصة بمدرسة معينة) أو عامًا
        if ($survey->school_id && $survey->school_id !== $user->school_id) {
            abort(403, 'ليس لديك صلاحية الاطلاع على هذا الاستبيان');
        }

        $survey->load(['lesson.concept.value', 'value', 'linkedSurvey', 'questions']);
        $comparisonData = $survey->getComparisonData($user->school_id);

        if (isset($comparisonData['error'])) {
            return back()->with('error', $comparisonData['error']);
        }

        return view('school-admin.surveys.comparison', compact('survey', 'comparisonData'));
    }

    /**
     * قائمة استبيانات التقييم المتاحة للمقارنة
     */
    public function surveyComparisonsList()
    {
        $user = Auth::user();
        $surveys = \App\Models\Survey::where('survey_type', 'pre_post_assessment')
            ->where(function ($q) use ($user) {
                $q->whereNull('school_id')
                    ->orWhere('school_id', $user->school_id);
            })
            ->where('assessment_phase', 'post')
            ->with(['lesson.concept.value', 'value', 'linkedSurvey'])
            ->latest()
            ->paginate(20);

        return view('school-admin.surveys.comparisons-list', compact('surveys'));
    }
}
