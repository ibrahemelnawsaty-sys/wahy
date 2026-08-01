<?php

namespace Tests\Feature;

use App\Models\PageBuilder;
use App\Models\User;
use App\Support\PageContentScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * تحصين رندرة صفحات PageBuilder العامّة (pages/show) ضدّ XSS المخزَّن:
 * حقن اسم الوسم (heading level / list type)، ومخطّطات الروابط javascript:، وiframe غير موثوق.
 * تُصيَّر لزوّار غير مصادَقين على /pages/{slug} — فالتحصين حرِج.
 */
class PageBuilderXssTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_url_neutralizes_dangerous_schemes(): void
    {
        $this->assertSame('#', safe_url('javascript:alert(1)'));
        $this->assertSame('#', safe_url("jav\tascript:alert(1)"));
        $this->assertSame('#', safe_url('data:text/html,x'));
        $this->assertSame('#', safe_url('JavaScript:alert(1)'));
        $this->assertSame('https://youtube.com/e/x', safe_url('https://youtube.com/e/x'));
        $this->assertSame('/relative', safe_url('/relative'));
        $this->assertSame('mailto:a@b.com', safe_url('mailto:a@b.com'));
    }

    public function test_public_page_render_neutralizes_injected_blocks(): void
    {
        $page = PageBuilder::create([
            'page_name' => 'xss-probe',
            'slug' => 'xss-probe',
            'json_data' => [
                'sections' => [[
                    'columns' => 1,
                    'grid' => [[
                        ['type' => 'heading', 'content' => ['level' => 'img src=x onerror=alert(1)', 'text' => 'مرحبا']],
                        ['type' => 'list', 'content' => ['type' => 'script', 'items' => ['أ', 'ب']]],
                        ['type' => 'button', 'content' => ['link' => 'javascript:alert(document.cookie)', 'text' => 'زر']],
                        ['type' => 'link', 'content' => ['url' => 'javascript:alert(1)', 'text' => 'رابط']],
                        ['type' => 'video', 'content' => ['url' => 'javascript:alert(1)//youtube.com']],
                    ]],
                ]],
            ],
            'is_active' => true,
        ]);

        $res = $this->get('/pages/' . $page->slug);
        $res->assertOk();

        // اسم الوسم المحقون لم يُصيَّر (whitelist): لا <img onerror ولا <script class=component-list
        $res->assertDontSee('src=x onerror', false);
        $res->assertDontSee('<script class="component-list"', false);
        // مخطّط javascript: حُيِّد إلى #
        $res->assertDontSee('javascript:alert', false);
        // العنوان صار h2 آمناً، والقائمة ul، والروابط #
        $res->assertSee('component-heading', false);
        $res->assertSee('href="#"', false);
        // iframe يوتيوب المزيّف لم يُصيَّر (ليس https حقيقيّاً)
        $res->assertDontSee('<iframe', false);
    }

    public function test_scanner_flags_payloads_and_ignores_clean_prose(): void
    {
        $bad = ['grid' => [[
            ['content' => ['level' => 'section']],
            ['content' => ['link' => 'javascript:alert(1)']],
            ['content' => ['code' => '<script>alert(1)</script>']],
            ['content' => ['x' => 'a onmouseover=alert(1) b']],
        ]]];
        $kinds = array_column(PageContentScanner::scan($bad), 'kind');
        $this->assertContains('tag-name-injection', $kinds);
        $this->assertContains('dangerous-scheme', $kinds);
        $this->assertContains('dangerous-tag', $kinds);
        $this->assertContains('event-handler', $kinds);

        // نثرٌ يذكر «javascript» دون أن يكون مخطّطاً + رابط شرعيّ + وسم آمن → لا مخالفة
        $clean = ['content' => [
            'level' => 'h3',
            'text' => 'تعلّم javascript في هذا الدرس التفاعليّ',
            'link' => 'https://example.com/page',
        ]];
        $this->assertSame([], PageContentScanner::scan($clean));
    }

    public function test_page_builder_store_rejects_unsafe_content(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $payload = [
            'page_name' => 'صفحة خبيثة',
            'slug' => 'evil-page',
            'json_data' => json_encode(['sections' => [['grid' => [[
                ['type' => 'button', 'content' => ['link' => 'javascript:alert(document.cookie)', 'text' => 'x']],
            ]]]]]),
        ];

        $this->actingAs($admin)->post(route('admin.pages.store'), $payload)
            ->assertRedirect(); // يعود بالخطأ لا ينشئ

        $this->assertDatabaseMissing('page_builder', ['slug' => 'evil-page']);
    }

    public function test_audit_command_reports_contaminated_rows(): void
    {
        PageBuilder::create([
            'page_name' => 'clean', 'slug' => 'clean',
            'json_data' => ['sections' => [['grid' => [[['type' => 'heading', 'content' => ['level' => 'h2', 'text' => 'ok']]]]]]],
            'is_active' => true,
        ]);
        // صفٌّ ملوَّث نتجاوز به طبقة الحفظ (كتابة مباشرة تحاكي بيانات مزروعة قبل التحصين)
        PageBuilder::create([
            'page_name' => 'dirty', 'slug' => 'dirty',
            'json_data' => ['sections' => [['grid' => [[['type' => 'button', 'content' => ['link' => 'javascript:alert(1)']]]]]]],
            'is_active' => true,
        ]);

        $this->artisan('pages:audit-xss')
            ->assertExitCode(1) // FAILURE عند وجود تلوّث
            ->expectsOutputToContain('الصفوف الملوَّثة');
    }
}
