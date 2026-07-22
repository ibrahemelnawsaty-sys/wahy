@extends('layouts.admin')

@section('page-title', 'تفاصيل النشاط')

@section('content')
<style>
.activity-header {
    background: white;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 24px;
}

.breadcrumb {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 16px;
    font-size: 14px;
    flex-wrap: wrap;
}

.breadcrumb-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #f1f5f9;
    border-radius: 6px;
    color: #475569;
    font-weight: 600;
}

.breadcrumb-separator {
    color: #cbd5e1;
}

.activity-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
}

.type-quiz { background: #e0e7ff; color: #4338ca; }
.type-exercise { background: #dbeafe; color: #1e40af; }
.type-project { background: #fce7f3; color: #9f1239; }

.activity-title {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
}

.status-active { background: #dcfce7; color: #166534; }
.status-inactive { background: #fee2e2; color: #991b1b; }

.activity-description {
    color: #64748b;
    font-size: 16px;
    line-height: 1.8;
    margin-bottom: 24px;
}

.activity-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    padding: 24px 0;
    border-top: 2px solid #f1f5f9;
    border-bottom: 2px solid #f1f5f9;
    margin-bottom: 24px;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.meta-label {
    font-size: 13px;
    color: #94a3b8;
    font-weight: 600;
}

.meta-value {
    font-size: 18px;
    color: #1e293b;
    font-weight: 600;
}

.questions-section {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 20px;
}

.question-item {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
}

.question-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: var(--color-primary);
    color: white;
    border-radius: 50%;
    font-weight: 700;
    margin-bottom: 12px;
}

.question-text {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 12px;
}

.options-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 12px;
}

.option-item {
    padding: 10px 16px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.option-item.correct {
    background: #dcfce7;
    border-color: #16a34a;
    color: #166534;
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f8fafc;
    border-radius: 12px;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 16px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    border: none;
}

.btn-primary { background: var(--color-primary); color: white; }
.btn-secondary { background: #e2e8f0; color: #475569; }

.header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
</style>

<div class="activity-header">
    <div class="breadcrumb">
        @if($activity->lesson)
        <span class="breadcrumb-item">
            {{ $activity->lesson->concept?->value?->icon }} {{ $activity->lesson->concept?->value?->name }}
        </span>
        <span class="breadcrumb-separator">←</span>
        <span class="breadcrumb-item">
            💡 {{ $activity->lesson->concept?->name }}
        </span>
        <span class="breadcrumb-separator">←</span>
        <span class="breadcrumb-item">
            📚 {{ $activity->lesson->title }}
        </span>
        @else
        <span class="breadcrumb-item">📝 نشاط مستقل (غير مرتبط بدرس)</span>
        @endif
    </div>
    
    <div class="activity-title-row">
        @if($activity->type == 'quiz')
        <span class="type-badge type-quiz">📋 اختبار</span>
        @elseif($activity->type == 'exercise')
        <span class="type-badge type-exercise">✍️ تمرين</span>
        @elseif($activity->type == 'project')
        <span class="type-badge type-project">🎨 مشروع</span>
        @endif
        
        <span class="status-badge status-{{ $activity->status }}">
            @if($activity->status == 'active')
            ✅ نشط
            @else
            ⏸️ غير نشط
            @endif
        </span>
    </div>

    <h1 class="activity-title">🎯 {{ $activity->title }}</h1>

    @if($activity->description)
    <div class="activity-description">{!! safe_html($activity->description) !!}</div>
    @endif

    <div class="activity-meta">
        <div class="meta-item">
            <span class="meta-label">الترتيب</span>
            <span class="meta-value">#{{ $activity->order }}</span>
        </div>
        @if($activity->points)
        <div class="meta-item">
            <span class="meta-label">النقاط</span>
            <span class="meta-value">{{ $activity->points }} 🪙</span>
        </div>
        @endif
        @if($activity->passing_score)
        <div class="meta-item">
            <span class="meta-label">درجة النجاح</span>
            <span class="meta-value">{{ $activity->passing_score }}%</span>
        </div>
        @endif
        @if($activity->questions && is_array($activity->questions))
        <div class="meta-item">
            <span class="meta-label">عدد الأسئلة</span>
            <span class="meta-value">{{ count($activity->questions) }}</span>
        </div>
        @endif
        <div class="meta-item">
            <span class="meta-label">الإرساليات</span>
            <span class="meta-value">{{ $submissionsCount }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">تاريخ الإضافة</span>
            <span class="meta-value">{{ $activity->created_at->format('Y-m-d') }}</span>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('admin.activities.edit', $activity) }}" class="btn btn-primary">✏️ تعديل</a>
        <a href="{{ route('admin.lessons.show', $activity->lesson) }}" class="btn btn-secondary">⬅️ العودة للدرس</a>
    </div>
</div>

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
<div class="empty-state">
    <div class="empty-icon">❓</div>
    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">لا توجد أسئلة لهذا النشاط</h3>
    <p style="color: #64748b;">يمكنك إضافة الأسئلة من خلال تعديل النشاط</p>
</div>
@endif

@endsection
