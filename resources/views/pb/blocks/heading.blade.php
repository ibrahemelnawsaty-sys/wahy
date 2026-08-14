@php
    $p = $block['props'] ?? [];
    // المستوى من قائمة سماح صارمة (h1..h6) — فلا يُصبّ وسمٌ عشوائيّ عبر <{{ $level }}>.
    $level = $p['level'] ?? 'h2';
    $level = in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) ? $level : 'h2';
    $align = $p['align'] ?? 'start';
    $align = in_array($align, ['start', 'center', 'end'], true) ? $align : 'start';
    $text = $p['text'] ?? '';
    $ed = ! empty($pvEdit) ? ' data-pb-edit="text"' : '';
@endphp
@if($text !== '')
    <{{ $level }} class="pb-block pb-heading" style="text-align:{{ $align }};"{!! $ed !!}>{{ $text }}</{{ $level }}>
@endif
