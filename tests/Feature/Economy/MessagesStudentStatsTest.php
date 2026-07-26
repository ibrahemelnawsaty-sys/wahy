<?php

namespace Tests\Feature\Economy;

use App\Http\Controllers\MessagesController;
use App\Models\Coin;
use App\Models\Point;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * إحصائيات الطالب في MessagesController كانت تربط points+coins بـleftJoin مزدوج فتُنتج حاصل
 * ضرب ديكارتيّ: SUM(points) = النقاط × عدد صفوف العملات (والعكس) — فظهر «المستوى 30» و«90486
 * عملة» في صفحة الرسائل بينما الصحيح مستوى 4. هذا الاختبار يحرس ضدّ عودة الخطأ.
 */
class MessagesStudentStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_stats_not_inflated_by_points_coins_cartesian_join(): void
    {
        $student = User::factory()->student()->create();

        // 3 صفوف نقاط (مجموع 150) + 4 صفوف عملات (مجموع 40).
        // الربط الديكارتيّ كان يُنتج نقاطاً = 150×4 = 600، وعملات = 40×3 = 120.
        foreach ([100, 30, 20] as $p) {
            Point::create(['user_id' => $student->id, 'points' => $p, 'reason' => 'test']);
        }
        foreach ([10, 10, 10, 10] as $c) {
            Coin::create(['user_id' => $student->id, 'coins' => $c, 'reason' => 'test']);
        }

        $method = new \ReflectionMethod(MessagesController::class, 'getStudentStats');
        $method->setAccessible(true);
        $stats = $method->invoke(app(MessagesController::class), $student->fresh());

        $this->assertSame(150, $stats['total_points'], 'مجموع النقاط لا يتضخّم بعدد صفوف العملات');
        $this->assertSame(40, $stats['total_coins'], 'مجموع العملات لا يتضخّم بعدد صفوف النقاط');
        // 150 نقطة ⇒ المستوى 2 (لا مستوى متضخّم من 600)
        $this->assertSame(2, GamificationService::levelForXp($stats['total_points']));
    }
}
