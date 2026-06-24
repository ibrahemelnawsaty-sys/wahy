<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentGift extends Model
{
    protected $fillable = [
        'parent_id',
        'student_id',
        'gift_type',
        'gift_message',
        'points_cost',
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
