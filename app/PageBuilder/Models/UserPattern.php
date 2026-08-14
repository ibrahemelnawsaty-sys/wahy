<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نمط مستخدم محفوظ (قسم قابل لإعادة الاستخدام) — شجرة كتل يعيد الأدمن إدراجها في أيّ صفحة.
 */
class UserPattern extends Model
{
    protected $table = 'pb_user_patterns';

    protected $fillable = ['name', 'blocks', 'created_by'];

    protected $casts = ['blocks' => 'array'];
}
