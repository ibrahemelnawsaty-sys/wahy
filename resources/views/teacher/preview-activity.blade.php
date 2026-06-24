@extends('layouts.teacher')

@section('title', 'معاينة: ' . $activity->title)

@push('styles')
<style>
    .preview-badge {
        position: fixed;
        top: 80px;
        left: 20px;
        z-index: 999;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        padding: 8px 16px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 13px;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
    }
    .preview-container { max-width: 800px; margin: 0 auto; }
    .preview-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.08);
        padding: 35px;
        margin-bottom: 25px;
    }
    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 14px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        margin-bottom: 20px;
    }
    .question-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 15px;
    }
    .question-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white; font-weight: 700; font-size: 13px; margin-left: 10px;
    }
    .option-item {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 15px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        margin-top: 8px;
        font-size: 15px;
    }
    .option-item.correct {
        background: #f0fdf4;
        border-color: #16a34a;
        color: #16a34a;
        font-weight: 600;
    }
    .image-grid { display: flex; flex-wrap: wrap; gap: 15px; }
    .image-item {
        text-align: center;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px;
    }
    .image-item img { width: 140px; height: 140px; object-fit: cover; border-radius: 8px; }
    .image-caption { font-size: 12px; color: #64748b; margin-top: 6px; }
    .order-badge {
        background: #667eea; color: white;
        border-radius: 50%; width: 22px; height: 22px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; margin-bottom: 5px;
    }
    .info-row { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px; }
    .info-chip {
        display: inline-flex; align-items: center; gap: 6px;
        background: #f1f5f9; border-radius: 50px;
        padding: 6px 14px; font-size: 14px; color: #475569;
    }
    .text-answer-preview {
        background: #f8fafc; border: 2px dashed #cbd5e1;
        border-radius: 12px; padding: 20px;
        color: #94a3b8; font-size: 15px; text-align: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- تحذير "معاينة فقط" --}}
    <div class="preview-badge">
        👁 وضع المعاينة — لن تُحفظ أي إجابة
    </div>

    <div class="preview-container">

        {{-- شريط الأدوات --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.activities') }}">الأنشطة</a></li>
                    <li class="breadcrumb-item active">معاينة: {{ $activity->title }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                <a href="{{ route('teacher.activities.edit', $activity->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> تعديل
                </a>
                <a href="{{ route('teacher.activities') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        {{-- معلومات النشاط --}}
        <div class="preview-card">
            @php
                $typeIcon = match($activity->type) {
                    'quiz'        => '📝',
                    'exercise'    => '📋',
                    'project'     => '🏗️',
                    'image_order' => '🖼️',
                    'creative'    => '✨',
                    'upload'      => '📤',
                    'practical'   => '🎯',
                    'discussion'  => '💬',
                    default       => '📚',
                };
                $typeName = match($activity->type) {
                    'quiz'        => 'اختبار',
                    'exercise'    => 'تمرين',
                    'project'     => 'مشروع',
                    'image_order' => 'ترتيب صور',
                    'creative'    => 'نشاط إبداعي',
                    'upload'      => 'رفع ملف',
                    'practical'   => 'عملي',
                    'discussion'  => 'مناقشة',
                    default       => 'نشاط',
                };
            @endphp

            <div class="text-center mb-3">
                <div style="font-size: 60px; margin-bottom: 10px;">{{ $typeIcon }}</div>
                <h2 class="mb-2">{{ $activity->title }}</h2>
                <div class="type-badge">{{ $typeIcon }} {{ $typeName }}</div>
            </div>

            @if($activity->description)
                <p class="text-muted text-center mb-4" style="font-size: 16px;">{{ $activity->description }}</p>
            @endif

            <div class="info-row justify-content-center">
                <span class="info-chip">⭐ {{ $activity->points }} نقطة</span>
                @if($activity->passing_score)
                    <span class="info-chip">✅ درجة النجاح: {{ $activity->passing_score }}%</span>
                @endif
                @if($activity->duration_minutes)
                    <span class="info-chip">⏱ {{ $activity->duration_minutes }} دقيقة</span>
                @endif
                <span class="info-chip">
                    @if($activity->status === 'active') 🟢 نشط
                    @elseif($activity->status === 'draft') 📝 مسودة
                    @else ⏸ غير نشط @endif
                </span>
                @if($activity->is_homework)
                    <span class="info-chip">🏠 واجب منزلي</span>
                @endif
            </div>

            @if($activity->attachment)
                <div class="alert alert-info mt-3">
                    <i class="fas fa-paperclip me-2"></i>
                    مرفق:
                    <a href="{{ asset('storage/app/public/data/' . $activity->attachment) }}" target="_blank">
                        {{ basename($activity->attachment) }}
                    </a>
                </div>
            @endif
        </div>

        {{-- محتوى النشاط حسب النوع --}}
        <div class="preview-card">
            @if($activity->type === 'quiz' || $activity->type === 'exercise')
                {{-- Quiz / Exercise: عرض الأسئلة --}}
                @if(!empty($activity->questions))
                    <h5 class="mb-4">
                        <i class="fas fa-question-circle me-2 text-primary"></i>
                        الأسئلة ({{ count($activity->questions) }})
                    </h5>
                    @foreach($activity->questions as $qi => $q)
                        <div class="question-card">
                            <div class="mb-3">
                                <span class="question-num">{{ $qi + 1 }}</span>
                                <strong style="font-size: 16px;">{{ $q['question'] ?? $q['text'] ?? 'سؤال ' . ($qi + 1) }}</strong>
                            </div>
                            
                            {{-- Image order question within a quiz --}}
                            @if(isset($q['type']) && $q['type'] === 'image_order' && !empty($q['images']))
                                <p style="color: #94a3b8; font-size: 13px; margin-bottom: 10px;">الطالب سيرتب هذه الصور بالترتيب الصحيح</p>
                                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                    @foreach($q['images'] as $imgIdx => $img)
                                    <div style="text-align: center; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; padding: 8px;">
                                        <div style="background: var(--color-primary, #667eea); color: white; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; margin-bottom: 6px;">{{ $imgIdx + 1 }}</div>
                                        @if(!empty($img['url']))
                                            <div><img src="{{ $img['url'] }}" alt="{{ $img['description'] ?? '' }}" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;" onerror="this.outerHTML='<div style=\'width:120px;height:120px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#dc2626;font-size:13px;\'>❌</div>';"></div>
                                        @else
                                            <div style="width:120px;height:120px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:28px;">🖼️</div>
                                        @endif
                                        @if(!empty($img['description']))
                                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $img['description'] }}</div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            @elseif(!empty($q['options']))
                                @foreach($q['options'] as $oi => $opt)
                                    <div class="option-item {{ ($q['correct_answer'] ?? -1) === $oi ? 'correct' : '' }}">
                                        <span style="font-weight: 700; min-width: 20px;">
                                            {{ ['أ','ب','ج','د','هـ','و'][$oi] ?? ($oi + 1) }}
                                        </span>
                                        <span>{{ is_string($opt) ? $opt : json_encode($opt) }}</span>
                                        @if(($q['correct_answer'] ?? -1) === $oi)
                                            <span class="ms-auto">✓ صحيح</span>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-answer-preview">📝 سؤال مقالي — الطالب يكتب إجابته</div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3 text-warning"></i>
                        <p>لا توجد أسئلة لهذا النشاط بعد.</p>
                        <a href="{{ route('teacher.activities.edit', $activity->id) }}" class="btn btn-primary btn-sm">
                            إضافة أسئلة
                        </a>
                    </div>
                @endif

            @elseif($activity->type === 'image_order')
                {{-- Image Order: عرض الصور --}}
                <h5 class="mb-4"><i class="fas fa-images me-2 text-warning"></i> صور النشاط</h5>
                @if(!empty($activity->questions))
                    @php
                        $firstQ = $activity->questions[0] ?? [];
                        $isTeacherFormat = isset($firstQ['image_url']);
                    @endphp
                    <div class="image-grid">
                        @foreach($activity->questions as $i => $img)
                            <div class="image-item">
                                <div class="order-badge">{{ $i + 1 }}</div>
                                @php
                                    $imgUrl = $isTeacherFormat ? ($img['image_url'] ?? '') : ($img['images'][0]['url'] ?? $img['url'] ?? '');
                                    $imgCaption = $isTeacherFormat ? ($img['caption'] ?? '') : ($img['description'] ?? $img['images'][0]['description'] ?? '');
                                @endphp
                                @if($imgUrl)
                                    <img src="{{ $imgUrl }}"
                                         alt="{{ $imgCaption ?: 'صورة ' . ($i+1) }}"
                                         onerror="this.src=''; this.closest('.image-item').style.background='#fee2e2'; this.outerHTML='<div style=\'color:#dc2626;padding:20px\'>❌ صورة غير متاحة</div>';">
                                @else
                                    <div style="width:140px;height:140px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94a3b8">🖼️</div>
                                @endif
                                @if($imgCaption)
                                    <div class="image-caption">{{ $imgCaption }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        الطالب سيرتب هذه الصور بالترتيب الصحيح.
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-images fa-2x mb-3"></i>
                        <p>لا توجد صور بعد.</p>
                        <a href="{{ route('teacher.activities.edit', $activity->id) }}" class="btn btn-warning btn-sm">
                            إضافة صور
                        </a>
                    </div>
                @endif

            @elseif($activity->type === 'upload')
                <h5 class="mb-3">📤 رفع ملف</h5>
                <div class="text-answer-preview">
                    <i class="fas fa-cloud-upload-alt fa-3x mb-3 d-block" style="color:#94a3b8"></i>
                    الطالب سيرفع ملفاً أو يكتب وصفاً لعمله
                </div>

            @elseif($activity->type === 'discussion')
                <h5 class="mb-3">💬 نقاش</h5>
                <div class="text-answer-preview">
                    <i class="fas fa-comments fa-3x mb-3 d-block" style="color:#94a3b8"></i>
                    الطالب سيشارك رأيه في النقاش
                </div>

            @elseif($activity->type === 'practical')
                <h5 class="mb-3">🎯 نشاط عملي</h5>
                <div class="text-answer-preview">
                    <i class="fas fa-tools fa-3x mb-3 d-block" style="color:#94a3b8"></i>
                    الطالب سيصف ما قام به في النشاط العملي
                </div>

            @elseif($activity->type === 'creative')
                <h5 class="mb-3">✨ نشاط إبداعي</h5>
                <div class="text-answer-preview">
                    <i class="fas fa-paint-brush fa-3x mb-3 d-block" style="color:#94a3b8"></i>
                    الطالب سيقدم عمله الإبداعي هنا
                </div>

            @elseif($activity->type === 'project')
                <h5 class="mb-3">🏗️ مشروع</h5>
                <div class="text-answer-preview">
                    <i class="fas fa-project-diagram fa-3x mb-3 d-block" style="color:#94a3b8"></i>
                    الطالب سيقدم مشروعه هنا
                </div>

            @else
                <div class="text-answer-preview">
                    الطالب سيكتب إجابته هنا
                </div>
            @endif
        </div>

        {{-- أزرار الإجراء --}}
        <div class="d-flex gap-3 justify-content-center mb-5">
            <a href="{{ route('teacher.activities.edit', $activity->id) }}" class="btn btn-primary px-4">
                <i class="fas fa-edit me-2"></i>تعديل النشاط
            </a>
            <a href="{{ route('teacher.activities') }}" class="btn btn-outline-secondary px-4">
                <i class="fas fa-arrow-right me-2"></i>قائمة الأنشطة
            </a>
        </div>

    </div>
</div>
@endsection
