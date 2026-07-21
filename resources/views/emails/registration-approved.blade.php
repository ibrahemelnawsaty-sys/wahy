@extends('emails.layouts.master')

@section('title', 'تمت الموافقة على طلبك')

@section('content')
    <h2 class="email-title">🎉 مبروك! تمت الموافقة على طلبك</h2>

    <p class="greeting">عزيزي/عزيزتي {{ $request->name }}،</p>

    <p class="message-text">
        يسعدنا إخبارك بأنه تمت الموافقة على طلب انضمامك إلى <strong>{{ $request->school->name }}</strong> عبر منصة {{ setting('site_name', 'قيمّ') }}.
    </p>

    <div class="success-box">
        <div class="success-box-title">✅ تمت الموافقة على طلبك</div>
        <div class="success-box-text">
            مرحباً بك في أسرة {{ $request->school->name }}! يمكنك الآن تسجيل الدخول إلى المنصة والبدء في رحلتك التعليمية.
        </div>
    </div>

    <div class="glass-card">
        <h3 style="color: #10b981; font-size: 20px; font-weight: 700; margin-bottom: 20px; text-align: center;">
            🔑 بيانات الدخول
        </h3>
        
        <table class="data-table">
            <tr>
                <td>البريد الإلكتروني:</td>
                <td><strong>{{ $request->email }}</strong></td>
            </tr>
            <tr>
                <td>كلمة المرور:</td>
                <td>
                    @if(!empty($temporaryPassword))
                        <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%); padding: 15px; border-radius: 8px; text-align: center; margin: 10px 0;">
                            <code style="font-size: 24px; font-weight: 700; color: #10b981; letter-spacing: 2px; font-family: 'Courier New', monospace;">{{ $temporaryPassword }}</code>
                        </div>
                        <small style="color: #ef4444; font-weight: 600; display: block; margin-top: 8px;">
                            ⚠️ ستُطلب منك تغيير كلمة المرور عند أول تسجيل دخول
                        </small>
                    @else
                        <strong style="color: #10b981;">كلمة المرور التي اخترتها عند التسجيل</strong>
                        <small style="color: #6b7280; display: block; margin-top: 8px;">
                            سجّل الدخول بنفس كلمة المرور التي أدخلتها عند تقديم طلبك. إن نسيتها فاستخدم «نسيت كلمة المرور».
                        </small>
                    @endif
                </td>
            </tr>
            <tr>
                <td>الدور:</td>
                <td>
                    @if($request->role == 'teacher')
                        <span style="color: #667eea; font-weight: 700;">👨‍🏫 معلم</span>
                    @elseif($request->role == 'student')
                        <span style="color: #10b981; font-weight: 700;">🎓 طالب</span>
                    @else
                        <span style="color: #3b82f6; font-weight: 700;">👨‍👩‍👧‍👦 ولي أمر</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>المدرسة:</td>
                <td><strong>{{ $request->school->name }}</strong></td>
            </tr>
            <tr>
                <td>تاريخ الموافقة:</td>
                <td>{{ now()->format('Y-m-d H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="info-box">
        <div class="info-box-title">🚀 ابدأ الآن</div>
        <div class="info-box-text">
            <ul style="margin: 10px 0; padding-right: 20px; line-height: 2;">
                <li>قم بتسجيل الدخول باستخدام بريدك الإلكتروني وكلمة المرور</li>
                <li>أكمل بياناتك الشخصية في صفحة الملف الشخصي</li>
                @if($request->role == 'teacher')
                    <li>تصفح الفصول الدراسية المتاحة</li>
                    <li>ابدأ بإنشاء الدروس والأنشطة</li>
                @elseif($request->role == 'student')
                    <li>تصفح الدروس والأنشطة المتاحة</li>
                    <li>ابدأ رحلتك التعليمية واكسب النقاط والشارات</li>
                @else
                    <li>تابع تقدم أبنائك الدراسي</li>
                    <li>تواصل مع المعلمين وإدارة المدرسة</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="btn-container">
        <a href="{{ rtrim(config('app.url'), '/') }}/login" class="btn">🔐 تسجيل الدخول الآن</a>
    </div>

    <div class="warning-box">
        <div class="warning-box-title">🔒 أمان حسابك</div>
        <div class="warning-box-text">
            <ul style="margin: 10px 0; padding-right: 20px; line-height: 1.8;">
                <li>لا تشارك كلمة المرور مع أي شخص</li>
                <li>استخدم كلمة مرور قوية ومعقدة</li>
                <li>قم بتفعيل المصادقة الثنائية من إعدادات الحساب</li>
                <li>في حال نسيان كلمة المرور، استخدم خيار "نسيت كلمة المرور"</li>
            </ul>
        </div>
    </div>

    <p class="message-text" style="text-align: center; color: #6b7280; margin-top: 30px;">
        نتمنى لك تجربة تعليمية رائعة ومثمرة! 🌟✨
    </p>
@endsection
