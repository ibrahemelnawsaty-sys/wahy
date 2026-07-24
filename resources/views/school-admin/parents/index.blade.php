@extends('layouts.school-admin')

@section('page-title', 'إدارة أولياء الأمور')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / إدارة أولياء الأمور
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-2">
                <i class="fas fa-users text-info me-2"></i>
                إدارة أولياء الأمور
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                عرض شامل لأولياء الأمور مع أبنائهم وفصولهم الدراسية
            </p>
        </div>
        <a href="{{ route('school-admin.parents.create') }}" class="btn btn-info btn-lg shadow-sm">
            <i class="fas fa-plus me-2"></i>
            إضافة ولي أمر جديد
        </a>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #17a2b8;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-users fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $parents->total() }}</h3>
                            <small class="text-muted">إجمالي أولياء الأمور</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #20c997;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background: rgba(32, 201, 151, 0.1);">
                                <i class="fas fa-check-circle fa-2x" style="color: #20c997;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $parents->where('status', 'active')->count() }}</h3>
                            <small class="text-muted">ولي أمر نشط</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #6610f2;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background: rgba(102, 16, 242, 0.1);">
                                <i class="fas fa-child fa-2x" style="color: #6610f2;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $parents->sum('children_count') }}</h3>
                            <small class="text-muted">إجمالي الأبناء</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- قائمة أولياء الأمور المفصلة -->
    @forelse($parents as $parent)
        <div class="card border-0 shadow-sm mb-3 parent-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- معلومات ولي الأمر -->
                    <div class="col-lg-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-lg bg-gradient-info text-white me-3">
                                {{ mb_substr($parent->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-bold">{{ $parent->name }}</h5>
                                <div class="text-muted small mb-1">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $parent->email }}
                                </div>
                                @if($parent->phone)
                                <div class="text-muted small">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $parent->phone }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- الإحصائيات -->
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="row g-2 text-center">
                            <div class="col-12">
                                <div class="p-2 rounded stats-box-info">
                                    <div class="text-white">
                                        <h4 class="mb-0 fw-bold">{{ $parent->children_count }}</h4>
                                        <small>ابن/ابنة</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-center">
                            @if($parent->status === 'active')
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
                            <a href="{{ route('school-admin.parents.edit', $parent->id) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>تعديل البيانات
                            </a>
                            <button type="button" class="btn btn-outline-info btn-sm" 
                                    onclick="toggleChildren({{ $parent->id }})">
                                <i class="fas fa-child me-1"></i>عرض الأبناء
                                <i class="fas fa-chevron-down ms-1" id="icon-{{ $parent->id }}"></i>
                            </button>
                            {{-- الحذف يُتاح فقط لحساب وليّ أمر أساسيّ؛ مستخدم «+وليّ أمر» (دوره الأساسيّ معلّم/طالب...)
                                 يُدار رابط أبنائه من «تعديل البيانات» ولا يُحذف حسابه كاملاً من هنا (كان يفشل بـ404). --}}
                            @if($parent->role === 'parent')
                            <form id="delete-parent-{{ $parent->id }}" action="{{ route('school-admin.parents.delete', $parent->id) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="confirmDeleteParent({{ $parent->id }}, @js($parent->name))">
                                    <i class="fas fa-trash me-1"></i>حذف
                                </button>
                            </form>
                            @else
                            <span class="badge bg-light text-muted align-self-start" title="حساب متعدّد الأدوار — يُدار من إدارة المستخدمين">
                                <i class="fas fa-user-tag me-1"></i>+وليّ أمر
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- تفاصيل الأبناء -->
                @if($parent->children->isNotEmpty())
                <div class="children-detail mt-3 pt-3 border-top" id="children-{{ $parent->id }}" style="display: none;">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-child text-info me-2"></i>
                        الأبناء ({{ $parent->children_count }})
                    </h6>
                    <div class="row g-3">
                        @foreach($parent->children as $child)
                        <div class="col-md-6">
                            <div class="card border-0 child-info-card">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="avatar-sm bg-success bg-opacity-10 text-success me-2">
                                            {{ mb_substr($child->name, 0, 1) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold">{{ $child->name }}</h6>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-envelope me-1"></i>
                                                {{ $child->email }}
                                            </small>
                                            <span class="badge bg-info mt-1" style="font-size: 10px;">
                                                {{ $child->pivot->relationship ?? 'ولي أمر' }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($child->classrooms->isNotEmpty())
                                        <div class="mt-2">
                                            <small class="text-muted fw-bold d-block mb-1">
                                                <i class="fas fa-door-open me-1"></i>الفصول:
                                            </small>
                                            @foreach($child->classrooms as $classroom)
                                                <div class="badge bg-primary-soft text-primary me-1 mb-1" style="font-size: 11px;">
                                                    {{ $classroom->name }}
                                                    @if($classroom->teacher)
                                                        <small class="d-block">
                                                            المعلم: {{ $classroom->teacher->name }}
                                                        </small>
                                                    @endif
                                                    @if($classroom->grade_level)
                                                        <small class="d-block">
                                                            المستوى: {{ $classroom->grade_level }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <small class="text-muted">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            لم يتم تسجيله في أي فصل
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="children-detail mt-3 pt-3 border-top" id="children-{{ $parent->id }}" style="display: none;">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        لم يتم ربط أبناء بهذا الحساب بعد
                    </div>
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-5x text-muted mb-4"></i>
                <h4 class="text-muted mb-2">لا يوجد أولياء أمور بعد</h4>
                <p class="text-muted mb-4">ابدأ بإضافة أولياء الأمور لإدارة المدرسة</p>
                <a href="{{ route('school-admin.parents.create') }}" class="btn btn-info">
                    <i class="fas fa-plus me-2"></i>إضافة أول ولي أمر
                </a>
            </div>
        </div>
    @endforelse

    <!-- الترقيم -->
    @if($parents->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $parents->links() }}
    </div>
    @endif
@endsection

@push('styles')
<style>
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(23, 162, 184, 0.3);
    }
    
    .parent-card {
        transition: all 0.3s ease;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .parent-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }
    
    .avatar-lg {
        transition: all 0.3s ease;
    }
    
    .parent-card:hover .avatar-lg {
        transform: scale(1.1);
    }
    
    .stats-box-info {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    }
    
    .child-info-card {
        background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .child-info-card:hover {
        transform: translateX(-5px);
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.2);
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
    
    .bg-primary-soft {
        background-color: rgba(13, 110, 253, 0.1);
        padding: 8px 12px;
        border-radius: 8px;
        display: inline-block;
    }
    
    .children-detail {
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
function toggleChildren(parentId) {
    const element = document.getElementById('children-' + parentId);
    const icon = document.getElementById('icon-' + parentId);
    
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

function confirmDeleteParent(parentId, parentName) {
    glassNotify.confirm(
        'حذف ولي الأمر',
        `هل أنت متأكد من حذف ولي الأمر "${parentName}"؟`,
        function() {
            document.getElementById('delete-parent-' + parentId).submit();
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
