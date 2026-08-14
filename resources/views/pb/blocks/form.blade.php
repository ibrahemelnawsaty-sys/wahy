@php $p = $block['props'] ?? []; @endphp
<div class="pb-block pb-form">
    @if(!empty($p['title']))<h3 class="pb-form-title">{{ $p['title'] }}</h3>@endif
    @if(request()->boolean('sent'))
        <div class="pb-alert pb-alert-success">شكراً — وصلتنا رسالتك وسنردّ قريباً.</div>
    @endif
    <form method="POST" action="{{ url('/pb/form-submit') }}" class="pb-form-fields">
        @csrf
        {{-- honeypot: مخفيّ عن المستخدم، يملؤه الروبوت --}}
        <input type="text" name="website" class="pb-hpot" tabindex="-1" autocomplete="off" aria-hidden="true">
        <input type="hidden" name="page_slug" value="{{ request()->segment(2) }}">
        <input type="text" name="name" placeholder="الاسم" required maxlength="120">
        <input type="email" name="email" placeholder="البريد الإلكترونيّ" required maxlength="180">
        <textarea name="message" placeholder="رسالتك" rows="4" required maxlength="3000"></textarea>
        <button type="submit" class="pb-btn pb-btn-primary">{{ $p['button_text'] ?? 'إرسال' }}</button>
    </form>
</div>
