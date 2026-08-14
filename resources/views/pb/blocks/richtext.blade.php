@php $p = $block['props'] ?? []; @endphp
{{-- الكتلة الوحيدة التي تسمح بوسوم HTML — تعقيم بقائمة سماح صارمة عبر HTMLPurifier (§10.12). --}}
<div class="pb-block pb-richtext rich-content">{!! \App\PageBuilder\HtmlPurify::clean((string) ($p['html'] ?? '')) !!}</div>
