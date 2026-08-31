<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز تأكيد رسالتك - {{ setting('site_name', 'أثيل مكة') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5; padding: 40px 20px; direction: rtl; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #3CCB8A 0%, #2BA55D 100%); padding: 40px 30px; text-align: center; }
        .logo { font-size: 48px; margin-bottom: 10px; }
        .header h1 { color: #ffffff; font-size: 26px; font-weight: 700; margin: 0; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 20px; color: #1F2937; margin-bottom: 20px; font-weight: 600; }
        .message { color: #6B7280; line-height: 1.8; font-size: 16px; margin-bottom: 30px; }
        .code-container { background: linear-gradient(135deg, rgba(60, 203, 138, 0.1) 0%, rgba(59, 130, 246, 0.1) 100%); border: 2px dashed #3CCB8A; border-radius: 12px; padding: 30px; text-align: center; margin: 30px 0; }
        .code-label { color: #6B7280; font-size: 14px; margin-bottom: 10px; font-weight: 600; }
        .code { font-size: 44px; font-weight: 700; color: #2BA55D; letter-spacing: 10px; margin: 10px 0; font-family: 'Courier New', monospace; }
        .code-expiry { color: #EF4444; font-size: 14px; margin-top: 15px; font-weight: 600; }
        .warning { background-color: #FEF3C7; border-right: 4px solid #F59E0B; padding: 15px 20px; border-radius: 8px; margin: 30px 0; }
        .warning-title { color: #D97706; font-weight: 700; margin-bottom: 8px; font-size: 16px; }
        .warning-text { color: #92400E; font-size: 14px; line-height: 1.6; }
        .footer { background-color: #F9FAFB; padding: 30px; text-align: center; border-top: 1px solid #E5E7EB; }
        .footer-text { color: #6B7280; font-size: 14px; line-height: 1.6; margin-bottom: 15px; }
        .copyright { color: #9CA3AF; font-size: 12px; margin-top: 20px; }
        @media only screen and (max-width: 600px) {
            body { padding: 20px 10px; }
            .header { padding: 30px 20px; }
            .header h1 { font-size: 22px; }
            .content { padding: 30px 20px; }
            .code { font-size: 34px; letter-spacing: 6px; }
            .code-container { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">✉️</div>
            <h1>منصة {{ setting('site_name', 'أثيل مكة') }}</h1>
        </div>

        <div class="content">
            <div class="greeting">تأكيد بريدك الإلكتروني</div>

            <div class="message">
                لقد بدأتَ إرسال رسالة عبر نموذج «تواصل معنا». لإتمام الإرسال والتأكّد من أنّ هذا بريدك،
                يرجى إدخال رمز التحقّق التالي في النموذج:
            </div>

            <div class="code-container">
                <div class="code-label">رمز التحقّق الخاص بك</div>
                <div class="code">{{ $code }}</div>
                <div class="code-expiry">⏰ هذا الرمز صالح لمدة 10 دقائق فقط</div>
            </div>

            <div class="warning">
                <div class="warning-title">⚠️ تنبيه</div>
                <div class="warning-text">
                    إذا لم تُرسِل رسالةً عبر موقعنا، فتجاهل هذا البريد — لن يُتّخذ أيّ إجراء دون إدخال هذا الرمز.
                </div>
            </div>

            <div class="message">
                شكراً لتواصلك مع منصة {{ setting('site_name', 'أثيل مكة') }}! 🌟
            </div>
        </div>

        <div class="footer">
            <div class="footer-text">
                منصة {{ setting('site_name', 'أثيل مكة') }} - منصة تعليمية رائدة لبناء القيم الإنسانية
            </div>
            <div class="copyright">
                &copy; {{ date('Y') }} منصة {{ setting('site_name', 'أثيل مكة') }}. جميع الحقوق محفوظة.
            </div>
        </div>
    </div>
</body>
</html>
