<?php

namespace Tests\Feature\Economy;

use App\Actions\Activity\SubmitActivityAction;
use App\Models\Activity;
use App\Models\Classroom;
use App\Models\Coin;
use App\Models\ParentPoint;
use App\Models\Point;
use App\Models\School;
use App\Models\SchoolPoint;
use App\Models\TeacherPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — site "activitySubmissionDistribution"
 * (App\Actions\Activity\SubmitActivityAction).
 *
 * The action awards the student XP + coins and (distribute:true) fans the points out
 * to teacher/parent/school INSIDE AwardService's transaction, keyed idempotently on
 * ('activity_submission', (string) $submission->id) — the submission row id is the
 * stable domain anchor.
 *
 * CONSISTENCY BOUNDARY (option a — domain-write + award atomic together):
 * the submission row create and the award run inside ONE DB::transaction in the
 * action; the lockForUpdate duplicate guard means a replay returns 'duplicate'
 * WITHOUT re-marking or re-awarding, and if the award throws the submission rolls
 * back too — there is no marked-but-unawarded window. So the idempotency key cannot
 * be exercised by a second submission of the SAME activity at all (the guard fires
 * first); replaying the same event therefore awards exactly once, which is what (a)
 * pins end-to-end. A genuinely different activity is a different submission id => a
 * different key => a legitimate new award, which is what (b) pins.
 *
 * These tests drive the real wired entrypoint SubmitActivityAction::execute().
 */
class activitySubmissionDistributionAwardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Align the in-memory SQLite schema with production. The original migration
     * created status as enum('pending','approved','rejected','needs_review'); a later
     * migration adds 'completed' via `ALTER TABLE ... MODIFY COLUMN` which is MySQL-only
     * (a documented no-op on SQLite). The wired action writes status='completed' for any
     * auto-graded submission, so on SQLite-backed tests we must widen the stale CHECK to
     * match MySQL — otherwise we are testing a schema production never runs. The table is
     * empty immediately after RefreshDatabase migrates, so a constraint rebuild is safe.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::statement('DROP TABLE IF EXISTS activity_submissions');
        DB::statement(<<<'SQL'
            CREATE TABLE "activity_submissions" (
                "id" integer primary key autoincrement not null,
                "activity_id" integer not null,
                "student_id" integer not null,
                "answer" text,
                "file_path" varchar,
                "score" integer,
                "status" varchar check ("status" in ('pending', 'approved', 'rejected', 'needs_review', 'completed')) not null default 'pending',
                "reviewed_by" integer,
                "feedback" text,
                "submitted_at" datetime not null default CURRENT_TIMESTAMP,
                "reviewed_at" datetime,
                "created_at" datetime,
                "updated_at" datetime,
                foreign key("activity_id") references "activities"("id") on delete cascade,
                foreign key("student_id") references "users"("id") on delete cascade,
                foreign key("reviewed_by") references "users"("id") on delete set null
            )
            SQL);
        DB::statement('PRAGMA foreign_keys = ON');
    }

    /**
     * Build a single-question multiple-choice activity whose only correct answer is
     * option index 0, so a deterministic answer maps to a known score.
     *
     * @return array{0: Activity, 1: array<int,int>, 2: array<int,int>}
     *                                                                  [activity, fullCreditAnswer, zeroCreditAnswer]
     */
    private function gradableActivity(School $school, int $points = 20): array
    {
        $activity = Activity::factory()->create([
            'type' => 'quiz',
            'points' => $points,
            'passing_score' => 50,
            'questions' => [
                ['question' => 'س1', 'options' => ['أ', 'ب', 'ج', 'د'], 'correct_answer' => 0],
            ],
        ]);

        // option 0 == correct => 100%; option 1 == wrong => 0%.
        return [$activity, [0], [1]];
    }

    private function wireRelations(School $school, User $student): array
    {
        $teacher = User::factory()->teacher($school)->create();
        $parent = User::factory()->parent($school)->create();

        $classroom = Classroom::factory()->create([
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
        ]);
        $student->classrooms()->attach($classroom->id, ['status' => 'active', 'enrollment_date' => now()]);
        DB::table('parent_student')->insert(['parent_id' => $parent->id, 'student_id' => $student->id]);

        return [$teacher, $parent];
    }

    /**
     * (a) IDEMPOTENCY — replay the SAME event (same student + same activity).
     * The wired duplicate guard returns 'duplicate' and awards nothing the second
     * time: ledger stays 1, no extra Point/Coin, no fan-out, balances unchanged.
     */
    public function test_replaying_the_same_submission_event_awards_exactly_once(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        [$teacher, $parent] = $this->wireRelations($school, $student);
        [$activity, $fullCredit] = $this->gradableActivity($school);

        $action = app(SubmitActivityAction::class);

        $first = $action->execute($student, $activity, ['answer' => $fullCredit]);

        $this->assertTrue($first['success']);
        $this->assertFalse($first['duplicate']);
        $this->assertSame(100, $first['score']);
        $this->assertSame(20, $first['xp_earned']); // round(100/100 * 20)

        // Snapshot the entire economy after the first, legitimate award.
        $this->assertSame(1, DB::table('award_ledger')->count());
        $ledgerPoints = (int) Point::where('user_id', $student->id)->sum('points');
        $ledgerCoins = (int) Coin::where('user_id', $student->id)->sum('coins');
        $pointRows = Point::where('user_id', $student->id)->count();
        $coinRows = Coin::where('user_id', $student->id)->count();
        $teacherPts = (int) TeacherPoint::where('teacher_id', $teacher->id)->sum('points');
        $parentPts = (int) ParentPoint::where('parent_id', $parent->id)->sum('points');
        $schoolPts = (int) SchoolPoint::where('school_id', $school->id)->sum('points');
        $totalBefore = (int) DB::table('users')->where('id', $student->id)->value('total_points');

        $this->assertSame(20, $ledgerPoints);
        $this->assertSame(10, $ledgerCoins); // max(1, floor(20/2))
        $this->assertSame(2, $teacherPts);   // max(1, floor(20*0.10))
        $this->assertSame(1, $parentPts);    // max(1, floor(20*0.05))
        $this->assertSame(1, $schoolPts);    // max(1, floor(20*0.02))

        // Replay the SAME event — guard fires, nothing is re-marked or re-awarded.
        $second = $action->execute($student, $activity, ['answer' => $fullCredit]);

        $this->assertFalse($second['success']);
        $this->assertTrue($second['duplicate']);

        // Submission count and ledger count both stay at one.
        $this->assertSame(1, DB::table('activity_submissions')
            ->where(['student_id' => $student->id, 'activity_id' => $activity->id])->count());
        $this->assertSame(1, DB::table('award_ledger')->count());

        // EVERY economy total is byte-for-byte unchanged.
        $this->assertSame($ledgerPoints, (int) Point::where('user_id', $student->id)->sum('points'));
        $this->assertSame($ledgerCoins, (int) Coin::where('user_id', $student->id)->sum('coins'));
        $this->assertSame($pointRows, Point::where('user_id', $student->id)->count(), 'no extra Point row');
        $this->assertSame($coinRows, Coin::where('user_id', $student->id)->count(), 'no extra Coin row');
        $this->assertSame($teacherPts, (int) TeacherPoint::where('teacher_id', $teacher->id)->sum('points'), 'teacher not double-credited');
        $this->assertSame($parentPts, (int) ParentPoint::where('parent_id', $parent->id)->sum('points'), 'parent not double-credited');
        $this->assertSame($schoolPts, (int) SchoolPoint::where('school_id', $school->id)->sum('points'), 'school not double-credited');
        $this->assertSame($totalBefore, (int) DB::table('users')->where('id', $student->id)->value('total_points'), 'dead total_points untouched');
    }

    /**
     * (b) LEGITIMATE-REPEAT — a genuinely DIFFERENT event (a new activity => a new
     * submission id => a new idempotency key) DOES award. Proves the key is not too
     * coarse (it is not keyed on student alone).
     */
    public function test_a_different_activity_is_a_new_event_and_awards_again(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $this->wireRelations($school, $student);

        [$activityA, $fullCredit] = $this->gradableActivity($school);
        [$activityB] = $this->gradableActivity($school);

        $action = app(SubmitActivityAction::class);

        $a = $action->execute($student, $activityA, ['answer' => $fullCredit]);
        $this->assertTrue($a['success']);

        // A DIFFERENT domain row — distinct submission id => distinct key => awards.
        $b = $action->execute($student, $activityB, ['answer' => $fullCredit]);
        $this->assertTrue($b['success']);
        $this->assertFalse($b['duplicate']);
        $this->assertSame(20, $b['xp_earned']);

        // Two distinct ledger claims, two awards — the second genuinely accrued.
        $this->assertSame(2, DB::table('award_ledger')->count());
        $this->assertSame(40, (int) Point::where('user_id', $student->id)->sum('points'));
        $this->assertSame(20, (int) Coin::where('user_id', $student->id)->sum('coins'));
    }

    /**
     * (c) BALANCE-FLOOR — a wrong (0%) submission earns zero XP, so the action never
     * calls AwardService at all: no ledger row, no Point/Coin, and crucially no
     * negative / garbage credit is written anywhere in the economy.
     */
    public function test_zero_score_submission_writes_no_negative_or_garbage_credit(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        [$teacher, $parent] = $this->wireRelations($school, $student);
        [$activity, , $zeroCredit] = $this->gradableActivity($school);

        $action = app(SubmitActivityAction::class);

        $result = $action->execute($student, $activity, ['answer' => $zeroCredit]);

        // Submission is recorded (completed, score 0) but no economy effect occurs.
        $this->assertTrue($result['success']);
        $this->assertSame(0, $result['score']);
        $this->assertSame(0, $result['xp_earned']);

        $this->assertSame(1, DB::table('activity_submissions')->count());
        $this->assertSame(0, DB::table('award_ledger')->count());
        $this->assertSame(0, Point::where('user_id', $student->id)->count());
        $this->assertSame(0, Coin::where('user_id', $student->id)->count());

        // Floor holds: no negative ledger rows anywhere, no stray fan-out.
        $this->assertSame(0, (int) Point::where('user_id', $student->id)->sum('points'));
        $this->assertSame(0, (int) Coin::where('user_id', $student->id)->sum('coins'));
        $this->assertSame(0, DB::table('award_ledger')->where('points', '<', 0)->count());
        $this->assertSame(0, DB::table('award_ledger')->where('coins', '<', 0)->count());
        $this->assertSame(0, (int) TeacherPoint::where('teacher_id', $teacher->id)->sum('points'));
        $this->assertSame(0, (int) ParentPoint::where('parent_id', $parent->id)->sum('points'));
        $this->assertSame(0, (int) SchoolPoint::where('school_id', $school->id)->sum('points'));
    }
}
