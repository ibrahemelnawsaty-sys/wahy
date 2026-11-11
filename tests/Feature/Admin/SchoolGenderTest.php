<?php

namespace Tests\Feature\Admin;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * نوع المدرسة (بنين/بنات) يحدّد جنس كلّ طلابها تلقائياً — مصدر واحد في User::saving.
 */
class SchoolGenderTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $o = []): array
    {
        return array_merge([
            'name' => 'مدرسة الاختبار',
            'address' => 'العنوان',
            'city' => 'مكة',
            'contact_email' => 'g@school.sa',
            'contact_phone' => '0500000000',
            'status' => 'active',
        ], $o);
    }

    public function test_store_requires_school_gender(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->post(route('admin.schools.store'), $this->payload())
            ->assertSessionHasErrors('gender');

        $this->assertDatabaseMissing('schools', ['contact_email' => 'g@school.sa']);
    }

    public function test_store_saves_school_gender(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->post(route('admin.schools.store'), $this->payload(['gender' => 'female']))
            ->assertRedirect(route('admin.schools.index'));

        $this->assertDatabaseHas('schools', ['contact_email' => 'g@school.sa', 'gender' => 'female']);
    }

    public function test_student_in_girls_school_is_forced_female_even_if_male_passed(): void
    {
        $school = School::factory()->create(['gender' => 'female']);
        $student = User::factory()->student($school)->create(['gender' => 'male']);

        $this->assertSame('female', $student->fresh()->gender);
    }

    public function test_student_without_explicit_gender_inherits_school_gender(): void
    {
        $school = School::factory()->create(['gender' => 'male']);
        // نفس مسار الاستيراد/قبول طلب التسجيل: لا جنس مُمرَّر
        $student = User::factory()->student($school)->create(['gender' => null]);

        $this->assertSame('male', $student->fresh()->gender);
    }

    public function test_school_without_gender_leaves_student_gender_untouched(): void
    {
        $school = School::factory()->create(['gender' => null]);
        $student = User::factory()->student($school)->create(['gender' => 'female']);

        $this->assertSame('female', $student->fresh()->gender);
    }

    public function test_teacher_and_parent_are_not_forced_by_school_gender(): void
    {
        $school = School::factory()->create(['gender' => 'female']);
        $teacher = User::factory()->teacher($school)->create(['gender' => 'male']);
        $parent = User::factory()->parent($school)->create(['gender' => 'male']);

        $this->assertSame('male', $teacher->fresh()->gender);
        $this->assertSame('male', $parent->fresh()->gender);
    }

    public function test_student_cannot_change_gender_away_from_school(): void
    {
        $school = School::factory()->create(['gender' => 'female']);
        $student = User::factory()->student($school)->create();

        $student->update(['gender' => 'male']);

        $this->assertSame('female', $student->fresh()->gender);
    }

    public function test_moving_student_to_another_school_adopts_its_gender(): void
    {
        $boys = School::factory()->create(['gender' => 'male']);
        $girls = School::factory()->create(['gender' => 'female']);
        $student = User::factory()->student($boys)->create();
        $this->assertSame('male', $student->fresh()->gender);

        $student->update(['school_id' => $girls->id]);

        $this->assertSame('female', $student->fresh()->gender);
    }

    public function test_updating_school_gender_resyncs_its_students_only(): void
    {
        $school = School::factory()->create(['gender' => null]);
        $other = School::factory()->create(['gender' => null]);
        $mine = User::factory()->student($school)->create(['gender' => null]);
        $theirs = User::factory()->student($other)->create(['gender' => 'male']);
        $teacher = User::factory()->teacher($school)->create(['gender' => 'male']);

        $this->actingAs(User::factory()->superAdmin()->create())
            ->put(route('admin.schools.update', $school), [
                'name' => $school->name,
                'address' => $school->address,
                'city' => $school->city,
                'contact_email' => $school->contact_email,
                'contact_phone' => $school->contact_phone,
                'status' => 'active',
                'gender' => 'female',
            ])
            ->assertRedirect(route('admin.schools.index'));

        $this->assertSame('female', $school->fresh()->gender);
        $this->assertSame('female', $mine->fresh()->gender);
        $this->assertSame('male', $theirs->fresh()->gender, 'طلاب مدرسة أخرى لا يُمَسّون');
        $this->assertSame('male', $teacher->fresh()->gender, 'المعلّم لا يُمَسّ');
    }

    public function test_update_requires_school_gender(): void
    {
        $school = School::factory()->create();

        $this->actingAs(User::factory()->superAdmin()->create())
            ->put(route('admin.schools.update', $school), [
                'name' => $school->name,
                'address' => $school->address,
                'city' => $school->city,
                'contact_email' => $school->contact_email,
                'contact_phone' => $school->contact_phone,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('gender');
    }
}
