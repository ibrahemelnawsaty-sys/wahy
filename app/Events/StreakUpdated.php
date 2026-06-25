<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreakUpdated
{
    use Dispatchable, SerializesModels;

    public $student;

    public $streakDays;

    public $milestone;

    public function __construct(User $student, $streakDays, $milestone = false)
    {
        $this->student = $student;
        $this->streakDays = $streakDays;
        $this->milestone = $milestone;
    }
}
