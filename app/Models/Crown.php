<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crown extends Model
{
    protected $fillable = ['user_id', 'value_id', 'earned_at'];

    protected $casts = ['earned_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function value()
    {
        return $this->belongsTo(Value::class);
    }
}
