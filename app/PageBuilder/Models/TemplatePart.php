<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * جزء قالب مشترك (هيدر/فوتر) — منطقة تحرير مستقلّة تُشارَك عبر الصفحات، لكلّ لغة.
 */
class TemplatePart extends Model
{
    protected $table = 'pb_template_parts';

    protected $fillable = [
        'translation_group', 'locale', 'name', 'kind', 'blocks',
        'is_active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'blocks' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeKind($query, string $kind)
    {
        return $query->where('kind', $kind);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(TemplatePartRevision::class)->latest('id');
    }

    /** الجزء الفعّال (هيدر/فوتر) لِلُغةٍ ما — الافتراضيّ حين لا تحدّده الصفحة. */
    public static function activeFor(string $kind, string $locale): ?self
    {
        return static::kind($kind)->where('locale', $locale)->active()->first();
    }
}
