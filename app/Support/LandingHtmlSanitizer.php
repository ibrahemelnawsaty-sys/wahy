<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * مُعقّم HTML لتخطيط الصفحة الرئيسية المُدمج.
 *
 * لقطة الـ <main> تُحفَظ من متصفّح السوبر أدمن ثم تُعرَض للزوّار عبر {!! !!}،
 * فلا بد من تعقيمها خادِمياً (defense-in-depth) لمنع XSS المُخزَّن حتى لو
 * تلاعب أحدٌ بالطلب. المنهج: قائمة منع صريحة (وسوم/سمات/بروتوكولات خطِرة)
 * + إزالة عناصر أدوات التحرير كي لا تظهر للزائر.
 *
 * ملاحظة: مقصور على السوبر أدمن (بوابة الراوت)، والتعقيم طبقة ثانية لا الوحيدة.
 */
class LandingHtmlSanitizer
{
    /** وسوم تُحذف بالكامل (مع محتواها) — تنفيذ سكربت/تضمين خارجي/حقن أنماط */
    private const FORBIDDEN_TAGS = [
        'script', 'iframe', 'object', 'embed', 'base', 'meta',
        'link', 'style', 'noscript', 'template', 'frame', 'frameset',
    ];

    /** أصناف عناصر أدوات المحرّر — تُحذف كي لا تظهر للزائر */
    private const EDITOR_CHROME_CLASSES = [
        'section-actions', 'component-actions', 'element-actions',
        'drop-zone', 'editor-panel', 'edit-fab-container', 'edit-fab-menu',
        'edit-toggle-btn', 'editor-toolbar',
    ];

    /** سمات URL يجب فحص بروتوكولها */
    private const URL_ATTRS = ['href', 'src', 'action', 'formaction', 'poster', 'xlink:href', 'background', 'cite'];

    /** بروتوكولات خطِرة في سمات الروابط */
    private const DANGEROUS_PROTOCOLS = ['javascript:', 'vbscript:', 'data:text/html', 'data:application'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        // نلفّ الجزء في حاوية ونجبر UTF-8 كي تُحفظ العربية سليمة.
        $wrapped = '<?xml encoding="UTF-8"?><div id="__wahy_root__">' . $html . '</div>';

        $prevInternal = libxml_use_internal_errors(true);
        $prevEntity = function_exists('libxml_disable_entity_loader')
            ? @libxml_disable_entity_loader(true)
            : null;

        $loaded = $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET | LIBXML_NOENT);

        libxml_clear_errors();
        libxml_use_internal_errors($prevInternal);
        if ($prevEntity !== null) {
            @libxml_disable_entity_loader($prevEntity);
        }

        if (! $loaded) {
            return '';
        }

        $xpath = new DOMXPath($dom);

        // 1) حذف الوسوم المحظورة بالكامل
        self::removeNodes($xpath, '//' . implode(' | //', self::FORBIDDEN_TAGS));

        // 2) حذف عناصر أدوات المحرّر حسب الصنف
        foreach (self::EDITOR_CHROME_CLASSES as $class) {
            self::removeNodes($xpath, "//*[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]");
        }

        // 3) تنظيف سمات كل عنصر متبقٍّ (on*، contenteditable، بروتوكولات خطِرة)
        /** @var DOMElement $el */
        foreach ($xpath->query('//*') as $el) {
            if (! $el instanceof DOMElement || ! $el->hasAttributes()) {
                continue;
            }

            // ننسخ الأسماء أولاً لأن الحذف أثناء التكرار يفسد المجموعة الحيّة
            $attrNames = [];
            foreach ($el->attributes as $attr) {
                $attrNames[] = $attr->nodeName;
            }

            foreach ($attrNames as $name) {
                $lname = strtolower($name);

                // معالِجات الأحداث inline (onclick, onerror, ...)
                if (str_starts_with($lname, 'on')) {
                    $el->removeAttribute($name);

                    continue;
                }

                // سمات تخصّ وضع التحرير — لا يجب أن تصل للزائر
                if (in_array($lname, ['contenteditable', 'draggable', 'spellcheck'], true)
                    || str_starts_with($lname, 'data-editor')
                    || str_starts_with($lname, 'x-')      // بقايا Alpine غير مقصودة
                    || $lname === 'srcdoc') {
                    $el->removeAttribute($name);

                    continue;
                }

                // فحص بروتوكول سمات الروابط
                if (in_array($lname, self::URL_ATTRS, true)) {
                    $value = strtolower(trim(preg_replace('/\s+/', '', (string) $el->getAttribute($name))));
                    foreach (self::DANGEROUS_PROTOCOLS as $proto) {
                        if (str_starts_with($value, $proto)) {
                            $el->removeAttribute($name);
                            break;
                        }
                    }
                }
            }
        }

        // 4) استخراج innerHTML للحاوية الجذر
        $root = $dom->getElementById('__wahy_root__');
        if (! $root) {
            return '';
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    private static function removeNodes(DOMXPath $xpath, string $query): void
    {
        $nodes = $xpath->query($query);
        if ($nodes === false) {
            return;
        }
        // نجمع ثم نحذف (NodeList حيّة)
        $toRemove = [];
        foreach ($nodes as $node) {
            $toRemove[] = $node;
        }
        foreach ($toRemove as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }
    }
}
