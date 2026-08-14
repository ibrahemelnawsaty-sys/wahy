<?php

namespace App\PageBuilder;

use App\Support\PageContentScanner;

/**
 * تحقّق مخطّط شجرة الكتل عند الحفظ (ت-١٤): يرفض الأنواع المجهولة والبنية المشوَّهة + حمولات XSS.
 * خلافاً لـBlockTree::prepare (يُسقِط المجهول بصمت وقت الرندرة)، هذا يُرجِع أخطاءً ليتلقّاها المحرّر.
 *
 * @return array<int, string> قائمة الأخطاء (فارغة = صالح)
 */
class BlockValidator
{
    public static function validate(mixed $blocks): array
    {
        $errors = [];
        self::walk($blocks, 'blocks', $errors);

        // حمولات XSS (المصدر المشترك مع طبقة الحفظ والتدقيق)
        foreach (PageContentScanner::scan($blocks) as $v) {
            $errors[] = "{$v['path']}: حمولة غير آمنة ({$v['kind']})";
        }

        return array_values(array_unique($errors));
    }

    private const MAX_DEPTH = 8;

    private static function walk(mixed $blocks, string $path, array &$errors, int $depth = 0): void
    {
        if (! is_array($blocks)) {
            $errors[] = "{$path}: يجب أن تكون قائمة كتل";

            return;
        }
        if ($depth > self::MAX_DEPTH) {
            $errors[] = "{$path}: تداخل عميق جدّاً (الحدّ " . self::MAX_DEPTH . ')';

            return;
        }

        foreach ($blocks as $i => $block) {
            $p = "{$path}[{$i}]";

            if (! is_array($block)) {
                $errors[] = "{$p}: كتلة غير صالحة";

                continue;
            }

            $type = $block['type'] ?? null;
            if (! is_string($type) || ! BlockRegistry::has($type)) {
                $errors[] = "{$p}: نوع كتلة مجهول (" . (is_string($type) ? $type : gettype($type)) . ')';

                continue;
            }

            if (array_key_exists('props', $block) && ! is_array($block['props'])) {
                $errors[] = "{$p}.props: يجب أن تكون كائناً";
            }

            if (array_key_exists('children', $block)) {
                self::walk($block['children'], "{$p}.children", $errors, $depth + 1);
            }
        }
    }
}
