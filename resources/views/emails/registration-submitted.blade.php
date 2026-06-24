@extends('emails.layouts.master')

@section('title', 'تأكيد استلام طلب التسجيل')

@section('content')
    <h2 class="email-title">✅ تم استلام طلبك بنجاح</h2>

    <p class="greeting">مرحباً {{ $request->name }}،</p>

    <p class="message-text">
        نشكرك على اهتمامك بالانضمام إلى <strong>{{ $request->school->name }}</strong> عبر منصة {{ setting('site_name', 'قيمّ') }}.
    </p>

    <div class="success-box">
        <div class="success-box-title">✨ تم استلام طلبك</div>
        <div class="success-box-text">
            تم تسجيل طلبك بنجاح وإرساله إلى إدارة المدرسة للمراجعة. سيتم التواصل معك قريباً بشأن قرار الموافقة.
        </div>
    </div>

    <div class="glass-card">
        <h3 style="color: #667eea; font-size: 20px; font-weight: 700; margin-bottom: 20px; text-align: center;">
            📋 تفاصيل الطلب
        </h3>
        
        <table class="data-table">
            <tr>
                <td>الاسم الكامل:</td>
                <td><strong>{{ $request->name }}</strong></td>
            </tr>
            <tr>
                <td>البريد الإلكتروني:</td>
                <td>{{ $request->email }}</td>
            </tr>
            @if($request->phone)
            <tr>
                <td>رقم الجوال:</td>
                <td>{{ $request->phone }}</td>
            </tr>
            @endif
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
                <td>تاريخ التقديم:</td>
                <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            <tr>
                <td>حالة الطلب:</td>
                <td><span style="color: #f59e0b; font-weight: 700;">⏳ قيد المراجعة</span></td>
            </tr>
        </table>
    </div>

    <div class="info-box">
        <div class="info-box-title">📌 ما التالي؟</div>
        <div class="info-box-text">
            <ul style="margin: 10px 0; padding-right: 20px; line-height: 2;">
                <li>ستقوم إدارة المدرسة بمراجعة طلبك خلال 24-48 ساعة</li>
                <li>ستصلك رسالة بريد إلكتروني بقرار الموافقة أو الرفض</li>
                <li>في حال الموافقة، ستتمكن من تسجيل الدخول مباشرة</li>
                <li>احتفظ ببياناتك (البريد الإلكتروني وكلمة المرور) للدخول لاحقاً</li>
            </ul>
        </div>
    </div>

    <div class="warning-box">
        <div class="warning-box-title">⚠️ تنبيه مهم</div>
        <div class="warning-box-text">
            لم تقدم هذا الطلب؟ يرجى تجاهل هذا البريد والتواصل مع إدارة المدرسة فوراً.
        </div>
    </div>

    <div class="btn-container">
        <a href="{{ url('/') }}" class="btn">🏠 العودة للرئيسية</a>
    </div>

    <p class="message-text" style="text-align: center; color: #6b7280; margin-top: 30px;">
        نتمنى لك تجربة تعليمية مميزة! 🌟
    </p>
@endsection
