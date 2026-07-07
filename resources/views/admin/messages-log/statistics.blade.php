@extends('layouts.admin')

@section('title', 'إحصائيات الرسائل')

@push('styles')
<style>
/* ===== Wahy — إحصائيات الرسائل (سوبر أدمن) — طبقة بصرية فاخرة =====
   كل الأسطح مبنيّة على متغيّرات النظام الموحّد (--w-*) المعرّفة للوضعين (light/dark)
   في partials/theme-toggle، فتعمل التغطية اللونية تلقائياً في الوضعين.
   ملاحظة: ألوان محاور/شبكة مخططات Chart.js تُضبط تلقائياً للوضع الليلي عبر
   partials/dark-coverage — هنا نُعتّم حاويات/بطاقات المخطط فقط عبر المتغيّرات. */
.mlog-stats {
    --mlog-grad: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --mlog-accent: #667eea;   /* accent للمرسِلين */
    --mlog-accent-2: #f5576c; /* accent للمستقبِلين */
}
html[data-theme="dark"] .mlog-stats {
    --mlog-accent: #a5b4fc;
    --mlog-accent-2: #f9a8d4;
}

/* ===== الهيدر ===== */
.mlog-head {
    display: flex; justify-content: space-between; align-items: flex-end;
    gap: 16px; flex-wrap: wrap;
    margin-bottom: 26px; padding-bottom: 20px;
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
}
.mlog-head-title {
    font-size: 26px; font-weight: 800; margin: 0 0 6px;
    color: var(--w-text, #0f172a);
    display: flex; align-items: center; gap: 10px;
}
.mlog-head-sub { font-size: 14px; color: var(--w-text-muted, #475569); margin: 0; }
.mlog-back-btn {
    background: var(--mlog-grad); color: #fff;
    padding: 11px 20px; border-radius: 12px; text-decoration: none;
    font-weight: 700; font-size: 14px; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 6px 18px rgba(102,126,234,0.35);
    transition: transform 0.2s, box-shadow 0.2s;
}
.mlog-back-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(102,126,234,0.45); }
.mlog-back-btn:active { transform: translateY(0); }

/* ===== شبكة بطاقات الإحصاء ===== */
.mlog-stats-grid {
    display: grid; gap: 18px; margin-bottom: 28px;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}
.mlog-stat {
    display: flex; align-items: center; gap: 16px;
    background: var(--w-card, #fff);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 18px; padding: 20px;
    box-shadow: var(--w-shadow, 0 10px 40px rgba(2,6,23,0.08));
    transition: transform 0.18s, box-shadow 0.2s;
}
.mlog-stat:hover { transform: translateY(-3px); box-shadow: 0 16px 44px rgba(2,6,23,0.12); }
.mlog-stat-ic {
    flex-shrink: 0; width: 58px; height: 58px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; color: #fff; line-height: 1;
    box-shadow: 0 8px 20px rgba(102,126,234,0.28);
}
.mlog-stat-val { font-size: 26px; font-weight: 800; line-height: 1.1; color: var(--w-text, #0f172a); }
.mlog-stat-lbl { font-size: 13px; color: var(--w-text-muted, #475569); margin-top: 4px; }

/* ===== البطاقات (حاويات المخططات/الجداول) ===== */
.mlog-card {
    background: var(--w-card, #fff);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 18px; overflow: hidden; margin-bottom: 22px;
    box-shadow: var(--w-shadow, 0 10px 40px rgba(2,6,23,0.08));
}
.mlog-card-head {
    padding: 18px 22px;
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
}
.mlog-card-head h3 {
    margin: 0; font-size: 17px; font-weight: 700;
    color: var(--w-text, #0f172a);
    display: flex; align-items: center; gap: 8px;
}
.mlog-card-body { padding: 22px; }

/* ===== شبكة المخططات الجانبية + حاوية المخطط ===== */
.mlog-charts-2 {
    display: grid; gap: 22px; margin-bottom: 22px;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
}
.mlog-charts-2 .mlog-card { margin-bottom: 0; }
/* المخططات الدائريّة تُحاط بحاوية مقيّدة العرض لتبقى نِسَبها متجاوبة دون تمدّد */
.mlog-chart-box { max-width: 340px; margin: 0 auto; }

/* ===== الجداول ===== */
.mlog-table-wrap { overflow-x: auto; }
.mlog-table { width: 100%; border-collapse: collapse; min-width: 520px; }
.mlog-table th {
    text-align: start; padding: 14px 16px; white-space: nowrap;
    font-size: 13px; font-weight: 700;
    color: var(--w-text-muted, #475569);
    background: rgba(102,126,234,0.07);
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
}
.mlog-table td {
    padding: 14px 16px; vertical-align: middle;
    color: var(--w-text, #0f172a);
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
}
.mlog-table tbody tr { transition: background 0.15s; }
.mlog-table tbody tr:hover { background: rgba(102,126,234,0.05); }
.mlog-table tbody tr:last-child td { border-bottom: none; }

.mlog-rank { font-size: 24px; text-align: center; }
.mlog-user { display: flex; align-items: center; gap: 12px; }
.mlog-avatar {
    flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 800;
    box-shadow: 0 6px 16px rgba(102,126,234,0.3);
}
.mlog-user-name { font-weight: 700; color: var(--w-text, #0f172a); }
.mlog-user-email { font-size: 12px; color: var(--w-text-muted, #475569); }
.mlog-role {
    display: inline-block; color: #fff;
    padding: 4px 12px; border-radius: 999px;
    font-size: 12px; font-weight: 700;
}
.mlog-count { font-size: 20px; font-weight: 800; }
.mlog-count.sender { color: var(--mlog-accent); }
.mlog-count.receiver { color: var(--mlog-accent-2); }

/* ===== الاستجابة ===== */
@media (max-width: 1024px) {
    .mlog-stats-grid { gap: 14px; }
    .mlog-card-body { padding: 18px; }
}
@media (max-width: 640px) {
    .mlog-head { align-items: flex-start; margin-bottom: 20px; padding-bottom: 16px; }
    .mlog-head-title { font-size: 22px; }
    .mlog-back-btn { width: 100%; justify-content: center; }
    .mlog-stats-grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .mlog-stat { padding: 16px; gap: 12px; }
    .mlog-stat-ic { width: 48px; height: 48px; font-size: 22px; border-radius: 14px; }
    .mlog-stat-val { font-size: 22px; }
    .mlog-card-head { padding: 16px; }
    .mlog-card-body { padding: 14px; }
    .mlog-charts-2 { grid-template-columns: 1fr; gap: 16px; }
}
</style>
@endpush

@section('content')
<div class="admin-container mlog-stats">
    <!-- Header -->
    <div class="mlog-head">
        <div>
            <h1 class="mlog-head-title">📊 إحصائيات الرسائل</h1>
            <p class="mlog-head-sub">تحليل شامل لنشاط الرسائل في النظام</p>
        </div>
        <a href="{{ route('admin.messages-log.index') }}" class="mlog-back-btn">
            ← العودة للسجل
        </a>
    </div>

    <!-- General Stats -->
    <div class="mlog-stats-grid">
        <div class="mlog-stat">
            <div class="mlog-stat-ic" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">💬</div>
            <div>
                <div class="mlog-stat-val">{{ number_format($generalStats['total_messages']) }}</div>
                <div class="mlog-stat-lbl">إجمالي الرسائل</div>
            </div>
        </div>

        <div class="mlog-stat">
            <div class="mlog-stat-ic" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">👥</div>
            <div>
                <div class="mlog-stat-val">{{ number_format($generalStats['total_conversations']) }}</div>
                <div class="mlog-stat-lbl">المحادثات</div>
            </div>
        </div>

        <div class="mlog-stat">
            <div class="mlog-stat-ic" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">👤</div>
            <div>
                <div class="mlog-stat-val">{{ number_format($generalStats['total_users_messaging']) }}</div>
                <div class="mlog-stat-lbl">مستخدمون نشطون</div>
            </div>
        </div>

        <div class="mlog-stat">
            <div class="mlog-stat-ic" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">📈</div>
            <div>
                <div class="mlog-stat-val">{{ $generalStats['avg_messages_per_conversation'] }}</div>
                <div class="mlog-stat-lbl">متوسط الرسائل/محادثة</div>
            </div>
        </div>

        <div class="mlog-stat">
            <div class="mlog-stat-ic" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">✓</div>
            <div>
                <div class="mlog-stat-val">{{ $readPercentage }}%</div>
                <div class="mlog-stat-lbl">معدل القراءة</div>
            </div>
        </div>
    </div>

    <!-- Messages Per Day Chart -->
    <div class="mlog-card">
        <div class="mlog-card-head">
            <h3>📅 الرسائل خلال آخر 30 يوم</h3>
        </div>
        <div class="mlog-card-body">
            <canvas id="messagesPerDayChart" style="max-height: 400px;"></canvas>
        </div>
    </div>

    <div class="mlog-charts-2">
        <!-- Messages By Role -->
        <div class="mlog-card">
            <div class="mlog-card-head">
                <h3>👥 الرسائل حسب الدور</h3>
            </div>
            <div class="mlog-card-body">
                <div class="mlog-chart-box">
                    <canvas id="messagesByRoleChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Read Rate Pie -->
        <div class="mlog-card">
            <div class="mlog-card-head">
                <h3>📖 معدل القراءة</h3>
            </div>
            <div class="mlog-card-body">
                <div class="mlog-chart-box">
                    <canvas id="readRateChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Senders -->
    <div class="mlog-card">
        <div class="mlog-card-head">
            <h3>🏆 أكثر المستخدمين إرسالاً للرسائل</h3>
        </div>
        <div class="mlog-card-body">
            <div class="mlog-table-wrap">
                <table class="mlog-table">
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
                                    <div class="mlog-rank">
                                        @if($index === 0) 🥇
                                        @elseif($index === 1) 🥈
                                        @elseif($index === 2) 🥉
                                        @else {{ $index + 1 }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="mlog-user">
                                        <div class="mlog-avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            {{ mb_substr($sender->sender->name ?? 'غ', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="mlog-user-name">{{ $sender->sender->name ?? 'غير معروف' }}</div>
                                            <div class="mlog-user-email">{{ $sender->sender->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="mlog-role" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        {{ $sender->sender->role ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="mlog-count sender">
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
    <div class="mlog-card">
        <div class="mlog-card-head">
            <h3>📬 أكثر المستخدمين استقبالاً للرسائل</h3>
        </div>
        <div class="mlog-card-body">
            <div class="mlog-table-wrap">
                <table class="mlog-table">
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
                                    <div class="mlog-rank">
                                        @if($index === 0) 🥇
                                        @elseif($index === 1) 🥈
                                        @elseif($index === 2) 🥉
                                        @else {{ $index + 1 }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="mlog-user">
                                        <div class="mlog-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                            {{ mb_substr($receiver->receiver->name ?? 'غ', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="mlog-user-name">{{ $receiver->receiver->name ?? 'غير معروف' }}</div>
                                            <div class="mlog-user-email">{{ $receiver->receiver->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="mlog-role" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                        {{ $receiver->receiver->role ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="mlog-count receiver">
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
