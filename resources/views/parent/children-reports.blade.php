@extends('layouts.parent')

@section('content')
<div style="padding: 30px;">
    <!-- Page Header -->
    <div style="margin-bottom: 35px;">
        <h1 style="font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">تقارير الأبناء</h1>
        <p style="color: #718096; font-size: 16px;">متابعة شاملة لتقدم أبنائك في المنصة</p>
    </div>

    <!-- Children Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
        @foreach($children as $child)
        <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); border-top: 5px solid #667eea; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 30px rgba(0,0,0,0.08)'">
            
            <!-- Child Header -->
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #f7fafc;">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; font-weight: 700;">
                    {{ mb_substr($child->name, 0, 1, "UTF-8") }}
                </div>
                <div style="flex: 1;">
                    <h3 style="font-size: 22px; font-weight: 700; color: #2d3748; margin-bottom: 5px;">{{ $child->name }}</h3>
                    <p style="font-size: 14px; color: #718096;">{{ $child->classroom->name ?? 'غير مسجل في فصل' }}</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 12px; text-align: center; color: white;">
                    <div style="font-size: 32px; font-weight: 700; margin-bottom: 5px;">{{ $child->total_points ?? 0 }}</div>
                    <div style="font-size: 13px; opacity: 0.9;">⭐ نقطة</div>
                </div>
                <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 20px; border-radius: 12px; text-align: center; color: white;">
                    <div style="font-size: 32px; font-weight: 700; margin-bottom: 5px;">{{ $child->streak_days ?? 0 }}</div>
                    <div style="font-size: 13px; opacity: 0.9;">🔥 يوم متتالي</div>
                </div>
            </div>

            <!-- Additional Stats -->
            <div style="display: grid; gap: 12px; margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f7fafc; border-radius: 10px;">
                    <span style="font-size: 14px; color: #718096;">الشارات المحققة</span>
                    <span style="font-size: 16px; font-weight: 700; color: #667eea;">🏅 {{ $child->badges_count ?? 0 }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f7fafc; border-radius: 10px;">
                    <span style="font-size: 14px; color: #718096;">التيجان المكتسبة</span>
                    <span style="font-size: 16px; font-weight: 700; color: #ffd700;">👑 {{ $child->crowns_count ?? 0 }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f7fafc; border-radius: 10px;">
                    <span style="font-size: 14px; color: #718096;">الأنشطة المكتملة</span>
                    <span style="font-size: 16px; font-weight: 700; color: #43e97b;">✅ {{ $child->completed_activities ?? 0 }}</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 13px; font-weight: 600; color: #2d3748;">التقدم العام</span>
                    <span style="font-size: 13px; font-weight: 700; color: #667eea;">{{ $child->overall_progress ?? 0 }}%</span>
                </div>
                <div style="width: 100%; height: 10px; background: #f7fafc; border-radius: 20px; overflow: hidden;">
                    <div style="width: {{ $child->overall_progress ?? 0 }}%; height: 100%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); transition: width 0.5s ease;"></div>
                </div>
            </div>

            <!-- View Details Button -->
            <a href="{{ route('parent.child.details', $child->id) }}" style="display: block; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(102, 126, 234, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(102, 126, 234, 0.3)'">
                عرض التفاصيل الكاملة
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
