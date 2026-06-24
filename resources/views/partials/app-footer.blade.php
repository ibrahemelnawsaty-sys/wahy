{{-- فوتر موحّد يقرأ من الإعدادات: footer_text + بيانات الاتصال + روابط التواصل
     يحلّ: footer_text/contact_*/social_* المُتجاهَلة، وتكرار/اختلاف الفوتر بين اللايوتات --}}
@php
    $ftText  = $branding['footer_text'] ?? setting('footer_text');
    $ftName  = $branding['site_name'] ?? setting('site_name', 'قيمّ');
    $ftEmail = setting('contact_email');
    $ftPhone = setting('contact_phone');
    $ftSocial = $branding['social_links'] ?? social_links();
    $socialIcons = ['facebook' => '📘', 'twitter' => '🐦', 'instagram' => '📷', 'linkedin' => '💼', 'youtube' => '▶️', 'whatsapp' => '💬'];
@endphp
<footer class="app-footer" style="margin-top:auto;padding:24px 16px;text-align:center;border-top:1px solid var(--color-border, rgba(0,0,0,.08));color:var(--color-text-muted, #64748b);font-size:13px;">
    @if(!empty($ftSocial))
    <div class="app-footer-social" style="display:flex;gap:14px;justify-content:center;margin-bottom:12px;">
        @foreach($ftSocial as $platform => $url)
            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $platform }}" style="font-size:20px;text-decoration:none;">{{ $socialIcons[$platform] ?? '🔗' }}</a>
        @endforeach
    </div>
    @endif
    @if($ftEmail || $ftPhone)
    <div class="app-footer-contact" style="margin-bottom:8px;">
        @if($ftEmail)<span>✉️ {{ $ftEmail }}</span>@endif
        @if($ftEmail && $ftPhone)<span style="margin:0 8px;">·</span>@endif
        @if($ftPhone)<span>📞 {{ $ftPhone }}</span>@endif
    </div>
    @endif
    <div class="app-footer-copy">
        {{ $ftText ?: '© ' . date('Y') . ' ' . $ftName . ' — جميع الحقوق محفوظة' }}
    </div>
</footer>
