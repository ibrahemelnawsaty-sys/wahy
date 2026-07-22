{{--
    عارض أسئلة النشاط الموحّد (المرحلة 4).
    مُستخرَج حرفيًّا من admin/activities/show.blade.php — العارض الوحيد الذي يغطّي كل الصيغ
    (image_order صيغة المعلّم/الأدمن، خيارات مع correct_index/answer/correct_answer، letter_choice،
    short_answer). ذاتيّ الاحتواء (يحمل CSS خاصّته عبر @once) ليعمل تحت أيّ طبقة دور.
    يتطلّب المتغيّر: $activity.
--}}
@once
<style>
.questions-section { background: white; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
.questions-section .section-title { font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 20px; }
.questions-section .question-item { background: #f8fafc; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
.questions-section .question-number { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: var(--color-primary, #667eea); color: white; border-radius: 50%; font-weight: 700; margin-bottom: 12px; }
.questions-section .question-text { font-size: 16px; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
.questions-section .options-list { display: flex; flex-direction: column; gap: 8px; margin-top: 12px; }
.questions-section .option-item { padding: 10px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
.questions-section .option-item.correct { background: #dcfce7; border-color: #16a34a; color: #166534; font-weight: 600; }
.activity-empty-questions { text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 12px; }
.activity-empty-questions .empty-icon { font-size: 64px; margin-bottom: 16px; }
</style>
@endonce

@if($activity->questions && is_array($activity->questions) && count($activity->questions) > 0)
<div class="questions-section">
    @php
        // Detect format: teacher format has 'image_url' at top level, admin format has 'type' + 'images'
        $firstQ = $activity->questions[0] ?? [];
        $isTeacherImageFormat = isset($firstQ['image_url']);
    @endphp

    @if($activity->type === 'image_order' && $isTeacherImageFormat)
        {{-- Teacher format: [{image_url, caption, order}] --}}
        <h2 class="section-title">🖼️ صور النشاط ({{ count($activity->questions) }})</h2>
        <p style="color: #64748b; margin-bottom: 20px;">الطالب سيرتب هذه الصور بالترتيب الصحيح.</p>
        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
            @foreach($activity->questions as $index => $img)
            <div style="text-align: center; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; padding: 10px;">
                <div style="background: var(--color-primary, #667eea); color: white; border-radius: 50%; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; margin-bottom: 8px;">{{ $img['order'] ?? $index + 1 }}</div>
                @if(!empty($img['image_url']))
                    <div>
                        <img src="{{ $img['image_url'] }}" alt="{{ $img['caption'] ?? 'صورة ' . ($index+1) }}"
                             style="width: 160px; height: 160px; object-fit: contain; background: #f1f5f9; border-radius: 8px;"
                             onerror="this.outerHTML='<div style=\'width:160px;height:160px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#dc2626;font-size:14px;\'>❌ صورة غير متاحة</div>';">
                    </div>
                @else
                    <div style="width:140px;height:140px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:40px;">🖼️</div>
                @endif
                @if(!empty($img['caption']))
                    <div style="font-size: 12px; color: #64748b; margin-top: 6px;">{{ $img['caption'] }}</div>
                @endif
            </div>
            @endforeach
        </div>
    @else
        <h2 class="section-title">❓ الأسئلة ({{ count($activity->questions) }})</h2>

        @foreach($activity->questions as $index => $question)
        <div class="question-item">
            <div class="question-number">{{ $index + 1 }}</div>
            <div class="question-text">{{ $question['question'] ?? $question['text'] ?? 'سؤال بدون نص' }}</div>

            {{-- Admin image_order question type (inside a quiz) --}}
            @if(isset($question['type']) && $question['type'] === 'image_order' && !empty($question['images']))
                <p style="color: #94a3b8; font-size: 13px; margin: 8px 0;">الطالب سيرتب هذه الصور بالترتيب الصحيح</p>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px;">
                    @foreach($question['images'] as $imgIdx => $img)
                    <div style="text-align: center; background: white; border: 2px solid #e2e8f0; border-radius: 10px; padding: 8px;">
                        <div style="background: var(--color-primary, #667eea); color: white; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; margin-bottom: 6px;">{{ $imgIdx + 1 }}</div>
                        @if(!empty($img['url']))
                            <div>
                                <img src="{{ $img['url'] }}" alt="{{ $img['description'] ?? 'صورة' }}"
                                     style="width: 140px; height: 140px; object-fit: contain; background: #f1f5f9; border-radius: 8px;"
                                     onerror="this.outerHTML='<div style=\'width:140px;height:140px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#dc2626;font-size:13px;\'>❌ غير متاحة</div>';">
                            </div>
                        @else
                            <div style="width:120px;height:120px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:32px;">🖼️</div>
                        @endif
                        @if(!empty($img['description']))
                            <div style="font-size: 11px; color: #64748b; margin-top: 5px;">{{ $img['description'] }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @elseif(isset($question['options']) && is_array($question['options']))
                <div class="options-list">
                    @foreach($question['options'] as $optIdx => $option)
                    @php
                        $isCorrectByIndex = isset($question['correct_index']) && (int) $question['correct_index'] === (int) $optIdx;
                        $isCorrectByValue = isset($question['answer']) && $option == $question['answer'];
                        $isCorrectByOldKey = isset($question['correct_answer']) && $question['correct_answer'] === $optIdx;
                        $isCorrect = $isCorrectByIndex || $isCorrectByValue || $isCorrectByOldKey;
                    @endphp
                    <div class="option-item {{ $isCorrect ? 'correct' : '' }}">
                        {{ is_string($option) ? $option : json_encode($option) }}
                        @if($isCorrect) ✅ @endif
                    </div>
                    @endforeach
                    @if(($question['type'] ?? null) === 'letter_choice' && !empty($question['word']))
                        <div style="margin-top: 10px; padding: 10px 14px; background: #ecfdf5; border-right: 4px solid #10b981; border-radius: 8px;">
                            <strong style="color: #065f46;">🎯 الكلمة المستهدفة:</strong> {{ $question['word'] }}
                        </div>
                    @endif
                </div>
            @elseif(($question['type'] ?? null) === 'short_answer')
                {{-- إجابة قصيرة: عرض الإجابة الصحيحة المتوقعة (Issue #37) --}}
                <div style="margin-top: 10px; padding: 12px 16px; background: #f0fdf4; border-right: 4px solid #10b981; border-radius: 8px;">
                    <div style="color: #065f46; font-size: 13px; font-weight: 600; margin-bottom: 4px;">الإجابة الصحيحة المتوقعة:</div>
                    <div style="color: #064e3b; font-size: 15px; font-weight: 700;">
                        {{ $question['answer'] ?? $question['correct_answer'] ?? 'لم تُحدّد بعد' }}
                    </div>
                </div>
            @elseif(($question['type'] ?? null) === 'letter_choice' && !empty($question['word']))
                <div style="margin-top: 10px; padding: 10px 14px; background: #ecfdf5; border-right: 4px solid #10b981; border-radius: 8px;">
                    <strong style="color: #065f46;">🎯 الكلمة المستهدفة:</strong> {{ $question['word'] }}
                </div>
            @endif
        </div>
        @endforeach
    @endif
</div>
@else
<div class="activity-empty-questions">
    <div class="empty-icon">❓</div>
    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">لا توجد أسئلة لهذا النشاط</h3>
    <p style="color: #64748b;">لم تُضَف أسئلة لهذا النشاط بعد.</p>
</div>
@endif
