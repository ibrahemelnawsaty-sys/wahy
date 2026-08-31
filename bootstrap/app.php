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
        // 419 (رمز CSRF بائت) لا يجوز أن يكون طريقاً مسدوداً. صفحة Laravel القياسيّة تترك
        // المستخدم عالقاً بلا مخرج — وقد حبست مالك المنصّة خارج تدفّق التحقّق الثنائيّ.
        // نُعيده للصفحة نفسها فتُصيَّر برمزٍ جديد ويُعيد المحاولة. (App\Exceptions\Handler فيه
        // فرعٌ مشابه لكنّه **غير مربوط** في Laravel 12 — لا شيء يسجّله، فكان ميّتاً.)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            $msg = 'انتهت صلاحية الصفحة. حدّثناها لك — يرجى إعادة المحاولة.';

            if ($request->expectsJson()) {
                return response()->json(['error' => $msg], 419);
            }

            return redirect()->back()
                ->withInput($request->except(['_token', 'password', 'password_confirmation', 'code']))
                ->with('error', $msg);
        });

        // 429 عبر AJAX/JSON (كنماذج fetch) كان يصل المستخدم بنصّ «Too Many Attempts.» الإنجليزيّ
        // الخام لأنّ مسار JSON يتجاوز صفحة errors/429.blade.php العربيّة. نُعرّب **مسار JSON فقط**
        // بالشكل المتوقَّع (success/message)؛ ونُبقي طلبات الويب العاديّة على صفحة 429 القياسيّة
        // (بحالة 429 الصحيحة — تعتمدها اختبارات أمن الحدّ) بإرجاع null (تفويضٌ للمُعالِج الافتراضيّ).
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if (! $request->expectsJson()) {
                return null;
            }
            $headers = method_exists($e, 'getHeaders') ? $e->getHeaders() : [];

            return response()->json([
                'success' => false,
                'message' => 'محاولاتٌ كثيرة جداً. يرجى الانتظار دقيقةً ثمّ إعادة المحاولة.',
            ], 429, $headers);
        });

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
