@php
    $p = $block['props'] ?? [];
    $addr = trim((string) ($p['address'] ?? ''));
    $h = (int) ($p['height'] ?? 320);
    $h = max(150, min(800, $h));
    // iframe يُبنى خادميّاً من مضيف مسموح (خرائط جوجل)، والعنوان مُرمَّز (urlencode) — لا حقن.
    $src = $addr !== '' ? ('https://maps.google.com/maps?q=' . urlencode($addr) . '&output=embed') : '';
@endphp
@if($src !== '')
    <div class="pb-block pb-map">
        <iframe src="{{ $src }}" height="{{ $h }}" loading="lazy" title="خريطة"
                style="width:100%;border:0;border-radius:var(--pb-radius,12px)" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
@endif
