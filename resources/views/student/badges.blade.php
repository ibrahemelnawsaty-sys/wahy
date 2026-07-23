@extends('layouts.student-app')

@php
    // نصّ الشرط المقروء + المقياس الحاليّ لكل نوع (يُشارك بين نصّ «كيف تكسبها» وشريط التقدّم)
    if (! function_exists('wahyBadgeConditionLabel')) {
        function wahyBadgeConditionLabel($type, $value) {
            $n = (int) $value;
            switch ($type) {
                case 'activities_completed': return 'أكمل ' . $n . ' نشاطاً';
                case 'level':                return 'بلوغ المستوى ' . $n;
                case 'streak':               return 'حافظ على سلسلة ' . $n . ' يوماً';
                case 'points':               return 'اجمع ' . $n . ' نقطة خبرة';
                case 'lessons_completed':    return 'أكمل ' . $n . ' درساً';
                case 'values_mastered':      return 'أتقِن ' . $n . ' قيمة';
                default:                     return 'شارة خاصة';
            }
        }
    }
@endphp

@section('content')
<style>
    /* أزرار التصفية — قائمة على الأصناف (لا hex مضمّن من JS) كي يعالجها الوضع الليلي مباشرةً */
    .badge-filter-btn {
        padding: 12px 24px;
        background: #f7fafc;
        color: #718096;
        border: 2px solid #e2e8f0;
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    .badge-filter-btn.active {
        background: #667eea;
        color: #fff;
        border-color: #667eea;
    }
    html[data-theme="dark"] .badge-filter-btn {
        background: #1e293b;
        color: #94a3b8;
        border-color: #334155;
    }
    html[data-theme="dark"] .badge-filter-btn.active {
        background: #667eea;
        color: #fff;
        border-color: #667eea;
    }
    /* مسار شريط التقدّم — rgba لا يلتقطه dark-coverage (المبنيّ على hex) فيبقى مرئياً فوق بطاقة الوضعين */
    .badge-progress-track {
        background: rgba(148, 163, 184, 0.35);
        border-radius: 10px;
        height: 10px;
        overflow: hidden;
        margin-bottom: 6px;
    }
</style>
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
<div style="padding: 30px;">
    <!-- Page Header -->
    <div style="margin-bottom: 35px; text-align: center;">
        <h1 style="font-size: 36px; font-weight: 700; color: #1a202c; margin-bottom: 15px;">مجموعة الشارات 🏅</h1>
        <p style="color: #718096; font-size: 18px;">اجمع كل الشارات وأكمل رحلتك التعليمية — المكتسبة ملوّنة، والباقي بانتظارك</p>
    </div>

    <!-- Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; color: white; text-align: center;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 8px;">{{ $totalBadges }} / {{ $allBadges->count() }}</div>
            <div style="opacity: 0.9;">الشارات المكتسبة</div>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 25px; border-radius: 15px; color: white; text-align: center;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 8px;">{{ $rareBadges }}</div>
            <div style="opacity: 0.9;">شارات خاصة</div>
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
            <button onclick="filterBadges('all', this)" id="filter-all" class="badge-filter-btn active">
                الكل
            </button>
            <button onclick="filterBadges('earned', this)" id="filter-earned" class="badge-filter-btn">
                ✅ المكتسبة
            </button>
            <button onclick="filterBadges('locked', this)" id="filter-locked" class="badge-filter-btn">
                🔒 المقفلة
            </button>
        </div>

        <!-- Badges Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px;">
            @forelse($allBadges as $badge)
                @php
                    $isEarned  = in_array($badge->id, $earnedIds);
                    $color     = $badge->color ?: '#667eea';
                    $earnedAt  = $earnedAtById[$badge->id] ?? null;
                    // المقياس الحاليّ لنوع شرط هذه الشارة + نسبة التقدّم
                    $target    = max(1, (int) $badge->condition_value);
                    $current   = (int) ($studentMetrics[$badge->condition_type] ?? 0);
                    $progress  = $badge->condition_type ? min(100, (int) floor($current / $target * 100)) : 0;
                    $condLabel = wahyBadgeConditionLabel($badge->condition_type, $badge->condition_value);
                @endphp

                @if($isEarned)
                <!-- شارة مكتسبة — ملوّنة -->
                <div class="badge-item" data-state="earned" style="background: linear-gradient(135deg, {{ $color }} 0%, {{ $color }} 100%); padding: 30px 20px; border-radius: 15px; text-align: center; position: relative; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-8px) scale(1.05)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.18)'" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='none'">
                    <!-- Shine Effect -->
                    <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: linear-gradient(45deg, transparent, rgba(255,255,255,0.12), transparent); transform: rotate(45deg); pointer-events: none;"></div>
                    <div style="position: relative; z-index: 1;">
                        @if($badge->image)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($badge->image) }}" alt="{{ $badge->name }}" style="width: 64px; height: 64px; object-fit: contain; margin-bottom: 15px;">
                        @else
                        <div style="font-size: 56px; margin-bottom: 15px;">{{ $badge->icon ?: '🏅' }}</div>
                        @endif
                        <h3 style="font-size: 18px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $badge->name }}</h3>
                        <p style="font-size: 13px; color: rgba(255,255,255,0.92); margin-bottom: 14px; min-height: 36px;">{{ $badge->description }}</p>
                        <div style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: rgba(255,255,255,0.22); border-radius: 20px; font-size: 12px; font-weight: 700; color: white;">
                            <span>✅</span>
                            <span>{{ $earnedAt ? \Carbon\Carbon::parse($earnedAt)->format('Y/m/d') : 'مكتسبة' }}</span>
                        </div>
                    </div>
                </div>
                @else
                <!-- شارة مقفلة — باهتة مع الشرط وشريط التقدّم -->
                <div class="badge-item" data-state="locked" style="background: #f7fafc; border: 2px solid #e2e8f0; padding: 30px 20px 24px; border-radius: 15px; text-align: center; position: relative; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 30px rgba(0,0,0,0.10)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <!-- Lock badge -->
                    <div style="position: absolute; top: 12px; left: 12px; width: 30px; height: 30px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 14px;">🔒</div>
                    @if($badge->image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($badge->image) }}" alt="{{ $badge->name }}" style="width: 64px; height: 64px; object-fit: contain; margin-bottom: 15px; filter: grayscale(100%); opacity: 0.5;">
                    @else
                    <div style="font-size: 56px; margin-bottom: 15px; filter: grayscale(100%); opacity: 0.5;">{{ $badge->icon ?: '🏅' }}</div>
                    @endif
                    <h3 style="font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 8px;">{{ $badge->name }}</h3>
                    <p style="font-size: 13px; color: #718096; margin-bottom: 14px; min-height: 36px;">{{ $badge->description }}</p>

                    <!-- كيف تكسبها -->
                    <div style="font-size: 12px; font-weight: 700; color: #4a5568; margin-bottom: 10px;">🎯 {{ $condLabel }}</div>

                    @if($badge->condition_type)
                    <!-- شريط التقدّم -->
                    <div class="badge-progress-track">
                        <div style="width: {{ $progress }}%; height: 100%; background: linear-gradient(90deg, {{ $color }}, {{ $color }}); border-radius: 10px; transition: width 0.4s;"></div>
                    </div>
                    <div style="font-size: 11px; color: #718096;">{{ number_format(min($current, $target)) }} / {{ number_format($target) }} ({{ $progress }}%)</div>
                    @endif

                    @if($badge->coins_reward)
                    <div style="margin-top: 12px; font-size: 12px; color: #4a5568; font-weight: 600;">🪙 مكافأة: {{ $badge->coins_reward }} عملة</div>
                    @endif
                </div>
                @endif
            @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 60px;">
                <div style="font-size: 64px; margin-bottom: 20px;">🏅</div>
                <h3 style="font-size: 22px; font-weight: 600; color: #2d3748; margin-bottom: 10px;">لا توجد شارات متاحة بعد</h3>
                <p style="color: #718096;">سيقوم المشرف بإضافة شارات قريباً — تابع رحلتك التعليمية!</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function filterBadges(state, btn) {
    var badges = document.querySelectorAll('.badge-item');

    // تبديل الحالة عبر صنف .active (يتكفّل الوضعان الفاتح/الليلي في <style>) بدل حقن hex مضمّن
    document.querySelectorAll('.badge-filter-btn').forEach(function (b) {
        b.classList.remove('active');
    });
    if (btn) {
        btn.classList.add('active');
    }

    badges.forEach(function (badge) {
        if (state === 'all' || badge.dataset.state === state) {
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    });
}
</script>
</div>
@endsection
