@extends('layouts.auth-clean')

@section('title', 'إعادة تعيين كلمة المرور - قيمّ')
@section('meta_description', 'إعادة تعيين كلمة المرور لحسابك في منصة قيمّ')

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
                    <span class="auth-logo-icon">🔑</span>
                    <span class="auth-logo-text">قيمّ</span>
                </div>
                <h1 class="auth-title">إعادة تعيين كلمة المرور</h1>
                <p class="auth-subtitle">أدخل كلمة المرور الجديدة لحسابك</p>
            </div>

            <!-- Errors Display -->
            @if ($errors->any())
                <div class="alert alert-error alert-animated">
                    <div class="alert-content">
                        <svg class="alert-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99zM11 16v-2h2v2h-2zm0-4v-4h2v4h-2z"/>
                        </svg>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p class="alert-message">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Reset Password Form -->
            <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <div class="form-input-wrapper">
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            class="form-input" 
                            value="{{ old('email', $email ?? '') }}" 
                            required 
                            autofocus
                            autocomplete="email"
                            placeholder="مثال: user@qiyamm.sa"
                        >
                        <svg class="form-icon" style="width:20px;height:20px" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور الجديدة</label>
                    <div class="form-input-wrapper">
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            class="form-input" 
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        >
                        <svg class="form-icon" style="width:20px;height:20px" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9z"/>
                        </svg>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')" aria-label="إظهار/إخفاء كلمة المرور">
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
                    <p class="form-hint">يجب أن تكون 8 أحرف على الأقل</p>
                </div>

                <!-- Password Confirmation Field -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                    <div class="form-input-wrapper">
                        <input 
                            id="password_confirmation" 
                            type="password" 
                            name="password_confirmation" 
                            class="form-input" 
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        >
                        <svg class="form-icon" style="width:20px;height:20px" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9z"/>
                        </svg>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')" aria-label="إظهار/إخفاء تأكيد كلمة المرور">
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
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-submit" id="resetBtn">
                    <span class="btn-text">إعادة تعيين كلمة المرور</span>
                    <span class="btn-loader" style="display:none">
                        <svg class="spinner" viewBox="0 0 50 50">
                            <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"/>
                        </svg>
                        جاري الحفظ...
                    </span>
                    <svg class="btn-icon" style="width:18px;height:18px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </button>
            </form>

            <!-- Back to Login -->
            <div class="auth-footer" style="margin-top: 24px;">
                <p><a href="{{ route('login') }}" class="auth-link">العودة لتسجيل الدخول</a></p>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Password Visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.closest('.form-input-wrapper').querySelector('.password-toggle');
    const eyeIcon = button.querySelector('.eye-icon');
    const eyeOffIcon = button.querySelector('.eye-off-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
    } else {
        field.type = 'password';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
    }
}

// Loading State
document.querySelector('.auth-form').addEventListener('submit', function(e) {
    const btn = document.getElementById('resetBtn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoader = btn.querySelector('.btn-loader');
    const btnIcon = btn.querySelector('.btn-icon');
    
    btn.disabled = true;
    btnText.style.display = 'none';
    btnIcon.style.display = 'none';
    btnLoader.style.display = 'flex';
});
</script>
@endsection
