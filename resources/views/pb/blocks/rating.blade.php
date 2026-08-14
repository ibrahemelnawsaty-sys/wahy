@php
    $p = $block['props'] ?? [];
    $v = (int) ($p['value'] ?? 0);
    $v = max(0, min(5, $v));
@endphp
<div class="pb-block pb-rating">
    <span class="pb-stars" aria-label="{{ $v }} من 5">@for($i = 1; $i <= 5; $i++){{ $i <= $v ? '★' : '☆' }}@endfor</span>
    @if(!empty($p['label']))<span class="pb-rating-label">{{ $p['label'] }}</span>@endif
</div>
