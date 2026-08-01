@php $p = $block['props'] ?? []; @endphp
<section class="pb-block pb-cta">
    @if(!empty($p['title']))
        <h2 class="pb-cta-title">{{ $p['title'] }}</h2>
    @endif
    @if(!empty($p['text']))
        <p class="pb-cta-text">{{ $p['text'] }}</p>
    @endif
    @if(!empty($p['button_text']))
        <a class="pb-btn pb-btn-primary" href="{{ safe_url($p['button_link'] ?? '#') }}">{{ $p['button_text'] }}</a>
    @endif
</section>
