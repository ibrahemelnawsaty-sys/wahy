<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DispatchCampaignJob;
use App\Mail\CampaignMail;
use App\Models\EmailCampaign;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * إرسال حملات البريد الجماعيّة/المخصّصة (خطّة البريد P5). محميّ can:access-admin.
 */
class EmailCampaignController extends Controller
{
    private array $roles = [
        'student' => 'الطلاب', 'teacher' => 'المعلّمون', 'school_admin' => 'مديرو المدارس',
        'parent' => 'أولياء الأمور', 'technical_support' => 'الدعم الفنّي', 'super_admin' => 'المشرفون',
    ];

    public function index()
    {
        $campaigns = EmailCampaign::latest()->paginate(20);

        return view('admin.email-campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $schools = class_exists(School::class) ? School::orderBy('name')->get(['id', 'name']) : collect();

        return view('admin.email-campaigns.create', ['roles' => $this->roles, 'schools' => $schools]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'audience_type' => 'required|in:all,role,school,custom',
            'audience_role' => 'required_if:audience_type,role|nullable|string',
            'audience_school' => 'required_if:audience_type,school|nullable|integer',
            'audience_custom' => 'required_if:audience_type,custom|nullable|string',
            'action' => 'nullable|in:send,test',
        ]);

        $audienceValue = match ($data['audience_type']) {
            'role' => $data['audience_role'],
            'school' => $data['audience_school'],
            'custom' => $data['audience_custom'],
            default => null,
        };

        $body = safe_html($data['body']); // تعقيم محتوى الأدمن قبل التخزين/الإرسال

        // إرسال تجريبيّ لبريد الأدمن نفسه (بلا حفظ حملة)
        if (($data['action'] ?? 'send') === 'test') {
            if (! (bool) setting('email_master_enabled', true)) {
                return back()->with('error', 'البريد موقوف مؤقّتًا عبر المفتاح الرئيسيّ للإعدادات.')->withInput();
            }
            $to = auth()->user()->email;
            if ($to) {
                Mail::to($to)->send(new CampaignMail('[اختبار] ' . $data['subject'], $body, null, 0, auth()->id()));
            }

            return back()->with('success', 'أُرسِلت رسالة تجريبيّة إلى ' . ($to ?: 'بريدك') . ' — راجِعها ثمّ أرسِل للحملة.')->withInput();
        }

        $campaign = EmailCampaign::create([
            'subject' => $data['subject'],
            'body' => $body,
            'audience_type' => $data['audience_type'],
            'audience_value' => $audienceValue,
            'status' => 'queued',
            'created_by' => auth()->id(),
        ]);

        DispatchCampaignJob::dispatch($campaign->id);

        return redirect()->route('admin.email-campaigns.show', $campaign)
            ->with('success', 'بدأ إرسال الحملة عبر الطابور. تظهر النتائج هنا تدريجيًّا.');
    }

    public function show(EmailCampaign $emailCampaign)
    {
        return view('admin.email-campaigns.show', [
            'campaign' => $emailCampaign,
            'sent' => $emailCampaign->sentCount(),
            'failed' => $emailCampaign->failedCount(),
        ]);
    }
}
