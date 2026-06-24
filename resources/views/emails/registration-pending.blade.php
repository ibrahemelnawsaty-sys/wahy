<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم استلام طلب التسجيل</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa; direction: rtl;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px 16px 0 0;">
                            <div style="font-size: 48px; margin-bottom: 10px;">📋</div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;">تم استلام طلب التسجيل</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 20px; color: #2d3748; font-size: 18px; line-height: 1.8;">
                                مرحباً <strong style="color: #667eea;">{{ $user->name }}</strong>،
                            </p>
                            
                            <p style="margin: 0 0 25px; color: #4a5568; font-size: 16px; line-height: 1.8;">
                                شكراً لتسجيلك في منصة <strong>قيمّ</strong> التعليمية! 🎉
                            </p>

                            <!-- Info Box -->
                            <div style="background: linear-gradient(135deg, #f6f9fc 0%, #edf2f7 100%); border-radius: 12px; padding: 25px; margin: 25px 0; border-right: 4px solid #667eea;">
                                <h3 style="margin: 0 0 15px; color: #2d3748; font-size: 16px;">📌 تفاصيل طلبك:</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; color: #718096; font-size: 14px;">الاسم:</td>
                                        <td style="padding: 8px 0; color: #2d3748; font-size: 14px; font-weight: 600;">{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #718096; font-size: 14px;">البريد الإلكتروني:</td>
                                        <td style="padding: 8px 0; color: #2d3748; font-size: 14px; font-weight: 600;">{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #718096; font-size: 14px;">نوع الحساب:</td>
                                        <td style="padding: 8px 0; color: #2d3748; font-size: 14px; font-weight: 600;">{{ $roleName }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Status Box -->
                            <div style="background: #fef3c7; border-radius: 12px; padding: 20px; margin: 25px 0; text-align: center;">
                                <div style="font-size: 32px; margin-bottom: 10px;">⏳</div>
                                <p style="margin: 0; color: #92400e; font-size: 16px; font-weight: 600;">
                                    طلبك قيد المراجعة
                                </p>
                                <p style="margin: 10px 0 0; color: #a16207; font-size: 14px;">
                                    سيتم مراجعة طلبك من قبل فريق الإدارة وسنرسل لك إشعاراً عبر البريد الإلكتروني فور اتخاذ القرار.
                                </p>
                            </div>

                            <p style="margin: 25px 0; color: #4a5568; font-size: 15px; line-height: 1.8;">
                                عادةً ما تستغرق عملية المراجعة من <strong>24 إلى 48 ساعة</strong> في أيام العمل.
                            </p>

                            <!-- Help Section -->
                            <div style="background: #f0fdf4; border-radius: 12px; padding: 20px; margin-top: 25px;">
                                <p style="margin: 0; color: #166534; font-size: 14px;">
                                    💡 <strong>هل لديك استفسار؟</strong><br>
                                    <span style="color: #4ade80;">تواصل معنا عبر البريد الإلكتروني أو من خلال صفحة التواصل في الموقع.</span>
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #f8fafc; border-radius: 0 0 16px 16px; text-align: center;">
                            <p style="margin: 0 0 10px; color: #64748b; font-size: 14px;">
                                مع تحيات فريق <strong style="color: #667eea;">منصة قيمّ</strong>
                            </p>
                            <p style="margin: 0; color: #94a3b8; font-size: 12px;">
                                © {{ date('Y') }} جميع الحقوق محفوظة
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
