{{-- هوية الموقع الموحّدة (شعار + اسم) — تقرأ من الإعدادات بدل القيم الثابتة في كل لايوت --}}
@php
    $brandName = $branding['site_name'] ?? setting('site_name', 'قيمّ');
    $brandLogo = $branding['site_logo'] ?? setting('site_logo');
@endphp
<span class="brand-identity" style="display:inline-flex;align-items:center;gap:10px;">
    @if(!empty($brandLogo))
        <img src="{{ asset('storage/app/public/data/' . $brandLogo) }}" alt="{{ $brandName }}" style="height:34px;width:auto;border-radius:8px;">
    @else
        <span class="brand-icon" style="font-size:1.6rem;line-height:1;">🌟</span>
    @endif
    <span class="brand-name">{{ $brandName }}</span>
</span>
