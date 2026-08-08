<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 0: أساس الإعدادات — أعلام رؤية الأقسام + مفاتيح الفيديو/الواتساب.
 * يشمل الخاصّيّة الحرجة: حفظ النموذج لا يُصفّر علماً مُفعَّلاً، والغائب = مطفأ.
 */
class HomeSettingsFlagsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_defaults_before_any_save(): void
    {
        $this->assertFalse((bool) setting('show_hero_stats', false));
        $this->assertTrue((bool) setting('show_coop_benefits', true));
        $this->assertFalse((bool) setting('show_partners', false));
        $this->assertFalse((bool) setting('hero_video_enabled', false));
    }

    public function test_index_shows_new_cards(): void
    {
        $this->actingAs($this->admin())->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('ظهور أقسام الصفحة الرئيسية')
            ->assertSee('فيديو الهيرو والتواصل السريع');
    }

    public function test_saving_persists_flags_and_keys_and_does_not_wipe_checked(): void
    {
        // نُرسل: coop مُفعَّل، الباقي غائب (=مطفأ)، + مفاتيح الفيديو/الواتساب.
        $this->actingAs($this->admin())->post(route('admin.settings.update'), [
            'show_coop_benefits' => '1',
            'hero_video_enabled' => '1',
            'hero_video_url' => 'videos/hero-main.mp4',
            'whatsapp_number' => '966501234567',
        ])->assertRedirect();

        \App\Models\Setting::clearCache();

        $this->assertTrue((bool) setting('show_coop_benefits'));   // مُفعَّل بقي مُفعَّلاً
        $this->assertFalse((bool) setting('show_hero_stats'));      // غائب → مطفأ
        $this->assertFalse((bool) setting('show_partners'));        // غائب → مطفأ
        $this->assertTrue((bool) setting('hero_video_enabled'));
        $this->assertSame('videos/hero-main.mp4', setting('hero_video_url'));
        $this->assertSame('966501234567', setting('whatsapp_number'));
    }

    public function test_whatsapp_number_rejects_letters(): void
    {
        $this->actingAs($this->admin())->post(route('admin.settings.update'), [
            'whatsapp_number' => 'abc-not-a-number',
        ])->assertSessionHasErrors('whatsapp_number');
    }

    public function test_non_admin_cannot_open_settings(): void
    {
        $this->actingAs(User::factory()->student()->create())
            ->get(route('admin.settings'))->assertForbidden();
    }
}
