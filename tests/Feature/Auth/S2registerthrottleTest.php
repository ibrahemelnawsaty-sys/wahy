<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Site: S2-register-throttle
 *
 * The public web registration POST (/register) is rate-limited via
 * `throttle:5,1` (5 requests/minute, keyed per-IP for guest routes).
 * No API register endpoint exists (routes/api.php has no register route),
 * so the web POST is the entire registration attack surface.
 *
 * NOTE: the shared Tests\TestCase::setUp() globally disables the
 * ThrottleRequests middleware for the whole suite. To actually exercise
 * the rate limit this class RE-ENABLES that middleware for itself via
 * withMiddleware() (we do not touch the shared TestCase). CACHE_STORE is
 * `array` in the phpunit env, so the limiter cache is live.
 */
class S2registerthrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The shared TestCase disables throttling for everyone — undo that
        // for this class only, so `throttle:5,1` genuinely runs.
        $this->withMiddleware(ThrottleRequests::class);

        // Start every test from a clean limiter slate (array cache persists
        // across tests within one process).
        RateLimiter::clear('');

        // The controller assigns a Spatie role on success; the seeder that
        // creates those roles does not run in tests, so create the ones the
        // public register flow needs. This makes the happy path genuinely
        // complete (User::create + assignRole both succeed).
        foreach (['student', 'teacher', 'parent'] as $name) {
            Role::findOrCreate($name, 'web');
        }

        // Registration sends a confirmation mail; keep it inert and assertable.
        Mail::fake();
    }

    /**
     * A valid registration payload with a fresh unique email so the only
     * thing that can reject a request is the throttle, never unique-email.
     */
    private function validPayload(?string $email = null): array
    {
        $email = $email ?? ('user' . uniqid() . '@example.com');

        return [
            'name' => 'مستخدم شرعي',
            'email' => $email,
            'phone' => '0501234567',
            'role' => UserRole::Student->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    /**
     * (a) ATTACKER / ABUSE — hammering the register endpoint past the
     * 5/min limit must start returning HTTP 429 (Too Many Requests),
     * while a single legitimate registration fired first still succeeds.
     */
    public function test_attacker_hammering_register_is_throttled_with_429(): void
    {
        // A legitimate registration up front succeeds (302) — proving the
        // throttle does not block real users on the normal path.
        $legit = $this->post('/register', $this->validPayload('legit-before@example.com'));
        $legit->assertStatus(302);
        $legit->assertSessionHas('registration_success', true);
        $this->assertDatabaseHas('users', [
            'email' => 'legit-before@example.com',
            'role' => UserRole::Student->value,
        ]);

        // Hammer the endpoint. Limit is 5/min per IP; we already spent 1.
        // Fire well past the limit and require that a 429 appears.
        $sawThrottle = false;
        for ($i = 0; $i < 12; $i++) {
            $response = $this->post('/register', $this->validPayload());

            if ($response->getStatusCode() === 429) {
                $sawThrottle = true;
                break;
            }
        }

        $this->assertTrue(
            $sawThrottle,
            'Hammering POST /register past the 5/min limit must return HTTP 429.',
        );
    }

    /**
     * Once the throttle trips, blocked (429) requests must never reach the
     * controller, so no extra accounts may be created beyond the limit.
     */
    public function test_throttled_requests_do_not_create_accounts(): void
    {
        $accepted = 0;
        $blocked = 0;

        for ($i = 0; $i < 15; $i++) {
            $response = $this->post('/register', $this->validPayload());

            if ($response->getStatusCode() === 429) {
                $blocked++;
            } elseif ($response->isRedirect()) {
                $accepted++;
            }
        }

        // Limit is 5/min, so at most 5 requests can be accepted...
        $this->assertLessThanOrEqual(
            5,
            $accepted,
            'No more than 5 registrations may be accepted within the 1-minute window.',
        );
        // ...and the excess must be actively blocked, not silently dropped.
        $this->assertGreaterThan(
            0,
            $blocked,
            'Excess requests must be blocked with 429, not accepted.',
        );

        // Persisted users must never exceed the number of accepted requests.
        $this->assertLessThanOrEqual($accepted, User::count());
    }

    /**
     * (b) LEGITIMATE — a single real registration through the normal flow
     * is NOT blocked: it redirects (302) with the success flag and the
     * account is persisted as inactive (pending admin approval).
     */
    public function test_legitimate_single_registration_is_not_blocked(): void
    {
        $response = $this->post('/register', $this->validPayload('real-user@example.com'));

        $response->assertStatus(302);
        $response->assertSessionHas('registration_success', true);

        $this->assertDatabaseHas('users', [
            'email' => 'real-user@example.com',
            'role' => UserRole::Student->value,
            // The account starts inactive — awaiting admin approval.
            'status' => 'inactive',
        ]);
    }

    /**
     * Several legitimate registrations that stay within the 5/min budget
     * all succeed — the throttle's normal allowance covers genuine traffic
     * and does not false-positive on real users.
     */
    public function test_several_legitimate_registrations_within_limit_all_succeed(): void
    {
        // 4 distinct legitimate users — under the 5/min ceiling.
        for ($i = 1; $i <= 4; $i++) {
            $email = "good-user-{$i}@example.com";
            $response = $this->post('/register', $this->validPayload($email));

            $response->assertStatus(302);
            $response->assertSessionHas('registration_success', true);
            $this->assertDatabaseHas('users', ['email' => $email]);
        }

        $this->assertSame(4, User::count());
    }
}
