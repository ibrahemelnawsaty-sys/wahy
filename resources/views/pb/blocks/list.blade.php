@php
    $p = $block['props'] ?? [];
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
    $tag = ! empty($p['ordered']) ? 'ol' : 'ul'; // قائمة سماح ثنائيّة
@endphp
@if(count($items))
    <{{ $tag }} class="pb-block pb-list">
        @foreach($items as $item)
            @php $item = is_array($item) ? $item : []; @endphp
            @if(! empty($item['text']))<li>{{ $item['text'] }}</li>@endif
        @endforeach
    </{{ $tag }}>
@endif
