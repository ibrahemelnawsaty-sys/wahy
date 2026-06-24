@extends('layouts.admin')

@section('title', 'إحصائيات الرسائل')

@section('content')
<div class="admin-container">
    <!-- Header -->
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">📊 إحصائيات الرسائل</h1>
            <p class="admin-page-subtitle">تحليل شامل لنشاط الرسائل في النظام</p>
        </div>
        <div>
            <a href="{{ route('admin.messages-log.index') }}" class="btn btn-primary">
                ← العودة للسجل
            </a>
        </div>
    </div>

    <!-- General Stats -->
    <div class="admin-stats-grid" style="margin-bottom: 30px;">
        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                💬
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ number_format($generalStats['total_messages']) }}</div>
                <div class="admin-stat-label">إجمالي الرسائل</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                👥
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ number_format($generalStats['total_conversations']) }}</div>
                <div class="admin-stat-label">المحادثات</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                👤
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ number_format($generalStats['total_users_messaging']) }}</div>
                <div class="admin-stat-label">مستخدمون نشطون</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                📈
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $generalStats['avg_messages_per_conversation'] }}</div>
                <div class="admin-stat-label">متوسط الرسائل/محادثة</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                ✓
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $readPercentage }}%</div>
                <div class="admin-stat-label">معدل القراءة</div>
            </div>
        </div>
    </div>

    <!-- Messages Per Day Chart -->
    <div class="admin-card" style="margin-bottom: 20px;">
        <div class="admin-card-header">
            <h3>📅 الرسائل خلال آخر 30 يوم</h3>
        </div>
        <div class="admin-card-body">
            <canvas id="messagesPerDayChart" style="max-height: 400px;"></canvas>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <!-- Messages By Role -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>👥 الرسائل حسب الدور</h3>
            </div>
            <div class="admin-card-body">
                <canvas id="messagesByRoleChart"></canvas>
            </div>
        </div>

        <!-- Read Rate Pie -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>📖 معدل القراءة</h3>
            </div>
            <div class="admin-card-body">
                <canvas id="readRateChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Senders -->
    <div class="admin-card" style="margin-bottom: 20px;">
        <div class="admin-card-header">
            <h3>🏆 أكثر المستخدمين إرسالاً للرسائل</h3>
        </div>
        <div class="admin-card-body">
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">الترتيب</th>
                            <th>المستخدم</th>
                            <th>الدور</th>
                            <th style="width: 150px;">عدد الرسائل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topSenders as $index => $sender)
                            <tr>
                                <td>
                                    <div style="font-size: 24px; text-align: center;">
                                        @if($index === 0) 🥇
                                        @elseif($index === 1) 🥈
                                        @elseif($index === 2) 🥉
                                        @else {{ $index + 1 }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                            {{ mb_substr($sender->sender->name ?? 'غ', 0, 1) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;">{{ $sender->sender->name ?? 'غير معروف' }}</div>
                                            <div style="font-size: 12px; color: #666;">{{ $sender->sender->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="background: #667eea; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                        {{ $sender->sender->role ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 20px; font-weight: bold; color: #667eea;">
                                        {{ number_format($sender->message_count) }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Receivers -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>📬 أكثر المستخدمين استقبالاً للرسائل</h3>
        </div>
        <div class="admin-card-body">
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">الترتيب</th>
                            <th>المستخدم</th>
                            <th>الدور</th>
                            <th style="width: 150px;">عدد الرسائل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topReceivers as $index => $receiver)
                            <tr>
                                <td>
                                    <div style="font-size: 24px; text-align: center;">
                                        @if($index === 0) 🥇
                                        @elseif($index === 1) 🥈
                                        @elseif($index === 2) 🥉
                                        @else {{ $index + 1 }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                            {{ mb_substr($receiver->receiver->name ?? 'غ', 0, 1) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;">{{ $receiver->receiver->name ?? 'غير معروف' }}</div>
                                            <div style="font-size: 12px; color: #666;">{{ $receiver->receiver->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="background: #f093fb; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                        {{ $receiver->receiver->role ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 20px; font-weight: bold; color: #f093fb;">
                                        {{ number_format($receiver->message_count) }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Messages Per Day Chart
const messagesPerDayCtx = document.getElementById('messagesPerDayChart').getContext('2d');
new Chart(messagesPerDayCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($messagesPerDay->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->locale('ar')->translatedFormat('d M'))) !!},
        datasets: [{
            label: 'عدد الرسائل',
            data: {!! json_encode($messagesPerDay->pluck('count')) !!},
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Messages By Role Chart
const messagesByRoleCtx = document.getElementById('messagesByRoleChart').getContext('2d');
new Chart(messagesByRoleCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($messagesByRole->pluck('role')) !!},
        datasets: [{
            data: {!! json_encode($messagesByRole->pluck('count')) !!},
            backgroundColor: [
                '#667eea',
                '#f093fb',
                '#4facfe',
                '#43e97b',
                '#fa709a',
                '#30cfd0'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});

// Read Rate Chart
const readRateCtx = document.getElementById('readRateChart').getContext('2d');
new Chart(readRateCtx, {
    type: 'doughnut',
    data: {
        labels: ['مقروءة', 'غير مقروءة'],
        datasets: [{
            data: [{{ $readPercentage }}, {{ 100 - $readPercentage }}],
            backgroundColor: ['#43e97b', '#fa709a']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});
</script>
@endsection
