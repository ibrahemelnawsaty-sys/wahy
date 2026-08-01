@php
    $p = $block['props'] ?? [];
    $h = (int) min(max((int) ($p['height'] ?? 24), 0), 400);
@endphp
<div class="pb-block pb-spacer" style="height:{{ $h }}px;" aria-hidden="true"></div>
