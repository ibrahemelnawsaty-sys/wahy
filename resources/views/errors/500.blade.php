@extends('layouts.auth')

@section('title', '500 - خطأ في الخادم')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="text-align: center; padding: 60px 40px;">
        <div style="font-size: 120px; margin-bottom: 20px;">⚠️</div>
        
        <h1 style="font-size: 42px; font-weight: 700; color: #ef4444; margin-bottom: 15px;">500</h1>
        <h2 style="font-size: 24px; font-weight: 600; color: #1e293b; margin-bottom: 15px;">حدث خطأ غير متوقع</h2>
        
        <p style="font-size: 16px; color: #64748b; margin-bottom: 35px;">
            عذراً، حدث خطأ في الخادم.<br>
            فريقنا التقني يعمل على حل المشكلة.
        </p>
        
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="/" class="btn btn-primary" style="text-decoration: none;">
                <i class="fas fa-home"></i>
                <span>الصفحة الرئيسية</span>
            </a>
            
            <a href="javascript:location.reload()" class="btn btn-secondary" style="text-decoration: none;">
                <i class="fas fa-redo"></i>
                <span>إعادة المحاولة</span>
            </a>
        </div>
    </div>
</div>
@endsection
