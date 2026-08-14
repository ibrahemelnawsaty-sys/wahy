<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;

/**
 * يلتقط كل بريد صادر تلقائيًّا في email_logs عبر أحداث Mail — بلا تعديل أيّ Mailable (خطّة البريد P3).
 * MessageSending: يُنشئ السجلّ (status=sending) ويربطه بترويسة X-Wahy-Log.
 * MessageSent: يقرأ الترويسة ويحدّث الحالة إلى sent + message_id.
 * الترويسات الاختياريّة X-Wahy-{Category,Mailable,User,Campaign,SentBy} تُثريه إن ضبطها المُرسِل.
 */
class RecordEmailActivity
{
    public function sending(MessageSending $event): void
    {
        try {
            $msg = $event->message; // Symfony\Component\Mime\Email
            $headers = $msg->getHeaders();
            $hdr = function (string $name) use ($headers) {
                return $headers->has($name) ? trim($headers->get($name)->getBodyAsString()) : null;
            };

            $to = $msg->getTo();
            $first = $to[0] ?? null;

            $html = $msg->getHtmlBody();
            $body = $html !== null ? (string) $html : ($msg->getTextBody() !== null ? (string) $msg->getTextBody() : null);

            $numeric = fn (?string $v) => ($v !== null && is_numeric($v)) ? (int) $v : null;

            $log = EmailLog::create([
                'to_email' => $first ? $first->getAddress() : '',
                'to_name' => $first && $first->getName() !== '' ? $first->getName() : null,
                'subject' => (string) $msg->getSubject(),
                'body' => $body,
                'category' => $hdr('X-Wahy-Category') ?: 'transactional',
                'mailable_class' => $hdr('X-Wahy-Mailable'),
                'user_id' => $numeric($hdr('X-Wahy-User')),
                'campaign_id' => $numeric($hdr('X-Wahy-Campaign')),
                'sent_by' => $numeric($hdr('X-Wahy-SentBy')),
                'status' => 'sending',
                'attempts' => 1,
            ]);

            // نربط الرسالة بالسجلّ كي يحدّثه MessageSent (ترويسة داخليّة، غير مرئيّة للمستخدم).
            $headers->addTextHeader('X-Wahy-Log', (string) $log->id);
        } catch (\Throwable $e) {
            // التسجيل يجب ألّا يُسقط الإرسال أبدًا.
        }
    }

    public function sent(MessageSent $event): void
    {
        try {
            $original = $event->sent->getOriginalMessage();
            $headers = $original->getHeaders();
            if (! $headers->has('X-Wahy-Log')) {
                return;
            }
            $id = (int) trim($headers->get('X-Wahy-Log')->getBodyAsString());
            if ($id <= 0) {
                return;
            }

            $messageId = null;
            try {
                $messageId = $event->sent->getMessageId();
            } catch (\Throwable $e) {
                // بعض النقلات لا تُعيد message-id
            }

            EmailLog::where('id', $id)->update([
                'status' => 'sent',
                'sent_at' => now(),
                'message_id' => $messageId,
            ]);
        } catch (\Throwable $e) {
            // لا نُسقط شيئًا بسبب التسجيل.
        }
    }
}
