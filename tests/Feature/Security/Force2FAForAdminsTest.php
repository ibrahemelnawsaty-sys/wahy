<?php

namespace Tests\Feature\Security;

use App\Http\Middleware\Force2FAForAdmins;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * 🔴 SEC-022: التأكد أن middleware Force2FAForAdmins يجبر أدمن بدون 2FA على إعداده.
 */
class Force2FAForAdminsTest extends TestCase
{
    use RefreshDatabase;

    private Force2FAForAdmins $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new Force2FAForAdmins;
    }

    public function test_passes_through_for_non_admin_users(): void
    {
        $student = User::factory()->student()->create(['two_factor_enabled' => false]);
        $this->actingAs($student);

        $request = Request::create('/student/dashboard');
        $next = fn () => response('OK');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_redirects_super_admin_without_2fa(): void
    {
        $admin = User::factory()->superAdmin()->create(['two_factor_enabled' => false]);
        $this->actingAs($admin);

        $request = Request::create('/admin/dashboard');
        $next = fn () => response('admin dashboard');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_allows_super_admin_with_2fa_enabled(): void
    {
        $admin = User::factory()->superAdmin()->create(['two_factor_enabled' => true]);
        $this->actingAs($admin);

        $request = Request::create('/admin/dashboard');
        $next = fn () => response('admin dashboard');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals('admin dashboard', $response->getContent());
    }

    public function test_redirects_school_admin_without_2fa(): void
    {
        $admin = User::factory()->schoolAdmin()->create(['two_factor_enabled' => false]);
        $this->actingAs($admin);

        $request = Request::create('/school-admin/dashboard');
        $next = fn () => response('OK');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_returns_json_for_api_requests(): void
    {
        $admin = User::factory()->superAdmin()->create(['two_factor_enabled' => false]);
        $this->actingAs($admin);

        $request = Request::create('/api/v1/admin/users', 'GET');
        $request->headers->set('Accept', 'application/json');

        $next = fn () => response('OK');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(403, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        $this->assertEquals('admin_2fa_required', $payload['code']);
    }

    public function test_guest_user_passes_through(): void
    {
        // لا actingAs
        $request = Request::create('/login');
        $next = fn () => response('login page');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals('login page', $response->getContent());
    }
}
