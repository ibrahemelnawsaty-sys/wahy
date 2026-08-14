@extends('emails.layouts.base')
@section('title', '✅ تمت الموافقة على نشاطك')
@section('content')
    <p>مرحبًا {{ $teacher->name }} 👋</p>
    <p>تمت الموافقة على نشاطك «{{ $activityTitle }}».</p>
    <p>{{ $direct ? 'أصبح الآن ظاهرًا لطلابك.' : 'أصبح متاحًا في بنك الأنشطة لجميع المعلّمين.' }}</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ $url }}">عرض النشاط</a></div>
@endsection
