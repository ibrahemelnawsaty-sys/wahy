<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * S6-api-2fa-parity — تطابق تدفّق 2FA بين الويب وواجهة الـ API.
 *
 * الثغرة قبل الإصلاح: نقطة /api/v1/login كانت تُصدر Bearer Token فور صحّة كلمة المرور
 * متجاوزةً المصادقة الثنائية للمستخدمين المُفعِّلين لها (two_factor_enabled).
 *
 * هذه الاختبارات تُشغّل المسارات الحقيقية عبر عميل HTTP حتى تمر الـ middleware/الـ throttles.
 */
class S6api2faparityTest extends TestCase
{
    use RefreshDatabase;

    private const LOGIN = '/api/v1/login';

    private const VERIFY = '/api/v1/two-factor/verify';

    private const PASSWORD = 'correct-horse-battery';

    private function makeUser(bool $twoFactor, string $role = UserRole::Student->value): User
    {
        return User::factory()->create([
            'email' => 'parity-' . ($twoFactor ? '2fa' : 'plain') . '-' . uniqid() . '@example.com',
            'password' => Hash::make(self::PASSWORD),
            'status' => 'active',
            'role' => $role,
            'two_factor_enabled' => $twoFactor,
        ]);
    }

    // ----------------------------------------------------------------------
    // (a) ATTACKER / ABUSE — API login must NOT bypass 2FA
    // ----------------------------------------------------------------------

    /**
     * مستخدم مُفعِّل لـ 2FA (admin) يرسل بيانات اعتماد صحيحة:
     * يجب ألّا يحصل على أي توكن قابل للاستخدام، بل تحدّي 2fa_required فقط.
     */
    public function test_two_factor_user_with_correct_password_gets_no_token_only_challenge(): void
    {
        $admin = $this->makeUser(true, UserRole::SchoolAdmin->value);

        $response = $this->postJson(self::LOGIN, [
            'email' => $admin->email,
            'password' => self::PASSWORD,
        ]);

        // الرد الناجح هنا 200 لكن success=false مع تحدٍّ صريح — وليس توكناً
        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'code' => '2fa_required',
            ])
            ->assertJsonPath('data.user_id', $admin->id);

        // الحرج: لا يوجد أي توكن في الاستجابة بأي مسار محتمل
        $response->assertJsonMissingPath('data.token');
        $this->assertArrayNotHasKey('token', (array) ($response->json('data') ?? []));

        // وعلى مستوى قاعدة البيانات: لم يُنشأ أي Personal Access Token للمستخدم
        $this->assertSame(0, $admin->tokens()->count(), 'No Sanctum token may be issued before 2FA verification');

        // التحدي خزّن كود تحقق فعّالاً (شرط استكمال التدفّق لاحقاً)
        $admin->refresh();
        $this->assertNotNull($admin->two_factor_code, 'A 2FA challenge code must be set on the user');
    }

    /**
     * كود تحقق خاطئ بعد التحدّي لا يُصدر توكناً.
     */
    public function test_verify_with_wrong_code_yields_no_token(): void
    {
        $admin = $this->makeUser(true, UserRole::SchoolAdmin->value);

        $this->postJson(self::LOGIN, [
            'email' => $admin->email,
            'password' => self::PASSWORD,
        ])->assertStatus(200)->assertJsonPath('code', '2fa_required');

        $admin->refresh();
        $realCode = (string) $admin->two_factor_code;
        $wrongCode = $realCode === '000000' ? '111111' : '000000';

        $response = $this->postJson(self::VERIFY, [
            'user_id' => $admin->id,
            'code' => $wrongCode,
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
        $response->assertJsonMissingPath('data.token');
        $this->assertSame(0, $admin->tokens()->count(), 'Wrong 2FA code must not issue a token');
    }

    /**
     * فقط نقطة verify بالكود الصحيح هي التي تُصدر توكناً قابلاً للاستخدام.
     */
    public function test_only_verify_with_correct_code_issues_usable_token(): void
    {
        $admin = $this->makeUser(true, UserRole::SchoolAdmin->value);

        $this->postJson(self::LOGIN, [
            'email' => $admin->email,
            'password' => self::PASSWORD,
        ])->assertStatus(200)->assertJsonPath('code', '2fa_required');

        $admin->refresh();
        $code = (string) $admin->two_factor_code;

        $verify = $this->postJson(self::VERIFY, [
            'user_id' => $admin->id,
            'code' => $code,
        ]);

        $verify->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.id', $admin->id);

        $token = $verify->json('data.token');
        $this->assertNotEmpty($token, 'verify with correct code must return a token');

        // التوكن قابل للاستخدام فعلاً على مسار محمي
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/profile')
            ->assertStatus(200)
            ->assertJsonPath('data.id', $admin->id);

        // الكود استُهلك بعد النجاح
        $admin->refresh();
        $this->assertNull($admin->two_factor_code, '2FA code must be cleared after successful verification');
    }

    /**
     * إساءة: محاولة استخدام مسار verify دون تحدٍّ مسبق (مستخدم بلا 2FA) لا تُصدر توكناً.
     */
    public function test_verify_without_pending_challenge_is_rejected(): void
    {
        $plain = $this->makeUser(false);

        $response = $this->postJson(self::VERIFY, [
            'user_id' => $plain->id,
            'code' => '123456',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
        $response->assertJsonMissingPath('data.token');
        $this->assertSame(0, $plain->tokens()->count());
    }

    // ----------------------------------------------------------------------
    // (b) LEGITIMATE — the normal happy path is NEVER blocked
    // ----------------------------------------------------------------------

    /**
     * مستخدم بلا 2FA: طلب واحد صحيح => نجاح فوري بتوكن (خطوة واحدة).
     */
    public function test_non_two_factor_user_logs_in_one_step(): void
    {
        $user = $this->makeUser(false);

        $response = $this->postJson(self::LOGIN, [
            'email' => $user->email,
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.id', $user->id);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token, 'a non-2FA user must receive a token in one step');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/profile')
            ->assertStatus(200)
            ->assertJsonPath('data.id', $user->id);
    }

    /**
     * مستخدم مُفعِّل لـ 2FA يُكمل التدفّق الكامل بطلبات شرعية مفردة دون أي حظر.
     */
    public function test_legitimate_two_factor_user_completes_full_flow(): void
    {
        $admin = $this->makeUser(true, UserRole::SchoolAdmin->value);

        // الخطوة 1: تسجيل الدخول الشرعي => تحدّي (ليس خطأ مصادقة 401/403)
        $login = $this->postJson(self::LOGIN, [
            'email' => $admin->email,
            'password' => self::PASSWORD,
        ]);
        $login->assertStatus(200)->assertJsonPath('code', '2fa_required');
        $this->assertNotEquals(401, $login->getStatusCode());
        $this->assertNotEquals(403, $login->getStatusCode());

        // الخطوة 2: التحقق بالكود الصحيح => توكن
        $admin->refresh();
        $verify = $this->postJson(self::VERIFY, [
            'user_id' => $admin->id,
            'code' => (string) $admin->two_factor_code,
        ]);

        $verify->assertStatus(200)
            ->assertJson(['success' => true]);
        $this->assertNotEmpty($verify->json('data.token'));
    }

    /**
     * صحة سلبية: كلمة مرور خاطئة لمستخدم 2FA لا تطلق التحدّي ولا تُصدر توكناً.
     */
    public function test_wrong_password_for_two_factor_user_does_not_trigger_challenge(): void
    {
        $admin = $this->makeUser(true, UserRole::SchoolAdmin->value);

        $response = $this->postJson(self::LOGIN, [
            'email' => $admin->email,
            'password' => 'totally-wrong',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
        $response->assertJsonMissingPath('code');

        $admin->refresh();
        $this->assertNull($admin->two_factor_code, 'no challenge code may be set on wrong password');
        $this->assertSame(0, $admin->tokens()->count());
    }
}
