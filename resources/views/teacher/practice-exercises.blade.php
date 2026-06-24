@extends('layouts.teacher')

@section('title', 'إدارة التمارين')

@section('content')
<div class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="page-title">📝 إدارة التمارين</h1>
        <a href="{{ route('teacher.exercises.create') }}" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 15px; box-shadow: 0 4px 15px rgba(102,126,234,0.4); transition: transform 0.2s;">
            ➕ إنشاء تمرين جديد
        </a>
    </div>

    {{-- إحصائيات --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 16px; padding: 25px; color: white; text-align: center;">
            <div style="font-size: 40px; font-weight: 800;">{{ $stats['total'] }}</div>
            <div style="opacity: 0.9;">إجمالي التمارين</div>
        </div>
        <div style="background: linear-gradient(135deg, #10b981, #059669); border-radius: 16px; padding: 25px; color: white; text-align: center;">
            <div style="font-size: 40px; font-weight: 800;">{{ $stats['active'] }}</div>
            <div style="opacity: 0.9;">تمارين نشطة</div>
        </div>
        <div style="background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 16px; padding: 25px; color: white; text-align: center;">
            <div style="font-size: 40px; font-weight: 800;">{{ $stats['total_attempts'] }}</div>
            <div style="opacity: 0.9;">إجمالي المحاولات</div>
        </div>
    </div>

    {{-- قائمة التمارين --}}
    @forelse($exercises as $exercise)
    <div style="background: white; border-radius: 16px; padding: 24px; margin-bottom: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; border-right: 5px solid {{ $exercise->is_active ? '#10b981' : '#94a3b8' }};">
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">{{ $exercise->title }}</h3>
                @if($exercise->is_active)
                    <span style="background: #dcfce7; color: #16a34a; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">نشط</span>
                @else
                    <span style="background: #f1f5f9; color: #64748b; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">معطل</span>
                @endif
                <span style="background: {{ $exercise->difficulty == 'easy' ? '#dbeafe' : ($exercise->difficulty == 'medium' ? '#fef3c7' : '#fecaca') }}; color: {{ $exercise->difficulty == 'easy' ? '#2563eb' : ($exercise->difficulty == 'medium' ? '#d97706' : '#dc2626') }}; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    {{ $exercise->difficulty == 'easy' ? 'سهل' : ($exercise->difficulty == 'medium' ? 'متوسط' : 'صعب') }}
                </span>
            </div>
            <div style="display: flex; gap: 20px; color: #64748b; font-size: 13px;">
                <span>📋 {{ count($exercise->questions ?? []) }} سؤال</span>
                <span>👥 {{ $exercise->attempts_count }} محاولة</span>
                @if($exercise->time_limit)
                    <span>⏱️ {{ $exercise->time_limit }} دقيقة</span>
                @endif
                @if($exercise->classroom)
                    <span>🏫 {{ $exercise->classroom->name }}</span>
                @else
                    <span>🌐 كل الفصول</span>
                @endif
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('teacher.exercises.results', $exercise->id) }}" style="background: #eff6ff; color: #2563eb; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600;">📊 النتائج</a>
            <a href="{{ route('teacher.exercises.edit', $exercise->id) }}" style="background: #fef3c7; color: #d97706; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600;">✏️ تعديل</a>
            <button onclick="deleteExercise({{ $exercise->id }})" style="background: #fef2f2; color: #dc2626; padding: 8px 16px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer;">🗑️ حذف</button>
        </div>
    </div>
    @empty
    <div style="text-align: center; padding: 60px; background: white; border-radius: 16px;">
        <div style="font-size: 60px; margin-bottom: 15px;">📝</div>
        <h3 style="color: #475569; margin-bottom: 10px;">لا توجد تمارين بعد</h3>
        <p style="color: #94a3b8;">ابدأ بإنشاء أول تمرين لطلابك</p>
        <a href="{{ route('teacher.exercises.create') }}" style="display: inline-block; margin-top: 15px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 700;">➕ إنشاء تمرين</a>
    </div>
    @endforelse

    {{ $exercises->links() }}
</div>

<script>
function deleteExercise(id) {
    if (!confirm('هل أنت متأكد من حذف هذا التمرين؟')) return;
    fetch(`/teacher/exercises/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(d => {
        if (d.success) location.reload();
        else alert(d.message || 'تعذّر الحذف');
    })
    .catch(err => alert('فشل حذف التمرين: ' + err.message));
}
</script>
@endsection
