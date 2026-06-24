<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم تقييم نشاطك</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        }
        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px auto;
            font-size: 36px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
        }
        .info-box {
            background-color: #f9fafb;
            border-right: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .info-box p {
            margin: 10px 0;
            color: #4b5563;
        }
        .info-box strong {
            color: #1f2937;
        }
        .feedback-box {
            background-color: #ecfdf5;
            border: 2px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .feedback-box h3 {
            color: #059669;
            margin-top: 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: bold;
        }
        .rewards {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
        }
        .reward-item {
            text-align: center;
        }
        .reward-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .reward-value {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ تم تقييم نشاطك</h1>
        </div>
        
        <div class="content">
            <p style="font-size: 18px; text-align: center; color: #1f2937;">
                مرحباً <strong>{{ $submission->student->name }}</strong>،
            </p>

            <p style="text-align: center; color: #4b5563;">
                تم تقييم نشاطك من قبل المعلم
            </p>

            <div class="score-circle">
                {{ $submission->score ?? 0 }}%
            </div>

            <div class="info-box">
                <p><strong>📚 النشاط:</strong> {{ $submission->activity->title }}</p>
                <p><strong>👨‍🏫 المعلم:</strong> {{ $submission->activity->creator->name }}</p>
                <p><strong>📅 تاريخ التقديم:</strong> {{ $submission->created_at->format('Y-m-d H:i') }}</p>
                <p><strong>✅ الحالة:</strong> 
                    @if($submission->status === 'completed')
                        مكتمل
                    @elseif($submission->status === 'pending')
                        قيد المراجعة
                    @else
                        @php
                            echo match($submission->status) {
                                'approved' => 'معتمد',
                                'rejected' => 'مرفوض',
                                default => $submission->status
                            };
                        @endphp
                    @endif
                </p>
            </div>

            @if($submission->feedback)
                <div class="feedback-box">
                    <h3>💬 ملاحظات المعلم:</h3>
                    <p style="line-height: 1.6;">{{ html_excerpt($submission->feedback, 1000) }}</p>
                </div>
            @endif

            <div class="rewards">
                <div class="reward-item">
                    <div class="reward-icon">⭐</div>
                    <div class="reward-value">+{{ $submission->activity->points ?? 0 }}</div>
                    <div style="color: #6b7280; font-size: 14px;">نقطة</div>
                </div>
                <div class="reward-item">
                    <div class="reward-icon">🪙</div>
                    <div class="reward-value">+{{ $submission->activity->coins ?? 0 }}</div>
                    <div style="color: #6b7280; font-size: 14px;">عملة</div>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/student/dashboard') }}" class="button">
                    📊 عرض لوحة التحكم
                </a>
            </div>

            <p style="margin-top: 30px; text-align: center; color: #4b5563;">
                @if($submission->score >= 90)
                    🎉 <strong>ممتاز!</strong> أداء رائع، استمر في التقدم!
                @elseif($submission->score >= 75)
                    👏 <strong>جيد جداً!</strong> أنت على الطريق الصحيح!
                @elseif($submission->score >= 60)
                    💪 <strong>جيد!</strong> يمكنك تحسين أدائك أكثر!
                @else
                    📚 <strong>حاول مرة أخرى!</strong> لا تستسلم، التعلم رحلة!
                @endif
            </p>
        </div>

        <div class="footer">
            <p><strong>منصة قيمّ التعليمية</strong></p>
            <p>استمر في التعلم والتطور 🚀</p>
        </div>
    </div>
</body>
</html>
