<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/**
 * لوحة مراقبة البريد الصادر (خطّة البريد P3): سرد بفلاتر + إحصاءات + تفاصيل + إعادة إرسال.
 * محميّة can:access-admin (سوبر أدمن).
 */
class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $q = EmailLog::query()->latest();

        if ($s = $request->input('status')) {
            $q->where('status', $s);
        }
        if ($c = $request->input('category')) {
            $q->where('category', $c);
        }
        if ($term = trim((string) $request->input('search'))) {
            $q->where(function ($w) use ($term) {
                $w->where('to_email', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%");
            });
        }
        if ($from = $request->input('from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        $logs = $q->paginate(30)->withQueryString();

        // إحصاءات آخر 30 يومًا + مؤشّرات صحّة
        $since = now()->subDays(30);
        $counts = EmailLog::where('created_at', '>=', $since)
            ->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');

        $stats = [
            'sent' => (int) ($counts['sent'] ?? 0),
            'failed' => (int) ($counts['failed'] ?? 0),
            'sending' => (int) ($counts['sending'] ?? 0),
            'stuck' => EmailLog::where('status', 'sending')->where('created_at', '<', now()->subMinutes(10))->count(),
            'queue_pending' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0,
        ];
        $stats['total'] = $stats['sent'] + $stats['failed'] + $stats['sending'];

        $categories = EmailLog::select('category')->distinct()->pluck('category');

        return view('admin.email-logs.index', compact('logs', 'stats', 'categories'));
    }

    public function show(EmailLog $emailLog)
    {
        return view('admin.email-logs.show', ['log' => $emailLog]);
    }

    /** إعادة إرسال نسخة من الرسالة المخزَّنة (يُنشئ سجلًّا جديدًا عبر مستمع التسجيل). */
    public function resend(EmailLog $emailLog)
    {
        if (! $emailLog->body || ! $emailLog->to_email) {
            return back()->with('error', 'لا يمكن إعادة الإرسال: لا يوجد محتوى/مستلِم محفوظ.');
        }

        try {
            $subject = $emailLog->subject ?: 'رسالة من ' . setting('site_name', 'أثيل مكة');
            Mail::html($emailLog->body, function ($m) use ($emailLog, $subject) {
                $m->to($emailLog->to_email, $emailLog->to_name)->subject($subject);
                $m->getHeaders()->addTextHeader('X-Wahy-Category', $emailLog->category ?: 'transactional');
                $m->getHeaders()->addTextHeader('X-Wahy-SentBy', (string) auth()->id());
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'فشلت إعادة الإرسال: ' . $e->getMessage());
        }

        return back()->with('success', 'أُعيد إرسال الرسالة إلى ' . $emailLog->to_email);
    }
}
