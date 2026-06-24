@extends('emails.layouts.master')

@section('title', 'طلب تسجيل جديد')

@section('content')
    <h2 class="email-title">🔔 طلب تسجيل جديد</h2>

    <p class="greeting">عزيزي مدير المدرسة،</p>

    <p class="message-text">
        تم استلام طلب تسجيل جديد في مدرسة <strong>{{ $request->school->name }}</strong> يتطلب موافقتك.
    </p>

    <div class="info-box">
        <div class="info-box-title">📢 إجراء مطلوب</div>
        <div class="info-box-text">
            يرجى مراجعة الطلب والموافقة عليه أو رفضه من خلال لوحة التحكم في أقرب وقت ممكن.
        </div>
    </div>

    <div class="glass-card">
        <h3 style="color: #667eea; font-size: 20px; font-weight: 700; margin-bottom: 20px; text-align: center;">
            👤 معلومات المتقدم
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
                <td>تاريخ التقديم:</td>
                <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
            </tr>
        </table>

        @if($request->data)
            <h4 style="color: #4b5563; font-size: 17px; font-weight: 700; margin: 25px 0 15px;">
                📝 معلومات إضافية
            </h4>
            
            @php $data = is_string($request->data) ? json_decode($request->data, true) : $request->data; @endphp
            
            <table class="data-table">
                @if($request->role == 'teacher')
                    @if(isset($data['qualifications']))
                    <tr>
                        <td>المؤهلات:</td>
                        <td>{{ $data['qualifications'] }}</td>
                    </tr>
                    @endif
                    @if(isset($data['experience_years']))
                    <tr>
                        <td>سنوات الخبرة:</td>
                        <td>{{ $data['experience_years'] }} سنة</td>
                    </tr>
                    @endif
                    @if(isset($data['specialization']))
                    <tr>
                        <td>التخصص:</td>
                        <td>{{ $data['specialization'] }}</td>
                    </tr>
                    @endif
                @elseif($request->role == 'student')
                    @if(isset($data['birth_date']))
                    <tr>
                        <td>تاريخ الميلاد:</td>
                        <td>{{ $data['birth_date'] }}</td>
                    </tr>
                    @endif
                    @if(isset($data['grade_level']))
                    <tr>
                        <td>المرحلة الدراسية:</td>
                        <td>{{ $data['grade_level'] }}</td>
                    </tr>
                    @endif
                    @if(isset($data['parent_name']))
                    <tr>
                        <td>اسم ولي الأمر:</td>
                        <td>{{ $data['parent_name'] }}</td>
                    </tr>
                    @endif
                    @if(isset($data['parent_name']))
                    <tr>
                        <td>اسم ولي الأمر:</td>
                        <td>{{ $data['parent_name'] }}</td>
                    </tr>
                    @endif
                    @if(isset($data['parent_email']))
                    <tr>
                        <td>بريد ولي الأمر:</td>
                        <td>{{ $data['parent_email'] }}</td>
                    </tr>
                    @endif
                    @if(isset($data['parent_phone']))
                    <tr>
                        <td>جوال ولي الأمر:</td>
                        <td>{{ $data['parent_phone'] }}</td>
                    </tr>
                    @endif
                @elseif($request->role == 'parent')
                    @if(isset($data['relationship']))
                    <tr>
                        <td>الصلة:</td>
                        <td>{{ $data['relationship'] }}</td>
                    </tr>
                    @endif
                    @if(isset($data['children_names']))
                    <tr>
                        <td>أسماء الأبناء:</td>
                        <td>{{ $data['children_names'] }}</td>
                    </tr>
                    @endif
                    @if(isset($data['address']))
                    <tr>
                        <td>العنوان:</td>
                        <td>{{ $data['address'] }}</td>
                    </tr>
                    @endif
                @endif
            </table>
        @endif
    </div>

    <div class="btn-container">
        <a href="{{ route('school-admin.requests') }}" class="btn">📋 مراجعة الطلب الآن</a>
    </div>

    <p class="message-text" style="text-align: center; color: #6b7280; margin-top: 30px;">
        يرجى مراجعة الطلب في أقرب وقت ممكن
    </p>
@endsection
