@extends('layouts.student-app')

@section('title', 'تيجان القيم 👑')

@push('styles')
<style>
    @keyframes crownFloat {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-10px) rotate(5deg); }
    }
    @keyframes crownGlow {
        0%, 100% { 
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.4);
        }
        50% { 
            box-shadow: 0 0 60px rgba(255, 215, 0, 0.8);
        }
    }
    .crown-card {
        animation: crownGlow 3s ease-in-out infinite;
    }
    .crown-icon {
        animation: crownFloat 4s ease-in-out infinite;
    }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
    
    <!-- Page Header -->
    <div style="text-align: center; margin-bottom: 40px;">
        <div style="font-size: 80px; margin-bottom: 15px; animation: crownFloat 3s ease-in-out infinite;">👑</div>
        <h1 style="font-size: 36px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">تيجان القيم</h1>
        <p style="color: #718096; font-size: 18px;">احصل على تاج عند إتقان قيمة كاملة!</p>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(255, 215, 0, 0.3);">
            <div style="font-size: 48px; font-weight: 700; color: #2d3436; margin-bottom: 8px;">{{ $totalCrowns }}</div>
            <div style="color: #2d3436; font-weight: 600;">تيجان مكتسبة</div>
        </div>
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);">
            <div style="font-size: 48px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $availableCrowns->count() }}</div>
            <div style="color: rgba(255,255,255,0.9); font-weight: 600;">قيم متبقية</div>
        </div>
        <div style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(72, 187, 120, 0.3);">
            <div style="font-size: 48px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $totalCrowns > 0 ? round(($totalCrowns / ($totalCrowns + $availableCrowns->count())) * 100) : 0 }}%</div>
            <div style="color: rgba(255,255,255,0.9); font-weight: 600;">نسبة الإتقان</div>
        </div>
    </div>

    <!-- Earned Crowns -->
    @if($crowns->count() > 0)
    <div style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); border-radius: 25px; padding: 35px; margin-bottom: 40px;">
        <h2 style="font-size: 28px; font-weight: 700; color: #2d3436; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 36px;">🏆</span>
            <span>تيجاني المكتسبة</span>
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px;">
            @foreach($crowns as $crown)
            <div class="crown-card" style="background: rgba(255,255,255,0.95); border-radius: 20px; padding: 30px; text-align: center; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700);"></div>
                
                <div class="crown-icon" style="font-size: 72px; margin-bottom: 15px;">👑</div>
                <div style="font-size: 32px; margin-bottom: 10px;">{{ $crown->value->icon ?? '⭐' }}</div>
                <h3 style="font-size: 22px; font-weight: 700; color: #2d3436; margin-bottom: 8px;">{{ $crown->value->name ?? 'قيمة' }}</h3>
                <p style="color: #718096; font-size: 14px; margin-bottom: 15px;">{{ $crown->value->description ?? '' }}</p>
                
                <div style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); padding: 10px 20px; border-radius: 25px; display: inline-block;">
                    <span style="font-size: 12px; color: #2d3436; font-weight: 600;">
                        🎉 أُتقنت في {{ $crown->earned_at ? \Carbon\Carbon::parse($crown->earned_at)->format('Y/m/d') : 'غير محدد' }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Available Crowns to Earn -->
    @if($availableCrowns->count() > 0)
    <div style="background: white; border-radius: 25px; padding: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
        <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 36px;">🎯</span>
            <span>تيجان تنتظرك!</span>
        </h2>
        <p style="color: #718096; margin-bottom: 25px;">أكمل جميع دروس هذه القيم للحصول على تاجها!</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
            @foreach($availableCrowns as $value)
            <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 20px; padding: 25px; border: 3px dashed #cbd5e0; transition: all 0.3s; cursor: pointer;"
                 onclick="window.location.href='{{ route('student.path') }}'"
                 onmouseover="this.style.borderColor='#667eea'; this.style.transform='translateY(-5px)';"
                 onmouseout="this.style.borderColor='#cbd5e0'; this.style.transform='translateY(0)';">
                
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="width: 60px; height: 60px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                        {{ $value->icon ?? '📚' }}
                    </div>
                    <div>
                        <h3 style="font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 4px;">{{ $value->name }}</h3>
                        <span style="font-size: 13px; color: #718096;">{{ $value->concepts->count() }} مفاهيم</span>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 48px; opacity: 0.3;">👑</div>
                    <div style="background: #667eea; color: white; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                        ابدأ الرحلة →
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Empty State -->
    @if($crowns->count() == 0 && $availableCrowns->count() == 0)
    <div style="text-align: center; padding: 60px; background: white; border-radius: 25px;">
        <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.5;">👑</div>
        <h3 style="font-size: 24px; color: #2d3748; margin-bottom: 10px;">لا توجد تيجان حالياً</h3>
        <p style="color: #718096;">ابدأ رحلتك التعليمية لتكتسب التيجان!</p>
    </div>
    @endif

</div>
@endsection
