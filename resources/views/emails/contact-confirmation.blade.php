<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد استلام رسالتك - منصة قيمّ</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            direction: rtl;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .header h1 {
            color: #10B981;
            margin: 0;
            font-size: 24px;
        }
        .content {
            color: #333;
            line-height: 1.8;
        }
        .success-box {
            background: #D4EDDA;
            border: 1px solid #C3E6CB;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .info-box {
            background: #F9FAFB;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .contact-info {
            margin-top: 30px;
            padding: 20px;
            background: #FAFAFA;
            border-radius: 8px;
        }
        .contact-info h3 {
            color: #1A1A1A;
            margin-top: 0;
        }
        .contact-info p {
            margin: 10px 0;
            color: #555;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            color: #7A7A7A;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🌟</div>
            <h1>تم استلام رسالتك بنجاح</h1>
        </div>

        <div class="content">
            <div class="success-box">
                <div class="success-icon">✓</div>
                <p><strong>شكراً لتواصلك معنا!</strong></p>
            </div>

            <p>مرحباً <strong>{{ $data['full_name'] }}</strong>،</p>

            <p>نشكرك على تواصلك مع منصة قيمّ. تم استلام رسالتك بنجاح وسيقوم فريقنا بالرد عليك في أقرب وقت ممكن.</p>

            <div class="info-box">
                <p><strong>ملخص رسالتك:</strong></p>
                <p>{{ \Illuminate\Support\Str::limit($data['message'], 200) }}</p>
            </div>

            <p>وقت الاستجابة المتوقع: <strong>1-2 يوم عمل</strong></p>

            <div class="contact-info">
                <h3>معلومات التواصل السريع</h3>
                <p>📧 البريد الإلكتروني: support@qiyamm.sa</p>
                <p>☎️ رقم الهاتف: +966 5 000 0000</p>
                <p>🕒 أوقات العمل: الأحد – الخميس | 8:00 صباحاً – 4:00 مساءً</p>
            </div>
        </div>

        <div class="footer">
            <p><strong>منصة قيمّ</strong> - نظام إدارة القيم المدرسية</p>
            <p>الرياض، المملكة العربية السعودية</p>
            <p>© 2025 جميع الحقوق محفوظة</p>
        </div>
    </div>
</body>
</html>
