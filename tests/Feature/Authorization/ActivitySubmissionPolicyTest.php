<?php

namespace Tests\Feature\Authorization;

use App\Models\ActivitySubmission;
use App\Models\School;
use App\Models\User;
use App\Policies\ActivitySubmissionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivitySubmissionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ActivitySubmissionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ActivitySubmissionPolicy;
    }

    public function test_student_can_view_own_submission(): void
    {
        $student = User::factory()->student()->create();
        $submission = ActivitySubmission::factory()->create(['student_id' => $student->id]);

        $this->assertTrue($this->policy->view($student, $submission));
    }

    public function test_student_cannot_view_other_students_submission(): void
    {
        $student = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $submission = ActivitySubmission::factory()->create(['student_id' => $otherStudent->id]);

        $this->assertFalse($this->policy->view($student, $submission));
    }

    public function test_teacher_in_same_school_can_review_submission(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $student = User::factory()->student($school)->create();
        $submission = ActivitySubmission::factory()->create(['student_id' => $student->id]);

        $this->assertTrue($this->policy->view($teacher, $submission));
        $this->assertTrue($this->policy->review($teacher, $submission));
    }

    public function test_teacher_in_other_school_cannot_review(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacher = User::factory()->teacher($schoolA)->create();
        $student = User::factory()->student($schoolB)->create();
        $submission = ActivitySubmission::factory()->create(['student_id' => $student->id]);

        $this->assertFalse($this->policy->view($teacher, $submission));
        $this->assertFalse($this->policy->review($teacher, $submission));
    }

    public function test_only_student_role_can_create_submission(): void
    {
        $this->assertTrue($this->policy->create(User::factory()->student()->create()));
        $this->assertFalse($this->policy->create(User::factory()->teacher()->create()));
        $this->assertFalse($this->policy->create(User::factory()->parent()->create()));
    }

    public function test_school_admin_can_delete_in_own_school(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->schoolAdmin($school)->create();
        $student = User::factory()->student($school)->create();
        $submission = ActivitySubmission::factory()->create(['student_id' => $student->id]);

        $this->assertTrue($this->policy->delete($admin, $submission));
    }
}
