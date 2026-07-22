<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckHomeworkDueDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'homework:check-due-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'التحقق من مواعيد تسليم الواجبات وإرسال تذكيرات';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 جاري التحقق من مواعيد تسليم الواجبات...');

        $now = Carbon::now();
        $in24Hours = $now->copy()->addHours(24);

        // الواجبات التي موعدها خلال 24 ساعة. لم نعد نفلتر بـapproval_status='approved': بعد ميزة
        // النشر صار «معتمَد» ≠ «مرئيّ»، والرؤية تُحسَم لكلّ طالب عبر isVisibleToStudentSchool أدناه
        // (تُغطّي الاتّجاهين: لا تذكير بما لا يُرى، وتذكير بالمنشور مباشرةً حتى قبل اعتماد الأدمن).
        $upcomingHomework = Activity::where('is_homework', true)
            ->where('status', 'active')
            ->whereBetween('due_date', [$now, $in24Hours])
            ->with(['classroom.students', 'creator'])
            ->get();

        $notificationsSent = 0;

        foreach ($upcomingHomework as $homework) {
            // الحصول على الطلاب المستهدفين
            if ($homework->classroom_id) {
                // فصل محدد
                $students = $homework->classroom->students;
            } else {
                // جميع طلاب المعلم
                $students = $homework->creator->teachingClassrooms()
                    ->with('students')
                    ->get()
                    ->pluck('students')
                    ->flatten()
                    ->unique('id');
            }

            foreach ($students as $student) {
                // لا تُذكّر إلا من يرى الواجب فعلاً (منشور مباشرةً لمدرسته أو مُسنَد لأحد فصوله)
                if (! $homework->isVisibleToStudentSchool($student->school_id, $student->classrooms->pluck('id')->all())) {
                    continue;
                }

                // التحقق من أن الطالب لم يسلم بعد
                $submission = ActivitySubmission::where('activity_id', $homework->id)
                    ->where('student_id', $student->id)
                    ->first();

                if (! $submission) {
                    // إرسال تذكير
                    NotificationService::homeworkReminder(
                        $student->id,
                        $homework->title,
                        $homework->due_date,
                    );
                    $notificationsSent++;
                }
            }
        }

        $this->info("✅ تم إرسال {$notificationsSent} تذكير");

        // التحقق من الواجبات المتأخرة (الرؤية تُحسَم لكلّ طالب أدناه، لا بـapproval_status)
        $overdueHomework = Activity::where('is_homework', true)
            ->where('status', 'active')
            ->where('due_date', '<', $now)
            ->with(['classroom.students', 'creator'])
            ->get();

        $overdueNotifications = 0;

        foreach ($overdueHomework as $homework) {
            // الحصول على الطلاب المستهدفين
            if ($homework->classroom_id) {
                $students = $homework->classroom->students;
            } else {
                $students = $homework->creator->teachingClassrooms()
                    ->with('students')
                    ->get()
                    ->pluck('students')
                    ->flatten()
                    ->unique('id');
            }

            foreach ($students as $student) {
                // لا تُنبّه إلا من يرى الواجب فعلاً
                if (! $homework->isVisibleToStudentSchool($student->school_id, $student->classrooms->pluck('id')->all())) {
                    continue;
                }

                // التحقق من أن الطالب لم يسلم بعد
                $submission = ActivitySubmission::where('activity_id', $homework->id)
                    ->where('student_id', $student->id)
                    ->first();

                if (! $submission) {
                    // إرسال تنبيه بالتأخير (مرة واحدة فقط)
                    $alreadyNotified = \App\Models\Notification::where('user_id', $student->id)
                        ->where('type', 'homework_overdue')
                        ->where('data->activity_id', $homework->id)
                        ->exists();

                    if (! $alreadyNotified) {
                        NotificationService::homeworkOverdue(
                            $student->id,
                            $homework->title,
                            $homework->due_date,
                        );
                        $overdueNotifications++;
                    }
                }
            }
        }

        $this->info("⚠️  تم إرسال {$overdueNotifications} تنبيه تأخير");
        $this->info('✨ تم الانتهاء من التحقق');

        return Command::SUCCESS;
    }
}
