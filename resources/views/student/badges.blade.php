@extends('layouts.student-app')

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
<div style="padding: 30px;">
    <!-- Page Header -->
    <div style="margin-bottom: 35px; text-align: center;">
        <h1 style="font-size: 36px; font-weight: 700; color: #1a202c; margin-bottom: 15px;">مجموعة الشارات 🏅</h1>
        <p style="color: #718096; font-size: 18px;">جميع الشارات التي حصلت عليها في رحلتك التعليمية</p>
    </div>

    <!-- Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; color: white; text-align: center;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 8px;">{{ $totalBadges }}</div>
            <div style="opacity: 0.9;">إجمالي الشارات</div>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 25px; border-radius: 15px; color: white; text-align: center;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 8px;">{{ $rareBadges }}</div>
            <div style="opacity: 0.9;">شارات نادرة</div>
        </div>
        <div style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); padding: 25px; border-radius: 15px; color: white; text-align: center;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 8px;">{{ $crowns }}</div>
            <div style="opacity: 0.9;">تيجان القيم</div>
        </div>
        <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 15px; color: white; text-align: center;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 8px;">{{ $recentBadges }}</div>
            <div style="opacity: 0.9;">شارات هذا الشهر</div>
        </div>
    </div>

    <!-- Crowns Section (Value Mastery) -->
    @if(count($masteredValues) > 0)
    <div style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); padding: 40px; border-radius: 20px; margin-bottom: 40px; color: white;">
        <h2 style="font-size: 28px; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 36px;">👑</span>
            تيجان القيم المتقنة
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
            @foreach($masteredValues as $value)
            <div style="background: rgba(255,255,255,0.25); padding: 25px; border-radius: 15px; text-align: center; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px) scale(1.05)'; this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.background='rgba(255,255,255,0.25)'">
                <div style="font-size: 48px; margin-bottom: 12px;">👑</div>
                <div style="font-size: 18px; font-weight: 700; margin-bottom: 5px;">{{ $value->name }}</div>
                <div style="font-size: 13px; opacity: 0.9;">قيمة متقنة</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Badges Grid -->
    <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
        <h2 style="font-size: 26px; font-weight: 700; color: #1a202c; margin-bottom: 30px;">جميع الشارات</h2>
        
        <!-- Filter Tabs -->
        <div style="display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap;">
            <button onclick="filterBadges('all')" id="filter-all" style="padding: 12px 24px; background: #667eea; color: white; border: none; border-radius: 25px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                الكل
            </button>
            <button onclick="filterBadges('bronze')" id="filter-bronze" style="padding: 12px 24px; background: #f7fafc; color: #718096; border: 2px solid #e2e8f0; border-radius: 25px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                🥉 برونزية
            </button>
            <button onclick="filterBadges('silver')" id="filter-silver" style="padding: 12px 24px; background: #f7fafc; color: #718096; border: 2px solid #e2e8f0; border-radius: 25px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                🥈 فضية
            </button>
            <button onclick="filterBadges('gold')" id="filter-gold" style="padding: 12px 24px; background: #f7fafc; color: #718096; border: 2px solid #e2e8f0; border-radius: 25px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                🥇 ذهبية
            </button>
            <button onclick="filterBadges('special')" id="filter-special" style="padding: 12px 24px; background: #f7fafc; color: #718096; border: 2px solid #e2e8f0; border-radius: 25px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                ⭐ خاصة
            </button>
        </div>

        <!-- Badges Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 25px;">
            @forelse($badges as $badge)
            <div class="badge-item" data-type="{{ $badge->type }}" style="background: linear-gradient(135deg, {{ $badge->color_start }} 0%, {{ $badge->color_end }} 100%); padding: 30px 20px; border-radius: 15px; text-align: center; position: relative; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-8px) scale(1.05)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='none'">
                
                <!-- Shine Effect -->
                <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent); transform: rotate(45deg);"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div style="font-size: 56px; margin-bottom: 15px;">{{ $badge->emoji }}</div>
                    <h3 style="font-size: 18px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $badge->name }}</h3>
                    <p style="font-size: 13px; color: rgba(255,255,255,0.9); margin-bottom: 12px;">{{ $badge->description }}</p>
                    <div style="font-size: 12px; color: rgba(255,255,255,0.8); margin-bottom: 15px;">
                        {{ $badge->earned_at ? \Carbon\Carbon::parse($badge->earned_at)->format('Y/m/d') : 'جديد' }}
                    </div>
                    
                    <!-- Badge Rarity -->
                    @if($badge->type === 'bronze')
                    <span style="padding: 6px 12px; background: rgba(205, 127, 50, 0.3); border-radius: 15px; font-size: 12px; font-weight: 600; color: white;">🥉 برونزية</span>
                    @elseif($badge->type === 'silver')
                    <span style="padding: 6px 12px; background: rgba(192, 192, 192, 0.3); border-radius: 15px; font-size: 12px; font-weight: 600; color: white;">🥈 فضية</span>
                    @elseif($badge->type === 'gold')
                    <span style="padding: 6px 12px; background: rgba(255, 215, 0, 0.3); border-radius: 15px; font-size: 12px; font-weight: 600; color: white;">🥇 ذهبية</span>
                    @else
                    <span style="padding: 6px 12px; background: rgba(255, 255, 255, 0.3); border-radius: 15px; font-size: 12px; font-weight: 600; color: white;">⭐ خاصة</span>
                    @endif
                </div>
            </div>
            @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 60px;">
                <div style="font-size: 64px; margin-bottom: 20px;">🏅</div>
                <h3 style="font-size: 22px; font-weight: 600; color: #2d3748; margin-bottom: 10px;">لم تحصل على شارات بعد</h3>
                <p style="color: #718096;">أكمل الدروس والأنشطة لتحصل على شارات رائعة!</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function filterBadges(type) {
    const badges = document.querySelectorAll('.badge-item');
    const buttons = document.querySelectorAll('[id^="filter-"]');
    
    // Reset all buttons
    buttons.forEach(btn => {
        btn.style.background = '#f7fafc';
        btn.style.color = '#718096';
        btn.style.border = '2px solid #e2e8f0';
    });
    
    // Highlight selected button
    const selectedBtn = document.getElementById('filter-' + type);
    selectedBtn.style.background = '#667eea';
    selectedBtn.style.color = 'white';
    selectedBtn.style.border = '2px solid #667eea';
    
    // Filter badges
    badges.forEach(badge => {
        if (type === 'all' || badge.dataset.type === type) {
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    });
}
</script>
</div>
@endsection
