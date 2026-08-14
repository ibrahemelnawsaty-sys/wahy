@php
    $p = $block['props'] ?? [];
    $ed = ! empty($pvEdit) ? ' data-pb-edit="text"' : '';
@endphp
@if(! empty($p['text']))
    <figure class="pb-block pb-quote">
        <blockquote{!! $ed !!}>{{ $p['text'] }}</blockquote>
        @if(! empty($p['cite']))<figcaption>— {{ $p['cite'] }}</figcaption>@endif
    </figure>
@endif
