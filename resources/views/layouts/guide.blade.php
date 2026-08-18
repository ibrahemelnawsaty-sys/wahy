@php $__site = setting('site_name', 'أثيل مكة'); @endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    {{-- تهيئة الوضع الليلي فورًا (يطابق بقيّة المنصّة، بلا وميض) --}}
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('wahy-theme') || 'dark');</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('guide_title') - {{ $__site }}</title>
    <meta name="description" content="@yield('guide_desc', 'دليل استخدام منصة ' . $__site)">
    <meta name="robots" content="index, follow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/guide.css') }}?v=1">
</head>
<body class="@yield('guide_class', '')">
    <header class="g-header">
        <div class="g-header-in">
            <a href="/" class="g-brand"><span class="dot"></span>{{ $__site }}</a>
            <div class="g-head-actions">
                <a href="{{ route('guides.index') }}" class="g-btn">كل الأدلّة</a>
                <button type="button" class="g-btn" onclick="window.print()">طباعة / PDF</button>
                <button type="button" class="g-btn" id="gThemeBtn" aria-label="تبديل الوضع الليلي">🌙</button>
            </div>
        </div>
    </header>

    @yield('guide_body')

    <footer class="g-foot">
        <div>
            <a href="/">الرئيسية</a>
            <a href="{{ route('guides.index') }}">كل الأدلّة</a>
            <a href="/terms">الشروط والأحكام</a>
            <a href="/privacy">سياسة الخصوصية</a>
        </div>
        <span class="cr">{{ setting('footer_text') ?: '© ' . date('Y') . ' منصة ' . $__site . '. جميع الحقوق محفوظة' }}</span>
    </footer>

    <script>
    (function () {
        // مفتاح الثيم الموحّد للمنصّة (wahy-theme) — نفس مفتاح بقيّة الواجهات
        var btn = document.getElementById('gThemeBtn');
        var root = document.documentElement;
        function paint() { btn.textContent = root.getAttribute('data-theme') === 'dark' ? '☀️' : '🌙'; }
        paint();
        btn.addEventListener('click', function () {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('wahy-theme', next); } catch (e) {}
            paint();
        });

        // إبراز القسم الحالي في الفهرس الجانبيّ
        var links = Array.prototype.slice.call(document.querySelectorAll('.g-toc a'));
        var secs = links.map(function (a) { return document.querySelector(a.getAttribute('href')); }).filter(Boolean);
        if (secs.length && 'IntersectionObserver' in window) {
            var obs = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (!en.isIntersecting) return;
                    links.forEach(function (a) { a.classList.remove('active'); });
                    var hit = links.filter(function (a) { return a.getAttribute('href') === '#' + en.target.id; })[0];
                    if (hit) hit.classList.add('active');
                });
            }, { rootMargin: '-80px 0px -70% 0px' });
            secs.forEach(function (s) { obs.observe(s); });
        }
    })();
    </script>
</body>
</html>
