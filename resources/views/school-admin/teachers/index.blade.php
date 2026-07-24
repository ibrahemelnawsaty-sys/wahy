@extends('layouts.school-admin')

@section('page-title', 'إدارة المعلمين')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / إدارة المعلمين
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-2">
                <i class="fas fa-chalkboard-teacher text-primary me-2"></i>
                إدارة المعلمين
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                عرض تفصيلي لجميع المعلمين والفصول المسؤولين عنها
            </p>
        </div>
        <a href="{{ route('school-admin.teachers.create') }}" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-plus me-2"></i>
            إضافة معلم جديد
        </a>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #0d6efd;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-chalkboard-teacher fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $teachers->total() }}</h3>
                            <small class="text-muted">إجمالي المعلمين</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #0dcaf0;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-door-open fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $teachers->sum('teaching_classrooms_count') }}</h3>
                            <small class="text-muted">إجمالي الفصول</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #6f42c1;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background: rgba(111, 66, 193, 0.1);">
                                <i class="fas fa-user-graduate fa-2x" style="color: #6f42c1;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">
                                {{ $teachers->flatMap(fn($t) => $t->teachingClassrooms)->sum('students_count') }}
                            </h3>
                            <small class="text-muted">إجمالي الطلاب</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- قائمة المعلمين المفصلة -->
    @forelse($teachers as $teacher)
        <div class="card border-0 shadow-sm mb-3 teacher-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- معلومات المعلم -->
                    <div class="col-lg-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-lg bg-gradient-primary text-white me-3">
                                {{ mb_substr($teacher->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-bold">{{ $teacher->name }}</h5>
                                <div class="text-muted small mb-1">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $teacher->email }}
                                </div>
                                @if($teacher->phone)
                                <div class="text-muted small">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $teacher->phone }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- الإحصائيات -->
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-2 rounded stats-box-purple">
                                    <div class="text-white">
                                        <h4 class="mb-0 fw-bold">{{ $teacher->teaching_classrooms_count }}</h4>
                                        <small>فصل دراسي</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded stats-box-pink">
                                    <div class="text-white">
                                        @php
                                            $totalStudents = 0;
                                            foreach($teacher->teachingClassrooms as $classroom) {
                                                $totalStudents += $classroom->students_count ?? 0;
                                            }
                                        @endphp
                                        <h4 class="mb-0 fw-bold">
                                            {{ $totalStudents }}
                                        </h4>
                                        <small>طالب</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-center">
                            @if($teacher->status === 'active')
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
                            <a href="{{ route('school-admin.teachers.edit', $teacher->id) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>تعديل البيانات
                            </a>
                            <button type="button" class="btn btn-outline-info btn-sm" 
                                    onclick="toggleClassrooms({{ $teacher->id }})">
                                <i class="fas fa-eye me-1"></i>عرض الفصول
                                <i class="fas fa-chevron-down ms-1" id="icon-{{ $teacher->id }}"></i>
                            </button>
                            <form id="delete-teacher-{{ $teacher->id }}" action="{{ route('school-admin.teachers.delete', $teacher->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="confirmDeleteTeacher({{ $teacher->id }}, @js($teacher->name))">
                                    <i class="fas fa-trash me-1"></i>حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- الفصول التفصيلية -->
                @if($teacher->teachingClassrooms->isNotEmpty())
                <div class="classrooms-detail mt-3 pt-3 border-top" id="classrooms-{{ $teacher->id }}" style="display: none;">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-door-open text-info me-2"></i>
                        الفصول الدراسية ({{ $teacher->teaching_classrooms_count }})
                    </h6>
                    <div class="row g-3">
                        @foreach($teacher->teachingClassrooms as $classroom)
                        <div class="col-md-6">
                            <div class="card border-0 classroom-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1 fw-bold text-primary">
                                                <i class="fas fa-bookmark me-1"></i>
                                                {{ $classroom->name }}
                                            </h6>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-layer-group me-1"></i>
                                                المستوى: {{ $classroom->grade_level ?? 'غير محدد' }}
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-calendar me-1"></i>
                                                العام: {{ $classroom->academic_year ?? 'غير محدد' }}
                                            </small>
                                        </div>
                                        <span class="badge bg-info fs-6">
                                            {{ $classroom->students_count }} طالب
                                        </span>
                                    </div>
                                    @if($classroom->status === 'active')
                                        <span class="badge bg-success-soft text-success mt-2">
                                            <i class="fas fa-circle" style="font-size: 8px;"></i> نشط
                                        </span>
                                    @else
                                        <span class="badge bg-danger-soft text-danger mt-2">
                                            <i class="fas fa-circle" style="font-size: 8px;"></i> غير نشط
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="classrooms-detail mt-3 pt-3 border-top" id="classrooms-{{ $teacher->id }}" style="display: none;">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        لا يوجد فصول دراسية مسندة لهذا المعلم بعد
                    </div>
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-chalkboard-teacher fa-5x text-muted mb-4"></i>
                <h4 class="text-muted mb-2">لا يوجد معلمون بعد</h4>
                <p class="text-muted mb-4">ابدأ بإضافة المعلمين لإدارة المدرسة</p>
                <a href="{{ route('school-admin.teachers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>إضافة أول معلم
                </a>
            </div>
        </div>
    @endforelse

    <!-- الترقيم -->
    @if($teachers->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $teachers->links() }}
    </div>
    @endif
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }
    
    .teacher-card {
        transition: all 0.3s ease;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .teacher-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }
    
    .avatar-lg {
        transition: all 0.3s ease;
    }
    
    .teacher-card:hover .avatar-lg {
        transform: scale(1.1);
    }
    
    .stats-box-purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .stats-box-pink {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .classroom-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .classroom-card:hover {
        transform: translateX(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .bg-success-soft {
        background-color: rgba(25, 135, 84, 0.1);
    }
    
    .bg-danger-soft {
        background-color: rgba(220, 53, 69, 0.1);
    }
    
    .classrooms-detail {
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
function toggleClassrooms(teacherId) {
    const element = document.getElementById('classrooms-' + teacherId);
    const icon = document.getElementById('icon-' + teacherId);
    
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

function confirmDeleteTeacher(teacherId, teacherName) {
    glassNotify.confirm(
        'حذف المعلم',
        `هل أنت متأكد من حذف المعلم "${teacherName}"؟`,
        function() {
            document.getElementById('delete-teacher-' + teacherId).submit();
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
