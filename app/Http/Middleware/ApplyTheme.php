<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ApplyTheme
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // استخدام Cache بسيط بدون استعلامات معقدة
        $themeSettings = Cache::remember('theme_settings_simple', 3600, function () {
            try {
                // محاولة جلب الإعدادات بطريقة آمنة
                $settings = DB::table('settings')
                    ->whereIn('key', [
                        'primary_color',
                        'secondary_color',
                        'site_name',
                        'site_logo',
                    ])
                    ->pluck('value', 'key')
                    ->toArray();

                return array_merge([
                    'primary_color' => '#3CCB8A',
                    'secondary_color' => '#3B82F6',
                    'site_name' => 'منصة قيمّ',
                    'site_logo' => null,
                ], $settings);
            } catch (\Exception $e) {
                // في حالة الخطأ، استخدم القيم الافتراضية
                Log::error('ApplyTheme error: ' . $e->getMessage());

                return [
                    'primary_color' => '#3CCB8A',
                    'secondary_color' => '#3B82F6',
                    'site_name' => 'منصة قيمّ',
                    'site_logo' => null,
                ];
            }
        });

        View::share('themeSettings', $themeSettings);

        return $next($request);
    }
}
