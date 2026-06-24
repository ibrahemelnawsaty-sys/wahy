@extends('layouts.admin')

@section('title', 'تخصيص الصفحة الرئيسية')
@section('page-title', '🏠 تخصيص الصفحة الرئيسية')

@push('styles')
<style>
.tabs-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.tabs-header {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
    background: #f8fafc;
}

.tab-btn {
    flex: 1;
    padding: 16px 24px;
    border: none;
    background: transparent;
    font-weight: 600;
    font-size: 15px;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s;
    border-bottom: 3px solid transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.tab-btn:hover {
    background: white;
    color: var(--color-primary);
}

.tab-btn.active {
    background: white;
    color: var(--color-primary);
    border-bottom-color: var(--color-primary);
}

.tab-content {
    display: none;
    padding: 32px;
}

.tab-content.active {
    display: block;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 24px;
}

.setting-group {
    margin-bottom: 24px;
}

.setting-label {
    display: block;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    font-size: 14px;
}

.setting-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.setting-input:focus {
    outline: none;
    border-color: var(--color-primary);
}

.color-picker-group {
    display: flex;
    gap: 12px;
    align-items: center;
}

.color-preview {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    cursor: pointer;
}

.font-preview {
    padding: 16px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    margin-top: 8px;
    text-align: center;
    font-size: 18px;
}

.save-btn {
    background: var(--color-primary);
    color: white;
    padding: 14px 32px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 15px;
    transition: all 0.3s;
}

.save-btn:hover {
    background: var(--color-primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(60, 203, 138, 0.3);
}

.preview-btn {
    background: #3b82f6;
    color: white;
    padding: 14px 32px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 15px;
    margin-left: 12px;
}

.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: none;
}

.alert.success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.alert.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

.info-box {
    background: #eff6ff;
    border: 2px solid #3b82f6;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}

.info-box h4 {
    color: #1e40af;
    margin: 0 0 8px 0;
    font-size: 16px;
}

.info-box p {
    color: #1e3a8a;
    margin: 0;
    font-size: 14px;
}
</style>
@endpush

@section('content')
<div class="tabs-container">
    <!-- Tabs Header -->
    <div class="tabs-header">
        <button class="tab-btn active" onclick="switchTab('theme')" id="tab-theme">
            🎨 إعدادات الثيم
        </button>
        <button class="tab-btn" onclick="switchTab('content')" id="tab-content">
            📝 محتوى الصفحة
        </button>
    </div>

    <!-- Alert Messages -->
    <div id="alertSuccess" class="alert success" style="margin: 24px; margin-bottom: 0;"></div>
    <div id="alertError" class="alert error" style="margin: 24px; margin-bottom: 0;"></div>

    <!-- Tab 1: Theme Settings -->
    <div class="tab-content active" id="content-theme">
        <div class="info-box">
            <h4>💡 نصيحة</h4>
            <p>هذه الإعدادات تؤثر على مظهر الصفحة الرئيسية فقط. لتغيير مظهر لوحة التحكم، استخدم صفحة "تخصيص الثيم".</p>
        </div>

        <form id="themeForm">
            @csrf
            <div class="settings-grid">
                <div class="setting-group">
                    <label class="setting-label">اسم الموقع</label>
                    <input type="text" name="site_name" class="setting-input" value="{{ $themeSettings['site_name'] }}" placeholder="نظام القيم">
                </div>

                <div class="setting-group">
                    <label class="setting-label">شعار الموقع</label>
                    <input type="text" name="site_tagline" class="setting-input" value="{{ $themeSettings['site_tagline'] }}" placeholder="منصة تعليمية لبناء القيم">
                </div>
            </div>

            <div class="settings-grid">
                <div class="setting-group">
                    <label class="setting-label">اللون الأساسي</label>
                    <div class="color-picker-group">
                        <input type="color" name="primary_color" id="primary_color" class="color-preview" value="{{ $themeSettings['primary_color'] }}">
                        <input type="text" class="setting-input" value="{{ $themeSettings['primary_color'] }}" readonly style="flex: 1;">
                    </div>
                </div>

                <div class="setting-group">
                    <label class="setting-label">اللون الثانوي</label>
                    <div class="color-picker-group">
                        <input type="color" name="secondary_color" id="secondary_color" class="color-preview" value="{{ $themeSettings['secondary_color'] }}">
                        <input type="text" class="setting-input" value="{{ $themeSettings['secondary_color'] }}" readonly style="flex: 1;">
                    </div>
                </div>
            </div>

            <div class="setting-group">
                <label class="setting-label">نوع الخط</label>
                <select name="font_family" class="setting-input" id="font_family">
                    <option value="IBM Plex Sans Arabic" {{ $themeSettings['font_family'] == 'IBM Plex Sans Arabic' ? 'selected' : '' }}>IBM Plex Sans Arabic</option>
                    <option value="Cairo" {{ $themeSettings['font_family'] == 'Cairo' ? 'selected' : '' }}>Cairo</option>
                    <option value="Tajawal" {{ $themeSettings['font_family'] == 'Tajawal' ? 'selected' : '' }}>Tajawal</option>
                    <option value="Almarai" {{ $themeSettings['font_family'] == 'Almarai' ? 'selected' : '' }}>Almarai</option>
                    <option value="Noto Sans Arabic" {{ $themeSettings['font_family'] == 'Noto Sans Arabic' ? 'selected' : '' }}>Noto Sans Arabic</option>
                </select>
                <div class="font-preview" id="font-preview">
                    مثال على الخط المختار - نظام القيم
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 32px;">
                <button type="button" onclick="previewTheme()" class="preview-btn">
                    👁️ معاينة
                </button>
                <button type="submit" class="save-btn">
                    💾 حفظ الإعدادات
                </button>
            </div>
        </form>
    </div>

    <!-- Tab 2: Page Content (Builder) -->
    <div class="tab-content" id="content-content">
        <div class="info-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h4>⚠️ ملاحظة مهمة</h4>
            <p style="margin-bottom: 12px;"><strong>الصفحة الرئيسية الحالية:</strong> تستخدم تصميم HTML ثابت من ملف <code style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px;">landing.blade.php</code></p>
            <p style="margin-bottom: 0;"><strong>محرر الصفحات:</strong> يستخدم نظام JSON مرن - لكن يحتاج بناء المحتوى من الصفر أو استخدام القالب الجاهز أدناه.</p>
        </div>

        <div style="text-align: center; padding: 40px 24px;">
            <div style="font-size: 48px; margin-bottom: 16px;">🎨</div>
            <h3 style="color: #1e293b; margin-bottom: 12px;">خيارات تعديل الصفحة الرئيسية</h3>
            <p style="color: #64748b; margin-bottom: 32px; max-width: 600px; margin-left: auto; margin-right: auto;">
                اختر الطريقة المناسبة لتعديل صفحتك الرئيسية
            </p>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; max-width: 800px; margin: 0 auto;">
                <!-- Option 1: Current Design -->
                <div style="background: white; border: 2px solid #e2e8f0; border-radius: 12px; padding: 24px; text-align: right;">
                    <div style="font-size: 32px; margin-bottom: 12px;">🎯</div>
                    <h4 style="color: #1e293b; margin-bottom: 8px;">التصميم الحالي</h4>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 16px;">
                        استمر في استخدام التصميم الأصلي الجاهز مع الأنيميشن
                    </p>
                    <a href="/" target="_blank" class="preview-btn" style="display: inline-block; text-decoration: none; width: 100%;">
                        👀 معاينة التصميم الحالي
                    </a>
                </div>

                <!-- Option 2: Custom Builder -->
                <div style="background: white; border: 2px solid #10b981; border-radius: 12px; padding: 24px; text-align: right;">
                    <div style="font-size: 32px; margin-bottom: 12px;">✏️</div>
                    <h4 style="color: #1e293b; margin-bottom: 8px;">محرر مخصص</h4>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 16px;">
                        استخدم Page Builder لبناء صفحة مخصصة بالكامل
                    </p>
                    <a href="{{ route('admin.pages.edit', $landingPage->id) }}" class="save-btn" style="display: inline-block; text-decoration: none; width: 100%;">
                        🚀 فتح المحرر المتقدم
                    </a>
                </div>
            </div>

            <!-- Instructions -->
            <div style="margin-top: 32px; padding: 20px; background: #f8fafc; border-radius: 8px; text-align: right; max-width: 800px; margin-left: auto; margin-right: auto;">
                <h4 style="color: #475569; margin-bottom: 12px;">💡 كيف تعمل؟</h4>
                <ul style="list-style: none; padding: 0; margin: 0; color: #64748b; font-size: 14px; text-align: right;">
                    <li style="margin-bottom: 8px;">📌 <strong>الآن:</strong> الصفحة الرئيسية تعرض التصميم الأصلي (HTML)</li>
                    <li style="margin-bottom: 8px;">🎨 <strong>لو بنيت صفحة بالمحرر:</strong> يمكنك عرضها على رابط <code>/home</code></li>
                    <li style="margin-bottom: 8px;">🔄 <strong>للتبديل:</strong> عدّل ملف <code>routes/web.php</code> لتغيير الصفحة الافتراضية</li>
                    <li>⚡ <strong>الثيم:</strong> الألوان والخطوط في التاب الأول تطبق على كل الموقع</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Switch Tabs
function switchTab(tab) {
    // Update buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    
    // Update content
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    document.getElementById('content-' + tab).classList.add('active');
}

// Color Picker Updates
document.getElementById('primary_color').addEventListener('input', function() {
    this.nextElementSibling.value = this.value;
});

document.getElementById('secondary_color').addEventListener('input', function() {
    this.nextElementSibling.value = this.value;
});

// Font Preview
document.getElementById('font_family').addEventListener('change', function() {
    const preview = document.getElementById('font-preview');
    preview.style.fontFamily = this.value + ', sans-serif';
});

// Save Theme Settings
document.getElementById('themeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('{{ route("admin.landing.theme") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(result.message, 'تم الحفظ!');
        } else {
            showError(result.message || 'حدث خطأ أثناء الحفظ');
        }
    } catch (error) {
        showError('حدث خطأ في الاتصال');
        console.error(error);
    }
});

// Save Content
async function saveContent() {
    // Redirect to editor
    window.location.href = '{{ route("admin.pages.edit", $landingPage->id) }}';
}

// Preview Theme
function previewTheme() {
    window.open('/', '_blank');
}
</script>
@endsection
