{{--
    المُصيِّر الآمن لشجرة الكتل. يستقبل $blocks (مُحضَّرة عبر App\PageBuilder\BlockTree::prepare)
    ويُصيِّر كلّ كتلة عبر مكوّنها الموثوق من السجلّ فقط. نوعٌ خارج السجلّ لا يُصيَّر إطلاقاً (منع XSS).
--}}
@foreach(($blocks ?? []) as $block)
    @php $__pbView = \App\PageBuilder\BlockRegistry::view($block['type'] ?? ''); @endphp
    @if($__pbView && view()->exists($__pbView))
        @include($__pbView, ['block' => $block])
    @endif
@endforeach
