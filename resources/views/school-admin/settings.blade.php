@extends('layouts.school-admin')

@section('title', 'إعدادات المدرسة')
@section('page-title', 'إعدادات المدرسة')

@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / الإعدادات
@endsection

@push('styles')
<style>
    /* Wahy dark-mode coverage — عناوين/نصوص البطاقات هنا بألوان inline مُصلَّبة */
    html[data-theme="dark"] .sa-settings-page [style*="color: #1a202c"],
    html[data-theme="dark"] .sa-settings-page [style*="color: #2d3748"],
    html[data-theme="dark"] .sa-settings-page [style*="color: #4a5568"] { color: var(--w-text) !important; }
    html[data-theme="dark"] .sa-settings-page [style*="color: #718096"],
    html[data-theme="dark"] .sa-settings-page [style*="color: #a0aec0"] { color: var(--w-text-muted) !important; }
    html[data-theme="dark"] .sa-settings-page [style*="background: #f7fafc"] { background: rgba(255,255,255,0.05) !important; }
    html[data-theme="dark"] .sa-settings-page hr { border-color: var(--w-border); }
</style>
@endpush

@section('content')
<div class="sa-settings-page" style="max-width: 900px; margin: 0 auto;">
    
    <!-- بيانات المدرسة -->
    <div class="card mb-4">
        <div class="card-header" style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-school" style="color: white; font-size: 18px;"></i>
            </div>
            <div>
                <h5 style="font-weight: 700; margin: 0; color: #1a202c;">بيانات المدرسة</h5>
                <p style="font-size: 13px; color: #718096; margin: 0;">تعديل معلومات المدرسة الأساسية</p>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('school-admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="school">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">اسم المدرسة <span style="color: red;">*</span></label>
                        <input type="text" name="school_name" class="form-control @error('school_name') is-invalid @enderror" 
                               value="{{ old('school_name', $school->name) }}" required>
                        @error('school_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">البريد الإلكتروني للمدرسة</label>
                        <input type="email" name="school_email" class="form-control @error('school_email') is-invalid @enderror" 
                               value="{{ old('school_email', $school->email ?? '') }}">
                        @error('school_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">هاتف المدرسة</label>
                        <input type="text" name="school_phone" class="form-control @error('school_phone') is-invalid @enderror" 
                               value="{{ old('school_phone', $school->phone ?? '') }}">
                        @error('school_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">عنوان المدرسة</label>
                        <input type="text" name="school_address" class="form-control @error('school_address') is-invalid @enderror" 
                               value="{{ old('school_address', $school->address ?? '') }}">
                        @error('school_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">وصف المدرسة</label>
                    <textarea name="school_description" class="form-control @error('school_description') is-invalid @enderror" 
                              rows="3">{{ old('school_description', $school->description ?? '') }}</textarea>
                    @error('school_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div style="text-align: left;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save" style="margin-left: 8px;"></i>
                        حفظ بيانات المدرسة
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- بيانات الحساب -->
    <div class="card mb-4">
        <div class="card-header" style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #48c774, #3ec46d); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-user-shield" style="color: white; font-size: 18px;"></i>
            </div>
            <div>
                <h5 style="font-weight: 700; margin: 0; color: #1a202c;">بيانات الحساب</h5>
                <p style="font-size: 13px; color: #718096; margin: 0;">تعديل معلومات حسابك الشخصي</p>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('school-admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="account">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">الاسم الكامل <span style="color: red;">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">البريد الإلكتروني <span style="color: red;">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">رقم الجوال</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                           value="{{ old('phone', $user->phone ?? '') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <hr style="margin: 24px 0;">
                <h6 style="font-weight: 700; color: #4a5568; margin-bottom: 16px;">
                    <i class="fas fa-key" style="margin-left: 8px;"></i>
                    تغيير كلمة المرور <span style="font-weight: 400; color: #a0aec0; font-size: 13px;">(اختياري)</span>
                </h6>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror">
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" name="new_password_confirmation" class="form-control">
                    </div>
                </div>
                
                <div style="text-align: left;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save" style="margin-left: 8px;"></i>
                        حفظ بيانات الحساب
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- إعدادات الإشعارات -->
    <div class="card mb-4">
        <div class="card-header" style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-bell" style="color: white; font-size: 18px;"></i>
            </div>
            <div>
                <h5 style="font-weight: 700; margin: 0; color: #1a202c;">إعدادات الإشعارات</h5>
                <p style="font-size: 13px; color: #718096; margin: 0;">التحكم في تنبيهات الرسائل والإشعارات</p>
            </div>
        </div>
        <div class="card-body">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: #f7fafc; border-radius: 12px; margin-bottom: 12px;">
                <div>
                    <h6 style="font-weight: 700; color: #2d3748; margin: 0 0 4px 0;">تنبيهات الرسائل</h6>
                    <p style="font-size: 13px; color: #718096; margin: 0;">إظهار إشعارات منبثقة عند وصول رسائل جديدة</p>
                </div>
                <div>
                    <button type="button" id="settingsMuteToggle" class="btn btn-sm" 
                            onclick="toggleNotificationMute()"
                            style="min-width: 100px;">
                        <i class="fas fa-bell"></i>
                        <span>مفعّل</span>
                    </button>
                </div>
            </div>
            <p style="font-size: 12px; color: #a0aec0; margin: 8px 0 0 0;">
                <i class="fas fa-info-circle" style="margin-left: 4px;"></i>
                يمكنك أيضاً استخدام زر الصوت <i class="fas fa-volume-up"></i> داخل لوحة الإشعارات لتبديل حالة الصوت سريعاً
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleNotificationMute() {
        if (window.messagesRealTime) {
            const isMuted = window.messagesRealTime.toggleMute();
            updateSettingsMuteButton(isMuted);
        }
    }
    
    function updateSettingsMuteButton(isMuted) {
        const btn = document.getElementById('settingsMuteToggle');
        if (!btn) return;
        
        const icon = btn.querySelector('i');
        const text = btn.querySelector('span');
        
        if (isMuted) {
            btn.className = 'btn btn-sm btn-danger';
            icon.className = 'fas fa-bell-slash';
            text.textContent = 'مكتوم';
        } else {
            btn.className = 'btn btn-sm btn-success';
            icon.className = 'fas fa-bell';
            text.textContent = 'مفعّل';
        }
    }
    
    // تحديث الحالة عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const isMuted = localStorage.getItem('messages_muted') === 'true';
            updateSettingsMuteButton(isMuted);
        }, 100);
    });
</script>
@endpush
@endsection
