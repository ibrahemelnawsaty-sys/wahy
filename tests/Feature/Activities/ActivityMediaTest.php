<?php

namespace Tests\Feature\Activities;

use App\Http\Controllers\Concerns\HandlesActivityMedia;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * عرض «الوسائط المتعددة» (فيديو خصوصاً) للأنشطة:
 *  - رفع الفيديو يُخزَّن في عمود media بالبنية الصحيحة.
 *  - رفعٌ سقط بسبب الحجم (upload_max_filesize) يرفع خطأً واضحاً بدل تجاهله بصمت.
 *  - عارض الوسائط المشترك يرسم وسم <video> تحت وصف النشاط بالرابط الصحيح.
 */
class ActivityMediaTest extends TestCase
{
    use RefreshDatabase;

    /** مُغلِّف صغير يكشف الدوالّ المحميّة للسمة المشتركة للاختبار. */
    private function mediaHelper(): object
    {
        return new class
        {
            use HandlesActivityMedia;

            public function collect(Request $r): array
            {
                return $this->collectUploadedActivityMedia($r);
            }
        };
    }

    public function test_uploaded_video_is_stored_in_media_column_shape(): void
    {
        Storage::fake('public');
        $request = Request::create('/x', 'POST', [], [], [
            'video' => [UploadedFile::fake()->create('lesson.mp4', 2048, 'video/mp4')],
        ]);

        $media = $this->mediaHelper()->collect($request);

        $this->assertCount(1, $media);
        $this->assertSame('video', $media[0]['type']);
        $this->assertSame('lesson.mp4', $media[0]['name']);
        $this->assertStringStartsWith('activity-media/', $media[0]['path']);
        Storage::disk('public')->assertExists($media[0]['path']);
    }

    public function test_oversized_upload_surfaces_clear_error_not_silent_drop(): void
    {
        // ملفٌّ بخطأ رفعٍ متعلّق بالحجم (كما يحدث حين يتجاوز upload_max_filesize) → hasFile=false.
        $errored = new UploadedFile(
            __FILE__, 'huge.mp4', 'video/mp4', UPLOAD_ERR_INI_SIZE, true
        );
        $request = Request::create('/x', 'POST', [], [], ['video' => [$errored]]);

        $this->expectException(ValidationException::class);
        $this->mediaHelper()->collect($request);
    }

    public function test_media_partial_renders_video_under_description(): void
    {
        $activity = Activity::factory()->create([
            'description' => 'وصف النشاط',
            'media' => [['type' => 'video', 'path' => 'activity-media/clip.mp4', 'name' => 'clip.mp4']],
        ]);

        $html = (string) $this->view('activities.partials.media', ['activity' => $activity]);

        $this->assertStringContainsString('<video', $html);
        // الرابط يتبع اصطلاح القرص العامّ غير القياسيّ (storage/app/public/data) لا asset('storage/…')
        $this->assertStringContainsString('storage/data/activity-media/clip.mp4', $html);
    }

    public function test_media_partial_handles_string_encoded_media(): void
    {
        // صفٌّ قديم خُزِّن فيه media نصّاً (ازدواج تشفير) — يجب ألّا يختفي.
        $activity = Activity::factory()->create(['media' => null]);
        $activity->setRawAttributes(array_merge($activity->getAttributes(), [
            'media' => json_encode([['type' => 'video', 'path' => 'activity-media/x.mp4', 'name' => 'x.mp4']]),
        ]));

        $html = (string) $this->view('activities.partials.media', ['activity' => $activity]);
        $this->assertStringContainsString('<video', $html);
    }
}
