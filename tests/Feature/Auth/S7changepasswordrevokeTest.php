<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * S7 — changePassword revokes sibling tokens, keeps the current one.
 *
 * Wired target: App\Http\Controllers\Api\AuthApiController::changePassword
 * Route: POST /api/v1/change-password (middleware auth:sanctum).
 * After a successful password change it runs:
 *   $user->tokens()->where('id', '!=', currentAccessToken()->id)->delete();
 * so every OTHER token of the user is revoked while the caller's token survives.
 */
class S7changepasswordrevokeTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $password = 'old-password-123'): User
    {
        return User::factory()->create([
            'email' => 's7-revoke@example.com',
            'password' => Hash::make($password),
            'status' => 'active',
        ]);
    }

    /**
     * (a) ATTACKER/ABUSE BLOCKED — a stolen/sibling token is killed after a
     * password change, and a wrong current-password cannot rotate the password.
     *
     * Scenario: user holds two live tokens (A = current device, B = an attacker's
     * exfiltrated session). The user changes the password from device A; token B
     * must immediately stop working, while A (the in-flight caller) keeps working.
     */
    public function test_change_password_revokes_sibling_tokens_and_rejects_wrong_current_password(): void
    {
        $user = $this->makeUser('old-password-123');

        $tokenA = $user->createToken('device-a')->plainTextToken;
        $tokenB = $user->createToken('attacker-b')->plainTextToken;
        $aId = (int) explode('|', $tokenA, 2)[0];
        $bId = (int) explode('|', $tokenB, 2)[0];

        // Sanity: both tokens are live before the change.
        $this->assertSame(2, $user->tokens()->count());

        // Wrong current password must be rejected (no rotation, no revocation).
        $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->postJson('/api/v1/change-password', [
                'current_password' => 'totally-wrong',
                'new_password' => 'brand-new-pass-9',
                'new_password_confirmation' => 'brand-new-pass-9',
            ])
            ->assertStatus(400)
            ->assertJson(['success' => false]);

        // Both tokens still alive after the failed attempt; password unchanged.
        $this->assertSame(2, $user->fresh()->tokens()->count());
        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));

        // Now perform the real change from device A (current token).
        $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->postJson('/api/v1/change-password', [
                'current_password' => 'old-password-123',
                'new_password' => 'brand-new-pass-9',
                'new_password_confirmation' => 'brand-new-pass-9',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        // The new password actually took effect (hash rotated).
        $this->assertTrue(Hash::check('brand-new-pass-9', $user->fresh()->password));

        // Exactly one token remains, and it is the CURRENT caller's token (A);
        // the sibling/attacker token (B) was the one revoked.
        $remaining = $user->fresh()->tokens()->pluck('id')->all();
        $this->assertSame([$aId], $remaining);
        $this->assertNotContains($bId, $remaining);

        // Token A (the caller) survives and still authenticates.
        // forgetGuards() drops the in-process Sanctum guard memoization so the
        // Bearer token is re-resolved against current DB state (mirrors a real,
        // separate HTTP request from the client).
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson('/api/v1/profile')
            ->assertOk();

        // Token B (sibling/attacker) is revoked -> rejected by auth:sanctum.
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenB)
            ->getJson('/api/v1/profile')
            ->assertStatus(401);
    }

    /**
     * (b) LEGITIMATE happy path — a single user with the correct current password
     * rotates it in one normal request and is NOT blocked. Their own (current)
     * token keeps working immediately after.
     */
    public function test_legitimate_user_changes_password_in_normal_flow_is_not_blocked(): void
    {
        $user = $this->makeUser('correct-current-1');

        $token = $user->createToken('only-device')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/change-password', [
                'current_password' => 'correct-current-1',
                'new_password' => 'fresh-secret-22',
                'new_password_confirmation' => 'fresh-secret-22',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'تم تغيير كلمة المرور بنجاح',
            ]);

        // Single legit token is preserved (user is not logged out of their own session).
        $this->assertSame(1, $user->fresh()->tokens()->count());
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/profile')
            ->assertOk();

        // New password is usable end-to-end via the API login flow (no 2FA on this user).
        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'fresh-secret-22',
        ])->assertOk()
            ->assertJson(['success' => true]);
    }
}
