@php
    $p = $block['props'] ?? [];
    $t = $p['type'] ?? 'info';
    $t = in_array($t, ['info', 'success', 'warning', 'error'], true) ? $t : 'info';
@endphp
@if(!empty($p['text']))
    <div class="pb-block pb-alert pb-alert-{{ $t }}">{{ $p['text'] }}</div>
@endif
