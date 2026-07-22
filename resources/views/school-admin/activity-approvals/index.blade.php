@extends('layouts.school-admin')

@section('page-title', 'اعتماد الأنشطة')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / اعتماد الأنشطة
@endsection

@section('content')
    <h2 class="fw-bold mb-4"><i class="fas fa-clipboard-check text-primary me-2"></i>اعتماد أنشطة المعلّمين</h2>

    {{-- بطاقات الحالة / فلترة --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <a href="{{ route('school-admin.activity-approvals', ['status' => 'pending']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm {{ $status === 'pending' ? 'border-start border-4 border-warning' : '' }}">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-warning">{{ $stats['pending'] }}</div>
                        <small class="text-muted">بانتظار الاعتماد</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4">
            <a href="{{ route('school-admin.activity-approvals', ['status' => 'approved']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm {{ $status === 'approved' ? 'border-start border-4 border-success' : '' }}">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-success">{{ $stats['approved'] }}</div>
                        <small class="text-muted">معتمدة</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4">
            <a href="{{ route('school-admin.activity-approvals', ['status' => 'rejected']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm {{ $status === 'rejected' ? 'border-start border-4 border-danger' : '' }}">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-danger">{{ $stats['rejected'] }}</div>
                        <small class="text-muted">مرفوضة</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>العنوان</th>
                            <th>المعلّم</th>
                            <th>الدرس</th>
                            <th>النوع</th>
                            <th>النقاط</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $activity->title }}</strong>
                                    @if($activity->is_activity_bank)
                                        <span class="badge bg-secondary">بنك</span>
                                    @else
                                        <span class="badge bg-info">درس</span>
                                    @endif
                                </td>
                                <td>{{ $activity->creator->name ?? '—' }}</td>
                                <td>{{ $activity->lesson->title ?? '—' }}</td>
                                <td>{{ $activity->type }}</td>
                                <td>{{ $activity->points }}</td>
                                <td>{{ $activity->created_at->diffForHumans() }}</td>
                                <td>
                                    @if($activity->school_approval_status === 'pending')
                                        <span class="badge bg-warning">بانتظار الاعتماد</span>
                                    @elseif($activity->school_approval_status === 'approved')
                                        <span class="badge bg-success">معتمد</span>
                                    @else
                                        <span class="badge bg-danger">مرفوض</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('school-admin.activities.show', $activity->id) }}"
                                        class="btn btn-sm btn-info mb-1">
                                        <i class="fas fa-eye me-1"></i>عرض
                                    </a>
                                    @if($activity->school_approval_status === 'pending')
                                        <form id="approve-activity-{{ $activity->id }}"
                                            action="{{ route('school-admin.activity-approvals.approve', $activity->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="publish_mode" value="direct">
                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="openApproveActivity({{ $activity->id }}, @js($activity->title))">
                                                <i class="fas fa-check me-1"></i>اعتماد
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="openRejectActivity({{ $activity->id }}, @js($activity->title))">
                                            <i class="fas fa-times me-1"></i>رفض
                                        </button>
                                    @elseif($activity->school_approval_status === 'rejected')
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-comment-dots me-1"></i>{{ $activity->school_rejection_reason }}
                                        </small>
                                    @else
                                        <small class="text-muted">تمت المراجعة</small>
                                    @endif
                                </td>
                            </tr>

                            {{-- Modal تفاصيل النشاط --}}
                            <div class="modal fade" id="activityModal-{{ $activity->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                                        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 25px 30px;">
                                            <h5 class="modal-title text-white fw-bold">
                                                <i class="fas fa-clipboard-list me-2"></i>{{ $activity->title }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-2 mb-3">
                                                <div class="col-md-6"><small class="d-block"><strong><i class="fas fa-chalkboard-teacher me-1"></i>المعلّم:</strong> {{ $activity->creator->name ?? '—' }}</small></div>
                                                <div class="col-md-6"><small class="d-block"><strong><i class="fas fa-book me-1"></i>الدرس:</strong> {{ $activity->lesson->title ?? '—' }}</small></div>
                                                <div class="col-md-6"><small class="d-block"><strong><i class="fas fa-shapes me-1"></i>النوع:</strong> {{ $activity->type }}</small></div>
                                                <div class="col-md-6"><small class="d-block"><strong><i class="fas fa-star me-1"></i>النقاط:</strong> {{ $activity->points }}</small></div>
                                                <div class="col-md-6"><small class="d-block"><strong><i class="fas fa-check-circle me-1"></i>درجة النجاح:</strong> {{ $activity->passing_score ?? '—' }}</small></div>
                                                <div class="col-md-6"><small class="d-block"><strong><i class="fas fa-user-check me-1"></i>التصحيح:</strong> {{ $activity->manual_review ? 'يدوي (المعلّم)' : 'تلقائي' }}</small></div>
                                            </div>
                                            @if($activity->description)
                                                <div class="card border-0 mb-2" style="background:#f8f9fa; border-radius:12px;">
                                                    <div class="card-body p-3">
                                                        <h6 class="fw-bold"><i class="fas fa-align-right me-1"></i>الوصف</h6>
                                                        <div>{!! safe_html($activity->description) !!}</div>
                                                    </div>
                                                </div>
                                            @endif
                                            @php $qCount = is_array($activity->questions) ? count($activity->questions) : 0; @endphp
                                            <small class="text-muted"><i class="fas fa-list-ol me-1"></i>عدد الأسئلة: {{ $qCount }}</small>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-clipboard-check fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">لا توجد أنشطة في هذه الحالة</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $activities->links() }}
            </div>
        </div>
    </div>

    {{-- Modal اعتماد النشاط مع اختيار وضع النشر لمدرستي --}}
    <div class="modal fade" id="approveActivityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 18px; overflow: hidden;">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i>اعتماد النشاط لمدرستي</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">النشاط: <strong id="approveActivityTitle"></strong></p>
                    <label class="form-label fw-bold mb-2">وضع النشر لطلاب مدرستك</label>
                    <div class="form-check p-3 mb-2 border rounded-3">
                        <input class="form-check-input" type="radio" name="approvePublishMode" id="apmDirect" value="direct" checked>
                        <label class="form-check-label" for="apmDirect">
                            <strong><i class="fas fa-users me-1 text-success"></i>مباشر للطلاب</strong>
                            <small class="d-block text-muted">يظهر تلقائيًّا لطلاب مدرستك ضمن الدرس/الواجب.</small>
                        </label>
                    </div>
                    <div class="form-check p-3 border rounded-3">
                        <input class="form-check-input" type="radio" name="approvePublishMode" id="apmBank" value="bank">
                        <label class="form-check-label" for="apmBank">
                            <strong><i class="fas fa-box-archive me-1 text-secondary"></i>للبنك فقط</strong>
                            <small class="d-block text-muted">يُتاح لمعلّمي مدرستك لإسناده لفصولهم — لا يظهر تلقائيًّا للطلاب.</small>
                        </label>
                    </div>
                    <p class="text-muted small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i>سيُرفَع بعدها للإدارة للمراجعة النهائية ونشره لبقيّة المدارس.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-success" id="approveActivityConfirmBtn"><i class="fas fa-check me-1"></i>اعتماد</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal الرفض مع سبب --}}
    <div class="modal fade" id="rejectActivityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 18px; overflow: hidden;">
                <form id="rejectActivityForm" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-times-circle me-2"></i>رفض النشاط</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-2">أنت على وشك رفض النشاط: <strong id="rejectActivityTitle"></strong></p>
                        <label class="form-label fw-bold">سبب الرفض <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required
                            placeholder="اكتب سبب الرفض ليطّلع عليه المعلّم ويعدّل نشاطه"></textarea>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>تأكيد الرفض</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function showActivityDetails(id) {
    var modal = new bootstrap.Modal(document.getElementById('activityModal-' + id));
    modal.show();
}

let _approveActivityId = null;
function openApproveActivity(id, title) {
    _approveActivityId = id;
    document.getElementById('approveActivityTitle').textContent = title;
    document.getElementById('apmDirect').checked = true;
    new bootstrap.Modal(document.getElementById('approveActivityModal')).show();
}
document.getElementById('approveActivityConfirmBtn').addEventListener('click', function () {
    if (!_approveActivityId) return;
    const form = document.getElementById('approve-activity-' + _approveActivityId);
    const mode = document.querySelector('input[name="approvePublishMode"]:checked').value;
    form.querySelector('input[name="publish_mode"]').value = mode;
    form.submit();
});

function openRejectActivity(id, title) {
    var form = document.getElementById('rejectActivityForm');
    form.action = '{{ url('school-admin/activity-approvals') }}/' + id + '/reject';
    document.getElementById('rejectActivityTitle').textContent = title;
    form.querySelector('textarea[name="rejection_reason"]').value = '';
    new bootstrap.Modal(document.getElementById('rejectActivityModal')).show();
}
</script>
@endpush
