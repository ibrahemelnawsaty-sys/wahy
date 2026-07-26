@extends('layouts.school-admin')

@section('page-title', 'التقديمات المعلّقة')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / التقديمات المعلّقة
@endsection

@section('content')
    <h2 class="fw-bold mb-1"><i class="fas fa-hourglass-half text-primary me-2"></i>التقديمات المعلّقة</h2>
    <p class="text-muted mb-4">تسليمات طلاب مدرستك العالقة بانتظار تصحيح المعلّم أو موافقة وليّ الأمر — يمكنك حسمها احتياطيّاً عند عدم تجاوبهم.</p>

    {{-- success/error يعرضهما التخطيط عالميّاً؛ نعرض 'info' فقط هنا (لا يعالجه التخطيط) تفاديّاً للازدواج. --}}
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- بطاقات الحالة --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-primary">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-primary">{{ $stats['total'] }}</div>
                    <small class="text-muted">إجمالي المعلّقة</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-info">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-info">{{ $stats['awaiting_teacher'] }}</div>
                    <small class="text-muted">بانتظار المعلّم</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-warning">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-warning">{{ $stats['awaiting_parent'] }}</div>
                    <small class="text-muted">بانتظار وليّ الأمر</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>النشاط</th>
                            <th>الإجابة</th>
                            <th>الدرجة</th>
                            <th>سبب التعليق</th>
                            <th>التاريخ</th>
                            <th style="min-width: 230px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $s)
                            @php
                                $awaitingParent = ($s->parent_approval_status ?? null) === 'pending';
                                // معاينة الإجابة: تسليم الويب يخزّن JSON داخل عمود answer (لا file_path)،
                                // فنفكّه لعرض تسمية ودّية بدل كتلة JSON خام.
                                $decoded = $s->answer ? json_decode($s->answer, true) : null;
                                if ($s->file_path || (is_array($decoded) && (! empty($decoded['file']) || ! empty($decoded['file_url'])))) {
                                    $note = (is_array($decoded) && ! empty($decoded['note']))
                                        ? ' — ' . \Illuminate\Support\Str::limit($decoded['note'], 40) : '';
                                    $answerPreview = '📎 ملف مرفوع' . $note;
                                } elseif (is_array($decoded)) {
                                    $answerPreview = 'إجابة اختيار (' . count($decoded) . ' عنصر)';
                                } elseif ($s->answer) {
                                    $answerPreview = \Illuminate\Support\Str::limit($s->answer, 50);
                                } else {
                                    $answerPreview = '—';
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $s->student->name ?? '—' }}</strong></td>
                                <td>
                                    {{ $s->activity->title ?? '—' }}
                                    @if ($s->activity && $s->activity->type)
                                        <span class="badge bg-secondary">{{ $s->activity->type }}</span>
                                    @endif
                                </td>
                                <td><span class="text-muted small">{{ $answerPreview }}</span></td>
                                <td>{{ $s->score !== null ? $s->score . '%' : '—' }}</td>
                                <td>
                                    @if ($awaitingParent)
                                        <span class="badge bg-warning text-dark">بانتظار وليّ الأمر</span>
                                    @else
                                        <span class="badge bg-info">بانتظار المعلّم</span>
                                    @endif
                                </td>
                                <td>{{ optional($s->submitted_at)->diffForHumans() ?? '—' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <form method="POST" action="{{ route('school-admin.pending-submissions.approve', $s->id) }}"
                                              class="d-inline-flex align-items-center gap-1"
                                              onsubmit="return confirm('اعتماد هذا التسليم ومنح الطالب نقاطه؟');">
                                            @csrf
                                            <input type="number" name="score" min="0" max="100"
                                                   value="{{ $s->score }}" placeholder="الدرجة"
                                                   class="form-control form-control-sm" style="width: 78px;"
                                                   title="درجة الطالب (0-100)">
                                            <button type="submit" class="btn btn-sm btn-success">✅ اعتماد</button>
                                        </form>
                                        <form method="POST" action="{{ route('school-admin.pending-submissions.reject', $s->id) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('رفض هذا التسليم؟ لن يُمنَح الطالب نقاطاً.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">❌ رفض</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <div class="fs-1 opacity-25">✅</div>
                                    لا توجد تقديمات معلّقة حاليّاً في مدرستك.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $submissions->links() }}
            </div>
        </div>
    </div>
@endsection
