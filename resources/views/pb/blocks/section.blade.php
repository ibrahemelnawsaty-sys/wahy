@php
    $p = $block['props'] ?? [];
    $full = ($p['width'] ?? 'boxed') === 'full';
@endphp
{{-- قسم/حاوية: خلفيّته تُضبَط عبر _style (تُصيَّرها الـblockwrap الخارجيّة)، ومحتواه كتل متداخلة
     تُصيَّر بنفس المُصيِّر الآمن. pvTop=false: المسار متداخل فلا يُلَفّ الأبناء بـ«انقر للتحرير». --}}
<div class="pb-block pb-section">
    <div class="pb-section-inner{{ $full ? ' is-full' : '' }}">
        @include('pb.renderer', ['blocks' => $block['children'] ?? [], 'pvTop' => false])
    </div>
</div>
