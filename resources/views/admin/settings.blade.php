@extends('layouts.admin')

@section('title', 'الإعدادات العامة')
@section('page-title', 'الإعدادات العامة')

@section('content')
    <form id="settingsForm" action="{{ route('admin.settings.update') }}" method="POST" novalidate>
        @csrf

        <!-- معلومات الموقع -->
        <div class="settings-glass-card fade-in-up" style="animation-delay: 0.1s;">
            <div class="settings-card-header">
                <div class="settings-header-icon" role="img" aria-label="أيقونة معلومات الموقع">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </div>
                <h3 class="settings-card-title">معلومات الموقع</h3>
            </div>
            <div class="settings-card-body">
                <div class="settings-form-group">
                    <label for="site_name" class="settings-form-label">اسم الموقع</label>
                    <input type="text" id="site_name" name="site_name" class="settings-form-input" value="{{ $settings['site_name'] }}" placeholder="أدخل اسم الموقع" required>
                    <div class="settings-form-help">اسم الموقع الذي سيظهر في الهيدر والفوتر</div>
                </div>

                <div class="settings-form-group">
                    <label for="site_description" class="settings-form-label">وصف الموقع</label>
                    <textarea id="site_description" name="site_description" class="settings-form-textarea" rows="3" placeholder="أدخل وصف مختصر عن الموقع" maxlength="300">{{ $settings['site_description'] ?? '' }}</textarea>
                    <div class="settings-form-help">
                        <span>وصف مختصر عن الموقع</span>
                        <span class="char-counter" data-target="site_description">
                            <span class="current">0</span> / 300
                        </span>
                    </div>
                </div>

                <div class="settings-form-group">
                    <label for="footer_text" class="settings-form-label">نص الفوتر</label>
                    <input type="text" id="footer_text" name="footer_text" class="settings-form-input" value="{{ $settings['footer_text'] }}" placeholder="© 2025 جميع الحقوق محفوظة" required>
                    <div class="settings-form-help">النص الذي سيظهر في أسفل الموقع</div>
                </div>
            </div>
        </div>

        <!-- معلومات الاتصال -->
        <div class="settings-glass-card fade-in-up" style="animation-delay: 0.2s;">
            <div class="settings-card-header">
                <div class="settings-header-icon" role="img" aria-label="أيقونة معلومات الاتصال">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <h3 class="settings-card-title">معلومات الاتصال</h3>
            </div>
            <div class="settings-card-body">
                <div class="settings-grid">
                    <div class="settings-form-group">
                        <label for="contact_email" class="settings-form-label">البريد الإلكتروني</label>
                        <input type="email" id="contact_email" name="contact_email" class="settings-form-input" value="{{ $settings['contact_email'] }}" placeholder="مثال: info@qiyamm.sa" required>
                    </div>

                    <div class="settings-form-group">
                        <label for="contact_phone" class="settings-form-label">رقم الهاتف</label>
                        <input type="tel" id="contact_phone" name="contact_phone" class="settings-form-input" value="{{ $settings['contact_phone'] }}" placeholder="+966 50 123 4567" required pattern="[\+]?[0-9\s\-\(\)]{10,20}">
                    </div>
                </div>
            </div>
        </div>

        <!-- روابط التواصل الاجتماعي -->
        <div class="settings-glass-card fade-in-up" style="animation-delay: 0.3s;">
            <div class="settings-card-header">
                <div class="settings-header-icon" role="img" aria-label="أيقونة روابط التواصل الاجتماعي">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M2 12h20"></path>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </div>
                <h3 class="settings-card-title">روابط التواصل الاجتماعي</h3>
            </div>
            <div class="settings-card-body">
                <div class="settings-grid">
                    <div class="settings-form-group">
                        <label for="facebook_url" class="settings-form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-left: 6px; color: #1877F2;">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            فيسبوك
                        </label>
                        <input type="url" id="facebook_url" name="facebook_url" class="settings-form-input" value="{{ $settings['facebook_url'] }}" placeholder="مثال: https://facebook.com/qiyamm">
                    </div>

                    <div class="settings-form-group">
                        <label for="twitter_url" class="settings-form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-left: 6px; color: #000000;">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                            تويتر (X)
                        </label>
                        <input type="url" id="twitter_url" name="twitter_url" class="settings-form-input" value="{{ $settings['twitter_url'] }}" placeholder="مثال: https://twitter.com/qiyamm">
                    </div>

                    <div class="settings-form-group">
                        <label for="instagram_url" class="settings-form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-left: 6px; color: #E4405F;">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                            إنستغرام
                        </label>
                        <input type="url" id="instagram_url" name="instagram_url" class="settings-form-input" value="{{ $settings['instagram_url'] }}" placeholder="مثال: https://instagram.com/qiyamm">
                    </div>

                    <div class="settings-form-group">
                        <label for="linkedin_url" class="settings-form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-left: 6px; color: #0A66C2;">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            لينكد إن
                        </label>
                        <input type="url" id="linkedin_url" name="linkedin_url" class="settings-form-input" value="{{ $settings['linkedin_url'] }}" placeholder="مثال: https://linkedin.com/company/qiyamm">
                    </div>
                </div>
            </div>
        </div>

        <!-- إعدادات متقدمة -->
        <div class="settings-glass-card fade-in-up" style="animation-delay: 0.4s;">
            <div class="settings-card-header">
                <div class="settings-header-icon" role="img" aria-label="أيقونة إعدادات متقدمة">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6m5.196-14.196l-4.243 4.243m0 5.657l-4.243 4.243M23 12h-6m-6 0H5m14.196 5.196l-4.243-4.243m0-5.657l-4.243-4.243"></path>
                    </svg>
                </div>
                <h3 class="settings-card-title">إعدادات متقدمة</h3>
            </div>
            <div class="settings-card-body">
                <div class="settings-form-group">
                    <div class="toggle-switch-wrapper">
                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" {{ $settings['maintenance_mode'] ? 'checked' : '' }} class="toggle-switch-input">
                        <label for="maintenance_mode" class="toggle-switch-label">
                            <span class="toggle-switch-slider"></span>
                            <div class="toggle-switch-text">
                                <div class="toggle-switch-title">وضع الصيانة</div>
                                <div class="settings-form-help">تفعيل وضع الصيانة سيمنع الزوار من الدخول للموقع (المسؤولون يمكنهم الدخول)</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="settings-form-group">
                    <label for="maintenance_message" class="settings-form-label">رسالة الصيانة</label>
                    <textarea id="maintenance_message" name="maintenance_message" class="settings-form-textarea" rows="3" placeholder="نعتذر، الموقع قيد الصيانة حالياً. سنعود قريباً!" maxlength="500">{{ $settings['maintenance_message'] ?? 'نعتذر عن الإزعاج. نقوم حالياً بإجراء بعض التحسينات والصيانة لتقديم تجربة أفضل لك.' }}</textarea>
                    <div class="settings-form-help">
                        <span>الرسالة التي ستظهر للزوار أثناء الصيانة</span>
                        <span class="char-counter" data-target="maintenance_message" role="status" aria-live="polite">
                            <span class="current">0</span> / 500
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="settings-actions fade-in-up" style="animation-delay: 0.5s;">
            <button type="button" class="settings-btn settings-btn-outline" onclick="resetSettings()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                    <path d="M8 16H3v5"></path>
                </svg>
                إعادة تعيين
            </button>
            <button type="submit" class="settings-btn settings-btn-primary" id="saveBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span id="saveBtnText">حفظ التغييرات</span>
                <span id="saveBtnLoader" class="btn-loader" style="display: none;">
                    <svg class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"></path>
                    </svg>
                    جاري الحفظ...
                </span>
            </button>
        </div>
    </form>
@endsection

@push('styles')
<style>
/* ===================================
   Settings Page Glass Design
   =================================== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

.settings-glass-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08),
                0 2px 8px rgba(0, 0, 0, 0.04);
    margin-bottom: 24px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.settings-glass-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12),
                0 4px 12px rgba(0, 0, 0, 0.06);
    border-color: rgba(16, 185, 129, 0.2);
}

.settings-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 24px 28px;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.02));
    border-bottom: 1px solid rgba(16, 185, 129, 0.1);
}

.settings-header-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #10B981, #059669);
    border-radius: 12px;
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    transition: all 0.3s ease;
}

.settings-glass-card:hover .settings-header-icon {
    transform: rotate(5deg) scale(1.05);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

.settings-card-title {
    font-size: 20px;
    font-weight: 700;
    color: #0F172A;
    margin: 0;
    letter-spacing: -0.5px;
}

.settings-card-body {
    padding: 28px;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
}

.settings-form-group {
    margin-bottom: 24px;
}

.settings-form-group:last-child {
    margin-bottom: 0;
}

.settings-form-label {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: #0F172A;
    margin-bottom: 10px;
    font-size: 15px;
    position: relative;
    padding-right: 16px;
    transition: all 0.3s ease;
}

.settings-form-label::before {
    content: '';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    background: linear-gradient(135deg, #10B981, #059669);
    border-radius: 50%;
    opacity: 0;
    transition: all 0.3s ease;
}

.settings-form-group:hover .settings-form-label::before,
.settings-form-input:focus ~ .settings-form-label::before,
.settings-form-textarea:focus ~ .settings-form-label::before {
    opacity: 1;
    transform: translateY(-50%) scale(1.2);
}

.settings-form-input,
.settings-form-textarea {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid rgba(15, 23, 42, 0.1);
    border-radius: 12px;
    font-family: 'IBM Plex Sans Arabic', 'Cairo', sans-serif;
    font-size: 15px;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    background: rgba(255, 255, 255, 0.7);
    color: #0F172A;
    outline: none;
}

.settings-form-input:hover,
.settings-form-textarea:hover {
    border-color: rgba(16, 185, 129, 0.3);
    background: rgba(255, 255, 255, 0.9);
}

.settings-form-input:focus,
.settings-form-textarea:focus {
    border-color: #10B981;
    background: white;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1),
                0 4px 12px rgba(16, 185, 129, 0.15);
    transform: translateY(-1px);
}

.settings-form-textarea {
    min-height: 100px;
    resize: vertical;
    line-height: 1.6;
}

.settings-form-help {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: #64748B;
    margin-top: 8px;
    line-height: 1.5;
}

.char-counter {
    font-size: 12px;
    font-weight: 600;
    color: #94A3B8;
    padding: 4px 10px;
    background: rgba(16, 185, 129, 0.08);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.char-counter .current {
    color: #10B981;
    font-weight: 700;
}

/* Toggle Switch Design */
.toggle-switch-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px;
    background: rgba(16, 185, 129, 0.05);
    border-radius: 16px;
    border: 2px dashed rgba(16, 185, 129, 0.2);
    transition: all 0.3s ease;
}

.toggle-switch-wrapper:hover {
    background: rgba(16, 185, 129, 0.08);
    border-color: rgba(16, 185, 129, 0.3);
}

.toggle-switch-input {
    display: none;
}

.toggle-switch-label {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    cursor: pointer;
    width: 100%;
}

.toggle-switch-slider {
    position: relative;
    width: 56px;
    height: 32px;
    background: #CBD5E1;
    border-radius: 32px;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    flex-shrink: 0;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toggle-switch-slider::before {
    content: '';
    position: absolute;
    width: 26px;
    height: 26px;
    right: 3px;
    top: 3px;
    background: white;
    border-radius: 50%;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.toggle-switch-input:checked + .toggle-switch-label .toggle-switch-slider {
    background: linear-gradient(135deg, #10B981, #059669);
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2),
                inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toggle-switch-input:checked + .toggle-switch-label .toggle-switch-slider::before {
    transform: translateX(-24px);
}

.toggle-switch-text {
    flex: 1;
}

.toggle-switch-title {
    font-size: 16px;
    font-weight: 600;
    color: #0F172A;
    margin-bottom: 6px;
}

/* Action Buttons */
.settings-actions {
    display: flex;
    justify-content: flex-end;
    gap: 16px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 2px dashed rgba(16, 185, 129, 0.15);
}

@media (max-width: 640px) {
    .settings-actions {
        flex-direction: column-reverse;
    }
    
    .settings-btn {
        width: 100%;
    }
}

.settings-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 28px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    font-family: 'IBM Plex Sans Arabic', 'Cairo', sans-serif;
    position: relative;
    overflow: hidden;
}

.settings-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.settings-btn:hover::before {
    transform: translateX(100%);
}

.settings-btn-outline {
    background: rgba(100, 116, 139, 0.1);
    color: #475569;
    border: 2px solid rgba(100, 116, 139, 0.2);
}

.settings-btn-outline:hover {
    background: rgba(100, 116, 139, 0.15);
    border-color: rgba(100, 116, 139, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
}

.settings-btn-outline:active {
    transform: translateY(0);
}

.settings-btn-primary {
    background: linear-gradient(135deg, #10B981, #059669);
    color: white;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
}

.settings-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
}

.settings-btn-primary:active {
    transform: translateY(0);
}

.settings-btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.btn-loader {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.spinner {
    animation: spin 1s linear infinite;
}

.spinner circle,
.spinner path {
    stroke: currentColor;
}

/* Dark Theme Support */
[data-theme="dark"] .settings-glass-card {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(255, 255, 255, 0.1);
}

[data-theme="dark"] .settings-card-title,
[data-theme="dark"] .settings-form-label,
[data-theme="dark"] .toggle-switch-title {
    color: #F1F5F9;
}

[data-theme="dark"] .settings-form-input,
[data-theme="dark"] .settings-form-textarea {
    background: rgba(15, 23, 42, 0.5);
    border-color: rgba(255, 255, 255, 0.1);
    color: #F1F5F9;
}

[data-theme="dark"] .settings-form-input:focus,
[data-theme="dark"] .settings-form-textarea:focus {
    background: rgba(15, 23, 42, 0.7);
}

[data-theme="dark"] .settings-form-help {
    color: #94A3B8;
}

/* Glass Error Messages */
.glass-error-message {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-top: 12px;
    padding: 16px 18px;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(220, 38, 38, 0.95));
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4),
                0 2px 8px rgba(239, 68, 68, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
    color: white;
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}

.glass-error-message::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.glass-error-message:hover::before {
    transform: translateX(100%);
}

.glass-error-message.show {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.glass-error-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s ease-in-out infinite;
}

.glass-error-icon svg {
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.glass-error-content {
    flex: 1;
}

.glass-error-title {
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 4px;
    letter-spacing: -0.3px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.glass-error-text {
    font-size: 13px;
    opacity: 0.95;
    line-height: 1.5;
    font-weight: 500;
}

.glass-error-close {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: white;
}

.glass-error-close:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg) scale(1.1);
}

.glass-error-close:active {
    transform: rotate(90deg) scale(0.95);
}

.input-error {
    border-color: #EF4444 !important;
    animation: shake 0.5s ease;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15),
                0 4px 12px rgba(239, 68, 68, 0.2) !important;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

/* Dark Theme for Error Messages */
[data-theme="dark"] .glass-error-message {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(220, 38, 38, 0.9));
    border-color: rgba(255, 255, 255, 0.2);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Glass Error Message Function - يجب تعريفها أولاً
    window.showGlassError = function(input, title, message) {
        // Remove any existing error messages
        const existingErrors = document.querySelectorAll('.glass-error-message');
        existingErrors.forEach(error => {
            error.classList.remove('show');
            setTimeout(() => error.remove(), 300);
        });
        
        // Add error class to input
        input.classList.add('input-error');
        
        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'glass-error-message';
        errorDiv.setAttribute('role', 'alert');
        errorDiv.setAttribute('aria-live', 'assertive');
        errorDiv.innerHTML = `
            <div class="glass-error-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="glass-error-content">
                <div class="glass-error-title">${title}</div>
                <div class="glass-error-text">${message}</div>
            </div>
            <button type="button" class="glass-error-close" data-input-id="${input.id}" aria-label="إغلاق رسالة الخطأ">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;
        
        // Insert error message after the form group
        input.closest('.settings-form-group').appendChild(errorDiv);
        
        // Add event listener to close button
        const closeBtn = errorDiv.querySelector('.glass-error-close');
        closeBtn.addEventListener('click', function() {
            errorDiv.remove();
            document.getElementById(this.getAttribute('data-input-id')).classList.remove('input-error');
        });
        
        // Animate in
        setTimeout(() => {
            errorDiv.classList.add('show');
        }, 10);
        
        // Remove error class when input changes
        const inputHandler = function() {
            input.classList.remove('input-error');
            const errorMsg = input.closest('.settings-form-group').querySelector('.glass-error-message');
            if (errorMsg) {
                errorMsg.classList.remove('show');
                setTimeout(() => errorMsg.remove(), 300);
            }
            input.removeEventListener('input', inputHandler);
        };
        input.addEventListener('input', inputHandler);
        
        // Auto remove after 8 seconds
        setTimeout(() => {
            const errorMsg = input.closest('.settings-form-group').querySelector('.glass-error-message');
            if (errorMsg && errorMsg.parentElement) {
                errorMsg.classList.remove('show');
                setTimeout(() => {
                    errorMsg.remove();
                    input.classList.remove('input-error');
                }, 300);
            }
        }, 8000);
    };

    // Character counter for textareas - تحديث فوري
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(textarea => {
        const counterElement = document.querySelector(`.char-counter[data-target="${textarea.id}"] .current`);
        if (counterElement) {
            // Function to update counter
            const updateCounter = () => {
                const currentLength = textarea.value.length;
                const maxLength = parseInt(textarea.getAttribute('maxlength'));
                const percentage = (currentLength / maxLength) * 100;
                
                // Update number
                counterElement.textContent = currentLength;
                
                // Update color based on percentage
                if (percentage > 90) {
                    counterElement.style.color = '#EF4444';
                } else if (percentage > 70) {
                    counterElement.style.color = '#F59E0B';
                } else {
                    counterElement.style.color = '#10B981';
                }
            };
            
            // Update on page load
            updateCounter();
            
            // Update on input
            textarea.addEventListener('input', updateCounter);
        }
    });

    // Form submission with loading state
    const form = document.getElementById('settingsForm');
    const saveBtn = document.getElementById('saveBtn');
    const saveBtnText = document.getElementById('saveBtnText');
    const saveBtnLoader = document.getElementById('saveBtnLoader');
    
    if (form && saveBtn) {
        form.addEventListener('submit', function(e) {
            // Show loading state
            saveBtn.disabled = true;
            saveBtnText.style.display = 'none';
            saveBtnLoader.style.display = 'inline-flex';
            
            // Validate required fields first
            const requiredFields = [
                { id: 'site_name', name: 'اسم الموقع' },
                { id: 'footer_text', name: 'نص الفوتر' },
                { id: 'contact_email', name: 'البريد الإلكتروني' },
                { id: 'contact_phone', name: 'رقم الهاتف' }
            ];
            
            for (const field of requiredFields) {
                const input = document.getElementById(field.id);
                if (input && !input.value.trim()) {
                    e.preventDefault();
                    showGlassError(
                        input,
                        `${field.name} مطلوب`,
                        `يرجى إدخال ${field.name}`
                    );
                    saveBtn.disabled = false;
                    saveBtnText.style.display = 'inline';
                    saveBtnLoader.style.display = 'none';
                    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => input.focus(), 300);
                    return;
                }
            }
            
            // Validate email format
            const emailInput = document.getElementById('contact_email');
            if (emailInput && emailInput.value && !emailInput.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                e.preventDefault();
                showGlassError(
                    emailInput,
                    'البريد الإلكتروني غير صحيح',
                    'يرجى إدخال بريد إلكتروني صحيح بصيغة: example@domain.com'
                );
                saveBtn.disabled = false;
                saveBtnText.style.display = 'inline';
                saveBtnLoader.style.display = 'none';
                emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => emailInput.focus(), 300);
                return;
            }
            
            // Validate phone format
            const phoneInput = document.getElementById('contact_phone');
            if (phoneInput && phoneInput.value && !phoneInput.value.match(/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,5}[-\s\.]?[0-9]{1,6}$/)) {
                e.preventDefault();
                showGlassError(
                    phoneInput,
                    'رقم الهاتف غير صحيح',
                    'يرجى إدخال رقم هاتف صحيح (مثال: +966501234567 أو 0501234567)'
                );
                saveBtn.disabled = false;
                saveBtnText.style.display = 'inline';
                saveBtnLoader.style.display = 'none';
                phoneInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => phoneInput.focus(), 300);
                return;
            }

            // Validate URLs
            const urlInputs = [
                { id: 'facebook_url', name: 'فيسبوك' },
                { id: 'twitter_url', name: 'تويتر' },
                { id: 'instagram_url', name: 'إنستغرام' },
                { id: 'linkedin_url', name: 'لينكد إن' }
            ];
            
            for (const urlInput of urlInputs) {
                const input = document.getElementById(urlInput.id);
                if (input && input.value && !input.value.match(/^https?:\/\/.+/)) {
                    e.preventDefault();
                    showGlassError(
                        input,
                        `رابط ${urlInput.name} غير صحيح`,
                        'الرابط يجب أن يبدأ بـ https:// أو http://'
                    );
                    saveBtn.disabled = false;
                    saveBtnText.style.display = 'inline';
                    saveBtnLoader.style.display = 'none';
                    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => input.focus(), 300);
                    return;
                }
            }
        });
    }

    // Reset settings function
    window.resetSettings = function() {
        if (typeof showConfirm === 'function') {
            showConfirm(
                'هل تريد إعادة تعيين جميع الحقول للقيم المحفوظة؟<br><small style="color: #64748B;">سيتم استرجاع القيم من قاعدة البيانات.</small>',
                () => {
                    window.location.reload();
                },
                'إعادة تعيين الحقول',
                'نعم، إعادة التعيين',
                'إلغاء'
            );
        } else {
            if (confirm('هل تريد إعادة تعيين جميع الحقول للقيم المحفوظة؟')) {
                window.location.reload();
            }
        }
    };

    // Show session messages
    @if(session('success'))
        if (typeof showSuccess === 'function') {
            showSuccess('{{ session('success') }}', 'تم الحفظ بنجاح!');
        }
    @endif

    @if(session('error'))
        if (typeof showError === 'function') {
            showError('{{ session('error') }}', 'خطأ!');
        }
    @endif

    @if($errors->any())
        if (typeof showError === 'function') {
            showError('{{ $errors->first() }}', 'خطأ في البيانات!');
        }
    @endif

    // Add input animations
    const inputs = document.querySelectorAll('.settings-form-input, .settings-form-textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            const formGroup = this.closest('.settings-form-group');
            if (formGroup) {
                formGroup.style.transform = 'scale(1.01)';
            }
        });
        
        input.addEventListener('blur', function() {
            const formGroup = this.closest('.settings-form-group');
            if (formGroup) {
                formGroup.style.transform = 'scale(1)';
            }
        });
    });
});
</script>
@endpush
