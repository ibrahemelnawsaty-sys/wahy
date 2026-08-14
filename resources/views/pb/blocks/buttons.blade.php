@php
    $p = $block['props'] ?? [];
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
    $align = $p['align'] ?? 'start';
    $align = in_array($align, ['start', 'center', 'end'], true) ? $align : 'start';
    $justify = $align === 'center' ? 'center' : ($align === 'end' ? 'flex-end' : 'flex-start');
@endphp
@if(count($items))
    <div class="pb-block pb-buttons" style="justify-content:{{ $justify }};">
        @foreach($items as $item)
            @php
                $item = is_array($item) ? $item : [];
                $st = $item['style'] ?? 'primary';
                $st = in_array($st, ['primary', 'secondary', 'ghost'], true) ? $st : 'primary';
            @endphp
            @if(! empty($item['text']))
                <a class="pb-btn pb-btn-{{ $st }}" href="{{ safe_url($item['link'] ?? '#') }}">{{ $item['text'] }}</a>
            @endif
        @endforeach
    </div>
@endif
