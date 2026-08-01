@php
    $p = $block['props'] ?? [];
    $src = trim($p['src'] ?? '');
    // خارجيّ https/http عبر safe_url؛ وإلا مسار وسائط محلّيّ آمن (بلا مخطّط/تسلّق) عبر القرص العامّ.
    if (\Illuminate\Support\Str::startsWith($src, ['http://', 'https://'])) {
        $url = safe_url($src, '');
    } elseif ($src !== '' && ! str_contains($src, '..') && ! preg_match('#^[a-z][a-z0-9+.\-]*:#i', $src)) {
        $url = \Illuminate\Support\Facades\Storage::disk('public')->url($src);
    } else {
        $url = '';
    }
@endphp
@if($url !== '')
    <figure class="pb-block pb-image">
        <img src="{{ $url }}" alt="{{ $p['alt'] ?? '' }}" loading="lazy">
        @if(!empty($p['caption']))
            <figcaption class="pb-image-caption">{{ $p['caption'] }}</figcaption>
        @endif
    </figure>
@endif
