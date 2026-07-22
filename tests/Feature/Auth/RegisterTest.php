<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // المتحكّم يُسند دور Spatie عند النجاح، وبذر الأدوار لا يعمل في الاختبارات
        // (RefreshDatabase لا يبذر) — فننشئ الأدوار التي يحتاجها مسار التسجيل كي يكتمل
        // المسار السعيد (User::create + assignRole معاً). بلا هذا يُطلق assignRole
        // RoleDoesNotExist داخل الـtransaction فتتراجع ولا يُنشأ مستخدم.
        foreach (['student', 'teacher', 'parent', 'school_admin'] as $name) {
            Role::findOrCreate($name, 'web');
        }

        // التسجيل يُرسل بريد تأكيد — نُبقيه خاملاً.
        Mail::fake();
    }

    public function test_register_page_is_accessible(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_user_can_register_as_student(): void
    {
        $response = $this->post('/register', [
            'name' => 'طالب جديد',
            'email' => 'newstudent@example.com',
            'phone' => '0501234567',
            'role' => UserRole::Student->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'newstudent@example.com',
            'role' => UserRole::Student->value,
            // الحساب يبدأ inactive (تنتظر اعتماد إدارة)
            'status' => 'inactive',
        ]);
    }

    /**
     * 🔴 SEC-001: التأكد أن الزائر لا يستطيع تسجيل نفسه كـ super_admin أو school_admin.
     */
    public function test_register_request_blocks_super_admin_role(): void
    {
        $validator = Validator::make([
            'name' => 'مهاجم',
            'email' => 'attacker@example.com',
            'role' => UserRole::SuperAdmin->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], (new RegisterRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('role', $validator->errors()->toArray());
    }

    public function test_register_request_blocks_school_admin_role(): void
    {
        $validator = Validator::make([
            'name' => 'مهاجم',
            'email' => 'attacker@example.com',
            'role' => UserRole::SchoolAdmin->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], (new RegisterRequest)->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_register_request_allows_student_teacher_parent(): void
    {
        foreach ([UserRole::Student, UserRole::Teacher, UserRole::Parent] as $role) {
            $validator = Validator::make([
                'name' => 'مستخدم',
                'email' => 'user' . $role->value . '@example.com',
                'role' => $role->value,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ], (new RegisterRequest)->rules());

            $this->assertFalse($validator->fails(), "الدور {$role->value} يجب أن يكون مقبولاً");
        }
    }

    public function test_register_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'مستخدم',
            'email' => 'taken@example.com',
            'role' => UserRole::Student->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors('email');
    }

    public function test_register_requires_password_confirmation(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'مستخدم',
            'email' => 'unique@example.com',
            'role' => UserRole::Student->value,
            'password' => 'password123',
            'password_confirmation' => 'mismatch123',
        ]);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors('password');
    }
}
