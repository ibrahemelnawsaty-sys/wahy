@extends('layouts.auth')

@section('title', '429 - طلبات كثيرة جداً')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="text-align: center; padding: 60px 40px;">
        <!-- Icon -->
        <div style="font-size: 100px; margin-bottom: 30px; animation: pulse 2s ease-in-out infinite;">⏱️</div>
        
        <!-- Error Code -->
        <div style="display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); border-radius: 50px; margin-bottom: 20px;">
            <h1 style="font-size: 24px; font-weight: 700; color: white; margin: 0;">429</h1>
        </div>
        
        <!-- Title -->
        <h2 style="font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 20px;">
            محاولات كثيرة جداً
        </h2>
        
        <!-- Message -->
        <div style="background: #fff7ed; border: 2px solid #fed7aa; border-radius: 12px; padding: 25px; margin-bottom: 30px; max-width: 500px; margin-left: auto; margin-right: auto;">
            <p style="font-size: 17px; color: #92400e; margin: 0; line-height: 1.8;">
                <i class="fas fa-info-circle" style="color: #f59e0b; margin-left: 8px;"></i>
                لقد تجاوزت عدد المحاولات المسموح بها.
            </p>
            <p style="font-size: 17px; color: #92400e; margin: 15px 0 0 0; line-height: 1.8;">
                <strong>يرجى الانتظار دقيقة واحدة</strong> قبل المحاولة مرة أخرى.
            </p>
        </div>
        
        <!-- Timer Display -->
        <div id="countdown" style="font-size: 48px; font-weight: 700; color: #f59e0b; margin-bottom: 30px; font-family: 'Courier New', monospace;">
            00:60
        </div>
        
        <!-- Buttons -->
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('login') }}" class="btn btn-primary" style="text-decoration: none;">
                <i class="fas fa-home"></i>
                <span>صفحة تسجيل الدخول</span>
            </a>
            <button onclick="location.reload()" class="btn btn-secondary" id="retryBtn" disabled>
                <i class="fas fa-redo"></i>
                <span>إعادة المحاولة (<span id="retryCounter">60</span>)</span>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.auth-card {
    animation: fadeIn 0.5s ease-out;
}
</style>

<script>
// Countdown timer (60 seconds)
let timeLeft = 60;
const countdownElement = document.getElementById('countdown');
const retryBtn = document.getElementById('retryBtn');
const retryCounter = document.getElementById('retryCounter');

function updateCountdown() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    
    const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    countdownElement.textContent = formattedTime;
    retryCounter.textContent = timeLeft;
    
    if (timeLeft <= 0) {
        clearInterval(countdownInterval);
        retryBtn.disabled = false;
        retryBtn.style.opacity = '1';
        retryBtn.style.cursor = 'pointer';
        retryBtn.innerHTML = '<i class="fas fa-check"></i><span>إعادة المحاولة الآن</span>';
        countdownElement.textContent = '✓';
        countdownElement.style.color = '#10b981';
    }
    
    timeLeft--;
}

const countdownInterval = setInterval(updateCountdown, 1000);
updateCountdown();
</script>
@endsection
