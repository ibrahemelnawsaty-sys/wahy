@php
    $p = $block['props'] ?? [];
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
@endphp
<section class="pb-block pb-features">
    @if(!empty($p['heading']))
        <h2 class="pb-features-heading">{{ $p['heading'] }}</h2>
    @endif
    <div class="pb-features-grid">
        @foreach($items as $item)
            @php $item = is_array($item) ? $item : []; @endphp
            <div class="pb-feature-card">
                @if(!empty($item['icon']))
                    <div class="pb-feature-icon">{{ $item['icon'] }}</div>
                @endif
                @if(!empty($item['title']))
                    <h3 class="pb-feature-title">{{ $item['title'] }}</h3>
                @endif
                @if(!empty($item['text']))
                    <p class="pb-feature-text">{{ $item['text'] }}</p>
                @endif
            </div>
        @endforeach
    </div>
</section>
