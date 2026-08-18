<?php

namespace Tests\Feature\Economy;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Classroom;
use App\Models\Coin;
use App\Models\Point;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * مكافأة تمييز التسليم: حين يميّز المعلّم عملَ طالبٍ متميّز يُمنح الطالب 10 نقاط إضافيّة.
 *
 * §3 (سلامة الاقتصاد) هي المحور هنا — لا مجرّد «هل وصلت النقاط»:
 *  • القناة الوحيدة AwardService::award (يُمنع Point::create مباشرةً).
 *  • مفتاح idempotency = (student, 'submission_featured', submission_id) ⟹ تمييزٌ ثمّ إلغاءٌ ثمّ
 *    تمييزٌ ثانٍ **لا يمنح مرّتين** — وإلّا صار زرّ التمييز مطبعةَ نقودٍ بيد المعلّم.
 *  • بلا توزيع فان-أوت: التوزيع يمنح المعلّمَ نسبةً من منحةٍ هو مَن قرّرها — حافزٌ منحرف.
 */
class FeaturedSubmissionBonusTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $teacher;

    private User $student;

    private ActivitySubmission $submission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $this->teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $this->school->id]);
        $this->student = User::factory()->create(['role' => 'student', 'school_id' => $this->school->id]);

        $classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
        ]);
        $classroom->students()->attach($this->student->id);

        $activity = Activity::factory()->create(['status' => 'active']);
        $this->submission = ActivitySubmission::create([
            'activity_id' => $activity->id,
            'student_id' => $this->student->id,
            'answer' => 'إجابة متميّزة',
            'status' => 'approved',
            'submitted_at' => now(),
        ]);
    }

    private function feature(?User $actor = null)
    {
        return $this->actingAs($actor ?? $this->teacher)
            ->post(route('teacher.review.feature', $this->submission->id), ['reason' => 'عمل متميّز']);
    }

    private function studentPoints(): int
    {
        return (int) Point::where('user_id', $this->student->id)->sum('points');
    }

    public function test_featuring_awards_the_student_ten_points(): void
    {
        $this->feature();

        $this->assertSame(10, $this->studentPoints());
        $this->assertTrue($this->submission->fresh()->is_featured);
    }

    public function test_award_goes_through_the_ledger_with_a_stable_key(): void
    {
        // §3: القناة الوحيدة تُقيّد في award_ledger بمفتاحٍ ثابت مبنيّ على معرّف التسليم.
        $this->feature();

        $this->assertDatabaseHas('award_ledger', [
            'user_id' => $this->student->id,
            'source_type' => 'submission_featured',
            'source_id' => (string) $this->submission->id,
        ]);
        $this->assertSame(1, (int) DB::table('award_ledger')->count());
    }

    public function test_refeaturing_after_unfeature_never_pays_twice(): void
    {
        // الاستغلال الواضح: تمييز ⟵ إلغاء ⟵ تمييز… حلقةٌ تطبع النقاط بلا حدّ.
        $this->feature();
        $this->actingAs($this->teacher)->post(route('teacher.review.unfeature', $this->submission->id));
        $this->feature();
        $this->feature();

        $this->assertSame(10, $this->studentPoints(), 'المنحة مرّة واحدة لكلّ تسليم مهما تكرّر التمييز');
        $this->assertSame(1, (int) DB::table('award_ledger')->count());
    }

    public function test_no_fanout_to_teacher_parent_or_school(): void
    {
        // توزيع الفان-أوت يمنح المعلّمَ 10% من منحةٍ هو مَن قرّرها — حافزٌ منحرف، فيُمنع هنا.
        $this->feature();

        $this->assertSame(0, (int) Point::where('user_id', $this->teacher->id)->sum('points'));
        $this->assertSame(0, (int) Coin::where('user_id', $this->student->id)->sum('coins'));
    }

    public function test_unauthorized_teacher_neither_features_nor_pays(): void
    {
        // §4: معلّمٌ من مدرسة أخرى لا يملك التسليم — لا تمييز ولا سكّ نقاط.
        $stranger = User::factory()->create([
            'role' => 'teacher',
            'school_id' => School::factory()->create()->id,
        ]);

        $this->feature($stranger);

        $this->assertFalse((bool) $this->submission->fresh()->is_featured);
        $this->assertSame(0, $this->studentPoints());
        $this->assertSame(0, (int) DB::table('award_ledger')->count());
    }

    public function test_unfeaturing_does_not_claw_back_points(): void
    {
        // §3 «سجلّات إضافة فقط»: لا يُحذف تاريخ؛ إلغاء التمييز يرفع الوسم ولا يسحب المنحة.
        $this->feature();
        $this->actingAs($this->teacher)->post(route('teacher.review.unfeature', $this->submission->id));

        $this->assertFalse((bool) $this->submission->fresh()->is_featured);
        $this->assertSame(10, $this->studentPoints());
    }

    // ---------------- الإشعار + البريد + الإعداد ----------------

    public function test_student_gets_an_in_app_notification(): void
    {
        $this->feature();

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $this->student->id,
            'type' => 'submission_featured',
        ]);
    }

    public function test_student_gets_an_email(): void
    {
        Mail::fake();
        $this->feature();

        Mail::assertSent(\App\Mail\StudentSubmissionFeaturedMail::class, function ($mail) {
            return $mail->hasTo($this->student->email);
        });
    }

    public function test_no_duplicate_notification_or_email_on_refeature(): void
    {
        // لا يُزعَج الطالب مرّتين بمنحةٍ لم تتكرّر — الإشعار مشروطٌ بنجاح المنح لا بضغط الزرّ.
        Mail::fake();
        $this->feature();
        $this->actingAs($this->teacher)->post(route('teacher.review.unfeature', $this->submission->id));
        $this->feature();

        Mail::assertSentCount(1);
        $this->assertSame(1, \App\Models\Notification::where('notifiable_id', $this->student->id)
            ->where('type', 'submission_featured')->count());
    }

    public function test_points_amount_follows_the_admin_setting(): void
    {
        set_setting('featured_submission_points', 25, 'integer');
        \App\Models\Setting::clearCache();

        $this->feature();

        $this->assertSame(25, $this->studentPoints());
    }

    public function test_zero_setting_awards_nothing_but_still_features(): void
    {
        // الإدارة قد تُعطّل المكافأة بصفر — التمييز يبقى ميزةً عرضيّة قائمة.
        set_setting('featured_submission_points', 0, 'integer');
        \App\Models\Setting::clearCache();

        $this->feature();

        $this->assertSame(0, $this->studentPoints());
        $this->assertTrue($this->submission->fresh()->is_featured);
    }

    public function test_email_is_not_sent_when_the_award_did_not_happen(): void
    {
        // §5: لا نُبلّغ الطالب بمنحةٍ لم تُسكّ (مثلاً حين تكون القيمة صفراً).
        Mail::fake();
        set_setting('featured_submission_points', 0, 'integer');
        \App\Models\Setting::clearCache();

        $this->feature();

        Mail::assertNothingSent();
    }
}
