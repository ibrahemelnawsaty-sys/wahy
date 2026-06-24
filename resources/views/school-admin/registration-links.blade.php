@extends('layouts.school-admin')

@section('page-title', 'روابط التسجيل')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / روابط التسجيل
@endsection

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-link text-primary me-2"></i>
            روابط التسجيل الذاتي
        </h2>
        <p class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            شارك هذه الروابط مع المعلمين والطلاب وأولياء الأمور للتسجيل المباشر
        </p>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- بطاقة المعلمين -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 registration-card">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        تسجيل المعلمين
                    </h5>
                </div>
                <div class="card-body">
                    <!-- حالة التسجيل -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">حالة التسجيل:</span>
                        <form action="{{ route('school-admin.toggle-registration') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="role" value="teacher">
                            <button type="submit" class="btn btn-sm {{ $school->enable_teacher_registration ? 'btn-success' : 'btn-danger' }}">
                                <i class="fas {{ $school->enable_teacher_registration ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                {{ $school->enable_teacher_registration ? 'مفعّل' : 'معطّل' }}
                            </button>
                        </form>
                    </div>

                    @if($school->enable_teacher_registration)
                    <!-- الرابط -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">الرابط المباشر:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="teacherLink" value="{{ $school->teacher_registration_url }}" readonly>
                            <button class="btn btn-outline-primary" onclick="copyToClipboard('teacherLink')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="text-center mb-3">
                        <div class="qr-code-wrapper p-3 bg-light rounded">
                            {!! QrCode::size(200)->generate($school->teacher_registration_url) !!}
                        </div>
                        <button class="btn btn-sm btn-outline-info mt-2" onclick="downloadQR('teacher')">
                            <i class="fas fa-download me-1"></i>تحميل QR Code
                        </button>
                    </div>

                    <!-- تجديد الرابط -->
                    <form id="regenerate-teacher-form" action="{{ route('school-admin.regenerate-token') }}" method="POST">
                        @csrf
                        <input type="hidden" name="role" value="teacher">
                        <button type="button" class="btn btn-outline-warning btn-sm w-100" 
                                onclick="confirmRegenerate('regenerate-teacher-form')">
                            <i class="fas fa-sync-alt me-1"></i>تجديد الرابط
                        </button>
                    </form>
                    @else
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        التسجيل معطّل حالياً
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- بطاقة الطلاب -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 registration-card">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        تسجيل الطلاب
                    </h5>
                </div>
                <div class="card-body">
                    <!-- حالة التسجيل -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">حالة التسجيل:</span>
                        <form action="{{ route('school-admin.toggle-registration') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="role" value="student">
                            <button type="submit" class="btn btn-sm {{ $school->enable_student_registration ? 'btn-success' : 'btn-danger' }}">
                                <i class="fas {{ $school->enable_student_registration ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                {{ $school->enable_student_registration ? 'مفعّل' : 'معطّل' }}
                            </button>
                        </form>
                    </div>

                    @if($school->enable_student_registration)
                    <!-- الرابط -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">الرابط المباشر:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="studentLink" value="{{ $school->student_registration_url }}" readonly>
                            <button class="btn btn-outline-primary" onclick="copyToClipboard('studentLink')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="text-center mb-3">
                        <div class="qr-code-wrapper p-3 bg-light rounded">
                            {!! QrCode::size(200)->generate($school->student_registration_url) !!}
                        </div>
                        <button class="btn btn-sm btn-outline-info mt-2" onclick="downloadQR('student')">
                            <i class="fas fa-download me-1"></i>تحميل QR Code
                        </button>
                    </div>

                    <!-- تجديد الرابط -->
                    <form id="regenerate-student-form" action="{{ route('school-admin.regenerate-token') }}" method="POST">
                        @csrf
                        <input type="hidden" name="role" value="student">
                        <button type="button" class="btn btn-outline-warning btn-sm w-100" 
                                onclick="confirmRegenerate('regenerate-student-form')">
                            <i class="fas fa-sync-alt me-1"></i>تجديد الرابط
                        </button>
                    </form>
                    @else
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        التسجيل معطّل حالياً
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- بطاقة أولياء الأمور -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 registration-card">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        تسجيل أولياء الأمور
                    </h5>
                </div>
                <div class="card-body">
                    <!-- حالة التسجيل -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">حالة التسجيل:</span>
                        <form action="{{ route('school-admin.toggle-registration') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="role" value="parent">
                            <button type="submit" class="btn btn-sm {{ $school->enable_parent_registration ? 'btn-success' : 'btn-danger' }}">
                                <i class="fas {{ $school->enable_parent_registration ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                {{ $school->enable_parent_registration ? 'مفعّل' : 'معطّل' }}
                            </button>
                        </form>
                    </div>

                    @if($school->enable_parent_registration)
                    <!-- الرابط -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">الرابط المباشر:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="parentLink" value="{{ $school->parent_registration_url }}" readonly>
                            <button class="btn btn-outline-primary" onclick="copyToClipboard('parentLink')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="text-center mb-3">
                        <div class="qr-code-wrapper p-3 bg-light rounded">
                            {!! QrCode::size(200)->generate($school->parent_registration_url) !!}
                        </div>
                        <button class="btn btn-sm btn-outline-info mt-2" onclick="downloadQR('parent')">
                            <i class="fas fa-download me-1"></i>تحميل QR Code
                        </button>
                    </div>

                    <!-- تجديد الرابط -->
                    <form id="regenerate-parent-form" action="{{ route('school-admin.regenerate-token') }}" method="POST">
                        @csrf
                        <input type="hidden" name="role" value="parent">
                        <button type="button" class="btn btn-outline-warning btn-sm w-100" 
                                onclick="confirmRegenerate('regenerate-parent-form')">
                            <i class="fas fa-sync-alt me-1"></i>تجديد الرابط
                        </button>
                    </form>
                    @else
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        التسجيل معطّل حالياً
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- معلومات إضافية -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-question-circle text-info me-2"></i>
                كيفية الاستخدام
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <strong>1</strong>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fw-bold">مشاركة الرابط</h6>
                            <p class="text-muted small mb-0">انسخ الرابط وشاركه مع المعلمين/الطلاب/أولياء الأمور عبر البريد أو الواتساب</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <strong>2</strong>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fw-bold">استخدام QR Code</h6>
                            <p class="text-muted small mb-0">اطبع الـ QR Code واعرضه في المدرسة للتسجيل السريع عبر الهاتف</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <strong>3</strong>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fw-bold">مراجعة الطلبات</h6>
                            <p class="text-muted small mb-0">ستظهر جميع الطلبات في صفحة "طلبات التسجيل" للموافقة عليها أو رفضها</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <strong>4</strong>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fw-bold">الأمان</h6>
                            <p class="text-muted small mb-0">يمكنك تعطيل التسجيل أو تجديد الرابط في أي وقت للحفاظ على الأمان</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .registration-card {
        transition: all 0.3s ease;
    }
    
    .registration-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }
    
    .qr-code-wrapper svg {
        width: 100%;
        height: auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
function copyToClipboard(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    document.execCommand('copy');
    
    // إظهار رسالة نجاح
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
    }, 2000);
}

function downloadQR(role) {
    const qrElement = event.target.closest('.card-body').querySelector('.qr-code-wrapper');
    
    html2canvas(qrElement).then(canvas => {
        const link = document.createElement('a');
        link.download = `qr-${role}-{{ $school->name }}.png`;
        link.href = canvas.toDataURL();
        link.click();
    });
}

function confirmRegenerate(formId) {
    glassNotify.confirm(
        'تأكيد تجديد الرابط',
        'تجديد الرابط سيجعل الرابط القديم غير صالح وستحتاج لمشاركة الرابط الجديد. هل أنت متأكد؟',
        function() {
            document.getElementById(formId).submit();
        },
        {
            confirmText: 'نعم، جدّد الرابط',
            cancelText: 'إلغاء',
            confirmType: 'warning'
        }
    );
}
</script>
@endpush
