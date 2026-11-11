<?php

namespace App\Services\Mail;

use Symfony\Component\Mime\Email;

/**
 * ترويسات التتبّع X-Wahy-* (يلتقطها RecordEmailActivity لسجلّ email_logs).
 *
 * كائن قابل للاستدعاء بدل Closure: Mailable::$callbacks تُسلسَل مع job الطابور،
 * والإغلاقات لا تُسلسَل ("Serialization of 'Closure' is not allowed") فكان كلّ بريد أحداث يسقط عند التصفيف.
 */
class TrackingHeaders
{
    public function __construct(
        public string $category,
        public ?int $userId,
        public string $mailable,
    ) {}

    public function __invoke(Email $message): void
    {
        $h = $message->getHeaders();
        $h->addTextHeader('X-Wahy-Category', $this->category);
        if ($this->userId) {
            $h->addTextHeader('X-Wahy-User', (string) $this->userId);
        }
        $h->addTextHeader('X-Wahy-Mailable', $this->mailable);
    }
}
