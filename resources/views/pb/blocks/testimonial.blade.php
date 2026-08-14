@php
    $p = $block['props'] ?? [];
    $src = trim($p['avatar'] ?? '');
    if (\Illuminate\Support\Str::startsWith($src, ['http://', 'https://'])) {
        $avatar = safe_url($src, '');
    } elseif ($src !== '' && ! str_contains($src, '..') && ! preg_match('#^[a-z][a-z0-9+.\-]*:#i', $src)) {
        $avatar = \Illuminate\Support\Facades\Storage::disk('public')->url($src);
    } else {
        $avatar = '';
    }
@endphp
@if(! empty($p['quote']))
    <figure class="pb-block pb-testimonial">
        <blockquote class="pb-testimonial-quote">{{ $p['quote'] }}</blockquote>
        <figcaption class="pb-testimonial-by">
            @if($avatar !== '')<img class="pb-testimonial-avatar" src="{{ $avatar }}" alt="{{ $p['name'] ?? '' }}" loading="lazy">@endif
            <span class="pb-testimonial-meta">
                @if(! empty($p['name']))<strong>{{ $p['name'] }}</strong>@endif
                @if(! empty($p['role']))<small>{{ $p['role'] }}</small>@endif
            </span>
        </figcaption>
    </figure>
@endif
