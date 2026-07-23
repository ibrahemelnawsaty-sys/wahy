@extends('layouts.admin')

@section('title', 'التسليمات المميّزة')

@section('content')
<style>
    .fs-page { max-width: 1100px; margin-inline: auto; }
    .fs-hero { background: linear-gradient(135deg, #f59e0b 0%, #f97316 50%, #ef4444 100%); border-radius: 18px; padding: 28px 30px; margin-bottom: 22px; color: #fff; box-shadow: 0 10px 30px rgba(245,158,11,0.32); }
    .fs-hero-icon { width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 12px; }
    .fs-hero h1 { font-size: 26px; font-weight: 800; margin: 0 0 6px; }
    .fs-hero p { opacity: 0.92; font-size: 15px; margin: 0; }

    .fs-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .fs-stat { background: #fff; border-radius: 16px; padding: 20px 22px; box-shadow: 0 4px 16px rgba(15,23,42,0.06); border: 1px solid #f1f5f9; }
    .fs-stat .n { font-size: 30px; font-weight: 800; color: #1e293b; }
    .fs-stat .l { font-size: 13.5px; color: #64748b; font-weight: 700; margin-top: 4px; }

    .fs-card { background: #fff; border-radius: 16px; padding: 20px 22px; box-shadow: 0 4px 18px rgba(15,23,42,0.06); border: 1px solid #f1f5f9; margin-bottom: 16px; }
    .fs-card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
    .fs-student { display: flex; align-items: center; gap: 12px; }
    .fs-avatar { width: 46px; height: 46px; border-radius: 50%; background: linear-gradient(135deg,#667eea,#764ba2); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; }
    .fs-student-name { font-weight: 800; color: #1e293b; font-size: 16px; }
    .fs-student-meta { font-size: 12.5px; color: #94a3b8; }
    .fs-score { font-size: 14px; font-weight: 800; color: #7c3aed; background: #f5f3ff; padding: 5px 12px; border-radius: 999px; }

    .fs-activity { display: inline-flex; align-items: center; gap: 8px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 6px 14px; border-radius: 10px; font-weight: 700; font-size: 13.5px; margin-bottom: 12px; }
    .fs-value-badge { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }

    .fs-answer { background: #f8fafc; border-radius: 10px; padding: 12px 14px; color: #334155; white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere; font-size: 14px; }
    .fs-io { display: flex; flex-wrap: wrap; gap: 12px; }
    .fs-io-item { position: relative; width: 120px; }
    .fs-io-item img { width: 120px; height: 120px; object-fit: contain; background: #f1f5f9; border-radius: 12px; border: 2px solid rgba(102,126,234,0.30); display: block; }
    .fs-io-order { position: absolute; top: -8px; inset-inline-start: -8px; width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg,#667eea,#764ba2); color: #fff; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; }
    .fs-file-link { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #fff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 10px; font-weight: 700; text-decoration: none; word-break: break-all; }

    .fs-reason { margin-top: 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 12px 14px; }
    .fs-reason .fs-reason-l { font-size: 12px; color: #b45309; font-weight: 700; margin-bottom: 3px; }
    .fs-reason .fs-reason-v { color: #92400e; font-weight: 700; font-size: 14px; }
    .fs-card-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9; }
    .fs-foot-meta { font-size: 12.5px; color: #94a3b8; }
    .fs-unfeature { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 8px 16px; border-radius: 10px; font-weight: 700; font-size: 13.5px; cursor: pointer; }
    .fs-unfeature:hover { background: #fecaca; }

    .fs-empty { text-align: center; padding: 50px 20px; background: #fff; border-radius: 16px; border: 1px solid #f1f5f9; }
    .fs-empty-icon { font-size: 48px; margin-bottom: 10px; }
</style>

<div class="fs-page">

    <div class="fs-hero">
        <div class="fs-hero-icon">⭐</div>
        <h1>التسليمات المميّزة</h1>
        <p>أعمالُ الطلاب المتميّزة التي ميّزها المعلّمون — للاطّلاع والاستفادة في التقارير وتكريم الطلاب.</p>
    </div>

    <div class="fs-stats">
        <div class="fs-stat"><div class="n">{{ $stats['total_featured'] }}</div><div class="l">إجمالي التسليمات المميّزة</div></div>
        <div class="fs-stat"><div class="n">{{ $stats['this_month'] }}</div><div class="l">مميّزة هذا الشهر</div></div>
        <div class="fs-stat"><div class="n">{{ $stats['students'] }}</div><div class="l">عدد الطلاب المتميّزين</div></div>
    </div>

    @forelse($submissions as $submission)
        @php
            // تحليل إجابة الطالب (نصّ/ترتيب صور/رفع ملف) — بناء رابط الملف من التخزين بأمان.
            $raw = $submission->answer;
            $decoded = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : null);
            $note = null; $file = null; $imgs = [];
            if (is_array($decoded)) {
                $isList = array_is_list($decoded);
                if ($isList) {
                    foreach ($decoded as $it) {
                        if (is_array($it) && ! empty($it['image_url']) && preg_match('~^https?://~i', (string) $it['image_url'])) {
                            $imgs[] = ['url' => (string) $it['image_url'], 'order' => $it['selected_order'] ?? ($it['order'] ?? null)];
                        }
                    }
                }
                if (! empty($imgs)) {
                    usort($imgs, fn ($a, $b) => ((int) ($a['order'] ?? 0)) <=> ((int) ($b['order'] ?? 0)));
                } elseif (array_key_exists('note', $decoded) || array_key_exists('file', $decoded)) {
                    $note = is_scalar($decoded['note'] ?? null) ? (string) $decoded['note'] : null;
                    $file = is_string($decoded['file'] ?? null) ? $decoded['file'] : null;
                } elseif ($isList) {
                    $note = implode('، ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE), $decoded));
                } else {
                    $lines = [];
                    foreach ($decoded as $k => $v) { $lines[] = (is_numeric($k) ? ('السؤال ' . ((int) $k + 1) . ': ') : ($k . ': ')) . (is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE)); }
                    $note = implode("\n", $lines);
                }
            } elseif (is_string($raw)) { $note = $raw; }
            if (! $file && $submission->file_path) { $file = $submission->file_path; }
            $fileUrl = null; $ext = '';
            if (is_string($file) && $file !== '' && ! preg_match('~://|\.\.~', $file)) {
                $fileUrl = asset('storage/app/public/data/' . ltrim($file, '/'));
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            }
            $kind = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg'], true) ? 'image'
                : (in_array($ext, ['mp3','wav','ogg','m4a','aac'], true) ? 'audio'
                : (in_array($ext, ['mp4','mov','webm','avi','mkv'], true) ? 'video' : 'file'));
        @endphp

        <div class="fs-card">
            <div class="fs-card-head">
                <div class="fs-student">
                    <div class="fs-avatar">{{ mb_substr($submission->student->name ?? '؟', 0, 1, 'UTF-8') }}</div>
                    <div>
                        <div class="fs-student-name">{{ $submission->student->name ?? 'طالب محذوف' }}</div>
                        <div class="fs-student-meta">{{ $submission->submitted_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                </div>
                @if($submission->score !== null)
                    <span class="fs-score">⭐ الدرجة: {{ $submission->score }}%</span>
                @endif
            </div>

            <div>
                <span class="fs-activity">📘 {{ $submission->activity?->title ?? 'نشاط محذوف' }}</span>
                @if($submission->activity?->lesson?->concept?->value?->name)
                    <span class="fs-value-badge">{{ $submission->activity->lesson->concept->value->name }}</span>
                @endif
            </div>

            {{-- محتوى التسليم --}}
            <div style="margin-top:12px;">
                @if(! empty($imgs))
                    <div class="fs-io">
                        @foreach($imgs as $it)
                            <div class="fs-io-item">
                                <span class="fs-io-order">{{ $it['order'] ?? ($loop->index + 1) }}</span>
                                <a href="{{ $it['url'] }}" target="_blank" rel="noopener noreferrer"><img src="{{ $it['url'] }}" alt="صورة {{ $loop->index + 1 }}" loading="lazy"></a>
                            </div>
                        @endforeach
                    </div>
                @elseif($note !== null && trim($note) !== '')
                    <div class="fs-answer">{!! nl2br(e(\Illuminate\Support\Str::limit($note, 2000))) !!}</div>
                @elseif(! $file)
                    <div class="fs-answer" style="color:#94a3b8;">لا توجد إجابة نصّية</div>
                @endif

                @if($file && $fileUrl)
                    <div style="margin-top:12px;">
                        @if($kind === 'image')
                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer"><img src="{{ $fileUrl }}" alt="مرفق" loading="lazy" style="max-width:100%;max-height:360px;border-radius:12px;border:1px solid #e5e7eb;display:block;"></a>
                        @elseif($kind === 'audio')
                            <audio controls preload="metadata" src="{{ $fileUrl }}" style="width:100%;max-width:420px;"></audio>
                        @elseif($kind === 'video')
                            <video controls preload="metadata" src="{{ $fileUrl }}" style="width:100%;max-width:520px;border-radius:12px;"></video>
                        @else
                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" download class="fs-file-link"><span>📎</span> فتح / تحميل مرفق الطالب</a>
                        @endif
                    </div>
                @endif
            </div>

            @if(trim((string) $submission->featured_reason) !== '')
                <div class="fs-reason">
                    <div class="fs-reason-l">سبب التمييز</div>
                    <div class="fs-reason-v">{{ $submission->featured_reason }}</div>
                </div>
            @endif

            <div class="fs-card-foot">
                <div class="fs-foot-meta">
                    ميّزه: {{ $submission->featuredBy->name ?? 'غير محدد' }}
                    @if($submission->featured_at) · {{ $submission->featured_at->diffForHumans() }} @endif
                </div>
                <form action="{{ route('admin.featured-activities.unfeature', $submission->id) }}" method="POST"
                      onsubmit="return confirm('إلغاء تمييز هذا التسليم؟');">
                    @csrf
                    <button type="submit" class="fs-unfeature">✕ إلغاء التمييز</button>
                </form>
            </div>
        </div>
    @empty
        <div class="fs-empty">
            <div class="fs-empty-icon">⭐</div>
            <h3 style="font-size:20px;font-weight:700;color:#475569;margin:0 0 6px;">لا توجد تسليمات مميّزة بعد</h3>
            <p style="color:#94a3b8;margin:0;font-size:14px;">حين يميّز المعلّمون أعمالَ طلابٍ متميّزة، تظهر هنا للاطّلاع والتكريم.</p>
        </div>
    @endforelse

    @if($submissions->hasPages())
        <div style="margin-top:20px;">{{ $submissions->links() }}</div>
    @endif

</div>
@endsection
