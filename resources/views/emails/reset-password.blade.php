<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 40px 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .email-header {
            background: linear-gradient(135deg, #10B981 0%, #3B82F6 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .email-body {
            padding: 40px 30px;
            color: #334155;
            line-height: 1.8;
        }
        .email-body p {
            margin: 0 0 20px;
            font-size: 16px;
        }
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #10B981 0%, #3B82F6 100%);
            color: white;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 18px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
            transition: all 0.3s ease;
        }
        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
        }
        .info-box {
            background: #F1F5F9;
            border-right: 4px solid #10B981;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #64748B;
        }
        .email-footer {
            background: #F8FAFC;
            padding: 30px;
            text-align: center;
            color: #94A3B8;
            font-size: 14px;
            border-top: 1px solid #E2E8F0;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .link-text {
            color: #10B981;
            word-break: break-all;
            font-size: 14px;
            background: #F1F5F9;
            padding: 10px;
            border-radius: 6px;
            display: block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo">🔑</div>
            <h1>إعادة تعيين كلمة المرور</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <p><strong>مرحباً،</strong></p>
            
            <p>لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك في منصة قيمّ.</p>
            
            <p>للمتابعة، يرجى الضغط على الزر أدناه:</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="reset-button">إعادة تعيين كلمة المرور</a>
            </div>
            
            <div class="info-box">
                <p><strong>⏰ مهم:</strong> هذا الرابط صالح لمدة 60 دقيقة فقط من وقت الطلب.</p>
            </div>
            
            <p>إذا لم تستطع الضغط على الزر، يمكنك نسخ الرابط التالي ولصقه في متصفحك:</p>
            
            <div class="link-text">{{ $resetUrl }}</div>
            
            <p><strong>ملاحظة أمنية:</strong></p>
            <ul style="color: #64748B; font-size: 14px;">
                <li>إذا لم تطلب إعادة تعيين كلمة المرور، يرجى تجاهل هذا البريد</li>
                <li>لن يتم تغيير كلمة المرور الخاصة بك حتى تقوم بإنشاء واحدة جديدة</li>
                <li>لا تشارك هذا الرابط مع أي شخص آخر</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>منصة قيمّ التعليمية</strong></p>
            <p>منصة تعليمية رائدة لبناء القيم الإنسانية</p>
            <p style="margin-top: 15px;">© {{ date('Y') }} جميع الحقوق محفوظة</p>
        </div>
    </div>
</body>
</html>
