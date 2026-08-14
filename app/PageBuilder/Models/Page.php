<?php

namespace App\PageBuilder\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * صفحة في محرّر الصفحات الاحترافيّ — جسمها شجرة كتل خاصّة، ولها جزءا هيدر/فوتر (أو الافتراضيّ).
 * شجرة كتل لكلّ لغة (ت-٣ نهج ب): نُسَخ اللغات تتشارك translation_group.
 */
class Page extends Model
{
    protected $table = 'pb_pages';

    protected $fillable = [
        'translation_group', 'locale', 'title', 'slug', 'status', 'blocks',
        'header_part_id', 'footer_part_id', 'hide_header', 'hide_footer',
        'meta_title', 'meta_description',
        'og_image', 'published_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'blocks' => 'array',
        'hide_header' => 'boolean',
        'hide_footer' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(TemplatePart::class, 'header_part_id');
    }

    public function footer(): BelongsTo
    {
        return $this->belongsTo(TemplatePart::class, 'footer_part_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest('id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** نُسَخ اللغات الأخرى لنفس الصفحة (ت-٣). */
    public function translations()
    {
        return static::where('translation_group', $this->translation_group)
            ->where('id', '!=', $this->id);
    }
}
