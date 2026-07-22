@extends('layouts.school-admin')

@section('title', 'تفاصيل النشاط')
@section('page-title', 'تفاصيل النشاط')
@section('breadcrumb')
    <a href="{{ route('school-admin.activity-approvals') }}">اعتماد الأنشطة</a> / تفاصيل النشاط
@endsection

@section('content')
    @include('activities.partials.detail')
@endsection
