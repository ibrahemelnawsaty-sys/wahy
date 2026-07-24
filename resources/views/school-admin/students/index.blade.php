@extends('layouts.school-admin')

@section('page-title', 'إدارة الطلاب')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / إدارة الطلاب
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-2">
                <i class="fas fa-user-graduate text-success me-2"></i>
                إدارة الطلاب
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                عرض شامل لجميع الطلاب مع الفصول وأولياء الأمور
            </p>
        </div>
        <a href="{{ route('school-admin.students.create') }}" class="btn btn-success btn-lg shadow-sm">
            <i class="fas fa-plus me-2"></i>
            إضافة طالب جديد
        </a>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #198754;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="fas fa-user-graduate fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $students->total() }}</h3>
                            <small class="text-muted">إجمالي الطلاب</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #0d6efd;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-star fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ number_format($students->sum('total_points')) }}</h3>
                            <small class="text-muted">مجموع النقاط</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #0dcaf0;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-tasks fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $students->sum('activity_submissions_count') }}</h3>
                            <small class="text-muted">الأنشطة المكتملة</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #20c997;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background: rgba(32, 201, 151, 0.1);">
                                <i class="fas fa-check-circle fa-2x" style="color: #20c997;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $students->where('status', 'active')->count() }}</h3>
                            <small class="text-muted">طالب نشط</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- قائمة الطلاب المفصلة -->
    @forelse($students as $student)
        <div class="card border-0 shadow-sm mb-3 student-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- معلومات الطالب -->
                    <div class="col-lg-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-lg bg-gradient-success text-white me-3">
                                {{ mb_substr($student->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-bold">{{ $student->name }}</h5>
                                <div class="text-muted small mb-1">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $student->email }}
                                </div>
                                @if($student->classrooms->isNotEmpty())
                                    <div class="text-muted small">
                                        <i class="fas fa-door-open me-1"></i>
                                        {{ $student->classrooms->count() }} فصل
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- الإحصائيات -->
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="p-2 text-center rounded stats-box-blue">
                                    <div class="text-white">
                                        <h5 class="mb-0 fw-bold">{{ number_format($student->total_points ?? 0) }}</h5>
                                        <small style="font-size: 11px;">نقطة</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 text-center rounded stats-box-teal">
                                    <div class="text-white">
                                        <h5 class="mb-0 fw-bold">{{ $student->activity_submissions_count }}</h5>
                                        <small style="font-size: 11px;">نشاط</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 text-center rounded stats-box-orange">
                                    <div class="text-white">
                                        <h5 class="mb-0 fw-bold">{{ $student->parents->count() }}</h5>
                                        <small style="font-size: 11px;">ولي أمر</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-center">
                            @if($student->status === 'active')
                                <span class="badge bg-success px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i>نشط
                                </span>
                            @else
                                <span class="badge bg-danger px-3 py-2">
                                    <i class="fas fa-times-circle me-1"></i>غير نشط
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- الإجراءات -->
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('school-admin.students.edit', $student->id) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>تعديل البيانات
                            </a>
                            <button type="button" class="btn btn-outline-info btn-sm" 
                                    onclick="toggleDetails({{ $student->id }})">
                                <i class="fas fa-info-circle me-1"></i>التفاصيل الكاملة
                                <i class="fas fa-chevron-down ms-1" id="icon-{{ $student->id }}"></i>
                            </button>
                            <form id="delete-student-{{ $student->id }}" action="{{ route('school-admin.students.delete', $student->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="confirmDeleteStudent({{ $student->id }}, @js($student->name))">
                                    <i class="fas fa-trash me-1"></i>حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- التفاصيل الكاملة -->
                <div class="student-details mt-3 pt-3 border-top" id="details-{{ $student->id }}" style="display: none;">
                    <div class="row g-3">
                        <!-- معلومات إضافية -->
                        <div class="col-12">
                            <div class="card border-0 mb-3" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); border-radius: 12px;">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-3 text-dark">
                                        <i class="fas fa-id-card me-2"></i>
                                        المعلومات الشخصية
                                    </h6>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <small class="text-dark d-block">
                                                <i class="fas fa-envelope me-1"></i>
                                                <strong>البريد:</strong> {{ $student->email }}
                                            </small>
                                        </div>
                                        @if($student->birth_date)
                                        <div class="col-md-4">
                                            <small class="text-dark d-block">
                                                <i class="fas fa-birthday-cake me-1"></i>
                                                <strong>العمر:</strong> {{ $student->age }} سنة
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-dark d-block">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <strong>تاريخ الميلاد:</strong> {{ $student->birth_date->format('Y-m-d') }}
                                            </small>
                                        </div>
                                        @else
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                <strong>العمر:</strong> غير محدد
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- الفصول الدراسية -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-door-open text-primary me-2"></i>
                                الفصول الدراسية
                            </h6>
                            @if($student->classrooms->isNotEmpty())
                                <div class="d-flex flex-column gap-2">
                                    @foreach($student->classrooms as $classroom)
                                    <div class="card border-0 classroom-info-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1 fw-bold text-primary">{{ $classroom->name }}</h6>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-chalkboard-teacher me-1"></i>
                                                        المعلم: {{ $classroom->teacher->name ?? 'غير محدد' }}
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-layer-group me-1"></i>
                                                        المستوى: {{ $classroom->grade_level ?? 'غير محدد' }}
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        العام: {{ $classroom->academic_year ?? 'غير محدد' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    لم يتم تسجيل الطالب في أي فصل بعد
                                </div>
                            @endif
                        </div>

                        <!-- أولياء الأمور -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-users text-success me-2"></i>
                                أولياء الأمور
                            </h6>
                            @if($student->parents->isNotEmpty())
                                <div class="d-flex flex-column gap-2">
                                    @foreach($student->parents as $parent)
                                    <div class="card border-0 parent-info-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start">
                                                <div class="avatar-sm bg-success bg-opacity-10 text-success me-2">
                                                    {{ mb_substr($parent->name, 0, 1) }}
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold">{{ $parent->name }}</h6>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-envelope me-1"></i>
                                                        {{ $parent->email }}
                                                    </small>
                                                    @if($parent->phone)
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-phone me-1"></i>
                                                        {{ $parent->phone }}
                                                    </small>
                                                    @endif
                                                    <span class="badge bg-info mt-1" style="font-size: 10px;">
                                                        {{ $parent->pivot->relationship ?? 'ولي أمر' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    لم يتم ربط ولي أمر بهذا الطالب
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-user-graduate fa-5x text-muted mb-4"></i>
                <h4 class="text-muted mb-2">لا يوجد طلاب بعد</h4>
                <p class="text-muted mb-4">ابدأ بإضافة الطلاب لإدارة المدرسة</p>
                <a href="{{ route('school-admin.students.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>إضافة أول طالب
                </a>
            </div>
        </div>
    @endforelse

    <!-- الترقيم -->
    @if($students->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $students->links() }}
    </div>
    @endif
@endsection

@push('styles')
<style>
    .bg-gradient-success {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(25, 135, 84, 0.3);
    }
    
    .student-card {
        transition: all 0.3s ease;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .student-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }
    
    .avatar-lg {
        transition: all 0.3s ease;
    }
    
    .student-card:hover .avatar-lg {
        transform: scale(1.1);
    }
    
    .stats-box-blue {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .stats-box-teal {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .stats-box-orange {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    
    .classroom-info-card {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .classroom-info-card:hover {
        transform: translateX(-5px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
    }
    
    .parent-info-card {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .parent-info-card:hover {
        transform: translateX(-5px);
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
    }
    
    .avatar-sm {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
    }
    
    .student-details {
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@push('scripts')
<script>
function toggleDetails(studentId) {
    const element = document.getElementById('details-' + studentId);
    const icon = document.getElementById('icon-' + studentId);
    
    if (element.style.display === 'none') {
        element.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        element.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

function confirmDeleteStudent(studentId, studentName) {
    glassNotify.confirm(
        'حذف الطالب',
        `هل أنت متأكد من حذف الطالب "${studentName}"؟`,
        function() {
            document.getElementById('delete-student-' + studentId).submit();
        },
        {
            confirmText: 'حذف',
            cancelText: 'إلغاء',
            type: 'error'
        }
    );
}
</script>
@endpush
