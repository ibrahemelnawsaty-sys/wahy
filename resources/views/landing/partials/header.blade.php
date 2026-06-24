<!-- Header -->
<header style="background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000;">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; padding: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                🏆
            </div>
            <div>
                <h1 style="font-size: 24px; font-weight: 700; color: #1a202c;">بناء القيم</h1>
                <p style="font-size: 12px; color: #718096;">منصة تعليم القيم الأخلاقية</p>
            </div>
        </div>
        
        <nav style="display: flex; gap: 30px; align-items: center;">
            <a href="#about" style="text-decoration: none; color: #4a5568; font-weight: 500; transition: color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#4a5568'">عن المنصة</a>
            <a href="#values" style="text-decoration: none; color: #4a5568; font-weight: 500; transition: color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#4a5568'">القيم</a>
            <a href="#features" style="text-decoration: none; color: #4a5568; font-weight: 500; transition: color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#4a5568'">المميزات</a>
            <a href="#contact" style="text-decoration: none; color: #4a5568; font-weight: 500; transition: color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#4a5568'">تواصل معنا</a>
            <a href="{{ route('login') }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.3s; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.6)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)'">
                تسجيل الدخول
            </a>
        </nav>
    </div>
</header>
