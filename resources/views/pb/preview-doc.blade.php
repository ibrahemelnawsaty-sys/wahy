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
    <script>(function(){try{var t=localStorage.getItem('wahy-theme');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
    <title>معاينة حيّة</title>
    @include('pb.partials.base-styles')
    <style>body{padding:0}.pb-preview-empty{padding:72px 24px;text-align:center;color:#94a3b8;line-height:1.9}.pb-preview-empty .pb-pe-ico{font-size:2.6rem;margin-bottom:8px}</style>
</head>
<body>
    <div class="pb-page">
        @if(!empty($useSiteHeader))
            @include('pb.partials.site-header')
        @elseif(!empty($headerBlocks))
            <header class="pb-page-header">@include('pb.renderer', ['blocks' => $headerBlocks])</header>
        @endif

        <main class="pb-page-body">
            @if(empty($bodyBlocks))
                <div class="pb-preview-empty">
                    <div class="pb-pe-ico">📄</div>
                    هذه معاينة صفحتك الحيّة.<br>اضغط «🧩 أنماط جاهزة» أو «أضف كتلة» من اليمين — وستظهر فوراً هنا بمحتوى مبدئيّ.
                </div>
            @else
                @include('pb.renderer', ['blocks' => $bodyBlocks, 'pvTop' => true])
            @endif
        </main>

        @if(!empty($useSiteFooter))
            @include('pb.partials.site-footer')
        @elseif(!empty($footerBlocks))
            <footer class="pb-page-footer">@include('pb.renderer', ['blocks' => $footerBlocks])</footer>
        @endif
    </div>
    {{-- تحرير حرفيّ في المكان + انقر لتحديد الكتلة (postMessage للمحرّر — لا حقن). --}}
    <script>
        (function () {
            // النصوص القابلة للتحرير: contenteditable + بثّ القيمة عند فقد التركيز
            document.querySelectorAll('[data-pb-edit]').forEach(function (el) {
                el.setAttribute('contenteditable', 'true');
                el.setAttribute('spellcheck', 'false');
                el.addEventListener('blur', function () {
                    var w = el.closest('[data-pb-path]'); if (!w) return;
                    try { parent.postMessage({ pbEdit: { path: w.getAttribute('data-pb-path'), key: el.getAttribute('data-pb-edit'), value: el.innerText } }, '*'); } catch (x) {}
                });
                // Enter يُنهي التحرير (عدا الفقرات — تسمح بأسطر)
                el.addEventListener('keydown', function (e) { if (e.key === 'Enter' && el.tagName !== 'P') { e.preventDefault(); el.blur(); } });
            });
            // النقر خارج نصّ قابل للتحرير يُحدّد الكتلة
            document.addEventListener('click', function (e) {
                if (e.target.closest('[data-pb-edit]')) return;
                var el = e.target.closest('[data-pb-path]'); if (!el) return;
                e.preventDefault();
                try { parent.postMessage({ pbSelect: el.getAttribute('data-pb-path') }, '*'); } catch (x) {}
            });
        })();
    </script>
    <button class="pb-theme-toggle" type="button" title="تبديل الوضع الليليّ/النهاريّ" aria-label="تبديل الوضع"
        onclick="(function(r){var d=r.getAttribute('data-theme')==='dark'?'light':'dark';r.setAttribute('data-theme',d);try{localStorage.setItem('wahy-theme',d);}catch(e){}})(document.documentElement)">🌓</button>
    @if(\App\PageBuilder\BlockRegistry::needsRuntime(array_merge($headerBlocks, $bodyBlocks, $footerBlocks)))
        <script src="{{ asset('js/pb-runtime.js') }}" defer></script>
    @endif
</body>
</html>
