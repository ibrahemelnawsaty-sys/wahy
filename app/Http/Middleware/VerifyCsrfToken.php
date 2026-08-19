<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);

        return is_string($request->session()->token()) &&
               is_string($token) &&
               hash_equals($request->session()->token(), $token);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        // أُزيل تدوير رمز CSRF على GET two-factor/verify: كان يجعل الصفحة أحاديّة الاستعمال فيُنتج
        // «419 Page Expired» لأيّ مستخدم يفتح الرابط مرّتين. (هذا الصنف غير مسجَّل أصلاً —
        // bootstrap/app.php يستعمل وسيط الإطار — ويبقى هنا لئلّا يُحيا النمط بالنسخ.)
        return parent::handle($request, $next);
    }
}
