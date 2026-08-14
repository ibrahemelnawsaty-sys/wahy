@php $p = $block['props'] ?? []; @endphp
@if(! empty($p['text']))
    <figure class="pb-block pb-quote">
        <blockquote>{{ $p['text'] }}</blockquote>
        @if(! empty($p['cite']))<figcaption>— {{ $p['cite'] }}</figcaption>@endif
    </figure>
@endif
