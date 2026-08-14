@php
    $p = $block['props'] ?? [];
    $bg = trim((string) ($p['bg'] ?? ''));
    if (\Illuminate\Support\Str::startsWith($bg, ['http://', 'https://'])) {
        $bgUrl = safe_url($bg, '');
    } elseif ($bg !== '' && ! str_contains($bg, '..') && ! preg_match('#^[a-z][a-z0-9+.\-]*:#i', $bg)) {
        $bgUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($bg);
    } else {
        $bgUrl = '';
    }
    $overlay = $p['overlay'] ?? 'dark';
    $overlay = in_array($overlay, ['none', 'dark', 'light'], true) ? $overlay : 'dark';
@endphp
<section class="pb-block pb-cover pb-cover-{{ $overlay }}" @if($bgUrl !== '')style="background-image:url('{{ $bgUrl }}')"@endif>
    <div class="pb-cover-inner">
        @if(! empty($p['title']))<h2 class="pb-cover-title">{{ $p['title'] }}</h2>@endif
        @if(! empty($p['subtitle']))<p class="pb-cover-sub">{{ $p['subtitle'] }}</p>@endif
        @if(! empty($p['button_text']))
            <a class="pb-btn pb-btn-primary" href="{{ safe_url($p['button_link'] ?? '#') }}">{{ $p['button_text'] }}</a>
        @endif
    </div>
</section>
