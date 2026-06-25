<?php

namespace App\Events;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeEarned
{
    use Dispatchable, SerializesModels;

    public User $user;

    public Badge $badge;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Badge $badge)
    {
        $this->user = $user;
        $this->badge = $badge;
    }
}
