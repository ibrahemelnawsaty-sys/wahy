<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChangeRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق من أن المستخدم مسجل دخول
        if (auth()->check()) {
            $user = auth()->user();
            
            // إذا كان المستخدم يحتاج لتغيير كلمة المرور
            if ($user->password_change_required) {
                // السماح بالوصول لصفحة تغيير كلمة المرور وتسجيل الخروج فقط
                if (!$request->routeIs('password.change') &&
                    !$request->routeIs('password.change.update') &&
                    !$request->routeIs('password.update') &&
                    !$request->routeIs('logout')) {
                    return redirect()->route('password.change')
                        ->with('warning', 'يجب عليك تغيير كلمة المرور المؤقتة قبل المتابعة');
                }
            }
        }
        
        return $next($request);
    }
}
