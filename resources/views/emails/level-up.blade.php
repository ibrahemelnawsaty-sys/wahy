@extends('emails.layouts.base')

@section('title', '🎉 ترقية مستوى!')

@section('content')
    <p>مرحبًا {{ $student->name }} 👋</p>
    <p>تهانينا! لقد وصلت إلى <strong>المستوى {{ $newLevel }}</strong> في {{ setting('site_name', 'أثيل مكة') }}.</p>
    <p>واصل تقدّمك الرائع — كل نشاطٍ تُكمله يقرّبك من المستوى التالي والمزيد من الشارات.</p>
    <div class="btn-wrap">
        <a class="email-btn" href="{{ url('/student/dashboard') }}">افتح لوحتك</a>
    </div>
@endsection
