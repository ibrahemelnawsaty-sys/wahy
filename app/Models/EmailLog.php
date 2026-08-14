<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * سجلّ بريد صادر واحد (خطّة البريد P3). يُملأ عبر App\Listeners\RecordEmailActivity.
 */
class EmailLog extends Model
{
    protected $fillable = [
        'to_email', 'to_name', 'user_id', 'subject', 'mailable_class', 'category',
        'status', 'error_message', 'body', 'related_type', 'related_id',
        'campaign_id', 'sent_by', 'message_id', 'attempts', 'opened_at', 'sent_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'sent_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    /** حالة عربيّة + لون للوحة. */
    public function statusLabel(): string
    {
        return [
            'sending' => 'قيد الإرسال',
            'sent' => 'أُرسِل',
            'failed' => 'فشل',
            'opened' => 'فُتِح',
        ][$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return [
            'sending' => '#f59e0b',
            'sent' => '#16a34a',
            'failed' => '#dc2626',
            'opened' => '#2563eb',
        ][$this->status] ?? '#6b7280';
    }

    /** «قيد الإرسال» منذ زمن = عالق/فاشل غالبًا (العامل متوقّف أو فشل SMTP). */
    public function isStuck(): bool
    {
        return $this->status === 'sending'
            && $this->created_at
            && $this->created_at->lt(now()->subMinutes(10));
    }

    /**
     * مصالحة: يحوّل الصفوف العالقة في «sending» إلى «failed» (لا مستمع لفشل SMTP، فبدونها
     * يبقى العالق sending للأبد ويُظهر عدّاد «فشل» صفرًا زائفًا). تُجدوَل دوريًّا.
     */
    public static function markStuckAsFailed(int $minutes = 20): int
    {
        return static::where('status', 'sending')
            ->where('created_at', '<', now()->subMinutes($minutes))
            ->update([
                'status' => 'failed',
                'error_message' => 'انتهت المهلة دون تأكيد الإرسال — تحقّق من عمل العامل (queue:work) وإعداد SMTP.',
            ]);
    }
}
