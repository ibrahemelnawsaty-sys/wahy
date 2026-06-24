@extends('layouts.parent')

@section('content')
<div class="child-details" style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem 0;">
    <div class="container">
        <!-- Header with Back Button -->
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
            <a href="{{ route('parent.dashboard') }}" style="background: rgba(255,255,255,0.2); color: white; padding: 0.75rem 1.25rem; border-radius: 10px; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; transition: all 0.3s;">
                <i class="fas fa-arrow-right"></i> العودة
            </a>
            <h1 style="color: white; font-size: 2rem; font-weight: 700; margin: 0;">
                <i class="fas fa-user-graduate"></i> تفاصيل الطالب
            </h1>
        </div>

        <!-- Coming Soon -->
        <div style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 4rem 2rem; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
            <i class="fas fa-tools" style="font-size: 5rem; color: #667eea; margin-bottom: 1rem; opacity: 0.5;"></i>
            <h2 style="color: #2d3748; margin-bottom: 1rem;">قريباً...</h2>
            <p style="color: #718096; font-size: 1.1rem;">سيتم إضافة صفحة التفاصيل الكاملة قريباً مع:</p>
            <ul style="color: #718096; text-align: right; max-width: 600px; margin: 2rem auto; list-style: none; padding: 0;">
                <li style="padding: 0.5rem; background: #f7fafc; border-radius: 8px; margin-bottom: 0.5rem;">
                    <i class="fas fa-chart-line" style="color: #667eea; margin-left: 0.5rem;"></i>
                    رسوم بيانية تفصيلية للتقدم
                </li>
                <li style="padding: 0.5rem; background: #f7fafc; border-radius: 8px; margin-bottom: 0.5rem;">
                    <i class="fas fa-history" style="color: #10b981; margin-left: 0.5rem;"></i>
                    سجل كامل للأنشطة والإنجازات
                </li>
                <li style="padding: 0.5rem; background: #f7fafc; border-radius: 8px; margin-bottom: 0.5rem;">
                    <i class="fas fa-trophy" style="color: #f59e0b; margin-left: 0.5rem;"></i>
                    لوحة الصدارة التفصيلية
                </li>
                <li style="padding: 0.5rem; background: #f7fafc; border-radius: 8px; margin-bottom: 0.5rem;">
                    <i class="fas fa-calendar-alt" style="color: #8b5cf6; margin-left: 0.5rem;"></i>
                    جدول الدروس والواجبات
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
