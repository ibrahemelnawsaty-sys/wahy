@php
    $p = $block['props'] ?? [];
    $style = $p['style'] ?? 'primary';
    $style = in_array($style, ['primary', 'secondary', 'ghost'], true) ? $style : 'primary';
    $align = $p['align'] ?? 'start';
    $align = in_array($align, ['start', 'center', 'end'], true) ? $align : 'start';
@endphp
<div class="pb-block pb-button-wrap" style="text-align:{{ $align }};">
    <a class="pb-btn pb-btn-{{ $style }}" href="{{ safe_url($p['link'] ?? '#') }}">{{ $p['text'] ?? 'زر' }}</a>
</div>
