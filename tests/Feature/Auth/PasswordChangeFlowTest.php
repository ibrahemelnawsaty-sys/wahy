<?php

namespace Tests\Feature\Auth;

use App\Mail\RegistrationApprovedMail;
use App\Models\RegistrationRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * تدفّق كلمة المرور للحسابات الجديدة:
 *  - قبول طلب التسجيل يجب أن يُبقي كلمة المرور التي اختارها المستخدم (لا مؤقتة، لا إجبار).
 *  - تغيير كلمة المرور المفروضة يجب أن يعمل ويُصفّي العلَم (كان يسقط بـ403 في الإنتاج
 *    بسبب حارس User::booted على password_change_required — يُتجاوَز الآن بـsaveQuietly).
 */
class PasswordChangeFlowTest extends TestCase
{
    use RefreshDatabase;

    private function request(School $school, array $overrides = []): RegistrationRequest
    {
        return RegistrationRequest::create(array_merge([
            'school_id' => $school->id,
            'name' => 'مستخدم جديد',
            'email' => 'newuser@test.sa',
            'phone' => '0500000000',
            'password' => bcrypt('ChosenPass123'),
            'role' => 'student',
            'status' => 'pending',
        ], $overrides));
    }

    public function test_approval_preserves_chosen_password_and_does_not_force_change(): void
    {
        Mail::fake();
        $school = School::factory()->create();
        $admin = User::factory()->schoolAdmin($school)->create();
        $req = $this->request($school);

        $this->actingAs($admin)
            ->post(route('school-admin.requests.approve', $req->id))
            ->assertRedirect();

        $user = User::where('email', 'newuser@test.sa')->firstOrFail();
        $this->assertFalse((bool) $user->password_change_required, 'لا إجبار على تغيير كلمة اختارها المستخدم');
        $this->assertTrue(Hash::check('ChosenPass123', $user->password), 'يجب الدخول بكلمة المرور المختارة');

        // البريد لا يرسل كلمة مرور مؤقتة (المستخدم يعرف كلمته)
        Mail::assertSent(RegistrationApprovedMail::class, fn ($m) => $m->temporaryPassword === null);
    }

    public function test_approval_without_chosen_password_falls_back_to_temp_and_forces_change(): void
    {
        Mail::fake();
        $school = School::factory()->create();
        $admin = User::factory()->schoolAdmin($school)->create();
        // طلب بلا كلمة مرور (حالة نادرة/بيانات غير مكتملة) → احتياط: مؤقتة + إجبار
        $req = $this->request($school, ['email' => 'nopw@test.sa', 'password' => '']);

        $this->actingAs($admin)
            ->post(route('school-admin.requests.approve', $req->id))
            ->assertRedirect();

        $user = User::where('email', 'nopw@test.sa')->firstOrFail();
        $this->assertTrue((bool) $user->password_change_required, 'بلا كلمة مختارة ⇒ إجبار التغيير');
        Mail::assertSent(RegistrationApprovedMail::class, fn ($m) => ! empty($m->temporaryPassword));
    }

    public function test_forced_user_can_change_password_and_flag_clears(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'role' => 'student',
            'school_id' => $school->id,
            'password' => Hash::make('TempPass1'),
            'password_change_required' => true,
        ]);

        $this->actingAs($user)
            ->post(route('password.change.update'), [
                'current_password' => 'TempPass1',
                'password' => 'BrandNew123',
                'password_confirmation' => 'BrandNew123',
            ])
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertFalse((bool) $user->password_change_required, 'يُصفّى العلَم بعد التغيير');
        $this->assertTrue(Hash::check('BrandNew123', $user->password), 'كلمة المرور الجديدة فعّالة');
    }

    public function test_password_change_rejects_wrong_current_password(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'role' => 'student',
            'school_id' => $school->id,
            'password' => Hash::make('TempPass1'),
            'password_change_required' => true,
        ]);

        $this->actingAs($user)
            ->post(route('password.change.update'), [
                'current_password' => 'WrongPass',
                'password' => 'BrandNew123',
                'password_confirmation' => 'BrandNew123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue((bool) $user->fresh()->password_change_required, 'يبقى العلَم عند فشل التحقّق');
    }
}
