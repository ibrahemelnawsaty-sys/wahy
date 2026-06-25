<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Headers أساسية
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS — فقط في الإنتاج عبر HTTPS (يمنع downgrade attack)
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content-Security-Policy — يبدأ بـ Report-Only لتجنّب كسر الواجهة
        // ⚠️  بعد المراقبة في console المتصفح لمدة أسبوع، حوّل إلى Content-Security-Policy فقط (بدون Report-Only)
        // unsafe-inline ضروري حالياً للسكربتات/الستايلات Inline في Blade. عند Migration إلى nonces، أزله.
        if (! $response->headers->has('Content-Security-Policy')
            && ! $response->headers->has('Content-Security-Policy-Report-Only')) {

            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://api.qrserver.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
                "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
                "img-src 'self' data: https: blob:",
                "media-src 'self' data: https: blob:",
                "connect-src 'self' https:",
                "frame-ancestors 'self'",
                "base-uri 'self'",
                "form-action 'self'",
                "object-src 'none'",
            ]);

            // Report-Only في الإنتاج، Enforce في غير الإنتاج (بدون kicker على الإنتاج حتى نتأكد)
            $response->headers->set('Content-Security-Policy-Report-Only', $csp);
        }

        return $response;
    }
}
