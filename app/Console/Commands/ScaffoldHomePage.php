<?php

namespace App\Console\Commands;

use App\PageBuilder\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * أداة ترحيل home إلى محرّر v2 (ت-١٢): تُنشئ صفحة home منشورة مبدئيّة (هيكل قابل للتحرير)
 * دون المساس بأيّ نظام قائم. غير مدمِّرة (تتخطّى إن وُجدت صفحة home بنفس اللغة).
 * بعدها يفعّلها المالك على المسار العامّ عبر زرّ «go-live» (لا يفعّلها هذا الأمر تلقائيّاً).
 */
class ScaffoldHomePage extends Command
{
    protected $signature = 'pb:scaffold-home {--locale=ar} {--slug=home}';

    protected $description = 'إنشاء صفحة home مبدئيّة في محرّر الصفحات v2 (غير مدمِّرة)';

    public function handle(): int
    {
        $locale = (string) $this->option('locale');
        $slug = (string) $this->option('slug');

        if (Page::where('slug', $slug)->where('locale', $locale)->exists()) {
            $this->warn("صفحة «{$slug}» ({$locale}) موجودة مسبقاً — لا تغيير.");

            return self::SUCCESS;
        }

        $page = Page::create([
            'translation_group' => (string) Str::uuid(),
            'locale' => $locale,
            'title' => 'الصفحة الرئيسية',
            'slug' => $slug,
            'status' => 'published',
            'published_at' => now(),
            'blocks' => [
                ['type' => 'hero', 'v' => 1, 'props' => [
                    'title' => 'منصّة قِيَم التعليميّة',
                    'subtitle' => 'ابنِ القيم، وحفّز التعلّم، وتابع التقدّم — بتجربة واحدة متكاملة.',
                    'button_text' => 'ابدأ الآن',
                    'button_link' => '/register',
                ]],
                ['type' => 'features', 'v' => 1, 'props' => [
                    'items' => [
                        ['title' => 'قيم راسخة', 'text' => 'أنشطة وقيم مصمَّمة لبناء الشخصيّة.'],
                        ['title' => 'تحفيز ذكيّ', 'text' => 'نقاط وشارات ومستويات تشجّع الاستمرار.'],
                        ['title' => 'متابعة الوليّ', 'text' => 'تقارير واضحة لرحلة الابن التعليميّة.'],
                    ],
                ]],
                ['type' => 'cta', 'v' => 1, 'props' => [
                    'title' => 'جاهز للانطلاق؟',
                    'button_text' => 'أنشئ حسابك',
                    'button_link' => '/register',
                ]],
            ],
        ]);

        $this->info("أُنشئت صفحة «{$slug}» ({$locale}) منشورةً — المعرّف {$page->id}.");
        $this->line('فعّلها على المسار العامّ عبر زرّ go-live في المحرّر متى شئت.');

        return self::SUCCESS;
    }
}
