<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل طالب جديد - {{ $school->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            max-width: 600px;
            margin: 40px auto;
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .school-logo i {
            font-size: 40px;
            color: #56ab2f;
        }
        
        .form-control:focus {
            border-color: #56ab2f;
            box-shadow: 0 0 0 0.2rem rgba(86, 171, 47, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            border: none;
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(86, 171, 47, 0.4);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        .required::after {
            content: " *";
            color: #dc3545;
        }
        
        .section-divider {
            border-top: 2px solid #e9ecef;
            margin: 30px 0 20px;
            padding-top: 20px;
        }
        
        .section-title {
            color: #56ab2f;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-card">
                <div class="card-header">
                    <div class="school-logo">
                        <i class="fas fa-school"></i>
                    </div>
                    <h3 class="mb-2">{{ $school->name }}</h3>
                    <p class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        تسجيل طالب جديد
                    </p>
                </div>
                
                <div class="card-body p-4">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>يرجى تصحيح الأخطاء التالية:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('public.register.student.submit', $school->student_token) }}">
                        @csrf
                        
                        <!-- بيانات الطالب -->
                        <h5 class="section-title">
                            <i class="fas fa-user-circle me-2"></i>
                            بيانات الطالب
                        </h5>
                        
                        <div class="row g-3">
                            <!-- الاسم الكامل -->
                            <div class="col-12">
                                <label for="name" class="form-label required">الاسم الكامل</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                </div>
                                @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- البريد الإلكتروني -->
                            <div class="col-12">
                                <label for="email" class="form-label required">البريد الإلكتروني</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                </div>
                                @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- تاريخ الميلاد -->
                            <div class="col-md-6">
                                <label for="birth_date" class="form-label required">تاريخ الميلاد</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                           id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required>
                                </div>
                                @error('birth_date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- المرحلة الدراسية -->
                            <div class="col-md-6">
                                <label for="grade_level" class="form-label required">المرحلة الدراسية</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                                    <input type="text" class="form-control @error('grade_level') is-invalid @enderror" 
                                           id="grade_level" name="grade_level" value="{{ old('grade_level') }}" 
                                           placeholder="مثال: الصف الأول الابتدائي" required>
                                </div>
                                @error('grade_level')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- كلمة المرور -->
                            <div class="col-md-6">
                                <label for="password" class="form-label required">كلمة المرور</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" minlength="8" required>
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>يجب أن تكون كلمة المرور 8 أحرف على الأقل</small>
                                @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- تأكيد كلمة المرور -->
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label required">تأكيد كلمة المرور</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                        </div>

                        <!-- بيانات ولي الأمر -->
                        <div class="section-divider">
                            <h5 class="section-title">
                                <i class="fas fa-user-friends me-2"></i>
                                بيانات ولي الأمر
                            </h5>
                        </div>

                        <div class="row g-3">
                            <!-- اسم ولي الأمر -->
                            <div class="col-12">
                                <label for="parent_name" class="form-label required">اسم ولي الأمر</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <input type="text" class="form-control @error('parent_name') is-invalid @enderror" 
                                           id="parent_name" name="parent_name" value="{{ old('parent_name') }}" required>
                                </div>
                                @error('parent_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- بريد ولي الأمر -->
                            <div class="col-12">
                                <label for="parent_email" class="form-label required">بريد ولي الأمر الإلكتروني</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control @error('parent_email') is-invalid @enderror" 
                                           id="parent_email" name="parent_email" value="{{ old('parent_email') }}" required>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    سيتم إرسال نسخة من إشعارات الطالب إلى هذا البريد
                                </small>
                                @error('parent_email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- جوال ولي الأمر -->
                            <div class="col-12">
                                <label for="parent_phone" class="form-label required">جوال ولي الأمر</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control @error('parent_phone') is-invalid @enderror" 
                                           id="parent_phone" name="parent_phone" value="{{ old('parent_phone') }}" 
                                           placeholder="05xxxxxxxx" required>
                                </div>
                                @error('parent_phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- زر التسجيل -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary btn-register w-100">
                                    <i class="fas fa-user-plus me-2"></i>
                                    إرسال طلب التسجيل
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            سيتم مراجعة طلبك من قبل إدارة المدرسة وإخطارك بالقرار
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
