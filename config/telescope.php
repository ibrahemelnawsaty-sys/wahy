<?php

/**
 * Laravel Telescope Configuration — Debug & Monitoring.
 *
 * تثبيت:
 *   composer require laravel/telescope --dev
 *   php artisan telescope:install
 *   php artisan migrate
 *
 * 🔴 أمان: Telescope محمي بـ Gate في TelescopeServiceProvider — يجب فتحه
 * فقط لـ super_admin أو في local environment.
 *
 * في .env:
 *   TELESCOPE_ENABLED=true   # local/staging فقط
 *   TELESCOPE_ENABLED=false  # production (إلا لو تحت Gate صارم)
 */

use Laravel\Telescope\Watchers;

return [

    'enabled' => env('TELESCOPE_ENABLED', false),

    /*
     * Telescope domain — استخدم subdomain منفصل في الإنتاج إن أمكن
     */
    'domain' => env('TELESCOPE_DOMAIN'),

    'path' => env('TELESCOPE_PATH', 'telescope'),

    'driver' => env('TELESCOPE_DRIVER', 'database'),

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'chunk' => 1000,
        ],
    ],

    /*
     * 🔴 Production: استخدم queue لتجنّب تأثير الأداء
     */
    'queue' => [
        'connection' => env('TELESCOPE_QUEUE_CONNECTION'),
        'queue' => env('TELESCOPE_QUEUE'),
    ],

    /*
     * Middleware: حماية الوصول
     */
    'middleware' => [
        'web',
        \Laravel\Telescope\Http\Middleware\Authorize::class,
    ],

    /*
     * المسارات المتجاهلة (لا تظهر في Telescope)
     */
    'only_paths' => [
        // فارغ = اعرض كل شيء
    ],

    'ignore_paths' => [
        'nova-api*',
        'telescope*',
        'horizon*',
        '_debugbar*',
        'up',
    ],

    'ignore_commands' => [
        'schedule:run',
        'queue:work',
    ],

    /*
     * Watchers — اختر ما تحتاجه فقط لتقليل حجم الـ storage
     */
    'watchers' => [
        Watchers\BatchWatcher::class => env('TELESCOPE_BATCH_WATCHER', true),
        Watchers\CacheWatcher::class => [
            'enabled' => env('TELESCOPE_CACHE_WATCHER', true),
            'hidden' => [],
            'ignore' => [],
        ],
        Watchers\ClientRequestWatcher::class => env('TELESCOPE_CLIENT_REQUEST_WATCHER', true),
        Watchers\CommandWatcher::class => [
            'enabled' => env('TELESCOPE_COMMAND_WATCHER', true),
            'ignore' => [],
        ],
        Watchers\DumpWatcher::class => [
            'enabled' => env('TELESCOPE_DUMP_WATCHER', true),
            'always' => env('TELESCOPE_DUMP_WATCHER_ALWAYS', false),
        ],
        Watchers\EventWatcher::class => [
            'enabled' => env('TELESCOPE_EVENT_WATCHER', true),
            'ignore' => [],
        ],
        Watchers\ExceptionWatcher::class => env('TELESCOPE_EXCEPTION_WATCHER', true),
        Watchers\GateWatcher::class => [
            'enabled' => env('TELESCOPE_GATE_WATCHER', true),
            'ignore_abilities' => [],
            'ignore_packages' => true,
            'ignore_paths' => [],
        ],
        Watchers\JobWatcher::class => env('TELESCOPE_JOB_WATCHER', true),
        Watchers\LogWatcher::class => [
            'enabled' => env('TELESCOPE_LOG_WATCHER', true),
            'level' => 'warning', // فقط warning + error لتقليل النوايز
        ],
        Watchers\MailWatcher::class => env('TELESCOPE_MAIL_WATCHER', true),
        Watchers\ModelWatcher::class => [
            'enabled' => env('TELESCOPE_MODEL_WATCHER', true),
            'events' => ['eloquent.*'],
            'hydrations' => false, // مكلف على الذاكرة
        ],
        Watchers\NotificationWatcher::class => env('TELESCOPE_NOTIFICATION_WATCHER', true),
        Watchers\QueryWatcher::class => [
            'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
            'ignore_packages' => true,
            'ignore_paths' => [],
            'slow' => 100, // 🔴 سجّل الـ queries > 100ms (للأداء)
        ],
        Watchers\RedisWatcher::class => env('TELESCOPE_REDIS_WATCHER', true),
        Watchers\RequestWatcher::class => [
            'enabled' => env('TELESCOPE_REQUEST_WATCHER', true),
            'size_limit' => env('TELESCOPE_RESPONSE_SIZE_LIMIT', 64),
            'ignore_http_methods' => [],
            'ignore_status_codes' => [],
        ],
        Watchers\ScheduleWatcher::class => env('TELESCOPE_SCHEDULE_WATCHER', true),
        Watchers\ViewWatcher::class => env('TELESCOPE_VIEW_WATCHER', true),
    ],
];
