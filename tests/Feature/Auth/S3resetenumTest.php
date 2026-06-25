<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Site S3-reset-enum — password-reset must not leak account existence and
 * reset tokens must be single-use + expiring.
 *
 * Wired surface (verified against routes/web.php + AuthController):
 *  - POST /forgot-password  name=password.email   throttle:5,1 (inside guest+throttle:20,1)
 *      -> sendResetLink(): ALWAYS returns back()->with('status', <neutral>) whether
 *         the email exists or not (no existence oracle).
 *  - POST /reset-password   name=password.update
 *      -> resetPassword(): token stored hashed in password_reset_tokens (Hash::make),
 *         deleted on success (single-use); rejected after 60 min (expiry on created_at).
 *
 * The raw token only ever exists inside the mailer, so the legit-flow tests seed the
 * password_reset_tokens row with Hash::make($rawToken) and POST the raw token, which is
 * exactly what a user clicking the emailed link does.
 */
class S3resetenumTest extends TestCase
{
    use RefreshDatabase;

    private const NEUTRAL = 'إن وُجد البريد، أُرسل رابط إعادة التعيين';

    private const RESET_INVALID = 'رابط إعادة التعيين غير صحيح';

    private const RESET_EXPIRED = 'انتهت صلاحية رابط إعادة التعيين. يرجى طلب رابط جديد';

    private const RESET_SUCCESS = 'تم إعادة تعيين كلمة المرور بنجاح! يرجى تسجيل الدخول بكلمة المرور الجديدة';

    // ---------------------------------------------------------------------
    // (a) ATTACKER / ABUSE — no existence oracle on forgot-password
    // ---------------------------------------------------------------------

    public function test_forgot_password_response_is_identical_for_known_and_unknown_email(): void
    {
        User::factory()->create(['email' => 'known@example.com']);

        $known = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'known@example.com',
        ]);

        $unknown = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'nobody-here@example.com',
        ]);

        // Identical HTTP status — no status-code oracle.
        $this->assertSame($known->getStatusCode(), $unknown->getStatusCode());

        // Both redirect back (the neutral happy path), neither is a validation bounce.
        $known->assertRedirect('/forgot-password');
        $unknown->assertRedirect('/forgot-password');

        // Identical neutral flash for BOTH — the existence-revealing signal is gone.
        $known->assertSessionHas('status', self::NEUTRAL);
        $unknown->assertSessionHas('status', self::NEUTRAL);
        $known->assertSessionHasNoErrors();
        $unknown->assertSessionHasNoErrors();

        // Only the real account ever gets a token row — the response just doesn't reveal it.
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'known@example.com']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'nobody-here@example.com']);
    }

    public function test_reset_token_is_single_use_then_rejected_on_reuse(): void
    {
        $user = User::factory()->create([
            'email' => 'reuse@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $rawToken = bin2hex(random_bytes(32));
        DB::table('password_reset_tokens')->insert([
            'email' => 'reuse@example.com',
            'token' => Hash::make($rawToken),
            'created_at' => now(),
        ]);

        // First use — succeeds and consumes the token.
        $first = $this->from('/reset-password')->post('/reset-password', [
            'token' => $rawToken,
            'email' => 'reuse@example.com',
            'password' => 'brand-new-pass-1',
            'password_confirmation' => 'brand-new-pass-1',
        ]);
        $first->assertRedirect('/login');
        $first->assertSessionHas('success', self::RESET_SUCCESS);

        // Token row is gone — proof of single-use consumption.
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'reuse@example.com']);

        // Second use of the SAME raw token — rejected, no second password change.
        $second = $this->from('/reset-password')->post('/reset-password', [
            'token' => $rawToken,
            'email' => 'reuse@example.com',
            'password' => 'attacker-chosen-pass',
            'password_confirmation' => 'attacker-chosen-pass',
        ]);
        $second->assertRedirect('/reset-password');
        $second->assertSessionHasErrors(['email']);
        $this->assertSame(self::RESET_INVALID, session('errors')->first('email'));

        // The first (legit) password is the live one; the reuse attempt did NOT take.
        $user->refresh();
        $this->assertTrue(Hash::check('brand-new-pass-1', $user->password));
        $this->assertFalse(Hash::check('attacker-chosen-pass', $user->password));
    }

    public function test_expired_reset_token_is_rejected_and_purged(): void
    {
        $user = User::factory()->create([
            'email' => 'expired@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $rawToken = bin2hex(random_bytes(32));
        // created_at older than the 60-minute window.
        DB::table('password_reset_tokens')->insert([
            'email' => 'expired@example.com',
            'token' => Hash::make($rawToken),
            'created_at' => now()->subMinutes(61),
        ]);

        $response = $this->from('/reset-password')->post('/reset-password', [
            'token' => $rawToken,
            'email' => 'expired@example.com',
            'password' => 'should-not-apply-1',
            'password_confirmation' => 'should-not-apply-1',
        ]);

        $response->assertRedirect('/reset-password');
        $response->assertSessionHasErrors(['email']);
        $this->assertSame(self::RESET_EXPIRED, session('errors')->first('email'));

        // Expired row is purged, and the password was NOT changed.
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'expired@example.com']);
        $user->refresh();
        $this->assertTrue(Hash::check('old-password', $user->password));
    }

    public function test_wrong_token_for_existing_email_is_rejected_with_generic_message(): void
    {
        User::factory()->create(['email' => 'victim@example.com']);

        DB::table('password_reset_tokens')->insert([
            'email' => 'victim@example.com',
            'token' => Hash::make(bin2hex(random_bytes(32))),
            'created_at' => now(),
        ]);

        // Attacker guesses a token — must NOT validate.
        $response = $this->from('/reset-password')->post('/reset-password', [
            'token' => 'attacker-guessed-token',
            'email' => 'victim@example.com',
            'password' => 'attacker-pass-12',
            'password_confirmation' => 'attacker-pass-12',
        ]);

        $response->assertRedirect('/reset-password');
        $response->assertSessionHasErrors(['email']);
        $this->assertSame(self::RESET_INVALID, session('errors')->first('email'));
    }

    /**
     * The route carries throttle:5,1 (plus the group's throttle:20,1), but the project's
     * base Tests\TestCase::setUp() globally disables Illuminate\Routing\Middleware\
     * ThrottleRequests for every test. So the 429 ceiling is NOT exercisable in this
     * PHPUnit-CLI harness — documented here rather than silently passing.
     *
     * What IS exercisable: repeated forgot-password hits (the flooding-oracle abuse shape)
     * must STILL return the same neutral response every time and never turn into an
     * existence oracle, even across many requests.
     */
    public function test_repeated_forgot_password_requests_stay_neutral_no_existence_oracle(): void
    {
        User::factory()->create(['email' => 'flood-known@example.com']);

        $knownStatuses = [];
        $unknownStatuses = [];

        for ($i = 0; $i < 6; $i++) {
            $known = $this->from('/forgot-password')->post('/forgot-password', [
                'email' => 'flood-known@example.com',
            ]);
            $unknown = $this->from('/forgot-password')->post('/forgot-password', [
                'email' => 'flood-unknown@example.com',
            ]);

            $known->assertSessionHas('status', self::NEUTRAL);
            $unknown->assertSessionHas('status', self::NEUTRAL);

            $knownStatuses[] = $known->getStatusCode();
            $unknownStatuses[] = $unknown->getStatusCode();
        }

        // Every known response is byte-for-byte status-equal to its unknown counterpart —
        // no amount of repetition produces an existence-revealing divergence.
        $this->assertSame($knownStatuses, $unknownStatuses);

        // Real account still gets exactly one token row; the phantom never does.
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'flood-known@example.com']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'flood-unknown@example.com']);
    }

    // ---------------------------------------------------------------------
    // (b) LEGITIMATE user completing the normal flow is NOT blocked
    // ---------------------------------------------------------------------

    public function test_legit_user_single_forgot_request_succeeds_neutrally(): void
    {
        User::factory()->create(['email' => 'legit@example.com']);

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'legit@example.com',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', self::NEUTRAL);

        // A real, usable token row was created for the legitimate user.
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'legit@example.com']);
    }

    public function test_legit_user_completes_full_reset_flow_and_can_login_with_new_password(): void
    {
        $user = User::factory()->create([
            'email' => 'happy@example.com',
            'password' => Hash::make('old-password'),
            'status' => 'active',
        ]);

        // Step 1: request the reset link (neutral, not blocked).
        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => 'happy@example.com'])
            ->assertSessionHas('status', self::NEUTRAL);

        // Step 2: simulate the emailed link — seed the hashed token as sendResetLink would,
        // then post the raw token exactly like a user clicking the link.
        $rawToken = bin2hex(random_bytes(32));
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'happy@example.com'],
            ['token' => Hash::make($rawToken), 'created_at' => now()],
        );

        // The reset form must render for the user.
        $this->get('/reset-password/' . $rawToken . '?email=happy@example.com')->assertOk();

        // Step 3: submit the new password — legit reset succeeds.
        $reset = $this->from('/reset-password')->post('/reset-password', [
            'token' => $rawToken,
            'email' => 'happy@example.com',
            'password' => 'fresh-secret-99',
            'password_confirmation' => 'fresh-secret-99',
        ]);
        $reset->assertRedirect('/login');
        $reset->assertSessionHas('success', self::RESET_SUCCESS);
        $reset->assertSessionHasNoErrors();

        // Step 4: the legitimate user can now log in with the new password (happy path NOT blocked).
        $user->refresh();
        $this->assertTrue(Hash::check('fresh-secret-99', $user->password));

        $login = $this->post('/login', [
            'email' => 'happy@example.com',
            'password' => 'fresh-secret-99',
        ]);
        $login->assertRedirect();
        $this->assertNotEquals(401, $login->getStatusCode());
    }
}
