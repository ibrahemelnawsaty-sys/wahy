{{-- رسائل الفلاش الموحّدة (success/error/warning/info) — كانت تُعرض في لايوت واحد فقط --}}
@php
    $flashes = [
        'success' => ['#16a34a', '#dcfce7', '✅'],
        'error'   => ['#dc2626', '#fee2e2', '⛔'],
        'warning' => ['#d97706', '#fef3c7', '⚠️'],
        'info'    => ['#2563eb', '#dbeafe', 'ℹ️'],
    ];
@endphp
@foreach($flashes as $key => [$color, $bg, $icon])
    @if(session($key))
    <div class="app-flash" role="alert" style="position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:10000;min-width:280px;max-width:90vw;background:{{ $bg }};color:{{ $color }};border:1px solid {{ $color }};border-radius:12px;padding:12px 18px;box-shadow:0 6px 24px rgba(0,0,0,.15);display:flex;align-items:center;gap:10px;font-weight:600;font-size:14px;">
        <span>{{ $icon }}</span>
        <span style="flex:1;">{{ session($key) }}</span>
        <button type="button" onclick="this.parentElement.remove()" aria-label="إغلاق" style="background:none;border:none;color:{{ $color }};cursor:pointer;font-size:18px;line-height:1;">×</button>
    </div>
    @endif
@endforeach
@if(session('success') || session('error') || session('warning') || session('info'))
<script>
    setTimeout(function () {
        document.querySelectorAll('.app-flash').forEach(function (el) { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(function () { el.remove(); }, 400); });
    }, 5000);
</script>
@endif
