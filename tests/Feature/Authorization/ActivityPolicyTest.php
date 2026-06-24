<?php

namespace Tests\Feature\Authorization;

use App\Models\Activity;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use App\Policies\ActivityPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ActivityPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ActivityPolicy;
    }

    public function test_super_admin_can_do_everything(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $activity = Activity::factory()->create();

        $this->assertTrue($this->policy->view($superAdmin, $activity));
        $this->assertTrue($this->policy->update($superAdmin, $activity));
        $this->assertTrue($this->policy->delete($superAdmin, $activity));
        $this->assertTrue($this->policy->approve($superAdmin, $activity));
        $this->assertTrue($this->policy->feature($superAdmin));
    }

    public function test_creator_can_update_and_delete_own_activity(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $activity = Activity::factory()->create(['created_by' => $teacher->id]);

        $this->assertTrue($this->policy->update($teacher, $activity));
        $this->assertTrue($this->policy->delete($teacher, $activity));
    }

    public function test_other_teacher_cannot_update_others_activity(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $otherTeacher = User::factory()->teacher($school)->create();
        $activity = Activity::factory()->create(['created_by' => $teacher->id]);

        $this->assertFalse($this->policy->update($otherTeacher, $activity));
        $this->assertFalse($this->policy->delete($otherTeacher, $activity));
    }

    public function test_school_admin_can_manage_activities_in_own_school(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->schoolAdmin($school)->create();
        $teacher = User::factory()->teacher($school)->create();
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $activity = Activity::factory()->create([
            'created_by' => $teacher->id,
            'classroom_id' => $classroom->id,
        ]);

        $this->assertTrue($this->policy->update($admin, $activity));
        $this->assertTrue($this->policy->approve($admin, $activity));
    }

    public function test_school_admin_cannot_manage_activities_in_other_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $admin = User::factory()->schoolAdmin($schoolA)->create();
        $teacher = User::factory()->teacher($schoolB)->create();
        $classroom = Classroom::factory()->create(['school_id' => $schoolB->id, 'teacher_id' => $teacher->id]);
        $activity = Activity::factory()->create([
            'created_by' => $teacher->id,
            'classroom_id' => $classroom->id,
        ]);

        $this->assertFalse($this->policy->update($admin, $activity));
        $this->assertFalse($this->policy->approve($admin, $activity));
    }

    public function test_only_super_admin_can_feature_activity(): void
    {
        $teacher = User::factory()->teacher()->create();
        $admin = User::factory()->schoolAdmin()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->assertFalse($this->policy->feature($teacher));
        $this->assertFalse($this->policy->feature($admin));
        $this->assertTrue($this->policy->feature($superAdmin));
    }

    public function test_student_cannot_create_or_update_activity(): void
    {
        $student = User::factory()->student()->create();
        $activity = Activity::factory()->create();

        $this->assertFalse($this->policy->create($student));
        $this->assertFalse($this->policy->update($student, $activity));
        $this->assertFalse($this->policy->delete($student, $activity));
    }
}
