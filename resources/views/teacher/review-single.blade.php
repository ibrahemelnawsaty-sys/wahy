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
                // إجابة الطالب مخزّنة في العمود answer؛ وعند رفع ملف تكون JSON: {note, file, file_url}
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

                if (is_array($decoded)) {
                    if (array_key_exists('note', $decoded) || array_key_exists('file', $decoded)) {
                        $answerNote = is_scalar($decoded['note'] ?? null) ? $decoded['note'] : null;
                        $answerFile = $decoded['file'] ?? null;
                        // نتجاهل file_url المخزّن — قد يكون قديماً/قصيراً؛ نبني الرابط الصحيح أدناه
                    } else {
                        // إجابة منظّمة (ترتيب/حروف/كويز) — اعرضها بصيغة مقروءة
                        $answerNote = implode('، ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE), $decoded));
                    }
                } else {
                    $answerNote = is_string($raw) ? $raw : null;
                }

                // احتياط للعمود القديم file_path
                if (! $answerFile && $submission->file_path) {
                    $answerFile = $submission->file_path;
                }
                // بناء رابط الملف بالاصطلاح العامل (نفس رفع صور الأنشطة: storage/app/public/data/…)
                if ($answerFile) {
                    $answerFileUrl = \Illuminate\Support\Str::startsWith((string) $answerFile, 'http')
                        ? $answerFile
                        : asset('storage/app/public/data/' . ltrim((string) $answerFile, '/'));
                }
            @endphp
            <div class="info-section">
                <h3 class="section-title">إجابة الطالب</h3>
                @if($answerNote !== null && $answerNote !== '')
                    <div class="student-answer">{{ html_excerpt($answerNote, 2000) }}</div>
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

                @if(($answerNote === null || $answerNote === '') && ! $answerFile)
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
