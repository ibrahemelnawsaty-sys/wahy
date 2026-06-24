@extends('layouts.student-app')

@section('title', 'فرقي 👥')

@push('styles')
<style>
    @keyframes teamPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }
    .team-card {
        transition: all 0.3s;
    }
    .team-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 60px rgba(102, 126, 234, 0.3);
    }
    .member-avatar {
        transition: all 0.3s;
    }
    .member-avatar:hover {
        transform: scale(1.2);
        z-index: 10;
    }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
    
    <!-- Page Header -->
    <div style="text-align: center; margin-bottom: 40px;">
        <div style="font-size: 80px; margin-bottom: 15px;">👥</div>
        <h1 style="font-size: 36px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">فرقي</h1>
        <p style="color: #718096; font-size: 18px;">تعاون مع زملائك لتحقيق إنجازات أكبر!</p>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);">
            <div style="font-size: 48px; margin-bottom: 10px;">👥</div>
            <div style="font-size: 36px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $totalTeams }}</div>
            <div style="color: rgba(255,255,255,0.9); font-weight: 600;">فرق منضم إليها</div>
        </div>
        @php
            $totalTeamPoints = $teams->sum('total_points');
            $totalTeamActivities = $teams->sum('total_activities');
        @endphp
        <div style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(72, 187, 120, 0.3);">
            <div style="font-size: 48px; margin-bottom: 10px;">⭐</div>
            <div style="font-size: 36px; font-weight: 700; color: white; margin-bottom: 8px;">{{ number_format($totalTeamPoints) }}</div>
            <div style="color: rgba(255,255,255,0.9); font-weight: 600;">نقاط الفرق</div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(245, 87, 108, 0.3);">
            <div style="font-size: 48px; margin-bottom: 10px;">✅</div>
            <div style="font-size: 36px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $totalTeamActivities }}</div>
            <div style="color: rgba(255,255,255,0.9); font-weight: 600;">أنشطة مكتملة</div>
        </div>
    </div>

    <!-- Teams List -->
    @if($teams->count() > 0)
    <div style="display: grid; gap: 30px;">
        @foreach($teams as $team)
        <div class="team-card" style="background: white; border-radius: 25px; padding: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08); overflow: hidden; position: relative;">
            <!-- Team Color Bar -->
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, {{ $team->color ?? '#667eea' }}, {{ $team->secondary_color ?? '#764ba2' }});"></div>
            
            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
                <!-- Team Info -->
                <div style="flex: 1; min-width: 250px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, {{ $team->color ?? '#667eea' }} 0%, {{ $team->secondary_color ?? '#764ba2' }} 100%); border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 36px;">
                            {{ $team->icon ?? '👥' }}
                        </div>
                        <div>
                            <h3 style="font-size: 24px; font-weight: 700; color: #2d3748; margin-bottom: 5px;">{{ $team->name }}</h3>
                            <span style="font-size: 14px; color: #718096;">
                                أنشئ بواسطة {{ $team->creator->name ?? 'المعلم' }}
                            </span>
                        </div>
                    </div>
                    
                    @if($team->description)
                    <p style="color: #4a5568; font-size: 15px; line-height: 1.7; margin-bottom: 15px;">
                        {{ $team->description }}
                    </p>
                    @endif
                    
                    <!-- Team Stats -->
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div style="background: #f0fff4; padding: 12px 20px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 20px;">⭐</span>
                            <span style="font-weight: 700; color: #2d3748;">{{ number_format($team->total_points) }}</span>
                            <span style="color: #718096; font-size: 13px;">نقطة</span>
                        </div>
                        <div style="background: #ebf4ff; padding: 12px 20px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 20px;">✅</span>
                            <span style="font-weight: 700; color: #2d3748;">{{ $team->total_activities }}</span>
                            <span style="color: #718096; font-size: 13px;">نشاط</span>
                        </div>
                        <div style="background: #faf5ff; padding: 12px 20px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 20px;">👥</span>
                            <span style="font-weight: 700; color: #2d3748;">{{ $team->members->count() }}</span>
                            <span style="color: #718096; font-size: 13px;">عضو</span>
                        </div>
                    </div>
                </div>
                
                <!-- Team Members -->
                <div style="min-width: 200px;">
                    <div style="font-size: 14px; color: #718096; font-weight: 600; margin-bottom: 12px;">أعضاء الفريق</div>
                    <div style="display: flex; flex-wrap: wrap; gap: -10px;">
                        @foreach($team->members->take(8) as $index => $member)
                        <div class="member-avatar" style="width: 45px; height: 45px; border-radius: 50%; border: 3px solid white; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; margin-left: {{ $index > 0 ? '-10px' : '0' }}; position: relative; cursor: pointer;" title="{{ $member->name }}">
                            @if($member->avatar)
                                <img src="{{ $member->avatar_url }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                                {{ mb_substr($member->name, 0, 1) }}
                            @endif
                        </div>
                        @endforeach
                        @if($team->members->count() > 8)
                        <div style="width: 45px; height: 45px; border-radius: 50%; border: 3px solid white; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #718096; font-weight: 700; font-size: 12px; margin-left: -10px;">
                            +{{ $team->members->count() - 8 }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Your Role Badge -->
            @php
                $myRole = $team->members->where('id', auth()->id())->first()?->pivot?->role ?? 'member';
                $roleConfig = [
                    'leader' => ['text' => 'قائد الفريق', 'color' => '#ffd700', 'icon' => '👑'],
                    'member' => ['text' => 'عضو', 'color' => '#667eea', 'icon' => '👤'],
                ];
                $role = $roleConfig[$myRole] ?? $roleConfig['member'];
            @endphp
            <div style="position: absolute; top: 20px; left: 20px; background: {{ $role['color'] }}; color: {{ $myRole == 'leader' ? '#2d3436' : 'white' }}; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 5px;">
                <span>{{ $role['icon'] }}</span>
                <span>{{ $role['text'] }}</span>
            </div>
            
            <!-- Join Date -->
            <div style="position: absolute; bottom: 20px; left: 20px; font-size: 12px; color: #a0aec0;">
                انضممت في {{ $team->pivot->joined_at ? \Carbon\Carbon::parse($team->pivot->joined_at)->format('Y/m/d') : 'غير محدد' }}
            </div>
        </div>
        @endforeach
    </div>
    @else
    <!-- Empty State -->
    <div style="text-align: center; padding: 80px; background: white; border-radius: 25px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
        <div style="font-size: 100px; margin-bottom: 25px; opacity: 0.6;">👥</div>
        <h3 style="font-size: 28px; color: #2d3748; margin-bottom: 15px;">لم تنضم لأي فريق بعد</h3>
        <p style="color: #718096; font-size: 18px; max-width: 400px; margin: 0 auto 25px;">
            اطلب من معلمك إضافتك لفريق للتعاون مع زملائك!
        </p>
        <div style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; border-radius: 25px; font-weight: 700;">
            <span>💡</span>
            <span>العمل الجماعي يضاعف الإنجاز!</span>
        </div>
    </div>
    @endif

</div>
@endsection
