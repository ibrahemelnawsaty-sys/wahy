@php
    $p = $block['props'] ?? [];
    $style = $p['style'] ?? 'line';
    $style = in_array($style, ['line', 'dots', 'space'], true) ? $style : 'line';
@endphp
<div class="pb-block pb-separator pb-sep-{{ $style }}">@if($style !== 'space')<span></span>@endif</div>
