@php
    $p = $block['props'] ?? [];
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
    $resolve = function ($src) {
        $src = trim((string) $src);
        if (\Illuminate\Support\Str::startsWith($src, ['http://', 'https://'])) {
            return safe_url($src, '');
        }
        if ($src !== '' && ! str_contains($src, '..') && ! preg_match('#^[a-z][a-z0-9+.\-]*:#i', $src)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($src);
        }

        return '';
    };
@endphp
@if(count($items))
    <div class="pb-block pb-gallery">
        @foreach($items as $it)
            @php $it = is_array($it) ? $it : []; $u = $resolve($it['src'] ?? ''); @endphp
            @if($u !== '')
                <figure class="pb-gallery-item"><img src="{{ $u }}" alt="{{ $it['alt'] ?? '' }}" loading="lazy"></figure>
            @endif
        @endforeach
    </div>
@endif
