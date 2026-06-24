<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حصلت على وسام جديد</title>
    <style>
        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .badge-display {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 4px solid #f59e0b;
            border-radius: 50%;
            width: 200px;
            height: 200px;
            margin: 30px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3);
        }
        .badge-icon {
            font-size: 80px;
            margin-bottom: 10px;
        }
        .badge-name {
            font-size: 20px;
            font-weight: bold;
            color: #92400e;
        }
        .congratulations {
            font-size: 24px;
            color: #1f2937;
            margin: 30px 0;
            font-weight: bold;
        }
        .description {
            background-color: #fffbeb;
            border: 2px solid #fbbf24;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            color: #92400e;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: bold;
        }
        .achievement-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 30px 0;
        }
        .stat-box {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
        }
        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #f59e0b;
        }
        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .sparkles {
            font-size: 48px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏆 مبروك! وسام جديد</h1>
        </div>
        
        <div class="content">
            <div class="sparkles">✨ ⭐ ✨</div>

            <p class="congratulations">
                مبروك <strong>{{ $user->name }}</strong>!
            </p>

            <div class="badge-display">
                <div class="badge-icon">{{ $badge->icon ?? '🏆' }}</div>
                <div class="badge-name">{{ $badge->name }}</div>
            </div>

            <p style="font-size: 18px; color: #4b5563; margin: 20px 0;">
                لقد حصلت على وسام جديد!
            </p>

            @if($badge->description)
                <div class="description">
                    <strong>📜 وصف الوسام:</strong><br>
                    {{ $badge->description }}
                </div>
            @endif

            <div class="achievement-stats">
                <div class="stat-box">
                    <div class="stat-icon">🏅</div>
                    <div class="stat-value">{{ $user->badges()->count() }}</div>
                    <div class="stat-label">إجمالي الأوسمة</div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-value">{{ $user->points()->sum('amount') }}</div>
                    <div class="stat-label">إجمالي النقاط</div>
                </div>
            </div>

            <p style="color: #4b5563; line-height: 1.6; margin: 30px 0;">
                هذا الإنجاز يعكس تفانيك والتزامك في التعلم. استمر في العمل الجاد لتحصل على المزيد من الأوسمة والإنجازات!
            </p>

            <div style="text-align: center;">
                <a href="{{ url('/student/badges') }}" class="button">
                    🎖️ عرض جميع الأوسمة
                </a>
            </div>

            <div style="margin-top: 40px; padding: 20px; background-color: #fef3c7; border-radius: 8px;">
                <p style="margin: 0; color: #92400e; font-weight: bold;">
                    💡 نصيحة: شارك إنجازك مع أصدقائك وحفزهم على التعلم معك!
                </p>
            </div>
        </div>

        <div class="footer">
            <p><strong>منصة قيمّ التعليمية</strong></p>
            <p>نفتخر بإنجازاتك! 🌟</p>
        </div>
    </div>
</body>
</html>
