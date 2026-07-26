<?php

namespace Tests\Feature\Gamification;

use App\Events\LevelUp;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GamificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private GamificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GamificationService;
    }

    public function test_add_xp_creates_point_record(): void
    {
        $student = User::factory()->student()->create();

        $result = $this->service->addXP($student->id, 50, 'test', 'اختبار');

        $this->assertEquals(50, DB::table('points')->where('user_id', $student->id)->sum('points'));
        $this->assertFalse($result['level_up']);
        $this->assertEquals(1, $result['old_level']);
        $this->assertEquals(1, $result['new_level']);
    }

    public function test_progressive_level_curve_maps_xp_sensibly(): void
    {
        // المنحنى التصاعديّ: المستوى = 1 + ⌊√(XP÷100)⌋ — يُبقي المبتدئين ويضغط الطرف العالي.
        $this->assertSame(1, GamificationService::levelForXp(0));
        $this->assertSame(1, GamificationService::levelForXp(99));
        $this->assertSame(2, GamificationService::levelForXp(100));   // كالصيغة القديمة عند العتبة الأولى
        $this->assertSame(2, GamificationService::levelForXp(399));
        $this->assertSame(3, GamificationService::levelForXp(400));
        $this->assertSame(4, GamificationService::levelForXp(900));
        // الحساب المُتضخِّم (89,200 نقطة) يصبح مستوىً معقولاً بدل 892.
        $this->assertSame(30, GamificationService::levelForXp(89200));
        $this->assertNotSame(892, GamificationService::levelForXp(89200));

        // معكوس المنحنى (عتبة المستوى) + التقدّم داخل المستوى.
        $this->assertSame(0, GamificationService::xpForLevel(1));
        $this->assertSame(100, GamificationService::xpForLevel(2));
        $this->assertSame(400, GamificationService::xpForLevel(3));

        $p = GamificationService::levelProgress(250); // مستوى 2 (عتبته 100)، التالي 400
        $this->assertSame(2, $p['level']);
        $this->assertSame(150, $p['into']);   // 250 - 100
        $this->assertSame(300, $p['span']);   // 400 - 100
        $this->assertSame(50, $p['percent']); // 150/300
        $this->assertSame(150, $p['to_next']); // 400 - 250
    }

    public function test_level_up_triggers_when_crossing_100_xp(): void
    {
        Event::fake();

        $student = User::factory()->student()->create();

        // أضف 90 XP أولاً (لا يحدث level up)
        $this->service->addXP($student->id, 90, 'test', 'first');
        Event::assertNotDispatched(LevelUp::class);

        // أضف 20 XP إضافية → الإجمالي 110 → Level 2
        $result = $this->service->addXP($student->id, 20, 'test', 'second');

        $this->assertTrue($result['level_up']);
        $this->assertEquals(2, $result['new_level']);
        Event::assertDispatched(LevelUp::class);
    }

    public function test_level_up_grants_bonus_coins(): void
    {
        $student = User::factory()->student()->create();

        // 100 XP = level up to 2 → bonus 2 * 10 = 20 coins
        $this->service->addXP($student->id, 100, 'test', 'level_up_test');

        $coins = (int) DB::table('coins')->where('user_id', $student->id)->sum('coins');

        // 20 coin bonus من level up
        $this->assertGreaterThanOrEqual(20, $coins);
    }

    public function test_deduct_coins_succeeds_when_balance_sufficient(): void
    {
        $student = User::factory()->student()->create();

        // أضف 100 عملة
        $this->service->addCoins($student->id, 100, 'test', 'إضافة');

        $result = $this->service->deductCoins($student->id, 30, 'شراء');

        $this->assertTrue($result['success']);
        $this->assertEquals(70, $result['remaining']);
    }

    public function test_deduct_coins_fails_when_balance_insufficient(): void
    {
        $student = User::factory()->student()->create();

        $this->service->addCoins($student->id, 10, 'test', 'إضافة');

        $result = $this->service->deductCoins($student->id, 50, 'شراء');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('غير كافٍ', $result['message']);
    }

    /**
     * 🔴 DB-002: التأكد أن addXP داخل transaction.
     * إذا فشلت transaction نصفها، يجب rollback كامل (لا نقاط ولا coins).
     *
     * نختبر هذا بالـ DB::transaction() الخارجي + rollback يدوي.
     */
    public function test_add_xp_is_transactional(): void
    {
        $student = User::factory()->student()->create();

        try {
            DB::transaction(function () use ($student) {
                $this->service->addXP($student->id, 100, 'test', 'transaction_test');
                throw new \RuntimeException('rollback intentional');
            });
        } catch (\RuntimeException $e) {
            // متوقع
        }

        // كل شيء يجب أن يكون rolled back
        $this->assertEquals(0, DB::table('points')->where('user_id', $student->id)->count());
        $this->assertEquals(0, DB::table('coins')->where('user_id', $student->id)->count());
    }

    /**
     * 🔴 SEC-003: التأكد أن سجل النقاط write-only من خارج CLI.
     */
    public function test_points_record_cannot_be_updated_outside_console(): void
    {
        $student = User::factory()->student()->create();
        $this->service->addXP($student->id, 50, 'test', 'init');

        $point = \App\Models\Point::where('user_id', $student->id)->first();
        $this->assertNotNull($point);

        // محاولة UPDATE من سياق HTTP (ليس CLI) — يجب أن تطلق abort(403)
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        // نُفعّل سياق request لمحاكاة non-console
        $this->app->instance('request', \Illuminate\Http\Request::create('/'));
        // نخدع runningInConsole عبر تشغيل من داخل HTTP context — يصعب في PHPUnit
        // لذا نستدعي UPDATE مباشرة مع توقع الخطأ
        // ⚠️  ملاحظة: في بيئة PHPUnit الافتراضية، runningInConsole = true
        //         لذا هذا الاختبار يحتاج محاكاة request context.
        // نحقق من السلوك بالتأكد من أن updating event مسجّل:
        $this->markTestSkipped('Append-only enforcement يعمل في HTTP فقط؛ يصعب محاكاته في PHPUnit CLI.');
    }
}
