@extends('layouts.admin')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@section('content')
    <!-- Today Stats Banner + Alerts -->
    <div class="dashboard-alerts-row" style="display: grid; grid-template-columns: 1fr auto; gap: 16px; margin-bottom: 24px;">
        @if(isset($today_stats))
        <div class="p-4 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl text-white">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-6 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📅</span>
                        <span class="font-bold">إحصائيات اليوم:</span>
                    </div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <span>👥 {{ $today_stats['new_users'] }} مستخدم جديد</span>
                        <span>📝 {{ $today_stats['new_submissions'] }} تقديم جديد</span>
                    </div>
                </div>
                @if(isset($stats['pending_submissions']) && $stats['pending_submissions'] > 0)
                <a href="{{ route('admin.pending-submissions') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition">
                    ⚠️ {{ $stats['pending_submissions'] }} تقديم بانتظار المراجعة
                </a>
                @endif
            </div>
        </div>
        @endif
        
        <!-- Unread Messages Alert -->
        @if(isset($unread_messages_count) && $unread_messages_count > 0)
        <a href="{{ route('admin.messages-log.index') }}" class="p-4 bg-gradient-to-r from-pink-500 to-rose-500 rounded-xl text-white flex items-center gap-3 hover:shadow-lg transition" style="min-width: 200px;">
            <span class="text-2xl">📨</span>
            <div>
                <div class="font-bold">{{ $unread_messages_count }}</div>
                <div class="text-sm opacity-90">رسالة غير مقروءة</div>
            </div>
        </a>
        @endif
    </div>

    <!-- Growth Comparison Cards -->
    @if(isset($growth_stats))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="p-4 bg-white rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">المستخدمون الجدد هذا الشهر</div>
                    <div class="text-2xl font-bold">{{ $growth_stats['users_current'] }}</div>
                    <div class="text-xs text-gray-400">الشهر السابق: {{ $growth_stats['users_last'] }}</div>
                </div>
                <div class="text-right">
                    @if($growth_stats['users_growth'] > 0)
                        <span class="text-green-500 text-lg font-bold">↑ {{ $growth_stats['users_growth'] }}%</span>
                    @elseif($growth_stats['users_growth'] < 0)
                        <span class="text-red-500 text-lg font-bold">↓ {{ abs($growth_stats['users_growth']) }}%</span>
                    @else
                        <span class="text-gray-500 text-lg font-bold">— 0%</span>
                    @endif
                    <div class="text-xs text-gray-400">مقارنة بالشهر السابق</div>
                </div>
            </div>
        </div>
        
        <div class="p-4 bg-white rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">التقديمات هذا الشهر</div>
                    <div class="text-2xl font-bold">{{ $growth_stats['submissions_current'] }}</div>
                    <div class="text-xs text-gray-400">الشهر السابق: {{ $growth_stats['submissions_last'] }}</div>
                </div>
                <div class="text-right">
                    @if($growth_stats['submissions_growth'] > 0)
                        <span class="text-green-500 text-lg font-bold">↑ {{ $growth_stats['submissions_growth'] }}%</span>
                    @elseif($growth_stats['submissions_growth'] < 0)
                        <span class="text-red-500 text-lg font-bold">↓ {{ abs($growth_stats['submissions_growth']) }}%</span>
                    @else
                        <span class="text-gray-500 text-lg font-bold">— 0%</span>
                    @endif
                    <div class="text-xs text-gray-400">مقارنة بالشهر السابق</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-icon primary">
                👥
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">إجمالي المستخدمين</div>
                <div class="admin-stat-value">{{ number_format($stats['total_users']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon secondary">
                🏫
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">المدارس</div>
                <div class="admin-stat-value">{{ number_format($stats['total_schools']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon success">
                👨‍🏫
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">المعلمين</div>
                <div class="admin-stat-value">{{ number_format($stats['total_teachers']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon warning">
                🎓
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">الطلاب</div>
                <div class="admin-stat-value">{{ number_format($stats['total_students']) }}</div>
                @if(isset($stats['active_students']))
                <div class="text-xs text-green-600 mt-1">{{ $stats['active_students'] }} نشط</div>
                @endif
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon info">
                👨‍👩‍👧‍👦
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">أولياء الأمور</div>
                <div class="admin-stat-value">{{ number_format($stats['total_parents']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon primary">
                📚
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">الدروس</div>
                <div class="admin-stat-value">{{ number_format($stats['total_lessons']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon success">
                🎯
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">الأنشطة</div>
                <div class="admin-stat-value">{{ number_format($stats['total_activities']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon accent">
                💎
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">القيم</div>
                <div class="admin-stat-value">{{ number_format($values_count ?? 0) }}</div>
            </div>
        </div>
    </div>
    
    <!-- System Tools Stats -->
    @if(isset($question_stats))
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <a href="{{ route('admin.question-bank.index') }}" class="block p-4 bg-white rounded-xl shadow-sm border hover:shadow-md transition">
            <div class="flex items-center gap-3">
                <span class="text-3xl">❓</span>
                <div>
                    <div class="text-sm text-gray-500">بنك الأسئلة</div>
                    <div class="font-bold text-lg">{{ $question_stats['total'] }} سؤال</div>
                    @if($question_stats['pending'] > 0)
                    <div class="text-xs text-yellow-600">{{ $question_stats['pending'] }} بانتظار الموافقة</div>
                    @endif
                </div>
            </div>
        </a>
        
        <a href="{{ route('admin.backups') }}" class="block p-4 bg-white rounded-xl shadow-sm border hover:shadow-md transition">
            <div class="flex items-center gap-3">
                <span class="text-3xl">💾</span>
                <div>
                    <div class="text-sm text-gray-500">النسخ الاحتياطي</div>
                    <div class="font-bold text-lg">إدارة النسخ</div>
                    <div class="text-xs text-blue-600">حماية البيانات</div>
                </div>
            </div>
        </a>
        
        <a href="{{ route('admin.activity-logs') }}" class="block p-4 bg-white rounded-xl shadow-sm border hover:shadow-md transition">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📋</span>
                <div>
                    <div class="text-sm text-gray-500">سجل الأنشطة</div>
                    <div class="font-bold text-lg">مراقبة النظام</div>
                    <div class="text-xs text-green-600">تتبع التغييرات</div>
                </div>
            </div>
        </a>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">⚡ إجراءات سريعة</h3>
        </div>
        <div class="admin-card-body">
            <div class="admin-actions-grid">
                <a href="{{ route('admin.theme') }}" class="admin-action-btn gradient-purple">
                    <span class="admin-action-icon">🎨</span>
                    <span class="admin-action-text">تخصيص الثيم</span>
                </a>
                <a href="{{ route('admin.pages.index') }}" class="admin-action-btn gradient-green">
                    <span class="admin-action-icon">📄</span>
                    <span class="admin-action-text">بناء صفحة جديدة</span>
                </a>
                <a href="{{ route('admin.landing-page') }}" class="admin-action-btn gradient-blue">
                    <span class="admin-action-icon">🏠</span>
                    <span class="admin-action-text">تحرير الصفحة الرئيسية</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="admin-action-btn gradient-orange">
                    <span class="admin-action-icon">⚙️</span>
                    <span class="admin-action-text">الإعدادات العامة</span>
                </a>
            </div>
        </div>
    </div>
    <!-- Charts Section -->
    @if(isset($users_chart_data) && isset($submissions_chart_data))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 mb-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">📈 المستخدمون الجدد (آخر 7 أيام)</h3>
            </div>
            <div class="admin-card-body">
                <canvas id="usersChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">📊 التقديمات (آخر 7 أيام)</h3>
            </div>
            <div class="admin-card-body">
                <canvas id="submissionsChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Reviews Section -->
    @if(isset($pending_reviews) && $pending_reviews->count() > 0)
    <div class="admin-card mt-6 mb-6">
        <div class="admin-card-header">
            <h3 class="admin-card-title">⏳ تقديمات بانتظار المراجعة</h3>
            <a href="{{ route('admin.reports.dashboard') }}" class="admin-btn admin-btn-outline" style="padding: 8px 16px; font-size: 12px;">
                عرض الكل
            </a>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #64748b;">الطالب</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #64748b;">النشاط</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #64748b;">التاريخ</th>
                            <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #64748b;">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pending_reviews as $review)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 16px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px;">
                                        {{ mb_substr($review->student->name ?? 'غ', 0, 1) }}
                                    </div>
                                    <span style="font-weight: 500;">{{ $review->student->name ?? 'غير معروف' }}</span>
                                </div>
                            </td>
                            <td style="padding: 12px 16px; color: #64748b;">{{ $review->activity->title ?? 'غير معروف' }}</td>
                            <td style="padding: 12px 16px; color: #64748b; font-size: 13px;">{{ $review->created_at->diffForHumans() }}</td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <a href="{{ route('admin.reports.dashboard') }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 16px; border-radius: 6px; font-size: 12px; text-decoration: none; display: inline-block;">
                                    مراجعة
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="dashboard-grid-responsive" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">
        <!-- Recent Users -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">آخر المستخدمين</h3>
                <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-outline" style="padding: 8px 16px; font-size: 12px;">
                    عرض الكل
                </a>
            </div>
            <div class="admin-card-body">
                @if($recent_users->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($recent_users as $user)
                            <div style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; background: #f8fafc;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #3CCB8A; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                    {{ mb_substr($user->name, 0, 1, "UTF-8") }}
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: #1e293b;">{{ $user->name }}</div>
                                    <div style="font-size: 12px; color: #64748b;">{{ $user->email }}</div>
                                </div>
                                <div style="font-size: 12px; color: #64748b;">
                                    {{ $user->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 40px; color: #64748b;">
                        لا توجد بيانات حاليًا
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Schools -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">آخر المدارس</h3>
                <a href="{{ route('admin.schools.index') }}" class="admin-btn admin-btn-outline" style="padding: 8px 16px; font-size: 12px;">
                    عرض الكل
                </a>
            </div>
            <div class="admin-card-body">
                @if($recent_schools->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($recent_schools as $school)
                            <div style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; background: #f8fafc;">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                    🏫
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: #1e293b;">{{ $school->name }}</div>
                                    <div style="font-size: 12px; color: #64748b;">{{ $school->city ?? 'غير محدد' }}</div>
                                </div>
                                <div style="font-size: 12px; color: #64748b;">
                                    {{ $school->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 40px; color: #64748b;">
                        لا توجد بيانات حاليًا
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Leaderboard Section -->
    <div class="dashboard-grid-responsive" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-top: 24px;">
        <!-- Top Students -->
        @if(isset($top_students) && $top_students->count() > 0)
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">🏆 أفضل الطلاب</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($top_students as $index => $student)
                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; background: {{ $index === 0 ? 'linear-gradient(135deg, #fef3c7 0%, #fde68a 100%)' : '#f8fafc' }}; {{ $index === 0 ? 'border: 2px solid #f59e0b;' : '' }}">
                            <div style="font-size: 24px; min-width: 32px; text-align: center;">
                                @if($index === 0) 🥇
                                @elseif($index === 1) 🥈
                                @elseif($index === 2) 🥉
                                @else {{ $index + 1 }}
                                @endif
                            </div>
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                {{ mb_substr($student->name, 0, 1, "UTF-8") }}
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #1e293b;">{{ $student->name }}</div>
                                <div style="font-size: 12px; color: #64748b;">{{ $student->school->name ?? 'غير محدد' }}</div>
                            </div>
                            <div style="text-align: left;">
                                <div style="font-weight: 700; color: #667eea; font-size: 18px;">{{ number_format($student->points_sum_points ?? 0) }}</div>
                                <div style="font-size: 11px; color: #64748b;">نقطة</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Top Schools -->
        @if(isset($top_schools) && $top_schools->count() > 0)
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">🏫 أكثر المدارس نشاطاً</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($top_schools as $index => $school)
                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; background: {{ $index === 0 ? 'linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%)' : '#f8fafc' }}; {{ $index === 0 ? 'border: 2px solid #3b82f6;' : '' }}">
                            <div style="font-size: 24px; min-width: 32px; text-align: center;">
                                @if($index === 0) 🥇
                                @elseif($index === 1) 🥈
                                @elseif($index === 2) 🥉
                                @else {{ $index + 1 }}
                                @endif
                            </div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                🏫
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #1e293b;">{{ $school->name }}</div>
                                <div style="font-size: 12px; color: #64748b;">{{ $school->city ?? 'غير محدد' }}</div>
                            </div>
                            <div style="text-align: left;">
                                <div style="font-weight: 700; color: #3b82f6; font-size: 18px;">{{ number_format($school->users_count ?? 0) }}</div>
                                <div style="font-size: 11px; color: #64748b;">طالب</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Users Chart
    @if(isset($users_chart_data))
    const usersCtx = document.getElementById('usersChart');
    if (usersCtx) {
        new Chart(usersCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode(collect($users_chart_data)->pluck('date')) !!},
                datasets: [{
                    label: 'مستخدمون جدد',
                    data: {!! json_encode(collect($users_chart_data)->pluck('count')) !!},
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }
    @endif

    // Submissions Chart
    @if(isset($submissions_chart_data))
    const submissionsCtx = document.getElementById('submissionsChart');
    if (submissionsCtx) {
        new Chart(submissionsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($submissions_chart_data)->pluck('date')) !!},
                datasets: [{
                    label: 'تقديمات',
                    data: {!! json_encode(collect($submissions_chart_data)->pluck('count')) !!},
                    backgroundColor: 'rgba(60, 203, 138, 0.8)',
                    borderColor: '#3CCB8A',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }
    @endif
});
</script>
@endpush

@push('styles')
<style>
/* Responsive Fixes for Dashboard */
@media (max-width: 768px) {
    .dashboard-alerts-row {
        grid-template-columns: 1fr !important;
    }
    
    .dashboard-grid-responsive {
        grid-template-columns: 1fr !important;
    }
    
    .admin-stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media (max-width: 480px) {
    .admin-stats-grid {
        grid-template-columns: 1fr !important;
    }
}

/* Icon colors */
.admin-stat-icon.info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.admin-stat-icon.accent {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
</style>
@endpush
