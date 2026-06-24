@extends('layouts.admin')

@section('title', 'تخصيص الثيم')
@section('page-title', 'تخصيص الثيم')

@section('content')
    <form action="{{ route('admin.theme.update') }}" method="POST" id="themeForm">
        @csrf

        <!-- Hidden fields for theme and layout (default values) -->
        <input type="hidden" name="site_theme" value="custom">
        <input type="hidden" name="layout_style" value="wide">

        <!-- Colors Section -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">🎨 الألوان</h3>
            </div>
            <div class="admin-card-body" style="padding: 24px;">
                
                <!-- شرح الألوان -->
                <div style="background: #fffbeb; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-right: 4px solid #f59e0b;">
                    <strong>🎨 كيف تختار الألوان:</strong>
                    <ol style="margin: 10px 0 0 0; padding-right: 20px; line-height: 1.8;">
                        <li>اضغط على المربع الملون 🟦 لفتح منتقي الألوان</li>
                        <li>اختر اللون الذي تريده</li>
                        <li>كرر العملية لكل لون</li>
                        <li>اضغط "حفظ التغييرات" في الأسفل ⬇️</li>
                    </ol>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">🎨 اللون الأساسي (Primary)</label>
                        <div class="admin-color-picker">
                            <input type="color" 
                                   name="primary_color" 
                                   value="{{ $settings['primary_color'] }}" 
                                   class="admin-color-preview"
                                   id="primaryColor"
                                   style="cursor: pointer; width: 60px; height: 60px; border-radius: 12px; border: 3px solid #e2e8f0;">
                            <input type="text" 
                                   class="admin-form-input admin-color-input" 
                                   value="{{ $settings['primary_color'] }}"
                                   readonly
                                   id="primaryColorText"
                                   style="font-weight: 600; text-align: center;">
                        </div>
                        <div class="admin-form-help">💡 للأزرار والعناصر الأساسية (مثل: أزرار تسجيل الدخول)</div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">🎨 اللون الثانوي (Secondary)</label>
                        <div class="admin-color-picker">
                            <input type="color" 
                                   name="secondary_color" 
                                   value="{{ $settings['secondary_color'] }}" 
                                   class="admin-color-preview"
                                   id="secondaryColor"
                                   style="cursor: pointer; width: 60px; height: 60px; border-radius: 12px; border: 3px solid #e2e8f0;">
                            <input type="text" 
                                   class="admin-form-input admin-color-input" 
                                   value="{{ $settings['secondary_color'] }}"
                                   readonly
                                   id="secondaryColorText"
                                   style="font-weight: 600; text-align: center;">
                        </div>
                        <div class="admin-form-help">💡 للعناصر المساعدة (مثل: الروابط والأيقونات)</div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">📝 لون النص</label>
                        <div class="admin-color-picker">
                            <input type="color" 
                                   name="text_color" 
                                   value="{{ $settings['text_color'] }}" 
                                   class="admin-color-preview"
                                   id="textColor"
                                   style="cursor: pointer; width: 60px; height: 60px; border-radius: 12px; border: 3px solid #e2e8f0;">
                            <input type="text" 
                                   class="admin-form-input admin-color-input" 
                                   value="{{ $settings['text_color'] }}"
                                   readonly
                                   id="textColorText"
                                   style="font-weight: 600; text-align: center;">
                        </div>
                        <div class="admin-form-help">💡 لون النصوص والكتابة في الموقع</div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">🖼️ لون الخلفية</label>
                        <div class="admin-color-picker">
                            <input type="color" 
                                   name="background_color" 
                                   value="{{ $settings['background_color'] }}" 
                                   class="admin-color-preview"
                                   id="backgroundColor"
                                   style="cursor: pointer; width: 60px; height: 60px; border-radius: 12px; border: 3px solid #e2e8f0;">
                            <input type="text" 
                                   class="admin-form-input admin-color-input" 
                                   value="{{ $settings['background_color'] }}"
                                   readonly
                                   id="backgroundColorText"
                                   style="font-weight: 600; text-align: center;">
                        </div>
                        <div class="admin-form-help">💡 لون خلفية الموقع الأساسية</div>
                    </div>
                </div>
                
                <!-- معاينة الألوان -->
                <div style="margin-top: 24px; padding: 20px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border-radius: 12px;">
                    <h4 style="margin: 0 0 15px 0; color: #1f2937;">👀 معاينة سريعة للألوان:</h4>
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <button type="button" id="previewBtn" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                            زر تجريبي 🎨
                        </button>
                        <span id="previewText" style="padding: 10px 20px; border-radius: 8px; font-weight: 600;">
                            نص تجريبي 📝
                        </span>
                    </div>
                    <small style="display: block; margin-top: 12px; color: #6b7280;">💡 غيّر الألوان في الأعلى وشاهد التغيير مباشرة</small>
                </div>
            </div>
        </div>

        <!-- Typography -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">الخطوط</h3>
            </div>
            <div class="admin-card-body" style="padding: 24px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">خط الموقع</label>
                    <select name="font_family" class="admin-form-select">
                        <option value="IBM Plex Sans Arabic" @selected($settings['font_family'] == 'IBM Plex Sans Arabic')>IBM Plex Sans Arabic</option>
                        <option value="Cairo" @selected($settings['font_family'] == 'Cairo')>Cairo</option>
                        <option value="Tajawal" @selected($settings['font_family'] == 'Tajawal')>Tajawal</option>
                        <option value="Almarai" @selected($settings['font_family'] == 'Almarai')>Almarai</option>
                    </select>
                    <div class="admin-form-help">اختر خط الموقع (يدعم العربية)</div>
                </div>
            </div>
        </div>

        <!-- Media Upload -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">الشعار والصور</h3>
            </div>
            <div class="admin-card-body" style="padding: 24px;">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
                    <!-- Logo -->
                    <div class="admin-form-group">
                        <label class="admin-form-label">شعار الموقع (Logo)</label>
                        <div style="border: 2px dashed #e2e8f0; border-radius: 8px; padding: 24px; text-align: center;">
                            @if($settings['site_logo'])
                                <img src="{{ asset('storage/app/public/data/' . $settings['site_logo']) }}" 
                                     alt="Logo" 
                                     style="max-width: 100%; max-height: 100px; margin-bottom: 16px;"
                                     id="logoPreview">
                            @else
                                <div style="font-size: 48px; margin-bottom: 16px;" id="logoPreview">🏢</div>
                            @endif
                            <button type="button" class="admin-btn admin-btn-outline" style="padding: 8px 16px; font-size: 12px;" onclick="document.getElementById('logoInput').click()">
                                📤 رفع شعار
                            </button>
                            <input type="file" id="logoInput" accept="image/*" style="display: none;" onchange="uploadFile(this, 'logo')">
                        </div>
                    </div>

                    <!-- Favicon -->
                    <div class="admin-form-group">
                        <label class="admin-form-label">أيقونة الموقع (Favicon)</label>
                        <div style="border: 2px dashed #e2e8f0; border-radius: 8px; padding: 24px; text-align: center;">
                            @if($settings['site_favicon'])
                                <img src="{{ asset('storage/app/public/data/' . $settings['site_favicon']) }}" 
                                     alt="Favicon" 
                                     style="max-width: 100%; max-height: 100px; margin-bottom: 16px;"
                                     id="faviconPreview">
                            @else
                                <div style="font-size: 48px; margin-bottom: 16px;" id="faviconPreview">⭐</div>
                            @endif
                            <button type="button" class="admin-btn admin-btn-outline" style="padding: 8px 16px; font-size: 12px;" onclick="document.getElementById('faviconInput').click()">
                                📤 رفع أيقونة
                            </button>
                            <input type="file" id="faviconInput" accept="image/*" style="display: none;" onchange="uploadFile(this, 'favicon')">
                        </div>
                    </div>

                    <!-- Hero Background -->
                    <div class="admin-form-group">
                        <label class="admin-form-label">خلفية الصفحة الرئيسية</label>
                        <div style="border: 2px dashed #e2e8f0; border-radius: 8px; padding: 24px; text-align: center;">
                            @if($settings['hero_background'])
                                <img src="{{ asset('storage/app/public/data/' . $settings['hero_background']) }}" 
                                     alt="Hero" 
                                     style="max-width: 100%; max-height: 100px; margin-bottom: 16px;"
                                     id="heroPreview">
                            @else
                                <div style="font-size: 48px; margin-bottom: 16px;" id="heroPreview">🖼️</div>
                            @endif
                            <button type="button" class="admin-btn admin-btn-outline" style="padding: 8px 16px; font-size: 12px;" onclick="document.getElementById('heroInput').click()">
                                📤 رفع خلفية
                            </button>
                            <input type="file" id="heroInput" accept="image/*" style="display: none;" onchange="uploadFile(this, 'hero_background')">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div style="display: flex; justify-content: flex-end; gap: 16px;">
            <button type="button" class="admin-btn admin-btn-outline" onclick="resetToDefaults()">
                🔄 إعادة تعيين للألوان الافتراضية
            </button>
            <button type="submit" class="admin-btn admin-btn-primary">
                💾 حفظ التغييرات
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<style>
/* Slide Down Animation */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Glassmorphism Popup Overlay */
.glassmorphism-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

.glassmorphism-popup-overlay.active {
    display: flex;
}

/* Glassmorphism Popup */
.glassmorphism-popup {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    padding: 0;
    animation: popupSlideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    overflow: hidden;
}

/* Popup Header */
.glassmorphism-popup-header {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9));
    padding: 24px;
    text-align: center;
}

.glassmorphism-popup-icon {
    font-size: 56px;
    margin-bottom: 12px;
    animation: iconBounce 0.6s ease;
}

.glassmorphism-popup-title {
    color: white;
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Popup Body */
.glassmorphism-popup-body {
    padding: 32px;
    text-align: center;
}

.glassmorphism-popup-message {
    color: #334155;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 24px;
}

.glassmorphism-popup-list {
    background: rgba(102, 126, 234, 0.05);
    border-radius: 12px;
    padding: 16px;
    margin: 20px 0;
    text-align: right;
}

.glassmorphism-popup-list-item {
    color: #475569;
    font-size: 14px;
    padding: 6px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.glassmorphism-popup-list-item::before {
    content: '✓';
    color: #667eea;
    font-weight: bold;
    font-size: 16px;
}

/* Popup Actions */
.glassmorphism-popup-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.glassmorphism-popup-btn {
    padding: 14px 32px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
    min-width: 120px;
}

.glassmorphism-popup-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.glassmorphism-popup-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.glassmorphism-popup-btn-secondary {
    background: rgba(148, 163, 184, 0.1);
    color: #64748b;
    border: 1px solid rgba(148, 163, 184, 0.3);
}

.glassmorphism-popup-btn-secondary:hover {
    background: rgba(148, 163, 184, 0.2);
}

/* Success Popup */
.glassmorphism-popup.success .glassmorphism-popup-header {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9));
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes popupSlideUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes iconBounce {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}
</style>

<script>
// الألوان الافتراضية
const defaultColors = {
    site_theme: 'light',
    layout_style: 'wide',
    primary_color: '#667eea',
    secondary_color: '#764ba2',
    text_color: '#334155',
    background_color: '#ffffff',
    font_family: 'IBM Plex Sans Arabic'
};

// Color Picker Sync
document.querySelectorAll('.admin-color-preview').forEach(input => {
    input.addEventListener('input', function() {
        const textInput = document.getElementById(this.id + 'Text');
        if (textInput) {
            textInput.value = this.value;
        }
    });
});

// File Upload
function uploadFile(input, type) {
    const file = input.files[0];
    if (!file) return;
    
    // التحقق من نوع الملف
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml'];
    if (!allowedTypes.includes(file.type)) {
        showGlassPopup('error', '❌', 'نوع ملف غير مدعوم', 'يرجى رفع صورة بصيغة: JPG, PNG, GIF, أو SVG');
        input.value = '';
        return;
    }
    
    // التحقق من حجم الملف (5MB max)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        showGlassPopup('error', '❌', 'حجم الملف كبير جداً', 'الحد الأقصى لحجم الملف هو 5 ميجابايت');
        input.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', type);
    formData.append('_token', '{{ csrf_token() }}');

    // Show loading
    const preview = document.getElementById(type + 'Preview') || document.getElementById(type.replace('_', '') + 'Preview');
    const originalContent = preview.innerHTML;
    preview.innerHTML = '<div style="font-size: 24px;">⏳</div><div style="font-size: 12px;">جاري الرفع...</div>';

    // إنشاء مؤقت timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 seconds timeout

    fetch('{{ route("admin.theme.upload") }}', {
        method: 'POST',
        body: formData,
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            preview.innerHTML = `<img src="${data.url}" style="max-width: 100%; max-height: 100px;">`;
            showGlassPopup('success', '✅', 'تم الرفع بنجاح!', data.message || 'تم رفع الملف وحفظه بنجاح');
        } else {
            preview.innerHTML = originalContent;
            showGlassPopup('error', '❌', 'فشل الرفع', data.message || 'حدث خطأ أثناء رفع الملف');
        }
        input.value = '';
    })
    .catch(error => {
        clearTimeout(timeoutId);
        preview.innerHTML = originalContent;
        input.value = '';
        
        if (error.name === 'AbortError') {
            showGlassPopup('error', '⏱️', 'انتهت المهلة', 'استغرق رفع الملف وقتاً طويلاً. يرجى المحاولة مرة أخرى');
        } else {
            showGlassPopup('error', '❌', 'خطأ في الاتصال', 'حدث خطأ أثناء رفع الملف. يرجى التحقق من الاتصال بالإنترنت');
        }
        console.error('Upload Error:', error);
    });
}

// Glassmorphism Popup Function
function showGlassPopup(type, icon, title, message, actions = null) {
    // Remove existing popup
    const existingPopup = document.querySelector('.glassmorphism-popup-overlay');
    if (existingPopup) {
        existingPopup.remove();
    }

    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'glassmorphism-popup-overlay';

    // Create popup
    const popup = document.createElement('div');
    popup.className = `glassmorphism-popup ${type}`;

    // Build popup content
    let actionsHTML = '';
    if (actions) {
        actionsHTML = '<div class="glassmorphism-popup-actions">';
        actions.forEach(action => {
            actionsHTML += `<button class="glassmorphism-popup-btn glassmorphism-popup-btn-${action.type}" onclick="${action.onclick}">${action.label}</button>`;
        });
        actionsHTML += '</div>';
    } else {
        actionsHTML = '<div class="glassmorphism-popup-actions"><button class="glassmorphism-popup-btn glassmorphism-popup-btn-primary" onclick="closeGlassPopup()">حسناً</button></div>';
    }

    popup.innerHTML = `
        <div class="glassmorphism-popup-header">
            <div class="glassmorphism-popup-icon">${icon}</div>
            <h3 class="glassmorphism-popup-title">${title}</h3>
        </div>
        <div class="glassmorphism-popup-body">
            <p class="glassmorphism-popup-message">${message}</p>
            ${actionsHTML}
        </div>
    `;

    overlay.appendChild(popup);
    document.body.appendChild(overlay);

    // Show with animation
    setTimeout(() => {
        overlay.classList.add('active');
    }, 10);

    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeGlassPopup();
        }
    });
}

// Close Glassmorphism Popup
function closeGlassPopup() {
    const overlay = document.querySelector('.glassmorphism-popup-overlay');
    if (overlay) {
        overlay.classList.remove('active');
        setTimeout(() => {
            overlay.remove();
        }, 300);
    }
}

// Reset to Default Colors with Glass Popup
function resetToDefaults() {
    showGlassPopup(
        'confirm',
        '🔄',
        'إعادة تعيين الألوان الافتراضية',
        `<div class="glassmorphism-popup-list">
            <div class="glassmorphism-popup-list-item">اللون الأساسي: #667eea</div>
            <div class="glassmorphism-popup-list-item">اللون الثانوي: #764ba2</div>
            <div class="glassmorphism-popup-list-item">لون النص: #334155</div>
            <div class="glassmorphism-popup-list-item">لون الخلفية: #ffffff</div>
            <div class="glassmorphism-popup-list-item">الخط: IBM Plex Sans Arabic</div>
        </div>
        <p style="color: #64748b; font-size: 14px;">هل تريد المتابعة؟</p>`,
        [
            {
                label: 'نعم، إعادة التعيين',
                type: 'primary',
                onclick: 'confirmResetToDefaults()'
            },
            {
                label: 'إلغاء',
                type: 'secondary',
                onclick: 'closeGlassPopup()'
            }
        ]
    );
}

// Confirm Reset
function confirmResetToDefaults() {
    // إعادة تعيين الخط
    const fontSelect = document.querySelector('select[name="font_family"]');
    if (fontSelect) {
        fontSelect.value = defaultColors.font_family;
    }
    
    // إعادة تعيين الألوان
    setColorValue('primaryColor', defaultColors.primary_color);
    setColorValue('secondaryColor', defaultColors.secondary_color);
    setColorValue('textColor', defaultColors.text_color);
    setColorValue('backgroundColor', defaultColors.background_color);
    
    closeGlassPopup();
    
    // Show success message
    showGlassPopup(
        'success',
        '✅',
        'تم إعادة التعيين بنجاح!',
        'تم إرجاع جميع الإعدادات للقيم الافتراضية.<br><br><strong>⚠️ لا تنسى حفظ التغييرات!</strong>'
    );
}

// Helper function لتعيين قيمة اللون
function setColorValue(colorId, colorValue) {
    const colorInput = document.getElementById(colorId);
    const colorText = document.getElementById(colorId + 'Text');
    
    if (colorInput) {
        colorInput.value = colorValue;
    }
    
    if (colorText) {
        colorText.value = colorValue;
    }
}

// تطبيق الثيم فوراً عند التغيير
document.addEventListener('DOMContentLoaded', function() {
    // معاينة مباشرة للألوان
    const colorInputs = {
        primaryColor: document.getElementById('primaryColor'),
        secondaryColor: document.getElementById('secondaryColor'),
        textColor: document.getElementById('textColor'),
        backgroundColor: document.getElementById('backgroundColor')
    };
    
    const previewBtn = document.getElementById('previewBtn');
    const previewText = document.getElementById('previewText');
    
    // تحديث المعاينة عند تغيير اللون
    function updateLivePreview() {
        if (colorInputs.primaryColor && previewBtn) {
            previewBtn.style.backgroundColor = colorInputs.primaryColor.value;
            previewBtn.style.color = '#ffffff';
        }
        
        if (colorInputs.textColor && previewText) {
            previewText.style.color = colorInputs.textColor.value;
        }
        
        if (colorInputs.backgroundColor && previewText) {
            previewText.style.backgroundColor = colorInputs.backgroundColor.value;
        }
    }
    
    // ربط كل لون بالمعاينة
    Object.keys(colorInputs).forEach(key => {
        const input = colorInputs[key];
        const textInput = document.getElementById(key + 'Text');
        
        if (input) {
            input.addEventListener('input', function() {
                // تحديث حقل النص
                if (textInput) {
                    textInput.value = this.value;
                }
                
                // تحديث المعاينة المباشرة
                updateLivePreview();
            });
        }
    });
    
    // تطبيق المعاينة عند تحميل الصفحة
    updateLivePreview();
});
</script>
@endpush
