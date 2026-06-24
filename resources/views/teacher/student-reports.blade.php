@extends('layouts.teacher')

@section('title', 'تقارير الطلاب')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/teacher-glass.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="teacher-glass-container">
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">📊</div>
            <div>
                <h1 class="header-title">تقارير الطلاب</h1>
                <p class="header-subtitle">{{ $students->count() }} طالب في فصولي</p>
            </div>
        </div>
        
        <!-- Filter -->
        <div class="header-actions">
            <select id="classroomFilter" class="filter-select">
                <option value="">جميع الفصول</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->name }}">{{ $classroom->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card glass-card">
            <div class="summary-icon">👥</div>
            <div class="summary-value">{{ $students->count() }}</div>
            <div class="summary-label">إجمالي الطلاب</div>
        </div>
        <div class="summary-card glass-card">
            <div class="summary-icon">⭐</div>
            <div class="summary-value">{{ number_format($students->avg('total_xp'), 0) }}</div>
            <div class="summary-label">متوسط XP</div>
        </div>
        <div class="summary-card glass-card">
            <div class="summary-icon">📈</div>
            <div class="summary-value">{{ number_format($students->avg('average_score'), 1) }}%</div>
            <div class="summary-label">متوسط الدرجات</div>
        </div>
        <div class="summary-card glass-card">
            <div class="summary-icon">✅</div>
            <div class="summary-value">{{ number_format($students->sum('completed_activities')) }}</div>
            <div class="summary-label">الأنشطة المكتملة</div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="table-container glass-card">
        <table class="students-table">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>الفصل</th>
                    <th>XP</th>
                    <th>المستوى</th>
                    <th>العملات</th>
                    <th>الأنشطة</th>
                    <th>المتوسط</th>
                    <th>الاستمرارية</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody id="studentsTableBody">
                @foreach($students as $student)
                    <tr data-classroom="{{ $student->classroom_name }}">
                        <td>
                            <div class="student-cell">
                                <div class="student-avatar-small">
                                    <img src="{{ $student->avatar_url }}" alt="{{ $student->name }}"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                    <div class="avatar-placeholder-small" style="display:none;">{{ mb_substr($student->name, 0, 1, "UTF-8") }}</div>
                                </div>
                                <span class="student-name-cell">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td><span class="classroom-badge">{{ $student->classroom_name }}</span></td>
                        <td><span class="xp-badge">{{ number_format($student->total_xp) }} XP</span></td>
                        <td><span class="level-badge">المستوى {{ floor($student->total_xp / 100) + 1 }}</span></td>
                        <td><span class="coins-badge">🪙 {{ number_format($student->total_coins) }}</span></td>
                        <td><span class="activities-count">{{ $student->completed_activities }}</span></td>
                        <td>
                            <div class="score-cell">
                                <div class="score-bar">
                                    <div class="score-fill" style="width: {{ $student->average_score ?? 0 }}%"></div>
                                </div>
                                <span class="score-text">{{ number_format($student->average_score ?? 0, 1) }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="streak-badge {{ $student->streak_days > 0 ? 'active' : '' }}">
                                🔥 {{ $student->streak_days }} يوم
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('teacher.students.detail', $student->id) }}" class="view-btn">
                                عرض التفاصيل
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<script>
// Filter students by classroom
document.getElementById('classroomFilter').addEventListener('change', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#studentsTableBody tr');
    
    rows.forEach(row => {
        const classroom = row.dataset.classroom.toLowerCase();
        if (filter === '' || classroom === filter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endsection
