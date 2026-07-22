<?php

namespace Tests\Feature\Authorization;

use App\Models\Activity;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * رؤية معاينة النشاط للمعلّم (teacher.activities.preview).
 * وُسِّعت المعاينة لتطابق قاعدة رؤية بنك الأنشطة تماماً: يعاين المعلّم نشاطه
 * (أياً كان)، أو نشاط بنك مشترك معتمد، أو نشاط عامّ — ويُمنع (404) عمّا لا يراه
 * أصلاً في البنك (أجنبيّ غير بنكيّ، أو بنكيّ غير معتمد).
 */
class PreviewActivityVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function preview(User $teacher, Activity $activity)
    {
        return $this->actingAs($teacher)->get(route('teacher.activities.preview', $activity->id));
    }

    public function test_teacher_can_preview_own_activity(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $activity = Activity::factory()->create(['created_by' => $teacher->id]);

        $this->preview($teacher, $activity)->assertOk();
    }

    public function test_teacher_can_preview_shared_approved_bank_activity(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $other = User::factory()->teacher($school)->create();
        // «مشترك» في النموذج الجديد = منشور فعلاً لبنك كل المدارس (all_schools_mode='bank')،
        // كما يُنتجه اعتماد الأدمن scope=all mode=bank. مجرّد approved بلا نشر لم يعد «مشتركًا».
        $shared = Activity::factory()->create([
            'created_by' => $other->id,
            'is_activity_bank' => true,
            'approval_status' => 'approved',
            'all_schools_mode' => 'bank',
        ]);

        $this->preview($teacher, $shared)->assertOk();
    }

    public function test_teacher_can_preview_global_bank_activity(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $global = Activity::factory()->create([
            'created_by' => null,
            'is_activity_bank' => true,
        ]);

        $this->preview($teacher, $global)->assertOk();
    }

    public function test_teacher_cannot_preview_foreign_non_bank_activity(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $other = User::factory()->teacher($school)->create();
        $foreign = Activity::factory()->create([
            'created_by' => $other->id,
            'is_activity_bank' => false,
        ]);

        $this->preview($teacher, $foreign)->assertNotFound();
    }

    public function test_teacher_cannot_preview_foreign_pending_bank_activity(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $other = User::factory()->teacher($school)->create();
        $pending = Activity::factory()->pendingApproval()->create([
            'created_by' => $other->id,
            'is_activity_bank' => true,
        ]);

        $this->preview($teacher, $pending)->assertNotFound();
    }
}
