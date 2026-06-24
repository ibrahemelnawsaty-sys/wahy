<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentRegistered
{
    use Dispatchable, SerializesModels;

    public User $student;
    public ?User $approvedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(User $student, ?User $approvedBy = null)
    {
        $this->student = $student;
        $this->approvedBy = $approvedBy;
    }
}
