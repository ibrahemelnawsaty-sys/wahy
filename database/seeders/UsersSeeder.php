<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. السوبر أدمن
        $superAdmin = User::create([
            'name' => 'مدير النظام',
            'email' => 'ibrahemelnawsaty@gmail.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'super_admin',
            'qr_code' => 'SA-ADMIN-001',
            'status' => 'active',
            'two_factor_enabled' => false,
        ]);

        // 2. مدرسة تجريبية
        $school = School::create([
            'name' => 'مدرسة القيم النموذجية',
            'description' => 'مدرسة رائدة في تعليم القيم',
            'address' => 'الرياض، حي النخيل',
            'city' => 'الرياض',
            'contact_email' => 'info@qiyamschool.sa',
            'contact_phone' => '0112345678',
            'qr_code' => 'SCH-001',
            'created_by' => $superAdmin->id,
            'status' => 'active',
        ]);

        // 3. مدير المدرسة
        User::create([
            'name' => 'أحمد محمد',
            'email' => 'school@qiyamm.sa',
            'password' => Hash::make('School@123'),
            'role' => 'school_admin',
            'school_id' => $school->id,
            'qr_code' => 'SA-SCH-ADM-001',
            'phone' => '0501234567',
            'status' => 'active',
        ]);

        // 4. معلمين
        User::create([
            'name' => 'فاطمة علي',
            'email' => 'teacher1@qiyamm.sa',
            'password' => Hash::make('Teacher@123'),
            'role' => 'teacher',
            'school_id' => $school->id,
            'qr_code' => 'SA-TCH-001',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'خالد عبدالله',
            'email' => 'teacher2@qiyamm.sa',
            'password' => Hash::make('Teacher@123'),
            'role' => 'teacher',
            'school_id' => $school->id,
            'qr_code' => 'SA-TCH-002',
            'status' => 'active',
        ]);

        // 5. طلاب
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "طالب $i",
                'email' => "student$i@qiyamm.sa",
                'password' => Hash::make('Student@123'),
                'role' => 'student',
                'school_id' => $school->id,
                'qr_code' => "SA-STU-00$i",
                'status' => 'active',
            ]);
        }

        // 6. أولياء أمور
        User::create([
            'name' => 'ولي أمر 1',
            'email' => 'parent1@qiyamm.sa',
            'password' => Hash::make('Parent@123'),
            'role' => 'parent',
            'school_id' => $school->id,
            'qr_code' => 'SA-PAR-001',
            'status' => 'active',
        ]);

        // 7. مستخدم بأكثر من دور (معلم وولي أمر)
        $saraAhmed = User::create([
            'name' => 'سارة أحمد',
            'email' => 'sara@qiyamm.sa',
            'password' => Hash::make('Sara@123'),
            'role' => 'teacher',
            'secondary_roles' => ['parent'],
            'school_id' => $school->id,
            'qr_code' => 'SA-TCH-003',
            'status' => 'active',
        ]);

        // 8. إضافة بيانات ربط أولياء الأمور بالطلاب
        $parent1 = User::where('email', 'parent1@qiyamm.sa')->first();
        $student1 = User::where('email', 'student1@qiyamm.sa')->first();
        $student2 = User::where('email', 'student2@qiyamm.sa')->first();
        $student3 = User::where('email', 'student3@qiyamm.sa')->first();

        // ربط ولي الأمر الأول بطالبين
        if ($parent1 && $student1 && $student2) {
            $parent1->children()->attach($student1->id, ['relationship' => 'أب']);
            $parent1->children()->attach($student2->id, ['relationship' => 'أب']);
        }

        // ربط سارة أحمد (معلم + ولي أمر) بطالب
        if ($saraAhmed && $student3) {
            $saraAhmed->children()->attach($student3->id, ['relationship' => 'أم']);
        }
    }
}
