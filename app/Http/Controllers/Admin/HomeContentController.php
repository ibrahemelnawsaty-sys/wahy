<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * محرّر محتوى الصفحة الرئيسية (المحرّر الموحّد الوحيد) — موثوق وخادميّ.
 * يُولَّد النموذج من config/landing_editable.php، ويُحفَظ في landing_content،
 * ويعرضه القالب عبر lc('key', الافتراضيّ) خادميّاً (بلا وميض ولا صفحة فارغة).
 * كلّ حفظ يُنشئ لقطة أمان في landing_content_versions قابلة للاسترجاع. محميّ can:access-admin.
 */
class HomeContentController extends Controller
{
    public function edit()
    {
        return view('admin.home-content', [
            'sections' => config('landing_editable', []),
            'saved' => LandingContent::map(), // مُخبّأ ومتحمّل لغياب الجدول
            'versions' => $this->recentVersions(),
        ]);
    }

    public function update(Request $request)
    {
        // كامل الحفظ ذرّيّ: لقطة أمان أوّلاً ثم كتابة كل الحقول ضمن معاملة واحدة (تراجع عند الفشل).
        DB::transaction(function () use ($request) {
            try {
                LandingContent::createSnapshot();
            } catch (\Throwable $e) {
                // جدول النسخ ثانويّ — لا يُفشِل الحفظ إن تعذّر (مثلاً لم يُهاجَر بعد)
                Log::warning('Landing snapshot skipped on save: ' . $e->getMessage());
            }

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
        });

        lc_forget(); // مسح الكاش ليظهر التعديل فوراً

        return redirect()->route('admin.home-content.edit')
            ->with('success', 'تم حفظ محتوى الصفحة الرئيسية بنجاح ✅');
    }

    /**
     * استرجاع لقطة سابقة — يستبدل المحتوى الحاليّ بالمحفوظ في النسخة، ذرّيّاً.
     * يأخذ لقطة للحالة الحاليّة أوّلاً فالاسترجاع نفسه قابل للتراجع.
     */
    public function restore(Request $request, int $version)
    {
        $row = DB::table('landing_content_versions')->where('id', $version)->first();
        if (! $row) {
            return back()->with('error', 'النسخة غير موجودة.');
        }
        $snapshot = json_decode((string) $row->content_snapshot, true);
        if (! is_array($snapshot)) {
            return back()->with('error', 'النسخة تالفة وتعذّر استرجاعها.');
        }

        DB::transaction(function () use ($snapshot) {
            try {
                LandingContent::createSnapshot(); // احفظ الحالة الحاليّة قبل الاستبدال
            } catch (\Throwable $e) {
                Log::warning('Pre-restore snapshot skipped: ' . $e->getMessage());
            }

            LandingContent::query()->delete(); // ضمن المعاملة — لا يُتلِف عند الفشل
            foreach ($snapshot as $r) {
                if (empty($r['key'])) {
                    continue;
                }
                LandingContent::create([
                    'key' => (string) $r['key'],
                    'value' => (string) ($r['value'] ?? ''),
                    'type' => $r['type'] ?? 'text',
                    'section' => $r['section'] ?? 'home',
                    'order' => (int) ($r['order'] ?? 0),
                    'metadata' => $r['metadata'] ?? null,
                ]);
            }
        });

        lc_forget();

        return redirect()->route('admin.home-content.edit')
            ->with('success', 'تم استرجاع النسخة المحدّدة بنجاح ✅');
    }

    /**
     * أحدث 10 لقطات (لعرض التراجع) — متحمّلة لغياب الجدول.
     */
    private function recentVersions()
    {
        try {
            return DB::table('landing_content_versions')
                ->orderByDesc('id')
                ->limit(10)
                ->get(['id', 'created_at', 'created_by']);
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
