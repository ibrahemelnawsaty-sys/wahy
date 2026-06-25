<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\Force2FAForAdmins;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * S4 — "force-2fa applied AND untrapped".
 *
 * Wired control:
 *   - bootstrap/app.php aliases 'force-2fa' => Force2FAForAdmins::class.
 *   - routes/web.php:168 applies it to the whole admin group:
 *       Route::prefix('admin')->name('admin.')->middleware(['can:access-admin', 'force-2fa'])
 *   - Force2FAForAdmins: an admin role (super_admin / school_admin) with
 *     two_factor_enabled = false is redirected to route('admin.users.edit', $self)
 *     — the REAL enrollment page that owns the two_factor_enabled field — and that
 *     route (plus admin.users.update) is EXEMPT, so the admin can load it and flip
 *     the flag without bouncing. The redirect deliberately does NOT target
 *     two-factor.verify / login (which require a login-time 2FA session and would
 *     dead-loop an already-logged-in admin).
 *
 * These tests drive the REAL HTTP routes (actingAs + get/put) so the middleware
 * stack (can:access-admin + force-2fa) actually runs.
 *
 * Relevant admin role for THIS group is super_admin: the access-admin gate
 * (AppServiceProvider) grants only super_admin, so super_admin is the role that
 * reaches force-2fa on these routes.
 */
class S42faapplieduntrappedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * (a) ATTACKER / ABUSE blocked — a 2FA-less admin is GATED out of the admin
     * area, but onto the ENROLLMENT page (not a dead end), and can self-enroll.
     *
     * Steps proven in one flow:
     *   1. Non-enrolled super_admin -> /admin/dashboard is NOT served; it is
     *      redirected (302) to their own admin.users.edit page.
     *   2. The redirect target is NOT the login / two-factor.verify dead-loop.
     *   3. The enrollment page actually LOADS (200) for that same admin — no
     *      infinite bounce back to login.
     *   4. They POST the enrollment form enabling 2FA — the flag flips in the DB.
     *   5. After enrolling, /admin/dashboard is now served (200) — they are
     *      through, proving the gate is satisfiable, not a trap.
     */
    public function test_nonenrolled_admin_is_redirected_to_enrollment_and_can_self_enroll(): void
    {
        $admin = User::factory()->superAdmin()->create([
            'name' => 'Pending Admin',
            'email' => 'pending-admin@example.com',
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        // 1 + 2: gated out of the admin area, redirected to the enrollment page,
        // NOT to the login / two-factor.verify dead-loop.
        $blocked = $this->actingAs($admin)->get('/admin/dashboard');

        $blocked->assertStatus(302)
            ->assertRedirect(route('admin.users.edit', $admin->getKey()));

        $location = $blocked->headers->get('Location');
        $this->assertStringNotContainsString('/login', $location);
        $this->assertStringNotContainsString('/two-factor', $location);

        // 3: the enrollment page itself is reachable for the non-enrolled admin
        // (exempt route) — proves there is no bounce loop.
        $this->actingAs($admin)
            ->get('/admin/users/' . $admin->getKey() . '/edit')
            ->assertStatus(200);

        // 4: the admin enables 2FA on their own record via the exempt update route.
        $this->actingAs($admin)
            ->put('/admin/users/' . $admin->getKey(), [
                'name' => 'Pending Admin',
                'email' => 'pending-admin@example.com',
                'role' => 'super_admin',
                'status' => 'active',
                'two_factor_enabled' => '1',
            ])
            ->assertRedirect(route('admin.users.index'));

        $admin->refresh();
        $this->assertTrue((bool) $admin->two_factor_enabled);

        // 5: now they pass the gate and the admin area is served.
        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertStatus(200);
    }

    /**
     * (a-cont) PROOF the route is now gated BY this middleware: the very same
     * non-enrolled admin reaches /admin/dashboard (200) once Force2FAForAdmins is
     * stripped from the stack. Without the middleware the admin area is reachable
     * 2FA-less; with it (previous test) it is not — so the gate is real and is the
     * middleware's doing, not some other guard.
     */
    public function test_without_the_middleware_the_admin_route_is_reachable_2fa_less(): void
    {
        $admin = User::factory()->superAdmin()->create([
            'two_factor_enabled' => false,
        ]);

        $this->actingAs($admin)
            ->withoutMiddleware(Force2FAForAdmins::class)
            ->get('/admin/dashboard')
            ->assertStatus(200);
    }

    /**
     * (a-cont) A non-admin hitting an admin route is NOT swept into the 2FA
     * enrollment trap. Force2FAForAdmins only acts on admin roles (it calls
     * $next for everyone else), so a student is handled by the access-admin gate
     * and gets a clean 403 — NOT a redirect into the admin's enrollment page.
     */
    public function test_non_admin_is_not_trapped_in_2fa_enrollment(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/admin/dashboard');

        $response->assertStatus(403);
        $this->assertFalse($response->isRedirect());
    }

    /**
     * (b) LEGITIMATE user completing the normal flow is NEVER blocked.
     *
     * A 2FA-enrolled super_admin makes a single, ordinary request to the admin
     * area and is served (200) — force-2fa lets enrolled admins straight through,
     * with no redirect to the enrollment page. This is the happy path the gate
     * must never break.
     */
    public function test_enrolled_admin_passes_through_to_admin_area(): void
    {
        $admin = User::factory()->superAdmin()->create([
            'two_factor_enabled' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $this->assertFalse($response->isRedirect());
    }

    /**
     * (b) HELD-HALF lock — force-2fa is intentionally NOT applied to the school-admin
     * group. school_admin has no self-service 2FA enrollment route (the only writer of
     * two_factor_enabled is the super_admin-only admin.users.edit form), so enforcing it
     * there would redirect every non-enrolled school_admin to a 403 dead-end = total
     * lockout. A non-enrolled school_admin must therefore still reach their own panel.
     * If anyone re-adds force-2fa to the school-admin group before an enrollment route
     * exists, this test fails — by design, that re-introduces the lockout.
     */
    public function test_nonenrolled_school_admin_is_not_locked_out(): void
    {
        $school = \App\Models\School::factory()->create();
        $schoolAdmin = User::factory()->create([
            'role' => 'school_admin',
            'school_id' => $school->getKey(),
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        // NOT redirected to the (super_admin-only) enrollment page — i.e. force-2fa is
        // not gating the school-admin group; the school_admin reaches their own dashboard.
        $response = $this->actingAs($schoolAdmin)->get('/school-admin/dashboard');

        $response->assertStatus(200);
        $this->assertFalse($response->isRedirect(), 'non-enrolled school_admin must not be force-2fa redirected (no enroll route → would be a lockout)');
    }
}
