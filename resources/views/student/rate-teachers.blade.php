@extends('layouts.student-app')

@section('title', 'تقييم المعلمين')

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">

<div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
    <h2 style="font-size: 26px; font-weight: 700; margin-bottom: 30px; color: #2d3748;">⭐ تقييم المعلمين</h2>
    
    <div style="display: grid; gap: 25px;">
        @foreach($teachers as $teacher)
        <div style="border: 2px solid #e2e8f0; border-radius: 15px; padding: 25px; transition: all 0.3s;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <h3 style="font-size: 22px; font-weight: 700; color: #2d3748; margin-bottom: 8px;">
                        👨‍🏫 {{ $teacher->name }}
                    </h3>
                    <div style="color: #718096; font-size: 14px; margin-bottom: 15px;">{{ $teacher->email }}</div>
                    
                    <!-- متوسط التقييم -->
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="display: flex; gap: 5px;">
                            @for($i = 1; $i <= 5; $i++)
                                <span style="font-size: 20px; color: {{ $i <= round($teacher->ratings_avg_rating ?? 0) ? '#fbbf24' : '#e5e7eb' }};">⭐</span>
                            @endfor
                        </div>
                        <span style="color: #4a5568; font-weight: 600;">
                            {{ number_format($teacher->ratings_avg_rating ?? 0, 1) }}
                        </span>
                        <span style="color: #718096; font-size: 13px;">
                            ({{ $teacher->ratings_count }} تقييم)
                        </span>
                    </div>
                </div>
                
                <button onclick="openRatingModal({{ $teacher->id }}, '{{ $teacher->name }}', {{ $teacher->ratings->first()->rating ?? 0 }}, '{{ $teacher->ratings->first()->comment ?? '' }}')" 
                        style="background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; padding: 12px 25px; border-radius: 12px; border: none; cursor: pointer; font-weight: 600; font-size: 15px;">
                    {{ $teacher->ratings->count() > 0 ? 'تعديل التقييم' : 'تقييم المعلم' }}
                </button>
            </div>
            
            @if($teacher->ratings->first())
            <div style="background: #f7fafc; border-radius: 12px; padding: 15px; margin-top: 15px;">
                <div style="font-size: 13px; color: #718096; margin-bottom: 5px;">تقييمك السابق:</div>
                <div style="display: flex; gap: 5px; margin-bottom: 8px;">
                    @for($i = 1; $i <= 5; $i++)
                        <span style="font-size: 16px; color: {{ $i <= $teacher->ratings->first()->rating ? '#fbbf24' : '#e5e7eb' }};">⭐</span>
                    @endfor
                </div>
                @if($teacher->ratings->first()->comment)
                <div style="color: #4a5568; font-size: 14px;">{{ $teacher->ratings->first()->comment }}</div>
                @endif
            </div>
            @endif
        </div>
        @endforeach
        
        @if($teachers->count() === 0)
        <div style="text-align: center; padding: 60px 20px; color: #718096;">
            <div style="font-size: 60px; margin-bottom: 15px;">👨‍🏫</div>
            <p style="font-size: 16px;">لا يوجد معلمون لتقييمهم</p>
        </div>
        @endif
    </div>
</div>

<!-- بوب اب احترافي -->
<div id="premiumPopup" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(4px); transition: all 0.3s ease;">
    <div id="popupContent" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 24px; width: 90%; max-width: 420px; padding: 40px 30px; box-shadow: 0 25px 80px rgba(0,0,0,0.25); text-align: center; transform: scale(0.8); opacity: 0; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);">
        <div id="popupIcon" style="font-size: 56px; margin-bottom: 16px;"></div>
        <h3 id="popupTitle" style="font-size: 20px; font-weight: 700; color: #1a202c; margin-bottom: 10px;"></h3>
        <p id="popupMessage" style="font-size: 15px; color: #64748b; margin-bottom: 24px; line-height: 1.6;"></p>
        <button onclick="closePremiumPopup()" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; border: none; border-radius: 14px; padding: 12px 40px; font-size: 15px; font-weight: 600; cursor: pointer; font-family: 'Cairo', sans-serif; transition: all 0.3s; box-shadow: 0 4px 15px rgba(99,102,241,0.4);">
            حسناً
        </button>
    </div>
</div>

<!-- نافذة التقييم -->
<div id="ratingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(3px);">
    <div style="background: white; border-radius: 20px; width: 90%; max-width: 500px; padding: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 20px;" id="modalTitle">تقييم المعلم</h3>
        
        <form id="ratingForm" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" id="teacherId">
            
            <div>
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #4a5568;">التقييم</label>
                <div style="display: flex; gap: 10px; justify-content: center; margin: 15px 0;">
                    <span class="star" data-value="1" onclick="setRating(1)" style="font-size: 40px; cursor: pointer; transition: all 0.2s;">⭐</span>
                    <span class="star" data-value="2" onclick="setRating(2)" style="font-size: 40px; cursor: pointer; transition: all 0.2s;">⭐</span>
                    <span class="star" data-value="3" onclick="setRating(3)" style="font-size: 40px; cursor: pointer; transition: all 0.2s;">⭐</span>
                    <span class="star" data-value="4" onclick="setRating(4)" style="font-size: 40px; cursor: pointer; transition: all 0.2s;">⭐</span>
                    <span class="star" data-value="5" onclick="setRating(5)" style="font-size: 40px; cursor: pointer; transition: all 0.2s;">⭐</span>
                </div>
                <input type="hidden" id="ratingValue" required>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #4a5568;">تعليق (اختياري)</label>
                <textarea id="ratingComment" style="width: 100%; border: 2px solid #e2e8f0; border-radius: 12px; padding: 12px; resize: none; font-family: 'Cairo', sans-serif;" rows="4" placeholder="شارك رأيك عن المعلم..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" id="submitBtn" style="flex: 1; background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; border: none; border-radius: 12px; padding: 12px; cursor: pointer; font-weight: 600; font-family: 'Cairo', sans-serif; transition: all 0.3s;">
                    إرسال التقييم
                </button>
                <button type="button" onclick="closeRatingModal()" style="background: #cbd5e0; color: #2d3748; border: none; border-radius: 12px; padding: 12px 25px; cursor: pointer; font-weight: 600; font-family: 'Cairo', sans-serif;">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentRating = 0;

// ==================== Premium Popup System ====================
function showPremiumPopup(type, title, message) {
    const popup = document.getElementById('premiumPopup');
    const content = document.getElementById('popupContent');
    const iconEl = document.getElementById('popupIcon');
    const titleEl = document.getElementById('popupTitle');
    const messageEl = document.getElementById('popupMessage');

    const config = {
        success: { icon: '✅', gradient: 'linear-gradient(135deg, #10b981, #059669)' },
        error: { icon: '❌', gradient: 'linear-gradient(135deg, #ef4444, #dc2626)' },
        warning: { icon: '⚠️', gradient: 'linear-gradient(135deg, #f59e0b, #d97706)' },
        info: { icon: 'ℹ️', gradient: 'linear-gradient(135deg, #6366f1, #8b5cf6)' }
    };

    const c = config[type] || config.info;
    iconEl.textContent = c.icon;
    titleEl.textContent = title;
    messageEl.textContent = message;
    popup.querySelector('button').style.background = c.gradient;

    popup.style.display = 'flex';
    requestAnimationFrame(() => {
        content.style.transform = 'scale(1)';
        content.style.opacity = '1';
    });

    // Auto-dismiss after 4 seconds for success
    if (type === 'success') {
        setTimeout(() => closePremiumPopup(), 4000);
    }
}

function closePremiumPopup() {
    const popup = document.getElementById('premiumPopup');
    const content = document.getElementById('popupContent');
    content.style.transform = 'scale(0.8)';
    content.style.opacity = '0';
    setTimeout(() => { popup.style.display = 'none'; }, 300);
}

// ==================== Rating Modal ====================
function openRatingModal(teacherId, teacherName, existingRating = 0, existingComment = '') {
    document.getElementById('teacherId').value = teacherId;
    document.getElementById('modalTitle').textContent = `تقييم المعلم: ${teacherName}`;
    document.getElementById('ratingComment').value = existingComment;
    document.getElementById('ratingModal').style.display = 'flex';
    
    if (existingRating > 0) {
        setRating(existingRating);
    } else {
        setRating(0);
    }
}

function closeRatingModal() {
    document.getElementById('ratingModal').style.display = 'none';
    document.getElementById('ratingForm').reset();
    setRating(0);
}

function setRating(rating) {
    currentRating = rating;
    document.getElementById('ratingValue').value = rating;
    
    const stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.style.filter = 'grayscale(0%)';
            star.style.transform = 'scale(1.1)';
        } else {
            star.style.filter = 'grayscale(100%)';
            star.style.transform = 'scale(1)';
        }
    });
}

// ==================== Form Submit ====================
document.getElementById('ratingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const teacherId = document.getElementById('teacherId').value;
    const rating = document.getElementById('ratingValue').value;
    const comment = document.getElementById('ratingComment').value;
    
    if (!rating || rating < 1) {
        showPremiumPopup('warning', 'تنبيه', 'الرجاء اختيار تقييم أولاً');
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'جاري الإرسال...';
    
    fetch('{{ route("student.rate.submit") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            teacher_id: teacherId,
            rating: rating,
            comment: comment
        })
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'إرسال التقييم';

        if(data.success) {
            closeRatingModal();
            showPremiumPopup('success', 'تم بنجاح! 🎉', data.message || 'تم إرسال التقييم بنجاح');
            setTimeout(() => location.reload(), 2000);
        } else {
            showPremiumPopup('error', 'خطأ', data.error || data.message || 'حدث خطأ أثناء إرسال التقييم');
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'إرسال التقييم';
        showPremiumPopup('error', 'خطأ في الاتصال', 'تعذر الاتصال بالخادم، يرجى المحاولة مرة أخرى');
    });
});

// ==================== Star Hover Effects ====================
document.querySelectorAll('.star').forEach((star, index) => {
    star.addEventListener('mouseenter', function() {
        const value = parseInt(this.dataset.value);
        document.querySelectorAll('.star').forEach((s, i) => {
            if (i < value) {
                s.style.filter = 'grayscale(0%)';
            }
        });
    });
});

document.getElementById('ratingModal').querySelector('form').addEventListener('mouseleave', function() {
    setRating(currentRating);
});
</script>

</div>
@endsection
