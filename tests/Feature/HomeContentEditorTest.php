<?php

namespace Tests\Feature;

use App\Models\LandingContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * محرّر محتوى الصفحة الرئيسية: عرض خادميّ عبر lc()، حفظ في landing_content،
 * والعودة للافتراضيّ عند الفراغ/المطابقة. محميّ can:access-admin.
 */
class HomeContentEditorTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_guest_cannot_open_editor(): void
    {
        $this->get(route('admin.home-content.edit'))->assertRedirect();
    }

    public function test_admin_sees_editor_with_registered_sections(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.home-content.edit'))
            ->assertOk()
            ->assertSee('الواجهة (الهيرو)')
            ->assertSee('قسم المزايا')
            ->assertSee('العنوان الرئيسيّ');
    }

    public function test_admin_saves_content_and_it_persists(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.home-content.update'), [
            'hero_title' => 'عنوان مخصّص للاختبار',
            'contact_title' => 'راسلنا الآن',
        ])->assertRedirect(route('admin.home-content.edit'));

        $this->assertDatabaseHas('landing_content', ['key' => 'hero_title', 'value' => 'عنوان مخصّص للاختبار']);
        $this->assertDatabaseHas('landing_content', ['key' => 'contact_title', 'value' => 'راسلنا الآن']);

        // يعود مملوءاً في نموذج التحرير
        $this->actingAs($admin)->get(route('admin.home-content.edit'))
            ->assertSee('عنوان مخصّص للاختبار');
    }

    public function test_lc_helper_returns_saved_then_default(): void
    {
        // لا شيء محفوظ ⟶ الافتراضيّ
        $this->assertSame('افتراضيّ', lc('some_key_x', 'افتراضيّ'));

        LandingContent::setValue('some_key_x', 'محفوظ', ['section' => 'home']);
        lc_forget();

        // بعد المسح، استعلام جديد ⟶ القيمة المحفوظة
        $this->assertSame('محفوظ', lc('some_key_x', 'افتراضيّ'));
    }

    public function test_saving_default_or_empty_removes_override(): void
    {
        $admin = $this->admin();

        LandingContent::setValue('hero_title', 'قيمة قديمة', ['section' => 'home']);
        $this->assertDatabaseHas('landing_content', ['key' => 'hero_title']);

        $default = config('landing_editable.hero.fields.hero_title.default');

        // حفظ نصّ مطابق للافتراضيّ ⟶ يُحذف التخصيص
        $this->actingAs($admin)->post(route('admin.home-content.update'), [
            'hero_title' => $default,
        ])->assertRedirect();
        $this->assertDatabaseMissing('landing_content', ['key' => 'hero_title']);

        // حفظ فراغ ⟶ يبقى محذوفاً
        LandingContent::setValue('contact_title', 'شيء', ['section' => 'home']);
        $this->actingAs($admin)->post(route('admin.home-content.update'), [
            'contact_title' => '   ',
        ])->assertRedirect();
        $this->assertDatabaseMissing('landing_content', ['key' => 'contact_title']);
    }

    public function test_landing_page_renders_defaults_and_reflects_saved_overrides(): void
    {
        // الصفحة تُصيَّر وتُظهر الافتراضيّات الخادميّة (header/hero/values/teams/cta/footer)
        $this->get('/')
            ->assertOk()
            ->assertSee('كيف نبني القيم؟')            // values (منهجية)
            ->assertSee('التعلم التعاوني مع الفرق')   // teams
            ->assertSee('جاهز للانضمام؟')             // cta
            ->assertSee('روابط سريعة');                // footer

        // حفظ تخصيص لعدّة أقسام ⟶ يظهر خادميّاً فوراً (بلا سكربت متصفّح)
        LandingContent::setValue('cta_title', 'انضم إلينا اليوم', ['section' => 'home']);
        LandingContent::setValue('nav_link_1', 'الصفحة الرئيسية', ['section' => 'home']);
        LandingContent::setValue('footer_quick_title', 'روابط مفيدة', ['section' => 'home']);
        lc_forget();

        $this->get('/')
            ->assertOk()
            ->assertSee('انضم إلينا اليوم')
            ->assertSee('الصفحة الرئيسية')
            ->assertSee('روابط مفيدة')
            ->assertDontSee('جاهز للانضمام؟'); // الافتراضيّ استُبدل
    }

    public function test_update_only_touches_submitted_fields(): void
    {
        $admin = $this->admin();

        LandingContent::setValue('values_title', 'قيمة محفوظة', ['section' => 'home']);

        // إرسال hero_title فقط لا يمسّ values_title
        $this->actingAs($admin)->post(route('admin.home-content.update'), [
            'hero_title' => 'جديد',
        ])->assertRedirect();

        $this->assertDatabaseHas('landing_content', ['key' => 'values_title', 'value' => 'قيمة محفوظة']);
    }
}
