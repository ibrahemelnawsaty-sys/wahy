@extends('layouts.school-admin')

@section('page-title', 'طلبات التسجيل')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / طلبات التسجيل
@endsection

@section('content')
    <h2 class="fw-bold mb-4"><i class="fas fa-inbox text-danger me-2"></i>طلبات التسجيل</h2>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد</th>
                            <th>الدور</th>
                            <th>الهاتف</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $request->name }}</strong></td>
                                <td>{{ $request->email }}</td>
                                <td>
                                    @if($request->role === 'teacher')
                                        <span class="badge bg-primary">معلم</span>
                                    @elseif($request->role === 'student')
                                        <span class="badge bg-success">طالب</span>
                                    @else
                                        <span class="badge bg-info">ولي أمر</span>
                                    @endif
                                </td>
                                <td>{{ $request->phone ?? '-' }}</td>
                                <td>{{ $request->created_at->diffForHumans() }}</td>
                                <td>
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning">معلق</span>
                                    @elseif($request->status === 'approved')
                                        <span class="badge bg-success">مقبول</span>
                                    @else
                                        <span class="badge bg-danger">مرفوض</span>
                                    @endif
                                </td>
                                <td>
                                    <!-- زر عرض البيانات -->
                                    <button type="button" class="btn btn-sm btn-info mb-1" onclick="showRequestData({{ $request->id }})">
                                        <i class="fas fa-eye me-1"></i>عرض البيانات
                                    </button>
                                    @if($request->status === 'pending')
                                        <form id="approve-form-{{ $request->id }}" action="{{ route('school-admin.requests.approve', $request->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-success" onclick="confirmApprove({{ $request->id }}, '{{ $request->name }}')">
                                                <i class="fas fa-check me-1"></i>قبول
                                            </button>
                                        </form>
                                        <form id="reject-form-{{ $request->id }}" action="{{ route('school-admin.requests.reject', $request->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmReject({{ $request->id }}, '{{ $request->name }}')">
                                                <i class="fas fa-times me-1"></i>رفض
                                            </button>
                                        </form>
                                    @else
                                        <small class="text-muted">تمت المراجعة</small>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal عرض البيانات -->
                            <div class="modal fade" id="dataModal-{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                                        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 25px 30px;">
                                            <h5 class="modal-title text-white fw-bold">
                                                <i class="fas fa-user-circle me-2"></i>
                                                بيانات التسجيل: {{ $request->name }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <!-- البيانات الأساسية -->
                                            <div class="card border-0 mb-3" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-radius: 15px;">
                                                <div class="card-body p-3">
                                                    <h6 class="fw-bold mb-3"><i class="fas fa-id-card me-2 text-primary"></i>البيانات الأساسية</h6>
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-user me-1"></i>الاسم:</strong> {{ $request->name }}</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-envelope me-1"></i>البريد:</strong> {{ $request->email }}</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-phone me-1"></i>الهاتف:</strong> {{ $request->phone ?? 'غير محدد' }}</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-user-tag me-1"></i>الدور:</strong> 
                                                                @if($request->role === 'student') طالب @elseif($request->role === 'teacher') معلم @else ولي أمر @endif
                                                            </small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-clock me-1"></i>تاريخ الطلب:</strong> {{ $request->created_at->format('Y-m-d H:i') }}</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-flag me-1"></i>الحالة:</strong> 
                                                                @if($request->status === 'pending') <span class="badge bg-warning">معلق</span>
                                                                @elseif($request->status === 'approved') <span class="badge bg-success">مقبول</span>
                                                                @else <span class="badge bg-danger">مرفوض</span> @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- البيانات الإضافية -->
                                            @php $extraData = json_decode($request->data, true) ?? []; @endphp
                                            @if(!empty($extraData))
                                            <div class="card border-0" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-radius: 15px;">
                                                <div class="card-body p-3">
                                                    <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-success"></i>بيانات إضافية</h6>
                                                    <div class="row g-2">
                                                        @if(!empty($extraData['birth_date']))
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-birthday-cake me-1"></i>تاريخ الميلاد:</strong> {{ $extraData['birth_date'] }}</small>
                                                        </div>
                                                        @endif
                                                        @if(!empty($extraData['grade_level']))
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-graduation-cap me-1"></i>المرحلة الدراسية:</strong> {{ $extraData['grade_level'] }}</small>
                                                        </div>
                                                        @endif
                                                        @if(!empty($extraData['parent_name']))
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-user-tie me-1"></i>اسم ولي الأمر:</strong> {{ $extraData['parent_name'] }}</small>
                                                        </div>
                                                        @endif
                                                        @if(!empty($extraData['parent_email']))
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-envelope me-1"></i>بريد ولي الأمر:</strong> {{ $extraData['parent_email'] }}</small>
                                                        </div>
                                                        @endif
                                                        @if(!empty($extraData['parent_phone']))
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-phone me-1"></i>جوال ولي الأمر:</strong> {{ $extraData['parent_phone'] }}</small>
                                                        </div>
                                                        @endif
                                                        @if(!empty($extraData['qualifications']))
                                                        <div class="col-12">
                                                            <small class="d-block"><strong><i class="fas fa-certificate me-1"></i>المؤهلات:</strong> {{ $extraData['qualifications'] }}</small>
                                                        </div>
                                                        @endif
                                                        @if(!empty($extraData['specialization']))
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-book me-1"></i>التخصص:</strong> {{ $extraData['specialization'] }}</small>
                                                        </div>
                                                        @endif
                                                        @if(!empty($extraData['experience_years']))
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-briefcase me-1"></i>سنوات الخبرة:</strong> {{ $extraData['experience_years'] }}</small>
                                                        </div>
                                                        @endif
                                                        @if(!empty($extraData['relationship']))
                                                        <div class="col-md-6">
                                                            <small class="d-block"><strong><i class="fas fa-heart me-1"></i>صلة القرابة:</strong> {{ $extraData['relationship'] }}</small>
                                                        </div>
                                                        @endif
                                                        @if(!empty($extraData['children_names']))
                                                        <div class="col-12">
                                                            <small class="d-block"><strong><i class="fas fa-child me-1"></i>أسماء الأبناء:</strong> {{ $extraData['children_names'] }}</small>
                                                        </div>
                                                        @endif
                                                        @if(!empty($extraData['address']))
                                                        <div class="col-12">
                                                            <small class="d-block"><strong><i class="fas fa-map-marker-alt me-1"></i>العنوان:</strong> {{ $extraData['address'] }}</small>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                                <small>لا توجد بيانات إضافية</small>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">لا توجد طلبات</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function showRequestData(requestId) {
    var modal = new bootstrap.Modal(document.getElementById('dataModal-' + requestId));
    modal.show();
}

function confirmApprove(requestId, requestName) {
    glassNotify.confirm(
        'قبول الطلب',
        `هل أنت متأكد من قبول طلب التسجيل لـ "${requestName}"؟`,
        function() {
            document.getElementById('approve-form-' + requestId).submit();
        },
        {
            confirmText: 'قبول',
            cancelText: 'إلغاء',
            type: 'success'
        }
    );
}

function confirmReject(requestId, requestName) {
    glassNotify.confirm(
        'رفض الطلب',
        `هل أنت متأكد من رفض طلب التسجيل لـ "${requestName}"؟`,
        function() {
            document.getElementById('reject-form-' + requestId).submit();
        },
        {
            confirmText: 'رفض',
            cancelText: 'إلغاء',
            type: 'error'
        }
    );
}
</script>
@endpush

