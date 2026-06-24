<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\UserRole;
use App\Models\BulkMessage;
use App\Models\BulkMessageRecipient;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BulkMessageController extends Controller
{
    /**
     * يتحقق أن المستخدم مخوّل لإرسال الرسائل الجماعية (super_admin أو school_admin فقط).
     * أمان حرج: المجموعة كانت متاحة لأي مستخدم مسجّل (Issue حرج).
     */
    private function authorizeBulkSender(): \App\Models\User
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['super_admin', 'school_admin'], true)) {
            abort(403, 'غير مصرّح لك بإرسال الرسائل الجماعية');
        }
        return $user;
    }

    /**
     * أنواع المستلمين المسموحة لمدير المدرسة (داخل مدرسته فقط).
     */
    private const SCHOOL_SCOPED_TYPES = ['school_teachers', 'school_parents', 'school_students', 'school_all'];

    /**
     * عرض صفحة إرسال الرسائل الجماعية
     */
    public function index()
    {
        $this->authorizeBulkSender();

        $sentMessages = BulkMessage::where('sender_id', Auth::id())
            ->withCount('recipients')
            ->with(['school', 'recipients'])
            ->latest()
            ->paginate(20);

        // إحصائيات
        $stats = [
            'total_sent' => BulkMessage::where('sender_id', Auth::id())->count(),
            'total_recipients' => BulkMessageRecipient::whereHas('bulkMessage', function ($q) {
                $q->where('sender_id', Auth::id());
            })->count(),
            'total_read' => BulkMessageRecipient::whereHas('bulkMessage', function ($q) {
                $q->where('sender_id', Auth::id());
            })->whereNotNull('read_at')->count(),
            'this_month' => BulkMessage::where('sender_id', Auth::id())
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('messages.bulk.index', compact('sentMessages', 'stats'));
    }

    /**
     * عرض نموذج إرسال رسالة جماعية
     */
    public function create()
    {
        $sender = $this->authorizeBulkSender();
        $schools = School::where('status', 'active')
            ->withCount(['students', 'teachers', 'users'])
            ->orderBy('name')
            ->get();

        // إحصائيات المستلمين
        $recipientCounts = [
            'teachers'      => User::where('role', UserRole::Teacher->value)->where('status', 'active')->count(),
            'students'      => User::where('role', UserRole::Student->value)->where('status', 'active')->count(),
            'parents'       => User::where('role', UserRole::Parent->value)->where('status', 'active')->count(),
            'school_admins' => User::where('role', UserRole::SchoolAdmin->value)->where('status', 'active')->count(),
            'all'           => User::where('status', 'active')->count(),
        ];

        return view('messages.bulk.create', compact('schools', 'recipientCounts'));
    }

    /**
     * إرسال رسالة جماعية
     */
    public function send(Request $request)
    {
        $sender = $this->authorizeBulkSender();

        $validated = $request->validate([
            'recipient_type' => 'required|in:teacher,parent,student,school_admin,all,school_teachers,school_parents,school_students,school_all',
            'school_id' => 'nullable|required_if:recipient_type,school_teachers,school_parents,school_students,school_all|exists:schools,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        // حصر النطاق: مدير المدرسة يُرسل داخل مدرسته فقط وبأنواع school_* حصراً
        if ($sender->role === 'school_admin') {
            if (!in_array($validated['recipient_type'], self::SCHOOL_SCOPED_TYPES, true)) {
                return back()->with('error', 'يمكنك الإرسال إلى أعضاء مدرستك فقط')->withInput();
            }
            $validated['school_id'] = $sender->school_id; // فرض مدرسة المُرسِل — يمنع استهداف مدارس أخرى
        }

        // rate limit للمُرسِل: حد أقصى 5 رسائل جماعية في الساعة لمنع الإرسال المتكرر بالخطأ
        $rateKey = 'bulk_msg:sender:' . Auth::id();
        $sentLastHour = (int) \Illuminate\Support\Facades\Cache::get($rateKey, 0);
        if ($sentLastHour >= 5) {
            return back()
                ->with('error', 'تجاوزت الحد المسموح به (5 رسائل جماعية في الساعة). حاول لاحقًا.')
                ->withInput();
        }
        \Illuminate\Support\Facades\Cache::put($rateKey, $sentLastHour + 1, now()->addHour());

        DB::beginTransaction();
        try {
            // إنشاء الرسالة الجماعية
            $bulkMessage = BulkMessage::create([
                'sender_id' => Auth::id(),
                'recipient_type' => $validated['recipient_type'],
                'school_id' => $validated['school_id'] ?? null,
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'sent_at' => now()
            ]);

            // تحديد المستلمين
            $recipients = $this->getRecipients($validated['recipient_type'], $validated['school_id'] ?? null);

            if ($recipients->isEmpty()) {
                DB::rollBack();
                return back()->with('error', 'لا يوجد مستلمين لهذه الفئة المحددة')->withInput();
            }

            // إرسال لكل مستلم باستخدام insert batch للأداء
            $recipientData = $recipients->map(function ($recipient) use ($bulkMessage) {
                return [
                    'bulk_message_id' => $bulkMessage->id,
                    'user_id' => $recipient->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            // Insert in chunks for better performance
            foreach (array_chunk($recipientData, 500) as $chunk) {
                BulkMessageRecipient::insert($chunk);
            }

            DB::commit();
            return redirect()->route('messages.bulk.index')
                ->with('success', "تم إرسال الرسالة بنجاح إلى {$recipients->count()} مستلم");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء الإرسال: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * تحديد المستلمين بناءً على النوع والمدرسة
     */
    private function getRecipients($type, $schoolId = null)
    {
        $query = User::where('status', 'active');

        // قائمة كل الأدوار غير-super_admin (لـ "all")
        $allRoles = [
            UserRole::Teacher->value,
            UserRole::Parent->value,
            UserRole::Student->value,
            UserRole::SchoolAdmin->value,
        ];

        switch ($type) {
            case 'teacher':
                return $query->where('role', UserRole::Teacher->value)->get();

            case 'parent':
                return $query->where('role', UserRole::Parent->value)->get();

            case 'student':
                return $query->where('role', UserRole::Student->value)->get();

            case 'school_admin':
                return $query->where('role', UserRole::SchoolAdmin->value)->get();

            case 'all':
                return $query->whereIn('role', $allRoles)->get();

            case 'school_teachers':
                return $query->where('role', UserRole::Teacher->value)->where('school_id', $schoolId)->get();

            case 'school_parents':
                return $query->where('role', UserRole::Parent->value)->where('school_id', $schoolId)->get();

            case 'school_students':
                return $query->where('role', UserRole::Student->value)->where('school_id', $schoolId)->get();

            case 'school_all':
                return $query->where('school_id', $schoolId)
                    ->whereIn('role', $allRoles)
                    ->get();

            default:
                return collect();
        }
    }

    /**
     * عرض الرسائل المستلمة
     */
    public function inbox()
    {
        $messages = BulkMessageRecipient::where('user_id', Auth::id())
            ->with('bulkMessage.sender')
            ->latest()
            ->paginate(20);

        $unreadCount = BulkMessageRecipient::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return view('messages.bulk.inbox', compact('messages', 'unreadCount'));
    }

    /**
     * قراءة رسالة
     */
    public function markAsRead($id)
    {
        $recipient = BulkMessageRecipient::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $recipient->update(['read_at' => now()]);

        return back()->with('success', 'تم تحديد الرسالة كمقروءة');
    }

    /**
     * API: الحصول على عدد المستلمين حسب النوع والمدرسة
     */
    public function getRecipientCount(Request $request)
    {
        $sender = $this->authorizeBulkSender();
        $type = $request->get('type');
        $schoolId = $request->get('school_id');

        // نفس حصر مدير المدرسة المطبَّق في send() حتى تتطابق المعاينة مع الإرسال الفعلي
        if ($sender->role === 'school_admin') {
            if (!in_array($type, self::SCHOOL_SCOPED_TYPES, true)) {
                return response()->json(['count' => 0]);
            }
            $schoolId = $sender->school_id;
        }

        $count = $this->getRecipients($type, $schoolId)->count();

        return response()->json(['count' => $count]);
    }
}
