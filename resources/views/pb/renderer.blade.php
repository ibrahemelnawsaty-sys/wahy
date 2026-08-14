{{--
    المُصيِّر الآمن لشجرة الكتل. يستقبل $blocks (مُحضَّرة عبر App\PageBuilder\BlockTree::prepare)
    ويُصيِّر كلّ كتلة عبر مكوّنها الموثوق من السجلّ فقط. نوعٌ خارج السجلّ لا يُصيَّر إطلاقاً (منع XSS).
    $pvTop (في المعاينة فقط): يلفّ كتل المستوى الأعلى بـdata-pb-path لتفعيل «انقر للتحرير».
--}}
@foreach(($blocks ?? []) as $__i => $block)
    @php
        $__pbView = \App\PageBuilder\BlockRegistry::view($block['type'] ?? '');
        // تصميم الكتلة (دفعة 3): قيمة style مُعقَّمة بقائمة سماح صارمة (لا CSS حرّ).
        $__pbStyle = \App\PageBuilder\BlockStyle::inline($block['props']['_style'] ?? null);
    @endphp
    @if($__pbView && view()->exists($__pbView))
        @if(!empty($pvTop))<div class="pb-pv-block" data-pb-path="{{ $__i }}">@endif
        @if($__pbStyle !== '')
            <div class="pb-blockwrap" style="{{ $__pbStyle }}">@include($__pbView, ['block' => $block])</div>
        @else
            @include($__pbView, ['block' => $block])
        @endif
        @if(!empty($pvTop))</div>@endif
    @endif
@endforeach
