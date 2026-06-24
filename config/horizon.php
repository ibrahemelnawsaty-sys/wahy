<?php

/**
 * Laravel Horizon — Redis Queue Dashboard.
 *
 * تثبيت:
 *   composer require laravel/horizon
 *   php artisan horizon:install
 *
 * 🔴 يتطلب Redis (لا يعمل مع database queue).
 * شغّل الـ daemon:
 *   php artisan horizon
 * (في الإنتاج: عبر Supervisor)
 */

use Illuminate\Support\Str;

return [

    'domain' => env('HORIZON_DOMAIN'),

    'path' => env('HORIZON_PATH', 'horizon'),

    'use' => 'default',

    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    /*
     * Middleware: حماية وصول الـ dashboard
     */
    'middleware' => ['web'],

    /*
     * Wait time thresholds (seconds) — Horizon ينبّه إن تجاوزت
     */
    'waits' => [
        'redis:default' => 60,
    ],

    /*
     * Retention — كم يوماً يحتفظ بسجلات الـ jobs
     */
    'trim' => [
        'recent'        => 60,    // 1 hour
        'pending'       => 60,
        'completed'     => 60,
        'recent_failed' => 10080, // 7 days
        'failed'        => 10080,
        'monitored'     => 10080,
    ],

    /*
     * Notifications — تنبيهات Slack/SMS عند زيادة wait time
     */
    'notifications' => [
        'email' => null,
        'slack' => null,
        'sms'   => null,
    ],

    'silenced' => [],
    'metrics' => [
        'trim_snapshots' => [
            'job'   => 24,
            'queue' => 24,
        ],
    ],

    /*
     * Fast termination — Horizon ينهي العمليات بأمان عند إعادة التشغيل
     */
    'fast_termination' => false,

    /*
     * Memory limit per worker (MB)
     */
    'memory_limit' => 64,

    /*
     * Defaults للـ Supervisor processes
     */
    'defaults' => [
        'supervisor-1' => [
            'connection'   => 'redis',
            'queue'        => ['default'],
            'balance'      => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 1,
            'maxTime'      => 0,
            'maxJobs'      => 0,
            'memory'       => 128,
            'tries'        => 3,
            'timeout'      => 60,
            'nice'         => 0,
        ],
    ],

    /*
     * Environment-specific configurations
     */
    'environments' => [

        'production' => [
            'supervisor-1' => [
                'maxProcesses'    => 10,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'tries'           => 3,
            ],
        ],

        'staging' => [
            'supervisor-1' => [
                'maxProcesses' => 3,
                'tries'        => 3,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'maxProcesses' => 3,
                'tries'        => 1,
            ],
        ],
    ],
];
