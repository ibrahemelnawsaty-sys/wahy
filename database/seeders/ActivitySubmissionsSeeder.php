<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySubmissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();

        if ($students->isEmpty()) {
            $this->command->error('لا يوجد طلاب!');

            return;
        }

        // إنشاء دروس وأنشطة إذا لم تكن موجودة
        $lesson = Lesson::first();
        if (! $lesson) {
            $lesson = Lesson::create([
                'title' => 'درس تجريبي - الصدق',
                'description' => 'درس عن قيمة الصدق',
                'value_id' => 1,
                'order' => 1,
            ]);
        }

        $activities = Activity::limit(5)->get();
        if ($activities->isEmpty()) {
            // إنشاء أنشطة تجريبية
            for ($i = 1; $i <= 5; $i++) {
                Activity::create([
                    'title' => "نشاط تجريبي {$i}",
                    'description' => "وصف النشاط {$i}",
                    'type' => 'individual',
                    'lesson_id' => $lesson->id,
                    'points' => rand(10, 50),
                ]);
            }
            $activities = Activity::limit(5)->get();
        }

        foreach ($students as $student) {
            // إنشاء 5-15 نشاط مقدم لكل طالب
            $submissionsCount = rand(5, 15);

            for ($i = 0; $i < $submissionsCount; $i++) {
                $activity = $activities->random();
                $status = ['pending', 'approved', 'rejected'][rand(0, 2)];

                ActivitySubmission::create([
                    'student_id' => $student->id,
                    'activity_id' => $activity->id,
                    'status' => $status,
                    'submitted_at' => now()->subDays(rand(0, 60)),
                    'reviewed_at' => $status !== 'pending' ? now()->subDays(rand(0, 50)) : null,
                    'created_at' => now()->subDays(rand(0, 60)),
                    'updated_at' => now()->subDays(rand(0, 60)),
                ]);
            }

            $approved = ActivitySubmission::where('student_id', $student->id)
                ->where('status', 'approved')
                ->count();
            $this->command->info("✅ أضيف {$submissionsCount} نشاط للطالب {$student->name} - المعتمد: {$approved}");
        }

        $this->command->info('✅ تم إضافة الأنشطة لجميع الطلاب بنجاح!');
    }
}
