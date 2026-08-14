@php
    $p = $block['props'] ?? [];
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
@endphp
@if(count($items))
    <div class="pb-block pb-pricing">
        @foreach($items as $item)
            @php
                $item = is_array($item) ? $item : [];
                $features = preg_split('/\r\n|\r|\n/', (string) ($item['features'] ?? ''));
            @endphp
            <div class="pb-price-card{{ ! empty($item['featured']) ? ' is-featured' : '' }}">
                @if(! empty($item['name']))<h3 class="pb-price-name">{{ $item['name'] }}</h3>@endif
                @if(! empty($item['price']))
                    <div class="pb-price-amount">{{ $item['price'] }}<small>{{ $item['period'] ?? '' }}</small></div>
                @endif
                <ul class="pb-price-features">
                    @foreach($features as $f)@if(trim($f) !== '')<li>{{ trim($f) }}</li>@endif @endforeach
                </ul>
                @if(! empty($item['button_text']))
                    <a class="pb-btn pb-btn-primary" href="{{ safe_url($item['button_link'] ?? '#') }}">{{ $item['button_text'] }}</a>
                @endif
            </div>
        @endforeach
    </div>
@endif
