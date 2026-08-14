{{--
    معاينة حيّة للمستند الكامل (هيدر+جسم+فوتر) — تُطابق pb/document.blade.php لكن من كتلٍ
    مُحضَّرة مُرسَلة (تحرير غير محفوظ). الرندرة عبر pb.renderer الموثوق فقط — لا HTML خامّ.
--}}
@php $isRtl = in_array($locale ?? 'ar', ['ar', 'he', 'fa', 'ur'], true); @endphp
<!DOCTYPE html>
<html lang="{{ $locale ?? 'ar' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>معاينة حيّة</title>
    @include('pb.partials.base-styles')
    <style>body{padding:0}.pb-preview-empty{padding:64px 20px;text-align:center;color:#9ca3af}</style>
</head>
<body>
    <div class="pb-page">
        @if(!empty($headerBlocks))
            <header class="pb-page-header">@include('pb.renderer', ['blocks' => $headerBlocks])</header>
        @endif

        <main class="pb-page-body">
            @if(empty($bodyBlocks))
                <div class="pb-preview-empty">لا كتل في الجسم بعد — أضِف كتلة لتظهر هنا.</div>
            @else
                @include('pb.renderer', ['blocks' => $bodyBlocks])
            @endif
        </main>

        @if(!empty($footerBlocks))
            <footer class="pb-page-footer">@include('pb.renderer', ['blocks' => $footerBlocks])</footer>
        @endif
    </div>
    @if(\App\PageBuilder\BlockRegistry::needsRuntime(array_merge($headerBlocks, $bodyBlocks, $footerBlocks)))
        <script src="{{ asset('js/pb-runtime.js') }}" defer></script>
    @endif
</body>
</html>
