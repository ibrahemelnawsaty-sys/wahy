<?php

namespace App\PageBuilder;

use App\PageBuilder\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * مكتبة الوسائط الموحّدة (ت-٨) — بديل الرفع المبعثر. تخزّن على القرص العامّ وتُسجّل صفّاً في pb_media.
 * الأمن: SVG مرفوض (قد يحمل سكربتاً) — يُفرَض في المتحكّم أيضاً؛ alt إلزاميّ منطقيّاً (يُفرَض بالتطبيق).
 */
class MediaService
{
    /** الامتدادات النقطيّة المسموحة فقط (لا SVG). */
    public const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function store(UploadedFile $file, ?string $alt, ?int $userId): MediaAsset
    {
        $path = $file->store('pb-media', 'public');

        [$width, $height] = $this->dimensions($path);

        return MediaAsset::create([
            'disk' => 'public',
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'alt' => $alt !== null ? mb_substr(trim($alt), 0, 255) : null,
            'created_by' => $userId,
        ]);
    }

    public function delete(MediaAsset $asset): void
    {
        Storage::disk($asset->disk ?: 'public')->delete($asset->path);
        $asset->delete();
    }

    /** أبعاد الصورة من القرص (أو [null,null] إن تعذّر). */
    private function dimensions(string $path): array
    {
        try {
            $full = Storage::disk('public')->path($path);
            $info = @getimagesize($full);

            return [$info[0] ?? null, $info[1] ?? null];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }
}
