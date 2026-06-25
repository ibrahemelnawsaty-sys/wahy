<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    protected $fillable = ['user_id', 'current_streak', 'longest_streak', 'last_activity_date'];

    protected $casts = ['last_activity_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
