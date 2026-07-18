<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * إدارة الدعم الفنيّ للتذاكر — عرض/ردّ/حلّ/إعادة فتح/إغلاق/تصعيد/إسناد.
 * المسارات محروسة role:technical_support (والسوبر أدمن يمرّ تلقائياً).
 */
class SupportTicketController extends Controller
{
    /**
     * قائمة كل التذاكر مع فلاتر (status/category/priority/search) وعدّادات.
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('escalated')) {
            $query->where('escalated', true);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->latest('updated_at')->paginate(20)->withQueryString();

        // عدّادات لوحة الفلاتر (تشمل «كم تذكرة حُلّت»)
        $counts = [
            'all' => SupportTicket::count(),
            'open' => SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count(),
            'answered' => SupportTicket::where('status', SupportTicket::STATUS_ANSWERED)->count(),
            'resolved' => SupportTicket::where('status', SupportTicket::STATUS_RESOLVED)->count(),
            'closed' => SupportTicket::where('status', SupportTicket::STATUS_CLOSED)->count(),
            'escalated' => SupportTicket::where('escalated', true)->count(),
        ];

        return view('support.tickets.index', compact('tickets', 'counts'));
    }

    /**
     * عرض تذكرة كاملة مع الردود.
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load(['replies.user', 'user', 'school', 'assignee', 'resolver']);

        return view('support.tickets.show', compact('ticket'));
    }

    /**
     * ردّ الدعم على التذكرة — يجعلها «تم الرد» ويُشعِر المالك.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        // لا ردّ على تذكرة مغلقة — يجب إعادة فتحها أولاً (النموذج مخفيّ، وهذا حارس الخادم)
        if ($ticket->status === SupportTicket::STATUS_CLOSED) {
            return back()->with('error', 'التذكرة مغلقة — أعِد فتحها قبل الردّ.');
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $normalized = normalize_message_html($validated['message']);
        if ($normalized === '') {
            return back()->withInput()->withErrors(['message' => 'نصّ الردّ فارغ.']);
        }

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $normalized,
            'is_staff_reply' => true,
        ]);

        // ردّ الدعم يجعلها «تم الرد»؛ وإن كانت محلولة ننظّف حقول الحلّ (خرجت من حالة الحلّ)
        $updates = [
            'status' => SupportTicket::STATUS_ANSWERED,
            'last_reply_at' => now(),
        ];
        if ($ticket->status === SupportTicket::STATUS_RESOLVED) {
            $updates['resolved_by'] = null;
            $updates['resolved_at'] = null;
        }
        $ticket->update($updates);

        NotificationService::send(
            $ticket->user_id,
            "تم الردّ على تذكرتك #{$ticket->id}",
            "ردّ فريق الدعم على تذكرتك \"{$ticket->subject}\".",
            'support_ticket',
            route('tickets.show', $ticket),
        );

        return redirect()
            ->route('support.tickets.show', $ticket)
            ->with('success', 'تم إرسال ردّك للمستخدم. ✅');
    }

    /**
     * وضع التذكرة كمحلولة.
     */
    public function resolve(SupportTicket $ticket)
    {
        if (in_array($ticket->status, [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_CLOSED], true)) {
            return back()->with('error', 'التذكرة محلولة/مغلقة بالفعل.');
        }

        // الحلّ يُنهي التصعيد (لم تعد «عالقة») فتخرج من عدّاد المُصعّدة تلقائياً.
        $ticket->update([
            'status' => SupportTicket::STATUS_RESOLVED,
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
            'escalated' => false,
            'escalated_at' => null,
        ]);

        NotificationService::send(
            $ticket->user_id,
            "تم حلّ تذكرتك #{$ticket->id}",
            "أُغلقت تذكرتك \"{$ticket->subject}\" كمحلولة. إن استمرّت المشكلة يمكنك الردّ لإعادة فتحها.",
            'support_ticket',
            route('tickets.show', $ticket),
        );

        return redirect()
            ->route('support.tickets.show', $ticket)
            ->with('success', 'تم وضع التذكرة كمحلولة. ✅');
    }

    /**
     * إعادة فتح تذكرة.
     */
    public function reopen(SupportTicket $ticket)
    {
        $ticket->update([
            'status' => SupportTicket::STATUS_OPEN,
            'resolved_by' => null,
            'resolved_at' => null,
        ]);

        return redirect()
            ->route('support.tickets.show', $ticket)
            ->with('success', 'تم إعادة فتح التذكرة. ✅');
    }

    /**
     * إغلاق تذكرة.
     */
    public function close(SupportTicket $ticket)
    {
        // الإغلاق يُنهي التصعيد أيضاً فلا تبقى في عدّاد المُصعّدة.
        $ticket->update([
            'status' => SupportTicket::STATUS_CLOSED,
            'escalated' => false,
            'escalated_at' => null,
        ]);

        return redirect()
            ->route('support.tickets.show', $ticket)
            ->with('success', 'تم إغلاق التذكرة. ✅');
    }

    /**
     * تصعيد التذكرة للسوبر أدمن + إشعار كل السوبر أدمن.
     */
    public function escalate(SupportTicket $ticket)
    {
        // لا تصعيد لتذكرة محلولة/مغلقة، ولا إعادة تصعيد لمُصعّدة (يمنع إعادة إزعاج كل السوبر أدمن).
        if (in_array($ticket->status, [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_CLOSED], true)) {
            return back()->with('error', 'لا يمكن تصعيد تذكرة محلولة/مغلقة.');
        }
        if ($ticket->escalated) {
            return back()->with('error', 'التذكرة مُصعّدة بالفعل.');
        }

        $ticket->update([
            'escalated' => true,
            'escalated_at' => now(),
        ]);

        $superAdminIds = User::where('role', 'super_admin')->pluck('id');
        foreach ($superAdminIds as $adminId) {
            NotificationService::send(
                $adminId,
                "تذكرة مُصعّدة #{$ticket->id}",
                "صعّد الدعم الفنيّ التذكرة \"{$ticket->subject}\" لتعذّر حلّها. بحاجة لتدخّلكم.",
                'support_ticket',
                route('support.tickets.show', $ticket),
            );
        }

        return redirect()
            ->route('support.tickets.show', $ticket)
            ->with('success', 'تم تصعيد التذكرة للسوبر أدمن. ✅');
    }

    /**
     * إسناد التذكرة للموظّف الحالي.
     */
    public function assign(SupportTicket $ticket)
    {
        $ticket->update(['assigned_to' => Auth::id()]);

        return redirect()
            ->route('support.tickets.show', $ticket)
            ->with('success', 'تم إسناد التذكرة إليك. ✅');
    }
}
