<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Point;

class PointsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();

        if ($students->isEmpty()) {
            $this->command->error('لا يوجد طلاب! قم بتشغيل UsersSeeder أولاً.');
            return;
        }

        $reasons = [
            'إكمال نشاط',
            'إكمال درس',
            'مشاركة فعالة',
            'إتمام واجب',
            'تفوق في الاختبار',
            'مساعدة الزملاء',
            'حضور منتظم',
            'سلوك جيد',
        ];

        foreach ($students as $student) {
            // إنشاء 10-30 نقطة عشوائية لكل طالب
            $pointsCount = rand(10, 30);
            
            for ($i = 0; $i < $pointsCount; $i++) {
                Point::create([
                    'user_id' => $student->id,
                    'points' => rand(5, 50),
                    'reason' => $reasons[array_rand($reasons)],
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now()->subDays(rand(0, 90)),
                ]);
            }

            $total = Point::where('user_id', $student->id)->sum('points');
            $this->command->info("✅ أضيف {$pointsCount} نقطة للطالب {$student->name} - الإجمالي: {$total}");
        }

        $this->command->info('✅ تم إضافة النقاط لجميع الطلاب بنجاح!');
    }
}
