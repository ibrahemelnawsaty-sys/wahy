<!-- Glass Notifications System -->
<link rel="stylesheet" href="{{ asset('css/glass-notifications.css') }}">
<script src="{{ asset('js/glass-notifications.js') }}" defer></script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        glassNotify.toastSuccess('{{ session('success') }}');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        glassNotify.toastError('{{ session('error') }}');
    });
</script>
@endif

@if(session('warning'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        glassNotify.toastWarning('{{ session('warning') }}');
    });
</script>
@endif

@if(session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        glassNotify.toastInfo('{{ session('info') }}');
    });
</script>
@endif

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($errors->all() as $error)
            glassNotify.toastError('{{ $error }}');
        @endforeach
    });
</script>
@endif
