@php $p = $block['props'] ?? []; @endphp
{{-- الكتلة الوحيدة التي تسمح بوسوم HTML — تمرّ عبر safe_html (تعقيم) بعد التطبيع. --}}
<div class="pb-block pb-richtext rich-content">{!! safe_html(normalize_message_html($p['html'] ?? '')) !!}</div>
