@extends('layouts.auth')

@section('title', $page->meta_title ?? $page->page_name)

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@push('styles')
<style>
/* Page Builder Styles */
.page-section {
    width: 100%;
}

.section-grid {
    display: grid;
    gap: 24px;
    padding: 40px 24px;
    max-width: 1200px;
    margin: 0 auto;
}

.grid-cols-1 { grid-template-columns: 1fr; }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
.grid-cols-4 { grid-template-columns: repeat(4, 1fr); }

.grid-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Component Styles */
.component-heading {
    font-weight: 700;
    color: #1e293b;
    line-height: 1.3;
    margin: 0;
}

.component-heading.h1 { font-size: 48px; }
.component-heading.h2 { font-size: 36px; }
.component-heading.h3 { font-size: 28px; }
.component-heading.h4 { font-size: 24px; }
.component-heading.h5 { font-size: 20px; }
.component-heading.h6 { font-size: 18px; }

.component-paragraph {
    font-size: 16px;
    line-height: 1.8;
    color: #475569;
    margin: 0;
}

.component-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 14px 32px;
    font-weight: 600;
    font-size: 16px;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s;
    border: 2px solid transparent;
    cursor: pointer;
}

.component-button.primary {
    background: var(--color-primary);
    color: white;
}

.component-button.primary:hover {
    background: var(--color-primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(60, 203, 138, 0.3);
}

.component-button.secondary {
    background: var(--color-secondary);
    color: white;
}

.component-button.secondary:hover {
    background: #2563eb;
    transform: translateY(-2px);
}

.component-button.outline {
    background: transparent;
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.component-button.outline:hover {
    background: var(--color-primary);
    color: white;
    transform: translateY(-2px);
}

.component-image {
    width: 100%;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.component-video {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.component-video iframe,
.component-video video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}

.component-card {
    background: white;
    padding: 32px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: all 0.3s;
    height: 100%;
}

.component-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.card-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.card-icon img {
    width: 64px;
    height: 64px;
    object-fit: contain;
}

.card-title {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 12px 0;
}

.card-text {
    font-size: 15px;
    line-height: 1.7;
    color: #64748b;
    margin: 0;
}

/* Text Alignment */
.text-right { text-align: right; }
.text-center { text-align: center; }
.text-left { text-align: left; }

/* List Styles */
.component-list {
    margin: 20px 0;
    padding-right: 24px;
    line-height: 1.8;
}

.component-list li {
    margin-bottom: 12px;
    color: #334155;
}

.component-list li::marker {
    color: var(--color-primary);
    font-weight: 600;
}

/* Quote Styles */
.component-quote {
    margin: 32px 0;
    padding: 24px 28px;
    border-radius: 12px;
    position: relative;
}

.component-quote.border {
    border-right: 5px solid var(--color-primary);
    background: linear-gradient(to left, #f8fafc 0%, #ffffff 100%);
}

.component-quote.background {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-right: none;
}

.component-quote.minimal {
    background: transparent;
    border: none;
    padding: 16px 0;
    font-style: italic;
}

.component-quote p {
    font-size: 18px;
    line-height: 1.8;
    margin: 0 0 12px;
    color: #1e293b;
}

.component-quote footer {
    font-size: 14px;
    color: #64748b;
    font-weight: 600;
    font-style: normal;
}

/* Divider Styles */
.component-divider {
    margin: 40px auto;
    opacity: 0.6;
}

/* Link Styles */
.component-link {
    display: inline-block;
    transition: all 0.3s ease;
    font-weight: 500;
}

.component-link:hover {
    opacity: 0.8;
    transform: translateX(-2px);
}

/* Badge Styles */
.component-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border-radius: 20px;
    font-weight: 600;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    font-size: 11px;
}

/* Gallery Styles */
.component-gallery {
    margin: 32px 0;
}

.component-gallery img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-radius: 12px;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.component-gallery img:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* Icon Styles */
.component-icon {
    display: inline-block;
    transition: all 0.3s;
    animation: iconFloat 3s ease-in-out infinite;
}

@keyframes iconFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.component-icon:hover {
    transform: scale(1.2) rotate(5deg);
}

/* Alert Styles */
.component-alert {
    margin: 24px 0;
    padding: 18px 24px;
    border-radius: 12px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    font-size: 15px;
    line-height: 1.6;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.component-alert .alert-icon {
    font-size: 28px;
    flex-shrink: 0;
    margin-top: -2px;
}

.component-alert .alert-text {
    flex: 1;
    color: #1e293b;
}

/* Accordion Styles */
.component-accordion {
    margin: 32px 0;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.component-accordion details {
    border-bottom: 1px solid #e2e8f0;
}

.component-accordion details:last-child {
    border-bottom: none;
}

.component-accordion summary {
    padding: 18px 24px;
    background: #f8fafc;
    cursor: pointer;
    font-weight: 600;
    color: #1e293b;
    user-select: none;
    transition: all 0.2s;
    list-style: none;
    display: flex;
    align-items: center;
    gap: 12px;
}

.component-accordion summary::-webkit-details-marker {
    display: none;
}

.component-accordion summary::before {
    content: '▼';
    font-size: 12px;
    color: var(--color-primary);
    transition: transform 0.3s;
}

.component-accordion details[open] summary::before {
    transform: rotate(180deg);
}

.component-accordion summary:hover {
    background: #f1f5f9;
}

.component-accordion .accordion-content {
    padding: 20px 24px;
    color: #64748b;
    line-height: 1.8;
    background: white;
}

/* Tabs Styles */
.component-tabs {
    margin: 32px 0;
}

.component-tabs .tabs-nav {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.component-tabs .tab-button {
    padding: 12px 24px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 600;
    color: #64748b;
    transition: all 0.2s;
    font-size: 15px;
    position: relative;
    top: 2px;
}

.component-tabs .tab-button:hover {
    color: var(--color-primary);
    background: #f8fafc;
}

.component-tabs .tab-button.active {
    color: var(--color-primary);
    border-color: var(--color-primary);
}

.component-tabs .tab-content {
    padding: 24px;
    color: #64748b;
    line-height: 1.8;
    background: #f8fafc;
    border-radius: 12px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Spacer Styles */
.component-spacer {
    background: transparent;
}

/* HTML Component */
.component-html {
    margin: 24px 0;
}

/* Responsive */
@media (max-width: 768px) {
    .section-grid {
        padding: 24px 16px;
        gap: 16px;
    }
    
    .grid-cols-2,
    .grid-cols-3,
    .grid-cols-4 {
        grid-template-columns: 1fr;
    }
    
    .component-heading.h1 { font-size: 32px; }
    .component-heading.h2 { font-size: 28px; }
    .component-heading.h3 { font-size: 24px; }
    
    .component-card {
        padding: 24px;
    }
    
    .component-gallery {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    .component-gallery img {
        height: 180px;
    }
    
    .component-quote {
        padding: 20px;
        font-size: 16px;
    }
    
    .component-tabs .tabs-nav {
        gap: 4px;
    }
    
    .component-tabs .tab-button {
        padding: 10px 16px;
        font-size: 14px;
    }
    
    .component-accordion summary {
        padding: 14px 16px;
        font-size: 14px;
    }
    
    .component-alert {
        padding: 14px 16px;
        font-size: 14px;
    }
}
</style>
@endpush

@section('content')
<div class="page-content">
    @if(isset($page->json_data['sections']) && is_array($page->json_data['sections']))
        @foreach($page->json_data['sections'] as $section)
            <section class="page-section">
                <div class="section-grid grid-cols-{{ $section['columns'] ?? 1 }}">
                    @if(isset($section['grid']) && is_array($section['grid']))
                        @foreach($section['grid'] as $column)
                            <div class="grid-column">
                                @if(is_array($column))
                                    @foreach($column as $component)
                                        @if(isset($component['type']))
                                            @switch($component['type'])
                                                @case('heading')
                                                    <{{ $component['content']['level'] ?? 'h2' }} 
                                                        class="component-heading {{ $component['content']['level'] ?? 'h2' }} text-{{ $component['content']['align'] ?? 'right' }}">
                                                        {{ $component['content']['text'] ?? '' }}
                                                    </{{ $component['content']['level'] ?? 'h2' }}>
                                                    @break

                                                @case('paragraph')
                                                    <p class="component-paragraph text-{{ $component['content']['align'] ?? 'right' }}">
                                                        {{ $component['content']['text'] ?? '' }}
                                                    </p>
                                                    @break

                                                @case('button')
                                                    <div class="text-{{ $component['content']['align'] ?? 'right' }}">
                                                        <a href="{{ $component['content']['link'] ?? '#' }}" 
                                                           class="component-button {{ $component['content']['style'] ?? 'primary' }}">
                                                            {{ $component['content']['text'] ?? 'زر' }}
                                                        </a>
                                                    </div>
                                                    @break

                                                @case('image')
                                                    <img src="{{ $component['content']['url'] ?? '' }}" 
                                                         alt="{{ $component['content']['alt'] ?? '' }}"
                                                         class="component-image">
                                                    @break

                                                @case('video')
                                                    <div class="component-video">
                                                        @if(str_contains($component['content']['url'] ?? '', 'youtube.com') || str_contains($component['content']['url'] ?? '', 'youtu.be'))
                                                            <iframe src="{{ $component['content']['url'] ?? '' }}" 
                                                                    allowfullscreen></iframe>
                                                        @else
                                                            <video src="{{ asset('storage/app/public/data/' . $component['content']['url'] ?? '') }}" 
                                                                   controls></video>
                                                        @endif
                                                    </div>
                                                    @break

                                                @case('card')
                                                    <div class="component-card">
                                                        @if(isset($component['content']['icon']))
                                                            <div class="card-icon">
                                                                @if(str_starts_with($component['content']['icon'], 'http') || str_contains($component['content']['icon'], '/'))
                                                                    <img src="{{ asset('storage/app/public/data/' . $component['content']['icon']) }}" alt="icon">
                                                                @else
                                                                    {{ $component['content']['icon'] }}
                                                                @endif
                                                            </div>
                                                        @endif
                                                        @if(isset($component['content']['title']))
                                                            <h3 class="card-title">{{ $component['content']['title'] }}</h3>
                                                        @endif
                                                        @if(isset($component['content']['text']))
                                                            <p class="card-text">{{ $component['content']['text'] }}</p>
                                                        @endif
                                                    </div>
                                                    @break

                                                @case('list')
                                                    @php $listType = $component['content']['type'] ?? 'ul'; @endphp
                                                    <{{ $listType }} class="component-list" style="color:{{ $component['content']['color'] ?? '#334155' }};font-size:{{ $component['content']['fontSize'] ?? '16px' }};">
                                                        @foreach($component['content']['items'] ?? [] as $item)
                                                            <li>{{ $item }}</li>
                                                        @endforeach
                                                    </{{ $listType }}>
                                                    @break

                                                @case('quote')
                                                    <blockquote class="component-quote {{ $component['content']['style'] ?? 'border' }}" style="font-size:{{ $component['content']['fontSize'] ?? '18px' }};color:{{ $component['content']['color'] ?? '#64748b' }};">
                                                        <p>"{{ $component['content']['text'] ?? '' }}"</p>
                                                        @if(isset($component['content']['author']) && $component['content']['author'])
                                                            <footer>— {{ $component['content']['author'] }}</footer>
                                                        @endif
                                                    </blockquote>
                                                    @break

                                                @case('divider')
                                                    <hr class="component-divider" style="border:none;border-top:{{ $component['content']['thickness'] ?? '2px' }} {{ $component['content']['style'] ?? 'solid' }} {{ $component['content']['color'] ?? '#e2e8f0' }};width:{{ $component['content']['width'] ?? '100%' }};">
                                                    @break

                                                @case('link')
                                                    <a href="{{ $component['content']['url'] ?? '#' }}" 
                                                       class="component-link"
                                                       style="color:{{ $component['content']['color'] ?? '#3b82f6' }};font-size:{{ $component['content']['fontSize'] ?? '16px' }};text-decoration:{{ ($component['content']['underline'] ?? true) ? 'underline' : 'none' }};"
                                                       target="_blank">
                                                        {{ $component['content']['text'] ?? 'رابط' }}
                                                    </a>
                                                    @break

                                                @case('badge')
                                                    <span class="component-badge" style="background:{{ $component['content']['bgColor'] ?? '#d1fae5' }};color:{{ $component['content']['color'] ?? '#10b981' }};font-size:{{ $component['content']['fontSize'] ?? '12px' }};font-weight:{{ $component['content']['fontWeight'] ?? '600' }};">
                                                        {{ $component['content']['text'] ?? 'وسم' }}
                                                    </span>
                                                    @break

                                                @case('gallery')
                                                    <div class="component-gallery" style="display:grid;grid-template-columns:repeat({{ $component['content']['columns'] ?? 3 }}, 1fr);gap:{{ $component['content']['gap'] ?? '16px' }};">
                                                        @foreach($component['content']['images'] ?? [] as $image)
                                                            <img src="{{ $image }}" alt="معرض">
                                                        @endforeach
                                                    </div>
                                                    @break

                                                @case('icon')
                                                    <div class="component-icon" style="text-align:center;font-size:{{ $component['content']['size'] ?? '48px' }};color:{{ $component['content']['color'] ?? '#fbbf24' }};margin:24px 0;">
                                                        {{ $component['content']['icon'] ?? '⭐' }}
                                                    </div>
                                                    @break

                                                @case('alert')
                                                    @php
                                                        $alertColors = ['info' => '#3b82f6', 'success' => '#10b981', 'warning' => '#f59e0b', 'error' => '#ef4444'];
                                                        $bgColors = ['info' => '#dbeafe', 'success' => '#d1fae5', 'warning' => '#fef3c7', 'error' => '#fee2e2'];
                                                        $type = $component['content']['type'] ?? 'info';
                                                        $type = in_array($type, ['info', 'success', 'warning', 'error'], true) ? $type : 'info';
                                                    @endphp
                                                    <div class="component-alert" style="background:{{ $bgColors[$type] }};border-right:4px solid {{ $alertColors[$type] }};">
                                                        <span class="alert-icon">{{ $component['content']['icon'] ?? 'ℹ️' }}</span>
                                                        <span class="alert-text">{{ $component['content']['text'] ?? 'رسالة تنبيه' }}</span>
                                                    </div>
                                                    @break

                                                @case('accordion')
                                                    <div class="component-accordion">
                                                        @foreach($component['content']['items'] ?? [] as $index => $item)
                                                            <details>
                                                                <summary>
                                                                    {{ $item['title'] ?? 'عنوان' }}
                                                                </summary>
                                                                <div class="accordion-content">
                                                                    {{ $item['content'] ?? 'محتوى' }}
                                                                </div>
                                                            </details>
                                                        @endforeach
                                                    </div>
                                                    @break

                                                @case('tabs')
                                                    <div class="component-tabs" x-data="{ activeTab: 0 }">
                                                        <div class="tabs-nav">
                                                            @foreach($component['content']['tabs'] ?? [] as $index => $tab)
                                                                <button @click="activeTab = {{ $index }}" 
                                                                        class="tab-button"
                                                                        :class="activeTab === {{ $index }} ? 'active' : ''">
                                                                    {{ $tab['title'] ?? "تبويب " . ($index + 1) }}
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                        <div>
                                                            @foreach($component['content']['tabs'] ?? [] as $index => $tab)
                                                                <div x-show="activeTab === {{ $index }}" class="tab-content">
                                                                    {{ $tab['content'] ?? 'محتوى' }}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @break

                                                @case('spacer')
                                                    <div class="component-spacer" style="height:{{ $component['content']['height'] ?? '40px' }};"></div>
                                                    @break

                                                @case('html')
                                                    <div class="component-html">
                                                        {!! safe_html($component['content']['code'] ?? '') !!}
                                                    </div>
                                                    @break
                                            @endswitch
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>
        @endforeach
    @else
        {{-- Fallback for old format --}}
        @if(is_array($page->json_data))
        <div style="min-height: 100vh; padding: 80px 0;">
            <div style="max-width: 1200px; margin: 0 auto; padding: 0 24px;">
        @foreach($page->json_data as $block)
            @if(!is_array($block) || !isset($block['type']))
                @continue
            @endif
            @switch($block['type'])
                @case('hero')
                    <section style="text-align: center; padding: 80px 24px; background: linear-gradient(135deg, {{ setting('primary_color', '#3CCB8A') }} 0%, #2fb577 100%); border-radius: 16px; color: white; margin-bottom: 48px;">
                        <h1 style="font-size: 48px; font-weight: 700; margin-bottom: 24px; line-height: 1.2;">
                            {{ $block['content']['title'] ?? 'عنوان رئيسي' }}
                        </h1>
                        <p style="font-size: 20px; margin-bottom: 32px; opacity: 0.95;">
                            {{ $block['content']['subtitle'] ?? 'وصف قصير' }}
                        </p>
                        @if(isset($block['content']['buttonText']))
                            <a href="{{ $block['content']['buttonLink'] ?? '#' }}" style="display: inline-block; background: white; color: {{ setting('primary_color', '#3CCB8A') }}; padding: 16px 48px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 18px; transition: all 0.3s ease;">
                                {{ $block['content']['buttonText'] }}
                            </a>
                        @endif
                    </section>
                    @break

                @case('heading')
                    @php
                        $level = $block['content']['level'] ?? 'h2';
                        $text = $block['content']['text'] ?? 'عنوان';
                    @endphp
                    <{{ $level }} style="color: #1e293b; margin: 32px 0 16px; font-weight: 700; line-height: 1.3;">
                        {{ $text }}
                    </{{ $level }}>
                    @break

                @case('paragraph')
                    <p style="color: #64748b; line-height: 1.8; margin-bottom: 24px; font-size: 16px;">
                        {{ $block['content']['text'] ?? 'نص الفقرة' }}
                    </p>
                    @break

                @case('button')
                    <div style="margin: 32px 0;">
                        <a href="{{ $block['content']['link'] ?? '#' }}" style="display: inline-block; background: {{ setting('primary_color', '#3CCB8A') }}; color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                            {{ $block['content']['text'] ?? 'انقر هنا' }}
                        </a>
                    </div>
                    @break

                @case('image')
                    <div style="margin: 40px 0;">
                        <img src="{{ $block['content']['url'] ?? '' }}" 
                             alt="{{ $block['content']['alt'] ?? 'صورة' }}" 
                             style="max-width: 100%; height: auto; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    </div>
                    @break

                @case('cards')
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin: 48px 0;">
                        @foreach($block['content']['items'] ?? [] as $item)
                            <div style="padding: 32px; background: white; border-radius: 12px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: all 0.3s ease; border: 1px solid #e2e8f0;">
                                <div style="font-size: 48px; margin-bottom: 16px;">{{ $item['icon'] ?? '⭐' }}</div>
                                <h3 style="font-size: 20px; margin-bottom: 12px; color: #1e293b; font-weight: 600;">{{ $item['title'] ?? 'عنوان' }}</h3>
                                <p style="color: #64748b; line-height: 1.6;">{{ $item['text'] ?? 'نص البطاقة' }}</p>
                            </div>
                        @endforeach
                    </div>
                    @break

                @case('video')
                    <div style="margin: 48px 0;">
                        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                            <iframe src="{{ $block['content']['url'] ?? '' }}" 
                                    frameborder="0" 
                                    allowfullscreen 
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
                        </div>
                    </div>
                    @break

                @case('spacer')
                    <div style="height: {{ $block['content']['height'] ?? '40px' }};"></div>
                    @break
                @endswitch
        @endforeach
            </div>
        </div>
        @endif
    @endif
</div>
@endsection