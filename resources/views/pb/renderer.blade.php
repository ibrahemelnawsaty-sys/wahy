{{--
    المُصيِّر الآمن لشجرة الكتل. يستقبل $blocks (مُحضَّرة عبر App\PageBuilder\BlockTree::prepare)
    ويُصيِّر كلّ كتلة عبر مكوّنها الموثوق من السجلّ فقط. نوعٌ خارج السجلّ لا يُصيَّر إطلاقاً (منع XSS).
    $pvTop (في المعاينة فقط): يلفّ كتل المستوى الأعلى بـdata-pb-path لتفعيل «انقر للتحرير».
--}}
@foreach(($blocks ?? []) as $__i => $block)
    @php
        $__pbView = \App\PageBuilder\BlockRegistry::view($block['type'] ?? '');
        // تصميم الكتلة + الإظهار حسب الجهاز — مُعقَّمان بقائمة سماح صارمة (لا CSS/صنف حرّ).
        $__pbSt = $block['props']['_style'] ?? null;
        $__pbStyle = \App\PageBuilder\BlockStyle::inline($__pbSt);
        $__pbClass = \App\PageBuilder\BlockStyle::classes($__pbSt);
        $__pbWrap = $__pbStyle !== '' || $__pbClass !== '';
        $__pbStyleAttr = $__pbStyle !== '' ? ' style="' . e($__pbStyle) . '"' : '';
        $__pbClassAttr = $__pbClass !== '' ? ' ' . $__pbClass : '';
    @endphp
    @if($__pbView && view()->exists($__pbView))
        @if(!empty($pvTop))<div class="pb-pv-block" data-pb-path="{{ $__i }}">@endif
        @if($__pbWrap)
            <div class="pb-blockwrap{{ $__pbClassAttr }}"{!! $__pbStyleAttr !!}>@include($__pbView, ['block' => $block, 'pvEdit' => ! empty($pvTop)])</div>
        @else
            @include($__pbView, ['block' => $block, 'pvEdit' => ! empty($pvTop)])
        @endif
        @if(!empty($pvTop))</div>@endif
    @endif
@endforeach
