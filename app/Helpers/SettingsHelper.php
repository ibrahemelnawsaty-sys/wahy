<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get or set a setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting($key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('safe_mail_subject')) {
    /**
     * تنظيف عنوان البريد من حقن CRLF لمنع Email Header Injection.
     * يزيل \r و \n و التحكم في صورة \0..\x1F.
     */
    function safe_mail_subject($subject): string
    {
        $s = (string) $subject;
        // إزالة CR/LF وأي تحكم
        $s = preg_replace('/[\r\n\0\x00-\x1F\x7F]+/u', ' ', $s) ?? '';
        // قص للحدود المعقولة (RFC 5322: 998 لكن أقل أفضل)
        return mb_substr(trim($s), 0, 200);
    }
}

if (!function_exists('social_links')) {
    /**
     * روابط التواصل الاجتماعي الموحّدة — تدمج نطاقَي المفاتيح المتعارضين:
     * social_facebook (السيدر/الإيميل) و facebook_url (الإعدادات/الفوتر).
     * تُرجع فقط الروابط غير الفارغة: ['facebook' => url, 'whatsapp' => url, ...].
     */
    function social_links(): array
    {
        $platforms = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'whatsapp'];
        $links = [];
        foreach ($platforms as $p) {
            $url = setting('social_' . $p) ?: setting($p . '_url');
            if (!empty($url) && $url !== '#') {
                $links[$p] = $url;
            }
        }
        return $links;
    }
}

if (!function_exists('set_setting')) {
    /**
     * Set a setting value
     * 
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $description
     * @return Setting
     */
    function set_setting($key, $value, $type = 'string', $description = null)
    {
        return Setting::set($key, $value, $type, $description);
    }
}

if (!function_exists('hexToRgba')) {
    /**
     * Convert HEX color to RGBA
     * 
     * @param string $hex
     * @param float $opacity
     * @return string
     */
    function hexToRgba($hex, $opacity = 1)
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "rgba($r, $g, $b, $opacity)";
    }
}

if (!function_exists('hexToRgb')) {
    /**
     * Convert HEX color to RGB (without alpha)
     * 
     * @param string $hex
     * @return string
     */
    function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "$r, $g, $b";
    }
}

if (!function_exists('adjustBrightness')) {
    /**
     * Adjust color brightness
     * 
     * @param string $hex
     * @param int $steps (-255 to 255)
     * @return string
     */
    function adjustBrightness($hex, $steps)
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = max(0, min(255, hexdec(substr($hex, 0, 2)) + $steps));
        $g = max(0, min(255, hexdec(substr($hex, 2, 2)) + $steps));
        $b = max(0, min(255, hexdec(substr($hex, 4, 2)) + $steps));
        
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
                  . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
                  . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('html_excerpt')) {
    /**
     * استخراج نص نظيف من محتوى محرر HTML لعرضه كمعاينة قصيرة.
     * يُزيل الوسوم ويفكّ ترميز الكيانات (&nbsp; &amp; ...) بشكل آمن وحتى لو كانت مرمّزة مرّتين.
     *
     * @param string|null $html
     * @param int $limit
     * @return string
     */
    function html_excerpt(?string $html, int $limit = 100): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // فك ترميز مزدوج لمعالجة الحالات التي يكون فيها المحتوى مخزّناً بـ &amp;nbsp;
        $text = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // إزالة الوسوم
        $text = strip_tags($text);

        // تطبيع المسافات الخاصة (NBSP) إلى مسافات عادية وضغط المتكرر منها
        $text = preg_replace('/\xC2\xA0|&nbsp;/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', trim($text));

        return \Illuminate\Support\Str::limit($text, $limit);
    }
}

if (!function_exists('safe_html')) {
    /**
     * تنظيف HTML من XSS مع السماح بالعلامات الآمنة (للرسائل ومحتوى الدروس).
     * يُستخدم في الـ Blade مع {!! safe_html($message->message) !!}
     *
     * @param  string|null $html
     * @return string
     */
    function safe_html(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // 1) إزالة كاملة للعلامات الخطرة — بما فيها svg/math/foreignObject لمنع XSS الناقل
        $dangerousTags = 'script|iframe|object|embed|link|style|meta|form|input|textarea|button|select|option|svg|math|foreignObject|frame|frameset|applet|base|portal|use|annotation|animate|set';
        $html = preg_replace('#<\s*(' . $dangerousTags . ')\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', (string) $html);
        $html = preg_replace('#<\s*(' . $dangerousTags . ')\b[^>]*/?\s*>#i', '', (string) $html);

        // 2) إزالة event handlers (onclick, onerror, onload, …)
        $html = preg_replace('#\son[a-z]+\s*=\s*"[^"]*"#i', '', (string) $html);
        $html = preg_replace("#\son[a-z]+\s*=\s*'[^']*'#i", '', (string) $html);
        $html = preg_replace('#\son[a-z]+\s*=\s*[^\s>]+#i', '', (string) $html);

        // 3) إزالة javascript: و vbscript: و data: في href/src
        $html = preg_replace('#\b(href|src|action|formaction|srcdoc|xlink:href|background|poster)\s*=\s*"[^"]*\b(javascript|vbscript|data)\s*:[^"]*"#i', '', (string) $html);
        $html = preg_replace("#\b(href|src|action|formaction|srcdoc|xlink:href|background|poster)\s*=\s*'[^']*\b(javascript|vbscript|data)\s*:[^']*'#i", '', (string) $html);

        // 4) إزالة CSS expression() و @import و url(javascript:...) في style attribute
        $html = preg_replace('#\sstyle\s*=\s*"[^"]*\b(expression|@import|javascript|vbscript|behavior)\s*[:\(][^"]*"#i', '', (string) $html);
        $html = preg_replace("#\sstyle\s*=\s*'[^']*\b(expression|@import|javascript|vbscript|behavior)\s*[:\(][^']*'#i", '', (string) $html);

        return $html;
    }
}
