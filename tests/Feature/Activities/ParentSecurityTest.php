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

    public function test_parent_approval_defers_child_economy_until_approved(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $parent = User::factory()->create(['role' => 'parent', 'school_id' => $school->id]);
        $parent->children()->attach($student->id);

        $mcq = fn () => ['type' => 'multiple_choice', 'correct_index' => 0,
            'options' => [['text' => 'أ', 'is_correct' => true], ['text' => 'ب', 'is_correct' => false]]];
        $activity = \App\Models\Activity::factory()->create([
            'lesson_id' => null, 'status' => 'active', 'all_schools_mode' => 'direct',
            'type' => 'quiz', 'points' => 10, 'passing_score' => 60, 'manual_review' => false,
            'requires_parent_approval' => true, 'questions' => [$mcq(), $mcq()],
        ]);

        // الابن يسلّم (صحيح+خطأ = 50%) → مؤجَّل: pending + بلا نقاط للابن
        $this->actingAs($student)
            ->postJson(route('student.activity.submit', $activity->id), ['answer' => [0, 1]])->assertOk();
        $sub = \App\Models\ActivitySubmission::where('student_id', $student->id)->firstOrFail();
        $this->assertSame('pending', $sub->status);
        $this->assertSame('pending', $sub->parent_approval_status);
        $this->assertSame(0, (int) Point::where('user_id', $student->id)->sum('points'), 'لا نقاط قبل موافقة الوليّ');

        // الوليّ يوافق → يُمنَح الابن وتُنهى الحالة (needs_review لأنّ 50 < 60)
        $this->actingAs($parent)
            ->post(route('parent.family-activities.parent-approve', $sub->id))->assertRedirect();
        $sub->refresh();
        $this->assertSame('needs_review', $sub->status);
        $this->assertSame('approved', $sub->parent_approval_status);
        $this->assertSame(5, (int) Point::where('user_id', $student->id)->sum('points'), '50% من 10 = 5 بعد الموافقة');
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
