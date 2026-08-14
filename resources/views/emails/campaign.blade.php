@extends('emails.layouts.base')

{{-- محتوى الحملة مؤلَّف من الأدمن ومُعقَّم بـsafe_html عند الحفظ؛ يُصيَّر داخل القالب الموحّد الآمن. --}}
@section('content')
    {!! $bodyHtml !!}
@endsection
