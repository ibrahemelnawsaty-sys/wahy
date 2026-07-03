<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LandingContent;
use App\Models\LandingLayout;
use Illuminate\Http\Request;

/**
 * @group Landing Page Content
 *
 * إدارة محتوى صفحة الـ Landing — متاح للقراءة العامة، تعديل/رفع للـ super_admin فقط.
 */
class LandingContentController extends Controller
{
    /**
     * جلب كل محتوى الـ landing page.
     *
     * متاح علناً بدون مصادقة. يُرجع key/value pairs + تجميع حسب الـ section.
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "success": true,
     *   "content": {
     *     "hero_title": "منصة قيمّ",
     *     "hero_subtitle": "تعليم القيم بأسلوب تفاعلي"
     *   },
     *   "grouped": {}
     * }
     */
    public function index()
    {
        $content = LandingContent::orderBy('section')->orderBy('order')->get();

        // تحويل المحتوى إلى key => value pairs
        $contentArray = $content->pluck('value', 'key')->toArray();

        return response()->json([
            'success' => true,
            'content' => $contentArray,
            'grouped' => $content->groupBy('section'), // للاستخدامات المستقبلية
        ]);
    }

    /**
     * تحديث محتوى معين
     */
    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
            'type' => 'sometimes|string',
            'section' => 'sometimes|string',
        ]);

        try {
            $content = LandingContent::setValue(
                $request->key,
                $request->value,
                $request->only(['type', 'section', 'order', 'metadata']),
            );

            return response()->json([
                'success' => true,
                'message' => 'تم التحديث بنجاح',
                'content' => $content,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحفظ',
            ], 500);
        }
    }

    /**
     * حفظ محتوى متعدد دفعة واحدة
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'contents' => 'required|array',
            'contents.*.key' => 'required|string',
            'contents.*.value' => 'required',
        ]);

        try {
            // حفظ نسخة احتياطية قبل التحديث (فقط إذا كان هناك محتوى موجود)
            if (LandingContent::count() > 0) {
                LandingContent::createSnapshot();
            }

            foreach ($request->contents as $item) {
                LandingContent::setValue(
                    $item['key'],
                    $item['value'],
                    array_intersect_key($item, array_flip(['type', 'section', 'order', 'metadata'])),
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ جميع التغييرات',
            ]);
        } catch (\Exception $e) {
            \Log::error('Landing Content Bulk Update Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ',
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * رفع صورة
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'key' => 'required|string',
        ]);

        try {
            $path = $request->file('image')->store('landing-images', 'public');

            LandingContent::setValue(
                $request->key,
                $path,
                ['type' => 'image'],
            );

            return response()->json([
                'success' => true,
                'path' => asset('storage/app/public/data/' . $path),
                'message' => 'تم رفع الصورة بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل رفع الصورة',
            ], 500);
        }
    }

    /**
     * استرجاع نسخة سابقة
     */
    public function restoreVersion($versionId)
    {
        try {
            $version = \DB::table('landing_content_versions')->find($versionId);

            if (! $version) {
                return response()->json([
                    'success' => false,
                    'message' => 'النسخة غير موجودة',
                ], 404);
            }

            // حفظ نسخة احتياطية قبل الاسترجاع
            LandingContent::createSnapshot();

            // حذف المحتوى الحالي
            LandingContent::truncate();

            // استرجاع المحتوى القديم
            $oldContent = json_decode($version->content_snapshot, true);
            foreach ($oldContent as $item) {
                LandingContent::create($item);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم استرجاع النسخة بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل الاسترجاع',
            ], 500);
        }
    }

    /**
     * حفظ تخطيط الصفحة الكامل (المحرّر المرئي المدمج).
     *
     * يستقبل لقطة HTML لمحتوى <main> بعد التعديل، يُعقّمها خادِمياً
     * (إزالة سكربتات/معالِجات أحداث/أدوات التحرير) ثم يخزّنها. تلتقط
     * التعديلات البنيوية (سحب/حذف/تكرار/ترتيب) والأنماط والنصوص معاً.
     */
    public function saveLayout(Request $request)
    {
        $request->validate([
            'html' => 'required|string|max:600000', // سقف ~600KB يمنع إغراق التخزين
        ]);

        try {
            $layout = LandingLayout::store($request->input('html'));

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ التخطيط بنجاح',
                'length' => mb_strlen((string) $layout->html),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Landing layout save error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ التخطيط',
            ], 500);
        }
    }

    /**
     * استعادة القالب الافتراضي — حذف التخطيط المخصّص (صمام أمان).
     */
    public function resetLayout()
    {
        try {
            LandingLayout::reset();

            return response()->json([
                'success' => true,
                'message' => 'تمت استعادة القالب الافتراضي',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Landing layout reset error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل استعادة القالب الافتراضي',
            ], 500);
        }
    }
}
