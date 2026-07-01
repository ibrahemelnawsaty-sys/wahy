@extends('layouts.admin')

@section('title', 'تفاصيل الطالب')

@section('content')
<div class="student-detail">
    <div class="page-header">
        <h1>👨‍🎓 {{ $student->name }}</h1>
        <a href="{{ route('admin.reports.students') }}" class="btn btn-secondary">← العودة</a>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon">🏆</div><div><h3>{{ number_format($stats['total_points']) }}</h3><p>إجمالي النقاط</p></div></div>
        <div class="stat-card"><div class="stat-icon">📝</div><div><h3>{{ $stats['total_submissions'] }}</h3><p>المشاركات</p></div></div>
        <div class="stat-card"><div class="stat-icon">✅</div><div><h3>{{ $stats['completed_activities'] }}</h3><p>أنشطة مكتملة</p></div></div>
        <div class="stat-card"><div class="stat-icon">📊</div><div><h3>{{ number_format($stats['average_score'] ?? 0, 1) }}%</h3><p>متوسط الدرجات</p></div></div>
        <div class="stat-card"><div class="stat-icon">🎖️</div><div><h3>{{ $stats['total_badges'] }}</h3><p>الشارات</p></div></div>
        <div class="stat-card"><div class="stat-icon">🔥</div><div><h3>{{ $stats['current_streak'] }}</h3><p>أيام متتالية</p></div></div>
    </div>

    <!-- Recent Activities -->
    <div class="section-card">
        <h3>📚 آخر الأنشطة</h3>
        <div class="activities-list">
            @forelse($recentActivities as $submission)
            <div class="activity-item">
                <div class="activity-info">
                    <h4>{{ $submission->activity->title }}</h4>
                    <p>{{ $submission->created_at->diffForHumans() }}</p>
                </div>
                <span class="badge badge-{{ $submission->status }}">
                    @php
                        $statusAr = match($submission->status) {
                            'completed' => 'مكتمل',
                            'approved' => 'معتمد',
                            'pending' => 'قيد المراجعة',
                            'needs_review' => 'لم يجتَز',
                            'rejected' => 'مرفوض',
                            default => $submission->status
                        };
                    @endphp
                    {{ $statusAr }}
                </span>
                @if($submission->score)
                <span class="score">{{ $submission->score }}%</span>
                @endif
            </div>
            @empty
            <p class="empty-state">لا توجد أنشطة</p>
            @endforelse
        </div>
    </div>

    <!-- Progress by Value -->
    <div class="section-card">
        <h3>💎 التقدم حسب القيمة</h3>
        <div class="values-progress">
            @forelse($progressByValue as $value)
            <div class="value-progress-item">
                <span class="value-emoji">💎</span>
                <div class="value-info">
                    <h4>{{ $value->name }}</h4>
                    <p>{{ $value->activities_count }} نشاط مكتمل</p>
                </div>
            </div>
            @empty
            <p class="empty-state">لا يوجد تقدم بعد</p>
            @endforelse
        </div>
    </div>
</div>

<style>
.student-detail { padding: 20px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: white; border-radius: 12px; padding: 20px; display: flex; gap: 15px; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.stat-icon { font-size: 36px; }
.stat-card h3 { font-size: 28px; margin-bottom: 4px; }
.stat-card p { font-size: 13px; color: #64748b; }
.section-card { background: white; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.section-card h3 { font-size: 18px; margin-bottom: 20px; }
.activities-list, .values-progress { display: flex; flex-direction: column; gap: 12px; }
.activity-item { display: flex; align-items: center; justify-content: space-between; padding: 15px; background: #f8fafc; border-radius: 8px; }
.activity-info h4 { font-size: 15px; margin-bottom: 4px; }
.activity-info p { font-size: 12px; color: #64748b; }
.score { font-size: 18px; font-weight: 700; color: var(--color-primary); }
.value-progress-item { display: flex; align-items: center; gap: 15px; padding: 15px; background: #f8fafc; border-radius: 8px; }
.value-emoji { font-size: 32px; }
.value-info h4 { font-size: 15px; margin-bottom: 4px; }
.value-info p { font-size: 12px; color: #64748b; }
.empty-state { text-align: center; padding: 40px; color: #94a3b8; }
</style>
@endsection
