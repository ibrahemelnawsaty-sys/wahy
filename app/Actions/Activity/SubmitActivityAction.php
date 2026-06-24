<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\User;
use App\Services\ActivityGradingService;
use App\Services\AwardService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
    /**
     * @param  array<string, mixed>  $payload  ['answer' => ..., 'file' => UploadedFile|null]
     */
    public function execute(User $student, Activity $activity, array $payload): array
    {
        // 1. معالجة الملف المرفق (خارج المعاملة لأن I/O قد يستغرق وقتًا)
        $uploadedPath = null;
        if (isset($payload['file']) && $payload['file'] instanceof UploadedFile) {
            $uploadedPath = $payload['file']->store(
                'activity-submissions/' . $student->id,
                'public',
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
                        'success' => false,
                        'duplicate' => true,
                        'message' => 'تم إرسال هذا النشاط مسبقاً',
                    ];
                }

                $submission = ActivitySubmission::create([
                    'student_id' => $student->id,
                    'activity_id' => $activity->id,
                    'answer' => $answerToStore,
                    'status' => $status,
                    'score' => $score,
                    'submitted_at' => now(),
                ]);

                // منح الطالب XP/Coins + توزيع المعلم/الولي/المدرسة كوحدة ذرّية واحدة.
                // ملاحظة الحدود (a): الدالة تفتح معاملتها الخاصة لكنها تتداخل هنا كـ savepoint
                // داخل معاملة هذا الإجراء — فإن رمت لأي سبب تراجع إنشاء الـ submission كاملًا.
                // المفتاح activity_submission + submission.id يضمن exactly-once. ولأن حارس
                // الـ lockForUpdate يُرجِع 'duplicate' عند الإعادة (فلا تُعاد المحاولة لتمنح)،
                // يجب أن يكون المنح ذرّيًا مع الإنشاء — لا نافذة marked-but-unawarded.
                if ($xp > 0) {
                    // داخل هذا الفرع $score غير فارغ دائماً (xp يساوي 0 عندما يكون score = null).
                    $scoreText = " | الدرجة: {$score}% | {$xp}/{$activityPoints} نقطة";

                    AwardService::award(
                        $student->id,
                        'activity_submission',
                        (string) $submission->id,
                        $xp,
                        max(1, (int) floor($xp / 2)),
                        'إكمال نشاط: ' . $activity->title . $scoreText,
                        distribute: true,
                    );
                }

                return [
                    'success' => true,
                    'submission' => $submission,
                    'xp_earned' => $xp,
                    'activity_points' => $activityPoints,
                    'score' => $score,
                    'duplicate' => false,
                ];
            }, 3);
        } catch (\Throwable $e) {
            // تنظيف الملف المرفوع لو فشلت المعاملة
            if ($uploadedPath) {
                try {
                    Storage::disk('public')->delete($uploadedPath);
                } catch (\Throwable $ignore) {
                }
            }
            throw $e;
        }

        return $result;
    }

    private function buildAnswerPayload(mixed $rawAnswer, ?string $uploadedPath): ?string
    {
        if ($uploadedPath) {
            return json_encode([
                'note' => is_array($rawAnswer) ? null : $rawAnswer,
                'file' => $uploadedPath,
                'file_url' => Storage::url($uploadedPath),
            ], JSON_UNESCAPED_UNICODE);
        }

        return is_array($rawAnswer)
            ? json_encode($rawAnswer, JSON_UNESCAPED_UNICODE)
            : $rawAnswer;
    }
}
