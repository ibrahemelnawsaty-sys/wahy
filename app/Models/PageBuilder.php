<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageBuilder extends Model
{
    protected $table = 'page_builder';

    protected $fillable = [
        'page_name',
        'slug',
        'json_data',
        'meta_title',
        'meta_description',
        'og_image',
        'is_active',
    ];

    protected $casts = [
        'json_data' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * أنواع كتل «الصيغة القديمة» التي يعرف قالب pages/show.blade.php تصييرها فعلاً
     * (فرع `@switch` أسفل «Fallback for old format»). النوع خارج القائمة **لا يُصيَّر إطلاقاً**
     * لأنّ الـ`@switch` بلا `@default` — فصفحةٌ كلّ كتلها من خارجها = شاشة بيضاء.
     *
     * ⚠️ محرّر الصفحة الرئيسية يعرض أنواعاً **ليست هنا** (stats/features/cta/testimonials):
     * تُحفَظ ولا تظهر. تُبقيها هذه القائمة خارج حساب «ذات محتوى» فلا تحجب landing.blade.
     * اختبار PageBuilderRenderableTypesTest يمنع انحراف هذه القائمة عن القالب.
     */
    public const RENDERABLE_BLOCK_TYPES = [
        'button', 'cards', 'heading', 'hero', 'image', 'paragraph', 'spacer', 'video',
    ];

    /** نظيرتها لصيغة `sections` (فرع المكوّنات في القالب نفسه). */
    public const RENDERABLE_COMPONENT_TYPES = [
        'accordion', 'alert', 'badge', 'button', 'card', 'divider', 'gallery', 'heading',
        'html', 'icon', 'image', 'link', 'list', 'paragraph', 'quote', 'spacer', 'tabs', 'video',
    ];

    /**
     * هل تحمل الصفحة محتوى قابلاً للعرض فعلاً؟
     *
     * صفٌّ نشط بلا كتل يُصيَّر عبر pages.show كغلافٍ فارغ (شاشة بيضاء) ويَحجب في الوقت نفسه
     * الصفحة الثابتة landing.blade — وهي الحادثة التي أوقعها إنشاء صفّ home فارغ من المحرّر.
     * لذلك «فارغ» يُعامَل كـ«غير مضبوط» لا كـ«منشور»، ولا يُخدَم للجمهور (الدستور §10.1.4).
     *
     * المنطق يعكس تفرّع resources/views/pages/show.blade.php حرفياً:
     * إن وُجد مفتاح sections كمصفوفة فهو المصدر، وإلّا تُعدّ json_data قائمة كتل قديمة.
     */
    public function hasRenderableContent(): bool
    {
        return self::contentIsRenderable($this->json_data);
    }

    /**
     * الفحص فوق القيمة المُفكَّكة الخام. النوع `mixed` مقصود: عمود JSON قد يُفكَّك إلى
     * null أو قيمة قياسية إن كُتب من خارج المحرّر، والقالب يتحطّم عليها لو عوملت كمصفوفة.
     */
    private static function contentIsRenderable(mixed $data): bool
    {
        if (! is_array($data) || $data === []) {
            return false;
        }

        // الصيغة الجديدة: {"sections": [...]} — يلزم مكوّنٌ واحد **يعرف القالب تصييره**.
        if (isset($data['sections']) && is_array($data['sections'])) {
            foreach ($data['sections'] as $section) {
                if (! is_array($section) || ! isset($section['grid']) || ! is_array($section['grid'])) {
                    continue;
                }
                foreach ($section['grid'] as $column) {
                    if (! is_array($column)) {
                        continue;
                    }
                    foreach ($column as $component) {
                        if (self::typeIsRenderable($component, self::RENDERABLE_COMPONENT_TYPES)) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        // الصيغة القديمة: قائمة كتل — كتلة واحدة من نوعٍ مُصيَّر تكفي. الأنواع التي لا `@case` لها
        // تُنتِج صفراً من المخرجات، فاحتسابها «محتوى» يُعيد إنتاج الشاشة البيضاء نفسها.
        foreach ($data as $block) {
            if (self::typeIsRenderable($block, self::RENDERABLE_BLOCK_TYPES)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $allowed
     */
    private static function typeIsRenderable(mixed $node, array $allowed): bool
    {
        return is_array($node)
            && isset($node['type'])
            && is_string($node['type'])
            && in_array($node['type'], $allowed, true);
    }

    /**
     * Get page by slug
     */
    public static function getBySlug($slug)
    {
        return self::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * الصفحة المنشورة **وذات المحتوى** لهذا الـslug — البوّابة الوحيدة للعرض العامّ.
     */
    public static function servableBySlug(string $slug): ?self
    {
        $page = self::getBySlug($slug);

        return $page && $page->hasRenderableContent() ? $page : null;
    }

    /**
     * Get all active pages
     */
    public static function getActivePages()
    {
        return self::where('is_active', true)
            ->orderBy('page_name')
            ->get();
    }
}
