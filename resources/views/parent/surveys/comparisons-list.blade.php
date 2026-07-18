@extends('layouts.parent')
@section('title', 'تقدم أبنائي')

@section('content')
<div style="padding: 24px; max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: 22px; margin-bottom: 8px; color: #1e293b;">📊 تقدم أبنائي في الاستبيانات</h1>
    <p style="color: #64748b; margin-bottom: 24px;">
        شاهد كيف تحسّن أبناؤك في تطبيق القيم — مقارنة قبل وبعد الدروس.
    </p>

    @if($surveys->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
        @foreach($surveys as $survey)
        <div style="background: white; border-radius: 14px; padding: 20px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border-right: 4px solid #ec4899;">
            <h3 style="font-size: 16px; margin-bottom: 8px; color: #1e293b;">{{ $survey->title }}</h3>
            @if($survey->lesson)
                <p style="font-size: 13px; color: #64748b; margin-bottom: 4px;">📚 {{ $survey->lesson->title }}</p>
                @if(optional($survey->lesson->concept)->value)
                    <p style="font-size: 13px; color: #ec4899; margin-bottom: 12px;">💎 {{ $survey->lesson->concept->value->name }}</p>
                @endif
            @elseif($survey->value)
                <p style="font-size: 13px; color: #ec4899; margin-bottom: 12px;">💎 قيمة: {{ $survey->value->icon }} {{ $survey->value->name }}</p>
            @endif
            <a href="{{ route('parent.surveys.comparison', $survey->id) }}"
               style="display: inline-block; background: linear-gradient(135deg, #ec4899, #be185d); color: white; padding: 8px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
                عرض تقدم أبنائي 📊
            </a>
        </div>
        @endforeach
    </div>
    <div style="margin-top: 20px;">{{ $surveys->links() }}</div>
    @else
    <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
        <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
        <p style="font-size: 16px; font-weight: 600;">لم يُكمل أبناؤك أي استبيانات تقييم بعد.</p>
    </div>
    @endif
</div>
@endsection
