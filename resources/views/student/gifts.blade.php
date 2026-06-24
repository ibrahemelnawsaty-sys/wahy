@extends('layouts.student-app')

@section('title', 'هدايا ومدح ولي الأمر 💝')

@push('styles')
<style>
    @keyframes heartPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    @keyframes giftBounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .praise-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 50px rgba(245, 101, 101, 0.3);
    }
    .gift-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
    }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
    
    <!-- Page Header -->
    <div style="text-align: center; margin-bottom: 40px;">
        <div style="font-size: 80px; margin-bottom: 15px;">💝</div>
        <h1 style="font-size: 36px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">هدايا ومدح ولي الأمر</h1>
        <p style="color: #718096; font-size: 18px;">كل الحب والتشجيع من والديك!</p>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(245, 87, 108, 0.3);">
            <div style="font-size: 48px; margin-bottom: 10px; animation: heartPulse 2s infinite;">❤️</div>
            <div style="font-size: 36px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $totalPraises }}</div>
            <div style="color: rgba(255,255,255,0.9); font-weight: 600;">مدح مستلم</div>
        </div>
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);">
            <div style="font-size: 48px; margin-bottom: 10px; animation: giftBounce 3s infinite;">🎁</div>
            <div style="font-size: 36px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $totalGifts }}</div>
            <div style="color: rgba(255,255,255,0.9); font-weight: 600;">هدية مستلمة</div>
        </div>
        <div style="background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%); padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(253, 203, 110, 0.3);">
            <div style="font-size: 48px; margin-bottom: 10px;">⭐</div>
            <div style="font-size: 36px; font-weight: 700; color: #2d3436; margin-bottom: 8px;">{{ $totalPraises + $totalGifts }}</div>
            <div style="color: #2d3436; font-weight: 600;">إجمالي التشجيعات</div>
        </div>
    </div>

    <!-- Recent Praises -->
    @if($praises->count() > 0)
    <div style="background: white; border-radius: 25px; padding: 35px; margin-bottom: 30px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
        <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 36px;">❤️</span>
            <span>كلمات التشجيع</span>
            <span style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; margin-right: auto;">{{ $totalPraises }}</span>
        </h2>
        
        <div style="display: grid; gap: 20px;">
            @foreach($praises as $praise)
            <div class="praise-card" style="background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%); border-radius: 20px; padding: 25px; border-right: 5px solid #f56565; transition: all 0.3s; cursor: pointer;">
                <div style="display: flex; align-items: start; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;">
                        @if($praise->parent && $praise->parent->avatar)
                            <img src="{{ $praise->parent->avatar_url }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            👨‍👩‍👦
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div style="font-weight: 700; color: #2d3748; font-size: 18px;">
                                {{ $praise->parent->name ?? 'ولي الأمر' }}
                            </div>
                            <div style="font-size: 12px; color: #718096;">
                                {{ $praise->created_at->diffForHumans() }}
                            </div>
                        </div>
                        @php
                            $praiseMsg = trim($praise->praise_message ?? $praise->message ?? '');
                            $praiseTypeFallback = match($praise->praise_type ?? '') {
                                'celebration' => '🎉 تهنئة على إنجازك الرائع!',
                                'motivation'  => '💪 استمر، أنت على الطريق الصحيح!',
                                'encouragement' => '🌟 أحسنت! نحن فخورون بك',
                                default       => '❤️ كلمة تشجيع من والدك',
                            };
                        @endphp
                        <p style="color: #4a5568; font-size: 16px; line-height: 1.7; background: white; padding: 15px; border-radius: 12px; margin: 0;">
                            "{{ $praiseMsg !== '' ? $praiseMsg : $praiseTypeFallback }}"
                        </p>
                        @if($praise->badge)
                        <div style="margin-top: 12px; display: inline-flex; align-items: center; gap: 6px; background: #f56565; color: white; padding: 6px 14px; border-radius: 20px; font-size: 13px;">
                            <span>{{ $praise->badge }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Gifts -->
    @if($gifts->count() > 0)
    <div style="background: white; border-radius: 25px; padding: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
        <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 36px;">🎁</span>
            <span>الهدايا</span>
            <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; margin-right: auto;">{{ $totalGifts }}</span>
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
            @foreach($gifts as $gift)
            @php
                // الحقول الفعلية في نموذج ParentGift: gift_type / gift_message / points_cost (Issue 72)
                $giftType = $gift->gift_type ?? '';
                $giftIcon = match($giftType) {
                    'star', 'stars' => '⭐',
                    'trophy' => '🏆',
                    'medal' => '🥇',
                    'crown' => '👑',
                    'heart' => '❤️',
                    'coins', 'points' => '💰',
                    default => '🎁',
                };
                $giftLabel = match($giftType) {
                    'star', 'stars' => 'نجمة تشجيعية',
                    'trophy' => 'كأس التميّز',
                    'medal' => 'ميدالية',
                    'crown' => 'تاج',
                    'heart' => 'هدية محبة',
                    default => 'هدية من ولي الأمر',
                };
                $giftMsg = trim($gift->gift_message ?? '');
            @endphp
            <div class="gift-card" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); border-radius: 20px; padding: 25px; border: 3px solid #667eea; transition: all 0.3s; cursor: pointer; text-align: center;">
                <div style="font-size: 64px; margin-bottom: 15px; animation: giftBounce 3s infinite;">
                    {{ $giftIcon }}
                </div>
                <h3 style="font-size: 20px; font-weight: 700; color: #2d3748; margin-bottom: 8px;">
                    {{ $giftLabel }}
                </h3>
                <p style="color: #718096; font-size: 14px; margin-bottom: 15px;">
                    {{ $giftMsg !== '' ? $giftMsg : 'تشجيع على تقدمك الرائع!' }}
                </p>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #c7d2fe;">
                    <div style="font-size: 13px; color: #718096;">
                        من: {{ $gift->parent->name ?? 'ولي الأمر' }}
                    </div>
                    <div style="font-size: 12px; color: #667eea; font-weight: 600;">
                        {{ $gift->created_at->diffForHumans() }}
                    </div>
                </div>

                @if(!empty($gift->points_cost))
                <div style="margin-top: 15px; background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); padding: 10px 20px; border-radius: 25px; display: inline-block;">
                    <span style="font-size: 14px; color: #2d3436; font-weight: 700;">
                        +{{ $gift->points_cost }} 💰 عملة
                    </span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Empty State -->
    @if($praises->count() == 0 && $gifts->count() == 0)
    <div style="text-align: center; padding: 80px; background: white; border-radius: 25px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
        <div style="font-size: 100px; margin-bottom: 25px; opacity: 0.6;">💝</div>
        <h3 style="font-size: 28px; color: #2d3748; margin-bottom: 15px;">لا توجد هدايا أو مدح حالياً</h3>
        <p style="color: #718096; font-size: 18px; max-width: 400px; margin: 0 auto;">
            واصل تقدمك الرائع! ولي أمرك سيرسل لك التشجيعات قريباً 💪
        </p>
    </div>
    @endif

</div>
@endsection
