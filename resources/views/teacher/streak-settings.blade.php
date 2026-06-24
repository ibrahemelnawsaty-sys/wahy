@extends('layouts.teacher')

@section('title', 'مكافأة الالتزام اليومي')

@section('content')
<div class="content-header" style="margin-bottom: 30px;">
    <h1 style="font-size: 28px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">🔥 نظام مكافأة الالتزام اليومي</h1>
    <p style="color: #64748b; font-size: 15px; margin: 0;">شجع طلابك على الدخول يومياً وإكمال الأنشطة</p>
</div>

@if(session('success'))
<div style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
    ✅ {{ session('success') }}
</div>
@endif

<!-- الإحصائيات -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 16px; padding: 24px; text-align: center;">
        <div style="font-size: 40px; margin-bottom: 10px;">🔥</div>
        <div style="font-size: 32px; font-weight: 800; color: #92400e;">{{ $activeStreakCount ?? 0 }}</div>
        <div style="color: #b45309; font-weight: 600;">طالب يبني streak</div>
    </div>
    <div style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); border-radius: 16px; padding: 24px; text-align: center;">
        <div style="font-size: 40px; margin-bottom: 10px;">🏆</div>
        <div style="font-size: 32px; font-weight: 800; color: #166534;">{{ $streakBonusCount ?? 0 }}</div>
        <div style="color: #15803d; font-weight: 600;">حصلوا على مكافأة</div>
    </div>
    <div style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe); border-radius: 16px; padding: 24px; text-align: center;">
        <div style="font-size: 40px; margin-bottom: 10px;">⭐</div>
        <div style="font-size: 32px; font-weight: 800; color: #4338ca;">{{ $streakSettings['bonus_points'] }}</div>
        <div style="color: #4f46e5; font-weight: 600;">نقاط المكافأة</div>
    </div>
</div>

<!-- نموذج الإعدادات -->
<form method="POST" action="{{ route('teacher.streak.update') }}">
    @csrf
    @method('PUT')
    
    <div style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        
        <!-- تفعيل النظام -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 12px; margin-bottom: 30px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 5px 0;">✅ تفعيل النظام</h3>
                <p style="color: #64748b; margin: 0; font-size: 14px;">عند التفعيل، سيحصل الطلاب على مكافأة عند الالتزام اليومي</p>
            </div>
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="enabled" value="1" id="enabledToggle"
                       {{ $streakSettings['enabled'] ? 'checked' : '' }}
                       style="width: 24px; height: 24px; accent-color: #10b981;">
                <span style="font-weight: 700; color: {{ $streakSettings['enabled'] ? '#10b981' : '#94a3b8' }}; font-size: 16px;" id="statusText">
                    {{ $streakSettings['enabled'] ? 'مفعّل' : 'معطّل' }}
                </span>
            </label>
        </div>
        
        <div id="settingsPanel" style="{{ $streakSettings['enabled'] ? '' : 'opacity: 0.5; pointer-events: none;' }}">
            
            <!-- عدد الأيام المطلوبة -->
            <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                📊 تحديد عدد الأيام المطلوبة
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 30px;">
                <div style="background: #fef3c7; border-radius: 12px; padding: 24px;">
                    <label style="display: block; font-weight: 700; color: #92400e; margin-bottom: 12px; font-size: 16px;">
                        🎯 عدد الأيام المطلوبة
                    </label>
                    <input type="number" name="min_days" value="{{ $streakSettings['min_days'] }}" min="1" max="30"
                           style="width: 100%; padding: 16px; border: 2px solid #fcd34d; border-radius: 10px; font-size: 20px; font-weight: 700; text-align: center; background: white;">
                    <p style="color: #b45309; font-size: 13px; margin: 10px 0 0; text-align: center;">
                        عدد الأيام التي يجب على الطالب إكمالها
                    </p>
                </div>
                
                <input type="hidden" name="max_days" value="{{ $streakSettings['max_days'] }}">
                
                <div style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe); border-radius: 12px; padding: 24px;">
                    <label style="display: block; font-weight: 700; color: #4338ca; margin-bottom: 12px; font-size: 16px;">
                        💎 نقاط المكافأة
                    </label>
                    <input type="number" name="bonus_points" value="{{ $streakSettings['bonus_points'] }}" min="0" max="500"
                           style="width: 100%; padding: 16px; border: 2px solid #a5b4fc; border-radius: 10px; font-size: 20px; font-weight: 700; text-align: center; background: white;">
                    <p style="color: #4f46e5; font-size: 13px; margin: 10px 0 0; text-align: center;">
                        النقاط الثابتة عند إكمال العدد المطلوب
                    </p>
                </div>
            </div>
            
            <!-- شرح النظام -->
            <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                <h4 style="font-weight: 700; color: #475569; margin: 0 0 12px 0;">💡 كيف يعمل النظام:</h4>
                <ul style="margin: 0; padding-right: 20px; color: #64748b; line-height: 1.8;">
                    <li>عندما يكمل الطالب نشاطاً واحداً على الأقل في أي يوم، يتم احتساب اليوم</li>
                    <li><strong style="color: #10b981;">لا يشترط أن تكون الأيام متتالية</strong> - يمكن للطالب إكمالها في أي وقت</li>
                    <li>عند الوصول للعدد المطلوب من الأيام، يحصل الطالب على المكافأة الثابتة</li>
                    <li>بعد الحصول على المكافأة، يبدأ عداد جديد من الصفر</li>
                </ul>
            </div>
        </div>
        
        <!-- زر الحفظ -->
        <div style="margin-top: 30px;">
            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 18px 32px; border-radius: 12px; border: none; font-weight: 700; font-size: 18px; cursor: pointer; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                💾 حفظ الإعدادات
            </button>
        </div>
    </div>
</form>

<!-- مثال توضيحي -->
<div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 16px; padding: 30px; margin-top: 30px;">
    <h3 style="font-size: 20px; font-weight: 700; color: #92400e; margin: 0 0 20px 0;">📖 مثال عملي:</h3>
    <div style="background: white; border-radius: 12px; padding: 20px;">
        <p style="margin: 0 0 15px; color: #78350f; font-size: 15px; line-height: 1.8;">
            <strong>الإعدادات:</strong> عدد الأيام المطلوبة = <span id="exMinDays">{{ $streakSettings['min_days'] }}</span> أيام، المكافأة = <span id="exBonus">{{ $streakSettings['bonus_points'] }}</span> نقطة
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <div style="background: #fef3c7; padding: 15px; border-radius: 8px;">
                <div style="font-size: 24px; text-align: center;">📅</div>
                <div style="font-weight: 700; color: #92400e; text-align: center;">أحمد</div>
                <div style="font-size: 13px; color: #b45309; text-align: center; margin-top: 5px;">
                    أكمل 3 أيام (السبت - الثلاثاء - الخميس)
                </div>
                <div style="font-size: 14px; color: #166534; text-align: center; margin-top: 8px; font-weight: 600;">
                    ✅ يحصل على {{ $streakSettings['bonus_points'] }} نقطة
                </div>
            </div>
            <div style="background: #e0e7ff; padding: 15px; border-radius: 8px;">
                <div style="font-size: 24px; text-align: center;">📅</div>
                <div style="font-weight: 700; color: #4338ca; text-align: center;">سارة</div>
                <div style="font-size: 13px; color: #4f46e5; text-align: center; margin-top: 5px;">
                    أكملت يومين فقط حتى الآن
                </div>
                <div style="font-size: 14px; color: #b45309; text-align: center; margin-top: 8px; font-weight: 600;">
                    ⏳ تحتاج يوم إضافي
                </div>
            </div>
        </div>
        <p style="margin: 15px 0 0; color: #78350f; font-size: 14px; text-align: center; background: #dcfce7; padding: 10px; border-radius: 8px;">
            💡 <strong>لا يشترط التتالي:</strong> يمكن للطالب إكمال الأيام في أي وقت خلال الفترة المحددة
        </p>
    </div>
</div>

<script>
document.getElementById('enabledToggle').addEventListener('change', function() {
    const panel = document.getElementById('settingsPanel');
    const statusText = document.getElementById('statusText');
    
    if (this.checked) {
        panel.style.opacity = '1';
        panel.style.pointerEvents = 'auto';
        statusText.textContent = 'مفعّل';
        statusText.style.color = '#10b981';
    } else {
        panel.style.opacity = '0.5';
        panel.style.pointerEvents = 'none';
        statusText.textContent = 'معطّل';
        statusText.style.color = '#94a3b8';
    }
});

// تحديث المثال عند تغيير القيم
document.querySelector('input[name="min_days"]').addEventListener('input', function() {
    document.getElementById('exMinDays').textContent = this.value;
});
document.querySelector('input[name="bonus_points"]').addEventListener('input', function() {
    document.getElementById('exBonus').textContent = this.value;
});
</script>
@endsection
