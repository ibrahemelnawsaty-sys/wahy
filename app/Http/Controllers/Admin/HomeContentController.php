<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingContent;
use Illuminate\Http\Request;

/**
 * محرّر محتوى الصفحة الرئيسية (نصوص الأقسام) — موثوق وخادميّ.
 * يُولَّد النموذج من config/landing_editable.php، ويُحفَظ في landing_content،
 * ويعرضه القالب عبر lc('key', الافتراضيّ) خادميّاً (بلا وميض ولا صفحة فارغة). محميّ can:access-admin.
 */
class HomeContentController extends Controller
{
    public function edit()
    {
        return view('admin.home-content', [
            'sections' => config('landing_editable', []),
            'saved' => LandingContent::map(), // مُخبّأ ومتحمّل لغياب الجدول
        ]);
    }

    public function update(Request $request)
    {
        foreach (config('landing_editable', []) as $section) {
            foreach ($section['fields'] as $key => $meta) {
                if (! $request->has($key)) {
                    continue;
                }
                $val = trim((string) $request->input($key));

                // مساوٍ للافتراضيّ أو فارغ ⟶ نحذف التخصيص (يعود القالب لافتراضيّه، نظافة)
                if ($val === '' || $val === trim((string) ($meta['default'] ?? ''))) {
                    LandingContent::where('key', $key)->delete();
                } else {
                    LandingContent::setValue($key, $val, ['section' => 'home', 'type' => $meta['type'] ?? 'text']);
                }
            }
        }

        lc_forget(); // مسح الكاش ليظهر التعديل فوراً

        return redirect()->route('admin.home-content.edit')
            ->with('success', 'تم حفظ محتوى الصفحة الرئيسية بنجاح ✅');
    }
}
