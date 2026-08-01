<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\Models\MediaAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * مكتبة الوسائط (ت-٨): رفع صورة نقطيّة، رفض SVG/غير الصور، حذف، حارس الدور.
 */
class PageBuilderV2MediaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_admin_uploads_raster_image(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin())
            ->postJson(route('admin.pb.media.store'), [
                'file' => UploadedFile::fake()->image('photo.png', 400, 300),
                'alt' => 'شعار المنصّة',
            ])->assertCreated();

        $this->assertDatabaseCount('pb_media', 1);
        $asset = MediaAsset::first();
        $this->assertSame('شعار المنصّة', $asset->alt);
        Storage::disk('public')->assertExists($asset->path);
    }

    public function test_svg_and_non_image_are_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        // SVG (ناقل XSS) مرفوض
        $this->actingAs($admin)->postJson(route('admin.pb.media.store'), [
            'file' => UploadedFile::fake()->create('x.svg', 10, 'image/svg+xml'),
            'alt' => 'x',
        ])->assertStatus(422);

        // غير صورة مرفوض
        $this->actingAs($admin)->postJson(route('admin.pb.media.store'), [
            'file' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
            'alt' => 'x',
        ])->assertStatus(422);

        $this->assertDatabaseCount('pb_media', 0);
    }

    public function test_alt_is_required(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin())->postJson(route('admin.pb.media.store'), [
            'file' => UploadedFile::fake()->image('a.jpg'),
        ])->assertStatus(422);
    }

    public function test_destroy_removes_file_and_row(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin)->postJson(route('admin.pb.media.store'), [
            'file' => UploadedFile::fake()->image('a.jpg'),
            'alt' => 'a',
        ])->assertCreated();
        $asset = MediaAsset::first();

        $this->actingAs($admin)->deleteJson(route('admin.pb.media.destroy', $asset))->assertOk();
        $this->assertDatabaseCount('pb_media', 0);
        Storage::disk('public')->assertMissing($asset->path);
    }

    public function test_non_admin_cannot_upload(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->student()->create())
            ->postJson(route('admin.pb.media.store'), [
                'file' => UploadedFile::fake()->image('a.jpg'),
                'alt' => 'a',
            ])->assertForbidden();
    }
}
