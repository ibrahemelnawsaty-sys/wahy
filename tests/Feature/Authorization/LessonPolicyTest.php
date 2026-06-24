<?php

namespace Tests\Feature\Authorization;

use App\Models\Lesson;
use App\Models\User;
use App\Policies\LessonPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonPolicyTest extends TestCase
{
    use RefreshDatabase;

    private LessonPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new LessonPolicy();
    }

    public function test_anyone_can_view_lessons(): void
    {
        $lesson = Lesson::factory()->create();
        foreach (['student', 'teacher', 'parent', 'school_admin', 'super_admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertTrue($this->policy->view($user, $lesson), "{$role} should view");
        }
    }

    public function test_teacher_school_admin_super_admin_can_create(): void
    {
        $this->assertTrue($this->policy->create(User::factory()->teacher()->create()));
        $this->assertTrue($this->policy->create(User::factory()->schoolAdmin()->create()));
        $this->assertTrue($this->policy->create(User::factory()->superAdmin()->create()));
        $this->assertFalse($this->policy->create(User::factory()->student()->create()));
        $this->assertFalse($this->policy->create(User::factory()->parent()->create()));
    }

    public function test_only_super_admin_can_update_lesson(): void
    {
        $lesson = Lesson::factory()->create();

        $this->assertFalse($this->policy->update(User::factory()->teacher()->create(), $lesson));
        $this->assertFalse($this->policy->update(User::factory()->schoolAdmin()->create(), $lesson));
        $this->assertTrue($this->policy->update(User::factory()->superAdmin()->create(), $lesson));
    }

    public function test_only_super_admin_can_delete_lesson(): void
    {
        $lesson = Lesson::factory()->create();

        $this->assertTrue($this->policy->delete(User::factory()->superAdmin()->create(), $lesson));
        $this->assertFalse($this->policy->delete(User::factory()->schoolAdmin()->create(), $lesson));
    }
}
