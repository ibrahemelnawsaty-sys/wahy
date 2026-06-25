<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // فحص إذا كان وضع الصيانة مفعل
        if (setting('maintenance_mode', false)) {
            // السماح للمسؤولين بالدخول
            if ($request->user() && $request->user()->role === 'super_admin') {
                return $next($request);
            }

            // السماح بصفحات تسجيل الدخول والخروج والـ admin panel
            if ($request->is('login') ||
                $request->is('logout') ||
                $request->is('admin') ||
                $request->is('admin/*') ||
                $request->is('register')) {
                return $next($request);
            }

            // عرض صفحة الصيانة للزوار
            return response()->view('maintenance', [
                'title' => 'الموقع تحت الصيانة',
                'message' => setting('maintenance_message', 'نعتذر، الموقع قيد الصيانة حالياً. سنعود قريباً!'),
            ], 503);
        }

        return $next($request);
    }
}
