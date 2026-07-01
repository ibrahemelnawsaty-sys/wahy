{{-- وسائط سؤال (صورة/فيديو/صوت) — تُمرَّر عبر ['q' => $question] --}}
@php
    $__m = is_array($q ?? null) ? $q : [];
    $__mt = $__m['media_type'] ?? null;
    $__mu = $__m['media_url'] ?? null;
@endphp
@if($__mt && $__mu)
<div style="margin:0 0 14px;text-align:center;">
    @if($__mt === 'image')
        <img src="{{ $__mu }}" alt="" loading="lazy"
             style="max-width:100%;max-height:300px;border-radius:12px;object-fit:contain;" onerror="this.style.display='none'">
    @elseif($__mt === 'video')
        <video src="{{ $__mu }}" controls playsinline
               style="max-width:100%;max-height:320px;border-radius:12px;"></video>
    @elseif($__mt === 'audio')
        <audio src="{{ $__mu }}" controls style="width:100%;"></audio>
    @endif
</div>
@endif
