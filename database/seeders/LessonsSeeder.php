<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meaning;
use App\Models\Lesson;
use App\Models\Activity;

class LessonsSeeder extends Seeder
{
    public function run(): void
    {
        $meaning = Meaning::where('name', 'رد الأمانات')->first();
        
        if ($meaning) {
            // درس تجريبي
            $lesson = Lesson::create([
                'meaning_id' => $meaning->id,
                'title' => 'ما هي الأمانة؟',
                'content' => 'الأمانة خلق عظيم يعني حفظ الحقوق وعدم الخيانة. المسلم أمين في قوله وفعله.',
                'type' => 'text',
                'duration' => 5,
                'points' => 10,
                'order' => 1,
                'status' => 'active',
            ]);

            // نشاط: اختبار قصير
            Activity::create([
                'lesson_id' => $lesson->id,
                'title' => 'اختبر فهمك',
                'description' => 'أجب عن هذه الأسئلة حول الأمانة',
                'type' => 'quiz',
                'questions' => json_encode([
                    [
                        'question' => 'ما معنى الأمانة؟',
                        'options' => ['حفظ الحقوق', 'الكذب', 'الخيانة', 'التأخير'],
                        'correct' => 0
                    ],
                    [
                        'question' => 'ماذا يفعل الطالب الأمين؟',
                        'options' => ['يحفظ أغراض زملائه', 'يأخذ ما ليس له', 'يكذب', 'يتأخر'],
                        'correct' => 0
                    ]
                ]),
                'points' => 20,
                'passing_score' => 50,
                'order' => 1,
                'status' => 'active',
            ]);

            // نشاط: رفع صورة
            Activity::create([
                'lesson_id' => $lesson->id,
                'title' => 'طبق الأمانة',
                'description' => 'قم بفعل أمانة وارفع صورة تثبت ذلك (مثل: إعادة قلم لزميلك)',
                'type' => 'upload',
                'points' => 30,
                'order' => 2,
                'status' => 'active',
            ]);
        }
    }
}
