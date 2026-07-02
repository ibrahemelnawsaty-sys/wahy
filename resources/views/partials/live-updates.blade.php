{{-- Wahy Live Updates — يُضمَّن في كل لايوت (قبل </body>) لتحديث الشارات/العدّادات لحظياً دون refresh.
     يعتمد على وجود meta csrf-token في <head> (متوفّر عبر partials/head-meta). --}}
<style>
    @keyframes wahyLiveBump {
        0%   { transform: scale(1); }
        30%  { transform: scale(1.35); }
        100% { transform: scale(1); }
    }
    .live-bump { animation: wahyLiveBump .5s ease; }
</style>
<script>
    window.WAHY_LIVE = { endpoint: '{{ route('live.summary') }}', interval: 10000 };
</script>
<script src="{{ asset('js/live-updates.js') }}?v={{ @filemtime(public_path('js/live-updates.js')) ?: '1' }}" defer></script>
