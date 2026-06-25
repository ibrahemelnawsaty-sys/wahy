<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * S1 — AuthController::login custom cache lockout re-keyed to ip|sha256(email).
 *
 * Wired control (app/Http/Controllers/AuthController.php::login, ~L44-96):
 *   $identifier = $request->ip().'|'.hash('sha256', strtolower((string) $request->email));
 *   $cacheKey   = 'login_attempts_'.$identifier;
 *   $lockoutKey = 'login_lockout_'.$identifier;
 *   - each wrong-credential request increments $cacheKey;
 *   - at $attempts >= 4 it writes $lockoutKey (escalating 30*(n-3) min) and the
 *     next requests for that key are short-circuited with a lockout message;
 *   - the generic failure message ('بيانات الدخول غير صحيحة.') is identical for
 *     an existing email and a non-existent one (no user enumeration).
 *
 * BEFORE the fix the identifier was keyed on the VICTIM user-id, so an attacker
 * could lock a real account out by failing its email. After the re-key the
 * lockout is bound to (sourceIp | emailHash): an attacker failing from IP-A only
 * locks 'IP-A|hash(email)', leaving the legitimate owner on IP-B untouched —
 * while credential-stuffing from a single ip|email is still capped.
 *
 * These tests drive the REAL route POST /login through the HTTP test client.
 * The route-group throttle middleware is disabled by the base TestCase, so the
 * control under test here is purely the controller's cache()-based lockout,
 * which IS exercisable (cache store = array per phpunit env). Source IP is set
 * via REMOTE_ADDR so each request resolves a distinct $request->ip().
 */
class S1lockoutrekeyTest extends TestCase
{
    use RefreshDatabase;

    private const ATTACKER_IP = '203.0.113.10';

    private const VICTIM_IP = '198.51.100.20';

    private const VICTIM_EMAIL = 'victim@example.com';

    /**
     * Post a login attempt from an explicit source IP (drives $request->ip()).
     *
     * @param  array<string, mixed>  $payload
     */
    private function loginFromIp(string $ip, array $payload): \Illuminate\Testing\TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->from('/login')
            ->post('/login', $payload);
    }

    /**
     * (a) ATTACKER / ABUSE blocked — the targeted-lockout attack no longer works.
     *
     * The attacker bursts 6 wrong-password logins against the victim's email from
     * IP-A. That trips the lockout for the key 'IP-A|hash(victimEmail)'. The victim
     * then logs in from a DIFFERENT IP-B with the CORRECT password and is NOT
     * blocked — proving the lockout is no longer keyed on the victim account and
     * cannot be weaponised against them.
     */
    public function test_attacker_burst_does_not_lock_out_the_real_owner_on_another_ip(): void
    {
        User::factory()->create([
            'email' => self::VICTIM_EMAIL,
            'password' => Hash::make('correct-horse-battery'),
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        // Attacker hammers the victim's email from IP-A: 6 wrong-password tries.
        for ($i = 0; $i < 6; $i++) {
            $this->loginFromIp(self::ATTACKER_IP, [
                'email' => self::VICTIM_EMAIL,
                'password' => 'attacker-guess-' . $i,
            ]);
        }

        // The attacker's own key IS now locked (the lockout itself works)...
        $attackerKey = self::ATTACKER_IP . '|' . hash('sha256', strtolower(self::VICTIM_EMAIL));
        $this->assertNotNull(
            Cache::get('login_lockout_' . $attackerKey),
            'Attacker source-key should be locked after >=4 failures.',
        );

        // ...but the lockout did NOT bleed onto the victim account/identity.
        // Victim, from a DIFFERENT IP, with the CORRECT password, is let through.
        $victimKey = self::VICTIM_IP . '|' . hash('sha256', strtolower(self::VICTIM_EMAIL));
        $this->assertNull(
            Cache::get('login_lockout_' . $victimKey),
            'Victim source-key must NOT be locked by the attacker on another IP.',
        );

        $response = $this->loginFromIp(self::VICTIM_IP, [
            'email' => self::VICTIM_EMAIL,
            'password' => 'correct-horse-battery',
        ]);

        // Success = a redirect that is NOT the lockout bounce back to /login with an error.
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    /**
     * (a-cont) Credential-stuffing from a SINGLE ip|email is still throttled.
     *
     * Six wrong-password tries against the same key must trip the lockout: the 4th
     * failure (and onward) returns the lockout message, not just the generic one,
     * and even a CORRECT password on that key afterwards is rejected by the lock.
     */
    public function test_stuffing_from_one_ip_email_is_still_locked_out(): void
    {
        User::factory()->create([
            'email' => self::VICTIM_EMAIL,
            'password' => Hash::make('correct-horse-battery'),
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        // Failures 1..3 -> generic "invalid credentials", no lockout yet.
        for ($i = 0; $i < 3; $i++) {
            $resp = $this->loginFromIp(self::ATTACKER_IP, [
                'email' => self::VICTIM_EMAIL,
                'password' => 'stuff-' . $i,
            ]);
            $resp->assertRedirect('/login');
        }

        $key = self::ATTACKER_IP . '|' . hash('sha256', strtolower(self::VICTIM_EMAIL));
        $this->assertNull(
            Cache::get('login_lockout_' . $key),
            'No lockout should exist before the 4th failure.',
        );

        // 4th..6th failures -> lockout is set and surfaced.
        for ($i = 3; $i < 6; $i++) {
            $resp = $this->loginFromIp(self::ATTACKER_IP, [
                'email' => self::VICTIM_EMAIL,
                'password' => 'stuff-' . $i,
            ]);
            $resp->assertRedirect('/login');
            $resp->assertSessionHasErrors('error');
        }

        $this->assertNotNull(
            Cache::get('login_lockout_' . $key),
            'Lockout must be set after >=4 failures on the same ip|email.',
        );

        // Even the CORRECT password on this locked key is bounced: the attacker
        // cannot ride their own lockout to a free guess.
        $blocked = $this->loginFromIp(self::ATTACKER_IP, [
            'email' => self::VICTIM_EMAIL,
            'password' => 'correct-horse-battery',
        ]);
        $blocked->assertRedirect('/login');
        $blocked->assertSessionHasErrors('error');
        $this->assertGuest();
    }

    /**
     * (a-cont) The failed-password response does NOT distinguish an existing email
     * from a non-existent one (no user enumeration). Same status, same redirect,
     * same single generic error message under the same key, in both cases.
     */
    public function test_failed_login_does_not_reveal_whether_email_exists(): void
    {
        User::factory()->create([
            'email' => 'real-user@example.com',
            'password' => Hash::make('correct-horse-battery'),
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        // Existing email, wrong password (use a fresh IP key so no lockout interferes).
        $existing = $this->loginFromIp('203.0.113.50', [
            'email' => 'real-user@example.com',
            'password' => 'definitely-wrong',
        ]);

        // Non-existent email (fresh, distinct IP key).
        $missing = $this->loginFromIp('203.0.113.51', [
            'email' => 'no-such-user@example.com',
            'password' => 'definitely-wrong',
        ]);

        // Identical observable outcome: same status + same redirect target.
        $this->assertSame($existing->getStatusCode(), $missing->getStatusCode());
        $existing->assertRedirect('/login');
        $missing->assertRedirect('/login');

        // Identical generic error message — nothing leaks the account's existence.
        // (Both carry the SAME generic 'invalid credentials' bag; no remaining-attempts
        // count and no existence signal differs between a real and an unknown email.)
        $existing->assertSessionHasErrors(['error' => 'بيانات الدخول غير صحيحة.']);
        $missing->assertSessionHasErrors(['error' => 'بيانات الدخول غير صحيحة.']);
        $this->assertGuest();
    }

    /**
     * (b) LEGITIMATE user completing the normal flow is NEVER blocked.
     *
     * A single correct-credential request from a fresh ip|email key succeeds:
     * the user is authenticated and redirected, with no lockout and no errors.
     * This is the happy path the re-key must never break.
     */
    public function test_legit_single_correct_login_is_not_blocked(): void
    {
        User::factory()->create([
            'email' => 'good-user@example.com',
            'password' => Hash::make('s3cret-pass'),
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        $response = $this->loginFromIp('192.0.2.77', [
            'email' => 'good-user@example.com',
            'password' => 's3cret-pass',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();

        // No lockout artifact was created for a clean login.
        $key = '192.0.2.77|' . hash('sha256', 'good-user@example.com');
        $this->assertNull(Cache::get('login_lockout_' . $key));
        $this->assertNull(Cache::get('login_attempts_' . $key));
    }

    /**
     * (b-cont) A legit user who fumbled a few times (under the threshold) then
     * typed the right password is NOT stuck behind the lockout, and the success
     * clears the attempt counter for their next login.
     */
    public function test_few_fumbles_then_correct_password_succeeds_and_clears_counter(): void
    {
        User::factory()->create([
            'email' => 'fumble@example.com',
            'password' => Hash::make('right-pass'),
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        $ip = '192.0.2.88';

        // Two mistyped attempts — still under the 4-failure threshold.
        for ($i = 0; $i < 2; $i++) {
            $this->loginFromIp($ip, [
                'email' => 'fumble@example.com',
                'password' => 'oops-' . $i,
            ])->assertRedirect('/login');
        }

        // Correct password succeeds AND clears the attempt/lockout keys.
        $ok = $this->loginFromIp($ip, [
            'email' => 'fumble@example.com',
            'password' => 'right-pass',
        ]);
        $ok->assertRedirect();
        $ok->assertSessionHasNoErrors();
        $this->assertAuthenticated();

        $key = $ip . '|' . hash('sha256', 'fumble@example.com');
        $this->assertNull(Cache::get('login_attempts_' . $key), 'Successful login must clear the attempt counter.');
        $this->assertNull(Cache::get('login_lockout_' . $key));
    }
}
