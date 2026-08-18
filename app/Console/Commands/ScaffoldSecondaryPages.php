<?php

namespace App\Console\Commands;

use App\PageBuilder\Models\Page;
use App\PageBuilder\SlugGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/**
 * توليد صفحات المحرّر للصفحات الجانبيّة (الشروط/الخصوصيّة) **من محتواها الحاليّ**.
 *
 * غير مدمِّر بثلاث طبقات: يتخطّى ما هو موجود، ويُنشئ **مسودّة** لا منشوراً، ولا يرفع علم
 * PageResolver — فيبقى القالب الثابت هو المخدوم حتى يراجع الأدمن ويَنشُر بنفسه.
 */
class ScaffoldSecondaryPages extends Command
{
    protected $signature = 'pb:scaffold-pages {--locale=ar}';

    protected $description = 'إنشاء مسودّات محرّر الصفحات للصفحات الجانبيّة من محتواها الحاليّ';

    /** slug => [العنوان، القالب الثابت] */
    private const PAGES = [
        'terms' => ['الشروط والأحكام', 'legal.terms'],
        'privacy' => ['سياسة الخصوصية', 'legal.privacy'],
    ];

    public function handle(): int
    {
        $locale = (string) $this->option('locale');

        foreach (self::PAGES as $slug => [$title, $view]) {
            if (! in_array($slug, SlugGuard::TAKEOVER_ALLOWED, true)) {
                $this->warn("«{$slug}» ليس ضمن المسارات المتنازِلة — تخطٍّ.");

                continue;
            }

            if (Page::where('slug', $slug)->where('locale', $locale)->exists()) {
                $this->warn("صفحة «{$slug}» ({$locale}) موجودة مسبقاً — لا تغيير.");

                continue;
            }

            $page = Page::create([
                'translation_group' => (string) Str::uuid(),
                'locale' => $locale,
                'title' => $title,
                'slug' => $slug,
                'status' => 'draft',
                'meta_title' => $title,
                'blocks' => [
                    ['type' => 'heading', 'v' => 1, 'props' => ['text' => $title, 'level' => 'h1']],
                    ['type' => 'richtext', 'v' => 1, 'props' => ['html' => $this->extractBody($view)]],
                ],
            ]);

            $this->info("أُنشئت مسودّة «{$title}» ({$slug}) — المعرّف {$page->id}.");
        }

        $this->line('راجِعها في /admin/pb ثمّ انشرها وفعّلها بزرّ go-live متى شئت.');
        $this->line('حتى ذلك الحين تبقى الصفحة الثابتة هي المعروضة للزوّار.');

        return self::SUCCESS;
    }

    /**
     * استخراج نصّ الصفحة الحاليّة ليبدأ الأدمن من محتواه الحقيقيّ لا من صفحةٍ بيضاء.
     * يُصيَّر القالب ثمّ يُقتطع جسم المستند وتُزال العناصر غير المحتوائيّة.
     */
    private function extractBody(string $view): string
    {
        try {
            $html = View::make($view)->render();
        } catch (\Throwable $e) {
            $this->error("تعذّر تصيير «{$view}»: " . $e->getMessage());

            return '<p></p>';
        }

        // جسم المستند فقط
        if (preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $m)) {
            $html = $m[1];
        } elseif (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $m)) {
            $html = $m[1];
        }

        // إزالة ما ليس محتوى (سكربتات/أنماط/تنقّل) — المحرّر يملك هيدره وفوتره
        $html = preg_replace('/<(script|style|nav|header|footer)\b[^>]*>.*?<\/\1>/is', '', $html) ?? $html;

        return trim($html) ?: '<p></p>';
    }
}
