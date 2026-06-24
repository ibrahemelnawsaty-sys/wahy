@extends('layouts.admin')

@section('title', 'تفاصيل المدرسة')

@section('content')
<div class="school-detail">
    <div class="page-header">
        <h1>🏫 {{ $school->name }}</h1>
        <a href="{{ route('admin.reports.schools') }}" class="btn btn-secondary">← العودة</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon">👨‍🎓</div><div><h3>{{ $stats['total_students'] }}</h3><p>الطلاب</p></div></div>
        <div class="stat-card"><div class="stat-icon">👨‍🏫</div><div><h3>{{ $stats['total_teachers'] }}</h3><p>المعلمين</p></div></div>
        <div class="stat-card"><div class="stat-icon">🏢</div><div><h3>{{ $stats['total_branches'] }}</h3><p>الفروع</p></div></div>
        <div class="stat-card"><div class="stat-icon">✅</div><div><h3>{{ $stats['active_students'] }}</h3><p>طلاب نشطين</p></div></div>
        <div class="stat-card"><div class="stat-icon">🏆</div><div><h3>{{ number_format($stats['total_points']) }}</h3><p>إجمالي النقاط</p></div></div>
    </div>

    <div class="section-card">
        <h3>🏆 أفضل الطلاب</h3>
        <div class="ranking-list">
            @forelse($topStudents as $student)
            <div class="ranking-item">
                <span>{{ $student->name }}</span>
                <strong>{{ number_format($student->total_points ?? 0) }} نقطة</strong>
            </div>
            @empty
            <p class="empty-state">لا يوجد طلاب</p>
            @endforelse
        </div>
    </div>

    <div class="section-card">
        <h3>👨‍🏫 المعلمين</h3>
        <div class="teachers-list">
            @forelse($teachers as $teacher)
            <div class="teacher-item">
                <span>{{ $teacher->name }}</span>
                <span class="badge badge-info">{{ $teacher->email }}</span>
            </div>
            @empty
            <p class="empty-state">لا يوجد معلمين</p>
            @endforelse
        </div>
    </div>
</div>

<style>
.school-detail { padding: 20px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: white; border-radius: 12px; padding: 20px; display: flex; gap: 15px; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.stat-icon { font-size: 36px; }
.section-card { background: white; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.section-card h3 { font-size: 18px; margin-bottom: 20px; }
.ranking-list, .teachers-list { display: flex; flex-direction: column; gap: 12px; }
.ranking-item, .teacher-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8fafc; border-radius: 8px; }
.empty-state { text-align: center; padding: 40px; color: #94a3b8; }
.badge-info { background: #e0e7ff; color: #3730a3; padding: 6px 12px; border-radius: 6px; font-size: 12px; }
</style>
@endsection
