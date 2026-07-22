<?php

namespace Tests\Feature\Admin;

use App\Models\Activity;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * عدّاد أنشطة المعلّمين بانتظار الاعتماد في ترويسة كلٍّ من السوبر أدمن ومدير المدرسة
 * (ليعرف المهامّ بمجرّد الدخول). كل دور يرى طابوره الصحيح:
 *  - مدير المدرسة: أنشطة مدرسته بـschool_approval_status=pending.
 *  - السوبر أدمن: أنشطة المعلّمين المعتمدة مدرسياً وبانتظار الاعتماد النهائيّ.
 */
class PendingActivitiesCounterTest extends TestCase
{
    use RefreshDatabase;

    private function seedActivities(School $school, User $teacher): void
    {
        // في طابور السوبر أدمن فقط (اعتمدها المدير، بانتظار الاعتماد النهائيّ)
        Activity::factory()->create([
            'created_by' => $teacher->id,
            'school_approval_status' => 'approved',
            'approval_status' => 'pending',
        ]);
        // في طابور مدير المدرسة فقط (بانتظار اعتماد المدرسة)
        Activity::factory()->create([
            'created_by' => $teacher->id,
            'school_approval_status' => 'pending',
            'approval_status' => 'pending',
        ]);
        // في لا طابور (معتمَد بالكامل)
        Activity::factory()->create([
            'created_by' => $teacher->id,
            'school_approval_status' => 'approved',
            'approval_status' => 'approved',
        ]);
    }

    public function test_super_admin_header_shows_final_approval_queue_count(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $this->seedActivities($school, $teacher);

        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.activity-approval.index'))
            ->assertOk()
            ->assertSee('نشاط بانتظار الاعتماد')
            // الطابور النهائيّ = نشاط واحد فقط (المعتمد مدرسياً وبانتظار النهائيّ)
            ->assertSee('header_pending_activities">1', false);
    }

    public function test_school_admin_header_shows_school_approval_queue_count(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $this->seedActivities($school, $teacher);

        $schoolAdmin = User::factory()->create(['role' => 'school_admin', 'school_id' => $school->id]);

        $this->actingAs($schoolAdmin)
            ->get(route('school-admin.activity-approvals'))
            ->assertOk()
            // طابور المدرسة = نشاط واحد فقط (بانتظار اعتماد المدرسة)
            ->assertSee('school_activity_approvals_pending" data-live-badge>1', false);
    }

    public function test_school_admin_count_is_scoped_to_own_school(): void
    {
        $mySchool = School::factory()->create();
        $otherSchool = School::factory()->create();
        $myTeacher = User::factory()->create(['role' => 'teacher', 'school_id' => $mySchool->id]);
        $otherTeacher = User::factory()->create(['role' => 'teacher', 'school_id' => $otherSchool->id]);

        // نشاط مدرسة أخرى بانتظار الاعتماد — يجب ألا يُحتسَب لي
        Activity::factory()->create(['created_by' => $otherTeacher->id, 'school_approval_status' => 'pending']);
        // نشاطي بانتظار الاعتماد
        Activity::factory()->create(['created_by' => $myTeacher->id, 'school_approval_status' => 'pending']);

        $schoolAdmin = User::factory()->create(['role' => 'school_admin', 'school_id' => $mySchool->id]);

        $this->actingAs($schoolAdmin)
            ->get(route('school-admin.activity-approvals'))
            ->assertOk()
            ->assertSee('school_activity_approvals_pending" data-live-badge>1', false);
    }
}
