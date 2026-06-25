<?php

namespace App\Events;

use App\Models\ActivitySubmission;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityGraded
{
    use Dispatchable, SerializesModels;

    public ActivitySubmission $submission;

    public int $grade;

    public ?string $feedback;

    /**
     * Create a new event instance.
     */
    public function __construct(ActivitySubmission $submission, int $grade, ?string $feedback = null)
    {
        $this->submission = $submission;
        $this->grade = $grade;
        $this->feedback = $feedback;
    }
}
