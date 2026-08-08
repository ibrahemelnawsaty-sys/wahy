@php $__site = setting('site_name', 'أثيل مكة'); @endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('doc_title') - {{ $__site }}</title>
    <meta name="description" content="@yield('doc_title') لمنصة {{ $__site }}">
    <meta name="robots" content="index, follow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--lg-primary:#3CCB8A;--lg-ink:#1e293b;--lg-muted:#64748b;--lg-bg:#f8fafc;--lg-card:#fff;--lg-border:#e5e7eb}
        *,*::before,*::after{box-sizing:border-box}
        body{margin:0;font-family:'Tajawal','Segoe UI',system-ui,sans-serif;background:var(--lg-bg);color:var(--lg-ink);line-height:1.9}
        a{color:var(--lg-primary);text-decoration:none}
        a:hover{text-decoration:underline}
        .lg-header{background:var(--lg-card);border-bottom:1px solid var(--lg-border);position:sticky;top:0;z-index:10}
        .lg-header-in{max-width:900px;margin:0 auto;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px}
        .lg-brand{display:flex;align-items:center;gap:8px;font-weight:800;font-size:1.15rem;color:var(--lg-ink)}
        .lg-brand .dot{width:12px;height:12px;border-radius:50%;background:var(--lg-primary)}
        .lg-home{font-weight:700;font-size:.92rem;border:1px solid var(--lg-border);border-radius:10px;padding:7px 14px;color:var(--lg-muted)}
        .lg-home:hover{border-color:var(--lg-primary);color:var(--lg-primary);text-decoration:none}
        .lg-wrap{max-width:820px;margin:0 auto;padding:36px 20px 60px}
        .lg-doc{background:var(--lg-card);border:1px solid var(--lg-border);border-radius:18px;padding:40px 34px}
        .lg-title{font-size:clamp(1.6rem,4vw,2.2rem);font-weight:800;margin:0 0 6px}
        .lg-updated{color:var(--lg-muted);font-size:.9rem;margin:0 0 8px}
        .lg-intro{color:var(--lg-muted);font-size:1.02rem;margin:0 0 24px;padding-bottom:20px;border-bottom:1px solid var(--lg-border)}
        .lg-doc h2{font-size:1.3rem;font-weight:800;margin:32px 0 10px;color:var(--lg-ink);scroll-margin-top:80px}
        .lg-doc h2 .n{color:var(--lg-primary);margin-inline-end:8px}
        .lg-doc h3{font-size:1.08rem;font-weight:700;margin:20px 0 8px}
        .lg-doc p{margin:0 0 12px}
        .lg-doc ul{margin:0 0 14px;padding-inline-start:22px}
        .lg-doc li{margin-bottom:7px}
        .lg-note{background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:14px 16px;color:#92400e;font-size:.92rem;margin:24px 0}
        .lg-foot{max-width:820px;margin:0 auto;padding:0 20px 40px;color:var(--lg-muted);font-size:.9rem;text-align:center}
        .lg-foot a{margin:0 8px}
        .lg-foot .cr{margin-top:10px;display:block}
        @media (prefers-color-scheme: dark){
            :root{--lg-ink:#e2e8f0;--lg-muted:#94a3b8;--lg-bg:#0f172a;--lg-card:#0b1220;--lg-border:#1f2937}
            .lg-note{background:#1c1917;border-color:#78350f;color:#fcd34d}
        }
        @media (max-width:560px){.lg-doc{padding:26px 20px}}
    </style>
</head>
<body>
    <header class="lg-header">
        <div class="lg-header-in">
            <a href="/" class="lg-brand"><span class="dot"></span>{{ $__site }}</a>
            <a href="/" class="lg-home">← العودة للرئيسية</a>
        </div>
    </header>

    <main class="lg-wrap">
        <article class="lg-doc">
            <h1 class="lg-title">@yield('doc_title')</h1>
            <p class="lg-updated">آخر تحديث: @yield('doc_updated', 'أغسطس 2026')</p>
            @yield('content')
        </article>
    </main>

    <footer class="lg-foot">
        <div>
            <a href="/">الرئيسية</a>
            <a href="/terms">الشروط والأحكام</a>
            <a href="/privacy">سياسة الخصوصية</a>
        </div>
        <span class="cr">{{ setting('footer_text') ?: '© ' . date('Y') . ' منصة ' . $__site . '. جميع الحقوق محفوظة' }}</span>
    </footer>
</body>
</html>
