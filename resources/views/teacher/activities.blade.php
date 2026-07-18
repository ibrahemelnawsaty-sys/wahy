@extends('layouts.teacher')

@section('title', 'إدارة الأنشطة')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">📚 إدارة الأنشطة</h2>
            <p class="text-muted mb-0">أنشئ وعدّل أنشطتك التعليمية</p>
        </div>
        <a href="{{ route('teacher.activities.create') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-plus me-2"></i>إنشاء نشاط جديد
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card glass-effect border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary bg-gradient rounded">
                                <i class="fas fa-tasks text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">إجمالي الأنشطة</p>
                            <h4 class="mb-0">{{ $stats['total_activities'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card glass-effect border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success bg-gradient rounded">
                                <i class="fas fa-home text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">واجبات منزلية</p>
                            <h4 class="mb-0">{{ $stats['homework_count'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card glass-effect border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-info bg-gradient rounded">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">نشطة</p>
                            <h4 class="mb-0">{{ $stats['active_count'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card glass-effect border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-warning bg-gradient rounded">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">بانتظار المراجعة</p>
                            <h4 class="mb-0">{{ $stats['submissions_pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="card glass-effect border-0">
        <div class="card-body">
            @if($activities->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>النوع</th>
                                <th>الدرس</th>
                                <th>الفصل</th>
                                <th>النقاط</th>
                                <th>الموعد النهائي</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($activity->is_homework)
                                                <span class="badge bg-warning text-dark me-2">
                                                    <i class="fas fa-home"></i>
                                                </span>
                                            @endif
                                            <strong>{{ $activity->title }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @switch($activity->type)
                                            @case('quiz')
                                                <span class="badge bg-primary">🧪 اختبار</span>
                                                @break
                                            @case('exercise')
                                                <span class="badge bg-secondary">📋 تمرين</span>
                                                @break
                                            @case('project')
                                                <span class="badge bg-dark">🏗️ مشروع</span>
                                                @break
                                            @case('image_order')
                                                <span class="badge" style="background:#f97316">🖼️ ترتيب صور</span>
                                                @break
                                            @case('creative')
                                                <span class="badge" style="background:#a855f7">✨ إبداعي</span>
                                                @break
                                            @case('upload')
                                                <span class="badge bg-info">📤 رفع ملف</span>
                                                @break
                                            @case('practical')
                                                <span class="badge bg-success">🎯 عملي</span>
                                                @break
                                            @case('discussion')
                                                <span class="badge" style="background:#6366f1">💬 نقاش</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ $activity->type }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($activity->lesson)
                                            <small class="text-muted">
                                                {{ $activity->lesson->concept?->value?->name }} →
                                                {{ $activity->lesson->title }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->classroom)
                                            <span class="badge bg-light text-dark">{{ $activity->classroom->name }}</span>
                                        @else
                                            <span class="text-muted">جميع الفصول</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-gold">
                                            <i class="fas fa-star"></i> {{ $activity->points }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($activity->due_date)
                                            <small class="text-muted">
                                                {{ $activity->due_date->format('Y-m-d') }}
                                                <br>
                                                {{ $activity->due_date->format('H:i') }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->status === 'active')
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-secondary">غير نشط</span>
                                        @endif
                                        <div class="mt-1">
                                            @if($activity->school_approval_status === 'rejected' || $activity->approval_status === 'rejected')
                                                <span class="badge bg-danger" title="{{ $activity->school_rejection_reason ?: $activity->rejection_reason }}">❌ مرفوض</span>
                                            @elseif($activity->school_approval_status === 'pending')
                                                <span class="badge bg-warning text-dark">⏳ بانتظار مدير المدرسة</span>
                                            @elseif($activity->approval_status === 'pending')
                                                <span class="badge" style="background:#2563eb;color:#fff;">⏳ بانتظار الإدارة</span>
                                            @elseif($activity->approval_status === 'approved')
                                                <span class="badge bg-success">✅ معتمد</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('teacher.activities.preview', $activity->id) }}"
                                               class="btn btn-sm btn-outline-info" title="معاينة">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('teacher.activities.edit', $activity->id) }}"
                                               class="btn btn-sm btn-outline-primary" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($activity->school_approval_status === 'rejected' || $activity->approval_status === 'rejected')
                                            <form action="{{ route('teacher.activities.resubmit', $activity->id) }}" method="POST"
                                                  onsubmit="return confirm('إعادة إرسال هذا النشاط للاعتماد؟');" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="إعادة إرسال للاعتماد">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            </form>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteActivity({{ $activity->id }})" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $activities->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <h4>لا توجد أنشطة بعد</h4>
                    <p class="text-muted">ابدأ بإنشاء أول نشاط لطلابك</p>
                    <a href="{{ route('teacher.activities.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>إنشاء نشاط جديد
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteActivity(id) {
    glassNotify.confirm('هل أنت متأكد من حذف هذا النشاط؟', 'لن تتمكن من استرجاعه', function() {
        fetch(`/teacher/activities/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                glassNotify.success(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                glassNotify.error('حدث خطأ أثناء الحذف');
            }
        })
        .catch(error => {
            glassNotify.error('حدث خطأ أثناء الحذف');
        });
    });
}
</script>
@endpush
@endsection
