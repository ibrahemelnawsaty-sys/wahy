@extends('layouts.support')

@section('title', 'تعديل: ' . $user->name)
@section('page-title', 'تعديل مستخدم')

@section('content')
    <div style="margin-bottom: 16px;">
        <a href="{{ route('support.users.index') }}" class="support-btn support-btn-ghost">← رجوع للمستخدمين</a>
    </div>

    @if($errors->any())
    <div class="support-card" style="padding: 16px 20px; margin-bottom: 20px; border-right: 4px solid #ef4444;">
        <ul style="margin: 0; padding-right: 20px; color: #b91c1c;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="max-width: 640px; display: flex; flex-direction: column; gap: 24px;">
        <!-- User Summary -->
        <div class="support-card" style="padding: 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 22px; overflow: hidden; flex-shrink: 0;">
                @if($user->avatar)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                @else
                    {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                @endif
            </div>
            <div>
                <div style="font-weight: 700; font-size: 17px;">{{ $user->name }}</div>
                <div style="font-size: 13px; color: #94a3b8;">{{ $user->email }}</div>
                @php
                    $roleLabels = [
                        'super_admin' => 'سوبر أدمن', 'school_admin' => 'مدير مدرسة', 'teacher' => 'معلم',
                        'student' => 'طالب', 'parent' => 'ولي أمر', 'technical_support' => 'الدعم الفنيّ',
                    ];
                @endphp
                <span class="support-badge secondary" style="margin-top: 6px;">{{ $roleLabels[$user->role] ?? $user->role }}</span>
            </div>
        </div>

        <!-- Edit Basic Info -->
        <div class="support-card" style="padding: 24px;">
            <h3 style="margin: 0 0 20px; font-size: 16px; font-weight: 700;">📝 البيانات الأساسية</h3>
            <form method="POST" action="{{ route('support.users.update', $user) }}">
                @csrf
                @method('PUT')
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">الاسم</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255"
                               style="padding: 11px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">البريد الإلكترونيّ</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               style="padding: 11px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">رقم الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" placeholder="اختياري"
                               style="padding: 11px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                    </div>
                    <div style="font-size: 12px; color: #94a3b8; background: #f8fafc; padding: 10px 14px; border-radius: 8px;">
                        ℹ️ الدعم الفنيّ لا يمكنه تغيير الدور أو المدرسة.
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                    <button type="submit" class="support-btn support-btn-primary">💾 حفظ التعديلات</button>
                </div>
            </form>
        </div>

        <!-- Reset Password -->
        <div class="support-card" style="padding: 24px;">
            <h3 style="margin: 0 0 6px; font-size: 16px; font-weight: 700;">🔑 إعادة تعيين كلمة المرور</h3>
            <p style="margin: 0 0 20px; font-size: 13px; color: #94a3b8;">عيّن كلمة مرور جديدة للمستخدم. يمكنك إجباره على تغييرها عند أوّل دخول.</p>
            <form method="POST" action="{{ route('support.users.reset-password', $user) }}">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">كلمة المرور الجديدة</label>
                        <input type="password" name="password" required minlength="8" placeholder="8 أحرف على الأقل"
                               style="padding: 11px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="أعد كتابة كلمة المرور"
                               style="padding: 11px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                    </div>
                    <label style="display: inline-flex; align-items: center; gap: 10px; font-size: 14px; color: #475569; cursor: pointer;">
                        <input type="checkbox" name="force" value="1">
                        إجبار المستخدم على تغيير كلمة المرور عند الدخول التالي
                    </label>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                    <button type="submit" class="support-btn support-btn-warning">🔑 إعادة تعيين كلمة المرور</button>
                </div>
            </form>
        </div>

        <!-- Toggle Status -->
        <div class="support-card" style="padding: 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
            <div>
                <h3 style="margin: 0 0 4px; font-size: 16px; font-weight: 700;">⚡ حالة الحساب</h3>
                <p style="margin: 0; font-size: 13px; color: #94a3b8;">
                    الحالة الحاليّة:
                    @php $sc = $user->status === 'active' ? 'success' : ($user->status === 'suspended' ? 'danger' : 'secondary'); @endphp
                    <span class="support-badge {{ $sc }}">
                        @switch($user->status)
                            @case('active') نشط @break
                            @case('inactive') غير نشط @break
                            @case('suspended') موقوف @break
                            @default {{ $user->status }}
                        @endswitch
                    </span>
                </p>
            </div>
            <form method="POST" action="{{ route('support.users.toggle-status', $user) }}">
                @csrf
                <button type="submit" class="support-btn {{ $user->status === 'active' ? 'support-btn-secondary' : 'support-btn-success' }}">
                    {{ $user->status === 'active' ? '🔴 تعطيل الحساب' : '✅ تفعيل الحساب' }}
                </button>
            </form>
        </div>
    </div>
@endsection
