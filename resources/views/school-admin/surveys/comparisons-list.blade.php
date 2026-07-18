@extends('layouts.school-admin')
@section('title', 'مقارنات الاستبيانات')
@section('page-title', '📊 مقارنات الاستبيانات القبلية / البعدية')

@push('styles')
<style>
    /* Wahy dark-mode coverage — بطاقات هذه الصفحة بألوان inline مُصلَّبة (تعتمد --w-* الموحّدة). */
    html[data-theme="dark"] .sa-surveys-list [style*="background: white"] {
        background: var(--w-card) !important;
        box-shadow: var(--w-shadow) !important;
    }
    html[data-theme="dark"] .sa-surveys-list [style*="color: #1e293b"] { color: var(--w-text) !important; }
    html[data-theme="dark"] .sa-surveys-list [style*="color: #64748b"] { color: var(--w-text-muted) !important; }
</style>
@endpush

@section('content')
<div class="sa-surveys-list" style="padding: 24px; max-width: 1200px; margin: 0 auto;">
    <p style="color: #64748b; margin-bottom: 24px;">
        اعرض مقارنة نتائج طلاب مدرستك في الاستبيانات القبلية مقابل البعدية لقياس مدى تحسّنهم.
    </p>

    @if($surveys->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
        @foreach($surveys as $survey)
        <div style="background: white; border-radius: 14px; padding: 20px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border-right: 4px solid #8b5cf6;">
            <h3 style="font-size: 16px; margin-bottom: 8px; color: #1e293b;">{{ $survey->title }}</h3>
            @if($survey->lesson)
                <p style="font-size: 13px; color: #64748b; margin-bottom: 4px;">📚 {{ $survey->lesson->title }}</p>
                @if(optional($survey->lesson->concept)->value)
                    <p style="font-size: 13px; color: #8b5cf6; margin-bottom: 12px;">💎 {{ $survey->lesson->concept->value->name }}</p>
                @endif
            @elseif($survey->value)
                <p style="font-size: 13px; color: #8b5cf6; margin-bottom: 12px;">💎 قيمة: {{ $survey->value->icon }} {{ $survey->value->name }}</p>
            @endif
            <a href="{{ route('school-admin.surveys.comparison', $survey->id) }}"
               style="display: inline-block; background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: white; padding: 8px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
                عرض المقارنة 📊
            </a>
        </div>
        @endforeach
    </div>
    <div style="margin-top: 20px;">{{ $surveys->links() }}</div>
    @else
    <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
        <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
        <p style="font-size: 16px; font-weight: 600;">لا توجد استبيانات تقييم قبلي/بعدي بعد.</p>
    </div>
    @endif
</div>
@endsection
