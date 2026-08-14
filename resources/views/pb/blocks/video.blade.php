@php
    $p = $block['props'] ?? [];
    // iframe يُبنى خادميّاً من مضيف مسموح (معرّف مُقيَّد) — لا iframe خام من المستخدم.
    $embed = \App\PageBuilder\Embed::iframe((string) ($p['url'] ?? ''));
@endphp
@if($embed)
    <div class="pb-block pb-video">
        <div class="pb-video-frame">{!! $embed !!}</div>
        @if(! empty($p['caption']))<div class="pb-video-cap">{{ $p['caption'] }}</div>@endif
    </div>
@endif
