<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\Lesson;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * أنواع الملفات المسموحة (allowed_file_types) — عطلان:
 *  1) تشفير مزدوج (json_encode يدويّ + صبّ array) فتُقرأ نصًّا لا مصفوفة → accept=".pdf".
 *  2) العمود يخزّن فئات (image/video/…) لا امتدادات → mimes:image يرفض كلَّ ملفّ.
 * الإصلاح: حذف json_encode + دوالّ موديل تُطبّع الفئات وتبني accept/mimes الصحيحَين
 * (مع شفاء ذاتيّ للصفوف القديمة المُشفَّرة مرّتين).
 */
class ActivityAllowedFileTypesTest extends TestCase
{
    use RefreshDatabase;

    // ---- دوالّ الموديل (وحدات) ----

    public function test_categories_normalized_from_clean_array(): void
    {
        $a = Activity::factory()->make(['allowed_file_types' => ['image', 'video']]);
        $this->assertSame(['image', 'video'], $a->allowedFileCategories());
    }

    public function test_categories_self_heal_from_double_encoded_string(): void
    {
        // نحاكي الصفّ القديم: العمود يحوي سلسلة JSON (بعد أن يفكّ الصبّ طبقةً واحدة).
        $a = Activity::factory()->make();
        $a->setRawAttributes(['allowed_file_types' => json_encode(json_encode(['image', 'video']))]);
        $this->assertSame(['image', 'video'], $a->allowedFileCategories(), 'يشفي التشفير المزدوج');
    }

    public function test_categories_default_when_empty(): void
    {
        $a = Activity::factory()->make(['allowed_file_types' => null]);
        $this->assertSame(['image', 'video', 'audio', 'document'], $a->allowedFileCategories());
    }

    public function test_extensions_map_from_categories(): void
    {
        $a = Activity::factory()->make(['allowed_file_types' => ['image', 'video']]);
        $exts = $a->allowedFileExtensions();
        $this->assertContains('jpg', $exts);
        $this->assertContains('png', $exts);
        $this->assertContains('mp4', $exts);
        $this->assertNotContains('pdf', $exts, 'المستندات غير مسموحة هنا');
        $this->assertNotContains('image', $exts, 'لا تُمرَّر الفئة كامتداد');
    }

    public function test_accept_uses_mime_groups_and_not_dot_pdf(): void
    {
        $a = Activity::factory()->make(['allowed_file_types' => ['image', 'video']]);
        $accept = $a->allowedFileAccept();
        $this->assertStringContainsString('image/*', $accept);
        $this->assertStringContainsString('video/*', $accept);
        $this->assertStringNotContainsString('.pdf', $accept, 'لا يرتدّ إلى pdf');
        $this->assertStringNotContainsString('.image', $accept, 'لا يبني ".image" العبثيّ');
    }

    public function test_label_is_arabic(): void
    {
        $a = Activity::factory()->make(['allowed_file_types' => ['image', 'video']]);
        $this->assertSame('صور، فيديو', $a->allowedFileTypesLabel());
    }

    // ---- رحلة الحفظ (تشفير مفرد لا مزدوج) ----

    public function test_admin_store_saves_clean_array_not_double_encoded(): void
    {
        $lesson = Lesson::factory()->create();
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->post(route('admin.activities.store'), [
            'lesson_id' => $lesson->id,
            'title' => 'مشروع صور وفيديو',
            'type' => 'project',
            'points' => 10,
            'status' => 'active',
            'allowed_file_types' => ['image', 'video'],
            'max_file_size' => 20,
        ])->assertRedirect();

        $activity = Activity::where('title', 'مشروع صور وفيديو')->firstOrFail();

        // القراءة يجب أن تكون مصفوفة نظيفة (لا نصّ JSON مزدوج التشفير)
        $this->assertIsArray($activity->allowed_file_types);
        $this->assertSame(['image', 'video'], $activity->allowed_file_types);
        // ومن ثمّ accept صحيح (جوهر شكوى المالك: لم يعد يرتدّ إلى .pdf)
        $this->assertStringContainsString('image/*', $activity->allowedFileAccept());
        $this->assertStringContainsString('video/*', $activity->allowedFileAccept());
    }

    // ---- تحقّق التسليم (mimes صحيح) ----

    private function uploadScenario(array $allowed): array
    {
        Storage::fake('public');
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = Activity::factory()->create([
            'type' => 'project',
            'status' => 'active',
            'all_schools_mode' => 'direct',
            'lesson_id' => null,
            'points' => 10,
            'allowed_file_types' => $allowed,
            'max_file_size' => 20,
        ]);

        return [$student, $activity];
    }

    public function test_student_can_upload_image_when_images_allowed(): void
    {
        [$student, $activity] = $this->uploadScenario(['image', 'video']);

        $this->actingAs($student)->post(route('student.activity.submit', $activity->id), [
            'answer' => 'تسليمي',
            'answer_file' => UploadedFile::fake()->image('work.jpg'),
        ])->assertSessionHasNoErrors();
    }

    public function test_student_cannot_upload_pdf_when_only_media_allowed(): void
    {
        [$student, $activity] = $this->uploadScenario(['image', 'video']);

        $this->actingAs($student)->post(route('student.activity.submit', $activity->id), [
            'answer' => 'تسليمي',
            'answer_file' => UploadedFile::fake()->create('doc.pdf', 40, 'application/pdf'),
        ])->assertSessionHasErrors('answer_file');
    }

    // ---- D: توسيع الامتدادات ليطابق مجموعات accept على الجوّال ----

    public function test_extensions_cover_common_mobile_formats(): void
    {
        $img = Activity::factory()->make(['allowed_file_types' => ['image']]);
        $this->assertContains('heic', $img->allowedFileExtensions(), 'صور آيفون heic');
        $aud = Activity::factory()->make(['allowed_file_types' => ['audio']]);
        $this->assertContains('opus', $aud->allowedFileExtensions(), 'رسائل صوتية opus');
    }

    // ---- A: مسار الجوّال (API) صار يفرض قيد النوع (كان ثغرة رفع خبيث) ----

    private function apiActivity(array $allowed): Activity
    {
        return Activity::factory()->create([
            'manual_review' => true,
            'all_schools_mode' => 'direct',
            'status' => 'active',
            'lesson_id' => null,
            'allowed_file_types' => $allowed,
            'max_file_size' => 20,
        ]);
    }

    public function test_api_rejects_disallowed_or_malicious_file(): void
    {
        Storage::fake('public');
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->apiActivity(['image']);

        \Laravel\Sanctum\Sanctum::actingAs($student);
        // ملفّ html خبيث (كان يُقبَل سابقاً بلا فحص نوع = XSS مخزَّن)
        $this->post("/api/v1/student/activities/{$activity->id}/submit", [
            'answers' => ['x'],
            'file' => UploadedFile::fake()->create('evil.html', 5, 'text/html'),
        ], ['Accept' => 'application/json'])->assertStatus(422);
    }

    public function test_api_accepts_allowed_file(): void
    {
        Storage::fake('public');
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->apiActivity(['image']);

        \Laravel\Sanctum\Sanctum::actingAs($student);
        $this->post("/api/v1/student/activities/{$activity->id}/submit", [
            'answers' => ['x'],
            'file' => UploadedFile::fake()->image('photo.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();
    }

    // ---- E: نوع upload يفرض الملفّ خادمياً (بدل required على input مخفيّ) ----

    public function test_upload_type_requires_a_file_server_side(): void
    {
        Storage::fake('public');
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = Activity::factory()->create([
            'type' => 'upload',
            'status' => 'active',
            'all_schools_mode' => 'direct',
            'lesson_id' => null,
            'allowed_file_types' => ['document'],
            'max_file_size' => 20,
        ]);

        // بلا ملفّ → خطأ على answer_file
        $this->actingAs($student)->post(route('student.activity.submit', $activity->id), [
            'answer' => 'ملاحظة',
        ])->assertSessionHasErrors('answer_file');

        // بملفّ صالح → بلا أخطاء
        $this->actingAs($student)->post(route('student.activity.submit', $activity->id), [
            'answer' => 'ملاحظة',
            'answer_file' => UploadedFile::fake()->create('report.pdf', 30, 'application/pdf'),
        ])->assertSessionHasNoErrors();
    }

    // ---- F: إلغاء تأشير كل الأنواع يُلغي القيد (لا عملية لاغية صامتة) ----

    public function test_admin_unchecking_all_types_clears_restriction(): void
    {
        $lesson = Lesson::factory()->create();
        $admin = User::factory()->create(['role' => 'super_admin']);
        $activity = Activity::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'project',
            'allowed_file_types' => ['image', 'video'],
        ]);

        // تحديث بلا إرسال allowed_file_types (كإلغاء تأشير الكلّ)
        $this->actingAs($admin)->post(route('admin.activities.update', $activity), [
            '_method' => 'PUT',
            'lesson_id' => $lesson->id,
            'title' => $activity->title,
            'type' => 'project',
            'points' => 10,
            'status' => 'active',
        ])->assertRedirect();

        $this->assertSame([], $activity->fresh()->allowed_file_types, 'القيد أُلغي فِعلاً (لا بقاء صامت للقديم)');
    }
}
