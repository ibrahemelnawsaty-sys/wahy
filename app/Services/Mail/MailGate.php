<?php

namespace App\Services\Mail;

use App\Models\EmailPreference;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * البوّابة المركزيّة لبريد الأحداث (خطّة البريد P6). كل بريد أحداث يمرّ عبرها فتحترم:
 *   1) المفتاح الرئيسيّ email_master_enabled (إيقاف طارئ لكل البريد)
 *   2) مفتاح النوع email_type_{type} (تحكّم الأدمن لكل نوع، افتراضيّه مُفعَّل)
 *   3) تفضيلات المستخدم (إلغاء الاشتراك) — عبر EmailPreference::allows
 * وتَسِم الرسالة بترويسات التتبّع فيلتقطها email_logs.
 */
class MailGate
{
    public static function send(?User $user, string $type, string $category, Mailable $mailable, ?string $toEmail = null): bool
    {
        $email = $toEmail ?: $user?->email;
        if (! $email) {
            return false;
        }
        if (! (bool) setting('email_master_enabled', true)) {
            return false;
        }
        if (! (bool) setting('email_type_' . $type, true)) {
            return false;
        }
        if ($user && ! EmailPreference::allows($user, $category)) {
            return false;
        }

        try {
            $mailable->withSymfonyMessage(function ($message) use ($category, $user, $mailable) {
                $h = $message->getHeaders();
                $h->addTextHeader('X-Wahy-Category', $category);
                if ($user) {
                    $h->addTextHeader('X-Wahy-User', (string) $user->id);
                }
                $h->addTextHeader('X-Wahy-Mailable', class_basename($mailable));
            });

            // Mail::send يُصفّف تلقائيًّا لو كان الـMailable ShouldQueue، وإلّا يرسل تزامنيًّا
            // (المستمعون هنا مُصفَّفون أصلًا فيجري ذلك داخل العامل).
            Mail::to($email)->send($mailable);

            return true;
        } catch (\Throwable $e) {
            Log::error('MailGate send failed (' . $type . '): ' . $e->getMessage());

            return false;
        }
    }
}
