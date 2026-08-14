@extends('layouts.admin')

@section('title', 'إعدادات البريد')
@section('page-title', 'إعدادات بريد الأحداث')

@section('content')
<div class="es-wrap">
    @if (session('success'))<div class="es-alert ok">{{ session('success') }}</div>@endif

    <form method="POST" action="{{ route('admin.email-settings.update') }}">
        @csrf

        <div class="es-card master">
            <label class="es-row">
                <span><span class="t">المفتاح الرئيسيّ</span><br><span class="d">إيقاف مؤقّت لكل بريد الأحداث (لا يشمل الرسائل الأمنيّة الحرِجة كالـ2FA وإعادة كلمة المرور).</span></span>
                <input type="checkbox" name="email_master_enabled" value="1" {{ $master ? 'checked' : '' }}>
            </label>
        </div>

        <div class="es-card">
            <div class="es-title">أنواع بريد الأحداث</div>
            @foreach($types as $key => $label)
                <label class="es-row">
                    <span class="t">{{ $label }}</span>
                    <input type="checkbox" name="type_{{ $key }}" value="1" {{ $flags[$key] ? 'checked' : '' }}>
                </label>
            @endforeach
            <div class="es-note">الأنواع مُفعَّلة افتراضيًّا. المستخدم يمكنه إلغاء الاشتراك بنفسه من تذييل أيّ رسالة.</div>
        </div>

        <button type="submit" class="es-save">💾 حفظ</button>
    </form>
</div>

<style>
.es-wrap{max-width:640px;margin:0 auto;}
.es-alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600;font-size:14px;background:#dcfce7;color:#166534;}
.es-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;margin-bottom:16px;}
.es-card.master{border-color:#fca5a5;background:#fff7f7;}
.es-title{font-weight:800;margin-bottom:8px;color:#1e293b;}
.es-row{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:12px 0;border-bottom:1px solid #f1f5f9;}
.es-row:last-of-type{border-bottom:none;}
.es-row .t{font-weight:700;color:#1e293b;} .es-row .d{color:#94a3b8;font-size:12.5px;}
.es-row input{width:22px;height:22px;accent-color:#10b981;}
.es-note{color:#94a3b8;font-size:12.5px;margin-top:10px;}
.es-save{background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;padding:12px 34px;border-radius:12px;font-weight:800;cursor:pointer;}
[data-theme="dark"] .es-card{background:#1e293b;border-color:#334155;} [data-theme="dark"] .es-card.master{background:#2a1618;}
[data-theme="dark"] .es-title,[data-theme="dark"] .es-row .t{color:#f1f5f9;}
</style>
@endsection
