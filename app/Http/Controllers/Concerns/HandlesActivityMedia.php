<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * جمع «الوسائط المتعددة» المرفوعة (فيديو/صوت/صورة/مستند) وتخزينها في القرص العامّ.
 * منطقٌ موحّد يستعمله متحكّم أنشطة الأدمن (ActivityManagementController) — مطابق تمامًا لما
 * يفعله TeacherController كي تظهر وسائط الأدمن للطالب بنفس القالب (activities/partials/media).
 */
trait HandlesActivityMedia
{
    /**
     * يجمع ملفّات الوسائط من مدخلات النموذج (image[]/audio[]/video[]/document[]/attachment[]) —
     * يقبل المفرد والمتعدّد — ويعيد مصفوفة [{type, path, name}] بعد التحقّق والتخزين.
     */
    protected function collectUploadedActivityMedia(Request $request): array
    {
        $specs = [
            // نستخدم mimes: (فحص بالامتداد ↔ خريطة MIME) لا mimetypes: (فحص MIME خام صارم) —
            // فكثير من ملفّات mp4/mov السليمة يُكتشَف MIMEها بصيَغ مختلفة (video/mp4 مقابل
            // application/octet-stream…) فتُرفَض بصمت. mimes أوسع وأمتن للرفع الحقيقيّ.
            'video' => ['rule' => 'mimes:mp4,m4v,mov,avi,webm,mkv,ogv,3gp,3g2,mpeg,mpg', 'max' => 102400, 'type' => 'video'],
            'image' => ['rule' => 'image', 'max' => 10240, 'type' => 'image'],
            'audio' => ['rule' => 'mimes:mp3,wav,ogg,oga,m4a,aac,weba,opus,mpga', 'max' => 20480, 'type' => 'audio'],
            'document' => ['rule' => 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', 'max' => 20480, 'type' => 'document'],
            // مدخل عامّ (name="attachment[]") — يُستنتَج النوع من الامتداد
            'attachment' => ['rule' => 'mimes:mp4,mov,avi,webm,m4v,mp3,wav,ogg,m4a,aac,jpg,jpeg,png,gif,webp,pdf,doc,docx,ppt,pptx,xls,xlsx', 'max' => 102400, 'type' => null],
        ];

        $media = [];
        foreach ($specs as $field => $spec) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $files = $request->file($field);
            $isMulti = is_array($files);

            $request->validate([
                ($isMulti ? "{$field}.*" : $field) => "file|{$spec['rule']}|max:{$spec['max']}",
            ]);

            foreach (($isMulti ? $files : [$files]) as $file) {
                $media[] = [
                    'type' => $spec['type'] ?? $this->guessMediaType($file->getClientOriginalExtension()),
                    'path' => $file->store('activity-media', 'public'),
                    'name' => $file->getClientOriginalName(),
                ];
            }
        }

        return $media;
    }

    /**
     * يدمج الوسائط الحالية (بعد حذف ما طُلب حذفه عبر remove_media[]) مع المرفوعة الجديدة —
     * يُستعمل عند التعديل. يعيد null إن لم يتغيّر شيء (فلا يُكتب عمود media).
     */
    protected function mergeActivityMedia(Request $request, array $existing): ?array
    {
        $removeIdx = array_map('intval', (array) $request->input('remove_media', []));
        $newMedia = $this->collectUploadedActivityMedia($request);

        if (empty($removeIdx) && empty($newMedia)) {
            return null; // لا تغيير
        }

        $kept = [];
        foreach ($existing as $i => $item) {
            if (in_array($i, $removeIdx, true)) {
                if (! empty($item['path']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($item['path'])) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($item['path']);
                }

                continue;
            }
            $kept[] = $item;
        }

        return array_values(array_merge($kept, $newMedia));
    }

    /**
     * يستنتج نوع الوسيط من الامتداد (للمدخل العامّ attachment).
     */
    protected function guessMediaType(string $ext): string
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['mp4', 'mov', 'avi', 'webm', 'm4v', 'ogv'], true)) {
            return 'video';
        }
        if (in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac'], true)) {
            return 'audio';
        }
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
            return 'image';
        }

        return 'document';
    }
}
