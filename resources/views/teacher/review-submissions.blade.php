@extends('layouts.teacher')

@section('title', 'مراجعة الأنشطة')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/teacher-glass.css') }}?v={{ time() }}">
<style>
    .review-header { display: flex; align-items: center; gap: 16px; margin-bottom: 30px; }
    .review-header-icon { font-size: 48px; }
    .review-header-title { font-size: 28px; font-weight: 800; color: white; }
    .review-header-sub { color: rgba(255,255,255,0.7); font-size: 14px; margin-top: 4px; }

    .submissions-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .submissions-table thead th { background: rgba(255,255,255,0.15); color: white; font-weight: 700; padding: 14px 18px; text-align: right; font-size: 14px; }
    .submissions-table thead th:first-child { border-radius: 0 14px 14px 0; }
    .submissions-table thead th:last-child { border-radius: 14px 0 0 14px; }
    .submissions-table tbody td { padding: 14px 18px; color: white; border-bottom: 1px solid rgba(255,255,255,0.08); font-size: 14px; }
    .submissions-table tbody tr:hover { background: rgba(255,255,255,0.05); }

    .student-cell { display: flex; align-items: center; gap: 12px; }
    .student-avatar-sm { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
    .student-avatar-placeholder { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; }
    .student-name { font-weight: 600; }
    .student-email { font-size: 12px; color: rgba(255,255,255,0.5); }

    .value-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; background: rgba(139,92,246,0.2); color: #c4b5fd; font-size: 12px; font-weight: 600; }
    .review-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 10px; font-weight: 700; font-size: 13px; text-decoration: none; transition: transform 0.2s; }
    .review-btn:hover { transform: scale(1.05); }

    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 64px; margin-bottom: 14px; }
    .empty-title { font-size: 22px; font-weight: 700; color: white; }
    .empty-sub { color: rgba(255,255,255,0.5); margin-top: 8px; }

    .pagination-wrap { padding: 20px; display: flex; justify-content: center; }
</style>
@endpush

@section('content')
<div class="teacher-glass-container">
    <div class="review-header">
        <div class="review-header-icon">📋</div>
        <div>
            <h1 class="review-header-title">مراجعة الأنشطة</h1>
            <p class="review-header-sub">الأنشطة المعلقة التي تنتظر تقييمك</p>
        </div>
    </div>

    <div class="glass-card" style="overflow: hidden;">
        @if($submissions->count() > 0)
        <div style="overflow-x: auto;">
            <table class="submissions-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th>النشاط</th>
                        <th>القيمة</th>
                        <th>تاريخ التقديم</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $index => $submission)
                    <tr>
                        <td>{{ $submissions->firstItem() + $index }}</td>
                        <td>
                            <div class="student-cell">
                                @if($submission->student?->avatar)
                                    <img src="{{ $submission->student->avatar_url }}" class="student-avatar-sm">
                                @else
                                    <div class="student-avatar-placeholder">
                                        {{ mb_substr($submission->student?->name ?? '?', 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="student-name">{{ $submission->student?->name ?? 'غير معروف' }}</div>
                                    <div class="student-email">{{ $submission->student?->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="student-name">{{ $submission->activity?->title ?? 'نشاط محذوف' }}</div>
                            <div class="student-email">{{ $submission->activity?->lesson?->title ?? '-' }}</div>
                        </td>
                        <td>
                            @if($submission->activity?->lesson?->concept?->value)
                                <span class="value-badge">💎 {{ $submission->activity->lesson->concept->value->name }}</span>
                            @else
                                <span style="color: rgba(255,255,255,0.3);">-</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $submission->submitted_at?->format('Y/m/d') ?? $submission->created_at->format('Y/m/d') }}</div>
                            <div class="student-email">{{ $submission->submitted_at?->format('H:i') ?? $submission->created_at->format('H:i') }}</div>
                        </td>
                        <td>
                            <a href="{{ route('teacher.review.single', $submission->id) }}" class="review-btn">
                                👁️ مراجعة
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $submissions->links() }}
        </div>
        @else
        <div class="empty-state">
            <div class="empty-icon">✅</div>
            <h3 class="empty-title">لا توجد أنشطة معلقة!</h3>
            <p class="empty-sub">تم مراجعة جميع الأنشطة المقدمة من طلابك</p>
        </div>
        @endif
    </div>
</div>
@endsection
