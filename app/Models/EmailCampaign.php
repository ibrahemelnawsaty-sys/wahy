<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * حملة بريد جماعيّة/مخصّصة (خطّة البريد P5). التتبّع لكل مستلِم عبر email_logs.campaign_id.
 */
class EmailCampaign extends Model
{
    protected $fillable = [
        'subject', 'body', 'audience_type', 'audience_value',
        'status', 'total_recipients', 'created_by', 'scheduled_at', 'sent_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'campaign_id');
    }

    public function sentCount(): int
    {
        return $this->logs()->where('status', 'sent')->count();
    }

    public function failedCount(): int
    {
        return $this->logs()->whereIn('status', ['failed'])->count();
    }

    /** استعلام المستخدمين للجماهير all/role/school؛ null لـcustom (تُعالَج كإيميلات). */
    public function usersQuery()
    {
        if ($this->audience_type === 'custom') {
            return null;
        }

        $q = User::query()->whereNotNull('email')->where('email', '!=', '');

        return match ($this->audience_type) {
            'role' => $q->where('role', $this->audience_value),
            'school' => $q->where('school_id', $this->audience_value),
            default => $q, // all
        };
    }

    /** قائمة الإيميلات المخصّصة (صالحة، فريدة). */
    public function customEmails(): array
    {
        if ($this->audience_type !== 'custom') {
            return [];
        }

        return collect(preg_split('/[\s,;]+/', (string) $this->audience_value))
            ->map(fn ($e) => trim((string) $e))
            ->filter(fn ($e) => $e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()->values()->all();
    }

    /** تقدير عدد المستلمين قبل الإرسال (للمعاينة). */
    public function estimateRecipients(): int
    {
        if ($q = $this->usersQuery()) {
            return (int) $q->count();
        }

        return count($this->customEmails());
    }

    public function audienceLabel(): string
    {
        return match ($this->audience_type) {
            'role' => 'دور: ' . $this->audience_value,
            'school' => 'مدرسة #' . $this->audience_value,
            'custom' => 'إيميلات مخصّصة (' . count($this->customEmails()) . ')',
            default => 'كل المستخدمين',
        };
    }

    public function statusLabel(): string
    {
        return [
            'draft' => 'مسودّة', 'queued' => 'في الطابور', 'sending' => 'قيد الإرسال', 'sent' => 'أُرسِلت',
        ][$this->status] ?? $this->status;
    }
}
