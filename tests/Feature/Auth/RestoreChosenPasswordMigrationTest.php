<?php

namespace Tests\Feature\Auth;

use App\Models\RegistrationRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * هجرة إصلاح البيانات (issue #18): استرجاع كلمة المرور المختارة وإلغاء إجبار التغيير
 * لمستخدمي رابط/باركود المدرسة الذين أُنشئوا قبل إصلاح approveRequest — مع تضييق أمنيّ
 * يستبعد من أُعيد تعيين كلمته لاحقاً (الدعم/الأدمن) كي لا نُبطِل كلمة شرعيّة جديدة.
 */
class RestoreChosenPasswordMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function runMigration(): void
    {
        $migration = require database_path(
            'migrations/2026_07_22_120000_restore_self_chosen_passwords_for_forced_users.php'
        );
        $migration->up();
    }

    public function test_restores_chosen_password_and_clears_forced_flag(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $school->id,
            'email' => 'teacher4@atheel-makkah.com',
            'password' => Hash::make('Temp-OLD1234'),
            'password_change_required' => true,
        ]);
        // اعتُمِد للتوّ (لم يُعدَّل المستخدم منذ إنشائه)
        RegistrationRequest::create([
            'school_id' => $school->id,
            'name' => 'معلّم',
            'email' => 'teacher4@atheel-makkah.com',
            'password' => bcrypt('MyChosenPass9'),
            'role' => 'teacher',
            'status' => 'approved',
            'user_id' => $user->id,
            'approved_at' => now(),
        ]);

        $this->runMigration();

        $user->refresh();
        $this->assertFalse((bool) $user->password_change_required, 'أُلغي إجبار التغيير');
        $this->assertTrue(Hash::check('MyChosenPass9', $user->password), 'يدخل بكلمته المختارة');
        $this->assertFalse(Hash::check('Temp-OLD1234', $user->password), 'الكلمة المؤقتة أُبطِلت');
    }

    public function test_does_not_touch_user_reset_by_support_after_approval(): void
    {
        $school = School::factory()->create();
        // أُعتمِد قبل 30 يوماً، ثمّ أعاد الدعم تعيين كلمته حديثاً (updated_at حديث ≠ approved_at القديم)
        $user = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $school->id,
            'email' => 'reset@test.sa',
            'password' => Hash::make('SupportTemp-P2'),
            'password_change_required' => true,
        ]);
        RegistrationRequest::create([
            'school_id' => $school->id,
            'name' => 'معلّم',
            'email' => 'reset@test.sa',
            'password' => bcrypt('OldRegistrationP1'),
            'role' => 'teacher',
            'status' => 'approved',
            'user_id' => $user->id,
            'approved_at' => now()->subDays(30), // الاعتماد قديم، والتعديل (الدعم) حديث → يُستبعَد
        ]);

        $this->runMigration();

        $user->refresh();
        $this->assertTrue(Hash::check('SupportTemp-P2', $user->password), 'كلمة الدعم الشرعيّة محفوظة');
        $this->assertFalse(Hash::check('OldRegistrationP1', $user->password), 'لم تُستعَد كلمة التسجيل القديمة');
        $this->assertTrue((bool) $user->password_change_required, 'إجبار الدعم باقٍ');
    }

    public function test_does_not_touch_user_who_already_changed_password(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $school->id,
            'email' => 'changed@test.sa',
            'password' => Hash::make('BrandNewChosen1'),
            'password_change_required' => false,
        ]);
        RegistrationRequest::create([
            'school_id' => $school->id,
            'name' => 'معلّم',
            'email' => 'changed@test.sa',
            'password' => bcrypt('OldRegistrationPass'),
            'role' => 'teacher',
            'status' => 'approved',
            'user_id' => $user->id,
            'approved_at' => now(),
        ]);

        $this->runMigration();

        $user->refresh();
        $this->assertTrue(Hash::check('BrandNewChosen1', $user->password));
        $this->assertFalse(Hash::check('OldRegistrationPass', $user->password));
    }

    public function test_is_idempotent(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $school->id,
            'email' => 't@test.sa',
            'password' => Hash::make('Temp-X'),
            'password_change_required' => true,
        ]);
        RegistrationRequest::create([
            'school_id' => $school->id, 'name' => 'م', 'email' => 't@test.sa',
            'password' => bcrypt('Chosen123'), 'role' => 'teacher',
            'status' => 'approved', 'user_id' => $user->id, 'approved_at' => now(),
        ]);

        $this->runMigration();
        $this->runMigration(); // إعادة التشغيل بلا أثر

        $user->refresh();
        $this->assertFalse((bool) $user->password_change_required);
        $this->assertTrue(Hash::check('Chosen123', $user->password));
    }

    public function test_matches_by_email_when_user_id_is_null(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $school->id,
            'email' => 'legacy@test.sa',
            'password' => Hash::make('Temp-Legacy'),
            'password_change_required' => true,
        ]);
        // طلب قديم بلا user_id → المطابقة بالبريد
        RegistrationRequest::create([
            'school_id' => $school->id, 'name' => 'م', 'email' => 'legacy@test.sa',
            'password' => bcrypt('LegacyChosen1'), 'role' => 'teacher',
            'status' => 'approved', 'user_id' => null, 'approved_at' => now(),
        ]);

        $this->runMigration();

        $user->refresh();
        $this->assertFalse((bool) $user->password_change_required);
        $this->assertTrue(Hash::check('LegacyChosen1', $user->password));
    }
}
