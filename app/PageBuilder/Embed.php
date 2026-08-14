<?php

namespace App\PageBuilder;

/**
 * بناء تضمينات آمنة (دفعة 4) — iframe يُبنى **خادميّاً** من مضيفٍ مسموح فقط (يوتيوب/ﭬيميو)،
 * والمعرّف مُقيَّد بـ[A-Za-z0-9_-] عبر regex ثمّ e() — فلا iframe خام من المستخدم ولا حقن.
 * (المُخزَّن نصٌّ = رابط عاديّ؛ الـiframe لا يُخزَّن أبداً — يُبنى وقت العرض فقط.)
 */
class Embed
{
    public static function iframe(string $url): ?string
    {
        $url = trim($url);
        $src = null;

        if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([A-Za-z0-9_-]{6,20})#', $url, $m)) {
            $src = 'https://www.youtube-nocookie.com/embed/' . $m[1];
        } elseif (preg_match('#vimeo\.com/(?:video/)?(\d{5,15})#', $url, $m)) {
            $src = 'https://player.vimeo.com/video/' . $m[1];
        }

        if ($src === null) {
            return null;
        }

        return '<iframe src="' . e($src) . '" loading="lazy" allowfullscreen frameborder="0" '
            . 'allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" '
            . 'referrerpolicy="strict-origin-when-cross-origin" title="فيديو مضمَّن"></iframe>';
    }
}
