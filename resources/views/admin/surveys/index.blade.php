@extends('layouts.admin')

@section('page-title', 'إدارة الاستبيانات')

@section('content')
<style>
.surveys-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.surveys-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.survey-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}

.survey-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.survey-icon {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
}

.survey-name {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.survey-description {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 16px;
    line-height: 1.6;
}

.survey-stats {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.survey-stat {
    font-size: 13px;
    color: #64748b;
}

.survey-stat strong {
    color: var(--color-primary);
    font-weight: 600;
}

.survey-actions {
    display: flex;
    gap: 8px;
    justify-content: space-between;
    align-items: center;
}

.survey-status {
    position: absolute;
    top: 16px;
    left: 16px;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-active { background: #dcfce7; color: #16a34a; }
.status-inactive { background: #f3f4f6; color: #6b7280; }

.btn-add {
    padding: 12px 24px;
    background: var(--color-primary);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-action {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
    font-weight: 600;
}

.btn-action:hover {
    opacity: 0.8;
    transform: scale(1.05);
}

.btn-view { background: #e0e7ff; color: #4f46e5; }
.btn-edit { background: #dbeafe; color: #2563eb; }
.btn-delete { background: #fee2e2; color: #dc2626; }
.btn-copy { background: #dcfce7; color: #16a34a; }
.btn-qr { background: #f3e8ff; color: #9333ea; }

.filters-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.target-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: #f1f5f9;
    border-radius: 6px;
    font-size: 12px;
    margin: 2px;
}

.trigger-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #e0e7ff;
    border-radius: 6px;
    font-size: 12px;
    color: #4338ca;
    font-weight: 600;
}
</style>

<div class="surveys-header">
    <div>
        <h2 style="margin: 0 0 8px 0;">📋 إدارة الاستبيانات</h2>
        <p style="margin: 0; color: #64748b;">عرض وإدارة جميع الاستبيانات</p>
    </div>
    <a href="{{ route('admin.surveys.create') }}" class="btn-add">
        ➕ إنشاء استبيان جديد
    </a>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET">
        <div class="filters-grid">
            <input type="text" name="search" placeholder="🔍 بحث..." value="{{ request('search') }}" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
            
            <select name="status" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
                <option value="">كل الحالات</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
            </select>

            <button type="submit" style="padding: 10px 20px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">تطبيق</button>
        </div>
    </form>
</div>

<!-- Surveys Grid -->
@if($surveys->count() > 0)
<div class="surveys-grid">
    @foreach($surveys as $survey)
    <div class="survey-card">
        <span class="survey-status status-{{ $survey->status }}">
            {{ $survey->status == 'active' ? 'نشط' : 'غير نشط' }}
        </span>
        
        <span class="survey-icon">
            @if($survey->survey_type === 'pre_post_assessment')
                📊
            @else
                📋
            @endif
        </span>
        
        <h3 class="survey-name">{{ $survey->title }}</h3>

        @if($survey->survey_type === 'pre_post_assessment')
        <div style="margin-bottom: 8px;">
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); border: 2px solid #8b5cf6; border-radius: 20px; font-size: 12px; font-weight: 700; color: #6d28d9;">
                📊 تقييم {{ $survey->assessment_phase === 'pre' ? 'قبلي' : 'بعدي' }}
                @if($survey->lesson)
                    - {{ $survey->lesson->title }}
                @endif
            </span>
        </div>
        @endif
        <p class="survey-description">{{ \Illuminate\Support\Str::limit($survey->description, 100) ?? 'لا يوجد وصف' }}</p>
        
        <div class="survey-stats">
            <div class="survey-stat">
                الأسئلة: <strong>{{ $survey->questions->count() }}</strong>
            </div>
            <div class="survey-stat">
                @php
                    // حساب عدد المشاركين (مسجلين + ضيوف)
                    $registeredUsers = $survey->responses()->whereNotNull('user_id')->distinct('user_id')->count('user_id');
                    $guestResponses = $survey->responses()->whereNull('user_id')->count();
                    $totalResponses = $registeredUsers + $guestResponses;
                @endphp
                الإجابات: <strong>{{ $totalResponses }}</strong>
            </div>
        </div>

        <div style="margin-bottom: 12px;">
            <div style="font-size: 12px; color: #64748b; margin-bottom: 6px;">المستهدفون:</div>
            <div>
                @foreach($survey->target_roles ?? [] as $target)
                <span class="target-badge">
                    @if($target == 'school_admin') 🏫 مدير المدرسة
                    @elseif($target == 'teacher') 👨‍🏫 معلم
                    @elseif($target == 'student') 🎓 طالب
                    @elseif($target == 'parent') 👪 ولي أمر
                    @elseif($target == 'schools') 🏫 المدارس
                    @elseif($target == 'teachers') 👨‍🏫 المعلمين
                    @elseif($target == 'students') 🎓 الطلاب
                    @elseif($target == 'parents') 👪 أولياء الأمور
                    @endif
                </span>
                @endforeach
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <span class="trigger-badge">
                @if($survey->survey_type == 'pre_post_assessment')
                    📊 تقييم {{ $survey->assessment_phase === 'pre' ? 'قبلي' : 'بعدي' }}
                @else
                    📋 استبيان عام
                @endif
            </span>
        </div>
        
        <div class="survey-actions" style="display: flex; flex-direction: column; gap: 8px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                @php
                    // حساب عدد المشاركين (مسجلين + ضيوف)
                    $registeredUsers = $survey->responses()->whereNotNull('user_id')->distinct('user_id')->count('user_id');
                    $guestResponses = $survey->responses()->whereNull('user_id')->count();
                    $totalResponses = $registeredUsers + $guestResponses;
                @endphp
                <a href="{{ route('admin.surveys.responses', $survey) }}" class="btn-action" style="background: #fef3c7; color: #d97706;">📊 الإجابات ({{ $totalResponses }})</a>
                @if($survey->survey_type === 'pre_post_assessment')
                    <a href="{{ route('admin.surveys.comparison', $survey) }}" class="btn-action" style="background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); color: #6d28d9; border: 1px solid #8b5cf6;">📈 المقارنة</a>
                @endif
                <a href="{{ route('admin.surveys.show', $survey) }}" class="btn-action btn-view">👁️ عرض</a>
                <a href="{{ route('admin.surveys.edit', $survey) }}" class="btn-action btn-edit">✏️ تعديل</a>
                <button type="button" class="btn-action btn-copy" onclick="copySurveyLink({{ $survey->id }})" title="نسخ الرابط">🔗 نسخ الرابط</button>
                <button type="button" class="btn-action btn-qr" onclick="showQRCode({{ $survey->id }}, '{{ $survey->title }}')" title="عرض QR Code">📱 QR</button>
                <form method="POST" action="{{ route('admin.surveys.destroy', $survey) }}" id="delete-survey-form-{{ $survey->id }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-action btn-delete" onclick="deleteSurvey({{ $survey->id }}, event); return false;">🗑️</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div style="padding: 20px; background: white; border-radius: 12px;">
    {{ $surveys->links() }}
</div>
@else
<div style="text-align: center; padding: 60px; background: white; border-radius: 12px;">
    <div style="font-size: 64px; margin-bottom: 16px;">📋</div>
    <h3>لا توجد استبيانات</h3>
    <p style="color: #64748b; margin-bottom: 24px;">ابدأ بإنشاء أول استبيان</p>
    <a href="{{ route('admin.surveys.create') }}" class="btn-add">➕ إنشاء استبيان جديد</a>
</div>
@endif

<!-- QR Code Modal -->
<div id="qrModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 32px; border-radius: 16px; max-width: 500px; text-align: center; position: relative;">
        <button onclick="closeQRModal()" style="position: absolute; top: 16px; left: 16px; background: #fee2e2; color: #dc2626; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 18px; font-weight: bold;">×</button>
        <h3 id="qrTitle" style="margin-bottom: 24px; color: #1e293b;"></h3>
        <div id="qrCodeContainer" style="margin-bottom: 24px; display: flex; justify-content: center;"></div>
        <p style="color: #64748b; font-size: 14px; margin-bottom: 16px;">امسح الـ QR Code بكاميرا الهاتف للوصول للاستبيان</p>
        <button onclick="downloadQRCode()" class="btn-action btn-copy" style="padding: 12px 24px; font-size: 14px;">📥 تحميل QR Code</button>
    </div>
</div>

<script>
let currentQRCode = null;
let currentSurveyId = null;

function copySurveyLink(surveyId) {
    const url = '{{ url("/survey") }}/' + surveyId;
    
    // نسخ إلى الحافظة
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => {
            // إظهار رسالة النجاح
            showToast('✅ تم نسخ الرابط بنجاح!', 'success');
        }).catch(err => {
            // fallback للمتصفحات القديمة
            fallbackCopyTextToClipboard(url);
        });
    } else {
        fallbackCopyTextToClipboard(url);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.width = "2em";
    textArea.style.height = "2em";
    textArea.style.padding = "0";
    textArea.style.border = "none";
    textArea.style.outline = "none";
    textArea.style.boxShadow = "none";
    textArea.style.background = "transparent";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showToast('✅ تم نسخ الرابط بنجاح!', 'success');
        } else {
            showToast('❌ فشل نسخ الرابط', 'error');
        }
    } catch (err) {
        showToast('❌ فشل نسخ الرابط', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showQRCode(surveyId, title) {
    currentSurveyId = surveyId;
    const url = '{{ url("/survey") }}/' + surveyId;
    const modal = document.getElementById('qrModal');
    const qrContainer = document.getElementById('qrCodeContainer');
    const qrTitle = document.getElementById('qrTitle');
    
    qrTitle.textContent = 'QR Code - ' + title;
    qrContainer.innerHTML = '';
    
    // إنشاء QR Code باستخدام مكتبة QRCode.js
    const qrCode = document.createElement('div');
    qrCode.id = 'qrcode-' + surveyId;
    qrContainer.appendChild(qrCode);
    
    // استخدام مكتبة QRCode.js
    if (typeof QRCode !== 'undefined') {
        currentQRCode = new QRCode(qrCode, {
            text: url,
            width: 256,
            height: 256,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    } else {
        // Fallback إذا لم تكن المكتبة محملة
        qrContainer.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=' + encodeURIComponent(url) + '" alt="QR Code" style="border: 4px solid #e2e8f0; border-radius: 8px;">';
    }
    
    modal.style.display = 'flex';
}

function closeQRModal() {
    document.getElementById('qrModal').style.display = 'none';
}

function downloadQRCode() {
    const qrContainer = document.getElementById('qrCodeContainer');
    const canvas = qrContainer.querySelector('canvas');
    const img = qrContainer.querySelector('img');
    
    if (canvas) {
        // تحميل من Canvas
        const url = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = 'survey-qr-' + currentSurveyId + '.png';
        link.href = url;
        link.click();
        showToast('✅ تم تحميل QR Code بنجاح!', 'success');
    } else if (img) {
        // تحميل من Image
        const link = document.createElement('a');
        link.download = 'survey-qr-' + currentSurveyId + '.png';
        link.href = img.src;
        link.click();
        showToast('✅ تم تحميل QR Code بنجاح!', 'success');
    } else {
        showToast('❌ فشل تحميل QR Code', 'error');
    }
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.position = 'fixed';
    toast.style.top = '24px';
    toast.style.right = '24px';
    toast.style.padding = '16px 24px';
    toast.style.borderRadius = '8px';
    toast.style.fontWeight = '600';
    toast.style.fontSize = '14px';
    toast.style.zIndex = '99999';
    toast.style.boxShadow = '0 4px 16px rgba(0,0,0,0.2)';
    toast.style.animation = 'slideIn 0.3s ease-out';
    
    if (type === 'success') {
        toast.style.background = '#dcfce7';
        toast.style.color = '#16a34a';
    } else {
        toast.style.background = '#fee2e2';
        toast.style.color = '#dc2626';
    }
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// إغلاق المودال عند الضغط خارجه
document.getElementById('qrModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeQRModal();
    }
});

// دالة حذف الاستبيان مع تأكيد
function deleteSurvey(surveyId, event) {
    // منع أي سلوك افتراضي
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }
    
    // عرض رسالة التأكيد باستخدام glassmorphism popup
    showConfirm(
        '⚠️ هل أنت متأكد من حذف هذا الاستبيان؟\n\nسيتم حذف جميع الأسئلة المرتبطة به.\nلا يمكن التراجع عن هذا الإجراء.',
        function() {
            console.log('تم تأكيد الحذف، سيتم إرسال الفورم...');
            const form = document.getElementById('delete-survey-form-' + surveyId);
            if (form) {
                form.submit();
            } else {
                console.error('لم يتم العثور على الفورم');
            }
        },
        'تأكيد الحذف',
        'نعم، احذف',
        'إلغاء'
    );
    
    return false;
}

// إضافة animation styles
if (!document.getElementById('toast-animations')) {
    const style = document.createElement('style');
    style.id = 'toast-animations';
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}
</script>

<!-- تحميل مكتبة QRCode.js -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

@endsection
