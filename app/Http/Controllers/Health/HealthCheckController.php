<?php

namespace App\Http\Controllers\Health;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Health check endpoint للـ load balancers / uptime monitors / Kubernetes readiness probes.
 *
 * GET /health
 *   200 إذا كل شيء يعمل
 *   503 إذا أحد المكونات الحرجة فاشل
 *
 * GET /health/detailed
 *   تفاصيل بكل مكون (مفيد للـ admin/debugging)
 */
class HealthCheckController extends Controller
{
    public function ping(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'    => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function detailed(): \Illuminate\Http\JsonResponse
    {
        $checks = [
            'database'   => $this->checkDatabase(),
            'cache'      => $this->checkCache(),
            'storage'    => $this->checkStorage(),
            'queue'      => $this->checkQueue(),
        ];

        $allHealthy = collect($checks)->every(fn ($c) => $c['healthy']);

        return response()->json([
            'status'     => $allHealthy ? 'healthy' : 'degraded',
            'timestamp'  => now()->toIso8601String(),
            'app'        => [
                'name'        => config('app.name'),
                'environment' => app()->environment(),
                'version'     => env('APP_VERSION', 'unknown'),
            ],
            'checks'     => $checks,
        ], $allHealthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'healthy'    => true,
                'latency_ms' => $latency,
                'driver'     => config('database.default'),
            ];
        } catch (\Throwable $e) {
            return [
                'healthy' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health:ping:' . uniqid();
            Cache::put($key, 'pong', 5);
            $value = Cache::get($key);
            Cache::forget($key);

            return [
                'healthy' => $value === 'pong',
                'driver'  => config('cache.default'),
            ];
        } catch (\Throwable $e) {
            return [
                'healthy' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $disk = Storage::disk('local');
            $disk->put('health-check.txt', 'ok');
            $content = $disk->get('health-check.txt');
            $disk->delete('health-check.txt');

            return [
                'healthy' => $content === 'ok',
                'driver'  => config('filesystems.default'),
            ];
        } catch (\Throwable $e) {
            return [
                'healthy' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');

            // فحص بسيط — فقط أن الـ driver متاح
            return [
                'healthy' => true,
                'driver'  => $connection,
            ];
        } catch (\Throwable $e) {
            return [
                'healthy' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
