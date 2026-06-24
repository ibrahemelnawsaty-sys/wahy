@extends('emails.layouts.master')

@section('title', 'تم رفض طلب التسجيل')

@section('content')
    <h2 class="email-title">❌ تم رفض طلب التسجيل</h2>

    <p class="greeting">عزيزي/عزيزتي {{ $request->name }}،</p>

    <p class="message-text">
        نأسف لإخبارك بأنه تم رفض طلب انضمامك إلى <strong>{{ $request->school->name }}</strong>.
    </p>

    <div class="danger-box">
        <div class="danger-box-title">⚠️ تم رفض الطلب</div>
        <div class="danger-box-text">
            للأسف، لم تتم الموافقة على طلب التسجيل الخاص بك في الوقت الحالي.
        </div>
    </div>

    <div class="glass-card">
        <h3 style="color: #ef4444; font-size: 20px; font-weight: 700; margin-bottom: 20px; text-align: center;">
            📋 تفاصيل الطلب المرفوض
        </h3>
        
        <table class="data-table">
            <tr>
                <td>الاسم:</td>
                <td><strong>{{ $request->name }}</strong></td>
            </tr>
            <tr>
                <td>البريد الإلكتروني:</td>
                <td>{{ $request->email }}</td>
            </tr>
            <tr>
                <td>الدور المطلوب:</td>
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
                <td>تاريخ الرفض:</td>
                <td>{{ now()->format('Y-m-d H:i') }}</td>
            </tr>
        </table>
    </div>

    @if($request->rejected_reason)
    <div class="warning-box">
        <div class="warning-box-title">📝 سبب الرفض</div>
        <div class="warning-box-text">
            {{ $request->rejected_reason }}
        </div>
    </div>
    @endif

    <div class="info-box">
        <div class="info-box-title">💡 ماذا يمكنك فعله؟</div>
        <div class="info-box-text">
            <ul style="margin: 10px 0; padding-right: 20px; line-height: 2;">
                <li>يمكنك التواصل مع إدارة المدرسة للاستفسار عن أسباب الرفض</li>
                <li>تأكد من استيفاء جميع المتطلبات اللازمة</li>
                <li>يمكنك إعادة تقديم الطلب بعد معالجة الأسباب</li>
                <li>في حال وجود أي استفسار، لا تتردد في التواصل معنا</li>
            </ul>
        </div>
    </div>

    <div class="btn-container">
        <a href="{{ url('/contact') }}" class="btn">📧 تواصل معنا</a>
    </div>

    <p class="message-text" style="text-align: center; color: #6b7280; margin-top: 30px;">
        نقدر اهتمامك ونتمنى لك التوفيق 🌟
    </p>
@endsection
