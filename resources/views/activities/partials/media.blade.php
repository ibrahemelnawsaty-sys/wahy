{{--
    عارض «الوسائط المتعددة» المرفقة بالنشاط — يعرض عدّة ملفّات (فيديو/صوت/صورة/مستند) من عمود
    media (مصفوفة [{type, path, name}])، مع توافق خلفيّ لعمود attachment المفرد القديم.
    يُضمَّن في: التفاصيل الموحّدة، مراجعة الأدمن، صفحة حلّ الطالب، ونموذج التعديل.
    يتطلّب المتغيّر: $activity.
--}}
@php
    $__mediaItems = [];
    if (is_array($activity->media ?? null)) {
        foreach ($activity->media as $__m) {
            if (! empty($__m['path'])) {
                $__mediaItems[] = ['type' => $__m['type'] ?? null, 'path' => $__m['path'], 'name' => $__m['name'] ?? basename($__m['path'])];
            }
        }
    }
    // توافق خلفيّ: المرفق المفرد القديم
    if (empty($__mediaItems) && ! empty($activity->attachment)) {
        $__mediaItems[] = ['type' => null, 'path' => $activity->attachment, 'name' => basename($activity->attachment)];
    }
@endphp
@if(! empty($__mediaItems))
    <div class="activity-media-block" style="margin: 18px 0; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px;">
        <div style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 14px;">📎 الوسائط المرفقة ({{ count($__mediaItems) }})</div>
        <div style="display: flex; flex-direction: column; gap: 16px;">
            @foreach($__mediaItems as $__item)
                @php
                    $__att = $__item['path'];
                    $__url = \Illuminate\Support\Str::startsWith($__att, ['http://', 'https://', '/'])
                        ? $__att
                        : asset('storage/' . ltrim($__att, '/'));
                    $__ext = strtolower(pathinfo(parse_url($__att, PHP_URL_PATH) ?? $__att, PATHINFO_EXTENSION));
                    $__type = $__item['type'] ?? null;
                    if (! in_array($__type, ['video', 'audio', 'image', 'document'], true)) {
                        $__type = in_array($__ext, ['mp4', 'mov', 'avi', 'webm', 'm4v', 'ogv'], true) ? 'video'
                            : (in_array($__ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac'], true) ? 'audio'
                            : (in_array($__ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true) ? 'image' : 'document'));
                    }
                @endphp
                <div>
                    @if($__type === 'video')
                        <video controls preload="metadata" src="{{ $__url }}"
                               style="max-width: 100%; max-height: 440px; border-radius: 12px; background: #000; display: block;">
                            متصفّحك لا يدعم تشغيل الفيديو.
                        </video>
                    @elseif($__type === 'audio')
                        <audio controls src="{{ $__url }}" style="width: 100%;">متصفّحك لا يدعم تشغيل الصوت.</audio>
                    @elseif($__type === 'image')
                        <img src="{{ $__url }}" alt="{{ $__item['name'] }}"
                             style="max-width: 100%; max-height: 440px; border-radius: 12px; display: block;">
                    @else
                        <a href="{{ $__url }}" target="_blank" rel="noopener"
                           style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 18px; background: #eef2ff; color: #4338ca; border-radius: 10px; text-decoration: none; font-weight: 700;">
                            📄 {{ $__item['name'] }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
