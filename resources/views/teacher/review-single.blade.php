@extends('layouts.teacher')

@section('title', 'تقييم النشاط')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/teacher-glass.css') }}?v={{ time() }}">
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
                    <div class="submission-date">تم التقديم: {{ $submission->submitted_at->format('Y-m-d H:i') }}</div>
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
                </div>
            </div>

            <!-- Activity Description -->
            <div class="info-section">
                <h3 class="section-title">وصف النشاط</h3>
                <div class="activity-description">{{ $submission->activity?->description }}</div>
            </div>

            <!-- Student Answer -->
            <div class="info-section">
                <h3 class="section-title">إجابة الطالب</h3>
                @if($submission->content)
                    <div class="student-answer">{{ html_excerpt($submission->content, 2000) }}</div>
                @endif

                @if($submission->file_path)
                    <div class="attached-file">
                        <span class="file-icon-large">📎</span>
                        <div>
                            <div class="file-label">ملف مرفق</div>
                            <a href="{{ asset('storage/app/public/data/' . $submission->file_path) }}" target="_blank" class="file-link">
                                فتح الملف
                            </a>
                        </div>
                    </div>
                @endif

                @if(!$submission->content && !$submission->file_path)
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

                <!-- XP Award -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">✨</span>
                        نقاط الخبرة (XP)
                    </label>
                    <input type="number" name="xp_awarded" class="form-input" min="0" max="50" value="10" required>
                    <div class="help-text">من 0 إلى 50 نقطة حسب الإجابة</div>
                </div>

                <!-- Coins Award -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">🪙</span>
                        العملات الممنوحة
                    </label>
                    <input type="number" name="coins_awarded" class="form-input" min="0" max="20" value="5" required>
                    <div class="help-text">من 0 إلى 20 عملة حسب الإجابة</div>
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
</script>
@endsection
