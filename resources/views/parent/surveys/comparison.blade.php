@extends('layouts.parent')
@section('title', 'تقدم أبنائي')

@section('content')
<div style="padding: 24px; max-width: 1200px; margin: 0 auto;">
    <a href="{{ route('parent.surveys.comparisons') }}" style="display: inline-flex; align-items: center; gap: 6px; color: #64748b; text-decoration: none; margin-bottom: 16px; font-size: 14px;">
        <i class="fas fa-arrow-right" aria-hidden="true"></i> رجوع إلى القائمة
    </a>

    <div style="background: linear-gradient(135deg, rgba(236,72,153,0.08), rgba(190,24,93,0.04)); padding: 14px 18px; border-radius: 10px; margin-bottom: 18px; border-right: 4px solid #ec4899; font-size: 14px;">
        👨‍👩‍👧 هذه المقارنة تشمل أبناءك فقط — مدى تحسّنهم في تطبيق هذه القيمة.
    </div>

    @include('partials.survey-comparison', ['survey' => $survey, 'comparisonData' => $comparisonData])
</div>
@endsection
