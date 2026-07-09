<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivitySubmission;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessagesController extends Controller
{
    /**
     * عرض صفحة الرسائل
     */
    public function index()
    {
        $user = Auth::user();

        // الحصول على المحادثات الخاصة بالمستخدم
        $conversations = Conversation::where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->with(['user1', 'user2', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        // الحصول على المستخدمين المتاحين للمراسلة حسب الصلاحيات
        $availableUsers = $this->getAvailableUsers($user);

        // إذا كان المستخدم طالباً، نحتاج stats و streak للتخطيط
        $data = ['conversations' => $conversations, 'availableUsers' => $availableUsers];
        if ($user->role === 'student') {
            $user->load('streak');
            $stats = $this->getStudentStats($user);
            $streak = $user->streak;
            $data['stats'] = $stats;
            $data['streak'] = $streak;
        }

        // توجيه كل دور لصفحته الخاصة
        $viewPath = match ($user->role) {
            'super_admin' => 'messages.admin.index',
            'school_admin' => 'messages.school-admin.index',
            'teacher' => 'messages.index', // موحّد مع الوليّ/الطالب على تصميم ملء-الصفحة المشترك؛ كان messages.teacher.index يُضمّن (@include) هذا الملفّ المُمتِدّ لتخطيط فيُنشئ تخطيطاً متداخلاً (قائمتان جانبيّتان)
            'parent' => 'messages.index', // نفس التصميم الافتراضي
            'student' => 'messages.index', // نفس التصميم الافتراضي
            default => 'messages.index'
        };

        return view($viewPath, $data);
    }

    /**
     * الحصول على المستخدمين المتاحين للمراسلة حسب الدور
     */
    private function getAvailableUsers($user)
    {
        $query = User::where('id', '!=', $user->id);

        if ($user->role === 'super_admin') {
            // السوبر أدمن يستطيع مراسلة الجميع
            return $query->orderBy('name')->get();
        }

        if ($user->role === 'school_admin') {
            // مدير المدرسة يستطيع مراسلة من في مدرسته فقط
            $schoolId = $user->school_id;

            return $query->where('school_id', $schoolId)
                ->whereIn('role', ['teacher', 'parent', 'student'])
                ->orderBy('name')
                ->get();
        }

        if ($user->role === 'teacher') {
            // المدرس يستطيع مراسلة:
            // 1. طلاب فصوله
            // 2. أولياء أمور طلاب فصوله
            // 3. مدراء مدرسته
            $studentIds = $user->teachingClassrooms()
                ->with('students')
                ->get()
                ->pluck('students')
                ->flatten()
                ->pluck('id')
                ->unique();

            $parentIds = User::whereIn('id', function ($query) use ($studentIds) {
                $query->select('parent_id')
                    ->from('parent_student')
                    ->whereIn('student_id', $studentIds);
            })->pluck('id');

            $adminIds = User::where('school_id', $user->school_id)
                ->where('role', UserRole::SchoolAdmin->value)
                ->pluck('id');

            $allIds = $studentIds->merge($parentIds)->merge($adminIds)->unique();

            return User::whereIn('id', $allIds)->orderBy('name')->get();
        }

        if ($user->role === 'parent') {
            // ولي الأمر يستطيع مراسلة:
            // 1. مدرسي أبنائه
            // 2. مدراء مدرسة أبنائه
            $children = $user->children; // العلاقة children يجب أن تكون موجودة في User model

            $teacherIds = collect();
            foreach ($children as $child) {
                // جلب جميع الفصول النشطة للطفل
                $classrooms = $child->classrooms()->wherePivot('status', 'active')->get();
                foreach ($classrooms as $classroom) {
                    if ($classroom->teacher_id) {
                        $teacherIds->push($classroom->teacher_id);
                    }
                }
            }

            $adminIds = User::where('school_id', $user->school_id)
                ->where('role', UserRole::SchoolAdmin->value)
                ->pluck('id');

            $allIds = $teacherIds->merge($adminIds)->unique();

            return User::whereIn('id', $allIds)->orderBy('name')->get();
        }

        if ($user->role === 'student') {
            // الطالب يستطيع مراسلة:
            // 1. مدرسي فصوله
            // 2. مدراء مدرسته
            $classrooms = $user->classrooms()
                ->wherePivot('status', 'active')
                ->with('teacher')
                ->get();

            $teacherIds = collect();
            foreach ($classrooms as $classroom) {
                if ($classroom->teacher_id && $classroom->teacher) {
                    $teacherIds->push($classroom->teacher_id);
                }
            }

            $adminIds = User::where('school_id', $user->school_id)
                ->where('role', UserRole::SchoolAdmin->value)
                ->pluck('id');

            $allIds = $teacherIds->merge($adminIds)->unique();

            return User::whereIn('id', $allIds)->orderBy('name')->get();
        }

        return collect();
    }

    /**
     * جلب محادثة عبر AJAX
     */
    public function getConversation($userId)
    {
        $currentUser = Auth::user();
        $otherUser = User::findOrFail($userId);

        // التحقق من صلاحية المراسلة
        if (! $this->canMessage($currentUser, $otherUser)) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        // الحصول على المحادثة أو إنشاؤها
        $conversation = Conversation::findOrCreate($currentUser->id, $otherUser->id);

        // جلب الرسائل
        $messages = $conversation->messages()->with(['sender:id,name,avatar,role', 'receiver:id,name,avatar,role'])->get();

        // تحديد جميع الرسائل كمقروءة
        $conversation->messages()
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'messages' => $messages,
            'otherUser' => ['id' => $otherUser->id, 'name' => $otherUser->name, 'avatar' => $otherUser->avatar, 'avatar_url' => $otherUser->avatar_url, 'role' => $otherUser->role],
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * عرض محادثة محددة
     */
    public function show($userId)
    {
        $currentUser = Auth::user();
        $otherUser = User::findOrFail($userId);

        // التحقق من صلاحية المراسلة
        if (! $this->canMessage($currentUser, $otherUser)) {
            abort(403, 'ليس لديك صلاحية لمراسلة هذا المستخدم');
        }

        // الحصول على المحادثة أو إنشاؤها
        $conversation = Conversation::findOrCreate($currentUser->id, $otherUser->id);

        // جلب الرسائل
        $messages = $conversation->messages()->with(['sender:id,name,avatar,role', 'receiver:id,name,avatar,role'])->get();

        // تحديد جميع الرسائل كمقروءة
        $conversation->messages()
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        // إذا كان المستخدم طالباً، نحتاج stats و streak للتخطيط
        $data = ['conversation' => $conversation, 'messages' => $messages, 'otherUser' => $otherUser];
        if ($currentUser->role === 'student') {
            $currentUser->load('streak');
            $stats = $this->getStudentStats($currentUser);
            $streak = $currentUser->streak;
            $data['stats'] = $stats;
            $data['streak'] = $streak;
        }

        return view('messages.show', $data);
    }

    /**
     * إرسال رسالة
     */
    public function send(Request $request)
    {
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'message' => 'required|string|max:5000',
            ]);

            $sender = Auth::user();
            $receiver = User::findOrFail($request->receiver_id);

            // التحقق من صلاحية المراسلة
            if (! $this->canMessage($sender, $receiver)) {
                return response()->json(['error' => 'ليس لديك صلاحية لمراسلة هذا المستخدم'], 403);
            }

            // الحصول على المحادثة أو إنشاؤها
            $conversation = Conversation::findOrCreate($sender->id, $receiver->id);

            // إنشاء الرسالة — نطبّع المحتوى قبل التخزين (يزيل العُقد الفارغة/الأحرف الخفية
            // التي يُدخلها المحرّر فتُضخّم الفقاعة)، مع تراجع للخام لو أنتج التطبيع فراغاً.
            $normalized = normalize_message_html($request->message);
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'message' => $normalized !== '' ? $normalized : $request->message,
            ]);

            // تحديث وقت آخر رسالة
            $conversation->update(['last_message_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => $message->load('sender:id,name,avatar,role'),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'يرجى التأكد من صحة البيانات المدخلة',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Message send failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ أثناء إرسال الرسالة',
            ], 500);
        }
    }

    /**
     * التحقق من صلاحية المراسلة بين مستخدمين
     */
    private function canMessage($user1, $user2)
    {
        // السوبر أدمن يستطيع مراسلة الجميع
        if ($user1->role === 'super_admin') {
            return true;
        }

        // الجميع يستطيع الرد على السوبر أدمن (فتح محادثة موجودة)
        if ($user2->role === 'super_admin') {
            return true;
        }

        // المستخدمان يجب أن يكونا في نفس المدرسة
        if ($user1->school_id !== $user2->school_id) {
            return false;
        }

        // التحقق من الصلاحيات حسب الدور
        $availableUsers = $this->getAvailableUsers($user1);

        return $availableUsers->contains('id', $user2->id);
    }

    /**
     * عدد الرسائل غير المقروءة
     */
    public function unreadCount()
    {
        $userId = Auth::id();

        $count = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * جلب الرسائل الجديدة في محادثة معينة
     */
    public function checkNewMessages($userId)
    {
        $currentUser = Auth::user();
        $otherUser = User::findOrFail($userId);

        // التحقق من صلاحية المراسلة
        if (! $this->canMessage($currentUser, $otherUser)) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $conversation = Conversation::findOrCreate($currentUser->id, $otherUser->id);

        // جلب الرسائل الجديدة فقط
        $newMessages = $conversation->messages()
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->with('sender:id,name,avatar,role')
            ->get();

        // تحديثها كمقروءة
        if ($newMessages->isNotEmpty()) {
            $conversation->messages()
                ->where('receiver_id', $currentUser->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json([
            'messages' => $newMessages,
            'hasNew' => $newMessages->isNotEmpty(),
        ]);
    }

    /**
     * جلب جميع المحادثات الجديدة
     */
    public function checkAllNewMessages()
    {
        $userId = Auth::id();

        // جلب المحادثات التي لديها رسائل جديدة
        $newMessagesCount = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->selectRaw('conversation_id, COUNT(*) as count, MAX(created_at) as last_message_at')
            ->groupBy('conversation_id')
            ->get();

        $notifications = [];
        foreach ($newMessagesCount as $item) {
            $message = Message::where('conversation_id', $item->conversation_id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->with('sender:id,name,avatar,role')
                ->latest()
                ->first();

            if ($message) {
                $notifications[] = [
                    'conversation_id' => $item->conversation_id,
                    'message_id' => $message->id,
                    'sender' => $message->sender,
                    'message' => $message->message,
                    'count' => $item->count,
                    'created_at' => $message->created_at,
                ];
            }
        }

        return response()->json([
            'hasNew' => ! empty($notifications),
            'total' => count($notifications),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Helper method للحصول على إحصائيات الطالب (للطلاب فقط)
     */
    private function getStudentStats($user)
    {
        $submissionStats = ActivitySubmission::where('student_id', $user->id)
            ->selectRaw("
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                AVG(CASE WHEN score IS NOT NULL THEN score END) as avg_score,
                SUM(CASE WHEN status = 'completed' AND DATE(created_at) = DATE('now') THEN 1 ELSE 0 END) as completed_today
            ")
            ->first();

        $totals = DB::table('users')
            ->where('users.id', $user->id)
            ->leftJoin('points', 'users.id', '=', 'points.user_id')
            ->leftJoin('coins', 'users.id', '=', 'coins.user_id')
            ->selectRaw('COALESCE(SUM(points.points), 0) as total_points, COALESCE(SUM(coins.coins), 0) as total_coins')
            ->first();

        // Get streak with null check
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
            \Log::error('Streak error for user ' . $user->id . ': ' . $e->getMessage());
            $currentStreak = 0;
        }

        return [
            'total_points' => (int) ($totals->total_points ?? 0),
            'total_coins' => (int) ($totals->total_coins ?? 0),
            'total_badges' => $user->badges()->count(),
            'current_streak' => (int) $currentStreak,
            'completed_activities' => (int) ($submissionStats->completed_count ?? 0),
            'pending_activities' => (int) ($submissionStats->pending_count ?? 0),
            'average_score' => round($submissionStats->avg_score ?? 0, 1),
            'completed_today' => (int) ($submissionStats->completed_today ?? 0),
        ];
    }

    /**
     * رفع صورة من محرر الرسائل
     */
    public function chatUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        $path = $request->file('file')->store('chat-images', 'public');

        return response()->json([
            'success' => true,
            'url' => asset('storage/app/public/data/' . $path),
        ]);
    }
}
