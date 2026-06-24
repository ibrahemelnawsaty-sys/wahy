@extends('layouts.teacher')
@section('title', 'مقارنات الاستبيانات')

@section('content')
<div style="padding: 24px; max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: 22px; margin-bottom: 8px; color: #1e293b;">📊 مقارنات الاستبيانات القبلية / البعدية</h1>
    <p style="color: #64748b; margin-bottom: 24px;">
        قارن نتائج طلاب فصولك في الاستبيانات القبلية مقابل البعدية لتقييم تأثير الدروس على القيم.
    </p>

    @if($surveys->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
        @foreach($surveys as $survey)
        <div style="background: white; border-radius: 14px; padding: 20px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border-right: 4px solid #10b981;">
            <h3 style="font-size: 16px; margin-bottom: 8px; color: #1e293b;">{{ $survey->title }}</h3>
            @if($survey->lesson)
                <p style="font-size: 13px; color: #64748b; margin-bottom: 4px;">📚 {{ $survey->lesson->title }}</p>
            @endif
            @if($survey->lesson && $survey->lesson->concept && $survey->lesson->concept->value)
                <p style="font-size: 13px; color: #10b981; margin-bottom: 12px;">💎 {{ $survey->lesson->concept->value->name }}</p>
            @endif
            <a href="{{ route('teacher.surveys.comparison', $survey->id) }}"
               style="display: inline-block; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 8px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
                عرض مقارنة طلابي 📊
            </a>
        </div>
        @endforeach
    </div>
    <div style="margin-top: 20px;">{{ $surveys->links() }}</div>
    @else
    <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
        <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
        <p style="font-size: 16px; font-weight: 600;">لا توجد استبيانات تقييم قبلي/بعدي حالياً.</p>
    </div>
    @endif
</div>
@endsection
