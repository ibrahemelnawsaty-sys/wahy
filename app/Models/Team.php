<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'classroom_id', 'name', 'description',
        'created_by', 'points', 'status',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_members', 'team_id', 'student_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function activities()
    {
        return $this->hasMany(TeamActivity::class);
    }

    public function leader()
    {
        return $this->belongsToMany(User::class, 'team_members', 'team_id', 'student_id')
            ->wherePivot('role', 'leader')
            ->limit(1);
    }
}
