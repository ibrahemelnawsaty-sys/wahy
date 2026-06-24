<?php

namespace Tests\Feature\Authorization;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BOLA / IDOR — ParentDashboardController@childDetails (route: parent/child/{id}).
 *
 * يثبت أن فحص ملكية الكائن (object-level) يعمل end-to-end عبر المسار الحقيقي:
 * الفحص داخل الـ controller هو
 *   $parent->children()->where('users.id', $childId)->first()  → abort(403) عند الفشل.
 *
 * لذا ولي أمر من مدرسة أخرى (أو غير مرتبط بالطفل) لا يستطيع رؤية طفل لا يخصّه.
 */
class ChildDetailsIdorTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_tenant_parent_cannot_view_another_schools_child(): void
    {
        // المدرسة B + ولي أمر فيها + طفله (الضحية / الكائن المملوك للمدرسة B)
        $schoolB     = School::factory()->create();
        $ownerParent = User::factory()->parent($schoolB)->create();
        $victimChild = User::factory()->student($schoolB)->create();
        $ownerParent->children()->attach($victimChild->id, ['relationship' => 'أب']);

        // المهاجم: ولي أمر من المدرسة A — غير مرتبط بطفل المدرسة B
        $schoolA       = School::factory()->create();
        $attackerParent = User::factory()->parent($schoolA)->create();

        $response = $this->actingAs($attackerParent)
            ->get("/parent/child/{$victimChild->id}");

        // الـ controller يطلق abort(403) لأن العلاقة children() لا تُرجع الطفل
        $response->assertStatus(403);
    }

    public function test_owner_parent_can_view_own_child(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->parent($school)->create();
        $child  = User::factory()->student($school)->create();
        $parent->children()->attach($child->id, ['relationship' => 'أب']);

        $response = $this->actingAs($parent)
            ->get("/parent/child/{$child->id}");

        $response->assertStatus(200);
    }
}
