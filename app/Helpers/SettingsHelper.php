<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Get or set a setting value
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function setting($key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('safe_mail_subject')) {
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

if (! function_exists('social_links')) {
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
            if (! empty($url) && $url !== '#') {
                $links[$p] = $url;
            }
        }

        return $links;
    }
}

if (! function_exists('set_setting')) {
    /**
     * Set a setting value
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  string  $type
     * @param  string|null  $description
     * @return Setting
     */
    function set_setting($key, $value, $type = 'string', $description = null)
    {
        return Setting::set($key, $value, $type, $description);
    }
}

if (! function_exists('hexToRgba')) {
    /**
     * Convert HEX color to RGBA
     *
     * @param  string  $hex
     * @param  float  $opacity
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

if (! function_exists('hexToRgb')) {
    /**
     * Convert HEX color to RGB (without alpha)
     *
     * @param  string  $hex
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

if (! function_exists('adjustBrightness')) {
    /**
     * Adjust color brightness
     *
     * @param  string  $hex
     * @param  int  $steps  (-255 to 255)
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

if (! function_exists('html_excerpt')) {
    /**
     * استخراج نص نظيف من محتوى محرر HTML لعرضه كمعاينة قصيرة.
     * يُزيل الوسوم ويفكّ ترميز الكيانات (&nbsp; &amp; ...) بشكل آمن وحتى لو كانت مرمّزة مرّتين.
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

if (! function_exists('safe_html')) {
    /**
     * تنظيف HTML من XSS مع السماح بالعلامات الآمنة (للرسائل ومحتوى الدروس).
     * يُستخدم في الـ Blade مع {!! safe_html($message->message) !!}
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
        //    الفاصل [\s/] وليس \s فقط: HTML يسمح بـ«/» فاصلاً بين السمات، فـ<img src=x/onerror=…>
        //    كان ينجو (img/a/video ليست ضمن الوسوم الخطرة) = XSS مخزَّن. [\s/] يلتقط الحالتين.
        $html = preg_replace('#[\s/]on[a-z]+\s*=\s*"[^"]*"#i', '', (string) $html);
        $html = preg_replace("#[\s/]on[a-z]+\s*=\s*'[^']*'#i", '', (string) $html);
        $html = preg_replace('#[\s/]on[a-z]+\s*=\s*[^\s>]+#i', '', (string) $html);

        // 3) تحييد المخطّطات الخطرة (javascript:/vbscript:/data:/about:) في السمات الحاملة
        //    لروابط — مهما كان الاقتباس (مزدوج/مفرد/بلا اقتباس)، وبعد فكّ التشويش داخل القيمة:
        //    فالمتصفح يُسقِط التاب/السطر/التحكّم (C0) وأحرف الكيانات (jav&#x09;ascript:) أثناء
        //    تحليل المخطط فيُعيد تكوين javascript:. regex الاقتباس القديم كان يفوته كلُّ ذلك
        //    (href غير مُقتبَس + التشويش) = XSS مخزَّن يُنفَّذ بالنقر في جلسة أعلى صلاحية.
        $urlAttrs = 'href|src|action|formaction|srcdoc|xlink:href|background|poster';
        $html = preg_replace_callback(
            '#\b(' . $urlAttrs . ')\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i',
            function ($m) {
                $raw = $m[2];
                // اقتطاع الاقتباس إن وُجد
                if (strlen($raw) >= 2 && ($raw[0] === '"' || $raw[0] === "'") && $raw[strlen($raw) - 1] === $raw[0]) {
                    $val = substr($raw, 1, -1);
                } else {
                    $val = $raw;
                }
                // تطبيع لكشف المخطط فقط (لا يمسّ الإخراج): فكّ الكيانات مرّتين ثمّ إزالة كل
                // أحرف التحكّم C0 + المسافة + NBSP التي يُسقطها محلّل الروابط، ثمّ توحيد الحالة.
                $probe = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $probe = html_entity_decode($probe, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $probe = preg_replace('/[\x00-\x20]|\xC2\xA0/', '', (string) $probe);
                $probe = strtolower((string) $probe);
                if (preg_match('#^(javascript|vbscript|data|about):#', $probe)) {
                    return ''; // إسقاط السمة الخطرة بالكامل (يُبقي نصّ الوسم مرئياً وحميداً)
                }

                return $m[0];
            },
            (string) $html
        );

        // 4) إزالة CSS expression() و @import و url(javascript:...) في style attribute
        $html = preg_replace('#\sstyle\s*=\s*"[^"]*\b(expression|@import|javascript|vbscript|behavior)\s*[:\(][^"]*"#i', '', (string) $html);
        $html = preg_replace("#\sstyle\s*=\s*'[^']*\b(expression|@import|javascript|vbscript|behavior)\s*[:\(][^']*'#i", '', (string) $html);

        return $html;
    }
}

if (! function_exists('normalize_message_html')) {
    /**
     * تطبيع محتوى رسالة قادم من محرّر contenteditable قبل التخزين/العرض.
     *
     * يعالج «الفقاعات الطويلة الفارغة»: المحرّر يُدخل عُقداً فارغة/بيضاء (كتل <div>
     * و<div><br></div> فارغة، &nbsp;/U+00A0، أحرف zero-width U+200B/200C/200D/2060/FEFF،
     * ومسافات/أسطر زائدة) و.message-bubble يعرضها بـpre-wrap فتصير ارتفاعاً حقيقياً.
     * لا يمسّ النصّ أو التنسيق الفعليّ (b/i/u/a/img غير الفارغ) ولا يقوم مقام safe_html
     * (يُستخدم قبله: safe_html(normalize_message_html($x))). آمن وعديم-التأثير عند التكرار.
     */
    function normalize_message_html(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }
        $html = (string) $html;

        // حصانة: نضمن UTF-8 صالحاً قبل أي preg_replace بمُعدِّل /u (يُعيد null على البايتات
        // الفاسدة فتختفي الرسالة). نُسقِط غير الصالح (نادر مع تخزين utf8mb4).
        if (! mb_check_encoding($html, 'UTF-8')) {
            $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
        }

        // 1) حذف الأحرف الخفية (zero-width / word-joiner / BOM / soft-hyphen) — لا يزيلها trim
        $html = preg_replace('/[\x{200B}\x{200C}\x{200D}\x{2060}\x{FEFF}\x{00AD}]/u', '', $html);

        // 2) توحيد المسافة غير الفاصلة إلى مسافة عاديّة (بأشكالها المرمّزة والحرفيّة)
        $html = preg_replace('/\x{00A0}|&nbsp;|&#0*160;|&#x0*A0;/iu', ' ', $html);

        // 3) توحيد كل أشكال <br> إلى <br>
        $html = preg_replace('#<\s*br\s*/?\s*>#i', '<br>', $html);

        // 4) إزالة العناصر الفارغة تكرارياً (محتواها مسافات/<br> فقط ولا وسائط بداخلها)
        //    التكرار يعالج التداخل: يُفرَّغ الداخليّ ثمّ يصبح الخارجيّ فارغاً في الجولة التالية.
        $emptyTag = 'div|p|span|b|i|u|strong|em|a|font|small|sub|sup|section|article';
        do {
            $before = $html;
            $html = preg_replace('#<(' . $emptyTag . ')\b[^>]*>(?:\s|<br>)*</\s*\1\s*>#iu', '', $html);
        } while ($html !== $before && $html !== null);

        // 5) طيّ تتابع <br> إلى واحد، وحذف البادئ/الذيليّ منها ومن المسافات المحيطة
        $html = preg_replace('#(?:\s*<br>\s*){2,}#i', '<br>', $html);
        $html = preg_replace('#^(?:\s|<br>)+#i', '', $html);
        $html = preg_replace('#(?:\s|<br>)+$#i', '', $html);

        // 6) ضغط المسافات/الأسطر المتكرّرة داخل النصّ (pre-line يطوي ما تبقّى)
        $html = preg_replace('/[ \t\x{00A0}]{2,}/u', ' ', $html);
        $html = preg_replace('/(\r?\n\s*){2,}/', "\n", $html);

        return trim((string) $html);
    }
}
