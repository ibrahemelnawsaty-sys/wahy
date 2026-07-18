<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * تذكرة دعم فنيّ — يرفعها أيّ مستخدم، ويعالجها الدعم الفنيّ أو السوبر أدمن.
 */
class SupportTicket extends Model
{
    use HasFactory;

    // ==================== ثوابت الحالات ====================
    public const STATUS_OPEN = 'open';
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    // ==================== ثوابت الأولويات ====================
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';

    // ==================== ثوابت التصنيفات ====================
    public const CATEGORY_TECHNICAL = 'technical';
    public const CATEGORY_ACCOUNT = 'account';
    public const CATEGORY_CONTENT = 'content';
    public const CATEGORY_OTHER = 'other';

    protected $fillable = [
        'user_id',
        'school_id',
        'subject',
        'message',
        'category',
        'priority',
        'status',
        'assigned_to',
        'escalated',
        'escalated_at',
        'resolved_by',
        'resolved_at',
        'last_reply_at',
    ];

    protected $casts = [
        'escalated' => 'boolean',
        'escalated_at' => 'datetime',
        'resolved_at' => 'datetime',
        'last_reply_at' => 'datetime',
    ];

    // ==================== العلاقات ====================

    /**
     * صاحب التذكرة.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * مدرسة صاحب التذكرة (قد تكون null لأدوار منصّيّة).
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * موظّف الدعم المُسنَدة إليه التذكرة.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * من قام بحلّ التذكرة.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * كل ردود التذكرة (تصاعديّاً بالوقت).
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    /**
     * آخر ردّ على التذكرة.
     */
    public function latestReply(): HasOne
    {
        return $this->hasOne(TicketReply::class, 'ticket_id')->latestOfMany();
    }

    // ==================== Scopes ====================

    /**
     * التذاكر المفتوحة (لم تُحلّ ولم تُغلق).
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_ANSWERED]);
    }

    /**
     * التذاكر المحلولة.
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    // ==================== دوال التسمية والعرض ====================

    /**
     * التسمية العربية للحالة.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'مفتوحة',
            self::STATUS_ANSWERED => 'تم الرد',
            self::STATUS_RESOLVED => 'محلولة',
            self::STATUS_CLOSED => 'مغلقة',
            default => $this->status,
        };
    }

    /**
     * لون الحالة (اسم دلاليّ للـ badges).
     */
    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'warning',
            self::STATUS_ANSWERED => 'info',
            self::STATUS_RESOLVED => 'success',
            self::STATUS_CLOSED => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * التسمية العربية للأولوية.
     */
    public function priorityLabel(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'منخفضة',
            self::PRIORITY_NORMAL => 'عادية',
            self::PRIORITY_HIGH => 'عالية',
            default => $this->priority,
        };
    }

    /**
     * التسمية العربية للتصنيف.
     */
    public function categoryLabel(): string
    {
        return match ($this->category) {
            self::CATEGORY_TECHNICAL => 'مشكلة تقنية',
            self::CATEGORY_ACCOUNT => 'مشكلة حساب',
            self::CATEGORY_CONTENT => 'محتوى',
            self::CATEGORY_OTHER => 'أخرى',
            default => $this->category,
        };
    }
}
