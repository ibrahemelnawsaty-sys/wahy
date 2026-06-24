@extends('layouts.teacher')

@section('page-title', 'الرسائل')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="color: #1e293b; font-size: 24px; font-weight: 700; margin-bottom: 8px;">
        <i class="fas fa-comments" style="color: #667eea;"></i> الرسائل
    </h2>
    <p style="color: #64748b; font-size: 14px;">تواصل مع طلابك وأولياء أمورهم</p>
</div>

@include('messages.index')

@endsection
