@php
    $p = $block['props'] ?? [];
    $items = array_values(array_filter(is_array($p['items'] ?? null) ? $p['items'] : [], 'is_array'));
@endphp
@if(count($items))
    <div class="pb-block pb-tabs" data-pb-tabs>
        <div class="pb-tabs-nav" role="tablist">
            @foreach($items as $i => $item)
                <button type="button" class="pb-tab-btn{{ $i === 0 ? ' is-active' : '' }}" data-pb-tab="{{ $i }}">{{ $item['title'] ?? ('تبويب ' . ($i + 1)) }}</button>
            @endforeach
        </div>
        <div class="pb-tabs-panels">
            @foreach($items as $i => $item)
                <div class="pb-tab-panel{{ $i === 0 ? ' is-active' : '' }}" data-pb-panel="{{ $i }}">{{ $item['content'] ?? '' }}</div>
            @endforeach
        </div>
    </div>
@endif
