<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Activity::class => \App\Policies\ActivityPolicy::class,
        \App\Models\ActivitySubmission::class => \App\Policies\ActivitySubmissionPolicy::class,
        \App\Models\Lesson::class => \App\Policies\LessonPolicy::class,
        \App\Models\Message::class => \App\Policies\MessagePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
