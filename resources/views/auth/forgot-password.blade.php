@extends('layouts.auth-clean')

@section('title', 'نسيت كلمة المرور - قيمّ')
@section('meta_description', 'استعادة كلمة المرور لحسابك في منصة قيمّ')

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
                    <span class="auth-logo-icon">🔐</span>
                    <span class="auth-logo-text">قيمّ</span>
                </div>
                <h1 class="auth-title">نسيت كلمة المرور؟</h1>
                <p class="auth-subtitle">لا تقلق! سنرسل لك رابط إعادة تعيين كلمة المرور</p>
            </div>

            <!-- Success Message -->
            @if (session('status'))
                <div class="alert alert-success alert-animated">
                    <div class="alert-content">
                        <svg class="alert-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <div>
                            <h4 class="alert-title">تم بنجاح!</h4>
                            <p class="alert-message">{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
            @endif

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

            <!-- Password Reset Form -->
            <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                @csrf

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <div class="form-input-wrapper">
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            class="form-input" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                            autocomplete="email"
                            placeholder="مثال: user@qiyamm.sa"
                        >
                        <svg class="form-icon" style="width:20px;height:20px" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                    </div>
                    <p class="form-hint">سنرسل لك رابط إعادة تعيين كلمة المرور على هذا البريد</p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-submit" id="resetBtn">
                    <span class="btn-text">إرسال رابط إعادة التعيين</span>
                    <span class="btn-loader" style="display:none">
                        <svg class="spinner" viewBox="0 0 50 50">
                            <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"/>
                        </svg>
                        جاري الإرسال...
                    </span>
                    <svg class="btn-icon" style="width:18px;height:18px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                    </svg>
                </button>
            </form>

            <!-- Divider -->
            <div class="auth-divider">
                <span>أو</span>
            </div>

            <!-- Back to Login -->
            <div class="auth-footer">
                <p>تذكرت كلمة المرور؟ <a href="{{ route('login') }}" class="auth-link">تسجيل الدخول</a></p>
            </div>
        </div>
    </div>
</div>

<script>
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
