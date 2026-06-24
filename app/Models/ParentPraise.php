<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentPraise extends Model
{
    protected $fillable = [
        'parent_id',
        'student_id',
        'praise_message',
        'praise_type',
        'points_awarded',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
