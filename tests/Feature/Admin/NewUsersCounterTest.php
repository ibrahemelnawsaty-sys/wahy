<?php

namespace Tests\Feature\Admin;

use App\Models\RegistrationRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * عدّاد «مستخدم جديد» في ترويسة السوبر أدمن.
 * كان يَعدّ طلبات التسجيل المعلّقة فقط، فلا يظهر المستخدم المُنشأ مباشرةً أو المُسجَّل ذاتياً
 * (User غير نشط) رغم ظهوره في الإحصاءات. الآن = طلبات معلّقة + غير-نشط (بانتظار التفعيل)
 * ∪ مُنشأ خلال 7 أيام (مع استبعاد المسؤول الحاليّ).
 */
class NewUsersCounterTest extends TestCase
{
    use RefreshDatabase;

    public function test_counter_includes_inactive_recent_users_and_pending_requests(): void
    {
        $admin = User::factory()->superAdmin()->create(); // حديث ونشط لكنه مستبعَد (الحاليّ)
        $school = School::factory()->create();

        // (1) مستخدم غير نشط قديم (بانتظار التفعيل) → يُحتسَب
        $inactive = User::factory()->create(['status' => 'inactive']);
        DB::table('users')->where('id', $inactive->id)->update(['created_at' => now()->subDays(30)]);

        // (2) مستخدم نشط حديث (خلال 7 أيام) → يُحتسَب
        User::factory()->create(['status' => 'active']);

        // (3) مستخدم نشط قديم (> 7 أيام) → لا يُحتسَب
        $oldActive = User::factory()->create(['status' => 'active']);
        DB::table('users')->where('id', $oldActive->id)->update(['created_at' => now()->subDays(30)]);

        // (4) طلب تسجيل معلّق → يُحتسَب
        RegistrationRequest::create([
            'school_id' => $school->id,
            'name' => 'مسجّل جديد',
            'email' => 'pending@test.sa',
            'password' => bcrypt('secret123'),
            'role' => 'student',
            'status' => 'pending',
        ]);

        // المتوقّع: غير-نشط(1) + نشط-حديث(1) + طلب(1) = 3
        $this->actingAs($admin)
            ->get(route('admin.activity-approval.index'))
            ->assertOk()
            ->assertSee('header_new_users">3', false);
    }

    public function test_counter_excludes_old_active_users_and_self(): void
    {
        $admin = User::factory()->superAdmin()->create();

        // مستخدمون نشطون قدامى فقط — لا يُحتسَب أيّ منهم، ولا المسؤول الحاليّ
        foreach (range(1, 3) as $i) {
            $u = User::factory()->create(['status' => 'active']);
            DB::table('users')->where('id', $u->id)->update(['created_at' => now()->subDays(60)]);
        }

        $this->actingAs($admin)
            ->get(route('admin.activity-approval.index'))
            ->assertOk()
            ->assertSee('header_new_users">0', false);
    }
}
