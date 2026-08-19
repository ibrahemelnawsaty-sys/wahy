<?php

namespace Tests\Feature\Demo;

use App\Models\School;
use App\Models\User;
use App\Support\DemoScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 0 من خطّة حسابات الديمو (docs/DEMO_ACCOUNTS_PLAN.md) — البنية التحتيّة الخاملة:
 * العمود يبدأ false، النطاق notDemo يستثني، الوراثة من مدرسة الديمو، وأدوات DemoScope.
 * (حارس الـ403 للسوبر أدمن حصراً يُختبَر عبر HTTP في دفعة الواجهة — يُتجاوَز في CLI console.)
 */
class DemoAccountsBatch0Test extends TestCase
{
    use RefreshDatabase;

    public function test_is_demo_defaults_false(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->fresh()->is_demo, 'كل حساب جديد حقيقيّ افتراضيّاً');

        $school = School::factory()->create();
        $this->assertFalse($school->fresh()->is_demo);
    }

    public function test_not_demo_scope_excludes_demo_users(): void
    {
        User::factory()->count(2)->create(['is_demo' => false]);
        User::factory()->create(['is_demo' => true]);

        $this->assertSame(3, User::count());
        $this->assertSame(2, User::notDemo()->count(), 'النطاق يستثني حساب الديمو');
    }

    public function test_not_demo_scope_survives_join_qualified_column(): void
    {
        // الاسم مؤهَّل بالجدول → لا «عمود مبهم» داخل join
        $demo = User::factory()->create(['is_demo' => true]);
        $count = User::notDemo()->leftJoin('points', 'points.user_id', '=', 'users.id')->count();
        $this->assertSame(0, User::notDemo()->whereKey($demo->id)->count());
        $this->assertIsInt($count);
    }

    public function test_school_not_demo_scope(): void
    {
        School::factory()->create(['is_demo' => false]);
        School::factory()->create(['is_demo' => true]);
        $this->assertSame(1, School::notDemo()->count());
    }

    public function test_user_inherits_demo_flag_from_demo_school(): void
    {
        $demoSchool = School::factory()->create(['is_demo' => true]);
        $user = User::factory()->create(['school_id' => $demoSchool->id]);
        $this->assertTrue($user->fresh()->is_demo, 'مستخدم تحت مدرسة ديمو يرث العلم');
    }

    public function test_user_under_real_school_is_not_demo(): void
    {
        $realSchool = School::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['school_id' => $realSchool->id]);
        $this->assertFalse($user->fresh()->is_demo);
    }

    public function test_explicit_demo_flag_is_respected_under_real_school(): void
    {
        $realSchool = School::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['school_id' => $realSchool->id, 'is_demo' => true]);
        $this->assertTrue($user->fresh()->is_demo, 'وسم فرديّ صريح يُحترَم داخل مدرسة حقيقيّة');
    }

    public function test_demoscope_not_demo_ids_filters(): void
    {
        $real = User::factory()->create(['is_demo' => false]);
        $demo = User::factory()->create(['is_demo' => true]);

        $filtered = DemoScope::notDemoIds([$real->id, $demo->id]);
        $this->assertContains($real->id, $filtered);
        $this->assertNotContains($demo->id, $filtered);
        $this->assertSame([], DemoScope::notDemoIds([]));
    }
}
