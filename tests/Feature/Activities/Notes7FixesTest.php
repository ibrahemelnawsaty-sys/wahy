<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * تحقّق مراجعة «الملاحظات 7» الخصميّة:
 *  #5) تطبيق الجوّال يستلم وسائط النشاط (كان API يُخرِج 'attachments' من خاصّية غير موجودة → null).
 *  #7) صفحة اعتماد الأدمن تعرض الأسئلة بالعارض الغنيّ (تمييز الإجابة الصحيحة/المتوقعة) كالمعلّم.
 */
class Notes7FixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_activity_details_includes_media_with_urls(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = Activity::factory()->create([
            'lesson_id' => null,            // بلا قيمة → يتجاوز بوّابة القيمة
            'status' => 'active',
            'all_schools_mode' => 'direct', // متاح مباشرةً
            'media' => [
                ['type' => 'video', 'path' => 'activity-media/lesson.mp4', 'name' => 'درس.mp4'],
                ['type' => 'image', 'path' => 'activity-media/pic.jpg', 'name' => 'صورة'],
            ],
        ]);

        Sanctum::actingAs($student);
        $res = $this->getJson("/api/v1/student/activities/{$activity->id}")->assertOk();

        $media = $res->json('data.media');
        $this->assertCount(2, $media, 'الوسائط تُسلسَل للجوّال');
        $this->assertSame('video', $media[0]['type']);
        $this->assertStringContainsString('storage/app/public/data/activity-media/lesson.mp4', $media[0]['url']);
        $this->assertStringContainsString('storage/app/public/data/activity-media/pic.jpg', $media[1]['url']);
    }

    public function test_admin_approval_show_uses_rich_questions_partial(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $teacher = User::factory()->teacher(School::factory()->create())->create();
        $activity = Activity::factory()->create([
            'created_by' => $teacher->id,
            'type' => 'quiz',
            'approval_status' => 'pending',
            'school_approval_status' => 'approved',
            'questions' => [
                ['type' => 'short_answer', 'text' => 'ما عاصمة السعودية؟', 'correct_answer' => 'الرياض'],
            ],
        ]);

        $html = $this->actingAs($admin)
            ->get(route('admin.activity-approval.show', $activity))
            ->assertOk()
            ->getContent();

        // نصّ لا يُنتجه إلا العارض الغنيّ الموحّد (كان العارض الداخليّ لا يعرض الإجابة المتوقعة)
        $this->assertStringContainsString('الإجابة الصحيحة المتوقعة', $html);
        $this->assertStringContainsString('الرياض', $html);
    }
}
