@php
    // استخدام المتغيرات من الـ layout إن وُجدت، وإلا جلبها من قاعدة البيانات
    if (!isset($siteName) || !isset($siteDescription)) {
        $footerSettings = \App\Models\Setting::getMany(
            ['site_name', 'site_description', 'contact_email', 'contact_phone', 'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url'],
            [
                'site_name' => 'قيمّ',
                'site_description' => 'منصة تعليمية رائدة لبناء القيم الإنسانية من خلال التعليم التفاعلي والممتع',
                'contact_email' => null,
                'contact_phone' => null,
                'facebook_url' => null,
                'twitter_url' => null,
                'instagram_url' => null,
                'linkedin_url' => null
            ]
        );
        
        $siteName = $footerSettings['site_name'] ?? 'قيمّ';
        $siteDescription = $footerSettings['site_description'] ?? 'منصة تعليمية رائدة لبناء القيم الإنسانية من خلال التعليم التفاعلي والممتع';
        $contactEmail = $footerSettings['contact_email'] ?? null;
        $contactPhone = $footerSettings['contact_phone'] ?? null;
        $facebookUrl = $footerSettings['facebook_url'] ?? null;
        $twitterUrl = $footerSettings['twitter_url'] ?? null;
        $instagramUrl = $footerSettings['instagram_url'] ?? null;
        $linkedinUrl = $footerSettings['linkedin_url'] ?? null;
    } else {
        $contactEmail = $contactEmail ?? null;
        $contactPhone = $contactPhone ?? null;
        $facebookUrl = $facebookUrl ?? null;
        $twitterUrl = $twitterUrl ?? null;
        $instagramUrl = $instagramUrl ?? null;
        $linkedinUrl = $linkedinUrl ?? null;
    }

    // Issue #23: تطبيع روابط التواصل — إن لم تبدأ بـ http تُعتبر نسبية ويُعاد المستخدم لنفس الصفحة
    $normalizeUrl = function ($url) {
        $url = trim((string) $url);
        if ($url === '') return null;
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        return $url;
    };
    $facebookUrl  = $normalizeUrl($facebookUrl);
    $twitterUrl   = $normalizeUrl($twitterUrl);
    $instagramUrl = $normalizeUrl($instagramUrl);
    $linkedinUrl  = $normalizeUrl($linkedinUrl);
@endphp

<footer class="footer">
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-col footer-brand">
                    <div class="editable-element" data-element="footer-logo">
                        <x-element-actions />
                        <a href="/" class="footer-logo">
                            <span class="logo-icon" data-editable="footer_logo_icon" data-section="footer">🌟</span>
                            <span class="logo-text" data-editable="footer_logo_text" data-section="footer">{{ $siteName }}</span>
                        </a>
                    </div>
                    <div class="editable-element" data-element="footer-description">
                        <x-element-actions />
                        <p class="footer-description" data-editable="footer_description" data-section="footer">
                            {{ $siteDescription }}
                        </p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <div class="editable-element" data-element="footer-quick-title">
                        <x-element-actions />
                        <h3 class="footer-title" data-editable="footer_quick_title" data-section="footer">روابط سريعة</h3>
                    </div>
                    <ul class="footer-links">
                        <li class="editable-element" data-element="footer-link-home">
                            <x-element-actions />
                            <a href="/" data-editable="footer_link_home" data-section="footer">الرئيسية</a>
                        </li>
                        <li class="editable-element" data-element="footer-link-features">
                            <x-element-actions />
                            <a href="/#features" data-editable="footer_link_features" data-section="footer">المميزات</a>
                        </li>
                        <li class="editable-element" data-element="footer-link-values">
                            <x-element-actions />
                            <a href="/#values" data-editable="footer_link_values" data-section="footer">القيم</a>
                        </li>
                        <li class="editable-element" data-element="footer-link-activities">
                            <x-element-actions />
                            <a href="/#activities" data-editable="footer_link_activities" data-section="footer">الأنشطة</a>
                        </li>
                        <li class="editable-element" data-element="footer-link-partners">
                            <x-element-actions />
                            <a href="/#partners" data-editable="footer_link_partners" data-section="footer">الشركاء</a>
                        </li>
                    </ul>
                </div>

                <!-- Account Links -->
                <div class="footer-col">
                    <h3 class="footer-title">الحسابات</h3>
                    <ul class="footer-links">
                        <li><a href="/login">تسجيل الدخول</a></li>
                        <li><a href="/register">إنشاء حساب</a></li>
                        <li><a href="/dashboard">لوحة التحكم</a></li>
                    </ul>
                </div>

                <!-- Legal Links -->
                <div class="footer-col">
                    <h3 class="footer-title">روابط قانونية</h3>
                    <ul class="footer-links">
                        <li><a href="/privacy">سياسة الخصوصية</a></li>
                        <li><a href="/terms">الشروط والأحكام</a></li>
                        <li><a href="/usage">سياسة الاستخدام</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer-col">
                    <h3 class="footer-title">تواصل معنا</h3>
                    <ul class="footer-contact">
                        @if($contactEmail)
                        <li>
                            <span class="contact-icon">📧</span>
                            <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                        </li>
                        @endif
                        @if($contactPhone)
                        <li>
                            <span class="contact-icon">📞</span>
                            <a href="tel:{{ $contactPhone }}">{{ $contactPhone }}</a>
                        </li>
                        @endif
                        @if(!$contactEmail && !$contactPhone)
                        <li>
                            <span class="contact-icon">📧</span>
                            <a href="mailto:info@qiyamm.sa">info@qiyamm.sa</a>
                        </li>
                        @endif
                    </ul>
                    
                    <!-- Social Media Links -->
                    @if($facebookUrl || $twitterUrl || $instagramUrl || $linkedinUrl)
                    <div class="footer-social">
                        @if($facebookUrl)
                        <a href="{{ $facebookUrl }}" target="_blank" rel="noopener" class="social-link" aria-label="فيسبوك">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        @endif
                        @if($twitterUrl)
                        <a href="{{ $twitterUrl }}" target="_blank" rel="noopener" class="social-link" aria-label="تويتر">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        @endif
                        @if($instagramUrl)
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" class="social-link" aria-label="إنستغرام">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        @endif
                        @if($linkedinUrl)
                        <a href="{{ $linkedinUrl }}" target="_blank" rel="noopener" class="social-link" aria-label="لينكد إن">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <p class="copyright">
                {{ setting('footer_text') ?: '© ' . date('Y') . ' ' . $siteName . '. جميع الحقوق محفوظة' }}
            </p>
        </div>
    </div>
</footer>
