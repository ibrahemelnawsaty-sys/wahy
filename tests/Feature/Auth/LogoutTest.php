<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * يؤكّد أن إصلاح تسجيل الخروج (منع كاش صفحات المُصادَق) يشمل كل الأدوار،
 * وأن تسجيل الخروج ينجح ويمسح الجلسة لكل دور.
 */
class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** حالات الـ factory لكل دور */
    private array $roleStates = ['superAdmin', 'schoolAdmin', 'teacher', 'parent', 'student'];

    public function test_authenticated_pages_are_not_cached_for_all_roles(): void
    {
        foreach ($this->roleStates as $state) {
            $user = User::factory()->{$state}()->create();

            $cacheControl = (string) $this->actingAs($user)
                ->get('/dashboard')
                ->headers->get('Cache-Control');

            $this->assertStringContainsString(
                'no-store',
                $cacheControl,
                "استجابة الدور [{$state}] المُصادَقة يجب أن تكون no-store (يمنع رمز CSRF قديماً وخطأ 419 عند الخروج)"
            );
        }
    }

    public function test_logout_succeeds_and_clears_session_for_all_roles(): void
    {
        foreach ($this->roleStates as $state) {
            $user = User::factory()->{$state}()->create();

            $this->actingAs($user)
                ->post('/logout')
                ->assertRedirect(route('login'));

            $this->assertGuest();
            $this->assertFalse(auth()->check(), "الدور [{$state}] يجب أن يخرج بنجاح ويصبح ضيفاً");
        }
    }
}
