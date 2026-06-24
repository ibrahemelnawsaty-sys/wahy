@extends('layouts.auth')

@section('title', 'التحقق الثنائي - قيمّ')
@section('extra_css')
<link rel="stylesheet" href="{{ asset('css/auth-glass.css') }}">
<style>
.code-inputs {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin: 24px 0;
}

.code-input {
    width: 56px !important;
    height: 56px;
    padding: 0 !important;
    text-align: center;
    font-size: 24px;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 2px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 12px;
    color: #F1F5F9 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.code-input:focus {
    background: rgba(255, 255, 255, 0.08) !important;
    border-color: rgba(60, 203, 138, 0.6) !important;
    box-shadow: 0 0 0 4px rgba(60, 203, 138, 0.15), 0 0 24px rgba(60, 203, 138, 0.3) !important;
    transform: scale(1.05);
}

@media (max-width: 480px) {
    .code-inputs {
        gap: 8px;
    }
    .code-input {
        width: 44px !important;
        height: 44px;
        font-size: 20px;
    }
}
</style>
@endsection

@section('content')
<div class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <span class="auth-logo-icon">🔒</span>
                    <span class="auth-logo-text">قيمّ</span>
                </div>
                <h1 class="auth-title">التحقق الثنائي</h1>
                <p class="auth-subtitle">تم إرسال رمز التحقق إلى بريدك الإلكتروني</p>
            </div>

            <!-- Errors Display -->
            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success">
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            <!-- 2FA Form -->
            <form method="POST" action="{{ route('two-factor.verify') }}" class="auth-form" id="twoFactorForm">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" style="text-align: center; display: block;">أدخل رمز التحقق المكون من 6 أرقام</label>
                    
                    <div class="code-inputs">
                        <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="0">
                        <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="1">
                        <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="2">
                        <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="3">
                        <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="4">
                        <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="5">
                    </div>
                    
                    <input type="hidden" name="code" id="hiddenCode">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-submit" id="submitBtn">
                    <span>تحقق الآن</span>
                    <i class="fas fa-shield-alt"></i>
                </button>
            </form>

            <!-- Divider -->
            <div class="auth-divider">
                <span>لم تستلم الرمز؟</span>
            </div>

            <!-- Resend Code -->
            <form method="POST" action="{{ route('two-factor.resend') }}" style="margin-top: 16px;">
                @csrf
                <button type="submit" class="btn btn-secondary btn-submit">
                    <i class="fas fa-sync-alt"></i>
                    <span>إعادة إرسال الرمز</span>
                </button>
            </form>

            <!-- Back to Login -->
            <div class="auth-footer">
                <p><a href="{{ route('login') }}" class="auth-link">العودة لتسجيل الدخول</a></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.code-input');
    const form = document.getElementById('twoFactorForm');
    const hiddenCode = document.getElementById('hiddenCode');
    const submitBtn = document.getElementById('submitBtn');

    // Auto-focus first input
    inputs[0].focus();

    inputs.forEach((input, index) => {
        // Handle input
        input.addEventListener('input', function(e) {
            if (this.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            
            // Update hidden field
            updateHiddenCode();
            
            // Auto-submit when all filled
            if (index === inputs.length - 1 && this.value.length === 1) {
                const allFilled = Array.from(inputs).every(inp => inp.value.length === 1);
                if (allFilled) {
                    setTimeout(() => form.submit(), 300);
                }
            }
        });

        // Handle backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
            }
        });

        // Handle paste
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').slice(0, 6);
            const digits = pastedData.replace(/\D/g, '');
            
            digits.split('').forEach((digit, i) => {
                if (inputs[i]) {
                    inputs[i].value = digit;
                }
            });
            
            updateHiddenCode();
            
            if (digits.length === 6) {
                setTimeout(() => form.submit(), 300);
            }
        });
    });

    function updateHiddenCode() {
        hiddenCode.value = Array.from(inputs).map(inp => inp.value).join('');
    }

    // Prevent form submit if code incomplete
    form.addEventListener('submit', function(e) {
        const code = hiddenCode.value;
        if (code.length !== 6) {
            e.preventDefault();
            inputs[0].focus();
        }
    });

    // Auto-refresh CSRF token
    setInterval(function() {
        fetch('/sanctum/csrf-cookie').then(() => {
            fetch('/two-factor/verify', {method: 'GET'});
        });
    }, 300000); // Every 5 minutes
});
</script>
@endsection
