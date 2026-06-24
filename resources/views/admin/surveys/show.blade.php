@extends('layouts.admin')

@section('page-title', 'تفاصيل الاستبيان: ' . $survey->title)

@section('content')
<style>
.survey-detail-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 24px;
}

.survey-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 2px solid #e2e8f0;
}

.survey-info h2 {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.survey-info p {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
}

.survey-actions {
    display: flex;
    gap: 12px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary { background: var(--color-primary); color: white; }
.btn-secondary { background: #e2e8f0; color: #475569; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: #f8fafc;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 8px;
}

.stat-label {
    color: #64748b;
    font-size: 14px;
}

.survey-settings {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.setting-item {
    background: #f8fafc;
    padding: 16px;
    border-radius: 8px;
}

.setting-label {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 8px;
    font-weight: 600;
}

.setting-value {
    font-size: 14px;
    color: #1e293b;
    font-weight: 600;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    margin: 2px;
}

.badge-primary { background: #e0e7ff; color: #4338ca; }
.badge-success { background: #dcfce7; color: #16a34a; }
.badge-warning { background: #fef3c7; color: #d97706; }

.share-section {
    background: #f0f9ff;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
    border: 2px solid #bae6fd;
}

.share-section h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
}

.url-container {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}

.url-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    font-family: monospace;
}

.btn-copy {
    background: #2563eb;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.qr-code-container {
    text-align: center;
    margin-top: 16px;
}

.qr-code-container img {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    background: white;
}

.questions-section {
    margin-top: 32px;
}

.questions-section h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
}

.question-item {
    background: #f8fafc;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    border-right: 4px solid var(--color-primary);
}

.question-text {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
}

.question-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #64748b;
}

.responses-section {
    margin-top: 32px;
}

.responses-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #f8fafc;
    padding: 12px;
    text-align: right;
    font-weight: 600;
    color: #475569;
    font-size: 14px;
    border-bottom: 2px solid #e2e8f0;
}

.table td {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
}
</style>

<div class="survey-detail-card">
    <div class="survey-header">
        <div class="survey-info">
            <h2>📋 {{ $survey->title }}</h2>
            @if($survey->description)
            <p>{{ $survey->description }}</p>
            @endif
        </div>
        <div class="survey-actions">
            <a href="{{ route('admin.surveys.edit', $survey) }}" class="btn btn-primary">✏️ تعديل</a>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">← رجوع</a>
        </div>
    </div>

    <!-- الإحصائيات -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_questions'] }}</div>
            <div class="stat-label">عدد الأسئلة</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_responses'] }}</div>
            <div class="stat-label">عدد الإجابات</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $survey->status == 'active' ? 'نشط' : 'غير نشط' }}</div>
            <div class="stat-label">الحالة</div>
        </div>
    </div>

    <!-- إعدادات الاستبيان -->
    <div class="survey-settings">
        <div class="setting-item">
            <div class="setting-label">المستهدفون</div>
            <div class="setting-value">
                @foreach($survey->target_roles ?? [] as $target)
                <span class="badge badge-primary">
                    @if($target == 'schools') 🏫 المدارس
                    @elseif($target == 'teachers') 👨‍🏫 المعلمين
                    @elseif($target == 'students') 🎓 الطلاب
                    @elseif($target == 'parents') 👪 أولياء الأمور
                    @endif
                </span>
                @endforeach
            </div>
        </div>
        <div class="setting-item">
            <div class="setting-label">نوع الاستبيان</div>
            <div class="setting-value">
                @if($survey->survey_type == 'pre_post_assessment')
                    📊 تقييم قبلي وبعدي ({{ $survey->assessment_phase == 'pre' ? 'قبلي' : 'بعدي' }})
                @else
                    📋 استبيان عام
                @endif
            </div>
        </div>
        <div class="setting-item">
            <div class="setting-label">إلزامي</div>
            <div class="setting-value">
                <span class="badge {{ $survey->is_mandatory ? 'badge-success' : 'badge-warning' }}">
                    {{ $survey->is_mandatory ? 'نعم ✓' : 'لا ✗' }}
                </span>
            </div>
        </div>
    </div>

    <!-- مشاركة الاستبيان -->
    <div class="share-section">
        <h3>🔗 مشاركة الاستبيان</h3>
        <div class="url-container">
            <input type="text" id="surveyUrl" class="url-input" value="{{ $surveyUrl }}" readonly>
            <button type="button" class="btn-copy" onclick="copyUrl()">📋 نسخ الرابط</button>
        </div>
        <div class="qr-code-container">
            <p style="margin-bottom: 12px; color: #64748b; font-size: 14px;">QR Code للاستبيان:</p>
            @if(isset($qrCodeType) && $qrCodeType === 'svg')
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code" style="max-width: 200px;">
            @elseif(isset($qrCodeType) && $qrCodeType === 'url')
                <img src="{{ $qrCode }}" alt="QR Code" style="max-width: 200px;">
            @else
                <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" style="max-width: 200px;">
            @endif
            <p style="margin-top: 12px; color: #64748b; font-size: 12px;">يمكن مسح الكود للوصول مباشرة للاستبيان</p>
        </div>
    </div>

    <!-- الأسئلة -->
    <div class="questions-section">
        <h3>الأسئلة ({{ $survey->questions->count() }})</h3>
        @foreach($survey->questions as $question)
        <div class="question-item">
            <div class="question-text">{{ $question->question_text }}</div>
            <div class="question-meta">
                <span>النوع: 
                    @if($question->question_type == 'text') نص
                    @elseif($question->question_type == 'textarea') نص طويل
                    @elseif($question->question_type == 'email') بريد إلكتروني
                    @elseif($question->question_type == 'phone') رقم جوال
                    @elseif($question->question_type == 'select') قائمة منسدلة
                    @elseif($question->question_type == 'radio') اختيار واحد
                    @elseif($question->question_type == 'checkbox') اختيار متعدد
                    @endif
                </span>
                @if($question->is_required)
                <span class="badge badge-warning">مطلوب</span>
                @endif
                @if($question->options && count($question->options) > 0)
                <span>الخيارات: {{ count($question->options) }}</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- الإجابات -->
    @php
        $questionsById = $survey->questions->keyBy('id');
    @endphp
    @if($stats['total_responses'] > 0)
    <div class="responses-section">
        <h3 style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 16px;">الإجابات ({{ $stats['total_responses'] }})</h3>
        <div class="responses-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>السؤال</th>
                        <th>الإجابة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($survey->responses->take(50) as $response)
                        @php $answers = $response->answers ?? []; @endphp
                        @if(empty($answers))
                            <tr>
                                <td>{{ $response->user->name ?? 'زائر' }}</td>
                                <td colspan="2" style="color:#94a3b8;">لا توجد إجابات</td>
                                <td>{{ $response->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @else
                            @foreach($answers as $qId => $answerValue)
                                @php
                                    $question = $questionsById[$qId] ?? null;
                                    $answerText = is_array($answerValue) ? implode('، ', $answerValue) : (string) $answerValue;
                                @endphp
                                <tr>
                                    <td>{{ $response->user->name ?? 'زائر' }}</td>
                                    <td>{{ $question ? \Illuminate\Support\Str::limit($question->question_text, 50) : 'سؤال محذوف' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($answerText, 100) }}</td>
                                    <td>{{ $response->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div style="text-align: center; padding: 40px; background: #f8fafc; border-radius: 8px; margin-top: 32px;">
        <p style="color: #64748b;">لا توجد إجابات بعد</p>
    </div>
    @endif
</div>

<script>
function copyUrl() {
    const urlInput = document.getElementById('surveyUrl');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999); // For mobile devices
    
    navigator.clipboard.writeText(urlInput.value).then(function() {
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = '✓ تم النسخ!';
        btn.style.background = '#16a34a';
        
        setTimeout(function() {
            btn.textContent = originalText;
            btn.style.background = '#2563eb';
        }, 2000);
    });
}
</script>

@endsection
