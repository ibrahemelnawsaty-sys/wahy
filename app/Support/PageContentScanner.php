<?php

namespace App\Support;

/**
 * ماسح حمولات XSS في محتوى الصفحات المخزَّن (page_builder.json_data وlanding_content.value).
 * مصدرٌ واحد يخدم **طبقة الحفظ** (رفض الإدخال الخطر) و**سكربت التدقيق** (فحص المخزَّن) — بند ت-١.
 *
 * يكشف: مخطّطات الروابط الخطرة (javascript:/data:/vbscript:…)، الوسوم الخطرة (<script/<iframe/…)،
 * معالجات الأحداث (on…=)، وحقن اسم الوسم في حقول level خارج قائمة السماح (h1-h6).
 */
class PageContentScanner
{
    /**
     * يمسح بنية (مصفوفة/نصّ) تكرارياً ويُعيد قائمة المخالفات.
     *
     * @return array<int, array{path:string, kind:string, sample:string}>
     */
    public static function scan(mixed $data, string $path = 'root'): array
    {
        $out = [];

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $childPath = $path . '.' . $key;

                // حقل اسم وسم العنوان (heading level) خارج قائمة السماح = حقن اسم وسم
                if ($key === 'level' && is_string($val)
                    && ! in_array(strtolower(trim($val)), ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
                    $out[] = ['path' => $childPath, 'kind' => 'tag-name-injection', 'sample' => self::sample($val)];
                }

                $out = array_merge($out, self::scan($val, $childPath));
            }
        } elseif (is_string($data)) {
            $out = array_merge($out, self::scanString($data, $path));
        }

        return $out;
    }

    /** هل توجد أيّ مخالفة؟ (لطبقة الحفظ — رفض سريع). */
    public static function hasViolations(mixed $data): bool
    {
        return self::scan($data) !== [];
    }

    private static function scanString(string $s, string $path): array
    {
        $out = [];

        // مخطّط خطر في **بداية** القيمة (رابط) — بعد فكّ التشويش (كيانات + أحرف تحكّم يُسقطها المحلّل).
        // نقتصر على البداية لتفادي إنذار كاذب على نصٍّ يذكر «javascript:» في جملة عاديّة.
        $probe = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $probe = html_entity_decode($probe, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $probe = preg_replace('/[\x00-\x20]|\xC2\xA0/', '', (string) $probe);
        if (preg_match('/^(javascript|data|vbscript|about|file):/i', (string) $probe)) {
            $out[] = ['path' => $path, 'kind' => 'dangerous-scheme', 'sample' => self::sample($s)];
        }

        // وسوم خطرة داخل النصّ (تُنفَّذ إن صُبّت خاماً)
        if (preg_match('#<\s*(script|iframe|object|embed|svg|form|math|foreignObject|link|meta|base)\b#i', $s)) {
            $out[] = ['path' => $path, 'kind' => 'dangerous-tag', 'sample' => self::sample($s)];
        }

        // معالج حدث onXXX= (مسبوقٌ بفاصل سمة أو في بداية القيمة)
        if (preg_match('#[\s"\'/]on[a-z]+\s*=#i', $s) || preg_match('#^on[a-z]+\s*=#i', $s)) {
            $out[] = ['path' => $path, 'kind' => 'event-handler', 'sample' => self::sample($s)];
        }

        return $out;
    }

    private static function sample(string $s): string
    {
        $s = trim((string) preg_replace('/\s+/u', ' ', $s));

        return mb_strlen($s) > 120 ? mb_substr($s, 0, 120) . '…' : $s;
    }
}
