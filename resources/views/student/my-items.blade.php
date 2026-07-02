@extends('layouts.student-app')

@section('title', 'مقتنياتي')

@push('styles')
<style>
    .mi-wrap { max-width: 900px; margin: 0 auto; padding: 16px; padding-bottom: 120px; }
    .mi-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 8px 0 18px; }
    .mi-title { font-size: 22px; font-weight: 800; color: var(--color-text, #0f172a); }
    .mi-shop-link { background: var(--color-card, #fff); border: 1px solid var(--color-border, rgba(0,0,0,.1)); color: var(--color-primary, #667eea); padding: 8px 14px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; }
    .mi-section-title { font-size: 15px; font-weight: 800; color: var(--color-text, #0f172a); margin: 20px 0 10px; display: flex; align-items: center; gap: 8px; }
    .mi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
    .mi-card { background: var(--color-card, #fff); border: 1.5px solid var(--color-border, rgba(0,0,0,.08)); border-radius: 16px; padding: 16px; text-align: center; box-shadow: var(--color-shadow, 0 8px 24px rgba(15,23,42,.06)); position: relative; }
    .mi-card.equipped { border-color: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,.25); }
    .mi-icon { width: 64px; height: 64px; margin: 0 auto 10px; border-radius: 16px; background: linear-gradient(135deg, #a78bfa, #ec4899); display: flex; align-items: center; justify-content: center; font-size: 34px; overflow: hidden; }
    .mi-icon img { width: 100%; height: 100%; object-fit: cover; }
    .mi-name { font-weight: 800; color: var(--color-text, #0f172a); font-size: 15px; margin-bottom: 2px; }
    .mi-rarity { font-size: 12px; color: var(--color-text-muted, #64748b); margin-bottom: 12px; }
    .mi-btn { width: 100%; border: none; border-radius: 10px; padding: 9px 12px; font-weight: 800; font-size: 14px; cursor: pointer; transition: transform .12s; }
    .mi-btn:active { transform: scale(.97); }
    .mi-btn-equip { background: linear-gradient(135deg, #8b5cf6, #ec4899); color: #fff; }
    .mi-btn-equipped { background: #10b981; color: #fff; }
    .mi-btn-use { background: linear-gradient(135deg, #f59e0b, #ef4444); color: #fff; }
    .mi-btn-used { background: var(--color-border, #e2e8f0); color: var(--color-text-muted, #64748b); cursor: default; }
    .mi-badge-tag { position: absolute; top: 10px; inset-inline-start: 10px; background: #10b981; color: #fff; font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 20px; }
    .mi-empty { text-align: center; padding: 60px 20px; color: var(--color-text-muted, #94a3b8); }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 90px;">
<div class="mi-wrap">
    <div class="mi-head">
        <div class="mi-title">🎒 مقتنياتي</div>
        <a href="{{ route('student.shop') }}" class="mi-shop-link">🛍️ المتجر</a>
    </div>

    @php
        $typeMeta = [
            'avatar'   => ['صور رمزية', '🧑‍🎨', 'equip'],
            'theme'    => ['الثيمات', '🎨', 'equip'],
            'badge'    => ['الشارات', '🏅', 'equip'],
            'power_up' => ['القوى', '⚡', 'use'],
            'special'  => ['عناصر خاصة', '✨', 'use'],
        ];
        $hasAny = $purchases->flatten()->count() > 0;
    @endphp

    @if(! $hasAny)
        <div class="mi-empty">
            <div style="font-size: 52px; margin-bottom: 12px;">🛍️</div>
            <p style="font-size: 16px; font-weight: 700;">لم تشترِ أي عنصر بعد</p>
            <a href="{{ route('student.shop') }}" class="mi-shop-link" style="display:inline-block;margin-top:14px;">تصفّح المتجر</a>
        </div>
    @else
        @foreach($typeMeta as $type => $meta)
            @php $items = $purchases[$type] ?? collect(); @endphp
            @if($items->count() > 0)
                <div class="mi-section-title">{{ $meta[1] }} {{ $meta[0] }} <span style="color:var(--color-text-muted,#94a3b8);font-weight:600;">({{ $items->count() }})</span></div>
                <div class="mi-grid">
                    @foreach($items as $item)
                        @php
                            $equipped = (bool) ($item->pivot->is_active ?? false);
                            $used = ! empty($item->pivot->used_at);
                            $action = $meta[2];
                        @endphp
                        @php $__mfa = (is_array($item->metadata) && ! empty($item->metadata['anim'])) ? $item->metadata['anim'] : null; @endphp
                        <div class="mi-card {{ $action === 'equip' && $equipped ? 'equipped' : '' }}" id="mi-{{ $item->id }}">
                            @if($action === 'equip' && $equipped)<span class="mi-badge-tag">مُجهَّز</span>@endif
                            <div class="mi-icon {{ $__mfa ? 'wahy-frame wahy-frame-' . $__mfa : '' }}">
                                @if($item->image)
                                    <img src="{{ asset('storage/app/public/data/' . $item->image) }}" alt="{{ $item->name }}" onerror="this.parentNode.textContent='{{ $item->icon ?: '🎁' }}'">
                                @else
                                    {{ $item->icon ?: '🎁' }}
                                @endif
                                @if($__mfa)@include('partials.wf-particles')@endif
                            </div>
                            <div class="mi-name">{{ $item->name }}</div>
                            <div class="mi-rarity">{{ ['common'=>'عادي','rare'=>'نادر','epic'=>'ملحمي','legendary'=>'خرافي'][$item->rarity] ?? $item->rarity }}</div>

                            @if($action === 'equip')
                                <button class="mi-btn {{ $equipped ? 'mi-btn-equipped' : 'mi-btn-equip' }}"
                                        data-id="{{ $item->id }}" onclick="equipItem(this)">
                                    {{ $equipped ? '✓ مُجهَّز — إلغاء' : 'تجهيز' }}
                                </button>
                            @else
                                <button class="mi-btn {{ $used ? 'mi-btn-used' : 'mi-btn-use' }}"
                                        data-id="{{ $item->id }}" {{ $used ? 'disabled' : '' }}
                                        onclick="useItem(this)">
                                    {{ $used ? 'مُستخدَم' : 'استخدام' }}
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    @endif
</div>
</div>

<script>
const MI_CSRF = '{{ csrf_token() }}';

function miPost(url, itemId) {
    return fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': MI_CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ item_id: itemId })
    }).then(r => r.json());
}

function equipItem(btn) {
    const id = btn.dataset.id;
    btn.disabled = true;
    miPost('{{ route('student.my-items.equip') }}', id).then(data => {
        btn.disabled = false;
        if (!data.success) { alert(data.message || 'تعذّر التجهيز'); return; }
        // إلغاء تجهيز البقية من نفس النوع بصرياً ثم تحديث الحالي
        if (data.equipped) {
            document.querySelectorAll('.mi-card.equipped .mi-btn-equipped').forEach(b => {
                if (b !== btn) {
                    const c = b.closest('.mi-card'); c.classList.remove('equipped');
                    const t = c.querySelector('.mi-badge-tag'); if (t) t.remove();
                    b.className = 'mi-btn mi-btn-equip'; b.textContent = 'تجهيز';
                }
            });
        }
        const card = btn.closest('.mi-card');
        card.classList.toggle('equipped', data.equipped);
        let tag = card.querySelector('.mi-badge-tag');
        if (data.equipped && !tag) { tag = document.createElement('span'); tag.className = 'mi-badge-tag'; tag.textContent = 'مُجهَّز'; card.prepend(tag); }
        if (!data.equipped && tag) tag.remove();
        btn.className = 'mi-btn ' + (data.equipped ? 'mi-btn-equipped' : 'mi-btn-equip');
        btn.textContent = data.equipped ? '✓ مُجهَّز — إلغاء' : 'تجهيز';
    }).catch(() => { btn.disabled = false; alert('تعذّر الاتصال'); });
}

function useItem(btn) {
    const id = btn.dataset.id;
    if (btn.disabled) return;
    if (!confirm('استخدام هذا العنصر؟ (استخدام واحد فقط)')) return;
    btn.disabled = true;
    miPost('{{ route('student.my-items.use') }}', id).then(data => {
        if (!data.success) { btn.disabled = false; alert(data.message || 'تعذّر الاستخدام'); return; }
        btn.className = 'mi-btn mi-btn-used'; btn.textContent = 'مُستخدَم';
        alert(data.message);
    }).catch(() => { btn.disabled = false; alert('تعذّر الاتصال'); });
}
</script>
@endsection
