<?php

namespace Tests\Feature\Gamification;

use App\Events\BadgeEarned;
use App\Events\StreakUpdated;
use App\Listeners\CheckBadgeEligibility;
use App\Models\Badge;
use App\Models\User;
use Database\Seeders\BadgesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * محرّك منح الشارات المبنيّ على الشرط (CheckBadgeEligibility) + البذرة + المخطّط.
 * يتحقّق من: المنح عند بلوغ العتبة، لا منح دونها، لا ازدواج، احترام مكافأة العملات القابلة للتهيئة،
 * وتعطيل البذرة للشارات القديمة عديمة الشرط.
 */
class BadgeAwardEngineTest extends TestCase
{
    use RefreshDatabase;

    private function seedBadges(): void
    {
        $this->seed(BadgesSeeder::class);
    }

    private function addPoints(User $u, int $pts): void
    {
        DB::table('points')->insert([
            'user_id' => $u->id,
            'points' => $pts,
            'reason' => 'test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** استدعاء المحرّك مباشرةً (معزول عن مستمع الإشعار). */
    private function runEngine(User $student): void
    {
        (new CheckBadgeEligibility)->handle(new StreakUpdated($student, 5));
    }

    private function earnedBadgeCoins(User $student): int
    {
        return (int) DB::table('coins')
            ->where('user_id', $student->id)
            ->where('source', 'badge_earned')
            ->sum('coins');
    }

    public function test_seeder_creates_twelve_conditional_badges(): void
    {
        $this->seedBadges();

        $this->assertSame(
            12,
            Badge::where('status', 'active')->whereNotNull('condition_type')->count()
        );
    }

    public function test_seeder_deactivates_legacy_conditionless_badges(): void
    {
        // شارة قديمة من بذرة سابقة بلا condition_type
        Badge::create([
            'name' => 'المتعاون',
            'description' => 'شارة قديمة',
            'type' => 'achievement',
            'status' => 'active',
        ]);

        $this->seedBadges();

        $this->assertSame('inactive', Badge::where('name', 'المتعاون')->first()->status);
    }

    /** شارة points مفردة معزولة (لا بذرة) — لتفادي تشابك عتبتَي points/level. */
    private function makeSingleBadge(int $value = 500, int $reward = 100): Badge
    {
        return Badge::create([
            'name' => 'شارة اختبار',
            'description' => 'شرط نقاط مفرد',
            'type' => 'achievement',
            'status' => 'active',
            'condition_type' => 'points',
            'condition_value' => $value,
            'coins_reward' => $reward,
        ]);
    }

    public function test_points_badge_awarded_when_threshold_met(): void
    {
        Event::fake([BadgeEarned::class]);
        $badge = $this->makeSingleBadge(500, 100);
        $student = User::factory()->student()->create();
        $this->addPoints($student, 500);

        $this->runEngine($student);

        $this->assertTrue($student->badges()->where('badges.id', $badge->id)->exists());
        $this->assertSame(100, $this->earnedBadgeCoins($student));
        Event::assertDispatched(BadgeEarned::class);
    }

    public function test_badge_not_awarded_below_threshold(): void
    {
        Event::fake([BadgeEarned::class]);
        $badge = $this->makeSingleBadge(500, 100);
        $student = User::factory()->student()->create();
        $this->addPoints($student, 499); // < 500

        $this->runEngine($student);

        $this->assertFalse($student->badges()->where('badges.id', $badge->id)->exists());
        $this->assertSame(0, $this->earnedBadgeCoins($student));
    }

    public function test_no_double_award_on_repeated_events(): void
    {
        Event::fake([BadgeEarned::class]);
        $this->seedBadges();
        $student = User::factory()->student()->create();
        $this->addPoints($student, 1000); // يستحق عدّة شارات (نقاط + مستوى)

        $this->runEngine($student); // منح أوّل
        $coinsAfterFirst = $this->earnedBadgeCoins($student);
        $rowsAfterFirst = DB::table('user_badges')->where('user_id', $student->id)->count();

        $this->runEngine($student); // إعادة — يجب أن تكون idempotent

        // لا صفوف إضافية ولا عملات إضافية مهما تكرّر الحدث
        $this->assertSame($rowsAfterFirst, DB::table('user_badges')->where('user_id', $student->id)->count());
        $this->assertSame($coinsAfterFirst, $this->earnedBadgeCoins($student));

        // كل شارة مكتسبة لها صفّ واحد بالضبط
        $star = Badge::where('name', 'نجم النقاط')->first();
        $this->assertSame(1, DB::table('user_badges')->where('user_id', $student->id)->where('badge_id', $star->id)->count());
    }

    public function test_configurable_coins_reward_is_used_not_hardcoded_fifty(): void
    {
        Event::fake([BadgeEarned::class]);
        // شارة مفردة بمكافأة 222 (ليست 50 الثابت القديم ولا 100)
        $badge = $this->makeSingleBadge(500, 222);
        $student = User::factory()->student()->create();
        $this->addPoints($student, 500);

        $this->runEngine($student);

        $this->assertTrue($student->badges()->where('badges.id', $badge->id)->exists());
        $this->assertSame(222, $this->earnedBadgeCoins($student));
    }

    public function test_inactive_badge_is_not_awarded(): void
    {
        Event::fake([BadgeEarned::class]);
        $this->seedBadges();
        Badge::where('name', 'نجم النقاط')->update(['status' => 'inactive']);

        $student = User::factory()->student()->create();
        $this->addPoints($student, 1000);

        $this->runEngine($student);

        $star = Badge::where('name', 'نجم النقاط')->first();
        $this->assertFalse($student->badges()->where('badges.id', $star->id)->exists());
    }

    public function test_engine_grants_through_real_event_dispatch(): void
    {
        // مسار حقيقي كامل (تسجيل المستمع + مستمع إشعار الشارة) — لا استثناء + المنح يحصل
        $this->seedBadges();
        $student = User::factory()->student()->create();
        $this->addPoints($student, 500);

        event(new StreakUpdated($student, 5));

        $star = Badge::where('name', 'نجم النقاط')->first();
        $this->assertTrue($student->fresh()->badges()->where('badges.id', $star->id)->exists());
    }
}
