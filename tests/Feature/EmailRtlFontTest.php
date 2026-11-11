<?php

namespace Tests\Feature;

use App\Mail\LevelUpMail;
use App\Mail\ResetPasswordMail;
use App\Mail\TwoFactorCodeMail;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * قوالب البريد عربيّة: RTL مضمَّن (يصمد حين يُسقط العميل <style> و<html dir>) + خطّ الموقع نفسه (font_family).
 */
class EmailRtlFontTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_email_template_is_rtl_with_site_font(): void
    {
        $dir = resource_path('views/emails');
        $checked = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $f) {
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $f->getPathname());
            // صفحة ويب لا بريد، والنصّيّة بلا HTML، والـlayouts/partials هي المصدر
            if (! str_ends_with($path, '.blade.php') || str_contains($path, '/layouts/') || str_contains($path, '/partials/')
                || str_ends_with($path, 'preferences.blade.php') || str_ends_with($path, '-text.blade.php')) {
                continue;
            }
            $src = file_get_contents($path);
            $viaLayout = (bool) preg_match("/@extends\('emails\.layouts\.(base|master)'\)/", $src);
            $standalone = str_contains($src, "emails.partials.rtl-head") && str_contains($src, "emails.partials.rtl-open");
            $this->assertTrue($viaLayout || $standalone, "$path ليس RTL/بخطّ الموقع");
            $this->assertStringNotContainsString("'Segoe UI', Tahoma, Geneva", $src, "$path يثبّت خطّاً غير خطّ الموقع");
            $checked++;
        }
        $this->assertGreaterThan(25, $checked);
    }

    public function test_layout_mail_is_rtl_inline_and_uses_the_configured_font(): void
    {
        Setting::set('font_family', 'Cairo', 'string');
        $u = User::factory()->create(['role' => 'student']);

        $html = (new LevelUpMail($u, 5))->render();

        $this->assertStringContainsString('dir="rtl"', $html);
        $this->assertStringContainsString('family=Cairo', $html);                       // رابط الخطّ
        $this->assertStringContainsString("font-family:'Cairo','IBM Plex Sans Arabic'", $html); // inline على الغلاف
        $this->assertMatchesRegularExpression('/<td class="bd" dir="rtl" style="direction:rtl;text-align:right;/', $html);
    }

    public function test_standalone_mails_are_rtl_inline_and_use_site_font_by_default(): void
    {
        foreach ([new TwoFactorCodeMail('123456', 'علي'), new ResetPasswordMail('tok', 'a@b.com')] as $mail) {
            $html = $mail->render();
            $this->assertStringContainsString('<body dir="rtl">', $html);
            $this->assertStringContainsString('<div dir="rtl" style="direction:rtl;text-align:right;font-family:\'IBM Plex Sans Arabic\'', $html);
            $this->assertStringContainsString('family=IBM+Plex+Sans+Arabic', $html);
            $this->assertStringNotContainsString("'Segoe UI', Tahoma, Geneva", $html);
        }
    }

    public function test_font_name_is_sanitized_before_inline_injection(): void
    {
        Setting::set('font_family', "Evil';}</style><script>alert(1)</script>", 'string');

        $this->assertStringNotContainsString('<', email_font_stack());
        $this->assertStringNotContainsString(';', email_font_family());
    }
}
