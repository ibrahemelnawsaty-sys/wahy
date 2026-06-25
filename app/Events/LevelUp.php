<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LevelUp
{
    use Dispatchable, SerializesModels;

    public $student;

    public $newLevel;

    public $oldLevel;

    public function __construct(User $student, $newLevel, $oldLevel)
    {
        $this->student = $student;
        $this->newLevel = $newLevel;
        $this->oldLevel = $oldLevel;
    }
}
