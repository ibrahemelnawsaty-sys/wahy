<?php

namespace Tests\Feature\Admin;

use App\Models\EducationLevel;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ربط المراحل الدراسية (EducationLevel) بالمدرسة من نموذج الإنشاء/التعديل.
 * المراحل تُخزَّن في pivot school_education_level (لا عمود في schools) وتُزامَن sync.
 */
class SchoolEducationLevelsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    /** @return EducationLevel[] */
    private function makeLevels(): array
    {
        return [
            EducationLevel::create(['name' => 'ابتدائي', 'sort_order' => 1, 'status' => true]),
            EducationLevel::create(['name' => 'متوسط', 'sort_order' => 2, 'status' => true]),
            EducationLevel::create(['name' => 'ثانوي', 'sort_order' => 3, 'status' => true]),
        ];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'مدرسة الاختبار',
            'address' => 'العنوان الرئيسي',
            'city' => 'الرياض',
            'contact_email' => 'test@school.sa',
            'contact_phone' => '0500000000',
            'status' => 'active',
        ], $overrides);
    }

    public function test_create_page_shows_education_levels(): void
    {
        $this->makeLevels();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.schools.create'))
            ->assertOk()
            ->assertSee('المراحل الدراسية')
            ->assertSee('ابتدائي');
    }

    public function test_store_syncs_selected_education_levels(): void
    {
        [$primary, $middle, $high] = $this->makeLevels();

        $this->actingAs($this->superAdmin())
            ->post(route('admin.schools.store'), $this->payload([
                'education_levels' => [$primary->id, $high->id],
            ]))
            ->assertRedirect(route('admin.schools.index'));

        $school = School::where('contact_email', 'test@school.sa')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            [$primary->id, $high->id],
            $school->educationLevels()->pluck('education_levels.id')->all()
        );
    }

    public function test_update_resyncs_education_levels(): void
    {
        [$primary, $middle, $high] = $this->makeLevels();
        $school = School::factory()->create();
        $school->educationLevels()->sync([$primary->id]);

        $this->actingAs($this->superAdmin())
            ->put(route('admin.schools.update', $school), [
                'name' => $school->name,
                'address' => $school->address,
                'city' => $school->city,
                'contact_email' => $school->contact_email,
                'contact_phone' => $school->contact_phone,
                'status' => 'active',
                'education_levels' => [$middle->id, $high->id],
            ])
            ->assertRedirect(route('admin.schools.index'));

        $this->assertEqualsCanonicalizing(
            [$middle->id, $high->id],
            $school->fresh()->educationLevels()->pluck('education_levels.id')->all()
        );
    }

    public function test_update_without_levels_clears_all_links(): void
    {
        [$primary] = $this->makeLevels();
        $school = School::factory()->create();
        $school->educationLevels()->sync([$primary->id]);

        $this->actingAs($this->superAdmin())
            ->put(route('admin.schools.update', $school), [
                'name' => $school->name,
                'address' => $school->address,
                'city' => $school->city,
                'contact_email' => $school->contact_email,
                'contact_phone' => $school->contact_phone,
                'status' => 'active',
                // لا education_levels ⇒ إلغاء تحديد الكل ⇒ فكّ كل الروابط
            ])
            ->assertRedirect(route('admin.schools.index'));

        $this->assertCount(0, $school->fresh()->educationLevels);
    }

    public function test_store_rejects_nonexistent_education_level(): void
    {
        $this->makeLevels();

        $this->actingAs($this->superAdmin())
            ->post(route('admin.schools.store'), $this->payload([
                'education_levels' => [999999],
            ]))
            ->assertSessionHasErrors('education_levels.0');
    }
}
