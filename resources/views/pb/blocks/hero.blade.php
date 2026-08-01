@php $p = $block['props'] ?? []; @endphp
<section class="pb-block pb-hero">
    @if(!empty($p['title']))
        <h1 class="pb-hero-title">{{ $p['title'] }}</h1>
    @endif
    @if(!empty($p['subtitle']))
        <p class="pb-hero-subtitle">{{ $p['subtitle'] }}</p>
    @endif
    @if(!empty($p['button_text']))
        <a class="pb-btn pb-btn-primary" href="{{ safe_url($p['button_link'] ?? '#') }}">{{ $p['button_text'] }}</a>
    @endif
</section>
