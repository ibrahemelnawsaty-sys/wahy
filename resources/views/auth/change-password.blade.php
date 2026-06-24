<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغيير كلمة المرور - {{ setting('site_name', 'منصة قيمّ') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .password-card {
            max-width: 550px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .lock-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .card-title {
            color: white;
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .card-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            margin-top: 8px;
            position: relative;
            z-index: 2;
        }
        
        .card-body {
            padding: 40px 35px;
        }
        
        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(245, 158, 11, 0.1) 100%);
            border-right: 4px solid #f59e0b;
            color: #92400e;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
            border-right: 4px solid #ef4444;
            color: #991b1b;
        }
        
        .form-label {
            font-weight: 700;
            color: #4b5563;
            margin-bottom: 10px;
            font-size: 15px;
        }
        
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        .input-group-text {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border: 2px solid #e5e7eb;
            border-left: none;
            border-radius: 0 12px 12px 0;
            color: #667eea;
            font-size: 18px;
            padding: 0 18px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 12px 0 0 12px;
        }
        
        .btn-change {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 17px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        
        .btn-logout {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 2px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        
        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
        }
        
        .info-box {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(37, 99, 235, 0.08) 100%);
            border-right: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 12px;
            margin-top: 25px;
        }
        
        .info-box-title {
            color: #1e40af;
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 16px;
        }
        
        .info-box ul {
            margin: 0;
            padding-right: 20px;
            color: #1e3a8a;
            line-height: 2;
            font-size: 14px;
        }
        
        .password-strength {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }
        
        .invalid-feedback {
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="password-card">
        <div class="card-header">
            <div class="lock-icon">🔐</div>
            <h1 class="card-title">تغيير كلمة المرور</h1>
            <p class="card-subtitle">يجب عليك تغيير كلمة المرور المؤقتة</p>
        </div>
        
        <div class="card-body">
            @if(session('warning'))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('warning') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger">
                <strong><i class="fas fa-times-circle me-2"></i>خطأ:</strong>
                <ul class="mb-0 mt-2" style="padding-right: 20px;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('password.change.update') }}" id="passwordForm">
                @csrf
                
                <div class="mb-4">
                    <label for="current_password" class="form-label">
                        <i class="fas fa-key me-2"></i>
                        كلمة المرور المؤقتة
                    </label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" 
                               name="current_password" 
                               required>
                        <span class="input-group-text">
                            <i class="fas fa-eye toggle-password" data-target="current_password" style="cursor: pointer;"></i>
                        </span>
                    </div>
                    @error('current_password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>
                        كلمة المرور الجديدة
                    </label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required
                               minlength="8">
                        <span class="input-group-text">
                            <i class="fas fa-eye toggle-password" data-target="password" style="cursor: pointer;"></i>
                        </span>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <small class="text-muted" id="strengthText"></small>
                    @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">
                        <i class="fas fa-check-circle me-2"></i>
                        تأكيد كلمة المرور
                    </label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required
                               minlength="8">
                        <span class="input-group-text">
                            <i class="fas fa-eye toggle-password" data-target="password_confirmation" style="cursor: pointer;"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-change">
                    <i class="fas fa-save me-2"></i>
                    تغيير كلمة المرور
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    تسجيل الخروج
                </button>
            </form>

            <div class="info-box">
                <div class="info-box-title">
                    <i class="fas fa-shield-alt me-2"></i>
                    متطلبات كلمة المرور
                </div>
                <ul>
                    <li>8 أحرف على الأقل</li>
                    <li>يُفضل استخدام مزيج من الأحرف الكبيرة والصغيرة</li>
                    <li>تضمين أرقام ورموز خاصة لمزيد من الأمان</li>
                    <li>تجنب استخدام كلمات مرور سهلة التخمين</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', function() {
                const targetId = this.dataset.target;
                const input = document.getElementById(targetId);
                
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                }
            });
        });

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength-bar';
            
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'ضعيفة';
                strengthText.style.color = '#ef4444';
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'متوسطة';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'قوية';
                strengthText.style.color = '#10b981';
            }
        });
    </script>
</body>
</html>
