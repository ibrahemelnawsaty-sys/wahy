<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentPoint extends Model
{
    protected $fillable = [
        'parent_id',
        'points',
        'reason',
        'reference_type',
        'reference_id',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
