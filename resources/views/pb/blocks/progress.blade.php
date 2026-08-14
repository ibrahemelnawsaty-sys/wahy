@php
    $p = $block['props'] ?? [];
    $v = (int) ($p['value'] ?? 0);
    $v = max(0, min(100, $v));
@endphp
<div class="pb-block pb-progress-wrap">
    @if(!empty($p['label']))<div class="pb-progress-label">{{ $p['label'] }} <span>{{ $v }}%</span></div>@endif
    <div class="pb-progress"><div class="pb-progress-bar" style="width:{{ $v }}%"></div></div>
</div>
