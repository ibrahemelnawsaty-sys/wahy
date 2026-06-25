<?php

/**
 * Sentry Error Tracking Configuration.
 *
 * يتطلب تثبيت الـ package أولاً:
 *   composer require sentry/sentry-laravel
 *   php artisan sentry:publish --dsn=YOUR_DSN
 *
 * ثم في .env أضف:
 *   SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project-id
 *   SENTRY_TRACES_SAMPLE_RATE=0.1  # نسبة 10% من الـ requests للـ performance tracking
 *   SENTRY_PROFILES_SAMPLE_RATE=0.1
 *   SENTRY_SEND_DEFAULT_PII=false  # 🔴 false لمنع إرسال بيانات شخصية
 *
 * في bootstrap/app.php (داخل withExceptions):
 *   $exceptions->report(function (\Throwable $e) {
 *       if (function_exists('\Sentry\captureException')) {
 *           \Sentry\captureException($e);
 *       }
 *   });
 */
return [

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    'release' => env('SENTRY_RELEASE'),

    'environment' => env('APP_ENV', 'production'),

    /*
     * Sample rate — كم نسبة الـ requests التي ترسل performance traces
     * 0.1 = 10% (مناسب للإنتاج لتقليل التكلفة)
     */
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.1),

    /*
     * Profiles sample rate — للأداء العميق
     */
    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 0.1),

    /*
     * 🔴 أمان: لا ترسل PII (Personally Identifiable Information) بشكل افتراضي
     */
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    /*
     * استثناءات التجاهل — لا ترسل validation errors أو 404s إلى Sentry
     */
    'ignore_exceptions' => [
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
    ],

    /*
     * تجاهل routes معينة (health checks, metrics)
     */
    'ignore_transactions' => [
        'GET /up',
        'GET /health',
    ],

    /*
     * قبل الإرسال — فلتر بيانات حساسة من الـ payload.
     *
     * 🔴 ملاحظة هامة: لا يمكن استخدام closure هنا لأن config:cache يحاول serialize كل الـ config.
     *    بدلاً من ذلك، الـ filter مُسجَّل في AppServiceProvider::boot() عبر:
     *      \Sentry\State\Scope::class . '->setExtra(...)
     *    أو عبر Sentry's beforeSend hook في الـ provider.
     *
     *    إن أردت تخصيص هذا، استخدم App\Providers\AppServiceProvider::registerSentryBeforeSend()
     */
    // 'before_send' => null,  // يُضبط في AppServiceProvider

    /*
     * Integrations — جمع breadcrumbs من المصادر المختلفة
     */
    'breadcrumbs' => [
        'logs' => true,
        'cache' => true,
        'livewire' => false,
        'sql_queries' => true,
        'sql_bindings' => false, // 🔴 false لمنع تسريب بيانات في الـ SQL
        'queue_info' => true,
        'command_info' => true,
    ],
];
