<?php

namespace Tests\Feature\Auth;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * S4 — ADMIN NOT LOCKED OUT (force-2fa enforcement intentionally disabled).
 *
 * Pass-4 originally applied the Force2FAForAdmins middleware to the admin route
 * group. On production shared hosting that enforcement was removed (routes/web.php)
 * because a non-enrolled admin could be redirect-trapped out of their own panel if
 * the self-enroll page diverged from the test environment — an unacceptable
 * admin-lockout risk. The middleware + 'force-2fa' alias remain in place for a
 * future, prod-verified re-enable; 2FA still works OPT-IN on web + API login for
 * users who choose to enable it.
 *
 * These tests lock the safety guarantee: a non-enrolled admin reaches their own
 * panel and is NEVER bounced/locked out. If anyone re-applies force-2fa to a route
 * group before a prod-verified self-enroll path exists, these fail — by design.
 */
class S42faapplieduntrappedTest extends TestCase
{
    use RefreshDatabase;

    public function test_nonenrolled_super_admin_is_not_locked_out_of_admin_area(): void
    {
        $admin = User::factory()->superAdmin()->create([
            'two_factor_enabled' => false,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $this->assertFalse($response->isRedirect(), 'non-enrolled super_admin must reach the admin area, never be force-2fa redirect-trapped');
    }

    public function test_nonenrolled_school_admin_is_not_locked_out(): void
    {
        $school = School::factory()->create();
        $schoolAdmin = User::factory()->create([
            'role' => 'school_admin',
            'school_id' => $school->getKey(),
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        $response = $this->actingAs($schoolAdmin)->get('/school-admin/dashboard');

        $response->assertStatus(200);
        $this->assertFalse($response->isRedirect(), 'non-enrolled school_admin must not be locked out');
    }

    public function test_enrolled_admin_also_reaches_admin_area(): void
    {
        $admin = User::factory()->superAdmin()->create([
            'two_factor_enabled' => true,
        ]);

        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);
    }
}
