<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', setting('site_name', 'منصة قيمّ'))</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            direction: rtl;
            min-height: 100vh;
        }
        
        .email-wrapper {
            max-width: 650px;
            margin: 0 auto;
        }
        
        .email-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
        }
        
        /* Glass Effect Header */
        .email-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
            backdrop-filter: blur(10px);
            padding: 50px 40px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .email-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .logo-container {
            position: relative;
            z-index: 2;
            margin-bottom: 20px;
        }
        
        .logo {
            max-width: 180px;
            height: auto;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }
        
        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .site-name {
            color: #ffffff;
            font-size: 32px;
            font-weight: 800;
            margin: 0;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            letter-spacing: -0.5px;
        }
        
        .site-tagline {
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            margin-top: 8px;
            font-weight: 500;
            position: relative;
            z-index: 2;
        }
        
        /* Content Area */
        .email-content {
            padding: 45px 40px;
            background: rgba(255, 255, 255, 0.98);
        }
        
        .email-title {
            font-size: 28px;
            color: #1a202c;
            font-weight: 800;
            margin-bottom: 25px;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .greeting {
            font-size: 20px;
            color: #2d3748;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .message-text {
            color: #4a5568;
            line-height: 1.9;
            font-size: 16px;
            margin-bottom: 25px;
        }
        
        /* Glass Card */
        .glass-card {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.1);
        }
        
        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            border-right: 4px solid #3b82f6;
            padding: 20px 25px;
            border-radius: 12px;
            margin: 25px 0;
            backdrop-filter: blur(5px);
        }
        
        .info-box-title {
            color: #1e40af;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 17px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-box-text {
            color: #1e3a8a;
            font-size: 15px;
            line-height: 1.7;
        }
        
        /* Success Box */
        .success-box {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            border-right: 4px solid #10b981;
            padding: 20px 25px;
            border-radius: 12px;
            margin: 25px 0;
        }
        
        .success-box-title {
            color: #065f46;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 17px;
        }
        
        .success-box-text {
            color: #047857;
            font-size: 15px;
            line-height: 1.7;
        }
        
        /* Warning Box */
        .warning-box {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(245, 158, 11, 0.1) 100%);
            border-right: 4px solid #f59e0b;
            padding: 20px 25px;
            border-radius: 12px;
            margin: 25px 0;
        }
        
        .warning-box-title {
            color: #92400e;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 17px;
        }
        
        .warning-box-text {
            color: #b45309;
            font-size: 15px;
            line-height: 1.7;
        }
        
        /* Danger Box */
        .danger-box {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
            border-right: 4px solid #ef4444;
            padding: 20px 25px;
            border-radius: 12px;
            margin: 25px 0;
        }
        
        .danger-box-title {
            color: #991b1b;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 17px;
        }
        
        .danger-box-text {
            color: #b91c1c;
            font-size: 15px;
            line-height: 1.7;
        }
        
        /* Button */
        .btn {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }
        
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        
        /* Footer */
        .email-footer {
            background: linear-gradient(135deg, rgba(249, 250, 251, 0.98) 0%, rgba(243, 244, 246, 0.98) 100%);
            padding: 35px 40px;
            text-align: center;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .footer-text {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .footer-links {
            margin: 25px 0;
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 25px 0;
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            text-decoration: none;
            font-size: 18px;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            transform: translateY(-3px);
        }
        
        .copyright {
            color: #9ca3af;
            font-size: 13px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(229, 231, 235, 0.8);
        }
        
        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 25px 0;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            overflow: hidden;
            backdrop-filter: blur(5px);
        }
        
        .data-table tr {
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }
        
        .data-table tr:last-child {
            border-bottom: none;
        }
        
        .data-table td {
            padding: 15px 20px;
            font-size: 15px;
        }
        
        .data-table td:first-child {
            font-weight: 700;
            color: #4b5563;
            width: 40%;
        }
        
        .data-table td:last-child {
            color: #1f2937;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            body {
                padding: 20px 10px;
            }
            
            .email-header {
                padding: 35px 25px;
            }
            
            .email-content {
                padding: 30px 25px;
            }
            
            .email-footer {
                padding: 25px 20px;
            }
            
            .site-name {
                font-size: 26px;
            }
            
            .email-title {
                font-size: 24px;
            }
            
            .greeting {
                font-size: 18px;
            }
            
            .message-text {
                font-size: 15px;
            }
            
            .btn {
                padding: 14px 30px;
                font-size: 15px;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="logo-container">
                    @if(setting('site_logo'))
                        <img src="{{ asset('storage/app/public/data/' . setting('site_logo')) }}" alt="{{ setting('site_name', 'منصة قيمّ') }}" class="logo">
                    @else
                        <div class="logo-icon">🎓</div>
                    @endif
                </div>
                <h1 class="site-name">{{ setting('site_name', 'منصة قيمّ') }}</h1>
                @if(setting('site_description'))
                    <p class="site-tagline">{{ setting('site_description') }}</p>
                @endif
            </div>

            <!-- Content -->
            <div class="email-content">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p class="footer-text">
                    {{ setting('site_description', 'منصة تعليمية متكاملة لبناء القيم والأخلاق الحميدة') }}
                </p>

                <div class="footer-links">
                    <a href="{{ url('/') }}">الرئيسية</a>
                    <a href="{{ url('/about') }}">من نحن</a>
                    <a href="{{ url('/contact') }}">تواصل معنا</a>
                    <a href="{{ url('/privacy') }}">سياسة الخصوصية</a>
                </div>

                @php $emailSocial = social_links(); $emailSocialIcons = ['facebook' => '📘', 'twitter' => '🐦', 'instagram' => '📷', 'youtube' => '📺', 'linkedin' => '💼', 'whatsapp' => '💬']; @endphp
                @if(!empty($emailSocial))
                <div class="social-links">
                    @foreach($emailSocial as $platform => $url)
                        <a href="{{ $url }}" class="social-link">{{ $emailSocialIcons[$platform] ?? '🔗' }}</a>
                    @endforeach
                </div>
                @endif

                <p class="copyright">
                    &copy; {{ date('Y') }} {{ setting('site_name', 'منصة قيمّ') }}. جميع الحقوق محفوظة.
                </p>

                <p class="footer-text" style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                    هذه رسالة تلقائية، يرجى عدم الرد عليها مباشرة.
                    <br>
                    في حال وجود أي استفسار، يرجى التواصل معنا عبر: {{ setting('contact_email', 'info@qiyamm.sa') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
