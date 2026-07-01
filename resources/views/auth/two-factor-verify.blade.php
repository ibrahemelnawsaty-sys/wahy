@extends('layouts.auth-clean')

@section('title', 'التحقق الثنائي - قيمّ')
@section('extra_css')
<link rel="stylesheet" href="{{ asset('css/auth-glass.css') }}">
<style>
.code-inputs {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin: 32px 0;
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

/* Wahy dark-mode coverage: تباين حقول رمز التحقق في الوضع النهاري */
html[data-theme="light"] .code-input {
    background: rgba(15, 23, 42, 0.04) !important;
    border-color: rgba(15, 23, 42, 0.12) !important;
    color: #0F172A !important;
}
html[data-theme="light"] .code-input:focus {
    background: rgba(60, 203, 138, 0.06) !important;
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

            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <!-- Errors Display -->
            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
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
            <div class="auth-footer" style="margin-top: 24px;">
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

    if (!inputs.length || !form || !hiddenCode) {
        console.error('Missing elements');
        return;
    }

    // التركيز على أول خانة
    setTimeout(() => inputs[0].focus(), 100);

    function syncHiddenCode() {
        hiddenCode.value = Array.from(inputs).map(inp => inp.value).join('');
    }

    // معالجة الإدخال في كل خانة
    inputs.forEach((input, index) => {
        // عند الكتابة
        input.addEventListener('input', function(e) {
            const value = this.value;
            
            // السماح بالأرقام فقط
            if (value && !/^\d$/.test(value)) {
                this.value = '';
                return;
            }

            syncHiddenCode();

            // الانتقال للخانة التالية تلقائياً
            if (value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            // إرسال النموذج تلقائياً عند اكتمال الكود
            if (index === inputs.length - 1 && value.length === 1) {
                const allFilled = Array.from(inputs).every(inp => inp.value.length === 1);
                if (allFilled) {
                    setTimeout(() => form.submit(), 300);
                }
            }
        });

        // معالجة زر Backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace') {
                if (!this.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                }
            } else if (e.key === 'ArrowLeft' && index > 0) {
                inputs[index - 1].focus();
            } else if (e.key === 'ArrowRight' && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        // معالجة اللصق في كل خانة
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            
            const pastedData = (e.clipboardData || window.clipboardData).getData('text');
            const digits = pastedData.replace(/\D/g, '').slice(0, 6);

            if (digits.length > 0) {
                // توزيع الأرقام على الخانات
                digits.split('').forEach((digit, i) => {
                    if (inputs[i]) {
                        inputs[i].value = digit;
                    }
                });
                
                syncHiddenCode();
                
                // التركيز على آخر خانة تم ملؤها
                const lastFilledIndex = Math.min(digits.length - 1, inputs.length - 1);
                inputs[lastFilledIndex].focus();
                
                // إرسال النموذج تلقائياً إذا كان الكود كاملاً
                if (digits.length === 6) {
                    setTimeout(() => form.submit(), 300);
                }
            }
        });

        // عند النقر على الخانة
        input.addEventListener('click', function() {
            this.select();
        });

        // عند التركيز على الخانة
        input.addEventListener('focus', function() {
            this.select();
        });
    });

    // منع إرسال النموذج إذا كان الكود غير مكتمل
    form.addEventListener('submit', function(e) {
        syncHiddenCode();
        if (hiddenCode.value.length !== 6) {
            e.preventDefault();
            alert('يرجى إدخال الكود المكون من 6 أرقام');
            inputs[0].focus();
        }
    });
});
</script>
@endsection
