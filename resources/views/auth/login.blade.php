@extends('layouts.auth-clean')

@section('title', 'تسجيل الدخول - قيمّ')
@section('meta_description', 'سجل دخولك إلى منصة قيمّ التعليمية')

@section('extra_css')
<link rel="stylesheet" href="{{ asset('css/login-enhancements.css') }}">
@endsection

@section('content')
<div class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <span class="auth-logo-icon">🎯</span>
                    <span class="auth-logo-text">قيمّ</span>
                </div>
                <h1 class="auth-title">مرحباً بعودتك</h1>
                <p class="auth-subtitle">سجل دخولك للوصول إلى حسابك</p>
            </div>

            <!-- Global Alerts (نجاح / أخطاء عامة) -->
            @if (session('success'))
                <div class="alert alert-success alert-animated" role="alert" aria-live="polite">
                    <div class="alert-content">
                        <svg class="alert-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <div>
                            <h4 class="alert-title">نجاح</h4>
                            <p class="alert-message">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error') || $errors->has('error'))
                <div class="alert alert-error alert-animated" role="alert" aria-live="polite">
                    <div class="alert-content">
                        <svg class="alert-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99zM11 16v-2h2v2h-2zm0-4v-4h2v4h-2z"/>
                        </svg>
                        <div>
                            <h4 class="alert-title">تنبيه هام</h4>
                            <p class="alert-message">
                                {{ session('error') ?? $errors->first('error') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <div class="form-input-wrapper">
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            class="form-input @error('email') form-input-error @enderror" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                            autocomplete="email"
                            placeholder="مثال: user@qiyamm.sa"
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                        >
                        <svg class="form-icon" style="width:20px;height:20px" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </div>
                    @error('email')
                        <p id="email-error" class="form-error-text">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <div class="form-input-wrapper">
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            class="form-input @error('password') form-input-error @enderror" 
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                        >
                        <svg class="form-icon" style="width:20px;height:20px" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9z"/></svg>
                        <button 
                            type="button" 
                            class="password-toggle" 
                            onclick="togglePassword()" 
                            aria-label="إظهار أو إخفاء كلمة المرور"
                            aria-pressed="false"
                        >
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-off-icon" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p id="password-error" class="form-error-text">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="form-options">
                    <div class="checkbox-group">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">تذكرني</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="forgot-password">نسيت كلمة المرور؟</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-submit" id="loginBtn">
                    <span class="btn-text">تسجيل الدخول</span>
                    <span class="btn-loader" style="display:none">
                        <svg class="spinner" viewBox="0 0 50 50">
                            <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"/>
                        </svg>
                        جاري التحقق...
                    </span>
                    <svg class="btn-icon" style="width:18px;height:18px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </button>
            </form>

            <!-- Divider -->
            <div class="auth-divider">
                <span>أو</span>
            </div>

            <!-- Register Link -->
            <div class="auth-footer">
                <p>ليس لديك حساب؟ <a href="{{ route('register') }}" class="auth-link">إنشاء حساب جديد</a></p>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Password Visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.querySelector('.eye-icon');
    const eyeOffIcon = document.querySelector('.eye-off-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
    } else {
        passwordInput.type = 'password';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
    }
}

// Loading State with Error Handling
const loginForm = document.querySelector('.auth-form');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        const btn = document.getElementById('loginBtn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoader = btn.querySelector('.btn-loader');
        const btnIcon = btn.querySelector('.btn-icon');
        
        // التحقق من صحة البيانات قبل الإرسال
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const emailError = document.getElementById('email-error');
        const passwordError = document.getElementById('password-error');
        
        // إعادة تعيين رسائل الأخطاء البسيطة في الواجهة
        if (emailError) emailError.textContent = '';
        if (passwordError) passwordError.textContent = '';
        
        if (!email.value || !password.value) {
            e.preventDefault();
            if (!email.value && emailError) {
                emailError.textContent = 'يرجى إدخال البريد الإلكتروني';
            }
            if (!password.value && passwordError) {
                passwordError.textContent = 'يرجى إدخال كلمة المرور';
            }
            return;
        }
        
        // إظهار حالة التحميل
        btn.disabled = true;
        btnText.style.display = 'none';
        btnIcon.style.display = 'none';
        btnLoader.style.display = 'flex';
    });
}

// Auto-dismiss alerts after 10 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 10000);
    });
});
</script>
@endsection
