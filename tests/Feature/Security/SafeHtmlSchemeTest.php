<?php

namespace Tests\Feature\Security;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * تحصين safe_html ضدّ المخطّطات الخطرة (javascript:/vbscript:/data:) مهما كان الاقتباس
 * أو التشويش — وجده تحقّق #22 الخصميّ: href غير المُقتبَس والمُشوَّش بالتاب/الكيانات كان ينجو.
 */
class SafeHtmlSchemeTest extends TestCase
{
    /** يُطبّع كما يفعل محلّل الروابط في المتصفح ليتأكّد ألّا مخطط خطر تنفيذيّ باقٍ. */
    private function assertNoDangerousScheme(string $out, string $case): void
    {
        // فكّ الكيانات + إزالة أحرف التحكّم/المسافات كما يفعل المتصفح، ثمّ ابحث عن مخطط تنفيذيّ
        $probe = html_entity_decode($out, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $probe = html_entity_decode($probe, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $probe = preg_replace('/[\x00-\x20]|\xC2\xA0/', '', $probe);
        $probe = strtolower((string) $probe);
        $this->assertDoesNotMatchRegularExpression(
            '#(href|src|action|formaction|srcdoc|xlink:href|background|poster)=[\'"]?(javascript|vbscript|data|about):#',
            $probe,
            "بقي مخطط خطر في: {$case} → {$out}",
        );
    }

    public static function attackVectors(): array
    {
        return [
            'مُقتبَس (النمط القديم)' => ['<a href="javascript:alert(1)">x</a>'],
            'بلا اقتباس' => ['<a href=javascript:alert(1)>x</a>'],
            'تاب كيان سداسي' => ['<a href="jav&#x09;ascript:alert(1)">x</a>'],
            'تاب كيان عشري' => ['<a href="jav&#9;ascript:alert(1)">x</a>'],
            'تاب حرفيّ' => ["<a href=\"jav\tascript:alert(1)\">x</a>"],
            'سطر جديد كيان' => ['<a href="java&#10;script:alert(1)">x</a>'],
            'مسافة قبل النقطتين' => ['<a href="javascript : alert(1)">x</a>'],
            'حالة مختلطة' => ['<a href="JaVaScRiPt:alert(1)">x</a>'],
            'vbscript بلا اقتباس' => ['<a href=vbscript:msgbox(1)>x</a>'],
            'data في src' => ['<img src="data:text/html,<script>alert(1)</script>">'],
            'مسافات بادئة' => ['<a href="  javascript:alert(1)">x</a>'],
        ];
    }

    #[DataProvider('attackVectors')]
    public function test_dangerous_schemes_are_neutralized(string $input): void
    {
        $this->assertNoDangerousScheme(safe_html($input), $input);
    }

    public function test_legitimate_links_survive(): void
    {
        $this->assertStringContainsString('https://example.com', safe_html('<a href="https://example.com">زيارة</a>'));
        $this->assertStringContainsString('mailto:a@b.com', safe_html('<a href="mailto:a@b.com">راسلني</a>'));
        $this->assertStringContainsString('/dashboard', safe_html('<a href="/dashboard">لوحتي</a>'));
        $this->assertStringContainsString('https://cdn.test/i.png', safe_html('<img src="https://cdn.test/i.png">'));
        // نصّ يذكر الكلمة javascript دون أن يكون مخططاً — يبقى
        $this->assertStringContainsString('javascript-tutorial.html', safe_html('<a href="/javascript-tutorial.html">درس</a>'));
    }

    public function test_event_handlers_still_stripped(): void
    {
        // حصانة انحدار: النمط [\s/] القائم يجب أن يبقى فعّالاً
        $this->assertStringNotContainsStringIgnoringCase('onerror', safe_html('<img src=x onerror=alert(1)>'));
        $this->assertStringNotContainsStringIgnoringCase('onerror', safe_html('<img src=x/onerror=alert(1)>'));
    }
}
