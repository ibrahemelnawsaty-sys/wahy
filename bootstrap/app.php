<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // الثقة بالبروكسي (Hostinger/Cloudflare يفصل HTTPS عند الـ edge)
        // لازم لتفعيل HSTS و تمرير $request->isSecure() الصحيح
        $middleware->trustProxies(
            at: '*',
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        // استبدال CSRF middleware الافتراضي بالمخصص
        $middleware->validateCsrfTokens(except: [
            // يمكن إضافة استثناءات هنا إذا لزم الأمر
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetArabicLocale::class,
            \App\Http\Middleware\CheckMaintenanceMode::class,
            \App\Http\Middleware\ApplyTheme::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\CheckPasswordChangeRequired::class,
            \App\Http\Middleware\CheckPendingSurveys::class,
        ]);

        // API throttle: 60 طلب/دقيقة افتراضياً + Security Headers
        $middleware->api(prepend: [
            'throttle:api',
            \App\Http\Middleware\SetArabicLocale::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Middleware Aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'school.access' => \App\Http\Middleware\CheckSchoolAccess::class,
            'force-2fa' => \App\Http\Middleware\Force2FAForAdmins::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sentry — يُفعَّل فقط لو ثُبّتت الحزمة (composer require sentry/sentry-laravel)
        $exceptions->report(function (\Throwable $e) {
            if (app()->bound('sentry') && function_exists('\\Sentry\\captureException')) {
                \Sentry\captureException($e);
            }
        });

        // 2FA + قائمة استثناءات للأمان
        $exceptions->dontReport([
            \Illuminate\Validation\ValidationException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        ]);

        // Pass-4 cluster 07: never leak internal exception detail to API/JSON clients,
        // regardless of APP_DEBUG. Known HTTP/validation/auth exceptions are still
        // rendered normally by the framework (return null = defer to default handler).
        $exceptions->render(function (\Throwable $e, $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }
            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقاً.',
            ], 500);
        });
    })->create();
