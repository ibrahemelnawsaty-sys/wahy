@php
    // جلب الإعدادات
    $settings = \App\Models\Setting::getMany(
        ['font_family', 'primary_color', 'secondary_color', 'text_color', 'background_color', 'site_logo', 'site_name', 'site_description'],
        [
            'font_family' => 'IBM Plex Sans Arabic',
            'primary_color' => '#3CCB8A',
            'secondary_color' => '#3B82F6',
            'text_color' => '#1e293b',
            'background_color' => '#f8fafc',
            'site_name' => 'قيمّ',
            'site_description' => 'منصة تعليمية رائدة لبناء القيم الإنسانية'
        ]
    );
    
    $fontFamily = $settings['font_family'];
    $primaryColor = $settings['primary_color'];
    $secondaryColor = $settings['secondary_color'];
    $textColor = $settings['text_color'];
    $backgroundColor = $settings['background_color'];
    $siteLogo = $settings['site_logo'] ?? null;
    $siteName = $settings['site_name'];
    $siteDescription = $settings['site_description'];
    
    $primaryHover = adjustBrightness($primaryColor, -20);
    $primaryLight = hexToRgba($primaryColor, 0.1);
    $secondaryHover = adjustBrightness($secondaryColor, -20);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $siteDescription }}">
    <meta name="theme-color" content="{{ $primaryColor }}">
    <title>{{ $siteName }} - ابنِ قيمك خطوة بخطوة</title>
    
    <link rel="preload" href="{{ asset('css/landing.min.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset('css/landing.min.css') }}">
    
    <style>
        :root {
            --color-primary: {{ $primaryColor }};
            --color-primary-hover: {{ $primaryHover }};
            --color-primary-light: {{ $primaryLight }};
            --color-secondary: {{ $secondaryColor }};
            --color-secondary-hover: {{ $secondaryHover }};
            --color-text: {{ $textColor }};
            --color-bg: {{ $backgroundColor }};
            --font-family-base: '{{ $fontFamily }}', sans-serif;
        }
        
        body {
            font-family: var(--font-family-base);
            margin: 0;
            padding: 0;
            background: var(--color-bg);
            color: var(--color-text);
        }
        
        /* Header Styles */
        .site-header {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .site-logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-primary);
            text-decoration: none;
        }
        
        .header-nav {
            display: flex;
            gap: 24px;
            align-items: center;
        }
        
        .nav-link {
            text-decoration: none;
            color: var(--color-text);
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--color-primary);
        }
        
        .btn-primary {
            background: var(--color-primary);
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        
        .btn-primary:hover {
            background: var(--color-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(60, 203, 138, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--color-primary);
            padding: 12px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: 2px solid var(--color-primary);
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background: var(--color-primary);
            color: white;
        }
        
        /* Content Container */
        .page-content {
            min-height: calc(100vh - 200px);
        }
        
        /* Block Styles */
        .block {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 24px;
        }
        
        /* Hero Block */
        .block-hero {
            text-align: center;
            padding: 100px 24px;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color: white;
        }
        
        .block-hero h1 {
            font-size: 48px;
            margin: 0 0 24px 0;
            font-weight: 700;
        }
        
        .block-hero p {
            font-size: 20px;
            line-height: 1.8;
            margin: 0 0 32px 0;
            opacity: 0.95;
        }
        
        .block-hero .buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Heading Block */
        .block-heading h1,
        .block-heading h2,
        .block-heading h3 {
            text-align: center;
            margin: 0;
            color: var(--color-text);
        }
        
        .block-heading h1 { font-size: 42px; }
        .block-heading h2 { font-size: 36px; }
        .block-heading h3 { font-size: 28px; }
        
        /* Paragraph Block */
        .block-paragraph {
            text-align: center;
            font-size: 18px;
            line-height: 1.8;
            color: #64748b;
        }
        
        /* Button Block */
        .block-button {
            text-align: center;
        }
        
        /* Stats Block */
        .block-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 32px;
            text-align: center;
        }
        
        .stat-item {
            padding: 32px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .stat-value {
            font-size: 48px;
            font-weight: 700;
            color: var(--color-primary);
            margin: 0 0 8px 0;
        }
        
        .stat-label {
            font-size: 18px;
            color: #64748b;
        }
        
        /* Features Block */
        .block-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
        }
        
        .feature-item {
            padding: 32px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .feature-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        
        .feature-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-text);
            margin: 0 0 12px 0;
        }
        
        .feature-description {
            font-size: 16px;
            line-height: 1.6;
            color: #64748b;
        }
        
        /* CTA Block */
        .block-cta {
            text-align: center;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color: white;
            padding: 80px 24px;
            border-radius: 16px;
        }
        
        .block-cta h2 {
            font-size: 36px;
            margin: 0 0 16px 0;
        }
        
        .block-cta p {
            font-size: 18px;
            opacity: 0.95;
            margin: 0 0 32px 0;
        }
        
        /* Image Block */
        .block-image {
            text-align: center;
        }
        
        .block-image img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        
        /* Spacer Block */
        .block-spacer {
            height: var(--spacer-height, 40px);
        }
        
        /* Footer */
        .site-footer {
            background: #1e293b;
            color: white;
            padding: 40px 24px;
            text-align: center;
        }
        
        .site-footer p {
            margin: 0;
            opacity: 0.8;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .block-hero h1 { font-size: 32px; }
            .block-hero p { font-size: 16px; }
            .header-nav { display: none; }
            .block { padding: 40px 16px; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-container">
            <a href="/" class="site-logo">{{ $siteName }}</a>
            <nav class="header-nav">
                <a href="#features" class="nav-link">المميزات</a>
                <a href="#about" class="nav-link">عن المنصة</a>
                <a href="{{ route('login') }}" class="btn-primary">تسجيل الدخول</a>
            </nav>
        </div>
    </header>

    <!-- Dynamic Content -->
    <main class="page-content">
        @foreach($landingPage->json_data as $block)
            @switch($block['type'])
                @case('hero')
                    <section class="block block-hero">
                        <h1>{{ $block['content']['title'] ?? '' }}</h1>
                        <p>{{ $block['content']['subtitle'] ?? '' }}</p>
                        <div class="buttons">
                            @if(!empty($block['content']['buttonText']))
                                <a href="{{ $block['content']['buttonLink'] ?? '#' }}" class="btn-primary">
                                    {{ $block['content']['buttonText'] }}
                                </a>
                            @endif
                            @if(!empty($block['content']['secondaryButtonText']))
                                <a href="{{ $block['content']['secondaryButtonLink'] ?? '#' }}" class="btn-secondary">
                                    {{ $block['content']['secondaryButtonText'] }}
                                </a>
                            @endif
                        </div>
                    </section>
                    @break

                @case('heading')
                    <section class="block block-heading">
                        @php $level = $block['content']['level'] ?? 'h2'; @endphp
                        <{{ $level }}>{{ $block['content']['text'] ?? '' }}</{{ $level }}>
                    </section>
                    @break

                @case('paragraph')
                    <section class="block block-paragraph">
                        <p>{{ $block['content']['text'] ?? '' }}</p>
                    </section>
                    @break

                @case('button')
                    <section class="block block-button">
                        @php $style = $block['content']['style'] ?? 'primary'; @endphp
                        <a href="{{ $block['content']['link'] ?? '#' }}" class="btn-{{ $style }}">
                            {{ $block['content']['text'] ?? '' }}
                        </a>
                    </section>
                    @break

                @case('stats')
                    <section class="block">
                        <div class="block-stats">
                            @foreach($block['content']['items'] ?? [] as $stat)
                                <div class="stat-item">
                                    <div class="stat-value">{{ $stat['value'] ?? '' }}</div>
                                    <div class="stat-label">{{ $stat['label'] ?? '' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                    @break

                @case('features')
                    <section class="block">
                        <div class="block-features">
                            @foreach($block['content']['items'] ?? [] as $feature)
                                <div class="feature-item">
                                    <h3 class="feature-title">{{ $feature['title'] ?? '' }}</h3>
                                    <p class="feature-description">{{ $feature['description'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                    @break

                @case('cta')
                    <section class="block">
                        <div class="block-cta">
                            <h2>{{ $block['content']['title'] ?? '' }}</h2>
                            <p>{{ $block['content']['description'] ?? '' }}</p>
                            @if(!empty($block['content']['buttonText']))
                                <a href="{{ $block['content']['buttonLink'] ?? '#' }}" class="btn-primary" style="background: white; color: var(--color-primary);">
                                    {{ $block['content']['buttonText'] }}
                                </a>
                            @endif
                        </div>
                    </section>
                    @break

                @case('image')
                    <section class="block block-image">
                        <img src="{{ $block['content']['url'] ?? '' }}" alt="{{ $block['content']['alt'] ?? '' }}">
                    </section>
                    @break

                @case('spacer')
                    <div class="block-spacer" style="--spacer-height: {{ $block['content']['height'] ?? 40 }}px;"></div>
                    @break
            @endswitch
        @endforeach
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <p>&copy; {{ date('Y') }} {{ $siteName }}. جميع الحقوق محفوظة.</p>
    </footer>

    <script src="{{ asset('js/landing.min.js') }}" defer></script>
</body>
</html>
