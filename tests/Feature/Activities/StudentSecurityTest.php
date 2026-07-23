<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * إصلاحات أمن تجربة الطالب (مراجعة خصميّة شاملة):
 *  - تسريب مفتاح الإجابات عبر API activityDetails (questionsForStudent يُسقِط المفاتيح).
 *  - XSS مخزَّن في الرسائل: getConversation يُعقّم المحتوى قبل بثّه للعارض الذي يحقنه خامّاً.
 */
class StudentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_questions_for_student_strips_answer_keys(): void
    {
        $activity = Activity::factory()->make([
            'questions' => [
                [
                    'text' => 'ما العاصمة؟', 'type' => 'multiple_choice',
                    'correct_index' => 2, 'correct' => 2, 'correct_answer' => 'الرياض',
                    'options' => [
                        ['text' => 'جدة', 'is_correct' => false],
                        ['text' => 'مكة', 'is_correct' => false],
                        ['text' => 'الرياض', 'is_correct' => true],
                    ],
                ],
                ['text' => 'اكتب', 'type' => 'short_answer', 'answer' => 'سرّ', 'word' => 'سرّ'],
            ],
        ]);

        $clean = $activity->questionsForStudent();
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);

        foreach (['correct_index', 'correct_answer', 'is_correct', '"correct"', '"answer"', '"word"'] as $leak) {
            $this->assertStringNotContainsString($leak, $json, "لا يجب أن يتسرّب $leak");
        }
        // يُبقي نصّ السؤال والخيارات
        $this->assertStringContainsString('العاصمة', $json);
        $this->assertStringContainsString('الرياض', $json); // كنصّ خيار فقط، لا كمفتاح
        $this->assertSame('multiple_choice', $clean[0]['type']);
        $this->assertCount(3, $clean[0]['options']);
    }

    public function test_api_activity_details_does_not_leak_answer_keys(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = Activity::factory()->create([
            'lesson_id' => null,
            'status' => 'active',
            'all_schools_mode' => 'direct',
            'type' => 'quiz',
            'questions' => [
                ['text' => 'س1', 'type' => 'multiple_choice', 'correct_index' => 1,
                 'options' => [['text' => 'أ', 'is_correct' => false], ['text' => 'ب', 'is_correct' => true]]],
            ],
        ]);

        Sanctum::actingAs($student);
        $res = $this->getJson("/api/v1/student/activities/{$activity->id}")->assertOk();
        $questionsJson = json_encode($res->json('data.questions'), JSON_UNESCAPED_UNICODE);

        $this->assertStringNotContainsString('correct_index', $questionsJson);
        $this->assertStringNotContainsString('is_correct', $questionsJson);
    }

    public function test_conversation_sanitizes_stored_xss_before_broadcast(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $admin = User::factory()->create(['role' => 'super_admin']);

        // رسالة خبيثة مخزَّنة (كما يخزّنها send: بلا تعقيم XSS)
        $conversation = Conversation::findOrCreate($student->id, $admin->id);
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $student->id,
            'receiver_id' => $admin->id,
            'message' => '<img src=x onerror="alert(document.cookie)">مرحبا',
        ]);

        // السوبر أدمن يفتح المحادثة → getConversation يجب أن يُعقّم قبل البثّ
        $res = $this->actingAs($admin)
            ->getJson(route('messages.conversation', $student->id))
            ->assertOk();

        $body = json_encode($res->json('messages'), JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('onerror', $body, 'معالِج الحدث الخبيث أُزيل');
        $this->assertStringContainsString('مرحبا', $body, 'النصّ الآمن يبقى');
    }
}
