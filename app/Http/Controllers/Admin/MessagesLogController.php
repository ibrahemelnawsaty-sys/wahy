<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessagesLogController extends Controller
{
    /**
     * عرض سجل جميع الرسائل للأدمن
     */
    public function index(Request $request)
    {
        $query = Message::with(['sender', 'receiver', 'conversation']);

        // البحث حسب المرسل
        if ($request->filled('sender_id')) {
            $query->where('sender_id', $request->sender_id);
        }

        // البحث حسب المستقبل
        if ($request->filled('receiver_id')) {
            $query->where('receiver_id', $request->receiver_id);
        }

        // البحث في محتوى الرسالة
        if ($request->filled('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        // فلترة حسب حالة القراءة
        if ($request->filled('read_status')) {
            if ($request->read_status === 'read') {
                $query->where('is_read', true);
            } elseif ($request->read_status === 'unread') {
                $query->where('is_read', false);
            }
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // فلترة حسب المحادثة
        if ($request->filled('conversation_id')) {
            $query->where('conversation_id', $request->conversation_id);
        }

        // الترتيب — حماية ضد SQL Injection بقائمة سماحية صارمة
        $allowedSortColumns = ['created_at', 'sender_id', 'receiver_id', 'is_read', 'id'];
        $sortBy = $request->get('sort_by', 'created_at');
        if (! in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'created_at';
        }
        $sortOrder = strtolower((string) $request->get('sort_order', 'desc'));
        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $messages = $query->paginate(50)->withQueryString();

        // إحصائيات
        $stats = [
            'total_messages' => Message::count(),
            'total_conversations' => Conversation::count(),
            'unread_messages' => Message::where('is_read', false)->count(),
            'messages_today' => Message::whereDate('created_at', today())->count(),
            'messages_this_week' => Message::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'messages_this_month' => Message::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // قائمة المستخدمين للفلترة
        $users = User::select('id', 'name', 'email', 'role')
            ->orderBy('name')
            ->get();

        // أكثر المستخدمين نشاطاً
        $topSenders = Message::select('sender_id', DB::raw('COUNT(*) as message_count'))
            ->groupBy('sender_id')
            ->orderBy('message_count', 'desc')
            ->limit(10)
            ->with('sender')
            ->get();

        return view('admin.messages-log.index', compact(
            'messages',
            'stats',
            'users',
            'topSenders',
        ));
    }

    /**
     * عرض تفاصيل رسالة محددة
     */
    public function show($id)
    {
        $message = Message::with(['sender', 'receiver', 'conversation'])
            ->findOrFail($id);

        // جلب جميع رسائل نفس المحادثة للسياق
        $conversationMessages = Message::where('conversation_id', $message->conversation_id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.messages-log.show', compact('message', 'conversationMessages'));
    }

    /**
     * عرض محادثة كاملة
     */
    public function showConversation($conversationId)
    {
        $conversation = Conversation::with(['user1', 'user2'])->findOrFail($conversationId);

        $messages = Message::where('conversation_id', $conversationId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        $stats = [
            'total_messages' => $messages->count(),
            'unread_messages' => $messages->where('is_read', false)->count(),
            'first_message_date' => $messages->first()?->created_at,
            'last_message_date' => $messages->last()?->created_at,
        ];

        return view('admin.messages-log.conversation', compact('conversation', 'messages', 'stats'));
    }

    /**
     * حذف رسالة (للأدمن فقط في حالات الطوارئ)
     */
    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $message->delete();

        return redirect()->back()->with('success', 'تم حذف الرسالة بنجاح');
    }

    /**
     * تصدير الرسائل إلى CSV
     */
    public function export(Request $request)
    {
        $query = Message::with(['sender', 'receiver']);

        // تطبيق نفس الفلاتر
        if ($request->filled('sender_id')) {
            $query->where('sender_id', $request->sender_id);
        }

        if ($request->filled('receiver_id')) {
            $query->where('receiver_id', $request->receiver_id);
        }

        if ($request->filled('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $messages = $query->orderBy('created_at', 'desc')->get();

        $filename = 'messages_log_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($messages) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers
            fputcsv($file, ['ID', 'المرسل', 'المستقبل', 'الرسالة', 'مقروءة', 'تاريخ القراءة', 'تاريخ الإرسال']);

            foreach ($messages as $message) {
                fputcsv($file, [
                    $message->id,
                    $message->sender->name ?? 'غير معروف',
                    $message->receiver->name ?? 'غير معروف',
                    $message->message,
                    $message->is_read ? 'نعم' : 'لا',
                    $message->read_at ? $message->read_at->format('Y-m-d H:i:s') : '-',
                    $message->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * إحصائيات متقدمة
     */
    public function statistics()
    {
        // إحصائيات عامة
        $generalStats = [
            'total_messages' => Message::count(),
            'total_conversations' => Conversation::count(),
            'total_users_messaging' => Message::distinct('sender_id')->count('sender_id'),
            'avg_messages_per_conversation' => round(Message::count() / max(Conversation::count(), 1), 2),
        ];

        // الرسائل حسب اليوم (آخر 30 يوم)
        $messagesPerDay = Message::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // الرسائل حسب الدور
        $messagesByRole = Message::join('users', 'messages.sender_id', '=', 'users.id')
            ->select('users.role', DB::raw('COUNT(*) as count'))
            ->groupBy('users.role')
            ->get();

        // أكثر المستخدمين إرسالاً
        $topSenders = Message::select('sender_id', DB::raw('COUNT(*) as message_count'))
            ->groupBy('sender_id')
            ->orderBy('message_count', 'desc')
            ->limit(10)
            ->with('sender')
            ->get();

        // أكثر المستخدمين استقبالاً
        $topReceivers = Message::select('receiver_id', DB::raw('COUNT(*) as message_count'))
            ->groupBy('receiver_id')
            ->orderBy('message_count', 'desc')
            ->limit(10)
            ->with('receiver')
            ->get();

        // معدل القراءة
        $readRate = Message::selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count
            ')
            ->first();

        $readPercentage = $readRate->total > 0
            ? round(($readRate->read_count / $readRate->total) * 100, 2)
            : 0;

        return view('admin.messages-log.statistics', compact(
            'generalStats',
            'messagesPerDay',
            'messagesByRole',
            'topSenders',
            'topReceivers',
            'readPercentage',
        ));
    }
}
