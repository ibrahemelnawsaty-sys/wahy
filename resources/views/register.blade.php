@extends('layouts.auth')

@section('title', 'إنشاء حساب جديد - قيمّ')
@section('meta_description', 'سجل في منصة قيمّ التعليمية وابدأ رحلتك التعليمية')

@section('content')
{{-- Issue #30/#31: تحسينات الجوال للنموذج والنافذة المنبثقة --}}
<style>
    @media (max-width: 640px) {
        /* توحيد شكل select مع باقي حقول الإدخال */
        #registerForm select.form-select {
            min-height: 48px;
            font-size: 16px;
            line-height: 1.5;
            padding-block: 12px;
        }
        /* تأكيد استجابة زر "حسناً سأنتظر" على الجوال */
        #successModal {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            padding: 20px;
        }
        #successModal .modal-content { padding: 28px 20px; max-height: 92vh; overflow-y: auto; }
        #successModal a[role="button"] {
            display: block !important;
            width: 100%;
            box-sizing: border-box;
            padding: 16px !important;
            min-height: 50px;
            font-size: 17px;
        }
    }
</style>
<!-- Success Popup Modal -->
@if(session('registration_success'))
<div id="successModal" class="modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; z-index: 9999; animation: fadeIn 0.3s ease; padding: 20px; overflow-y: auto;">
    <div class="modal-content" style="background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%); border-radius: 24px; padding: 40px; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.25); animation: slideUp 0.4s ease; position: relative;">
        <!-- Success Icon -->
        <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; margin: 0 auto 25px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);">
            <svg style="width: 50px; height: 50px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <!-- Title -->
        <h2 style="color: #1e293b; font-size: 26px; font-weight: 700; margin-bottom: 15px;">
            تم استلام طلبك بنجاح! 🎉
        </h2>
        
        <!-- Welcome Message -->
        <p style="color: #475569; font-size: 16px; line-height: 1.8; margin-bottom: 25px;">
            مرحباً <strong style="color: #667eea;">{{ session('user_name') }}</strong>!
        </p>
        
        <!-- Info Box -->
        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; border: 2px solid #f59e0b;">
            <div style="font-size: 40px; margin-bottom: 10px;">⏳</div>
            <h3 style="color: #92400e; font-size: 18px; font-weight: 700; margin-bottom: 10px;">
                طلبك قيد المراجعة
            </h3>
            <p style="color: #a16207; font-size: 14px; line-height: 1.8; margin: 0;">
                سيتم مراجعة طلب التسجيل من قبل فريق الإدارة.<br>
                <strong>سنرسل لك إشعاراً عبر البريد الإلكتروني فور الموافقة.</strong>
            </p>
        </div>
        
        <!-- Email Confirmation -->
        <div style="background: #f0fdf4; border-radius: 12px; padding: 15px; margin-bottom: 25px;">
            <p style="color: #166534; font-size: 14px; margin: 0;">
                ✉️ تم إرسال رسالة تأكيد إلى بريدك الإلكتروني
            </p>
        </div>
        
        <!-- Time Estimate -->
        <p style="color: #64748b; font-size: 13px; margin-bottom: 25px;">
            ⏱️ عادةً ما تستغرق المراجعة من <strong>24 إلى 48 ساعة</strong> في أيام العمل
        </p>
        
        <!-- Close Button -->
        <a href="{{ route('login') }}"
           role="button"
           style="position: relative; z-index: 2; display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 40px; border-radius: 12px; font-size: 16px; font-weight: 700; text-decoration: none; transition: all 0.3s; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); pointer-events: auto; touch-action: manipulation; -webkit-tap-highlight-color: rgba(255,255,255,.15); cursor: pointer;">
            حسناً، سأنتظر ✓
        </a>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>
@endif

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="/" class="auth-logo">
                <span class="auth-logo-icon">🌟</span>
            </a>
            <h1 class="auth-title">إنشاء حساب جديد</h1>
            <p class="auth-subtitle">انضم إلى منصة قيمّ وابدأ رحلتك التعليمية</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <svg class="alert-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="width:20px;height:20px">
                    <path d="M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99zM11 16v-2h2v2h-2zm0-4v-4h2v4h-2z"/>
                </svg>
                <div>
                    @foreach ($errors->all() as $error)
                        <p style="margin: 4px 0;">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="auth-form" id="registerForm">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label form-label-required">الاسم الكامل</label>
                <input id="name" type="text" name="name" class="form-input @error('name') error @enderror" value="{{ old('name') }}" required autofocus placeholder="أدخل اسمك الكامل">
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label form-label-required">البريد الإلكتروني</label>
                <input id="email" type="email" name="email" class="form-input @error('email') error @enderror" value="{{ old('email') }}" required autocomplete="email" placeholder="example@domain.com">
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">رقم الجوال</label>
                <input id="phone" type="tel" name="phone" class="form-input @error('phone') error @enderror" value="{{ old('phone') }}" placeholder="05xxxxxxxx" pattern="[0-9]{10}">
                @error('phone')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="role" class="form-label form-label-required">نوع الحساب</label>
                <select id="role" name="role" class="form-select @error('role') error @enderror" required>
                    <option value="">اختر نوع الحساب</option>
                    <option value="school_admin" {{ old('role') == 'school_admin' ? 'selected' : '' }}>مدير مدرسة</option>
                    <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>معلم</option>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>طالب</option>
                    <option value="parent" {{ old('role') == 'parent' ? 'selected' : '' }}>ولي أمر</option>
                </select>
                @error('role')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label form-label-required">كلمة المرور</label>
                <div class="password-group">
                    <input id="password" type="password" name="password" class="form-input @error('password') error @enderror" required autocomplete="new-password" placeholder="••••••••" minlength="8" onkeyup="checkPasswordStrength()">
                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')" aria-label="إظهار أو إخفاء كلمة المرور">
                        <svg id="toggleIcon1" class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="toggleIcon1-off" class="eye-off-icon" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
                <span class="password-strength-text" id="strengthText"></span>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label form-label-required">تأكيد كلمة المرور</label>
                <div class="password-group">
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" required autocomplete="new-password" placeholder="••••••••" minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'toggleIcon2')" aria-label="إظهار أو إخفاء تأكيد كلمة المرور">
                        <svg id="toggleIcon2" class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="toggleIcon2-off" class="eye-off-icon" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">أوافق على <a href="/terms" target="_blank">الشروط والأحكام</a> و <a href="/privacy" target="_blank">سياسة الخصوصية</a></label>
            </div>

            <button type="submit" class="btn btn-primary btn-submit">
                <span>إنشاء حساب</span>
                <svg style="width:18px;height:18px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </button>
        </form>

        <div class="auth-divider"><span>أو</span></div>

        <div class="auth-footer">
            <p>لديك حساب بالفعل؟ <a href="/login">تسجيل الدخول</a></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const eyeIcon = document.getElementById(iconId);
    const eyeOffIcon = document.getElementById(iconId + '-off');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (eyeIcon) eyeIcon.style.display = 'none';
        if (eyeOffIcon) eyeOffIcon.style.display = 'block';
    } else {
        passwordInput.type = 'password';
        if (eyeIcon) eyeIcon.style.display = 'block';
        if (eyeOffIcon) eyeOffIcon.style.display = 'none';
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    strengthBar.className = 'password-strength-bar';
    if (password.length === 0) {
        strengthText.textContent = '';
    } else if (strength <= 2) {
        strengthBar.classList.add('weak');
        strengthText.textContent = 'كلمة مرور ضعيفة';
        strengthText.style.color = '#EF4444';
    } else if (strength <= 4) {
        strengthBar.classList.add('medium');
        strengthText.textContent = 'كلمة مرور متوسطة';
        strengthText.style.color = '#F59E0B';
    } else {
        strengthBar.classList.add('strong');
        strengthText.textContent = 'كلمة مرور قوية';
        strengthText.style.color = '#3CCB8A';
    }
}

const phoneInput = document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 10) value = value.slice(0, 10);
        e.target.value = value;
    });
}

document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirmation').value;
    if (password !== passwordConfirm) {
        e.preventDefault();
        alert('كلمتا المرور غير متطابقتين');
        return false;
    }
    const terms = document.getElementById('terms').checked;
    if (!terms) {
        e.preventDefault();
        alert('يجب الموافقة على الشروط والأحكام');
        return false;
    }
});
</script>
@endpush