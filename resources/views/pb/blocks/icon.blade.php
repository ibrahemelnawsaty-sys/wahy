@php
    $p = $block['props'] ?? [];
    $size = $p['size'] ?? 'md';
    $size = in_array($size, ['sm', 'md', 'lg'], true) ? $size : 'md';
    $align = $p['align'] ?? 'center';
    $align = in_array($align, ['start', 'center', 'end'], true) ? $align : 'center';
@endphp
@if(! empty($p['icon']))
    <div class="pb-block pb-icon pb-icon-{{ $size }}" style="text-align:{{ $align }}">{{ $p['icon'] }}</div>
@endif
