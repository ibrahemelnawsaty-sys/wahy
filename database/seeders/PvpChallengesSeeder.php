<?php

namespace Database\Seeders;

use App\Models\PvpChallenge;
use App\Models\QuestionBank;
use Illuminate\Database\Seeder;

class PvpChallengesSeeder extends Seeder
{
    /**
     * يُنشئ تحدي افتراضي طالب ضد طالب باستخدام أسئلة من بنك الأسئلة.
     * إن لم يوجد بنك أسئلة، يُنشأ تحدي بأسئلة فارغة (يملؤه الأدمن لاحقاً).
     */
    public function run(): void
    {
        if (PvpChallenge::where('title', 'تحدي القيم — العام')->exists()) {
            return;
        }

        // العمود الصحيح هو `status` وليس `is_active` في جدول question_bank
        $questionIds = QuestionBank::query()
            ->where('status', 'approved')
            ->limit(10)
            ->pluck('id')
            ->toArray();

        PvpChallenge::create([
            'title'         => 'تحدي القيم — العام',
            'questions'     => $questionIds,
            'time_limit'    => 15 * max(1, count($questionIds)),   // 15 ثانية لكل سؤال
            'is_active'     => true,
            'created_by'    => null,
        ]);
    }
}
