{{-- معاينة المحرّر: تُصيَّر الكتل ($blocks مُحضَّرة) عبر المُصيِّر الموثوق داخل مستند مستقلّ (iframe). --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>معاينة</title>
    @include('pb.partials.base-styles')
    <style>body{padding:0}.pb-preview-empty{padding:80px 20px;text-align:center;color:#9ca3af}</style>
</head>
<body>
    @if(empty($blocks))
        <div class="pb-preview-empty">لا كتل بعد — أضِف كتلة لتظهر المعاينة.</div>
    @else
        <div class="pb-page"><main class="pb-page-body">@include('pb.renderer', ['blocks' => $blocks])</main></div>
    @endif
</body>
</html>
