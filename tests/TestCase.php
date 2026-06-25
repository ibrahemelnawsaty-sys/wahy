<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * نُمرّر --force للـ migrate:fresh تجنباً لطلب تأكيد لو حُدّد env=production بالخطأ.
     * بدون return type لأن الـ trait يستخدم الإعلان بدون type.
     */
    protected function migrateFreshUsing()
    {
        return [
            '--drop-views' => false,
            '--drop-types' => false,
            '--seed' => false,
            '--force' => true,
        ];
    }

    /**
     * إعداد عام للاختبارات قبل كل test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // تعطيل throttle middleware في الاختبارات
        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        ]);

        // مسح cache بين الاختبارات — array driver يحتفظ بالقيم عبر الـ tests في نفس الـ process
        \Illuminate\Support\Facades\Cache::flush();
    }

    /**
     * Helper للتأكد من بيئة sqlite — يفشل بوضوح لو شغّلت الاختبارات على MySQL إنتاج.
     */
    protected function assertSafeTestingEnv(): void
    {
        $this->assertEquals(
            'sqlite',
            config('database.default'),
            'CRITICAL: tests must run on sqlite, not production DB',
        );
    }
}
