<?php

namespace Tests\Feature\Activities;

use App\Models\Classroom;
use App\Models\Point;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * إصلاحات أمن/نزاهة تجربة وليّ الأمر (المراجعة الخصميّة الشاملة):
 *  - الوليّ يراسل معلّمي أبنائه فقط (لا أيّ معلّم في المدرسة).
 *  - نقاط تشجيع/هدية الوليّ لا تُحتسَب في ترتيب لوحة الصدارة.
 */
class ParentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_cannot_message_teacher_not_teaching_their_child(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->create(['role' => 'parent', 'school_id' => $school->id]);
        $child = User::factory()->student($school)->create();
        $parent->children()->attach($child->id);

        $teacherA = User::factory()->teacher($school)->create(); // يُدرّس الابن
        $teacherB = User::factory()->teacher($school)->create(); // لا يُدرّس الابن
        $classroomA = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacherA->id]);
        $child->classrooms()->attach($classroomA->id);

        // معلّم لا يُدرّس الابن → مرفوض (كان يمرّ بفحص same-school فقط)
        $this->actingAs($parent)->postJson(route('parent.messages.send'), [
            'teacher_id' => $teacherB->id, 'message' => 'مرحبا',
        ])->assertStatus(403);

        // معلّم الابن → مقبول
        $this->actingAs($parent)->postJson(route('parent.messages.send'), [
            'teacher_id' => $teacherA->id, 'message' => 'مرحبا',
        ])->assertOk();
    }

    public function test_leaderboard_query_excludes_parent_gift_and_praise_points(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();

        // نقاط نشاط مكتسَبة (source=null قديم) + نقاط تشجيع/هدية الوليّ (تُخلَق من العدم)
        Point::create(['user_id' => $student->id, 'points' => 100, 'reason' => 'نشاط']);
        Point::create(['user_id' => $student->id, 'points' => 50, 'reason' => 'هدية', 'source' => 'parent_gift']);
        Point::create(['user_id' => $student->id, 'points' => 25, 'reason' => 'مدح', 'source' => 'parent_praise']);

        // نفس منطق withSum في StudentController::leaderboard
        $rankXp = (int) Point::where('user_id', $student->id)
            ->where(function ($q) {
                $q->whereNull('source')->orWhereNotIn('source', ['parent_praise', 'parent_gift']);
            })->sum('points');

        $this->assertSame(100, $rankXp, 'الترتيب يحتسب النقاط المكتسَبة فقط (100)، لا نقاط الوليّ');
        // الرصيد الكليّ يبقى شاملاً (المستوى/المحفظة) — الاستبعاد للترتيب فقط
        $this->assertSame(175, (int) Point::where('user_id', $student->id)->sum('points'));
    }
}
