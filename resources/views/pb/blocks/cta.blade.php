@php
    $p = $block['props'] ?? [];
    $edT = ! empty($pvEdit) ? ' data-pb-edit="title"' : '';
    $edX = ! empty($pvEdit) ? ' data-pb-edit="text"' : '';
@endphp
<section class="pb-block pb-cta">
    @if(!empty($p['title']))
        <h2 class="pb-cta-title"{!! $edT !!}>{{ $p['title'] }}</h2>
    @endif
    @if(!empty($p['text']))
        <p class="pb-cta-text"{!! $edX !!}>{{ $p['text'] }}</p>
    @endif
    @if(!empty($p['button_text']))
        <a class="pb-btn pb-btn-primary" href="{{ safe_url($p['button_link'] ?? '#') }}">{{ $p['button_text'] }}</a>
    @endif
</section>
