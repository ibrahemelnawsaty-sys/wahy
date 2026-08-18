@extends('layouts.guide')

@section('guide_title', $guide['title'])
@section('guide_desc', $guide['tagline'])
@section('guide_class', 'g-role-' . $role)

@section('guide_body')
<main class="g-wrap">
    <aside class="g-toc">
        <h4>محتويات الدليل</h4>
        @foreach ($guide['sections'] as $anchor => $label)
            <a href="#{{ $anchor }}">{{ $label }}</a>
        @endforeach
    </aside>

    <div class="g-main">
        <div class="g-rolebar">
            @foreach ($guides as $slug => $g)
                <a href="{{ route('guides.show', $slug) }}" class="{{ $slug === $role ? 'current' : '' }}">
                    <span>{{ $g['emoji'] }}</span>{{ $g['title'] }}
                </a>
            @endforeach
        </div>

        <div class="g-hero">
            <span class="g-hero-emoji">{{ $guide['emoji'] }}</span>
            <h1>{{ $guide['title'] }}</h1>
            <p>{{ $guide['tagline'] }}</p>
            <div class="g-hero-meta">
                @foreach ($guide['chips'] as $chip)
                    <span class="g-chip">{{ $chip }}</span>
                @endforeach
            </div>
        </div>

        @include('guides.content.' . $role)
    </div>
</main>
@endsection
