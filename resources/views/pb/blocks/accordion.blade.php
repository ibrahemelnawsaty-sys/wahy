@php
    $p = $block['props'] ?? [];
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
@endphp
@if(count($items))
    <div class="pb-block pb-accordion">
        @foreach($items as $item)
            @php $item = is_array($item) ? $item : []; @endphp
            @if(! empty($item['title']))
                <details class="pb-acc-item">
                    <summary>{{ $item['title'] }}</summary>
                    @if(! empty($item['content']))<div class="pb-acc-body">{{ $item['content'] }}</div>@endif
                </details>
            @endif
        @endforeach
    </div>
@endif
