@extends('layouts.parent')

@section('title', 'الأنشطة العائلية المعلقة')

@section('content')
<div class="container-fluid px-4 py-5">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="mb-1">👨‍👩‍👧 الأنشطة العائلية المعلقة</h2>
        <p class="text-muted mb-0">راجع وافق على الأنشطة العائلية التي أكملها أبناؤك</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Submissions -->
    @if($submissions->count() > 0)
        <div class="row g-4">
            @foreach($submissions as $submission)
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-warning bg-opacity-10 border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-clock text-warning me-2"></i>
                                    بانتظار الموافقة
                                </h5>
                                <span class="badge bg-warning">جديد</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Student Info -->
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px;">
                                    {{ mb_substr($submission->student->name, 0, 1, "UTF-8") }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $submission->student->name }}</h6>
                                    <small class="text-muted">الطالب</small>
                                </div>
                            </div>

                            <!-- Activity Info -->
                            <div class="mb-3">
                                <h6 class="text-primary mb-2">
                                    <i class="fas fa-tasks me-2"></i>
                                    {{ $submission->activity->title }}
                                </h6>
                                <p class="text-muted small mb-0">
                                    {{ html_excerpt($submission->activity->description, 160) }}
                                </p>
                            </div>

                            <!-- Submission Data -->
                            @if($submission->submission_data)
                                <div class="mb-3">
                                    <h6 class="mb-2">تفاصيل التسليم:</h6>
                                    <div class="bg-light rounded p-3">
                                        @foreach($submission->submission_data as $key => $value)
                                            <div class="mb-2">
                                                <strong>{{ $key }}:</strong> {{ $value }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Photos -->
                            @if($submission->photos && count($submission->photos) > 0)
                                <div class="mb-3">
                                    <h6 class="mb-2">الصور:</h6>
                                    <div class="row g-2">
                                        @foreach($submission->photos as $photo)
                                            <div class="col-4">
                                                <img src="{{ asset('storage/app/public/data/' . $photo) }}" 
                                                     class="img-fluid rounded" 
                                                     alt="صورة النشاط"
                                                     style="cursor: pointer;"
                                                     onclick="showImageModal('{{ asset('storage/app/public/data/' . $photo) }}')">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Submission Date -->
                            <div class="text-muted small mb-3">
                                <i class="far fa-clock me-1"></i>
                                تم التسليم: {{ $submission->created_at->diffForHumans() }}
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2">
                                <button class="btn btn-success flex-grow-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#approveModal{{ $submission->id }}">
                                    <i class="fas fa-check me-2"></i>موافقة
                                </button>
                                <button class="btn btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModal{{ $submission->id }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approve Modal -->
                <div class="modal fade" id="approveModal{{ $submission->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('parent.family-activities.approve', $submission->id) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">الموافقة على النشاط</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-success">
                                        <i class="fas fa-info-circle me-2"></i>
                                        عند الموافقة، سيحصل الطالب على <strong>20 نقطة</strong> وستحصل أنت على <strong>10 نقاط</strong>!
                                    </div>

                                    <!-- Praise Options -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">اختر رسالة مدح:</label>
                                        <select name="praise" class="form-select mb-2" id="praiseSelect{{ $submission->id }}">
                                            <option value="">-- اختر رسالة --</option>
                                            <option value="أحسنت! عمل رائع 👏">أحسنت! عمل رائع 👏</option>
                                            <option value="ممتاز! فخور بك 🌟">ممتاز! فخور بك 🌟</option>
                                            <option value="رائع! استمر في التميز 🚀">رائع! استمر في التميز 🚀</option>
                                            <option value="مبدع! أنت الأفضل 💪">مبدع! أنت الأفضل 💪</option>
                                            <option value="custom">رسالة مخصصة...</option>
                                        </select>
                                    </div>

                                    <!-- Custom Praise -->
                                    <div class="mb-3" id="customPraise{{ $submission->id }}" style="display: none;">
                                        <label class="form-label fw-bold">رسالة مدح مخصصة:</label>
                                        <textarea name="custom_praise" class="form-control" rows="3" 
                                                  placeholder="اكتب رسالة مدح خاصة لابنك..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        إلغاء
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-2"></i>موافقة وإرسال المدح
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div class="modal fade" id="rejectModal{{ $submission->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('parent.family-activities.approve', $submission->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="reject" value="1">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">رفض النشاط</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        هل أنت متأكد من رفض هذا النشاط؟
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">سبب الرفض:</label>
                                        <textarea name="rejection_reason" class="form-control" rows="3" 
                                                  placeholder="اكتب سبب الرفض..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        إلغاء
                                    </button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-times me-2"></i>رفض النشاط
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                document.getElementById('praiseSelect{{ $submission->id }}').addEventListener('change', function() {
                    const customDiv = document.getElementById('customPraise{{ $submission->id }}');
                    if (this.value === 'custom') {
                        customDiv.style.display = 'block';
                    } else {
                        customDiv.style.display = 'none';
                    }
                });
                </script>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $submissions->links() }}
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>لا توجد أنشطة معلقة</h5>
                <p class="text-muted mb-0">جميع الأنشطة العائلية تمت مراجعتها</p>
            </div>
        </div>
    @endif
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <img id="modalImage" src="" class="img-fluid w-100" alt="">
            </div>
        </div>
    </div>
</div>

<script>
function showImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endsection
