<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * أساس تكييف صيغة الخطاب العربيّ حسب جنس المستخدم: الدالّة g() + حقل users.gender + جمعه بالتسجيل.
 */
class GenderTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_g_defaults_to_male_for_guest(): void
    {
        $this->assertSame('مرحباً بك', g('مرحباً بك', 'مرحباً بكِ'));
    }

    public function test_g_returns_female_form_for_female_user(): void
    {
        $this->actingAs(User::factory()->create(['gender' => 'female']));
        $this->assertSame('أدخلي اسمكِ', g('أدخل اسمك', 'أدخلي اسمكِ'));
    }

    public function test_g_returns_male_form_for_male_user(): void
    {
        $this->actingAs(User::factory()->create(['gender' => 'male']));
        $this->assertSame('أدخل اسمك', g('أدخل اسمك', 'أدخلي اسمكِ'));
    }

    public function test_g_uses_explicit_user_override_without_auth(): void
    {
        // في البريد المُصفَّف لا يوجد auth() — نمرّر المُستقبِل صراحةً.
        $female = User::factory()->create(['gender' => 'female']);
        $this->assertSame('عزيزتنا', g('عزيزنا', 'عزيزتنا', $female));
    }

    public function test_null_gender_user_is_treated_as_male(): void
    {
        $this->actingAs(User::factory()->create(['gender' => null]));
        $this->assertSame('مذكّر', g('مذكّر', 'مؤنّث'));
    }

    // ---------- التسجيل يجمع الجنس ----------

    private function payload(array $o = []): array
    {
        return array_merge([
            'name' => 'مستخدمة',
            'gender' => 'female',
            'email' => 'newuser' . uniqid() . '@example.com',
            'role' => UserRole::Student->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $o);
    }

    public function test_registration_requires_gender(): void
    {
        Mail::fake();
        $p = $this->payload();
        unset($p['gender']);

        $this->post('/register', $p)->assertSessionHasErrors('gender');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_persists_gender(): void
    {
        Mail::fake();
        foreach (['student', 'teacher', 'parent'] as $name) {
            Role::findOrCreate($name, 'web');
        }

        $this->post('/register', $this->payload(['email' => 'she@example.com', 'gender' => 'female']))
            ->assertStatus(302);

        $this->assertDatabaseHas('users', ['email' => 'she@example.com', 'gender' => 'female']);
    }

    public function test_registration_rejects_invalid_gender(): void
    {
        Mail::fake();
        $this->post('/register', $this->payload(['gender' => 'other']))
            ->assertSessionHasErrors('gender');
    }
}
