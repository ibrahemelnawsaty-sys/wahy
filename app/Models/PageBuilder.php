<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageBuilder extends Model
{
    protected $table = 'page_builder';

    protected $fillable = [
        'page_name',
        'slug',
        'json_data',
        'meta_title',
        'meta_description',
        'og_image',
        'is_active',
    ];

    protected $casts = [
        'json_data' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get page by slug
     */
    public static function getBySlug($slug)
    {
        return self::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active pages
     */
    public static function getActivePages()
    {
        return self::where('is_active', true)
            ->orderBy('page_name')
            ->get();
    }
}
