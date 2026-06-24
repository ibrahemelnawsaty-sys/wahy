<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كود التحقق - قيمّ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 40px 20px;
            direction: rtl;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #3CCB8A 0%, #2BA55D 100%);
            padding: 40px 30px;
            text-align: center;
        }
        
        .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 20px;
            color: #1F2937;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message {
            color: #6B7280;
            line-height: 1.8;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        .code-container {
            background: linear-gradient(135deg, rgba(60, 203, 138, 0.1) 0%, rgba(59, 130, 246, 0.1) 100%);
            border: 2px dashed #3CCB8A;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        
        .code-label {
            color: #6B7280;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .code {
            font-size: 48px;
            font-weight: 700;
            color: #3CCB8A;
            letter-spacing: 8px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }
        
        .code-expiry {
            color: #EF4444;
            font-size: 14px;
            margin-top: 15px;
            font-weight: 600;
        }
        
        .warning {
            background-color: #FEF3C7;
            border-right: 4px solid #F59E0B;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .warning-title {
            color: #D97706;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .warning-text {
            color: #92400E;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .footer {
            background-color: #F9FAFB;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }
        
        .footer-text {
            color: #6B7280;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .footer-links {
            margin-top: 20px;
        }
        
        .footer-links a {
            color: #3CCB8A;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .copyright {
            color: #9CA3AF;
            font-size: 12px;
            margin-top: 20px;
        }
        
        .social-links {
            margin-top: 20px;
        }
        
        .social-links a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background-color: #E5E7EB;
            border-radius: 50%;
            margin: 0 5px;
            text-decoration: none;
            line-height: 36px;
            transition: background-color 0.3s;
        }
        
        .social-links a:hover {
            background-color: #3CCB8A;
        }
        
        @media only screen and (max-width: 600px) {
            body {
                padding: 20px 10px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .code {
                font-size: 36px;
                letter-spacing: 4px;
            }
            
            .code-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">🌟</div>
            <h1>منصة قيمّ</h1>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                مرحباً {{ $userName }}!
            </div>
            
            <div class="message">
                لقد تلقينا طلباً لتسجيل الدخول إلى حسابك. لإكمال عملية تسجيل الدخول، يرجى استخدام كود التحقق التالي:
            </div>
            
            <!-- Code Container -->
            <div class="code-container">
                <div class="code-label">كود التحقق الخاص بك</div>
                <div class="code">{{ $code }}</div>
                <div class="code-expiry">⏰ هذا الكود صالح لمدة 10 دقائق فقط</div>
            </div>
            
            <div class="message">
                قم بإدخال هذا الكود في صفحة التحقق لإكمال عملية تسجيل الدخول.
            </div>
            
            <!-- Warning -->
            <div class="warning">
                <div class="warning-title">⚠️ تنبيه أمني</div>
                <div class="warning-text">
                    إذا لم تحاول تسجيل الدخول إلى حسابك، يرجى تجاهل هذا البريد الإلكتروني وتغيير كلمة المرور الخاصة بك فوراً للحفاظ على أمان حسابك.
                </div>
            </div>
            
            <div class="message">
                شكراً لاستخدامك منصة قيمّ! 🌟
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                منصة قيمّ - منصة تعليمية رائدة لبناء القيم الإنسانية
            </div>
            
            <div class="footer-links">
                <a href="http://127.0.0.2:8000">الرئيسية</a>
                <a href="http://127.0.0.2:8000/login">تسجيل الدخول</a>
                <a href="mailto:info@sa-salem.com">الدعم الفني</a>
            </div>
            
            <div class="copyright">
                &copy; {{ date('Y') }} منصة قيمّ. جميع الحقوق محفوظة.
            </div>
        </div>
    </div>
</body>
</html>
