@extends('layouts.admin')

@section('page-title', 'إجابات الاستبيان: ' . $survey->title)

@section('content')
<style>
.responses-header {
    background: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.survey-info {
    display: flex;
    gap: 32px;
    align-items: center;
    margin-bottom: 16px;
}

.survey-icon-large {
    font-size: 64px;
}

.survey-details h1 {
    margin: 0 0 8px 0;
    font-size: 24px;
    color: #1e293b;
}

.survey-meta {
    display: flex;
    gap: 24px;
    color: #64748b;
    font-size: 14px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.responses-table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.responses-table {
    width: 100%;
    border-collapse: collapse;
}

.responses-table thead {
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    color: white;
}

.responses-table th {
    padding: 16px;
    text-align: right;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
}

.responses-table tbody tr {
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.2s;
}

.responses-table tbody tr:hover {
    background: #f8fafc;
}

.responses-table td {
    padding: 16px;
    font-size: 14px;
    color: #475569;
    vertical-align: top;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-small {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}

.user-details-small {
    min-width: 0;
}

.user-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 2px;
}

.user-email {
    font-size: 12px;
    color: #64748b;
}

.response-text {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.5;
}

.actions-cell {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-action-small {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
}

.btn-view-detail {
    background: #e0e7ff;
    color: #4f46e5;
}

.btn-delete-response {
    background: #fee2e2;
    color: #dc2626;
}

.btn-action-small:hover {
    opacity: 0.8;
    transform: scale(1.05);
}

.export-btn {
    padding: 10px 20px;
    background: #16a34a;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.export-btn:hover {
    background: #15803d;
    transform: translateY(-2px);
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #e2e8f0;
    color: #475569;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
}

.back-btn:hover {
    background: #cbd5e1;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 16px;
}

.no-responses {
    color: #64748b;
    margin-bottom: 24px;
}

.date-cell {
    white-space: nowrap;
    color: #64748b;
    font-size: 13px;
}

.response-badge {
    background: #f0f9ff;
    color: #0369a1;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}
</style>

<div class="responses-header">
    <div class="survey-info">
        <span class="survey-icon-large">📋</span>
        <div class="survey-details">
            <h1>{{ $survey->title }}</h1>
            <div class="survey-meta">
                <span>📅 تاريخ الإنشاء: {{ $survey->created_at->format('Y/m/d') }}</span>
                <span>❓ عدد الأسئلة: {{ $survey->questions->count() }}</span>
                <span>
                    @if($survey->status == 'active')
                        ✅ نشط
                    @else
                        ⏸️ غير نشط
                    @endif
                </span>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 12px;">
        <a href="{{ route('admin.surveys.export', $survey) }}" class="export-btn">📥 تصدير Excel</a>
        <a href="{{ route('admin.surveys.index') }}" class="back-btn">⬅️ العودة للقائمة</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $responses->count() }}</div>
        <div class="stat-label">عدد المشاركين</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value">{{ $survey->responses()->count() }}</div>
        <div class="stat-label">إجمالي الإجابات</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value">{{ $survey->questions->count() }}</div>
        <div class="stat-label">عدد الأسئلة</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value">{{ number_format(($responses->count() / max($survey->questions->count(), 1)) * 100, 1) }}%</div>
        <div class="stat-label">معدل الإكمال</div>
    </div>
</div>

@if($responses->count() > 0)
    @php
        $firstThreeQuestions = $survey->questions->take(3);
    @endphp
    
    <div class="responses-table-container">
        <table class="responses-table">
            <thead>
                <tr>
                    <th style="width: 200px;">المستخدم</th>
                    @foreach($firstThreeQuestions as $question)
                        <th>{{ \Illuminate\Support\Str::limit($question->question_text, 30) }}</th>
                    @endforeach
                    <th style="width: 150px;">التاريخ</th>
                    <th style="width: 150px;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($responses as $userId => $userResponses)
                    @php
                        $firstResponse = $userResponses->first();
                        $user = $firstResponse->user;
                        $isGuest = is_null($user);
                        $answersMap = $firstResponse->answers ?? [];
                    @endphp

                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar-small">
                                    {{ $isGuest ? '👤' : mb_substr($user->name, 0, 1) }}
                                </div>
                                <div class="user-details-small">
                                    <div class="user-name">{{ $isGuest ? 'زائر' : $user->name }}</div>
                                    @if(!$isGuest)
                                        <div class="user-email">{{ $user->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        @foreach($firstThreeQuestions as $question)
                            <td>
                                @php
                                    $rawValue = $answersMap[$question->id] ?? $answersMap[(string) $question->id] ?? null;
                                    $value = is_array($rawValue) ? implode('، ', $rawValue) : $rawValue;
                                @endphp
                                @if($value !== null && $value !== '')
                                    <div class="response-text" title="{{ $value }}">{{ \Illuminate\Support\Str::limit((string) $value, 60) }}</div>
                                @else
                                    <span style="color: #cbd5e1;">-</span>
                                @endif
                            </td>
                        @endforeach
                        
                        <td class="date-cell">
                            {{ $userResponses->first()->created_at->format('Y/m/d') }}<br>
                            <small>{{ $userResponses->first()->created_at->format('H:i') }}</small>
                        </td>
                        
                        <td>
                            <div class="actions-cell">
                                <button onclick="viewResponseDetail('{{ $userId }}')" class="btn-action-small btn-view-detail">
                                    👁️ عرض
                                </button>
                                <button onclick="deleteResponse('{{ $userId }}', '{{ $survey->id }}', event)" class="btn-action-small btn-delete-response">
                                    🗑️ حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Modal for detailed view -->
    <div id="responseDetailModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; overflow-y: auto; padding: 20px;">
        <div style="background: white; max-width: 800px; margin: 40px auto; border-radius: 12px; padding: 32px; position: relative;">
            <button onclick="closeResponseDetail()" style="position: absolute; top: 16px; left: 16px; background: #fee2e2; color: #dc2626; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 18px; font-weight: bold;">×</button>
            <div id="responseDetailContent"></div>
        </div>
    </div>
    
    <script>
    const responsesData = @json($responses);
    const surveyQuestions = @json($survey->questions->keyBy('id'));
    const csrfToken = '{{ csrf_token() }}';
    
    function deleteResponse(userId, surveyId, event) {
        // منع أي سلوك افتراضي
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // عرض تأكيد الحذف باستخدام glassmorphism popup
        showConfirm(
            '⚠️ هل أنت متأكد من حذف هذه الإجابات؟\n\nلا يمكن التراجع عن هذا الإجراء.',
            function() {
                // تعطيل الزر مؤقتاً
                if (event && event.target) {
                    event.target.disabled = true;
                    event.target.style.opacity = '0.5';
                    event.target.textContent = '⏳ جاري الحذف...';
                }
                
                fetch(`/admin/surveys/${surveyId}/responses/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // حذف الصف من الجدول
                        const row = event.target.closest('tr');
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        
                        setTimeout(() => {
                            row.remove();
                            
                            // تحديث العدادات
                            updateStats();
                            
                            // حذف من البيانات
                            delete responsesData[userId];
                            
                            // التحقق إذا لم يتبقى أي إجابات
                            if (Object.keys(responsesData).length === 0) {
                                location.reload();
                            }
                            
                            // عرض رسالة نجاح
                            showToast('تم حذف الإجابات بنجاح! 🗑️', 'success');
                        }, 300);
                    } else {
                        showToast('حدث خطأ أثناء الحذف!', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('حدث خطأ أثناء الحذف!', 'error');
                    
                    // إعادة تفعيل الزر
                    if (event && event.target) {
                        event.target.disabled = false;
                        event.target.style.opacity = '1';
                        event.target.textContent = '🗑️ حذف';
                    }
                });
            },
            'تأكيد الحذف',
            'نعم، احذف',
            'إلغاء'
        );
        
        return false;
    }
    
    function updateStats() {
        const remainingResponses = document.querySelectorAll('.responses-table tbody tr').length;
        const statCards = document.querySelectorAll('.stat-value');
        if (statCards[0]) {
            statCards[0].textContent = remainingResponses;
        }
    }
    
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#dcfce7' : '#fee2e2'};
            color: ${type === 'success' ? '#16a34a' : '#dc2626'};
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 10000;
            font-weight: 600;
            animation: slideIn 0.3s ease-out;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Add animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    function viewResponseDetail(userId) {
        const userResponses = responsesData[userId];
        if (!userResponses || userResponses.length === 0) return;
        
        const user = userResponses[0].user;
        const isGuest = !user;
        
        let html = '<h2 style="margin-bottom: 24px;">تفاصيل الإجابات</h2>';
        
        html += '<div style="background: #f8fafc; padding: 16px; border-radius: 8px; margin-bottom: 24px;">';
        html += '<div style="display: flex; align-items: center; gap: 12px;">';
        html += '<div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 18px;">';
        html += isGuest ? '👤' : user.name.charAt(0);
        html += '</div>';
        html += '<div>';
        html += '<div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;">' + (isGuest ? 'زائر (غير مسجل)' : user.name) + '</div>';
        if (!isGuest) {
            html += '<div style="color: #64748b; font-size: 14px;">📧 ' + user.email + '</div>';
        }
        html += '<div style="color: #64748b; font-size: 13px;">🕒 ' + new Date(userResponses[0].created_at).toLocaleString('ar-EG') + '</div>';
        html += '</div></div></div>';
        
        // الإجابات مخزنة كـ JSON map: { question_id: value } داخل response.answers
        const answersMap = (userResponses[0] && userResponses[0].answers) || {};
        const orderedQuestions = Object.values(surveyQuestions).sort((a, b) => (a.order ?? 0) - (b.order ?? 0));

        orderedQuestions.forEach(question => {
            const value = answersMap[question.id] ?? answersMap[String(question.id)];
            html += '<div style="margin-bottom: 20px; padding: 16px; background: #f8fafc; border-radius: 8px;">';
            html += '<div style="font-weight: 600; color: #1e293b; margin-bottom: 8px;">' + (question.order ?? '') + '. ' + question.question_text + '</div>';

            if (value === undefined || value === null || value === '') {
                html += '<div style="color: #94a3b8;">—</div>';
            } else if (Array.isArray(value)) {
                html += '<ul style="list-style: none; padding: 0; margin: 0;">';
                value.forEach(answer => {
                    html += '<li style="padding: 6px 0;">✓ ' + answer + '</li>';
                });
                html += '</ul>';
            } else {
                html += '<div style="color: #475569;">' + value + '</div>';
            }
            html += '</div>';
        });
        
        document.getElementById('responseDetailContent').innerHTML = html;
        document.getElementById('responseDetailModal').style.display = 'flex';
        document.getElementById('responseDetailModal').style.alignItems = 'center';
        document.getElementById('responseDetailModal').style.justifyContent = 'center';
    }
    
    function closeResponseDetail() {
        document.getElementById('responseDetailModal').style.display = 'none';
    }
    
    // Close modal on outside click
    document.getElementById('responseDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeResponseDetail();
        }
    });
    </script>
@else
    <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>لا توجد إجابات بعد</h3>
        <p class="no-responses">لم يقم أي مستخدم بالإجابة على هذا الاستبيان حتى الآن</p>
        <a href="{{ route('admin.surveys.index') }}" class="back-btn">⬅️ العودة للقائمة</a>
    </div>
@endif

@endsection
