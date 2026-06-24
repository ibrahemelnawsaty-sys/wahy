<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .welcome-text {
            font-size: 18px;
            color: #1f2937;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f9fafb;
            border-right: 4px solid #667eea;
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
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 30px 0;
        }
        .feature {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .feature-icon {
            font-size: 32px;
            margin-bottom: 10px;
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
            <h1>🎓 مرحباً بك في منصة قيمّ التعليمية</h1>
        </div>
        
        <div class="content">
            <p class="welcome-text">
                مرحباً <strong>{{ $user->name }}</strong>،
            </p>
            
            <p class="welcome-text">
                يسعدنا انضمامك إلى منصة قيمّ التعليمية! نحن متحمسون لبدء رحلتك التعليمية معنا.
            </p>

            <div class="info-box">
                <p><strong>📧 البريد الإلكتروني:</strong> {{ $user->email }}</p>
                <p><strong>🏫 المدرسة:</strong> {{ $user->school->name ?? 'غير محدد' }}</p>
                <p><strong>👤 الدور:</strong> 
                    @if($user->role === 'student')
                        طالب
                    @elseif($user->role === 'teacher')
                        معلم
                    @elseif($user->role === 'parent')
                        ولي أمر
                    @endif
                </p>
            </div>

            <div style="text-align: center;">
                <a href="{{ rtrim(config('app.url'), '/') }}/login" class="button">
                    🚀 ابدأ الآن
                </a>
            </div>

            <div class="features">
                <div class="feature">
                    <div class="feature-icon">📚</div>
                    <strong>أنشطة تفاعلية</strong>
                    <p style="font-size: 13px; color: #6b7280; margin: 5px 0;">اكتشف مئات الأنشطة التعليمية</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">🏆</div>
                    <strong>نظام المكافآت</strong>
                    <p style="font-size: 13px; color: #6b7280; margin: 5px 0;">احصل على نقاط وأوسمة</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">📊</div>
                    <strong>تتبع التقدم</strong>
                    <p style="font-size: 13px; color: #6b7280; margin: 5px 0;">راقب تطورك المستمر</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">👥</div>
                    <strong>العمل الجماعي</strong>
                    <p style="font-size: 13px; color: #6b7280; margin: 5px 0;">تعاون مع زملائك</p>
                </div>
            </div>

            <p class="welcome-text" style="margin-top: 30px;">
                إذا كان لديك أي أسئلة، لا تتردد في التواصل مع مدرستك أو فريق الدعم.
            </p>

            <p class="welcome-text">
                <strong>مع تمنياتنا لك بتجربة تعليمية ممتعة! 🌟</strong>
            </p>
        </div>

        <div class="footer">
            <p><strong>منصة قيمّ التعليمية</strong></p>
            <p>بناء القيم من خلال التعليم التفاعلي</p>
            <p style="margin-top: 15px; font-size: 12px;">
                © {{ date('Y') }} جميع الحقوق محفوظة
            </p>
        </div>
    </div>
</body>
</html>
