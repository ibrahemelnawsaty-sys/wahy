<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * رفع صور محرر النصوص الغني (الدرس / الأنشطة).
 * يحفظ تحت storage/app/public/editor/{yyyy}/{mm}/ ويُرجع URL عام.
 */
class EditorUploadController extends Controller
{
    private const ALLOWED_MIME = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        // SVG مُزال عمداً: يسمح بـ XSS مخزّن عبر سكربت مضمّن
    ];

    private const MAX_BYTES = 5 * 1024 * 1024; // 5 MB

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|max:5120|mimetypes:image/jpeg,image/png,image/gif,image/webp',
        ], [
            'image.required'   => 'الرجاء اختيار صورة',
            'image.file'       => 'الملف غير صالح',
            'image.max'        => 'الحد الأقصى لحجم الصورة 5MB',
            'image.mimetypes'  => 'الأنواع المسموحة: JPG, PNG, GIF, WEBP, SVG',
        ]);

        $file = $request->file('image');

        if (!in_array($file->getMimeType(), self::ALLOWED_MIME, true)) {
            return response()->json([
                'success' => false,
                'message' => 'نوع الصورة غير مدعوم',
            ], 422);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            return response()->json([
                'success' => false,
                'message' => 'الصورة تتجاوز الحد المسموح (5MB)',
            ], 422);
        }

        $ext      = $file->getClientOriginalExtension() ?: $file->guessExtension();
        $filename = now()->format('His') . '_' . Str::random(10) . '.' . strtolower($ext);
        $path     = 'editor/' . now()->format('Y/m');

        $stored = $file->storeAs($path, $filename, 'public');

        return response()->json([
            'success'  => true,
            'url'      => Storage::url($stored),
            'filename' => $filename,
            'size'     => $file->getSize(),
        ]);
    }
}
