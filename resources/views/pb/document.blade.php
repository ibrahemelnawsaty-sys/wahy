{{--
    مستند صفحة محرّر v2 الكامل (ت-١٢): يؤلّف منطقة الهيدر (جزء قالب) + جسم الكتل + الفوتر (جزء قالب)
    كنموذج FSE (كلٌّ مستقلّ التحرير). كلّ الكتل تمرّ عبر BlockTree::prepare (قائمة سماح + ترقية)
    وتُصيَّر عبر pb.renderer الموثوق فقط — لا HTML خامّ إطلاقاً.
--}}
@php
    use App\PageBuilder\BlockTree;
    use App\PageBuilder\Models\TemplatePart;

    $locale = $page->locale ?? 'ar';
    $isRtl = in_array($locale, ['ar', 'he', 'fa', 'ur'], true);

    $headerPart = $page->header ?: TemplatePart::activeFor('header', $locale);
    $footerPart = $page->footer ?: TemplatePart::activeFor('footer', $locale);

    $headerBlocks = BlockTree::prepare($headerPart?->blocks ?? []);
    $bodyBlocks   = BlockTree::prepare($page->blocks ?? []);
    $footerBlocks = BlockTree::prepare($footerPart?->blocks ?? []);

    $docTitle = $page->meta_title ?: $page->title;
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $docTitle }}</title>
    @if(!empty($page->meta_description))
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    @if(!empty($page->og_image))
        <meta property="og:title" content="{{ $docTitle }}">
        <meta property="og:image" content="{{ safe_url($page->og_image) }}">
    @endif
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{margin:0;font-family:'Tajawal','Segoe UI',system-ui,-apple-system,sans-serif;
            color:#1f2937;background:#fff;line-height:1.6}
        img{max-width:100%;height:auto;display:block}
        a{color:#667eea}
        .pb-page{min-height:100vh;display:flex;flex-direction:column}
        .pb-page-body{flex:1}
        .pb-block{max-width:1140px;margin-inline:auto;padding:28px 20px}
        .pb-hero{text-align:center;padding:72px 20px}
        .pb-hero-title{font-size:clamp(1.8rem,4vw,2.8rem);margin:0 0 12px;font-weight:800}
        .pb-hero-subtitle{font-size:1.15rem;color:#4b5563;margin:0 auto 24px;max-width:640px}
        .pb-btn{display:inline-block;padding:12px 28px;border-radius:12px;text-decoration:none;font-weight:700}
        .pb-btn-primary{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff}
        .pb-features-grid,.pb-columns-grid{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))}
        .pb-feature-card{padding:24px;border:1px solid #e5e7eb;border-radius:16px;text-align:center}
        .pb-feature-title{margin:0 0 8px;font-size:1.15rem}
        .pb-spacer{padding:0}
        .pb-cta{text-align:center;background:#f9fafb;border-radius:18px}
        @media (prefers-color-scheme: dark){
            body{background:#0f172a;color:#e2e8f0}
            .pb-hero-subtitle{color:#94a3b8}
            .pb-cta{background:#1e293b}
            .pb-feature-card{border-color:#334155}
        }
    </style>
</head>
<body>
    <div class="pb-page">
        @if(!empty($headerBlocks))
            <header class="pb-page-header">@include('pb.renderer', ['blocks' => $headerBlocks])</header>
        @endif

        <main class="pb-page-body">@include('pb.renderer', ['blocks' => $bodyBlocks])</main>

        @if(!empty($footerBlocks))
            <footer class="pb-page-footer">@include('pb.renderer', ['blocks' => $footerBlocks])</footer>
        @endif
    </div>
</body>
</html>
