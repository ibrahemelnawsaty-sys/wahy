<?php

namespace App\Models;

use App\Support\LandingHtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * تخطيط الصفحة الرئيسية المُدمج — لقطة HTML مُعقّمة لمحتوى <main>.
 *
 * @see \App\Support\LandingHtmlSanitizer
 */
class LandingLayout extends Model
{
    protected $fillable = ['key', 'html', 'updated_by'];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * الـ HTML الفعّال لصفحة معيّنة، أو null إن لم يُخصَّص تخطيط (يُستخدم القالب الثابت).
     */
    public static function currentHtml(string $key = 'home'): ?string
    {
        $html = self::where('key', $key)->value('html');

        return ($html !== null && trim($html) !== '') ? $html : null;
    }

    /**
     * حفظ تخطيط جديد — يُعقَّم دائماً قبل التخزين.
     */
    public static function store(string $html, string $key = 'home'): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'html' => LandingHtmlSanitizer::clean($html),
                'updated_by' => auth()->id(),
            ],
        );
    }

    /**
     * استعادة القالب الافتراضي (حذف التخطيط المخصّص) — صمام أمان.
     */
    public static function reset(string $key = 'home'): void
    {
        self::where('key', $key)->delete();
    }
}
