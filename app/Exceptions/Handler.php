<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'two_factor_code',
        'token',
        'secret',
        'api_key',
        'api_token',
        'access_token',
        'refresh_token',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // في Production، إخفاء تفاصيل الأخطاء
        if (app()->environment('production')) {
            // أخطاء 404
            if ($e instanceof ModelNotFoundException || $e instanceof HttpException && $e->getStatusCode() === 404) {
                return response()->view('errors.404', [], 404);
            }

            // أخطاء المصادقة
            if ($e instanceof AuthenticationException) {
                return redirect()->route('login')->withErrors(['error' => 'يرجى تسجيل الدخول للمتابعة']);
            }

            // أخطاء التحقق من البيانات
            if ($e instanceof ValidationException) {
                return parent::render($request, $e);
            }

            // أخطاء 403
            if ($e instanceof HttpException && $e->getStatusCode() === 403) {
                return response()->view('errors.403', [], 403);
            }

            // أخطاء 419 CSRF
            if ($e instanceof HttpException && $e->getStatusCode() === 419) {
                return redirect()->back()->withErrors(['error' => 'انتهت صلاحية الجلسة. يرجى المحاولة مرة أخرى']);
            }

            // أخطاء 429 Rate Limiting
            if ($e instanceof HttpException && $e->getStatusCode() === 429) {
                return response()->view('errors.429', [], 429);
            }

            // أخطاء 500 عامة - لا نكشف التفاصيل
            if ($request->expectsJson()) {
                \Log::error('Unhandled exception [' . get_class($e) . ']: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ في الخادم. يرجى المحاولة مرة أخرى.',
                ], 500);
            }

            return response()->view('errors.500', [], 500);
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => 'غير مصرح'], 401)
            : redirect()->guest(route('login'));
    }
}
