@extends('layouts.teacher')

@section('title', 'بنك الأسئلة')

@push('styles')
<style>
    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-up { animation: slideInUp 0.6s ease-out; }
    .hover-lift { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
</style>
@endpush

@section('content')

<!-- Header Section -->
<div class="animate-up" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 25px; padding: 35px; margin-bottom: 30px; box-shadow: 0 15px 50px rgba(16, 185, 129, 0.3); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: white; margin-bottom: 8px;">❓ بنك الأسئلة</h1>
            <p style="color: rgba(255,255,255,0.95); font-size: 16px;">إضافة أسئلة تحتاج موافقة السوبر أدمن</p>
        </div>
        <a href="{{ route('teacher.question-bank.create') }}" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white; padding: 15px 30px; border-radius: 15px; border: 2px solid rgba(255,255,255,0.3); font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.3s; text-decoration: none;"
                onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-3px)'"
                onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)'">
            ➕ إضافة سؤال جديد
        </a>
    </div>
</div>

<!-- Stats Cards -->
@if(session('success'))
<div class="animate-up" style="background: #dcfce7; border: 2px solid #86efac; border-radius: 16px; padding: 18px 25px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <span style="font-size: 24px;">✅</span>
    <span style="color: #166534; font-weight: 600; font-size: 15px;">{{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div class="animate-up" style="background: #fee2e2; border: 2px solid #fca5a5; border-radius: 16px; padding: 18px 25px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <span style="font-size: 24px;">❌</span>
    <span style="color: #991b1b; font-weight: 600; font-size: 15px;">{{ session('error') }}</span>
</div>
@endif
<div class="animate-up" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="hover-lift" style="background: white; border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <div style="font-size: 48px; margin-bottom: 10px;">📊</div>
        <div style="font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 5px;">{{ $stats['total'] }}</div>
        <div style="color: #718096; font-size: 14px; font-weight: 600;">إجمالي الأسئلة</div>
    </div>
    <div class="hover-lift" style="background: #fef3c7; border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <div style="font-size: 48px; margin-bottom: 10px;">⏳</div>
        <div style="font-size: 32px; font-weight: 700; color: #92400e; margin-bottom: 5px;">{{ $stats['pending'] }}</div>
        <div style="color: #78350f; font-size: 14px; font-weight: 600;">في الانتظار</div>
    </div>
    <div class="hover-lift" style="background: #dcfce7; border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <div style="font-size: 48px; margin-bottom: 10px;">✅</div>
        <div style="font-size: 32px; font-weight: 700; color: #166534; margin-bottom: 5px;">{{ $stats['approved'] }}</div>
        <div style="color: #14532d; font-size: 14px; font-weight: 600;">معتمدة</div>
    </div>
    <div class="hover-lift" style="background: #fee2e2; border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <div style="font-size: 48px; margin-bottom: 10px;">❌</div>
        <div style="font-size: 32px; font-weight: 700; color: #991b1b; margin-bottom: 5px;">{{ $stats['rejected'] }}</div>
        <div style="color: #7f1d1d; font-size: 14px; font-weight: 600;">مرفوضة</div>
    </div>
</div>

<!-- Questions Grid -->
<div class="animate-up" style="background: white; border-radius: 25px; padding: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
    <h2 style="font-size: 24px; font-weight: 700; color: #1a202c; margin-bottom: 25px;">قائمة الأسئلة</h2>
    
    @if($questions->isEmpty())
    <div style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;">❓</div>
        <h3 style="font-size: 20px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">لا توجد أسئلة بعد</h3>
        <p style="color: #718096; margin-bottom: 20px;">ابدأ بإضافة سؤال جديد إلى بنك الأسئلة</p>
        <button onclick="showAddQuestionModal()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px 30px; border-radius: 12px; border: none; font-weight: 700; font-size: 16px; cursor: pointer;">
            ➕ إضافة سؤال جديد
        </button>
    </div>
    @else
    <div style="display: grid; gap: 20px;">
        @foreach($questions as $question)
        <div class="hover-lift" style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 18px; padding: 25px; border: 2px solid #e2e8f0; position: relative; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);">
                            ❓
                        </div>
                        <div style="flex: 1;">
                            <h3 style="font-size: 20px; font-weight: 700; color: #1a202c; margin-bottom: 5px;">{{ $question->title }}</h3>
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <span style="background: #e2e8f0; color: #4a5568; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                    @if($question->question_type === 'multiple_choice')
                                    اختيار متعدد
                                    @elseif($question->question_type === 'true_false')
                                    صح/خطأ
                                    @elseif($question->question_type === 'short_answer')
                                    إجابة قصيرة
                                    @else
                                    مقال
                                    @endif
                                </span>
                                <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                    @if($question->difficulty === 'easy')
                                    سهل
                                    @elseif($question->difficulty === 'medium')
                                    متوسط
                                    @else
                                    صعب
                                    @endif
                                </span>
                                <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">⭐ {{ $question->points }} نقطة</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 15px; border-radius: 12px; margin-bottom: 15px; border-right: 4px solid #10b981;">
                        <p style="color: #1a202c; font-size: 15px; font-weight: 600; line-height: 1.6; margin-bottom: 10px;">{{ $question->question_text }}</p>
                        @if($question->options && is_array($question->options))
                        <div style="margin-top: 10px;">
                            @foreach($question->options as $option)
                            <div style="padding: 8px; margin-bottom: 5px; background: #f7fafc; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                                @if(isset($option['is_correct']) && $option['is_correct'])
                                <span style="color: #10b981; font-weight: 700;">✓</span>
                                @else
                                <span style="color: #cbd5e0;">○</span>
                                @endif
                                <span style="color: #4a5568; font-size: 14px;">{{ $option['text'] ?? $option }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    
                    @if($question->explanation)
                    <div style="background: #f0fdf4; padding: 12px; border-radius: 10px; border-right: 3px solid #10b981; margin-bottom: 15px;">
                        <div style="font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 5px;">💡 الشرح:</div>
                        <p style="color: #14532d; font-size: 14px; line-height: 1.6;">{{ $question->explanation }}</p>
                    </div>
                    @endif
                    
                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        @if($question->lesson)
                        <div style="display: flex; align-items: center; gap: 6px; color: #718096; font-size: 13px;">
                            <span>📖</span>
                            <span>{{ $question->lesson->title }}</span>
                        </div>
                        @endif
                        <div style="display: flex; align-items: center; gap: 6px; color: #718096; font-size: 13px;">
                            <span>📅</span>
                            <span>{{ $question->created_at->diffForHumans() }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; color: #718096; font-size: 13px;">
                            <span>🔄</span>
                            <span>استخدم {{ $question->usage_count }} مرة</span>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 10px; min-width: 150px;">
                    @if($question->status === 'pending')
                    <span style="background: #fef3c7; color: #92400e; padding: 8px 12px; border-radius: 10px; font-size: 13px; font-weight: 700; text-align: center;">⏳ في الانتظار</span>
                    @elseif($question->status === 'approved')
                    <span style="background: #dcfce7; color: #166534; padding: 8px 12px; border-radius: 10px; font-size: 13px; font-weight: 700; text-align: center;">✅ معتمدة</span>
                    @if($question->approved_at)
                    <div style="font-size: 11px; color: #718096; text-align: center;">
                        {{ $question->approved_at->format('Y-m-d') }}
                    </div>
                    @endif
                    @else
                    <span style="background: #fee2e2; color: #991b1b; padding: 8px 12px; border-radius: 10px; font-size: 13px; font-weight: 700; text-align: center;">❌ مرفوضة</span>
                    @if($question->rejection_reason)
                    <div style="font-size: 11px; color: #718096; text-align: center; margin-top: 5px;">
                        {{ Str::limit($question->rejection_reason, 50) }}
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    <div style="margin-top: 30px; display: flex; justify-content: center;">
        {{ $questions->links() }}
    </div>
    @endif
</div>

<!-- Add Question Modal -->
<div id="addQuestionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto; padding: 20px;">
    <div style="max-width: 800px; margin: 50px auto; background: white; border-radius: 25px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); position: relative;">
        <button onclick="closeAddQuestionModal()" style="position: absolute; top: 20px; left: 20px; background: #f1f5f9; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 20px; color: #475569;">✕</button>
        
        <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px; text-align: center;">➕ إضافة سؤال جديد</h2>
        
        <form id="addQuestionForm" method="POST" action="{{ route('teacher.question-bank.store') }}">
            @csrf
            
            <div style="display: grid; gap: 20px;">
                <div>
                    <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">عنوان السؤال <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="title" required placeholder="مثال: ما هي أركان الإسلام؟" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
                </div>
                
                <div>
                    <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">نص السؤال <span style="color: #ef4444;">*</span></label>
                    <textarea name="question_text" rows="4" required placeholder="اكتب نص السؤال الكامل هنا..." style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">نوع السؤال <span style="color: #ef4444;">*</span></label>
                        <select name="question_type" id="questionType" required onchange="toggleQuestionType()" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px;">
                            <option value="multiple_choice">اختيار متعدد</option>
                            <option value="true_false">صح / خطأ</option>
                            <option value="short_answer">إجابة قصيرة</option>
                            <option value="essay">مقال</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">الصعوبة <span style="color: #ef4444;">*</span></label>
                        <select name="difficulty" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px;">
                            <option value="easy">سهل</option>
                            <option value="medium" selected>متوسط</option>
                            <option value="hard">صعب</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">النقاط <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="points" value="10" min="1" max="50" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px;">
                    </div>
                </div>
                
                <div>
                    <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">الدرس</label>
                    <select name="lesson_id" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px;">
                        <option value="">اختر درس (اختياري)</option>
                        @foreach(\App\Models\Lesson::all() as $lesson)
                        <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- خيارات الاختيار المتعدد - تظهر بالافتراضي -->
                <div id="optionsSection">
                    <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 12px;">الخيارات <span style="color: #ef4444;">*</span></label>
                    <div id="optionsContainer" style="display: grid; gap: 10px;">
                        <div class="option-row" style="display: flex; align-items: center; gap: 10px;">
                            <input type="text" name="options[0][text]" placeholder="الخيار 1" required style="flex: 1; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #475569; white-space: nowrap;"><input type="checkbox" name="options[0][is_correct]" value="1"> صحيح</label>
                            <button type="button" onclick="removeOption(this)" style="background: #fee2e2; color: #991b1b; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 12px;">✕</button>
                        </div>
                        <div class="option-row" style="display: flex; align-items: center; gap: 10px;">
                            <input type="text" name="options[1][text]" placeholder="الخيار 2" required style="flex: 1; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #475569; white-space: nowrap;"><input type="checkbox" name="options[1][is_correct]" value="1"> صحيح</label>
                            <button type="button" onclick="removeOption(this)" style="background: #fee2e2; color: #991b1b; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 12px;">✕</button>
                        </div>
                        <div class="option-row" style="display: flex; align-items: center; gap: 10px;">
                            <input type="text" name="options[2][text]" placeholder="الخيار 3" style="flex: 1; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #475569; white-space: nowrap;"><input type="checkbox" name="options[2][is_correct]" value="1"> صحيح</label>
                            <button type="button" onclick="removeOption(this)" style="background: #fee2e2; color: #991b1b; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 12px;">✕</button>
                        </div>
                        <div class="option-row" style="display: flex; align-items: center; gap: 10px;">
                            <input type="text" name="options[3][text]" placeholder="الخيار 4" style="flex: 1; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #475569; white-space: nowrap;"><input type="checkbox" name="options[3][is_correct]" value="1"> صحيح</label>
                            <button type="button" onclick="removeOption(this)" style="background: #fee2e2; color: #991b1b; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 12px;">✕</button>
                        </div>
                    </div>
                    <button type="button" onclick="addOption()" style="margin-top: 10px; background: #dcfce7; color: #166534; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        ➕ إضافة خيار
                    </button>
                </div>

                <!-- صح/خطأ وإجابة قصيرة -->
                <div id="correctAnswerSection" style="display: none;">
                    <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">الإجابة الصحيحة</label>
                    <input type="text" name="correct_answer" placeholder="اكتب الإجابة الصحيحة" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px;">
                </div>
                
                <div>
                    <label style="display: block; font-weight: 700; color: #1a202c; margin-bottom: 8px;">💡 شرح الإجابة (اختياري)</label>
                    <textarea name="explanation" rows="3" placeholder="اكتب شرحاً للإجابة الصحيحة..." style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px;"></textarea>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 10px;">
                    <button type="submit" style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 15px; border-radius: 12px; border: none; font-weight: 700; font-size: 16px; cursor: pointer;">
                        💾 حفظ السؤال
                    </button>
                    <button type="button" onclick="closeAddQuestionModal()" style="flex: 1; background: #f1f5f9; color: #475569; padding: 15px; border-radius: 12px; border: none; font-weight: 700; font-size: 16px; cursor: pointer;">
                        إلغاء
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function showAddQuestionModal() {
    document.getElementById('addQuestionModal').style.display = 'flex';
}

function closeAddQuestionModal() {
    document.getElementById('addQuestionModal').style.display = 'none';
}

let optionIndex = 4; // already have 0-3

function toggleQuestionType() {
    const type = document.getElementById('questionType').value;
    const optionsSection = document.getElementById('optionsSection');
    const correctAnswerSection = document.getElementById('correctAnswerSection');
    
    if (type === 'multiple_choice') {
        optionsSection.style.display = 'block';
        correctAnswerSection.style.display = 'none';
        // Make option inputs required
        document.querySelectorAll('#optionsContainer .option-row:nth-child(-n+2) input[type="text"]').forEach(i => i.required = true);
    } else if (type === 'true_false' || type === 'short_answer') {
        optionsSection.style.display = 'none';
        correctAnswerSection.style.display = 'block';
        document.querySelectorAll('#optionsContainer input[type="text"]').forEach(i => i.required = false);
    } else {
        // essay
        optionsSection.style.display = 'none';
        correctAnswerSection.style.display = 'none';
        document.querySelectorAll('#optionsContainer input[type="text"]').forEach(i => i.required = false);
    }
}

function addOption() {
    const container = document.getElementById('optionsContainer');
    const div = document.createElement('div');
    div.className = 'option-row';
    div.style.cssText = 'display: flex; align-items: center; gap: 10px;';
    div.innerHTML = `
        <input type="text" name="options[${optionIndex}][text]" placeholder="الخيار ${optionIndex + 1}" style="flex: 1; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
        <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #475569; white-space: nowrap;"><input type="checkbox" name="options[${optionIndex}][is_correct]" value="1"> صحيح</label>
        <button type="button" onclick="removeOption(this)" style="background: #fee2e2; color: #991b1b; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 12px;">✕</button>
    `;
    container.appendChild(div);
    optionIndex++;
}

function removeOption(btn) {
    const container = document.getElementById('optionsContainer');
    if (container.querySelectorAll('.option-row').length > 2) {
        btn.closest('.option-row').remove();
    } else {
        alert('يجب أن يكون هناك خياران على الأقل');
    }
}
</script>

@endsection

