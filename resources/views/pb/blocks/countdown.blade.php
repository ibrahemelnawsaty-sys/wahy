@php $p = $block['props'] ?? []; $date = trim((string) ($p['date'] ?? '')); @endphp
@if($date !== '')
    <div class="pb-block pb-countdown" data-pb-countdown data-target="{{ $date }}">
        @if(!empty($p['label']))<div class="pb-cd-label">{{ $p['label'] }}</div>@endif
        <div class="pb-cd-grid">
            <div><span data-cd="d">0</span><small>يوم</small></div>
            <div><span data-cd="h">0</span><small>ساعة</small></div>
            <div><span data-cd="m">0</span><small>دقيقة</small></div>
            <div><span data-cd="s">0</span><small>ثانية</small></div>
        </div>
    </div>
@endif
