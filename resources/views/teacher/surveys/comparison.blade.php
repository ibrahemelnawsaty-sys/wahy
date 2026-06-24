@extends('layouts.teacher')
@section('title', 'مقارنة استبيان')

@section('content')
<div style="padding: 24px; max-width: 1200px; margin: 0 auto;">
    <a href="{{ route('teacher.surveys.comparisons') }}" style="display: inline-flex; align-items: center; gap: 6px; color: #64748b; text-decoration: none; margin-bottom: 16px; font-size: 14px;">
        <i class="fas fa-arrow-right" aria-hidden="true"></i> رجوع إلى القائمة
    </a>

    <div style="background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(5,150,105,0.04)); padding: 14px 18px; border-radius: 10px; margin-bottom: 18px; border-right: 4px solid #10b981; font-size: 14px;">
        🎓 هذه المقارنة تشمل فقط طلاب الفصول التي تُدرّسها.
    </div>

    @include('partials.survey-comparison', ['survey' => $survey, 'comparisonData' => $comparisonData])
</div>
@endsection
