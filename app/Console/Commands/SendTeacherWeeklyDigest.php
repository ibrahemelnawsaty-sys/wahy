<?php

namespace App\Console\Commands;

use App\Mail\TeacherWeeklyDigestMail;
use App\Models\User;
use App\Services\Mail\MailGate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * الملخّص الأسبوعيّ للمعلّمين (خطّة أدوار البريد T6): تذكير بالتسليمات المعلّقة.
 * يُرسَل فقط لمن لديه تسليمات بانتظار المراجعة. عبر MailGate (فئة digest).
 */
class SendTeacherWeeklyDigest extends Command
{
    protected $signature = 'emails:digest-teacher-weekly';

    protected $description = 'إرسال الملخّص الأسبوعيّ للمعلّمين عن التسليمات المعلّقة';

    public function handle(): int
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('classrooms')) {
            $this->warn('لا يوجد جدول classrooms — تُخطّى.');

            return self::SUCCESS;
        }

        $sent = 0;

        User::where('role', 'teacher')->whereNotNull('email')->where('email', '!=', '')
            ->chunkById(200, function ($teachers) use (&$sent) {
                foreach ($teachers as $teacher) {
                    try {
                        $classroomIds = DB::table('classrooms')->where('teacher_id', $teacher->id)->pluck('id');
                        if ($classroomIds->isEmpty()) {
                            continue;
                        }
                        // أرقام الملخّص لا تحتسب طلاب الديمو
                        $studentIds = DB::table('classroom_student')
                            ->join('users', 'users.id', '=', 'classroom_student.student_id')
                            ->whereIn('classroom_student.classroom_id', $classroomIds)
                            ->where('users.is_demo', false)
                            ->pluck('classroom_student.student_id')->unique();
                        if ($studentIds->isEmpty()) {
                            continue;
                        }
                        $pending = DB::table('activity_submissions')
                            ->whereIn('student_id', $studentIds)
                            ->whereIn('status', ['pending', 'needs_review'])
                            ->count();
                    } catch (\Throwable $e) {
                        continue;
                    }

                    if ($pending <= 0) {
                        continue;
                    }

                    if (MailGate::send($teacher, 'teacher_weekly_digest', 'digest', new TeacherWeeklyDigestMail($teacher, (int) $pending, $studentIds->count()))) {
                        $sent++;
                    }
                }
            });

        $this->info("الملخّص الأسبوعيّ للمعلّمين: أُرسِل لـ{$sent} معلّم.");

        return self::SUCCESS;
    }
}
