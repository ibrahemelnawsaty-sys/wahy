@extends('layouts.student-app')

@section('title', 'المتجر')

@push('styles')
<style>
    .shop-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: var(--spacing-xl) var(--spacing-md);
        padding-bottom: 120px;
    }
    
    .shop-header {
        text-align: center;
        margin-bottom: var(--spacing-2xl);
    }
    
    .shop-title {
        font-size: 36px;
        font-weight: 800;
        color: white;
        margin-bottom: var(--spacing-md);
    }
    
    .shop-subtitle {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: var(--spacing-xl);
    }
    
    .coins-balance-card {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        border-radius: var(--radius-2xl);
        padding: var(--spacing-xl);
        display: inline-flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 12px 40px rgba(255, 215, 0, 0.4);
        animation: pulse 2s ease-in-out infinite;
    }
    
    .coins-icon-large {
        font-size: 56px;
    }
    
    .coins-info {
        text-align: right;
    }
    
    .coins-label {
        font-size: 14px;
        color: rgba(124, 58, 237, 0.8);
        font-weight: 600;
    }
    
    .coins-value {
        font-size: 48px;
        font-weight: 800;
        color: #7C3AED;
    }
    
    .rewards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: var(--spacing-xl);
    }
    
    .reward-card {
        background: var(--glass-bg-medium);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-xl);
        padding: var(--spacing-xl);
        transition: all var(--transition-base);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .reward-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.5s;
        pointer-events: none; /* Issue #73 — يمنع حجب نقر زر شراء الآن في الجوال */
        z-index: 0;
    }
    
    .reward-card:hover::before {
        left: 100%;
    }
    
    .reward-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(16, 185, 129, 0.3);
        border-color: var(--color-primary);
    }
    
    .reward-card.disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .reward-card.disabled:hover {
        transform: none;
    }
    
    .reward-icon-large {
        font-size: 80px;
        text-align: center;
        margin-bottom: var(--spacing-lg);
    }
    
    .reward-name {
        font-size: 20px;
        font-weight: 700;
        color: white;
        margin-bottom: var(--spacing-sm);
        text-align: center;
    }
    
    .reward-description {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: var(--spacing-lg);
        text-align: center;
        min-height: 42px;
    }
    
    .reward-price {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 28px;
        font-weight: 700;
        color: #FFD700;
        margin-bottom: var(--spacing-md);
    }
    
    .redeem-btn {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        border: none;
        padding: 14px 28px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        transition: all var(--transition-base);
        /* ضمان استجابة الزر للنقر على الجوال */
        position: relative;
        z-index: 2;
        pointer-events: auto;
        touch-action: manipulation;
        -webkit-tap-highlight-color: rgba(255,255,255,.15);
        -webkit-appearance: none;
        appearance: none;
    }

    @media (hover: hover) {
        .redeem-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }
    }

    .redeem-btn:active {
        transform: scale(0.98);
    }

    .redeem-btn:disabled {
        background: rgba(255, 255, 255, 0.2);
        cursor: not-allowed;
        opacity: 0.5;
        pointer-events: none;
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(10px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: var(--spacing-lg);
    }

    /* P2-C: تحسين الأداء على الجوال — تعطيل backdrop-filter والـ animations */
    @media (max-width: 768px) {
        .reward-card {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            background: rgba(16, 185, 129, 0.08) !important;
        }
        .reward-card::before { display: none !important; }
        .coins-balance-card { animation: none !important; }
        .modal {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            background: rgba(0, 0, 0, 0.9);
        }
    }
    
    .modal.active {
        display: flex;
    }
    
    .modal-content {
        background: var(--glass-bg-heavy);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-2xl);
        padding: var(--spacing-2xl);
        max-width: 400px;
        width: 100%;
        text-align: center;
        animation: scaleIn 0.3s ease-out;
    }
    
    @keyframes scaleIn {
        from {
            transform: scale(0.8);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .modal-icon {
        font-size: 80px;
        margin-bottom: var(--spacing-lg);
    }
    
    .modal-title {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin-bottom: var(--spacing-sm);
    }
    
    .modal-text {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: var(--spacing-xl);
    }
    
    .modal-buttons {
        display: flex;
        gap: var(--spacing-md);
    }
    
    .modal-btn {
        flex: 1;
        padding: 14px;
        border: none;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: all var(--transition-base);
    }
    
    .modal-btn-cancel {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }
    
    .modal-btn-confirm {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
    }
    
    .modal-btn:hover {
        transform: translateY(-2px);
    }
    
    .success-animation {
        animation: successPop 0.6s ease-out;
    }
    
    @keyframes successPop {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
<div class="shop-container fade-in">
    <!-- Shop Header -->
    <div class="shop-header slide-up">
        <h1 class="shop-title">🛒 المتجر</h1>
        <p class="shop-subtitle">استبدل عملاتك بمكافآت رائعة!</p>
        
        <div class="coins-balance-card">
            <div class="coins-icon-large">💰</div>
            <div class="coins-info">
                <div class="coins-label">رصيدك</div>
                <div class="coins-value" id="coinsBalance">{{ $stats['total_coins'] }}</div>
            </div>
        </div>
    </div>

    <!-- Rewards Grid -->
    <div class="rewards-grid">
        @forelse($shopItems as $index => $item)
            @php
                $hasPurchased = auth()->user()->hasPurchased($item->id);
                $canAfford = $stats['total_coins'] >= $item->price;
            @endphp
            <div class="reward-card scale-in {{ !$canAfford || $hasPurchased ? 'disabled' : '' }}" 
                 style="animation-delay: {{ $index * 0.1 }}s;">
                @if($item->image)
                    <img src="{{ asset('storage/app/public/data/' . $item->image) }}" alt="{{ $item->name }}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 12px; margin-bottom: 16px;">
                @else
                    <div class="reward-icon-large">{{ $item->icon }}</div>
                @endif
                
                <!-- Rarity Badge -->
                @php
                    $rarityColors = [
                        'common' => 'background: linear-gradient(135deg, #9CA3AF, #6B7280);',
                        'rare' => 'background: linear-gradient(135deg, #3B82F6, #2563EB);',
                        'epic' => 'background: linear-gradient(135deg, #8B5CF6, #7C3AED);',
                        'legendary' => 'background: linear-gradient(135deg, #F59E0B, #D97706);',
                    ];
                    $rarityNames = [
                        'common' => '⚪ عادي',
                        'rare' => '🔵 نادر',
                        'epic' => '🟣 أسطوري',
                        'legendary' => '🟡 خرافي',
                    ];
                @endphp
                <div style="display: inline-block; padding: 6px 12px; border-radius: 20px; {{ $rarityColors[$item->rarity] ?? '' }} color: white; font-size: 12px; font-weight: 700; margin-bottom: 12px;">
                    {{ $rarityNames[$item->rarity] ?? $item->rarity }}
                </div>
                
                <h3 class="reward-name">{{ $item->name }}</h3>
                <p class="reward-description">{{ $item->description ?? 'منتج رائع من المتجر' }}</p>
                
                @if($item->stock !== null)
                    <p style="font-size: 12px; color: {{ $item->stock > 10 ? '#10B981' : ($item->stock > 0 ? '#F59E0B' : '#EF4444') }}; margin-bottom: 8px;">
                        المخزون: {{ $item->stock > 0 ? $item->stock : 'نفذ' }}
                    </p>
                @endif
                
                <div class="reward-price">
                    <span>🪙</span>
                    <span>{{ number_format($item->price) }}</span>
                </div>
                
                @if($hasPurchased)
                    <button class="redeem-btn" disabled style="background: #10B981;">
                        ✓ تم الشراء
                    </button>
                @elseif(!$canAfford)
                    <button class="redeem-btn" disabled>
                        رصيد غير كافٍ
                    </button>
                @elseif($item->stock !== null && $item->stock <= 0)
                    <button class="redeem-btn" disabled>
                        نفذ من المخزون
                    </button>
                @else
                    <button class="redeem-btn" type="button"
                            onclick="purchaseItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }})">
                        شراء الآن
                    </button>
                @endif
            </div>
        @empty
            <!-- Empty State -->
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: var(--glass-bg-medium); backdrop-filter: blur(40px) saturate(180%); border: 1px solid var(--glass-border); border-radius: var(--radius-xl);">
                <div style="font-size: 80px; margin-bottom: 20px;">🛒</div>
                <h3 style="font-size: 24px; font-weight: 700; color: white; margin-bottom: 12px;">لا توجد منتجات متاحة حالياً</h3>
                <p style="font-size: 16px; color: rgba(255, 255, 255, 0.7);">سيتم إضافة منتجات جديدة قريباً. تحقق لاحقاً!</p>
            </div>
        @endforelse
    </div>
</div>
</div>

<!-- Redeem Confirmation Modal -->
<div class="modal" id="redeemModal">
    <div class="modal-content">
        <div class="modal-icon" id="modalIcon">🎁</div>
        <h3 class="modal-title" id="modalTitle">تأكيد الاستبدال</h3>
        <p class="modal-text" id="modalText">هل تريد استبدال هذه المكافأة؟</p>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-cancel" onclick="closeModal()">إلغاء</button>
            <button class="modal-btn modal-btn-confirm" onclick="confirmRedeem()">تأكيد</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal" id="successModal">
    <div class="modal-content">
        <div class="modal-icon success-animation">🎉</div>
        <h3 class="modal-title">تم الاستبدال بنجاح!</h3>
        <p class="modal-text" id="successText">تم إضافة المكافأة إلى حسابك</p>
        <button class="modal-btn modal-btn-confirm" onclick="closeSuccessModal()" style="width: 100%;">رائع!</button>
    </div>
</div>

@push('scripts')
<script>
    let selectedItem = null;
    
    function purchaseItem(itemId, itemName, itemPrice) {
        selectedItem = {
            id: itemId,
            name: itemName,
            price: itemPrice
        };
        
        document.getElementById('modalTitle').textContent = 'تأكيد الشراء';
        document.getElementById('modalText').textContent = `هل تريد شراء ${itemName} بـ ${itemPrice.toLocaleString()} عملة؟`;
        document.getElementById('redeemModal').classList.add('active');
    }
    
    function closeModal() {
        document.getElementById('redeemModal').classList.remove('active');
        selectedItem = null;
    }
    
    function confirmRedeem() {
        if (!selectedItem) return;

        // Issue #74: لا نعتمد على event.target (غير معرّف على الجوال عند onclick بلا event)
        // بل نحدّد زر التأكيد داخل النافذة مباشرةً
        const confirmBtn = document.querySelector('#redeemModal .modal-btn-confirm')
            || document.querySelector('.modal-btn-confirm');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'جارٍ الشراء...';
        }
        
        // Issue #74: قراءة الـ CSRF من meta لو موجود (أحدث من قيمة blade المُخزَّنة قد تنتهي)
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        fetch('/student/shop/purchase', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                item_id: selectedItem.id,
                price: selectedItem.price
            })
        })
        .then(async response => {
            // قراءة الـ body كنص أولاً للقدرة على عرض رسالة خطأ سيرفر مفهومة
            const text = await response.text();
            let data;
            try { data = JSON.parse(text); } catch (e) {
                throw new Error('استجابة غير متوقعة من الخادم (HTTP ' + response.status + ')');
            }
            if (!response.ok) {
                throw new Error(data.message || ('فشل الطلب (HTTP ' + response.status + ')'));
            }
            return data;
        })
        .then(data => {
            if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = 'تأكيد'; }

            if (data.success) {
                // Update balance
                const balanceEl = document.getElementById('coinsBalance');
                balanceEl.textContent = data.remaining_coins;
                balanceEl.classList.add('success-animation');
                setTimeout(() => balanceEl.classList.remove('success-animation'), 600);
                
                // Show success modal
                closeModal();
                document.getElementById('successText').textContent = data.message || `تم شراء ${selectedItem.name} بنجاح!`;
                document.getElementById('successModal').classList.add('active');
                
                // Reload page after 2 seconds to update buttons
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                alert(data.message || 'حدث خطأ. حاول مرة أخرى.');
                closeModal();
            }
        })
        .catch(error => {
            console.error('Shop purchase error:', error);
            if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = 'تأكيد'; }
            // عرض رسالة الخطأ الحقيقية بدل رسالة عامة (Issue #74)
            alert(error.message || 'تعذّر إتمام الشراء. يرجى المحاولة لاحقاً.');
            closeModal();
        });
    }
    
    function closeSuccessModal() {
        document.getElementById('successModal').classList.remove('active');
    }
    
    // Close modal on backdrop click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
</script>
@endpush
@endsection
