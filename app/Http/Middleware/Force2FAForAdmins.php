<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * 🔴 SEC-022: يُجبر الأدوار الإدارية (super_admin / school_admin) على تفعيل 2FA.
 *
 * إن لم يفعّل الأدمن 2FA، يُعاد توجيهه لصفحة الإعدادات لتفعيله أولاً.
 *
 * استخدام في bootstrap/app.php:
 *   $middleware->alias(['force-2fa' => Force2FAForAdmins::class]);
 *
 * ثم في routes/web.php:
 *   Route::middleware(['auth', 'role:super_admin', 'force-2fa'])->prefix('admin')...
 */
class Force2FAForAdmins
{
    /** المسارات المستثناة (Setup 2FA + Logout) */
    private const EXEMPT_ROUTES = [
        'two-factor.verify',
        'two-factor.verify.post',
        'two-factor.resend',
        'logout',
        'password.change',
        'password.change.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $this->isAdminRole($user->role)) {
            return $next($request);
        }

        // مسار محايد (logout, 2FA setup) — اسمح
        if ($this->isExemptRoute($request)) {
            return $next($request);
        }

        // الأدمن يجب أن يكون مفعّلاً 2FA
        if (! $user->two_factor_enabled) {
            // AJAX/API requests: 403 JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تفعيل المصادقة الثنائية (2FA) للوصول كأدمن',
                    'code' => 'admin_2fa_required',
                ], 403);
            }

            return redirect()
                ->route('two-factor.verify')
                ->with('warning', 'يجب تفعيل المصادقة الثنائية أولاً للوصول كأدمن');
        }

        return $next($request);
    }

    private function isAdminRole(?string $role): bool
    {
        return in_array($role, [
            UserRole::SuperAdmin->value,
            UserRole::SchoolAdmin->value,
        ], true);
    }

    private function isExemptRoute(Request $request): bool
    {
        $routeName = optional($request->route())->getName();

        return $routeName && in_array($routeName, self::EXEMPT_ROUTES, true);
    }
}
