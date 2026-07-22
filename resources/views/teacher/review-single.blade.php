@extends('layouts.teacher')

@section('title', 'تقييم النشاط')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/teacher-glass.css') }}?v={{ time() }}">
<style>
    /* صندوق «المكافأة التلقائية» — يتبع لون نصّ البطاقة (نهاراً/ليلاً) عبر color: inherit */
    .reward-info-box { background: rgba(102,126,234,0.10); border: 1px solid rgba(102,126,234,0.28); border-radius: 12px; padding: 16px 18px; margin-bottom: 22px; }
    .reward-info-title { display: flex; align-items: center; gap: 8px; font-weight: 800; margin-bottom: 8px; color: inherit; }
    .reward-info-text { margin: 0; font-size: 14px; line-height: 1.75; color: inherit; opacity: 0.92; }

    /* ===== عرض وصف النشاط الغنيّ (HTML من الأدمن/المعلّم) بشكل احترافي — مطابق لصفحة الطالب ===== */
    .activity-description.rich-content { text-align: right; }
    .activity-description.rich-content p { margin-bottom: 12px; }
    .activity-description.rich-content p:last-child { margin-bottom: 0; }
    .activity-description.rich-content img { max-width: 100%; height: auto; border-radius: 10px; margin: 12px 0; }
    .activity-description.rich-content a { color: #2563eb; text-decoration: underline; }
    .activity-description.rich-content ul, .activity-description.rich-content ol { padding-right: 24px; margin-bottom: 12px; }
    .activity-description.rich-content li { margin-bottom: 6px; }
    .activity-description.rich-content b, .activity-description.rich-content strong { font-weight: 700; }
    .activity-description.rich-content h1, .activity-description.rich-content h2,
    .activity-description.rich-content h3, .activity-description.rich-content h4 { margin-bottom: 10px; font-weight: 800; }
    /* إلغاء أي خلفية بيضاء/معتمة كتبها المؤلّف كي يبقى النص مقروءاً على البطاقة (حلّ «الخلفية البيضاء») */
    .activity-description.rich-content [style*="background"] { background: transparent !important; }
    /* تحييد ألوان النصّ المضمّنة (المحرّر يفترض لوناً داكناً) → يرث لون البطاقة المقروء في الوضعين */
    .activity-description.rich-content [style*="color"] { color: inherit !important; }
    html[data-theme="dark"] .activity-description.rich-content a { color: #60a5fa; }

    /* منع تجاوز النصّ الطويل (روابط/JSON) أفقياً — كان يدفع المحتوى ليعلو القائمة الجانبية */
    .review-layout > * { min-width: 0; }
    .student-answer { word-break: break-word; overflow-wrap: anywhere; white-space: pre-wrap; }
    .student-answer a { color: #2563eb; text-decoration: underline; word-break: break-all; }

    /* رابط الطالب كمربّع قابل للضغط */
    .answer-link-box { display: inline-flex; align-items: center; gap: 8px; padding: 12px 18px; background: rgba(37,99,235,0.08); border: 1px solid rgba(37,99,235,0.30); border-radius: 12px; color: #2563eb; text-decoration: none; font-weight: 700; word-break: break-all; }
    .answer-link-box:hover { background: rgba(37,99,235,0.15); }
    html[data-theme="dark"] .answer-link-box { color: #93c5fd; border-color: rgba(147,197,253,0.35); }

    /* عرض إجابة «ترتيب الصور» احترافياً — صور مصغّرة مرقّمة قابلة للضغط */
    .image-order-answer { display: flex; flex-wrap: wrap; gap: 16px; }
    .io-item { position: relative; width: 130px; }
    .io-item img { width: 150px; height: 150px; object-fit: contain; background: rgba(255,255,255,0.05); border-radius: 12px; border: 2px solid rgba(102,126,234,0.35); display: block; transition: transform .15s, border-color .15s; }
    .io-item img:hover { transform: translateY(-3px); border-color: #667eea; }
    .io-order { position: absolute; top: -8px; inset-inline-start: -8px; width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg,#667eea,#764ba2); color: #fff; font-weight: 800; font-size: 14px; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(0,0,0,0.25); z-index: 1; }
    @media (max-width: 640px) { .io-item, .io-item img { width: 104px; } .io-item img { height: 104px; } }
</style>
@endpush

@section('content')
<div class="teacher-glass-container">
    
    <!-- Back Button -->
    <a href="{{ route('teacher.review') }}" class="back-btn">
        <span class="back-icon">←</span>
        التقديمات المعلقة
    </a>

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">📋</div>
            <div>
                <h1 class="header-title">مراجعة النشاط</h1>
                <p class="header-subtitle">تقييم إجابة الطالب</p>
            </div>
        </div>
    </div>

    <div class="review-layout">
        
        <!-- Left: Submission Details -->
        <div class="submission-section glass-card">
            
            <!-- Student Info -->
            <div class="student-header">
                <div class="student-avatar-large">
                    @if($submission->student->avatar)
                        <img src="{{ $submission->student->avatar_url }}" alt="{{ $submission->student->name }}">
                    @else
                        <div class="avatar-placeholder-large">{{ mb_substr($submission->student->name, 0, 1, "UTF-8") }}</div>
                    @endif
                </div>
                <div>
                    <div class="student-name-large">{{ $submission->student->name }}</div>
                    <div class="submission-date">تم التقديم: {{ $submission->submitted_at?->format('Y-m-d H:i') ?? '-' }}</div>
                </div>
            </div>

            <!-- Activity Info -->
            <div class="info-section">
                <h3 class="section-title">معلومات النشاط</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">القيمة:</span>
                        <span class="info-value">{{ $submission->activity?->lesson?->concept?->value?->name ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">المفهوم:</span>
                        <span class="info-value">{{ $submission->activity?->lesson?->concept?->name ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">الدرس:</span>
                        <span class="info-value">{{ $submission->activity?->lesson?->title ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">النشاط:</span>
                        <span class="info-value">{{ $submission->activity?->title ?? 'نشاط محذوف' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">عدد المحاولات:</span>
                        <span class="info-value">{{ $submission->attempts ?? 1 }}@if($submission->activity?->max_attempts) / {{ $submission->activity->max_attempts }} مسموحة@endif</span>
                    </div>
                </div>
            </div>

            <!-- Activity Description -->
            <div class="info-section">
                <h3 class="section-title">وصف النشاط</h3>
                @php $descHtml = trim(safe_html(normalize_message_html($submission->activity?->description))); @endphp
                @if($descHtml !== '')
                    <div class="activity-description rich-content">{!! $descHtml !!}</div>
                @else
                    <div class="no-content">لا يوجد وصف لهذا النشاط</div>
                @endif
            </div>

            <!-- Student Answer -->
            @php
                // إجابة الطالب في العمود answer؛ قد تكون: نصّ، ملف {note,file}, ترتيب صور
                // [{image_url,selected_order}], قائمة (ترتيب كلمات/حروف)، أو كائن كويز.
                $raw = $submission->answer;
                $decoded = null;
                if (is_string($raw)) {
                    $tmp = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE) { $decoded = $tmp; }
                } elseif (is_array($raw)) {
                    $decoded = $raw;
                }

                $answerNote = null;
                $answerFile = null;
                $answerFileUrl = null;
                $imageOrderItems = [];

                if (is_array($decoded)) {
                    $isList = array_is_list($decoded);

                    // (1) ترتيب صور: قائمة عناصرها {image_url, selected_order}
                    if ($isList) {
                        foreach ($decoded as $it) {
                            // http(s) فقط — الإجابة من الطالب؛ نمنع javascript:/data: (XSS مخزّن على المعلّم)
                            if (is_array($it) && ! empty($it['image_url']) && preg_match('~^https?://~i', (string) $it['image_url'])) {
                                $imageOrderItems[] = [
                                    'url' => (string) $it['image_url'],
                                    'order' => $it['selected_order'] ?? ($it['order'] ?? null),
                                ];
                            }
                        }
                    }

                    if (! empty($imageOrderItems)) {
                        usort($imageOrderItems, fn ($a, $b) => ((int) ($a['order'] ?? 0)) <=> ((int) ($b['order'] ?? 0)));
                    } elseif (array_key_exists('note', $decoded) || array_key_exists('file', $decoded)) {
                        // (2) ملف مرفوع
                        $answerNote = is_scalar($decoded['note'] ?? null) ? $decoded['note'] : null;
                        $answerFile = $decoded['file'] ?? null;
                    } elseif ($isList) {
                        // (3) قائمة نصوص (ترتيب كلمات/حروف)
                        $answerNote = implode('، ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE), $decoded));
                    } else {
                        // (4) كائن كويز {رقم السؤال: الإجابة} → أسطر مقروءة
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

                // احتياط للعمود القديم file_path
                if (! $answerFile && $submission->file_path) {
                    $answerFile = $submission->file_path;
                }
                if ($answerFile) {
                    $answerFileUrl = \Illuminate\Support\Str::startsWith((string) $answerFile, 'http')
                        ? $answerFile
                        : asset('storage/app/public/data/' . ltrim((string) $answerFile, '/'));
                }

                // إجابة نصّية = رابط وحيد؟ نعرضه كمربّع قابل للضغط
                $answerIsUrl = ($answerNote !== null && preg_match('~^\s*https?://\S+\s*$~i', $answerNote) === 1);
            @endphp
            <div class="info-section">
                <h3 class="section-title">إجابة الطالب</h3>

                @if(! empty($imageOrderItems))
                    {{-- ترتيب الصور: صور مصغّرة مرقّمة بالترتيب الذي اختاره الطالب --}}
                    <div class="image-order-answer">
                        @foreach($imageOrderItems as $it)
                            <div class="io-item">
                                <span class="io-order">{{ $it['order'] ?? ($loop->index + 1) }}</span>
                                <a href="{{ $it['url'] }}" target="_blank" rel="noopener noreferrer" title="فتح الصورة">
                                    <img src="{{ $it['url'] }}" alt="صورة {{ $loop->index + 1 }}" loading="lazy">
                                </a>
                            </div>
                        @endforeach
                    </div>
                @elseif($answerIsUrl)
                    <a href="{{ trim($answerNote) }}" target="_blank" rel="noopener noreferrer" class="answer-link-box">🔗 فتح رابط الطالب</a>
                @elseif($answerNote !== null && $answerNote !== '')
                    <div class="student-answer">{!! nl2br(e(\Illuminate\Support\Str::limit($answerNote, 3000))) !!}</div>
                @endif

                @if($answerFile)
                    <div class="attached-file">
                        <span class="file-icon-large">📎</span>
                        <div>
                            <div class="file-label">ملف مرفق من الطالب</div>
                            <a href="{{ $answerFileUrl }}" target="_blank" class="file-link">فتح / تحميل الملف</a>
                        </div>
                    </div>
                @endif

                @if(empty($imageOrderItems) && ($answerNote === null || $answerNote === '') && ! $answerFile)
                    <div class="no-content">لم يقدم الطالب إجابة نصية أو ملف</div>
                @endif
            </div>

        </div>

        <!-- Right: Grading Form -->
        <div class="grading-section glass-card">
            <h3 class="section-title">تقييم الطالب</h3>

            <form id="gradingForm" class="grading-form">
                @csrf
                
                <!-- Score -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">⭐</span>
                        الدرجة (من 100)
                    </label>
                    <input type="number" name="score" class="form-input" min="0" max="100" required>
                    <div class="score-slider">
                        <input type="range" name="score_slider" min="0" max="100" value="50" class="slider">
                        <div class="slider-labels">
                            <span>0</span>
                            <span>50</span>
                            <span>100</span>
                        </div>
                    </div>
                </div>

                <!-- المكافأة محدَّدة مسبقاً بالنشاط وتُمنح تلقائياً حسب الدرجة — المعلّم يُقيّم فقط -->
                @php $activityBasePoints = (int) ($submission->activity?->points ?? 10); @endphp
                <div class="reward-info-box">
                    <div class="reward-info-title">
                        <span class="label-icon">🎁</span>
                        مكافأة النشاط (تلقائية)
                    </div>
                    <p class="reward-info-text">
                        نقاط هذا النشاط محدَّدة مسبقاً: <strong>{{ $activityBasePoints }} نقطة</strong>.
                        يحصل الطالب على النقاط والعملات <strong>تلقائياً بحسب الدرجة التي تمنحها</strong>،
                        تماماً كالأنشطة المُصحَّحة آلياً. أنت تُقيّم فقط — والنظام يوزّع المكافأة.
                    </p>
                </div>

                <!-- Feedback -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">💬</span>
                        ملاحظات للطالب (اختياري)
                    </label>
                    <textarea name="feedback" class="form-textarea" rows="4" placeholder="اكتب ملاحظاتك وتعليقاتك للطالب..."></textarea>
                </div>

                <!-- Quick Feedback Templates -->
                <div class="quick-feedback">
                    <div class="quick-label">ردود سريعة:</div>
                    <div class="quick-buttons">
                        <button type="button" class="quick-btn" onclick="setFeedback('ممتاز! أحسنت يا بطل 🌟')">ممتاز</button>
                        <button type="button" class="quick-btn" onclick="setFeedback('جيد جداً، واصل التقدم 👏')">جيد</button>
                        <button type="button" class="quick-btn" onclick="setFeedback('إجابة رائعة، أنت مبدع في هذا! 🏆')">إجابة رائعة</button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">
                    <span class="btn-icon">✅</span>
                    حفظ التقييم
                </button>

                <!-- Allow Retry Button -->
                <button type="button" id="allowRetryBtn" class="submit-btn" style="background:linear-gradient(135deg,#f59e0b,#d97706);margin-top:12px;">
                    <span class="btn-icon">🔄</span>
                    السماح للطالب بإعادة المحاولة
                </button>
                <div class="help-text" style="text-align:center;margin-top:6px;">يعيد النشاط للطالب بمجموعة محاولات جديدة</div>

                <div id="submitMessage" class="submit-message" style="display: none;"></div>
            </form>
        </div>

    </div>

</div>

<script>
// Sync slider with input
document.querySelector('input[name="score_slider"]').addEventListener('input', function() {
    document.querySelector('input[name="score"]').value = this.value;
});

document.querySelector('input[name="score"]').addEventListener('input', function() {
    document.querySelector('input[name="score_slider"]').value = this.value;
});

// Quick feedback
function setFeedback(text) {
    document.querySelector('textarea[name="feedback"]').value = text;
}

// Form submission
document.getElementById('gradingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const messageEl = document.getElementById('submitMessage');
    
    try {
        const response = await fetch('{{ route("teacher.review.submit", $submission->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageEl.textContent = '✅ ' + data.message;
            messageEl.className = 'submit-message success';
            messageEl.style.display = 'block';
            
            setTimeout(() => {
                window.location.href = '{{ route("teacher.review") }}';
            }, 1500);
        } else {
            throw new Error(data.error || data.message || 'حدث خطأ');
        }
    } catch (error) {
        messageEl.textContent = '❌ ' + error.message;
        messageEl.className = 'submit-message error';
        messageEl.style.display = 'block';
    }
});

// السماح بإعادة المحاولة
const allowRetryBtn = document.getElementById('allowRetryBtn');
if (allowRetryBtn) {
    allowRetryBtn.addEventListener('click', async function () {
        if (!confirm('السماح للطالب بإعادة محاولة هذا النشاط من جديد؟')) return;
        const messageEl = document.getElementById('submitMessage');
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('feedback', document.querySelector('textarea[name="feedback"]')?.value || '');
        try {
            const response = await fetch('{{ route("teacher.review.allow-retry", $submission->id) }}', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            if (data.success) {
                messageEl.textContent = '🔄 ' + data.message;
                messageEl.className = 'submit-message success';
                messageEl.style.display = 'block';
                setTimeout(() => { window.location.href = '{{ route("teacher.review") }}'; }, 1500);
            } else {
                throw new Error(data.error || data.message || 'حدث خطأ');
            }
        } catch (error) {
            messageEl.textContent = '❌ ' + error.message;
            messageEl.className = 'submit-message error';
            messageEl.style.display = 'block';
        }
    });
}
</script>
@endsection
