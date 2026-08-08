<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Concept;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonsSeeder extends Seeder
{
    public function run(): void
    {
        // الدروس تُربط مباشرةً بالمفهوم بعد إزالة «المعاني» من المنظومة.
        $concept = Concept::where('name', 'حفظ الحقوق')->first();

        if ($concept) {
            // درس تجريبي
            $lesson = Lesson::create([
                'concept_id' => $concept->id,
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
                        'correct' => 0,
                    ],
                    [
                        'question' => 'ماذا يفعل الطالب الأمين؟',
                        'options' => ['يحفظ أغراض زملائه', 'يأخذ ما ليس له', 'يكذب', 'يتأخر'],
                        'correct' => 0,
                    ],
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
