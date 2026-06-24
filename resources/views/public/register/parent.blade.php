<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل ولي أمر جديد - {{ $school->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
            color: #17a2b8;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #17a2b8;
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.4);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        .required::after {
            content: " *";
            color: #dc3545;
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
                        <i class="fas fa-users me-2"></i>
                        تسجيل ولي أمر جديد
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

                    <form method="POST" action="{{ route('public.register.parent.submit', $school->parent_token) }}">
                        @csrf
                        
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
                                <label for="email" class="form-label">البريد الإلكتروني (اختياري)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}">
                                </div>
                                @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- رقم الجوال -->
                            <div class="col-12">
                                <label for="phone" class="form-label required">رقم الجوال</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" 
                                           placeholder="05xxxxxxxx" required>
                                </div>
                                @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- كلمة المرور -->
                            <div class="col-md-6">
                                <label for="password" class="form-label required">كلمة المرور</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                </div>
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

                            <!-- الصلة -->
                            <div class="col-12">
                                <label for="relationship" class="form-label required">الصلة بالطالب</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-heart"></i></span>
                                    <select class="form-select @error('relationship') is-invalid @enderror" 
                                            id="relationship" name="relationship" required>
                                        <option value="">-- اختر الصلة --</option>
                                        <option value="أب" {{ old('relationship') == 'أب' ? 'selected' : '' }}>أب</option>
                                        <option value="أم" {{ old('relationship') == 'أم' ? 'selected' : '' }}>أم</option>
                                        <option value="وصي" {{ old('relationship') == 'وصي' ? 'selected' : '' }}>وصي</option>
                                        <option value="جد" {{ old('relationship') == 'جد' ? 'selected' : '' }}>جد</option>
                                        <option value="جدة" {{ old('relationship') == 'جدة' ? 'selected' : '' }}>جدة</option>
                                        <option value="عم" {{ old('relationship') == 'عم' ? 'selected' : '' }}>عم</option>
                                        <option value="خال" {{ old('relationship') == 'خال' ? 'selected' : '' }}>خال</option>
                                        <option value="أخ" {{ old('relationship') == 'أخ' ? 'selected' : '' }}>أخ</option>
                                        <option value="أخت" {{ old('relationship') == 'أخت' ? 'selected' : '' }}>أخت</option>
                                    </select>
                                </div>
                                @error('relationship')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- أسماء الأبناء -->
                            <div class="col-12">
                                <label for="children_names" class="form-label required">أسماء الأبناء في المدرسة</label>
                                <textarea class="form-control @error('children_names') is-invalid @enderror" 
                                          id="children_names" name="children_names" rows="2" 
                                          placeholder="اكتب أسماء أبنائك الطلاب في المدرسة (كل اسم في سطر)"
                                          required>{{ old('children_names') }}</textarea>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    مثال: محمد أحمد العلي، فاطمة أحمد العلي
                                </small>
                                @error('children_names')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- العنوان -->
                            <div class="col-12">
                                <label for="address" class="form-label">العنوان (اختياري)</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="2" 
                                          placeholder="حي السلام - شارع الملك فهد">{{ old('address') }}</textarea>
                                @error('address')
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
