<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;

class ClassroomStudentSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // إنشاء فصول دراسية إذا لم تكن موجودة
        $school = \App\Models\School::first();
        
        if (!$school) {
            $this->command->error('لا توجد مدارس! قم بتشغيل UsersSeeder أولاً.');
            return;
        }

        // إنشاء فصول دراسية
        $classrooms = [
            [
                'name' => 'الصف الأول الابتدائي - أ',
                'grade_level' => 'الصف الأول',
                'school_id' => $school->id,
                'teacher_id' => User::where('role', 'teacher')->first()?->id,
                'capacity' => 30,
                'status' => 'active',
            ],
            [
                'name' => 'الصف الثاني الابتدائي - ب',
                'grade_level' => 'الصف الثاني',
                'school_id' => $school->id,
                'teacher_id' => User::where('role', 'teacher')->skip(1)->first()?->id,
                'capacity' => 30,
                'status' => 'active',
            ],
            [
                'name' => 'الصف الثالث الابتدائي - أ',
                'grade_level' => 'الصف الثالث',
                'school_id' => $school->id,
                'teacher_id' => User::where('role', 'teacher')->first()?->id,
                'capacity' => 30,
                'status' => 'active',
            ],
        ];

        foreach ($classrooms as $classroomData) {
            Classroom::updateOrCreate(
                ['name' => $classroomData['name']],
                $classroomData
            );
        }

        // ربط الطلاب بالفصول
        $students = User::where('role', 'student')->get();
        $classroomIds = Classroom::pluck('id')->toArray();

        foreach ($students as $index => $student) {
            $classroomId = $classroomIds[$index % count($classroomIds)];
            
            DB::table('classroom_student')->updateOrInsert(
                [
                    'student_id' => $student->id,
                    'classroom_id' => $classroomId,
                ],
                [
                    'enrollment_date' => now()->subMonths(rand(1, 6)),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->command->info("ربط الطالب {$student->name} بالفصل #{$classroomId}");
        }

        $this->command->info('✅ تم ربط جميع الطلاب بالفصول بنجاح!');
    }
}
