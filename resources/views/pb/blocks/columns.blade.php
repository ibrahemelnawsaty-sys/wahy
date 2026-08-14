@php
    $p = $block['props'] ?? [];
    $count = (int) min(max((int) ($p['count'] ?? 2), 1), 6);
@endphp
<div class="pb-block pb-columns" style="--pb-cols:{{ $count }};">
    {{-- الكتل الأبناء تتدفّق في شبكة الأعمدة — تُصيَّر بنفس المُصيِّر الآمن (تداخل).
         pvTop=false: لا تُلَفّ الأبناء بـdata-pb-path ولا تُفعَّل فيها الكتابة في المكان (المسار متداخل). --}}
    @include('pb.renderer', ['blocks' => $block['children'] ?? [], 'pvTop' => false])
</div>
