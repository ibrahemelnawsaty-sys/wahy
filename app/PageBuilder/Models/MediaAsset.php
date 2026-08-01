<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * أصل وسائط في المكتبة الموحّدة (ت-٨). رابط العرض عبر القرص العامّ (اصطلاح المشروع).
 */
class MediaAsset extends Model
{
    protected $table = 'pb_media';

    protected $fillable = ['disk', 'path', 'mime', 'size', 'width', 'height', 'alt', 'created_by'];

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk ?: 'public')->url($this->path);
    }
}
