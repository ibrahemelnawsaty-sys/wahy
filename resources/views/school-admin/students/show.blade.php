@extends('layouts.school-admin')

@section('page-title', 'تفاصيل الطالب — ' . $student->name)

@section('content')
<style>
    .student-show-page { max-width: 1100px; margin: 0 auto; padding: 24px; direction: rtl; }
    .student-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 6px 24px rgba(15,23,42,.06); margin-bottom: 22px; }
    .student-header { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
    .student-avatar { width: 92px; height: 92px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 36px; flex-shrink: 0; }
    .student-meta h1 { margin: 0 0 6px; font-size: 26px; font-weight: 800; color: #0f172a; }
    .student-meta p  { margin: 0; color: #64748b; font-size: 14px; }
    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-top: 22px; }
    .stat-tile { background: #f8fafc; border-right: 4px solid #6366f1; padding: 16px 18px; border-radius: 12px; }
    .stat-tile .v { font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1; }
    .stat-tile .l { font-size: 13px; color: #64748b; margin-top: 6px; }
    .recent-table { width: 100%; border-collapse: collapse; }
    .recent-table th, .recent-table td { padding: 10px 12px; text-align: start; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
    .recent-table th { background: #f8fafc; font-weight: 700; color: #0f172a; }
    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .status-completed { background: #ecfdf5; color: #047857; }
    .status-pending   { background: #fffbeb; color: #b45309; }
    .status-rejected  { background: #fef2f2; color: #b91c1c; }
    .actions-bar { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 22px; }
    .btn { padding: 11px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px; }
    .btn-edit    { background: #fbbf24; color: #92400e; }
    .btn-back    { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
</style>

<div class="student-show-page">
    <div class="student-card">
        <div class="student-header">
            <div class="student-avatar">{{ mb_substr($student->name, 0, 1) }}</div>
            <div class="student-meta">
                <h1>{{ $student->name }}</h1>
                <p>
                    @if($student->email) 📧 {{ $student->email }} @endif
                    @if($student->classrooms->isNotEmpty()) · 🏫 {{ $student->classrooms->pluck('name')->join('، ') }} @endif
                    @if($student->birth_date) · 🎂 {{ \Carbon\Carbon::parse($student->birth_date)->age }} سنة @endif
                </p>
                <p style="margin-top:6px;">
                    @if($student->status === 'active')
                        <span class="status-badge status-completed">نشط</span>
                    @else
                        <span class="status-badge status-pending">غير نشط</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-tile">
                <div class="v">{{ (int) ($student->total_points ?? 0) }}</div>
                <div class="l">إجمالي النقاط</div>
            </div>
            <div class="stat-tile">
                <div class="v">{{ $student->completed_count ?? 0 }}</div>
                <div class="l">أنشطة مكتملة</div>
            </div>
            <div class="stat-tile">
                <div class="v">{{ $student->activity_submissions_count ?? 0 }}</div>
                <div class="l">إجمالي التسليمات</div>
            </div>
            <div class="stat-tile">
                <div class="v">{{ $student->badges->count() }}</div>
                <div class="l">شارات</div>
            </div>
            <div class="stat-tile">
                <div class="v">{{ $student->streak->current_streak ?? 0 }}</div>
                <div class="l">سلسلة الإنجاز</div>
            </div>
        </div>

        <div class="actions-bar">
            <a href="{{ route('school-admin.students.edit', $student->id) }}" class="btn btn-edit">✏️ تعديل البيانات</a>
            <a href="{{ route('school-admin.students') }}" class="btn btn-back">← العودة للقائمة</a>
        </div>
    </div>

    <div class="student-card">
        <h2 style="margin:0 0 16px;font-size:18px;font-weight:800;color:#0f172a;">📋 آخر التسليمات</h2>
        @if($recentSubmissions->isEmpty())
            <p style="color:#94a3b8;text-align:center;padding:20px;">لا توجد تسليمات بعد.</p>
        @else
            <div style="overflow-x:auto;">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>النشاط</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>الدرجة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSubmissions as $s)
                            <tr>
                                <td>{{ optional($s->activity)->title ?? '—' }}</td>
                                <td>{{ optional($s->activity)->type ?? '—' }}</td>
                                <td>
                                    @if(in_array($s->status, ['completed', 'approved']))
                                        <span class="status-badge status-completed">{{ $s->status === 'approved' ? 'مُعتمد' : 'مكتمل' }}</span>
                                    @elseif(in_array($s->status, ['pending', 'needs_review']))
                                        <span class="status-badge status-pending">بانتظار المراجعة</span>
                                    @elseif($s->status === 'rejected')
                                        <span class="status-badge status-rejected">مرفوض</span>
                                    @else
                                        <span class="status-badge">{{ $s->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $s->score !== null ? $s->score . '%' : '—' }}</td>
                                <td>{{ $s->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
