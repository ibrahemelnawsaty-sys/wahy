<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * «الوسائط المتعددة» عند تعريف النشاط لدى الأدمن — كانت غائبة عن نموذج الأدمن (تظهر للمعلّم فقط).
 * تُخزَّن في عمود media وتظهر للطالب عبر activities/partials/media.
 */
class AdminActivityMediaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_admin_can_attach_media_when_creating_activity(): void
    {
        Storage::fake('public');
        $lesson = Lesson::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.activities.store'), [
            'lesson_id' => $lesson->id,
            'title' => 'نشاط بوسائط',
            'type' => 'project',
            'points' => 10,
            'status' => 'active',
            'image' => [UploadedFile::fake()->image('pic.jpg')],
            'document' => [UploadedFile::fake()->create('ملف.pdf', 40, 'application/pdf')],
        ])->assertRedirect();

        $activity = Activity::where('title', 'نشاط بوسائط')->first();
        $this->assertNotNull($activity);
        $this->assertIsArray($activity->media);
        $this->assertCount(2, $activity->media, 'صورة + مستند مُخزَّنان');

        $types = collect($activity->media)->pluck('type')->all();
        $this->assertContains('image', $types);
        $this->assertContains('document', $types);

        foreach ($activity->media as $m) {
            Storage::disk('public')->assertExists($m['path']);
        }
    }

    public function test_admin_edit_can_add_and_remove_media(): void
    {
        Storage::fake('public');
        $lesson = Lesson::factory()->create();
        $activity = Activity::factory()->create([
            'lesson_id' => $lesson->id,
            'media' => [
                ['type' => 'image', 'path' => 'activity-media/old.jpg', 'name' => 'old.jpg'],
                ['type' => 'document', 'path' => 'activity-media/keep.pdf', 'name' => 'keep.pdf'],
            ],
        ]);

        $this->actingAs($this->admin())->post(route('admin.activities.update', $activity), [
            '_method' => 'PUT',
            'lesson_id' => $lesson->id,
            'title' => $activity->title,
            'type' => $activity->type,
            'points' => 10,
            'status' => 'active',
            'remove_media' => [0], // احذف القديمة الأولى
            'video' => [UploadedFile::fake()->create('v.mp4', 100, 'video/mp4')],
        ])->assertRedirect();

        $activity->refresh();
        $paths = collect($activity->media)->pluck('path')->all();
        $this->assertNotContains('activity-media/old.jpg', $paths, 'المحذوفة أُزيلت');
        $this->assertContains('activity-media/keep.pdf', $paths, 'المُبقاة باقية');
        $this->assertTrue(collect($activity->media)->contains(fn ($m) => ($m['type'] ?? '') === 'video'), 'الجديدة أُضيفت');
    }

    public function test_media_display_url_uses_public_disk_convention(): void
    {
        // القرص العامّ جذره storage/app/public/data — يجب أن يتضمّن الرابط هذا المسار (لا storage/ فقط)
        $url = Storage::disk('public')->url('activity-media/x.mp4');
        $this->assertStringContainsString('storage/app/public/data/activity-media/x.mp4', $url);
    }

    /**
     * جوهر شكوى المالك: فيديو مرفوع «لا يظهر». نُصيّر قالب العرض الفعليّ (المُضمَّن في
     * صفحة الطالب/المراجعة/المعاينة) ونتأكّد أنّه يُخرِج عنصر <video> برابط القرص الصحيح.
     */
    public function test_media_partial_renders_video_with_correct_url(): void
    {
        $activity = Activity::factory()->make([
            'media' => [
                ['type' => 'video', 'path' => 'activity-media/lesson.mp4', 'name' => 'درس.mp4'],
            ],
        ]);

        $html = view('activities.partials.media', ['activity' => $activity])->render();

        $this->assertStringContainsString('<video', $html, 'يجب أن يُعرَض عنصر الفيديو');
        $this->assertStringContainsString('storage/app/public/data/activity-media/lesson.mp4', $html, 'رابط الفيديو بمسار القرص العامّ الصحيح');
    }
}
