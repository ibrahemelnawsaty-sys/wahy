<?php

namespace Tests\Feature\Gamification;

use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * يختبر آلية الـ Shop Purchase (deductCoins) — الأكثر حساسية اقتصادياً.
 * SEC-003: يجب أن يكون transactional + lockForUpdate لمنع overspend.
 */
class ShopPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private GamificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GamificationService::class);
    }

    public function test_deduct_coins_creates_negative_record(): void
    {
        $student = User::factory()->student()->create();
        $this->service->addCoins($student->id, 100, 'test', 'init');

        $result = $this->service->deductCoins($student->id, 30, 'شراء قبعة');

        $this->assertTrue($result['success']);
        $this->assertEquals(70, $result['remaining']);

        // يجب أن يُنشأ سجل سلبي
        $negativeRecord = DB::table('coins')
            ->where('user_id', $student->id)
            ->where('coins', -30)
            ->first();

        $this->assertNotNull($negativeRecord);
        // deductCoins now routes through SpendService, which labels the audit row by its
        // spend family ('gamification_deduct') rather than the old hard-coded 'shop_purchase'.
        $this->assertEquals('gamification_deduct', $negativeRecord->source);
    }

    public function test_deduct_coins_zero_does_not_underflow(): void
    {
        $student = User::factory()->student()->create();
        // الطالب بلا نقاط

        $result = $this->service->deductCoins($student->id, 1, 'شراء');

        $this->assertFalse($result['success']);

        // لا يُنشأ سجل سلبي
        $count = DB::table('coins')->where('user_id', $student->id)->count();
        $this->assertEquals(0, $count);
    }

    public function test_balance_calculation_is_correct(): void
    {
        $student = User::factory()->student()->create();

        $this->service->addCoins($student->id, 100, 'test', '+100');
        $this->service->addCoins($student->id, 50, 'test', '+50');
        $this->service->deductCoins($student->id, 40, '-40');

        $balance = (int) DB::table('coins')->where('user_id', $student->id)->sum('coins');

        $this->assertEquals(110, $balance, '100 + 50 - 40 = 110');
    }

    public function test_purchase_transactional_rollback_on_failure(): void
    {
        $student = User::factory()->student()->create();
        $this->service->addCoins($student->id, 100, 'test', 'init');

        // محاولة عملية كبيرة داخل transaction خارجي ثم rollback
        $caught = false;
        try {
            DB::transaction(function () use ($student) {
                $this->service->deductCoins($student->id, 30, 'محاولة');
                throw new \RuntimeException('intentional rollback');
            });
        } catch (\RuntimeException $e) {
            $caught = true;
        }

        $this->assertTrue($caught);

        // الرصيد يجب أن يكون 100 (لم يُخصم شيء)
        $balance = (int) DB::table('coins')->where('user_id', $student->id)->sum('coins');
        $this->assertEquals(100, $balance, 'rollback يجب أن يلغي الخصم');
    }

    /**
     * محاولة محاكاة race condition (PHPUnit single-threaded — اختبار توثيقي).
     * في الإنتاج، lockForUpdate يمنع التداخل عبر MySQL row locking.
     */
    public function test_sequential_deductions_are_safe(): void
    {
        $student = User::factory()->student()->create();
        $this->service->addCoins($student->id, 100, 'test', 'init');

        // 5 خصومات متعاقبة بقيمة 30 — الإجمالي 150 لكن الرصيد فقط 100
        $successCount = 0;
        for ($i = 0; $i < 5; $i++) {
            $result = $this->service->deductCoins($student->id, 30, "شراء #{$i}");
            if ($result['success']) {
                $successCount++;
            }
        }

        // يجب أن تنجح 3 فقط (90 من 100)، والاثنتان الأخريان تفشلان (10 < 30)
        $this->assertEquals(3, $successCount);

        $balance = (int) DB::table('coins')->where('user_id', $student->id)->sum('coins');
        $this->assertEquals(10, $balance);
    }
}
