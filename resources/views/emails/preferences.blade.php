@php $site = setting('site_name', 'أثيل مكة'); $primary = setting('primary_color', '#3CCB8A'); @endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تفضيلات البريد — {{ $site }}</title>
    <style>
        body { margin:0; background:#f1f5f9; font-family:'Segoe UI',Tahoma,sans-serif; color:#1f2937; padding:24px 14px; }
        .box { max-width:520px; margin:40px auto; background:#fff; border-radius:16px; padding:32px 28px; box-shadow:0 10px 30px rgba(0,0,0,.08); }
        h1 { font-size:22px; margin:0 0 6px; }
        .sub { color:#64748b; font-size:14px; margin-bottom:22px; }
        .done { background:#dcfce7; color:#166534; padding:12px 16px; border-radius:10px; margin-bottom:18px; font-weight:600; }
        .row { display:flex; align-items:center; gap:12px; padding:14px 0; border-bottom:1px solid #f1f5f9; }
        .row input { width:20px; height:20px; accent-color: {{ $primary }}; }
        .row .t { font-weight:700; } .row .d { color:#94a3b8; font-size:13px; }
        .save { background: {{ $primary }}; color:#fff; border:none; padding:12px 30px; border-radius:10px; font-weight:700; font-size:15px; cursor:pointer; margin-top:20px; }
        .allbtn { background:none; border:none; color:#dc2626; text-decoration:underline; cursor:pointer; font-size:13px; margin-top:16px; }
        .note { color:#94a3b8; font-size:12px; margin-top:18px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>تفضيلات البريد الإلكترونيّ</h1>
        <div class="sub">{{ $user->email }} — {{ $site }}</div>

        @if(!empty($done))<div class="done">✅ {{ $done }}</div>@endif

        <form method="POST" action="{{ \Illuminate\Support\Facades\URL::signedRoute('email.unsubscribe.update', ['user' => $user->id]) }}">
            @csrf
            <label class="row">
                <input type="checkbox" name="events" value="1" {{ $pref->events ? 'checked' : '' }}>
                <span><span class="t">إشعارات الأحداث</span><br><span class="d">تصحيح نشاط، شارة جديدة، رسالة، تذكرة…</span></span>
            </label>
            <label class="row">
                <input type="checkbox" name="announcements" value="1" {{ $pref->announcements ? 'checked' : '' }}>
                <span><span class="t">الإعلانات والحملات</span><br><span class="d">جديد المنصّة والتعميمات</span></span>
            </label>
            <label class="row">
                <input type="checkbox" name="digests" value="1" {{ $pref->digests ? 'checked' : '' }}>
                <span><span class="t">الملخّصات الدوريّة</span><br><span class="d">ملخّص أسبوعيّ لتقدّمك</span></span>
            </label>

            <button type="submit" class="save">حفظ التفضيلات</button>
        </form>

        <form method="POST" action="{{ \Illuminate\Support\Facades\URL::signedRoute('email.unsubscribe.update', ['user' => $user->id]) }}">
            @csrf
            <input type="hidden" name="action" value="unsubscribe_all">
            <button type="submit" class="allbtn">إلغاء الاشتراك في كل الرسائل غير الأساسيّة</button>
        </form>

        <div class="note">الرسائل الأساسيّة (رمز الدخول، إعادة كلمة المرور، تنبيهات الأمان) تُرسَل دائمًا لحماية حسابك.</div>
    </div>
</body>
</html>
