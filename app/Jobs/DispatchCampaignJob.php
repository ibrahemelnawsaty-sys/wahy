<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailPreference;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * يوزّع حملةً على جمهورها: يحلّ المستلمين، يحترم إلغاء الاشتراك (opt-out)، يعوّض المتغيّرات،
 * ويُصفّف رسالة لكل مستلِم (خطّة البريد P5). العامل يعالجها تباعًا فيخفّف الضغط على SMTP.
 */
class DispatchCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 1; // بلا إعادة محاولة — يمنع إعادة بثّ الحملة كاملةً عند الفشل

    public function __construct(public int $campaignId) {}

    public function handle(): void
    {
        $campaign = EmailCampaign::find($this->campaignId);
        if (! $campaign) {
            return;
        }

        // إيقاف طارئ: المفتاح الرئيسيّ يوقف الحملات أيضًا (لا يقتصر أثره على بريد الأحداث).
        if (! (bool) setting('email_master_enabled', true)) {
            return; // تبقى الحالة queued؛ يُعاد الإرسال عند إعادة التفعيل
        }

        // ادّعاء ذرّيّ: نسخة واحدة فقط تنقل queued→sending، فيُمنَع إعادةُ البثّ عند إعادة تشغيل
        // الوظيفة/التوازي (retry_after<timeout). الحملة المقطوعة تبقى ناقصةً لا مُكرَّرة.
        $claimed = EmailCampaign::where('id', $this->campaignId)->where('status', 'queued')->update(['status' => 'sending']);
        if (! $claimed) {
            return;
        }

        $count = 0;

        if ($q = $campaign->usersQuery()) {
            $q->select(['id', 'name', 'email'])->chunkById(200, function ($users) use ($campaign, &$count) {
                foreach ($users as $u) {
                    if (! EmailPreference::allows($u, 'campaign')) {
                        continue; // احترام إلغاء الاشتراك
                    }
                    $this->queueOne($campaign, $u->email, $u->name, $u->id);
                    $count++;
                }
            });
        } else {
            foreach ($campaign->customEmails() as $email) {
                $u = User::where('email', $email)->first(['id', 'name', 'email']);
                if ($u && ! EmailPreference::allows($u, 'campaign')) {
                    continue;
                }
                $this->queueOne($campaign, $email, $u?->name, $u?->id);
                $count++;
            }
        }

        $campaign->update(['status' => 'sent', 'total_recipients' => $count, 'sent_at' => now()]);
    }

    private function queueOne(EmailCampaign $campaign, string $email, ?string $name, ?int $userId): void
    {
        $subject = $this->render($campaign->subject, $name);
        $body = $this->render($campaign->body, $name);
        $unsub = $userId ? email_unsubscribe_url($userId) : null;

        Mail::to($email, $name)->queue(new CampaignMail($subject, $body, $unsub, $campaign->id, $userId));
    }

    /** تعويض المتغيّرات البسيطة في العنوان/المتن. */
    private function render(string $text, ?string $name): string
    {
        return str_replace(['{{name}}', '{{الاسم}}'], [$name ?? '', $name ?? ''], $text);
    }
}
