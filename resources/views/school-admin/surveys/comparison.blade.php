@extends('layouts.school-admin')
@section('title', 'مقارنة استبيان')
@section('page-title', '📊 مقارنة استبيان')

@section('content')
<div style="padding: 24px; max-width: 1200px; margin: 0 auto;">
    <a href="{{ route('school-admin.surveys.comparisons') }}" style="display: inline-flex; align-items: center; gap: 6px; color: #64748b; text-decoration: none; margin-bottom: 16px; font-size: 14px;">
        <i class="fas fa-arrow-right" aria-hidden="true"></i> رجوع إلى القائمة
    </a>

    @include('partials.survey-comparison', ['survey' => $survey, 'comparisonData' => $comparisonData])
</div>
@endsection
