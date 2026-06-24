@php
    $pendingSurveys = session('pending_surveys', collect());
    $showPopup = session('show_survey_popup', false) && $pendingSurveys->isNotEmpty();
@endphp

@if($showPopup)
{{-- كتلة حماية: تخفي كل المحتوى أسفل الاستبيان --}}
<div id="surveyBlockingLayer" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: #0f172a; z-index: 99998;"></div>

<div id="surveyPopupOverlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.95); z-index: 99999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div id="surveyPopupContainer" style="background: white; border-radius: 20px; max-width: 600px; width: 95%; max-height: 90vh; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.5); animation: popupSlideIn 0.4s ease-out;">
        
        @foreach($pendingSurveys as $index => $survey)
        <div class="survey-form" data-survey-id="{{ $survey->id }}" style="{{ $index > 0 ? 'display: none;' : '' }}">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 25px 30px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 40px;">📋</div>
                    <div>
                        <h2 style="margin: 0; font-size: 22px;">{{ $survey->title }}</h2>
                        @if($survey->description)
                        <p style="margin: 8px 0 0; opacity: 0.9; font-size: 14px;">{{ $survey->description }}</p>
                        @endif
                    </div>
                </div>
                <div style="margin-top: 15px; background: rgba(255,255,255,0.2); padding: 12px 16px; border-radius: 10px; font-size: 14px; font-weight: 600;">
                    🔒 هذا الاستبيان إجباري ولا يمكن تخطيه. يرجى الإجابة على جميع الأسئلة للمتابعة في استخدام المنصة.
                </div>
            </div>

            <!-- Questions -->
            <div style="padding: 25px 30px; max-height: 50vh; overflow-y: auto;">
                <form id="surveyForm-{{ $survey->id }}" class="survey-questions-form">
                    @csrf
                    @foreach($survey->questions as $qIndex => $question)
                    <div class="question-item" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #e5e7eb;">
                        <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 12px; font-size: 15px;">
                            <span style="color: #6366f1; margin-left: 5px;">{{ $qIndex + 1 }}.</span>
                            {{ $question->question_text }}
                            @if($question->is_required)
                            <span style="color: #ef4444;">*</span>
                            @endif
                        </label>

                        @switch($question->question_type)
                            @case('text')
                                <input type="text" name="answers[{{ $question->id }}]" 
                                       {{ $question->is_required ? 'required' : '' }}
                                       style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: border-color 0.2s;"
                                       onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e5e7eb'"
                                       placeholder="اكتب إجابتك هنا...">
                                @break

                            @case('textarea')
                                <textarea name="answers[{{ $question->id }}]" rows="3"
                                          {{ $question->is_required ? 'required' : '' }}
                                          style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; resize: vertical; transition: border-color 0.2s;"
                                          onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e5e7eb'"
                                          placeholder="اكتب إجابتك هنا..."></textarea>
                                @break

                            @case('radio')
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    @foreach($question->options ?? [] as $optIndex => $option)
                                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; background: #f9fafb; border-radius: 10px; cursor: pointer; transition: background 0.2s;"
                                           onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#f9fafb'">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}"
                                               {{ $question->is_required ? 'required' : '' }}
                                               style="width: 18px; height: 18px; accent-color: #6366f1;">
                                        <span style="font-size: 14px;">{{ $option }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @break

                            @case('checkbox')
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    @foreach($question->options ?? [] as $optIndex => $option)
                                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; background: #f9fafb; border-radius: 10px; cursor: pointer; transition: background 0.2s;"
                                           onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#f9fafb'">
                                        <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option }}"
                                               style="width: 18px; height: 18px; accent-color: #6366f1;">
                                        <span style="font-size: 14px;">{{ $option }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @break

                            @case('select')
                                <select name="answers[{{ $question->id }}]" 
                                        {{ $question->is_required ? 'required' : '' }}
                                        style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; background: white;">
                                    <option value="">اختر إجابة...</option>
                                    @foreach($question->options ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                                @break

                            @case('rating')
                                <div style="display: flex; gap: 10px; justify-content: center;">
                                    @for($i = 1; $i <= 5; $i++)
                                    <label style="cursor: pointer;">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $i }}"
                                               {{ $question->is_required ? 'required' : '' }}
                                               style="display: none;" class="rating-input">
                                        <span class="rating-star" data-value="{{ $i }}" 
                                              style="font-size: 32px; color: #d1d5db; transition: color 0.2s; display: inline-block;"
                                              onmouseover="highlightStars(this, {{ $question->id }})" 
                                              onmouseout="resetStars({{ $question->id }})"
                                              onclick="selectRating(this, {{ $question->id }}, {{ $i }})">★</span>
                                    </label>
                                    @endfor
                                </div>
                                @break

                            @case('scale')
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 5px;">
                                    @for($i = 1; $i <= 10; $i++)
                                    <label style="flex: 1; text-align: center; cursor: pointer;">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $i }}"
                                               {{ $question->is_required ? 'required' : '' }}
                                               style="display: none;" class="scale-input">
                                        <span class="scale-number" data-value="{{ $i }}"
                                              style="display: block; padding: 10px 5px; background: #f3f4f6; border-radius: 8px; font-weight: 600; transition: all 0.2s;"
                                              onclick="selectScale(this, {{ $question->id }}, {{ $i }})">{{ $i }}</span>
                                    </label>
                                    @endfor
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #6b7280;">
                                    <span>ضعيف جداً</span>
                                    <span>ممتاز</span>
                                </div>
                                @break
                        @endswitch
                    </div>
                    @endforeach
                </form>
            </div>

            <!-- Footer -->
            <div style="padding: 20px 30px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 13px; color: #6b7280;">
                    الاستبيان {{ $index + 1 }} من {{ $pendingSurveys->count() }}
                </div>
                <button type="button" onclick="submitSurvey({{ $survey->id }})"
                        style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 14px 35px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
                        onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 10px 25px rgba(99,102,241,0.4)'"
                        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                    إرسال الإجابات ✓
                </button>
            </div>
        </div>
        @endforeach

        <!-- Success Message -->
        <div id="surveySuccessMessage" style="display: none; padding: 60px 30px; text-align: center;">
            <div style="font-size: 80px; margin-bottom: 20px;">🎉</div>
            <h2 style="color: #10b981; margin-bottom: 15px;">شكراً لك!</h2>
            <p style="color: #6b7280; font-size: 16px;">تم حفظ إجاباتك بنجاح</p>
        </div>
    </div>
</div>

<style>
@keyframes popupSlideIn {
    from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.question-item input:focus,
.question-item textarea:focus,
.question-item select:focus {
    outline: none;
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.scale-number:hover {
    background: #6366f1 !important;
    color: white !important;
}

.scale-number.selected {
    background: #6366f1 !important;
    color: white !important;
}

/* إخفاء scrollbar لمنع التفاعل مع الصفحة */
body.survey-locked {
    overflow: hidden !important;
    pointer-events: none !important;
}

body.survey-locked #surveyPopupOverlay,
body.survey-locked #surveyPopupOverlay * {
    pointer-events: auto !important;
}
</style>

<script>
// ======== قفل المنصة بالكامل حتى إكمال الاستبيان ========

(function() {
    // قفل الجسم
    document.body.classList.add('survey-locked');

    // منع كل اختصارات لوحة المفاتيح
    document.addEventListener('keydown', function(e) {
        const overlay = document.getElementById('surveyPopupOverlay');
        if (!overlay || overlay.style.display === 'none') return;

        // السماح فقط بالمفاتيح داخل النموذج (Tab, letters, numbers, etc.)
        const isInsideForm = e.target.closest('#surveyPopupContainer');
        
        // منع: Escape, F5, Ctrl+R, Ctrl+W, Ctrl+L, Alt+F4, Alt+Left, Alt+Right
        if (e.key === 'Escape' || 
            e.key === 'F5' || 
            (e.ctrlKey && ['r', 'w', 'l', 'n', 't'].includes(e.key.toLowerCase())) ||
            (e.altKey && ['F4', 'ArrowLeft', 'ArrowRight'].includes(e.key))) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // إذا لم يكن داخل النموذج، منع كل شيء
        if (!isInsideForm) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);

    // منع التنقل بالماوس (زر الرجوع والتقدم)
    window.addEventListener('popstate', function(e) {
        const overlay = document.getElementById('surveyPopupOverlay');
        if (overlay && overlay.style.display !== 'none') {
            history.pushState(null, '', window.location.href);
        }
    });
    
    // إضافة state للمنع
    history.pushState(null, '', window.location.href);

    // تحذير عند محاولة إغلاق أو مغادرة الصفحة
    window.addEventListener('beforeunload', function(e) {
        const overlay = document.getElementById('surveyPopupOverlay');
        if (overlay && overlay.style.display !== 'none') {
            e.preventDefault();
            e.returnValue = 'يجب إكمال الاستبيان قبل مغادرة الصفحة';
            return e.returnValue;
        }
    });

    // منع النقر بزر الماوس الأيمن
    document.addEventListener('contextmenu', function(e) {
        const overlay = document.getElementById('surveyPopupOverlay');
        if (overlay && overlay.style.display !== 'none') {
            if (!e.target.closest('#surveyPopupContainer')) {
                e.preventDefault();
            }
        }
    });
})();

function submitSurvey(surveyId) {
    const form = document.getElementById('surveyForm-' + surveyId);
    if (!form) {
        alert('تعذّر العثور على نموذج الاستبيان');
        return;
    }
    const formData = new FormData(form);

    // تحويل FormData إلى object
    const answers = {};
    for (let [key, value] of formData.entries()) {
        const match = key.match(/answers\[(\d+)\](\[\])?/);
        if (match) {
            const questionId = match[1];
            const isArray = match[2];

            if (isArray) {
                if (!answers[questionId]) answers[questionId] = [];
                answers[questionId].push(value);
            } else {
                answers[questionId] = value;
            }
        }
    }

    // الحصول على CSRF token بأمان — كان الكود يفجر TypeError لو meta غير موجود (Issue #41)
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.content : (form.querySelector('input[name="_token"]')?.value || '');
    if (!csrfToken) {
        alert('تعذّر التحقق من الجلسة. يرجى تحديث الصفحة والمحاولة مرة أخرى.');
        return;
    }

    // إرسال الإجابات
    fetch('/survey/' + surveyId + '/submit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ answers: answers })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const currentForm = document.querySelector('.survey-form[data-survey-id="' + surveyId + '"]');
            currentForm.style.display = 'none';
            
            if (data.has_more_surveys) {
                // عرض الاستبيان التالي
                const nextForm = document.querySelector('.survey-form[style*="display: none"]:not([data-survey-id="' + surveyId + '"])');
                if (nextForm) {
                    nextForm.style.display = 'block';
                }
            } else {
                // عرض رسالة النجاح وفك القفل
                document.getElementById('surveySuccessMessage').style.display = 'block';
                setTimeout(() => {
                    // فك قفل الصفحة
                    document.body.classList.remove('survey-locked');
                    document.getElementById('surveyPopupOverlay').style.display = 'none';
                    document.getElementById('surveyBlockingLayer').style.display = 'none';
                    location.reload();
                }, 2000);
            }
        } else {
            alert(data.error || 'حدث خطأ أثناء حفظ الإجابات');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.');
    });
}

function highlightStars(star, questionId) {
    const value = parseInt(star.dataset.value);
    const container = star.closest('.question-item');
    const stars = container.querySelectorAll('.rating-star');
    stars.forEach((s, i) => {
        s.style.color = i < value ? '#fbbf24' : '#d1d5db';
    });
}

function resetStars(questionId) {
    const container = document.querySelector(`input[name="answers[${questionId}]"]:checked`);
    if (container) {
        const value = parseInt(container.value);
        const stars = container.closest('.question-item').querySelectorAll('.rating-star');
        stars.forEach((s, i) => {
            s.style.color = i < value ? '#fbbf24' : '#d1d5db';
        });
    } else {
        const questionItem = document.querySelector(`input[name="answers[${questionId}]"]`).closest('.question-item');
        const stars = questionItem.querySelectorAll('.rating-star');
        stars.forEach(s => s.style.color = '#d1d5db');
    }
}

function selectRating(star, questionId, value) {
    const container = star.closest('.question-item');
    const input = container.querySelector(`input[value="${value}"]`);
    input.checked = true;
    
    const stars = container.querySelectorAll('.rating-star');
    stars.forEach((s, i) => {
        s.style.color = i < value ? '#fbbf24' : '#d1d5db';
    });
}

function selectScale(element, questionId, value) {
    const container = element.closest('.question-item');
    const input = container.querySelector(`input[value="${value}"]`);
    input.checked = true;
    
    const numbers = container.querySelectorAll('.scale-number');
    numbers.forEach((n, i) => {
        if (i < value) {
            n.classList.add('selected');
        } else {
            n.classList.remove('selected');
        }
    });
}
</script>
@endif
