@extends('layouts.school-admin')

@section('page-title', 'استيراد وتصدير البيانات')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / استيراد وتصدير البيانات
@endsection

@section('content')

<!-- Header Section -->
<div class="row mb-4">
    <div class="col-12">
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 24px; padding: 40px; box-shadow: 0 20px 60px rgba(139, 92, 246, 0.3); position: relative; overflow: hidden;">
            <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
            <div style="position: relative; z-index: 1;">
                <h1 style="font-size: 32px; font-weight: 800; color: white; margin-bottom: 8px;">
                    <i class="fas fa-file-excel me-2"></i>
                    استيراد وتصدير البيانات
                </h1>
                <p style="color: rgba(255,255,255,0.95); font-size: 16px; margin: 0;">
                    قم بتحميل القوالب، املأها، ثم ارفعها لتسجيل المستخدمين دفعة واحدة
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Download Templates Section -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header">
                <div class="header-content">
                    <h5 class="card-title">
                        <i class="fas fa-download me-2" style="color: #667eea;"></i>
                        تحميل القوالب
                    </h5>
                    <p class="card-subtitle">قم بتحميل القوالب الجاهزة واملأها بالبيانات</p>
                </div>
            </div>
            <div class="modern-card-body">
                <div class="row g-3">
                    <!-- Students Template -->
                    <div class="col-md-4">
                        <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 15px; padding: 25px; text-align: center; border: 2px solid #e2e8f0; transition: all 0.3s; height: 100%;"
                             onmouseover="this.style.borderColor='#48c6ef'; this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 30px rgba(72, 198, 239, 0.2)'"
                             onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div style="font-size: 48px; margin-bottom: 15px;">📥</div>
                            <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 10px;">قالب الطلاب</h6>
                            <p style="font-size: 0.85rem; color: #718096; margin-bottom: 15px;">قم بتحميل القالب واملأ بيانات الطلاب</p>
                            <form action="{{ route('school-admin.download-template') }}" method="GET" style="display: inline;">
                                <input type="hidden" name="role" value="students">
                                <button type="submit" style="background: linear-gradient(135deg, #48c6ef 0%, #6f86d6 100%); color: white; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.3s;"
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                    <i class="fas fa-download me-2"></i>تحميل القالب
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Teachers Template -->
                    <div class="col-md-4">
                        <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 15px; padding: 25px; text-align: center; border: 2px solid #e2e8f0; transition: all 0.3s; height: 100%;"
                             onmouseover="this.style.borderColor='#667eea'; this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 30px rgba(102, 126, 234, 0.2)'"
                             onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div style="font-size: 48px; margin-bottom: 15px;">👨‍🏫</div>
                            <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 10px;">قالب المعلمين</h6>
                            <p style="font-size: 0.85rem; color: #718096; margin-bottom: 15px;">قم بتحميل القالب واملأ بيانات المعلمين</p>
                            <form action="{{ route('school-admin.download-template') }}" method="GET" style="display: inline;">
                                <input type="hidden" name="role" value="teachers">
                                <button type="submit" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.3s;"
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                    <i class="fas fa-download me-2"></i>تحميل القالب
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Parents Template -->
                    <div class="col-md-4">
                        <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 15px; padding: 25px; text-align: center; border: 2px solid #e2e8f0; transition: all 0.3s; height: 100%;"
                             onmouseover="this.style.borderColor='#a8edea'; this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 30px rgba(168, 237, 234, 0.2)'"
                             onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div style="font-size: 48px; margin-bottom: 15px;">👨‍👩‍👧</div>
                            <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 10px;">قالب أولياء الأمور</h6>
                            <p style="font-size: 0.85rem; color: #718096; margin-bottom: 15px;">قم بتحميل القالب واملأ بيانات أولياء الأمور</p>
                            <form action="{{ route('school-admin.download-template') }}" method="GET" style="display: inline;">
                                <input type="hidden" name="role" value="parents">
                                <button type="submit" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #1a202c; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.3s;"
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                    <i class="fas fa-download me-2"></i>تحميل القالب
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Section -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <div class="header-content">
                    <h5 class="card-title" style="color: white;">
                        <i class="fas fa-download me-2"></i>
                        تصدير البيانات من المنصة
                    </h5>
                    <p class="card-subtitle" style="color: rgba(255,255,255,0.9);">قم بتصدير بيانات المستخدمين والأنشطة بصيغة Excel</p>
                </div>
            </div>
            <div class="modern-card-body">
                <div class="row g-3">
                    <!-- Export Students -->
                    <div class="col-md-3">
                        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 15px; padding: 25px; text-align: center; border: 2px solid #bae6fd; transition: all 0.3s; height: 100%;"
                             onmouseover="this.style.borderColor='#48c6ef'; this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 30px rgba(72, 198, 239, 0.2)'"
                             onmouseout="this.style.borderColor='#bae6fd'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div style="font-size: 48px; margin-bottom: 15px;">👨‍🎓</div>
                            <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 10px;">تصدير الطلاب</h6>
                            <p style="font-size: 0.85rem; color: #718096; margin-bottom: 15px;">جميع بيانات الطلاب</p>
                            <form action="{{ route('school-admin.export-data') }}" method="GET" style="display: inline;">
                                <input type="hidden" name="type" value="students">
                                <button type="submit" style="background: linear-gradient(135deg, #48c6ef 0%, #6f86d6 100%); color: white; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.3s;"
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                    <i class="fas fa-file-excel me-2"></i>تصدير
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Export Teachers -->
                    <div class="col-md-3">
                        <div style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border-radius: 15px; padding: 25px; text-align: center; border: 2px solid #e9d5ff; transition: all 0.3s; height: 100%;"
                             onmouseover="this.style.borderColor='#667eea'; this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 30px rgba(102, 126, 234, 0.2)'"
                             onmouseout="this.style.borderColor='#e9d5ff'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div style="font-size: 48px; margin-bottom: 15px;">👨‍🏫</div>
                            <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 10px;">تصدير المعلمين</h6>
                            <p style="font-size: 0.85rem; color: #718096; margin-bottom: 15px;">جميع بيانات المعلمين</p>
                            <form action="{{ route('school-admin.export-data') }}" method="GET" style="display: inline;">
                                <input type="hidden" name="type" value="teachers">
                                <button type="submit" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.3s;"
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                    <i class="fas fa-file-excel me-2"></i>تصدير
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Export Parents -->
                    <div class="col-md-3">
                        <div style="background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); border-radius: 15px; padding: 25px; text-align: center; border: 2px solid #99f6e4; transition: all 0.3s; height: 100%;"
                             onmouseover="this.style.borderColor='#a8edea'; this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 30px rgba(168, 237, 234, 0.2)'"
                             onmouseout="this.style.borderColor='#99f6e4'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div style="font-size: 48px; margin-bottom: 15px;">👨‍👩‍👧</div>
                            <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 10px;">تصدير أولياء الأمور</h6>
                            <p style="font-size: 0.85rem; color: #718096; margin-bottom: 15px;">جميع بيانات أولياء الأمور</p>
                            <form action="{{ route('school-admin.export-data') }}" method="GET" style="display: inline;">
                                <input type="hidden" name="type" value="parents">
                                <button type="submit" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #1a202c; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.3s;"
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                    <i class="fas fa-file-excel me-2"></i>تصدير
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Export Activities -->
                    <div class="col-md-3">
                        <div style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-radius: 15px; padding: 25px; text-align: center; border: 2px solid #fed7aa; transition: all 0.3s; height: 100%;"
                             onmouseover="this.style.borderColor='#f59e0b'; this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 30px rgba(245, 158, 11, 0.2)'"
                             onmouseout="this.style.borderColor='#fed7aa'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div style="font-size: 48px; margin-bottom: 15px;">📚</div>
                            <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 10px;">تصدير الأنشطة</h6>
                            <p style="font-size: 0.85rem; color: #718096; margin-bottom: 15px;">جميع الأنشطة التعليمية</p>
                            <form action="{{ route('school-admin.export-data') }}" method="GET" style="display: inline;">
                                <input type="hidden" name="type" value="activities">
                                <button type="submit" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.3s;"
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                    <i class="fas fa-file-excel me-2"></i>تصدير
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Section -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="header-content">
                    <h5 class="card-title" style="color: white;">
                        <i class="fas fa-upload me-2"></i>
                        رفع الملف المملوء
                    </h5>
                    <p class="card-subtitle" style="color: rgba(255,255,255,0.9);">قم برفع الملف بعد ملء البيانات</p>
                </div>
            </div>
            <div class="modern-card-body">
                <form action="{{ route('school-admin.import-users') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">
                                <i class="fas fa-users me-2" style="color: #667eea;"></i>
                                اختر نوع المستخدمين
                            </label>
                            <select name="role" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 16px; transition: all 0.3s;"
                                    onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                                <option value="">اختر النوع</option>
                                <option value="students">الطلاب</option>
                                <option value="teachers">المعلمين</option>
                                <option value="parents">أولياء الأمور</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">
                                <i class="fas fa-file-excel me-2" style="color: #10b981;"></i>
                                اختر الملف (Excel)
                            </label>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                                   style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 16px; transition: all 0.3s;"
                                   onchange="this.style.borderColor='#10b981';"
                                   onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)'"
                                   onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            <small style="color: #718096; font-size: 0.85rem; margin-top: 5px; display: block;">
                                المسموح: .xlsx, .xls, .csv (حتى 5MB)
                            </small>
                        </div>
                        <div class="col-md-2">
                            <label style="display: block; color: transparent; margin-bottom: 8px;">&nbsp;</label>
                            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.3s;"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(16, 185, 129, 0.4)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                <i class="fas fa-upload me-2"></i>رفع
                            </button>
                        </div>
                    </div>
                </form>
                
                @if(session('success'))
                <div style="margin-top: 25px; padding: 20px; background: #dcfce7; border-radius: 12px; border-right: 4px solid #10b981; color: #166534; font-weight: 600; animation: slideInUp 0.5s ease-out;">
                    <i class="fas fa-check-circle me-2" style="font-size: 20px;"></i>
                    {{ session('success') }}
                </div>
                @endif
                
                @if(session('error'))
                <div style="margin-top: 25px; padding: 20px; background: #fee2e2; border-radius: 12px; border-right: 4px solid #ef4444; color: #991b1b; font-weight: 600; animation: slideInUp 0.5s ease-out;">
                    <i class="fas fa-exclamation-circle me-2" style="font-size: 20px;"></i>
                    {{ session('error') }}
                </div>
                @endif
                
                @if(session('import_errors'))
                <div style="margin-top: 25px; padding: 20px; background: #fef3c7; border-radius: 12px; border-right: 4px solid #f59e0b; color: #92400e; animation: slideInUp 0.5s ease-out;">
                    <h6 style="font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        تفاصيل الأخطاء:
                    </h6>
                    <ul style="margin: 0; padding-right: 25px; line-height: 1.8;">
                        @foreach(session('import_errors') as $error)
                        <li style="margin-bottom: 8px;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- كلمات المرور المؤقّتة العشوائيّة (بدل «123456» المشترك) — تُعرَض مرّةً ليوزّعها المدير --}}
                @if(session('import_credentials') && count(session('import_credentials')))
                <div style="margin-top: 25px; padding: 20px; background: #ecfdf5; border-radius: 12px; border-right: 4px solid #10b981; color: #065f46;">
                    <h6 style="font-weight: 700; margin-bottom: 6px;"><i class="fas fa-key"></i> كلمات المرور المؤقّتة (احفظها الآن — لن تظهر ثانيةً):</h6>
                    <p style="font-size: 13px; margin-bottom: 12px;">لكلّ مستخدم كلمة مرور عشوائيّة خاصّة؛ يُطلَب منه تغييرها أوّل دخول.</p>
                    <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:14px;">
                        <thead><tr style="background:#d1fae5;"><th style="padding:8px; text-align:right;">الاسم</th><th style="padding:8px; text-align:right;">البريد</th><th style="padding:8px; text-align:right;">كلمة المرور المؤقّتة</th></tr></thead>
                        <tbody>
                        @foreach(session('import_credentials') as $cred)
                            <tr style="border-bottom:1px solid #a7f3d0;">
                                <td style="padding:8px;">{{ $cred['name'] }}</td>
                                <td style="padding:8px;" dir="ltr">{{ $cred['email'] }}</td>
                                <td style="padding:8px;" dir="ltr"><code>{{ $cred['password'] }}</code></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Instructions Section -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header">
                <div class="header-content">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle me-2" style="color: #3b82f6;"></i>
                        ملاحظات وتعليمات هامة
                    </h5>
                    <p class="card-subtitle">اقرأ التعليمات بعناية قبل الاستخدام</p>
                </div>
            </div>
            <div class="modern-card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div style="background: #eff6ff; padding: 20px; border-radius: 12px; border-right: 4px solid #3b82f6;">
                            <h6 style="font-weight: 700; color: #1e40af; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-check-circle"></i>
                                متطلبات البيانات:
                            </h6>
                            <ul style="margin: 0; padding-right: 20px; color: #1e40af; line-height: 1.8;">
                                <li>الاسم <strong>مطلوب</strong> في جميع القوالب</li>
                                <li>البريد الإلكتروني <strong>مطلوب</strong> ويجب أن يكون فريداً</li>
                                <li>الهاتف <strong>اختياري</strong></li>
                                <li>تاريخ الميلاد للطلاب بصيغة: <code>YYYY-MM-DD</code></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background: #f0fdf4; padding: 20px; border-radius: 12px; border-right: 4px solid #10b981;">
                            <h6 style="font-weight: 700; color: #166534; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-key"></i>
                                كلمات المرور:
                            </h6>
                            <p style="margin: 0; color: #166534; line-height: 1.8;">
                                كلمة المرور الافتراضية للمستخدمين الجدد هي: <strong style="background: white; padding: 2px 8px; border-radius: 4px;">123456</strong>
                                <br>
                                سيتم إلزام المستخدمين بتغيير كلمة المرور عند أول تسجيل دخول
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background: #fefce8; padding: 20px; border-radius: 12px; border-right: 4px solid #eab308;">
                            <h6 style="font-weight: 700; color: #854d0e; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-users-class"></i>
                                للفصول:
                            </h6>
                            <p style="margin: 0; color: #854d0e; line-height: 1.8;">
                                في قالب الطلاب: اكتب اسم الفصل <strong>تماماً</strong> كما هو في النظام
                                <br>
                                إذا لم يكن الفصل موجوداً، سيتم تسجيل الطالب بدون فصل
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background: #fef2f2; padding: 20px; border-radius: 12px; border-right: 4px solid #ef4444;">
                            <h6 style="font-weight: 700; color: #991b1b; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-link"></i>
                                لأولياء الأمور:
                            </h6>
                            <p style="margin: 0; color: #991b1b; line-height: 1.8;">
                                اكتب أسماء الطلاب مفصولة بفاصلة: <code>الطالب الأول, الطالب الثاني</code>
                                <br>
                                يجب أن تكون أسماء الطلاب موجودة في النظام مسبقاً
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modern-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s;
    border: 1px solid rgba(0,0,0,0.05);
}

.modern-card:hover {
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.modern-card-header {
    padding: 25px 30px;
    border-bottom: 1px solid #f7fafc;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
    display: flex;
    align-items: center;
}

.card-subtitle {
    font-size: 0.85rem;
    color: #a0aec0;
    margin: 4px 0 0 0;
}

.modern-card-body {
    padding: 30px;
}

/* Wahy dark-mode coverage — رؤوس/نصوص وبطاقات القوالب ذات الخلفيات الفاتحة inline */
html[data-theme="dark"] .modern-card { background: var(--w-card); border-color: var(--w-border); box-shadow: var(--w-shadow); }
html[data-theme="dark"] .modern-card-header:not([style*="gradient"]) { border-bottom-color: var(--w-border); }
html[data-theme="dark"] .card-title { color: var(--w-text); }
html[data-theme="dark"] .card-subtitle { color: var(--w-text-muted); }
/* صناديق القوالب/التصدير: خلفيات فاتحة inline → داكنة، ونصوصها الداكنة → فاتحة */
html[data-theme="dark"] .modern-card-body [style*="linear-gradient(135deg, #f7fafc"],
html[data-theme="dark"] .modern-card-body [style*="linear-gradient(135deg, #f0f9ff"],
html[data-theme="dark"] .modern-card-body [style*="linear-gradient(135deg, #faf5ff"],
html[data-theme="dark"] .modern-card-body [style*="linear-gradient(135deg, #f0fdfa"],
html[data-theme="dark"] .modern-card-body [style*="linear-gradient(135deg, #fff7ed"] {
    background: rgba(255,255,255,0.05) !important;
    border-color: var(--w-border) !important;
}
html[data-theme="dark"] .modern-card-body [style*="color: #1a202c"] { color: var(--w-text) !important; }
html[data-theme="dark"] .modern-card-body [style*="color: #718096"] { color: var(--w-text-muted) !important; }
html[data-theme="dark"] .modern-card-body select,
html[data-theme="dark"] .modern-card-body input[type="file"] {
    background: rgba(255,255,255,0.05) !important;
    color: var(--w-text) !important;
    border-color: var(--w-border) !important;
}
</style>

@endsection
