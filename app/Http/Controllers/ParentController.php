<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\ActivitySubmission;
use App\Models\User;

class ParentController extends Controller
{
    /**
     * لوحة التحكم لولي الأمر - محسّنة
     */
    public function dashboard()
    {
        $user = Auth::user();
        $school = $user->school;
        
        // جلب الأبناء مع جميع البيانات - استعلام واحد محسّن
        $children = $user->children()
            ->with(['school', 'classrooms', 'badges', 'streak'])
            ->withSum('points as total_points', 'points')
            ->withSum('coins as total_coins', 'coins')
            ->withCount([
                'activitySubmissions',
                'activitySubmissions as completed_activities_count' => function($q) {
                    $q->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES);
                },
                'activitySubmissions as pending_activities_count' => function($q) {
                    $q->where('status', 'pending');
                }
            ])
            ->withAvg('activitySubmissions as average_score', 'score')
            ->get();
        
        // آخر 5 أنشطة لكل ابن - استعلام واحد مع Eager Loading
        $childrenIds = $children->pluck('id');
        $recentActivitiesByChild = ActivitySubmission::whereIn('student_id', $childrenIds)
            ->with(['activity.lesson'])
            ->latest()
            ->get()
            ->groupBy('student_id')
            ->map(function($activities) {
                return $activities->take(5);
            });
        
        // إلحاق الأنشطة بكل ابن
        foreach ($children as $child) {
            $child->completed_activities = $child->completed_activities_count ?? 0;
            $child->pending_activities = $child->pending_activities_count ?? 0;
            $child->recent_activities = $recentActivitiesByChild->get($child->id, collect());
        }
        
        // إحصائيات عامة لجميع الأبناء
        $stats = [
            'total_children' => $children->count(),
            'total_points' => $children->sum('total_points'),
            'total_coins' => $children->sum('total_coins'),
            'total_badges' => $children->sum(fn($child) => $child->badges->count()),
            'total_completed' => $children->sum('completed_activities'),
        ];
        
        return view('parent.dashboard', compact('user', 'school', 'children', 'stats'));
    }

    /**
     * عرض تفاصيل ابن محدد
     */
    public function childDetail($id)
    {
        $user = Auth::user();
        
        // التأكد من أن الابن تابع لهذا الولي
        $child = $user->children()
            ->with(['school', 'classrooms', 'badges', 'streak'])
            ->findOrFail($id);

        // إحصائيات الابن
        $stats = [
            'total_points' => $child->points()->sum('points'),
            'total_coins' => $child->coins()->sum('coins'),
            'completed_activities' => ActivitySubmission::where('student_id', $child->id)
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                ->count(),
            'average_score' => ActivitySubmission::where('student_id', $child->id)
                ->whereNotNull('score')
                ->avg('score') ?? 0,
        ];

        // آخر 10 أنشطة
        $recentActivities = ActivitySubmission::where('student_id', $child->id)
            ->with(['activity.lesson'])
            ->latest()
            ->take(10)
            ->get();

        // الشارات
        $badges = $child->badges;

        // السلسلة
        $streak = $child->streak;

        // المعلمون
        $teachers = User::where('role', 'teacher')
            ->whereHas('classrooms', function($query) use ($child) {
                $query->whereHas('students', function($q) use ($child) {
                    $q->where('user_id', $child->id);
                });
            })
            ->with('classrooms')
            ->get();

        // بيانات الرسم البياني - آخر 30 يوم
        $chartData = $this->getChildProgressChartData($child->id);

        return view('parent.child-detail', compact(
            'child',
            'stats',
            'recentActivities',
            'badges',
            'streak',
            'teachers',
            'chartData'
        ));
    }

    /**
     * بيانات الرسم البياني للتقدم
     */
    private function getChildProgressChartData($studentId)
    {
        $data = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d/m');
            
            $points = ActivitySubmission::where('student_id', $studentId)
                ->whereDate('created_at', $date)
                ->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)
                ->count() * 10; // افتراض 10 نقاط لكل نشاط
                
            $data[] = $points;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * عرض صفحة المراسلات مع المعلمين
     */
    public function messages()
    {
        $user = Auth::user();
        
        // جلب قائمة المعلمين الذين لديهم أطفال الولي
        $teachers = User::where('role', 'teacher')
            ->where('school_id', $user->school_id)
            ->whereHas('teachingClassrooms.students', function($q) use ($user) {
                $q->whereIn('users.id', $user->children()->pluck('users.id'));
            })
            ->select('id', 'name', 'email')
            ->get();

        // محادثة واحدة لكل (معلم، طالب): آخر رسالة فقط بدل صف لكل رسالة
        $latestIds = \App\Models\ParentTeacherMessage::where('parent_id', $user->id)
            ->selectRaw('MAX(id) as id')
            ->groupBy('teacher_id', 'student_id')
            ->pluck('id');

        $conversations = \App\Models\ParentTeacherMessage::whereIn('id', $latestIds)
            ->with(['teacher:id,name', 'student:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('parent.messages', compact('teachers', 'conversations'));
    }

    /**
     * عرض محادثة محددة مع معلم
     */
    public function getConversation(Request $request)
    {
        $user = Auth::user();
        
        $messages = \App\Models\ParentTeacherMessage::where('parent_id', $user->id)
        ->where('teacher_id', $request->teacher_id)
        ->when($request->student_id, function($q) use ($request) {
            $q->where('student_id', $request->student_id);
        })
        ->with(['teacher:id,name', 'student:id,name'])
        ->orderBy('created_at', 'asc')
        ->get();

        // تحديد الرسائل كمقروءة
        \App\Models\ParentTeacherMessage::where('parent_id', $user->id)
            ->where('teacher_id', $request->teacher_id)
            ->where('sender_type', 'teacher')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json($messages);
    }

    /**
     * إرسال رسالة جديدة للمعلم
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'teacher_id' => 'required|exists:users,id',
                'student_id' => 'nullable|exists:users,id',
                'message' => 'required|string|max:1000',
            ]);

            $user = Auth::user();

            // التحقق من أن المعلم في نفس المدرسة
            $teacherValid = User::where('id', $request->teacher_id)
                ->where('role', 'teacher')
                ->where('school_id', $user->school_id)
                ->exists();

            if (!$teacherValid) {
                return response()->json(['success' => false, 'error' => 'المعلم غير صالح'], 403);
            }

            // التحقق أن الطالب (إن حُدّد) من أبناء ولي الأمر — منع ربط رسالة بطالب ليس ابنه
            if ($request->student_id && !$user->children()->where('users.id', $request->student_id)->exists()) {
                return response()->json(['success' => false, 'error' => 'غير مصرح لك بهذا الطالب'], 403);
            }

            $message = \App\Models\ParentTeacherMessage::create([
                'parent_id' => $user->id,
                'teacher_id' => $request->teacher_id,
                'student_id' => $request->student_id,
                'message' => $request->message,
                'sender_type' => 'parent',
            ]);

            // إرسال إشعار للمعلم
            try {
                \App\Services\NotificationService::create(
                    $request->teacher_id,
                    'new_parent_message',
                    '💬 رسالة جديدة من ولي أمر',
                    "لديك رسالة جديدة من {$user->name}",
                    [],
                    route('teacher.messages')
                );
            } catch (\Exception $e) {
                Log::error('Parent message notification failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => $message->load(['teacher:id,name', 'student:id,name'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Parent sendMessage error: ' . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile());
            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ أثناء إرسال الرسالة'
            ], 500);
        }
    }

    // ==================== نظام النقاط والمدح ====================

    /**
     * إضافة نقاط لولي الأمر
     */
    private function givePoints($parentId, $points, $reason, $referenceType = null, $referenceId = null)
    {
        \App\Models\ParentPoint::create([
            'parent_id' => $parentId,
            'points' => $points,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId
        ]);
    }

    /**
     * مدح الطالب - رسالة تحفيزية
     */
    public function praiseChild(Request $request, $childId)
    {
        try {
            $parent = Auth::user();
            $message = $request->input('praise_message', '');
            $type = $request->input('praise_type', 'encouragement');
            // حصر النوع بقيم enum المسموحة (منع قيم خارج القيد)
            $type = in_array($type, ['encouragement', 'achievement', 'behavior', 'custom'], true) ? $type : 'encouragement';

            if (empty(trim($message))) {
                return response()->json(['success' => false, 'error' => 'الرجاء كتابة رسالة تحفيزية'], 422);
            }

            // التحقق من أن الطفل تابع لولي الأمر
            $child = $parent->children()->where('users.id', $childId)->first();
            if (!$child) {
                return response()->json(['success' => false, 'error' => 'غير مصرح لك'], 403);
            }

            // P2-E + race: التحقق من الحد + إنشاء praise + نقطة الطالب — كله في معاملة
            // لمنع تجاوز الحد اليومي عبر double-click متزامن
            $rateLimitHit = DB::transaction(function () use ($parent, $childId, $message, $type) {
                if (Schema::hasTable('parent_praises')) {
                    $todayCount = DB::table('parent_praises')
                        ->where('parent_id', $parent->id)
                        ->where('student_id', (int) $childId)
                        ->whereDate('created_at', now()->toDateString())
                        ->lockForUpdate()
                        ->count();
                    if ($todayCount >= 5) {
                        return true;
                    }

                    // نُدرج هنا داخل المعاملة لمنع race بين الفحص و الإدراج
                    DB::table('parent_praises')->insert([
                        'parent_id' => $parent->id,
                        'student_id' => (int) $childId,
                        'praise_message' => $message,
                        'praise_type' => $type,
                        'points_awarded' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // منح نقطة الطالب داخل فرع الجدول فقط — حتى لا يُتجاوز الحد اليومي عند غياب الجدول
                    \App\Models\Point::create([
                        'user_id' => (int) $childId,
                        'points' => 5,
                        'reason' => 'تشجيع من ولي الأمر: ' . mb_substr($message, 0, 100),
                        'reference_type' => 'parent_praise',
                        'reference_id' => null
                    ]);
                }

                return false;
            }, 3);

            if ($rateLimitHit) {
                return response()->json([
                    'success' => false,
                    'error'   => 'وصلت الحد اليومي لرسائل التشجيع (5 رسائل). جرّب مرة أخرى غداً.'
                ], 429);
            }

            // ملاحظة: حفظ parent_praises تم داخل المعاملة أعلاه لمنع race مع rate-limit

            // محاولة نقاط ولي الأمر (اختياري)
            try {
                if (Schema::hasTable('parent_points')) {
                    DB::table('parent_points')->insert([
                        'parent_id' => $parent->id,
                        'points' => 5,
                        'reason' => 'مدح الطالب',
                        'reference_type' => 'parent_praise',
                        'reference_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // تجاهل
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال التشجيع بنجاح! حصل ' . $child->name . ' على 5 نقاط ✨'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('praiseChild fatal: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in ' . $e->getFile());
            return response()->json([
                'success' => false, 
                'error' => 'حدث خطأ'
            ], 500);
        }
    }

    /**
     * إرسال هدية للطالب
     */
    public function sendGift(Request $request, $childId)
    {
        $validated = $request->validate([
            'gift_type' => 'required|string',
            'gift_message' => 'nullable|string|max:500'
        ]);

        // التحقق من ملكية الطالب عبر علاقة parent_student pivot (وليس عمود parent_id الذي لا يوجد)
        $child = Auth::user()->children()->where('users.id', $childId)->first();

        if (!$child) {
            return back()->with('error', 'غير مصرح لك بإرسال هدية لهذا الطالب');
        }

        // P2-E: حد أقصى 3 هدايا لنفس الابن في اليوم
        $todayGifts = \App\Models\ParentGift::where('parent_id', Auth::id())
            ->where('student_id', $childId)
            ->whereDate('created_at', now()->toDateString())
            ->count();
        if ($todayGifts >= 3) {
            return back()->with('error', 'وصلت الحد اليومي للهدايا (3 هدايا). جرّب مرة أخرى غداً.');
        }

        \DB::beginTransaction();
        try {
            $gift = \App\Models\ParentGift::create([
                'parent_id' => Auth::id(),
                'student_id' => $childId,
                'gift_type' => $validated['gift_type'],
                'gift_message' => $validated['gift_message'],
                'points_cost' => 10
            ]);

            $this->givePoints(Auth::id(), 10, 'إرسال هدية للطالب', 'parent_gift', $gift->id);

            \App\Models\Point::create([
                'user_id' => $childId,
                'points' => 10,
                'reason' => 'هدية من ولي الأمر',
                'reference_type' => 'parent_gift',
                'reference_id' => $gift->id
            ]);

            \DB::commit();
            return back()->with('success', 'تم إرسال الهدية بنجاح وحصلت على 10 نقاط');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Parent sendGift failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ');
        }
    }

    // ==================== الأنشطة العائلية ====================

    public function pendingFamilyActivities()
    {
        $submissions = \App\Models\FamilyActivitySubmission::where('parent_id', Auth::id())
            ->where('status', 'pending')
            ->with(['activity', 'student'])
            ->latest()
            ->paginate(20);

        return view('parent.family-activities.pending', compact('submissions'));
    }

    public function approveFamilyActivity(Request $request, $submissionId)
    {
        $validated = $request->validate([
            'praise' => 'nullable|string',
            'custom_praise' => 'nullable|string|max:1000',
            'reject' => 'nullable|boolean',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        try {
            return \DB::transaction(function () use ($validated, $submissionId) {
                // قفل صفّي يمنع التكرار/السباق (منح نقاط متعدد) — Issue idempotency
                $submission = \App\Models\FamilyActivitySubmission::lockForUpdate()->findOrFail($submissionId);

                if ($submission->parent_id !== Auth::id()) {
                    return back()->with('error', 'غير مصرح لك بمعالجة هذا النشاط');
                }

                // حماية ضد التكرار: إن عولج مسبقاً (اعتُمد أو رُفض) لا نمنح نقاطاً ثانيةً
                if (($submission->status ?? 'pending') !== 'pending') {
                    return back()->with('info', 'تمت معالجة هذا النشاط مسبقاً');
                }

                // ===== فرع الرفض: لا نقاط، فقط تسجيل الحالة والسبب (Issue حرج) =====
                if (!empty($validated['reject'])) {
                    $submission->update([
                        'status' => 'rejected',
                        'parent_approved' => false,
                        'rejection_reason' => $validated['rejection_reason'] ?? null,
                    ]);
                    return back()->with('success', 'تم رفض النشاط. يمكن للطالب إعادة المحاولة.');
                }

                // ===== فرع الموافقة: منح النقاط مرة واحدة =====
                // "custom" قيمة حارسة لا نص مدح فعلي — نستخدم الرسالة المخصّصة عندها
                $praiseValue = ($validated['praise'] ?? null) === 'custom'
                    ? ($validated['custom_praise'] ?? null)
                    : ($validated['custom_praise'] ?? ($validated['praise'] ?? null));

                $submission->update([
                    'parent_approved' => true,
                    'status' => 'approved',
                    'parent_approved_at' => now(),
                    'parent_praise' => $praiseValue,
                ]);

                \App\Models\Point::create([
                    'user_id' => $submission->student_id,
                    'points' => 20,
                    'reason' => 'إكمال نشاط عائلي',
                    'reference_type' => 'family_activity',
                    'reference_id' => $submission->id,
                ]);

                $this->givePoints(Auth::id(), 10, 'الموافقة على نشاط عائلي', 'family_activity', $submission->id);

                return back()->with('success', 'تم الموافقة على النشاط بنجاح! حصل الطالب على 20 نقطة وحصلت على 10 نقاط');
            }, 3);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('approveFamilyActivity failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ');
        }
    }
}

