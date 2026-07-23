@extends('layouts.admin')

@section('title', 'تفاصيل النشاط المميّز')

@section('content')
<style>
    .fad-page { max-width: 1080px; margin-inline: auto; }
    .fad-back { display: inline-flex; align-items: center; gap: 8px; color: #64748b; text-decoration: none; font-weight: 700; font-size: 14px; margin-bottom: 18px; }
    .fad-back:hover { color: #334155; }

    .fad-hero { background: linear-gradient(135deg, #f59e0b 0%, #f97316 55%, #ef4444 100%); border-radius: 18px; padding: 26px 28px; color: #fff; box-shadow: 0 10px 30px rgba(245,158,11,0.32); margin-bottom: 22px; }
    .fad-hero-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 999px; font-size: 12.5px; font-weight: 700; margin-bottom: 12px; }
    .fad-hero h1 { font-size: 25px; font-weight: 800; margin: 0 0 6px; }
    .fad-hero .fad-hero-meta { opacity: 0.92; font-size: 14px; }

    .fad-card { background: #fff; border-radius: 16px; padding: 22px 24px; box-shadow: 0 4px 18px rgba(15,23,42,0.06); border: 1px solid #f1f5f9; margin-bottom: 20px; }
    .fad-card h3 { font-size: 17px; font-weight: 800; color: #1e293b; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }

    .fad-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
    .fad-item { background: #f8fafc; border-radius: 12px; padding: 12px 14px; }
    .fad-label { font-size: 12px; color: #94a3b8; font-weight: 700; margin-bottom: 4px; }
    .fad-value { font-size: 14.5px; color: #1e293b; font-weight: 700; }

    .fad-feature-note { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px 16px; }
    .fad-feature-note .fad-label { color: #b45309; }
    .fad-feature-note .fad-value { color: #92400e; font-weight: 700; }

    /* وصف النشاط الغنيّ */
    .fad-desc.rich-content { text-align: right; color: #334155; line-height: 1.8; }
    .fad-desc.rich-content p { margin-bottom: 12px; }
    .fad-desc.rich-content img { max-width: 100%; height: auto; border-radius: 10px; margin: 12px 0; }
    .fad-desc.rich-content a { color: #2563eb; text-decoration: underline; }
    .fad-desc.rich-content ul, .fad-desc.rich-content ol { padding-right: 24px; margin-bottom: 12px; }
    .fad-desc.rich-content [style*="background"] { background: transparent !important; }
    .fad-desc.rich-content [style*="color"] { color: inherit !important; }

    /* تسليمات الطلاب */
    .fad-sub { border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 18px; margin-bottom: 14px; }
    .fad-sub-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
    .fad-student { display: flex; align-items: center; gap: 10px; }
    .fad-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg,#667eea,#764ba2); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; }
    .fad-student-name { font-weight: 800; color: #1e293b; font-size: 14.5px; }
    .fad-student-date { font-size: 12px; color: #94a3b8; }
    .fad-status { font-size: 12.5px; font-weight: 700; padding: 4px 12px; border-radius: 999px; }
    .fad-status.approved { background: #dcfce7; color: #15803d; }
    .fad-status.pending { background: #fef9c3; color: #a16207; }
    .fad-status.rejected { background: #fee2e2; color: #b91c1c; }
    .fad-score { font-size: 13px; font-weight: 800; color: #7c3aed; }

    .fad-answer { background: #f8fafc; border-radius: 10px; padding: 12px 14px; color: #334155; white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere; font-size: 14px; }
    .fad-answer a { color: #2563eb; text-decoration: underline; word-break: break-all; }
    .fad-link-box { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: rgba(37,99,235,0.08); border: 1px solid rgba(37,99,235,0.30); border-radius: 10px; color: #2563eb; text-decoration: none; font-weight: 700; word-break: break-all; }
    .fad-file { display: inline-flex; align-items: center; gap: 8px; margin-top: 10px; padding: 10px 16px; background: #eff6ff; border-radius: 10px; }
    .fad-file a { color: #2563eb; font-weight: 700; text-decoration: none; }

    .fad-io { display: flex; flex-wrap: wrap; gap: 14px; }
    .fad-io-item { position: relative; width: 130px; }
    .fad-io-item img { width: 130px; height: 130px; object-fit: contain; background: #f1f5f9; border-radius: 12px; border: 2px solid rgba(102,126,234,0.30); display: block; }
    .fad-io-order { position: absolute; top: -8px; inset-inline-start: -8px; width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg,#667eea,#764ba2); color: #fff; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(0,0,0,0.2); }

    .fad-empty { text-align: center; padding: 30px; color: #94a3b8; }
    .fad-empty-icon { font-size: 40px; margin-bottom: 8px; }
</style>

<div class="fad-page">

    <a href="{{ route('admin.featured-activities') }}" class="fad-back">
        <span>←</span> الأنشطة المميّزة
    </a>

    {{-- Hero --}}
    <div class="fad-hero">
        <div class="fad-hero-badge">⭐ نشاط مميّز</div>
        <h1>{{ $activity->title }}</h1>
        <div class="fad-hero-meta">
            ميّزه: {{ $activity->featuredBy->name ?? 'غير محدد' }}
            @if($activity->featured_at) · {{ $activity->featured_at->diffForHumans() }} @endif
        </div>
    </div>

    {{-- معلومات النشاط --}}
    <div class="fad-card">
        <h3>📋 معلومات النشاط</h3>
        <div class="fad-grid">
            <div class="fad-item">
                <div class="fad-label">القيمة</div>
                <div class="fad-value">{{ $activity->lesson?->concept?->value?->name ?? '—' }}</div>
            </div>
            <div class="fad-item">
                <div class="fad-label">المفهوم</div>
                <div class="fad-value">{{ $activity->lesson?->concept?->name ?? '—' }}</div>
            </div>
            <div class="fad-item">
                <div class="fad-label">الدرس</div>
                <div class="fad-value">{{ $activity->lesson?->title ?? '—' }}</div>
            </div>
            <div class="fad-item">
                <div class="fad-label">النقاط</div>
                <div class="fad-value">{{ $activity->points ?? 0 }} نقطة</div>
            </div>
            <div class="fad-item">
                <div class="fad-label">المنشئ</div>
                <div class="fad-value">{{ $activity->creator->name ?? '—' }}</div>
            </div>
            <div class="fad-item">
                <div class="fad-label">عدد التسليمات</div>
                <div class="fad-value">{{ $activity->submissions->count() }}</div>
            </div>
        </div>

        @if(trim((string) $activity->featured_reason) !== '')
            <div class="fad-feature-note" style="margin-top:16px;">
                <div class="fad-label">سبب التمييز</div>
                <div class="fad-value">{{ $activity->featured_reason }}</div>
            </div>
        @endif
    </div>

    {{-- وصف النشاط --}}
    @php $descHtml = trim(safe_html(normalize_message_html($activity->description))); @endphp
    @if($descHtml !== '')
        <div class="fad-card">
            <h3>📝 وصف النشاط</h3>
            <div class="fad-desc rich-content">{!! $descHtml !!}</div>
        </div>
    @endif

    {{-- تسليمات الطلاب — كما يستعرضها المعلّم --}}
    <div class="fad-card">
        <h3>👥 تسليمات الطلاب ({{ $activity->submissions->count() }})</h3>

        @forelse($activity->submissions as $submission)
            @php
                // تحليل إجابة الطالب (نصّ/ملف/ترتيب صور/كويز) — مطابق لصفحة مراجعة المعلّم
                $raw = $submission->answer;
                $decoded = null;
                if (is_string($raw)) {
                    $tmp = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE) { $decoded = $tmp; }
                } elseif (is_array($raw)) {
                    $decoded = $raw;
                }

                $answerNote = null; $answerFile = null; $answerFileUrl = null; $imageOrderItems = [];

                if (is_array($decoded)) {
                    $isList = array_is_list($decoded);
                    if ($isList) {
                        foreach ($decoded as $it) {
                            if (is_array($it) && ! empty($it['image_url']) && preg_match('~^https?://~i', (string) $it['image_url'])) {
                                $imageOrderItems[] = ['url' => (string) $it['image_url'], 'order' => $it['selected_order'] ?? ($it['order'] ?? null)];
                            }
                        }
                    }
                    if (! empty($imageOrderItems)) {
                        usort($imageOrderItems, fn ($a, $b) => ((int) ($a['order'] ?? 0)) <=> ((int) ($b['order'] ?? 0)));
                    } elseif (array_key_exists('note', $decoded) || array_key_exists('file', $decoded)) {
                        $answerNote = is_scalar($decoded['note'] ?? null) ? $decoded['note'] : null;
                        $answerFile = $decoded['file'] ?? null;
                    } elseif ($isList) {
                        $answerNote = implode('، ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE), $decoded));
                    } else {
                        $lines = [];
                        foreach ($decoded as $k => $v) {
                            $val = is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE);
                            $lines[] = (is_numeric($k) ? ('السؤال ' . ((int) $k + 1) . ': ') : ($k . ': ')) . $val;
                        }
                        $answerNote = implode("\n", $lines);
                    }
                } else {
                    $answerNote = is_string($raw) ? $raw : null;
                }

                if (! $answerFile && $submission->file_path) { $answerFile = $submission->file_path; }
                // بناء رابط الملف من مسار تخزين التطبيق فقط (رفض :// و.. — قد يُحقَن عبر API)
                $answerExt = '';
                if (is_string($answerFile) && $answerFile !== '' && ! preg_match('~://|\.\.~', $answerFile)) {
                    $answerFileUrl = asset('storage/app/public/data/' . ltrim($answerFile, '/'));
                    $answerExt = strtolower(pathinfo($answerFile, PATHINFO_EXTENSION));
                } else {
                    $answerFile = null;
                }
                $answerFileKind = in_array($answerExt, ['jpg','jpeg','png','gif','webp','bmp','svg'], true) ? 'image'
                    : (in_array($answerExt, ['mp3','wav','ogg','m4a','aac'], true) ? 'audio'
                    : (in_array($answerExt, ['mp4','mov','webm','avi','mkv'], true) ? 'video' : 'file'));
                $answerIsUrl = ($answerNote !== null && preg_match('~^\s*https?://\S+\s*$~i', $answerNote) === 1);
                $status = $submission->status ?? 'pending';
                $statusClass = in_array($status, ['approved', 'pending', 'rejected'], true) ? $status : 'pending';
                $statusLabel = ['approved' => 'مقبول', 'pending' => 'قيد المراجعة', 'rejected' => 'مرفوض', 'needs_review' => 'يحتاج مراجعة'][$status] ?? $status;
            @endphp

            <div class="fad-sub">
                <div class="fad-sub-head">
                    <div class="fad-student">
                        <div class="fad-avatar">{{ mb_substr($submission->student->name ?? '؟', 0, 1, 'UTF-8') }}</div>
                        <div>
                            <div class="fad-student-name">{{ $submission->student->name ?? 'طالب محذوف' }}</div>
                            <div class="fad-student-date">{{ $submission->submitted_at?->format('Y-m-d H:i') ?? '—' }}</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        @if($submission->score !== null)<span class="fad-score">⭐ {{ $submission->score }}/100</span>@endif
                        <span class="fad-status {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>

                @if(! empty($imageOrderItems))
                    <div class="fad-io">
                        @foreach($imageOrderItems as $it)
                            <div class="fad-io-item">
                                <span class="fad-io-order">{{ $it['order'] ?? ($loop->index + 1) }}</span>
                                <a href="{{ $it['url'] }}" target="_blank" rel="noopener noreferrer" title="فتح الصورة">
                                    <img src="{{ $it['url'] }}" alt="صورة {{ $loop->index + 1 }}" loading="lazy">
                                </a>
                            </div>
                        @endforeach
                    </div>
                @elseif($answerIsUrl)
                    <a href="{{ trim($answerNote) }}" target="_blank" rel="noopener noreferrer" class="fad-link-box">🔗 فتح رابط الطالب</a>
                @elseif($answerNote !== null && $answerNote !== '')
                    <div class="fad-answer">{!! nl2br(e(\Illuminate\Support\Str::limit($answerNote, 3000))) !!}</div>
                @elseif(! $answerFile)
                    <div class="fad-answer" style="color:#94a3b8;">لم يقدّم الطالب إجابة نصّية</div>
                @endif

                @if($answerFile && $answerFileUrl)
                    <div class="fad-file" style="display:block;">
                        <div style="margin-bottom:8px;font-weight:700;">📎 مرفق الطالب</div>
                        @if($answerFileKind === 'image')
                            <a href="{{ $answerFileUrl }}" target="_blank" rel="noopener noreferrer" title="فتح الصورة كاملة"><img src="{{ $answerFileUrl }}" alt="مرفق" loading="lazy" style="max-width:100%;max-height:400px;border-radius:10px;border:1px solid #e2e8f0;display:block;"></a>
                        @elseif($answerFileKind === 'audio')
                            <audio controls preload="metadata" src="{{ $answerFileUrl }}" style="width:100%;max-width:420px;"></audio>
                        @elseif($answerFileKind === 'video')
                            <video controls preload="metadata" src="{{ $answerFileUrl }}" style="width:100%;max-width:520px;border-radius:10px;"></video>
                        @else
                            <a href="{{ $answerFileUrl }}" target="_blank" rel="noopener noreferrer" download>فتح / تحميل ملف الطالب</a>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="fad-empty">
                <div class="fad-empty-icon">📭</div>
                <p>لا توجد تسليمات لهذا النشاط بعد.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
