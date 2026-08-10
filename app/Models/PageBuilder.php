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

        // الصيغة الجديدة: {"sections": [...]}
        if (isset($data['sections']) && is_array($data['sections'])) {
            return $data['sections'] !== [];
        }

        // الصيغة القديمة: قائمة كتل — القالب يتخطّى أيّ عنصر بلا مفتاح type، فكتلة صالحة واحدة تكفي.
        foreach ($data as $block) {
            if (is_array($block) && isset($block['type'])) {
                return true;
            }
        }

        return false;
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
