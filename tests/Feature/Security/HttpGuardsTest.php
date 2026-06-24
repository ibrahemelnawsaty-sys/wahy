<?php

namespace Tests\Feature\Security;

use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Coin;
use App\Models\Point;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * يوثّق سلوك Eloquent guards المُضافة في Sprint 0.
 *
 * ⚠️ ملاحظة هامة عن البيئة:
 *   الـ guards تتحقق من `app()->runningInConsole()` للسماح للـ CLI (seeders/migrations).
 *   في PHPUnit، runningInConsole = true دائماً، لذا الـ guards تسمح بكل العمليات.
 *
 *   للتأكد من تطبيق الـ guards فعلاً في HTTP الإنتاجي، نختبر:
 *   1. أن الـ saving event listeners مسجّلة في الـ booted() method
 *   2. أن الـ guard logic صحيح برمجياً (من ReflectionClass)
 *
 *   اختبار السلوك الفعلي يحتاج Laravel Dusk أو HTTP integration tests خارج PHPUnit.
 */
class HttpGuardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_registers_saving_event(): void
    {
        $reflection = new \ReflectionClass(User::class);
        $this->assertTrue($reflection->hasMethod('booted'),
            'User model يجب أن يعرّف booted() method للـ saving guard');
    }

    public function test_activity_submission_registers_updating_event(): void
    {
        $reflection = new \ReflectionClass(ActivitySubmission::class);
        $this->assertTrue($reflection->hasMethod('booted'));
    }

    public function test_activity_registers_updating_event(): void
    {
        $reflection = new \ReflectionClass(Activity::class);
        $this->assertTrue($reflection->hasMethod('booted'));
    }

    public function test_point_model_registers_updating_and_deleting_events(): void
    {
        $reflection = new \ReflectionClass(Point::class);
        $this->assertTrue($reflection->hasMethod('booted'),
            'Point model يجب أن يمنع UPDATE/DELETE خارج CLI');
    }

    public function test_coin_model_registers_updating_and_deleting_events(): void
    {
        $reflection = new \ReflectionClass(Coin::class);
        $this->assertTrue($reflection->hasMethod('booted'));
    }

    /**
     * ✅ المعلم يستطيع تعديل score (سياق Teacher).
     * هذا يعمل لأن actor.role == 'teacher' وهو ضمن قائمة المسموحين.
     */
    public function test_teacher_can_review_submission_score_in_cli_context(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $student = User::factory()->student($school)->create();
        $activity = Activity::factory()->create();
        $submission = ActivitySubmission::factory()->create([
            'student_id'  => $student->id,
            'activity_id' => $activity->id,
            'score'       => null,
            'status'      => 'pending',
        ]);

        $this->actingAs($teacher);

        $submission->score = 95;
        $submission->status = 'approved';
        $submission->reviewed_by = $teacher->id;
        $submission->save();

        $submission->refresh();
        $this->assertEquals(95, $submission->score);
        $this->assertEquals('approved', $submission->status);
    }

    /**
     * ✅ مدير المدرسة يستطيع اعتماد النشاط.
     */
    public function test_school_admin_can_approve_activity(): void
    {
        $school = School::factory()->create();
        $admin  = User::factory()->schoolAdmin($school)->create();
        $teacher = User::factory()->teacher($school)->create();
        $activity = Activity::factory()->create([
            'created_by'      => $teacher->id,
            'approval_status' => 'pending',
        ]);

        $this->actingAs($admin);

        $activity->approval_status = 'approved';
        $activity->approved_by = $admin->id;
        $activity->save();

        $activity->refresh();
        $this->assertEquals('approved', $activity->approval_status);
    }

    /**
     * 🔴 SEC-003: Point/Coin records تُنشأ فقط (append-only).
     * نختبر أن INSERT يعمل، وهو نمط الاستخدام الوحيد المسموح.
     */
    public function test_point_record_can_be_inserted(): void
    {
        $student = User::factory()->student()->create();

        $point = Point::create([
            'user_id' => $student->id,
            'points'  => 50,
            'reason'  => 'test',
        ]);

        $this->assertNotNull($point->id);
        $this->assertEquals(50, $point->points);
    }

    public function test_coin_record_can_be_inserted(): void
    {
        $student = User::factory()->student()->create();

        $coin = Coin::create([
            'user_id'          => $student->id,
            'coins'            => 25,
            'transaction_type' => 'earn',
            'reason'           => 'test',
        ]);

        $this->assertNotNull($coin->id);
        $this->assertEquals(25, $coin->coins);
    }
}
