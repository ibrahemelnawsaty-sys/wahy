<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Coin;
use App\Models\Point;
use App\Models\User;
use App\Services\Activity\PointsDistributionService;
use App\Services\ActivityGradingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Action: تقديم نشاط من طالب — يلخّص 340 سطر من StudentController::submitActivity.
 *
 * المسؤولية: تنسيق التدفق الكامل من validation → grading → save → distribute.
 *
 * استخدام:
 *   $action = app(SubmitActivityAction::class);
 *   $result = $action->execute($student, $activity, $request);
 *
 * يرجع array: [
 *   'success' => bool,
 *   'submission' => ActivitySubmission,
 *   'xp_earned' => int,
 *   'score' => ?int,
 *   'duplicate' => bool, // true إذا كان النشاط مُقدّم سابقاً
 * ]
 */
class SubmitActivityAction
{
    public function __construct(
        private PointsDistributionService $pointsDistribution
    ) {
    }

    /**
     * @param  array<string, mixed> $payload  ['answer' => ..., 'file' => UploadedFile|null]
     */
    public function execute(User $student, Activity $activity, array $payload): array
    {
        // 1. معالجة الملف المرفق (خارج المعاملة لأن I/O قد يستغرق وقتًا)
        $uploadedPath = null;
        if (isset($payload['file']) && $payload['file'] instanceof UploadedFile) {
            $uploadedPath = $payload['file']->store(
                'activity-submissions/' . $student->id,
                'public'
            );
        }

        $rawAnswer = $payload['answer'] ?? null;

        // 2. تصحيح آلي
        $score = ActivityGradingService::grade($activity, $rawAnswer);
        $status = $score !== null ? 'completed' : 'pending';
        $answerToStore = $this->buildAnswerPayload($rawAnswer, $uploadedPath);
        $activityPoints = (int) ($activity->points ?? 10);
        $xp = $score !== null
            ? (int) round(($score / 100) * $activityPoints)
            : 0;

        // 3. تنفيذ ذرّي: فحص duplicate + إنشاء submission + منح XP/Coins
        //    داخل DB::transaction مع lockForUpdate لمنع double-submit race
        try {
            $result = DB::transaction(function () use ($student, $activity, $answerToStore, $status, $score, $xp, $activityPoints) {
                // فحص duplicate تحت قفل صفّي لمنع race
                $exists = ActivitySubmission::where('student_id', $student->id)
                    ->where('activity_id', $activity->id)
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    return [
                        'success'   => false,
                        'duplicate' => true,
                        'message'   => 'تم إرسال هذا النشاط مسبقاً',
                    ];
                }

                $submission = ActivitySubmission::create([
                    'student_id'   => $student->id,
                    'activity_id'  => $activity->id,
                    'answer'       => $answerToStore,
                    'status'       => $status,
                    'score'        => $score,
                    'submitted_at' => now(),
                ]);

                if ($xp > 0) {
                    $this->awardXpAndCoins($student, $activity, $xp, $activityPoints, $score);
                }

                return [
                    'success'         => true,
                    'submission'      => $submission,
                    'xp_earned'       => $xp,
                    'activity_points' => $activityPoints,
                    'score'           => $score,
                    'duplicate'       => false,
                ];
            }, 3);
        } catch (\Throwable $e) {
            // تنظيف الملف المرفوع لو فشلت المعاملة
            if ($uploadedPath) {
                try { Storage::disk('public')->delete($uploadedPath); } catch (\Throwable $ignore) {}
            }
            throw $e;
        }

        // 4. توزيع النقاط على المعلم/ولي الأمر/المدرسة (بعد commit ناجح)
        if (!empty($result['success']) && $xp > 0) {
            $this->pointsDistribution->distribute(
                $student,
                $xp,
                'activity_completion',
                $activity->title
            );
        }

        return $result;
    }

    private function buildAnswerPayload(mixed $rawAnswer, ?string $uploadedPath): ?string
    {
        if ($uploadedPath) {
            return json_encode([
                'note'     => is_array($rawAnswer) ? null : $rawAnswer,
                'file'     => $uploadedPath,
                'file_url' => Storage::url($uploadedPath),
            ], JSON_UNESCAPED_UNICODE);
        }

        return is_array($rawAnswer)
            ? json_encode($rawAnswer, JSON_UNESCAPED_UNICODE)
            : $rawAnswer;
    }

    private function awardXpAndCoins(User $student, Activity $activity, int $xp, int $activityPoints, ?int $score): void
    {
        try {
            Point::create([
                'user_id'     => $student->id,
                'points'      => $xp,
                'reason'      => 'إكمال نشاط: ' . $activity->title,
                'activity_id' => $activity->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Activity XP award failed', [
                'student_id'  => $student->id,
                'activity_id' => $activity->id,
                'error'       => $e->getMessage(),
            ]);
        }

        try {
            $scoreText = $score !== null
                ? " | الدرجة: {$score}% | {$xp}/{$activityPoints} نقطة"
                : " | {$xp} نقطة";

            Coin::create([
                'user_id'          => $student->id,
                'coins'            => max(1, (int) floor($xp / 2)),
                'reason'           => 'إكمال نشاط: ' . $activity->title . $scoreText,
                'transaction_type' => 'earn',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Activity coins award failed', [
                'student_id'  => $student->id,
                'activity_id' => $activity->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
