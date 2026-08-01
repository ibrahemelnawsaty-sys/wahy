<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PageBuilder\Models\MediaAsset;
use App\PageBuilder\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * مكتبة وسائط المحرّر الاحترافيّ (ت-٨) — رفع/سرد/حذف. محميّة can:access-admin.
 * الأمن: قائمة سماح mimes نقطيّة (لا SVG — ناقل XSS)، alt إلزاميّ، حدّ حجم.
 */
class MediaLibraryController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): JsonResponse
    {
        $assets = MediaAsset::query()->latest('id')->paginate(50);
        $assets->getCollection()->transform(fn (MediaAsset $a) => [
            'id' => $a->id,
            'url' => $a->url,
            'alt' => $a->alt,
            'mime' => $a->mime,
            'width' => $a->width,
            'height' => $a->height,
            'size' => $a->size,
        ]);

        return response()->json($assets);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            // قائمة سماح صريحة (لا SVG) + حدّ 10 ميجا. image يرفض غير الصور أصلاً.
            'file' => 'required|file|image|mimes:jpg,jpeg,png,webp,gif|max:10240',
            'alt' => 'required|string|max:255',
        ]);

        // دفاع ثانٍ: نوع MIME الحقيقيّ ضمن القائمة (يمنع تزييف الامتداد).
        if (! in_array($request->file('file')->getMimeType(), MediaService::ALLOWED_MIMES, true)) {
            return response()->json(['success' => false, 'message' => 'نوع ملفّ غير مسموح.'], 422);
        }

        $asset = $this->media->store($request->file('file'), $request->input('alt'), $request->user()?->id);

        return response()->json([
            'success' => true,
            'asset' => ['id' => $asset->id, 'url' => $asset->url, 'alt' => $asset->alt],
        ], 201);
    }

    public function destroy(MediaAsset $medium): JsonResponse
    {
        $this->media->delete($medium);

        return response()->json(['success' => true]);
    }
}
