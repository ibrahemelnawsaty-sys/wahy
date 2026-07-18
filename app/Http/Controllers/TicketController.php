<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * تذاكر الدعم الفنيّ لنهاية المستخدم — متاحة لكل الأدوار (عابرة للمدارس/الأدوار).
 * كل مستخدم يرى تذاكره هو فقط ويرفع تذاكر جديدة ويردّ عليها ويغلقها.
 */
class TicketController extends Controller
{
    /**
     * قائمة تذاكري أنا فقط.
     */
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->withCount('replies')
            ->latest('updated_at')
            ->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * نموذج رفع تذكرة جديدة.
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * حفظ تذكرة جديدة + إشعار كل الدعم الفنيّ وكل السوبر أدمن.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'category' => 'required|in:technical,account,content,other',
            'priority' => 'required|in:low,normal,high',
        ]);

        $user = Auth::user();

        // نخزّن المحتوى المطبَّع فقط؛ لو طُبِّع إلى فراغ (وسوم فارغة/مسافات) نرفض بدل تخزين الخام.
        $normalized = normalize_message_html($validated['message']);
        if ($normalized === '') {
            return back()->withInput()->withErrors(['message' => 'نصّ الرسالة فارغ.']);
        }

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'subject' => $validated['subject'],
            'message' => $normalized,
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => SupportTicket::STATUS_OPEN,
            'last_reply_at' => now(),
        ]);

        // إشعار كل موظّفي الدعم الفنيّ + كل السوبر أدمن (العنوان منوّع برقم التذكرة لتجاوز dedup)
        $staffIds = User::whereIn('role', ['technical_support', 'super_admin'])
            ->pluck('id');

        foreach ($staffIds as $staffId) {
            NotificationService::send(
                $staffId,
                "تذكرة دعم جديدة #{$ticket->id}: {$ticket->subject}",
                "رفع {$user->name} تذكرة دعم فنيّ جديدة بحاجة للمراجعة.",
                'support_ticket',
                route('support.tickets.show', $ticket),
            );
        }

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'تم رفع تذكرتك بنجاح! سيتواصل معك فريق الدعم قريباً. ✅');
    }

    /**
     * عرض تذكرة (المالك فقط).
     */
    public function show(SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $ticket->load(['replies.user', 'assignee', 'resolver']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * ردّ المالك على تذكرته.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

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
            'is_staff_reply' => false,
        ]);

        // ردّ المالك يعيد فتح التذكرة إن كانت مُجابة/محلولة — وننظّف حقول الحلّ حتى لا
        // تبقى محسوبةً في «محلولاتي»/«حُلّت» بعد إعادة الفتح (اتّساقاً مع reopen).
        $updates = ['last_reply_at' => now()];
        if (in_array($ticket->status, [SupportTicket::STATUS_ANSWERED, SupportTicket::STATUS_RESOLVED], true)) {
            $updates['status'] = SupportTicket::STATUS_OPEN;
            $updates['resolved_by'] = null;
            $updates['resolved_at'] = null;
        }
        $ticket->update($updates);

        // إشعار الدعم: المُسنَد إليه إن وُجد، وإلا كل الدعم الفنيّ + كل السوبر أدمن
        // (السوبر أدمن قد يكون هو المُشغِّل الفعليّ للوحة الدعم على نشرٍ بلا موظّفي دعم).
        $user = Auth::user();
        if ($ticket->assigned_to) {
            NotificationService::send(
                $ticket->assigned_to,
                "ردّ جديد على التذكرة #{$ticket->id}",
                "أضاف {$user->name} ردّاً على التذكرة \"{$ticket->subject}\".",
                'support_ticket',
                route('support.tickets.show', $ticket),
            );
        } else {
            $staffIds = User::whereIn('role', ['technical_support', 'super_admin'])->pluck('id');
            foreach ($staffIds as $staffId) {
                NotificationService::send(
                    $staffId,
                    "ردّ جديد على التذكرة #{$ticket->id}",
                    "أضاف {$user->name} ردّاً على التذكرة \"{$ticket->subject}\".",
                    'support_ticket',
                    route('support.tickets.show', $ticket),
                );
            }
        }

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'تم إرسال ردّك. ✅');
    }

    /**
     * إغلاق التذكرة (المالك فقط).
     */
    public function close(SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $ticket->update(['status' => SupportTicket::STATUS_CLOSED]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'تم إغلاق التذكرة. ✅');
    }
}
