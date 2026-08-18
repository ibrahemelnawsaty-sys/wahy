@extends('layouts.guide')

@section('guide_title', 'أدلّة الاستخدام')
@section('guide_desc', 'دليل مستقلّ لكلّ دور في المنصّة: مهامّه وصلاحيّاته وشاشاته واقتصاد نقاطه.')

@section('guide_body')
<main class="g-wrap g-nosidebar">
    <div class="g-hero">
        <span class="g-hero-emoji">📘</span>
        <h1>أدلّة الاستخدام</h1>
        <p>لكلّ دورٍ في المنصّة دليلٌ مستقلّ يشرح مهامّه وصلاحيّاته وكلّ شاشةٍ يراها، وكيف تُحتسَب نقاطه بالضبط. اختر دورك وابدأ.</p>
        <div class="g-hero-meta">
            <span class="g-chip">٦ أدلّة</span>
            <span class="g-chip">قابلة للطباعة</span>
            <span class="g-chip">تعمل ليلاً ونهاراً</span>
        </div>
    </div>

    <section class="g-sec">
        <h2><span class="num">◆</span> اختر دورك</h2>
        <p class="lead">كلّ دليلٍ مكتوبٌ لمن يستخدم المنصّة فعلاً — لا مواصفات تقنيّة، بل ما تحتاج معرفته لتعمل.</p>

        <div class="g-grid">
            @foreach ($guides as $slug => $g)
                <a href="{{ route('guides.show', $slug) }}" class="g-card" style="text-decoration:none;display:block">
                    <span class="ico">{{ $g['emoji'] }}</span>
                    <h5>{{ $g['title'] }}</h5>
                    <p>{{ \Illuminate\Support\Str::limit($g['tagline'], 110) }}</p>
                    <span class="path">افتح الدليل ←</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="g-sec">
        <h2><span class="num">؟</span> كيف تستفيد من هذه الأدلّة</h2>
        <ul>
            <li><strong>أرسل لكلّ فئةٍ رابطها فقط.</strong> لا تُرسل دليل مدير النظام للطلاب، ولا دليل الطالب لأولياء الأمور.</li>
            <li><strong>كلّ دليل مستقلّ تماماً</strong> — يُقرأ وحده بلا حاجةٍ لغيره.</li>
            <li><strong>اطبعه أو احفظه PDF</strong> من زرّ الطباعة أعلى الصفحة، لتوزيعه في اللقاءات التعريفيّة.</li>
            <li><strong>الفهرس الجانبيّ</strong> ينقلك لأيّ قسمٍ مباشرة، ويتتبّع موضعك أثناء القراءة.</li>
        </ul>

        <div class="g-note info">
            <strong>هل يحمل مستخدمٌ أكثر من دور؟</strong>
            بعض الحسابات تحمل دوراً ثانويّاً (كمعلّمٍ هو وليّ أمرٍ أيضاً) وتُبدّل بينها من داخل المنصّة. في هذه الحالة أرسل الدليلين معاً.
        </div>
    </section>
</main>
@endsection
