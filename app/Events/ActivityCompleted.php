<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityCompleted
{
    use Dispatchable, SerializesModels;

    public $student;

    public $activity;

    public $score;

    public $xpAwarded;

    public $coinsAwarded;

    public function __construct(User $student, $activity, $score, $xpAwarded, $coinsAwarded)
    {
        $this->student = $student;
        $this->activity = $activity;
        $this->score = $score;
        $this->xpAwarded = $xpAwarded;
        $this->coinsAwarded = $coinsAwarded;
    }
}
