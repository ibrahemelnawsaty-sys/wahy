<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تقرير تقدم الطالب</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0 0 5px 0;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .info-box {
            background: #F3F4F6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-label {
            font-weight: bold;
            color: #4B5563;
        }
        .info-value {
            color: #111827;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background: #4F46E5;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #4F46E5;
            margin: 5px 0;
        }
        .stat-label {
            font-size: 12px;
            color: #6B7280;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #E5E7EB;
            padding: 10px;
            text-align: center;
            font-size: 13px;
        }
        table th {
            background: #EEF2FF;
            color: #4F46E5;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background: #F9FAFB;
        }
        .badge-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .badge-item {
            background: #FEF3C7;
            border: 2px solid #F59E0B;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            color: #92400E;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 12px;
            color: #9CA3AF;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #9CA3AF;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📊 تقرير تقدم الطالب</h1>
        <p>منصة قِيَم - نظام إدارة القيم التعليمية</p>
        <p>{{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <!-- Student Info -->
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">اسم الطالب:</span>
            <span class="info-value">{{ $student->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">البريد الإلكتروني:</span>
            <span class="info-value">{{ $student->email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">المدرسة:</span>
            <span class="info-value">{{ $student->school->name ?? 'غير محدد' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">تاريخ التسجيل:</span>
            <span class="info-value">{{ $student->created_at->format('Y-m-d') }}</span>
        </div>
    </div>

    <!-- Statistics -->
    <div class="section">
        <div class="section-title">📈 الإحصائيات العامة</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">المستوى</div>
                <div class="stat-value">{{ $stats['level'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">إجمالي النقاط</div>
                <div class="stat-value">{{ number_format($stats['total_points']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">العملات</div>
                <div class="stat-value">{{ number_format($stats['total_coins']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">الشارات</div>
                <div class="stat-value">{{ $stats['total_badges'] }}</div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="section">
        <div class="section-title">📝 الأنشطة الأخيرة</div>
        @if($recentActivities->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>النشاط</th>
                        <th>النوع</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th>الدرجة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentActivities as $submission)
                        <tr>
                            <td>{{ $submission->activity->title }}</td>
                            <td>
                                @if($submission->activity->type === 'quiz') اختبار
                                @elseif($submission->activity->type === 'upload') تحميل ملف
                                @elseif($submission->activity->type === 'practical') عملي
                                @else مناقشة
                                @endif
                            </td>
                            <td>{{ $submission->created_at->format('Y-m-d') }}</td>
                            <td>
                                @if($submission->status === 'completed') مكتمل ✓
                                @elseif($submission->status === 'pending') معلق
                                @else مرفوض
                                @endif
                            </td>
                            <td>{{ $submission->score ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">لا توجد أنشطة مسجلة</div>
        @endif
    </div>

    <!-- Badges -->
    <div class="section">
        <div class="section-title">🏆 الشارات المكتسبة</div>
        @if($badges->count() > 0)
            <div class="badge-container">
                @foreach($badges as $badge)
                    <div class="badge-item">🎖️ {{ $badge->name }}</div>
                @endforeach
            </div>
        @else
            <div class="no-data">لم يحصل على أي شارات بعد</div>
        @endif
    </div>

    <!-- Performance by Values -->
    <div class="section">
        <div class="section-title">💎 الأداء حسب القيم</div>
        @if($valueStats->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>القيمة</th>
                        <th>عدد الأنشطة</th>
                        <th>متوسط الدرجات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($valueStats as $stat)
                        <tr>
                            <td>{{ $stat->value_name }}</td>
                            <td>{{ $stat->activities_count }}</td>
                            <td>{{ number_format($stat->avg_score, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">لا توجد بيانات كافية</div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>تم إنشاء هذا التقرير بواسطة منصة قِيَم التعليمية</p>
        <p>جميع الحقوق محفوظة © {{ now()->year }}</p>
    </div>
</body>
</html>
