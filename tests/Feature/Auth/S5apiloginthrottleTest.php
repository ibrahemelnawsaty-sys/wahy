<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * S5 — AuthApiController::login is rate-limited by (ip | hash(email)).
 *
 * Wired control (app/Http/Controllers/Api/AuthApiController.php::login):
 *   $throttleKey = 'api-login:'.$request->ip().'|'.hash('sha256', mb_strtolower($email));
 *   RateLimiter::tooManyAttempts($throttleKey, 5) -> 429
 *   RateLimiter::hit($throttleKey, 60) on each failed credential check
 *   RateLimiter::clear($throttleKey) on a successful login
 *
 * These tests drive the REAL route POST /api/v1/login through the HTTP test
 * client so the controller's RateLimiter logic runs. Cache store is `array`
 * (phpunit env), so RateLimiter is fully exercisable in PHPUnit-CLI.
 */
class S5apiloginthrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clean slate: the array cache is per-process but clearing here makes
        // the per-key thresholds deterministic regardless of test ordering.
        RateLimiter::clear('api-login:127.0.0.1|' . hash('sha256', 'victim@example.com'));
    }

    /**
     * (a) ATTACKER / ABUSE blocked.
     *
     * Burst wrong-password logins against the same email+ip. Each failed
     * attempt calls RateLimiter::hit. After 5 hits the next request short-
     * circuits to HTTP 429 BEFORE the credential check — proving the throttle
     * caps brute-force guessing on a single account.
     */
    public function test_burst_api_logins_past_the_limit_are_throttled_with_429(): void
    {
        User::factory()->create([
            'email' => 'victim@example.com',
            'password' => Hash::make('correct-horse-battery'),
            'status' => 'active',
        ]);

        // 5 allowed (but wrong-credential) attempts -> all 401, each registers a hit.
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'victim@example.com',
                'password' => 'wrong-guess-' . $i,
            ])->assertStatus(401);
        }

        // 6th attempt is over the limit -> 429, regardless of password.
        $blocked = $this->postJson('/api/v1/login', [
            'email' => 'victim@example.com',
            'password' => 'wrong-guess-6',
        ]);

        $blocked->assertStatus(429)
            ->assertJson(['success' => false]);

        // Critical: once the key is locked, even the CORRECT password is blocked
        // for this ip+email window — the attacker cannot ride the lockout to a
        // free guess, and the lockout is real (not just on bad creds).
        $this->postJson('/api/v1/login', [
            'email' => 'victim@example.com',
            'password' => 'correct-horse-battery',
        ])->assertStatus(429);
    }

    /**
     * (a-cont) A legit mobile login WITHIN the limit returns a token.
     *
     * Same abuse surface, but the well-behaved client stays under 5 attempts
     * and gets a working Bearer token — the throttle does not punish normal use.
     */
    public function test_legit_mobile_login_within_limit_returns_a_token(): void
    {
        User::factory()->create([
            'email' => 'mobile-user@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'mobile-user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * (b) LEGITIMATE user completing the normal flow is NEVER blocked.
     *
     * A single correct-credential request from a fresh ip+email key must
     * succeed. This is the happy path the throttle must never break.
     */
    public function test_legit_single_correct_login_is_not_blocked(): void
    {
        User::factory()->create([
            'email' => 'good-user@example.com',
            'password' => Hash::make('s3cret-pass'),
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'good-user@example.com',
            'password' => 's3cret-pass',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertIsString($response->json('data.token'));
    }

    /**
     * (b-cont) A successful login CLEARS the counter, so a legit user who
     * fumbled a few times then typed the right password is not stuck behind
     * the lockout on their next attempt.
     */
    public function test_successful_login_clears_throttle_for_subsequent_attempt(): void
    {
        User::factory()->create([
            'email' => 'fumble@example.com',
            'password' => Hash::make('right-pass'),
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        // A couple of mistyped attempts (still under the limit).
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'fumble@example.com',
                'password' => 'oops-' . $i,
            ])->assertStatus(401);
        }

        // Correct password succeeds AND clears the counter.
        $this->postJson('/api/v1/login', [
            'email' => 'fumble@example.com',
            'password' => 'right-pass',
        ])->assertStatus(200);

        // Immediately logging in again is fine — counter was reset, no 429.
        $this->postJson('/api/v1/login', [
            'email' => 'fumble@example.com',
            'password' => 'right-pass',
        ])->assertStatus(200);
    }
}
