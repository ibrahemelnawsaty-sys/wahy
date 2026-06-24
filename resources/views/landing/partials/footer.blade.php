@php
    $socialIconStyle = "width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: all 0.3s; text-decoration: none; border: 1px solid rgba(255,255,255,0.2);";
    $socialHover     = "this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-3px)'";
    $socialOut       = "this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'";
    $socials = [
        ['url' => $twitterUrl   ?? null, 'icon' => '🐦', 'label' => 'Twitter'],
        ['url' => $linkedinUrl  ?? null, 'icon' => '💼', 'label' => 'LinkedIn'],
        ['url' => $instagramUrl ?? null, 'icon' => '📷', 'label' => 'Instagram'],
        ['url' => $facebookUrl  ?? null, 'icon' => '💬', 'label' => 'Facebook'],
    ];
@endphp
<!-- Footer Section -->
<footer style="background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%); color: white; padding: 60px 0 30px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 50px; margin-bottom: 50px;">
            <!-- About Column -->
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <div style="font-size: 32px;">🏆</div>
                    <h3 style="font-size: 24px; font-weight: 700; margin: 0;">بناء القيم</h3>
                </div>
                <p style="color: rgba(255,255,255,0.8); font-size: 15px; line-height: 1.8; margin-bottom: 25px;">
                    منصة تعليمية تفاعلية متخصصة في تعزيز القيم الأخلاقية لدى الطلاب من خلال التعليم الممتع والتحفيزي.
                </p>
                <div style="display: flex; gap: 12px;">
                    @foreach($socials as $s)
                        @if(!empty($s['url']))
                            <a href="{{ $s['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $s['label'] }}"
                               style="{{ $socialIconStyle }}"
                               onmouseover="{{ $socialHover }}"
                               onmouseout="{{ $socialOut }}">{{ $s['icon'] }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
            
            <!-- Quick Links Column -->
            <div>
                <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: white;">روابط سريعة</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 12px;">
                        <a href="#about" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 15px; transition: all 0.3s; display: inline-block;" onmouseover="this.style.color='white'; this.style.paddingRight='8px'" onmouseout="this.style.color='rgba(255,255,255,0.8)'; this.style.paddingRight='0'">← عن المنصة</a>
                    </li>
                    <li style="margin-bottom: 12px;">
                        <a href="#values" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 15px; transition: all 0.3s; display: inline-block;" onmouseover="this.style.color='white'; this.style.paddingRight='8px'" onmouseout="this.style.color='rgba(255,255,255,0.8)'; this.style.paddingRight='0'">← القيم الأساسية</a>
                    </li>
                    <li style="margin-bottom: 12px;">
                        <a href="#features" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 15px; transition: all 0.3s; display: inline-block;" onmouseover="this.style.color='white'; this.style.paddingRight='8px'" onmouseout="this.style.color='rgba(255,255,255,0.8)'; this.style.paddingRight='0'">← المميزات</a>
                    </li>
                    <li style="margin-bottom: 12px;">
                        <a href="#contact" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 15px; transition: all 0.3s; display: inline-block;" onmouseover="this.style.color='white'; this.style.paddingRight='8px'" onmouseout="this.style.color='rgba(255,255,255,0.8)'; this.style.paddingRight='0'">← تواصل معنا</a>
                    </li>
                    <li style="margin-bottom: 12px;">
                        <a href="{{ route('login') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 15px; transition: all 0.3s; display: inline-block;" onmouseover="this.style.color='white'; this.style.paddingRight='8px'" onmouseout="this.style.color='rgba(255,255,255,0.8)'; this.style.paddingRight='0'">← تسجيل الدخول</a>
                    </li>
                </ul>
            </div>
            
            <!-- For Schools Column — كل الروابط الآن تشير إلى صفحات/أقسام حقيقية -->
            <div>
                <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: white;">للمدارس</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 12px;">
                        <a href="{{ url('/register') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 15px; transition: all 0.3s; display: inline-block;" onmouseover="this.style.color='white'; this.style.paddingRight='8px'" onmouseout="this.style.color='rgba(255,255,255,0.8)'; this.style.paddingRight='0'">← التسجيل كمدرسة</a>
                    </li>
                    <li style="margin-bottom: 12px;">
                        <a href="{{ url('/#packages') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 15px; transition: all 0.3s; display: inline-block;" onmouseover="this.style.color='white'; this.style.paddingRight='8px'" onmouseout="this.style.color='rgba(255,255,255,0.8)'; this.style.paddingRight='0'">← الباقات والأسعار</a>
                    </li>
                    <li style="margin-bottom: 12px;">
                        <a href="{{ url('/#features') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 15px; transition: all 0.3s; display: inline-block;" onmouseover="this.style.color='white'; this.style.paddingRight='8px'" onmouseout="this.style.color='rgba(255,255,255,0.8)'; this.style.paddingRight='0'">← دليل الاستخدام</a>
                    </li>
                    <li style="margin-bottom: 12px;">
                        <a href="{{ url('/#contact') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 15px; transition: all 0.3s; display: inline-block;" onmouseover="this.style.color='white'; this.style.paddingRight='8px'" onmouseout="this.style.color='rgba(255,255,255,0.8)'; this.style.paddingRight='0'">← الدعم الفني</a>
                    </li>
                    <li style="margin-bottom: 12px;">
                        <a href="{{ url('/#faq') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 15px; transition: all 0.3s; display: inline-block;" onmouseover="this.style.color='white'; this.style.paddingRight='8px'" onmouseout="this.style.color='rgba(255,255,255,0.8)'; this.style.paddingRight='0'">← الأسئلة الشائعة</a>
                    </li>
                </ul>
            </div>
            
            <!-- Contact Info Column -->
            <div>
                <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: white;">معلومات التواصل</h4>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="display: flex; align-items: start; gap: 12px;">
                        <div style="font-size: 20px; min-width: 24px;">📧</div>
                        <div>
                            <div style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 3px;">البريد الإلكتروني</div>
                            <div style="font-size: 15px; color: rgba(255,255,255,0.9);">info@qiyam.edu.sa</div>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: start; gap: 12px;">
                        <div style="font-size: 20px; min-width: 24px;">📱</div>
                        <div>
                            <div style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 3px;">رقم الهاتف</div>
                            <div style="font-size: 15px; color: rgba(255,255,255,0.9); direction: ltr; text-align: right;">+966 50 123 4567</div>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: start; gap: 12px;">
                        <div style="font-size: 20px; min-width: 24px;">📍</div>
                        <div>
                            <div style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 3px;">العنوان</div>
                            <div style="font-size: 15px; color: rgba(255,255,255,0.9);">الرياض، المملكة العربية السعودية</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div style="color: rgba(255,255,255,0.7); font-size: 14px;">
                © 2025 بناء القيم. جميع الحقوق محفوظة.
            </div>
            <div style="display: flex; gap: 25px;">
                <a href="{{ url('/page/privacy-policy') }}" style="color: rgba(255,255,255,0.7); text-decoration: none; font-size: 14px; transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">سياسة الخصوصية</a>
                <a href="{{ url('/page/terms') }}" style="color: rgba(255,255,255,0.7); text-decoration: none; font-size: 14px; transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">شروط الاستخدام</a>
                <a href="{{ url('/#contact') }}" style="color: rgba(255,255,255,0.7); text-decoration: none; font-size: 14px; transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">تواصل معنا</a>
            </div>
        </div>
    </div>
</footer>
