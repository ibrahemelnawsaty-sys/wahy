@php
    $p = $block['props'] ?? [];
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
@endphp
@if(count($items))
    <ul class="pb-block pb-iconlist">
        @foreach($items as $item)
            @php $item = is_array($item) ? $item : []; @endphp
            @if(! empty($item['text']))
                <li><span class="pb-iconlist-icon">{{ $item['icon'] ?? '•' }}</span><span>{{ $item['text'] }}</span></li>
            @endif
        @endforeach
    </ul>
@endif
