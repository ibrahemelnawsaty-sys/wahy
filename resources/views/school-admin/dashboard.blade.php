@extends('layouts.school-admin')

@section('page-title', 'لوحة التحكم')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / لوحة التحكم
@endsection

@section('content')
    
    <!-- ترحيب مدير المدرسة -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card">
                <div class="welcome-content">
                    <div class="welcome-text">
                        <h1 class="welcome-title">مرحباً بك، {{ $user->name }} 👋</h1>
                        <p class="welcome-subtitle">
                            <i class="fas fa-school me-2"></i>
                            مدير مدرسة {{ $school->name }}
                        </p>
                        <p class="welcome-description">
                            لوحة تحكم شاملة لإدارة المدرسة والإشراف على جميع الأنشطة
                        </p>
                    </div>
                    <div class="welcome-illustration">
                        <div class="floating-icon">
                            <i class="fas fa-school"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- الإحصائيات الرئيسية - Grid محدث -->
    <div class="row g-4 mb-4">
        <!-- بطاقة المعلمون -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card stat-teachers">
                <div class="stat-card-body">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">{{ $stats['teachers'] }}</h3>
                        <p class="stat-label">المعلمون النشطون</p>
                        @if($stats['inactive_teachers'] > 0)
                            <span class="stat-badge">{{ $stats['inactive_teachers'] }} غير نشط</span>
                        @endif
                    </div>
                    <a href="{{ route('school-admin.teachers') }}" class="stat-link">
                        عرض الكل <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
                <div class="stat-decoration"></div>
            </div>
        </div>

        <!-- بطاقة الطلاب -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card stat-students">
                <div class="stat-card-body">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">{{ $stats['students'] }}</h3>
                        <p class="stat-label">الطلاب النشطون</p>
                        @if($stats['inactive_students'] > 0)
                            <span class="stat-badge">{{ $stats['inactive_students'] }} غير نشط</span>
                        @endif
                    </div>
                    <a href="{{ route('school-admin.students') }}" class="stat-link">
                        عرض الكل <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
                <div class="stat-decoration"></div>
            </div>
        </div>

        <!-- بطاقة أولياء الأمور -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card stat-parents">
                <div class="stat-card-body">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">{{ $stats['parents'] }}</h3>
                        <p class="stat-label">أولياء الأمور</p>
                    </div>
                    <a href="{{ route('school-admin.parents') }}" class="stat-link">
                        عرض الكل <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
                <div class="stat-decoration"></div>
            </div>
        </div>

        <!-- بطاقة الفصول -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card stat-classrooms">
                <div class="stat-card-body">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fas fa-door-open"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">{{ $stats['classrooms'] }}</h3>
                        <p class="stat-label">الفصول الدراسية</p>
                    </div>
                    <a href="{{ route('school-admin.classrooms') }}" class="stat-link">
                        عرض الكل <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
                <div class="stat-decoration"></div>
            </div>
        </div>
    </div>

    <!-- صف ثاني: إحصائيات الإنجاز -->
    <div class="row g-4 mb-4">
        <!-- النقاط الإجمالية -->
        <div class="col-xl-4 col-lg-4 col-md-6">
            <div class="achievement-card achievement-points">
                <div class="achievement-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="achievement-content">
                    <h3 class="achievement-number">{{ number_format($stats['total_points']) }}</h3>
                    <p class="achievement-label">النقاط الإجمالية</p>
                    <div class="achievement-progress">
                        <div class="progress-bar" style="width: 75%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الأنشطة المكتملة -->
        <div class="col-xl-4 col-lg-4 col-md-6">
            <div class="achievement-card achievement-activities">
                <div class="achievement-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="achievement-content">
                    <h3 class="achievement-number">{{ number_format($stats['completed_activities']) }}</h3>
                    <p class="achievement-label">الأنشطة المكتملة</p>
                    <div class="achievement-progress">
                        <div class="progress-bar" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- طلبات معلقة -->
        <div class="col-xl-4 col-lg-4 col-md-6">
            <div class="achievement-card achievement-requests">
                <div class="achievement-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="achievement-content">
                    <h3 class="achievement-number">{{ $stats['pending_requests'] }}</h3>
                    <p class="achievement-label">طلبات التسجيل المعلقة</p>
                    @if($stats['pending_requests'] > 0)
                        <a href="{{ route('school-admin.requests') }}" class="achievement-action">
                            مراجعة الآن <i class="fas fa-arrow-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>


    <!-- القسم الرئيسي: الرسم البياني + طلبات التسجيل -->
    <div class="row g-4 mb-4">
        <!-- الرسم البياني -->
        <div class="col-xl-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="header-content">
                        <h5 class="card-title">
                            <i class="fas fa-chart-line me-2"></i>
                            نشاط الطلاب - آخر 30 يوم
                        </h5>
                        <p class="card-subtitle">إحصائيات الأنشطة المكتملة</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn-icon" title="تحديث">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="modern-card-body">
                    <canvas id="activitiesChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <!-- طلبات التسجيل المعلقة -->
        <div class="col-xl-4">
            <div class="modern-card requests-card">
                <div class="modern-card-header">
                    <div class="header-content">
                        <h5 class="card-title">
                            <i class="fas fa-bell me-2"></i>
                            طلبات التسجيل
                        </h5>
                        @if($pendingRequests->count() > 0)
                            <span class="requests-badge">{{ $pendingRequests->count() }}</span>
                        @endif
                    </div>
                </div>
                <div class="modern-card-body p-0">
                    @if($pendingRequests->count() > 0)
                        <div class="requests-list">
                            @foreach($pendingRequests as $request)
                                <div class="request-item">
                                    <div class="request-avatar {{ $request->role === 'teacher' ? 'avatar-teacher' : ($request->role === 'student' ? 'avatar-student' : 'avatar-parent') }}">
                                        {{ mb_substr($request->name, 0, 1) }}
                                    </div>
                                    <div class="request-info">
                                        <h6 class="request-name">{{ $request->name }}</h6>
                                        <span class="request-role">
                                            {{ $request->role === 'teacher' ? 'معلم' : ($request->role === 'student' ? 'طالب' : 'ولي أمر') }}
                                        </span>
                                    </div>
                                    <a href="{{ route('school-admin.requests') }}" class="request-action">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <div class="requests-footer">
                            <a href="{{ route('school-admin.requests') }}" class="view-all-link">
                                عرض جميع الطلبات <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>لا توجد طلبات معلقة</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    <!-- الطلاب النشطين حالياً (الأون لاين) -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="header-content">
                        <h5 class="card-title" style="color: white;">
                            <i class="fas fa-circle me-2" style="color: #34d399; animation: pulse 2s infinite;"></i>
                            الطلاب النشطين حالياً (الأون لاين)
                        </h5>
                        <p class="card-subtitle" style="color: rgba(255,255,255,0.9);">الطلاب المتصلين حالياً في المنصة</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 8px 16px; border-radius: 12px; color: white; font-weight: 700; font-size: 18px;">
                        {{ $onlineStudents->count() }}
                    </div>
                </div>
                <div class="modern-card-body p-0">
                    @if($onlineStudents->count() > 0)
                        <div class="online-students-list">
                            @foreach($onlineStudents as $student)
                                <div class="online-student-item">
                                    <div style="position: relative;">
                                        <div class="student-avatar" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                            {{ mb_substr($student->name, 0, 1) }}
                                        </div>
                                        <span class="online-indicator"></span>
                                    </div>
                                    <div class="student-info" style="flex: 1;">
                                        <h6 class="student-name">{{ $student->name }}</h6>
                                        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; margin-top: 5px;">
                                            <span style="font-size: 0.85rem; color: #10b981; font-weight: 600;">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $student->online_since }}
                                            </span>
                                            <span style="font-size: 0.85rem; color: #718096; font-weight: 600;">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                وقت اليوم: {{ $student->session_time }}
                                            </span>
                                            <span style="font-size: 0.85rem; color: #667eea; font-weight: 600;">
                                                <i class="fas fa-star me-1"></i>
                                                {{ number_format($student->total_points ?? 0) }} نقطة
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <p>لا يوجد طلاب متصلين حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- القسم السفلي: أفضل الطلاب + أحدث الفصول -->
    <div class="row g-4 mb-4">
        <!-- أفضل 5 طلاب -->
        <div class="col-xl-6">
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="header-content">
                        <h5 class="card-title">
                            <i class="fas fa-trophy me-2" style="color: #ffd700;"></i>
                            أفضل 5 طلاب
                        </h5>
                        <p class="card-subtitle">الطلاب الأكثر تميزاً في المدرسة</p>
                    </div>
                </div>
                <div class="modern-card-body p-0">
                    @if($topStudents->count() > 0)
                        <div class="leaderboard-list">
                            @foreach($topStudents as $index => $student)
                                <div class="leaderboard-item">
                                    <div class="rank-badge rank-{{ $index + 1 }}">
                                        @if($index === 0)
                                            <i class="fas fa-crown"></i>
                                        @elseif($index === 1)
                                            <i class="fas fa-medal"></i>
                                        @elseif($index === 2)
                                            <i class="fas fa-medal"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    <div class="student-avatar">
                                        {{ mb_substr($student->name, 0, 1) }}
                                    </div>
                                    <div class="student-info">
                                        <h6 class="student-name">{{ $student->name }}</h6>
                                        <span class="student-points">
                                            <i class="fas fa-star"></i>
                                            {{ number_format($student->total_points ?? 0) }} نقطة
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-user-graduate"></i>
                            <p>لا يوجد طلاب بعد</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- أحدث الفصول -->
        <div class="col-xl-6">
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="header-content">
                        <h5 class="card-title">
                            <i class="fas fa-door-open me-2"></i>
                            أحدث الفصول الدراسية
                        </h5>
                        <p class="card-subtitle">آخر الفصول المضافة</p>
                    </div>
                </div>
                <div class="modern-card-body p-0">
                    @if($recentClassrooms->count() > 0)
                        <div class="classrooms-list">
                            @foreach($recentClassrooms as $classroom)
                                <div class="classroom-item">
                                    <div class="classroom-icon">
                                        <i class="fas fa-chalkboard"></i>
                                    </div>
                                    <div class="classroom-info">
                                        <h6 class="classroom-name">{{ $classroom->name }}</h6>
                                        <span class="classroom-teacher">
                                            <i class="fas fa-user-tie"></i>
                                            {{ $classroom->teacher->name ?? 'لا يوجد معلم' }}
                                        </span>
                                    </div>
                                    <div class="classroom-stats">
                                        <span class="students-count">
                                            <i class="fas fa-users"></i>
                                            {{ $classroom->students_count }}
                                        </span>
                                        <a href="{{ route('school-admin.classrooms') }}" class="classroom-link">
                                            <i class="fas fa-arrow-left"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-door-closed"></i>
                            <p>لا توجد فصول بعد</p>
                        </div>
                    @endif
                </div>
                <div class="modern-card-footer">
                    <a href="{{ route('school-admin.classrooms') }}" class="view-all-link">
                        عرض جميع الفصول <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>


    <!-- Excel Import/Export -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
                    <div class="header-content">
                        <h5 class="card-title" style="color: white;">
                            <i class="fas fa-file-excel me-2"></i>
                            تسجيل المستخدمين من Excel
                        </h5>
                        <p class="card-subtitle" style="color: rgba(255,255,255,0.9);">قم بتحميل القالب، املأه، ثم ارفعه لتسجيل المستخدمين دفعة واحدة</p>
                    </div>
                </div>
                <div class="modern-card-body">
                    <div class="row g-3">
                        <!-- تحميل القوالب -->
                        <div class="col-md-4">
                            <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 15px; padding: 25px; text-align: center; border: 2px solid #e2e8f0;">
                                <div style="font-size: 48px; margin-bottom: 15px;">📥</div>
                                <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 10px;">تحميل قالب الطلاب</h6>
                                <p style="font-size: 0.85rem; color: #718096; margin-bottom: 15px;">قم بتحميل القالب واملأ بيانات الطلاب</p>
                                <form action="{{ route('school-admin.download-template') }}" method="GET" style="display: inline;">
                                    <input type="hidden" name="role" value="students">
                                    <button type="submit" style="background: linear-gradient(135deg, #48c6ef 0%, #6f86d6 100%); color: white; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer;">
                                        <i class="fas fa-download me-2"></i>تحميل القالب
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 15px; padding: 25px; text-align: center; border: 2px solid #e2e8f0;">
                                <div style="font-size: 48px; margin-bottom: 15px;">👨‍🏫</div>
                                <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 10px;">تحميل قالب المعلمين</h6>
                                <p style="font-size: 0.85rem; color: #718096; margin-bottom: 15px;">قم بتحميل القالب واملأ بيانات المعلمين</p>
                                <form action="{{ route('school-admin.download-template') }}" method="GET" style="display: inline;">
                                    <input type="hidden" name="role" value="teachers">
                                    <button type="submit" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer;">
                                        <i class="fas fa-download me-2"></i>تحميل القالب
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 15px; padding: 25px; text-align: center; border: 2px solid #e2e8f0;">
                                <div style="font-size: 48px; margin-bottom: 15px;">👨‍👩‍👧</div>
                                <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 10px;">تحميل قالب أولياء الأمور</h6>
                                <p style="font-size: 0.85rem; color: #718096; margin-bottom: 15px;">قم بتحميل القالب واملأ بيانات أولياء الأمور</p>
                                <form action="{{ route('school-admin.download-template') }}" method="GET" style="display: inline;">
                                    <input type="hidden" name="role" value="parents">
                                    <button type="submit" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #1a202c; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer;">
                                        <i class="fas fa-download me-2"></i>تحميل القالب
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- رفع الملفات -->
                    <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e2e8f0;">
                        <h6 style="font-weight: 700; color: #1a202c; margin-bottom: 20px;">
                            <i class="fas fa-upload me-2" style="color: #8b5cf6;"></i>
                            رفع الملف المملوء
                        </h6>
                        <form action="{{ route('school-admin.import-users') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">اختر نوع المستخدمين</label>
                                    <select name="role" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 16px;">
                                        <option value="">اختر النوع</option>
                                        <option value="students">الطلاب</option>
                                        <option value="teachers">المعلمين</option>
                                        <option value="parents">أولياء الأمور</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">اختر الملف</label>
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 16px;">
                                </div>
                                <div class="col-md-2">
                                    <label style="display: block; color: transparent; margin-bottom: 8px;">&nbsp;</label>
                                    <button type="submit" style="width: 100%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 16px; cursor: pointer;">
                                        <i class="fas fa-upload me-2"></i>رفع
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        @if(session('success'))
                        <div style="margin-top: 20px; padding: 15px; background: #dcfce7; border-radius: 10px; border-right: 4px solid #10b981; color: #166534; font-weight: 600;">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                        @endif
                        
                        @if(session('error'))
                        <div style="margin-top: 20px; padding: 15px; background: #fee2e2; border-radius: 10px; border-right: 4px solid #ef4444; color: #991b1b; font-weight: 600;">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                        </div>
                        @endif
                        
                        @if(session('errors'))
                        <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 10px; border-right: 4px solid #f59e0b; color: #92400e;">
                            <h6 style="font-weight: 700; margin-bottom: 10px;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                تفاصيل الأخطاء:
                            </h6>
                            <ul style="margin: 0; padding-right: 20px;">
                                @foreach(session('errors') as $error)
                                <li style="margin-bottom: 5px;">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        
                        <div style="margin-top: 20px; padding: 15px; background: #eff6ff; border-radius: 10px; border-right: 4px solid #3b82f6; color: #1e40af; font-size: 0.9rem;">
                            <strong><i class="fas fa-info-circle me-2"></i>ملاحظات هامة:</strong>
                            <ul style="margin: 10px 0 0 0; padding-right: 20px;">
                                <li>الاسم والبريد الإلكتروني مطلوبان</li>
                                <li>البريد الإلكتروني يجب أن يكون فريداً</li>
                                <li>كلمة المرور الافتراضية للمستخدمين الجدد: <strong>123456</strong></li>
                                <li>للفصل في قالب الطلاب: اكتب اسم الفصل كما هو في النظام</li>
                                <li>لأولياء الأمور: يمكنك كتابة عدة أسماء طلاب مفصولة بفاصلة</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- إجراءات سريعة -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="quick-actions-card">
                <div class="quick-actions-header">
                    <h5 class="actions-title">
                        <i class="fas fa-bolt"></i>
                        إجراءات سريعة
                    </h5>
                    <p class="actions-subtitle">الوصول السريع للمهام الشائعة</p>
                </div>
                <div class="quick-actions-grid">
                    <a href="{{ route('school-admin.teachers.create') }}" class="action-btn action-teachers">
                        <div class="action-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <span class="action-label">إضافة معلم</span>
                    </a>
                    <a href="{{ route('school-admin.students.create') }}" class="action-btn action-students">
                        <div class="action-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <span class="action-label">إضافة طالب</span>
                    </a>
                    <a href="{{ route('school-admin.parents.create') }}" class="action-btn action-parents">
                        <div class="action-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="action-label">إضافة ولي أمر</span>
                    </a>
                    <a href="{{ route('school-admin.classrooms.create') }}" class="action-btn action-classrooms">
                        <div class="action-icon">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <span class="action-label">إنشاء فصل</span>
                    </a>
                    <a href="{{ route('school-admin.requests') }}" class="action-btn action-requests">
                        <div class="action-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <span class="action-label">الطلبات</span>
                        @if($stats['pending_requests'] > 0)
                            <span class="action-badge">{{ $stats['pending_requests'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('school-admin.dashboard') }}" class="action-btn action-manage">
                        <div class="action-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <span class="action-label">إدارة المدرسة</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>


<style>
/* ==================== Welcome Card ==================== */
.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
    color: white;
    overflow: hidden;
    position: relative;
}

.welcome-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 2;
}

.welcome-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 10px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.welcome-subtitle {
    font-size: 1.1rem;
    opacity: 0.95;
    margin-bottom: 8px;
}

.welcome-description {
    font-size: 0.95rem;
    opacity: 0.85;
    margin: 0;
}

.floating-icon {
    width: 120px;
    height: 120px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    backdrop-filter: blur(10px);
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

/* ==================== Stat Cards ==================== */
.stat-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid rgba(0,0,0,0.05);
}

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

.stat-card-body {
    position: relative;
    z-index: 2;
}

.stat-icon-wrapper {
    margin-bottom: 20px;
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.stat-teachers .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-students .stat-icon { background: linear-gradient(135deg, #48c6ef 0%, #6f86d6 100%); }
.stat-parents .stat-icon { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
.stat-classrooms .stat-icon { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); }

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    color: #2d3748;
    margin: 10px 0;
    line-height: 1;
}

.stat-label {
    font-size: 0.95rem;
    color: #718096;
    font-weight: 600;
    margin-bottom: 8px;
}

.stat-badge {
    display: inline-block;
    padding: 4px 12px;
    background: #fee;
    color: #c53030;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.stat-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #667eea;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    margin-top: 12px;
    transition: gap 0.3s;
}

.stat-link:hover {
    gap: 10px;
    color: #5568d3;
}

.stat-decoration {
    position: absolute;
    bottom: -30px;
    right: -30px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    z-index: 1;
}

/* ==================== Achievement Cards ==================== */
.achievement-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.3s;
    border: 2px solid transparent;
}

.achievement-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.achievement-points { border-color: rgba(102, 126, 234, 0.2); }
.achievement-points:hover { border-color: #667eea; }

.achievement-activities { border-color: rgba(72, 187, 120, 0.2); }
.achievement-activities:hover { border-color: #48bb78; }

.achievement-requests { border-color: rgba(245, 101, 101, 0.2); }
.achievement-requests:hover { border-color: #f56565; }

.achievement-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
    flex-shrink: 0;
}

.achievement-points .achievement-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.achievement-activities .achievement-icon { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
.achievement-requests .achievement-icon { background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); }

.achievement-content {
    flex: 1;
}

.achievement-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: #2d3748;
    margin: 0;
    line-height: 1;
}

.achievement-label {
    color: #718096;
    font-size: 0.95rem;
    font-weight: 600;
    margin: 8px 0;
}

.achievement-progress {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 12px;
}

.achievement-progress .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 1s ease;
}

.achievement-action {
    color: #e53e3e;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
}

.achievement-action:hover {
    gap: 10px;
}

/* ==================== Modern Cards ==================== */
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

.btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #f7fafc;
    border: none;
    color: #718096;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-icon:hover {
    background: #e2e8f0;
    color: #2d3748;
    transform: rotate(180deg);
}

.modern-card-body {
    padding: 30px;
}

.modern-card-footer {
    padding: 20px 30px;
    border-top: 1px solid #f7fafc;
    text-align: center;
}

/* ==================== Requests Card ==================== */
.requests-card .modern-card-header {
    background: linear-gradient(135deg, #fef5e7 0%, #ffe4e1 100%);
}

.requests-badge {
    background: #e53e3e;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    margin-right: 10px;
}

.requests-list {
    max-height: 400px;
    overflow-y: auto;
}

.request-item {
    padding: 20px 30px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 1px solid #f7fafc;
    transition: background 0.3s;
}

.request-item:hover {
    background: #f7fafc;
}

.request-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    color: white;
    flex-shrink: 0;
}

.avatar-teacher { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.avatar-student { background: linear-gradient(135deg, #48c6ef 0%, #6f86d6 100%); }
.avatar-parent { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }

.request-info {
    flex: 1;
}

.request-name {
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0 0 4px 0;
}

.request-role {
    font-size: 0.85rem;
    color: #718096;
}

.request-action {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s;
}

.request-action:hover {
    background: #5568d3;
    transform: scale(1.1);
}

.requests-footer {
    padding: 20px 30px;
    border-top: 1px solid #f7fafc;
    text-align: center;
}

.view-all-link {
    color: #667eea;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: gap 0.3s;
}

.view-all-link:hover {
    gap: 12px;
}

.empty-state {
    padding: 60px 20px;
    text-align: center;
    color: #a0aec0;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 16px;
    display: block;
    opacity: 0.5;
}

/* ==================== Leaderboard ==================== */
.leaderboard-list {
    padding: 10px 0;
}

.leaderboard-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 30px;
    border-bottom: 1px solid #f7fafc;
    transition: background 0.3s;
}

.leaderboard-item:hover {
    background: #f7fafc;
}

.rank-badge {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.rank-1 { background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); color: #fff; box-shadow: 0 5px 20px rgba(255, 215, 0, 0.4); }
.rank-2 { background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%); color: #666; }
.rank-3 { background: linear-gradient(135deg, #cd7f32 0%, #d89a5f 100%); color: #fff; }
.rank-badge:not(.rank-1):not(.rank-2):not(.rank-3) { background: #edf2f7; color: #4a5568; }

.student-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #48c6ef 0%, #6f86d6 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.student-info {
    flex: 1;
}

.student-name {
    font-size: 1.05rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0 0 4px 0;
}

.student-points {
    font-size: 0.9rem;
    color: #667eea;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ==================== Classrooms List ==================== */
.classrooms-list {
    padding: 10px 0;
}

.classroom-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 30px;
    border-bottom: 1px solid #f7fafc;
    transition: background 0.3s;
}

.classroom-item:hover {
    background: #f7fafc;
}

.classroom-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.classroom-info {
    flex: 1;
}

.classroom-name {
    font-size: 1.05rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0 0 6px 0;
}

.classroom-teacher {
    font-size: 0.85rem;
    color: #718096;
    display: flex;
    align-items: center;
    gap: 6px;
}

.classroom-stats {
    display: flex;
    align-items: center;
    gap: 15px;
}

.students-count {
    background: #edf2f7;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    color: #4a5568;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.classroom-link {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s;
}

.classroom-link:hover {
    background: #5568d3;
    transform: scale(1.1);
}

/* ==================== Quick Actions ==================== */
.quick-actions-card {
    background: white;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

.quick-actions-header {
    text-align: center;
    margin-bottom: 30px;
}

.actions-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #2d3748;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.actions-subtitle {
    color: #718096;
    font-size: 0.95rem;
    margin: 0;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
}

.action-btn {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 30px 20px;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    opacity: 0;
    transition: opacity 0.3s;
}

.action-btn:hover::before {
    opacity: 0.05;
}

.action-btn:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.action-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 16px;
    transition: all 0.3s;
    position: relative;
}

.action-btn:hover .action-icon {
    transform: scale(1.1) rotate(5deg);
}

.action-teachers .action-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.action-students .action-icon { background: linear-gradient(135deg, #48c6ef 0%, #6f86d6 100%); }
.action-parents .action-icon { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
.action-classrooms .action-icon { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); }
.action-requests .action-icon { background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); }
.action-manage .action-icon { background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); }

.action-label {
    color: #2d3748;
    font-weight: 600;
    font-size: 1rem;
    position: relative;
}

.action-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #e53e3e;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
}

/* ==================== Online Students ==================== */
.online-students-list {
    padding: 10px 0;
}

.online-student-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 30px;
    border-bottom: 1px solid #f7fafc;
    transition: background 0.3s;
}

.online-student-item:hover {
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
}

.online-indicator {
    position: absolute;
    top: 0;
    left: 0;
    width: 14px;
    height: 14px;
    background: #10b981;
    border: 2px solid white;
    border-radius: 50%;
    animation: pulse-online 2s infinite;
}

@keyframes pulse-online {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
    }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* ==================== Responsive ==================== */
@media (max-width: 768px) {
    .welcome-title { font-size: 1.8rem; }
    .welcome-content { flex-direction: column; text-align: center; }
    .floating-icon { margin-top: 20px; }
    .stat-number { font-size: 2.2rem; }
    .achievement-number { font-size: 2rem; }
    .quick-actions-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // رسم بياني للأنشطة المكتملة
    const ctx = document.getElementById('activitiesChart');
    if (ctx) {
        const activitiesData = @json($dailyActivities);
        
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(102, 126, 234, 0.3)');
        gradient.addColorStop(1, 'rgba(102, 126, 234, 0.0)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: activitiesData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('ar-SA', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'عدد الأنشطة',
                    data: activitiesData.map(item => item.count),
                    borderColor: '#667eea',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#5568d3',
                    pointHoverBorderWidth: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 16,
                        titleFont: {
                            size: 15,
                            family: 'Cairo, sans-serif',
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 14,
                            family: 'Cairo, sans-serif'
                        },
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#667eea',
                        borderWidth: 2,
                        cornerRadius: 12,
                        displayColors: false,
                        rtl: true,
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return context.parsed.y + ' نشاط مكتمل';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                family: 'Cairo, sans-serif',
                                size: 13
                            },
                            color: '#718096',
                            padding: 10,
                            callback: function(value) {
                                return value;
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false,
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: 'Cairo, sans-serif',
                                size: 12
                            },
                            color: '#718096',
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 10,
                            padding: 10
                        },
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
    
    // تأثيرات الأرقام المتحركة
    const animateNumbers = () => {
        const numbers = document.querySelectorAll('.stat-number, .achievement-number');
        numbers.forEach(num => {
            const target = parseInt(num.textContent.replace(/,/g, ''));
            if (isNaN(target)) return;
            
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    num.textContent = target.toLocaleString('ar-SA');
                    clearInterval(timer);
                } else {
                    num.textContent = Math.floor(current).toLocaleString('ar-SA');
                }
            }, 30);
        });
    };
    
    // تشغيل تأثير الأرقام عند التحميل
    setTimeout(animateNumbers, 300);
    
    // تأثير Progress Bars
    const animateProgressBars = () => {
        const bars = document.querySelectorAll('.progress-bar');
        bars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 500);
        });
    };
    
    setTimeout(animateProgressBars, 500);
});
</script>
@endpush

@endsection
