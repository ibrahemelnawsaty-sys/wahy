<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * تفضيلات بريد المستخدم (خطّة البريد P4). allows() هي البوّابة التي تحترمها الحملات (P5)
 * ورسائل الأحداث (P6) قبل الإرسال. الفئات الحرِجة تتجاوزها دائمًا.
 */
class EmailPreference extends Model
{
    protected $table = 'user_email_preferences';

    protected $fillable = ['user_id', 'unsubscribed_all', 'digests', 'announcements', 'events'];

    protected $casts = [
        'unsubscribed_all' => 'boolean',
        'digests' => 'boolean',
        'announcements' => 'boolean',
        'events' => 'boolean',
    ];

    /** فئات تُرسَل دائمًا مهما كانت التفضيلات (أمنيّة/معاملاتيّة). */
    public const CRITICAL = ['auth', 'transactional', 'security'];

    /** ربط الفئة بعمود التفضيل. */
    public const MAP = [
        'digest' => 'digests',
        'campaign' => 'announcements',
        'announcement' => 'announcements',
        'event' => 'events',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** هل يُسمح بإرسال بريد من هذه الفئة لهذا المستخدم؟ */
    public static function allows(?User $user, string $category): bool
    {
        if (! $user) {
            return true; // ضيف/بلا حساب — لا تفضيلات
        }
        if (in_array($category, self::CRITICAL, true)) {
            return true; // حرِج — دائمًا
        }

        $pref = self::where('user_id', $user->id)->first();
        if (! $pref) {
            return true; // لا تفضيلات محفوظة = الافتراضيّ (مسموح)
        }
        if ($pref->unsubscribed_all) {
            return false;
        }

        $col = self::MAP[$category] ?? null;

        return $col === null ? true : (bool) $pref->{$col};
    }
}
