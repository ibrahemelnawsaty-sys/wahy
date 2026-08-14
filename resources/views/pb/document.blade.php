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

    // hide_header/hide_footer (دفعة 1): تُلغي المنطقة لهذه الصفحة صراحةً (تختلف عن null = الافتراضيّ العالميّ).
    $headerPart = $page->hide_header ? null : ($page->header ?: TemplatePart::activeFor('header', $locale));
    $footerPart = $page->hide_footer ? null : ($page->footer ?: TemplatePart::activeFor('footer', $locale));

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
    <link rel="canonical" href="{{ url('/pages/' . $page->slug) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $docTitle }}">
    <meta property="og:url" content="{{ url('/pages/' . $page->slug) }}">
    <meta name="twitter:card" content="{{ !empty($page->og_image) ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $docTitle }}">
    @if(!empty($page->meta_description))
        <meta name="description" content="{{ $page->meta_description }}">
        <meta property="og:description" content="{{ $page->meta_description }}">
        <meta name="twitter:description" content="{{ $page->meta_description }}">
    @endif
    @if(!empty($page->og_image))
        <meta property="og:image" content="{{ safe_url($page->og_image) }}">
        <meta name="twitter:image" content="{{ safe_url($page->og_image) }}">
    @endif
    @include('pb.partials.base-styles')
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
    @if(\App\PageBuilder\BlockRegistry::needsRuntime(array_merge($headerBlocks, $bodyBlocks, $footerBlocks)))
        <script src="{{ asset('js/pb-runtime.js') }}" defer></script>
    @endif
</body>
</html>
