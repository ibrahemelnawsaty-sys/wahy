@extends('emails.layouts.base')

@section('title', '🎉 ترقية مستوى!')

@section('content')
    <p>مرحبًا {{ $student->name }} 👋</p>
    <p>{{ g('تهانينا! لقد وصلت إلى', 'تهانينا! لقد وصلتِ إلى', $student) }} <strong>المستوى {{ $newLevel }}</strong> في {{ setting('site_name', 'أثيل مكة') }}.</p>
    <p>{{ g('واصل تقدّمك الرائع — كل نشاطٍ تُكمله يقرّبك من المستوى التالي والمزيد من الشارات.', 'واصلي تقدّمكِ الرائع — كل نشاطٍ تُكملينه يقرّبكِ من المستوى التالي والمزيد من الشارات.', $student) }}</p>
    <div class="btn-wrap">
        <a class="email-btn" href="{{ url('/student/dashboard') }}">افتح لوحتك</a>
    </div>
@endsection
