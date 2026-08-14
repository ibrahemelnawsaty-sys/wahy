<?php

namespace App\PageBuilder;

/**
 * تعقيم HTML لكتلة النصّ الغنيّ عبر HTMLPurifier — تنفيذ §10.12/R9 من الدستور:
 * استبدال safe_html (قائمة حظر regex قابلة للتجاوز) بمُعقِّم **allowlist** حقيقيّ.
 * يُبقي التنسيق الآمن (فقرات/غامق/روابط/عناوين/قوائم/جداول) ويُسقط كلّ ما عداه.
 */
class HtmlPurify
{
    private static ?\HTMLPurifier $instance = null;

    public static function clean(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        return self::purifier()->purify($html);
    }

    private static function purifier(): \HTMLPurifier
    {
        if (self::$instance) {
            return self::$instance;
        }

        $config = \HTMLPurifier_Config::createDefault();
        // بلا كاش تعريفات على القرص (يتجنّب أيّ مشكلة صلاحيّات كتابة على الإنتاج).
        $config->set('Cache.DefinitionImpl', null);
        // وسوم HTML4 مدعومة فقط (لا HTML5 مثل figure كي لا يُطلِق تحذيراً).
        $config->set('HTML.Allowed',
            'p,br,strong,b,em,i,u,ul,ol,li,a[href|title|target|rel],'
            . 'h1,h2,h3,h4,h5,h6,blockquote,span[style],div[style],hr,'
            . 'img[src|alt|width|height],'
            . 'table,thead,tbody,tr,th,td');
        $config->set('CSS.AllowedProperties', 'text-align,color,background-color,font-weight,font-style,text-decoration');
        // مخطّطات روابط مسموحة فقط — يُسقِط javascript:/data: تلقائيّاً.
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetBlank', true); // يضيف rel=noopener لروابط target=_blank

        return self::$instance = new \HTMLPurifier($config);
    }
}
