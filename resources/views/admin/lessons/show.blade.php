@extends('layouts.admin')

@section('page-title', 'تفاصيل الدرس')

@section('content')
<style>
.lesson-header {
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

.lesson-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
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

.type-text { background: #dbeafe; color: #1e40af; }
.type-video { background: #fce7f3; color: #9f1239; }
.type-audio { background: #fef3c7; color: #92400e; }

.lesson-title {
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
.status-draft { background: #fee2e2; color: #991b1b; }

.lesson-content {
    background: #f8fafc;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    line-height: 1.8;
    color: #475569;
}
/* تنسيق محتوى محرر النصوص */
.lesson-content img { max-width: 100%; border-radius: 8px; margin: 8px 0; height: auto; }
.lesson-content a { color: #3b82f6; text-decoration: underline; }
.lesson-content ul, .lesson-content ol { padding-right: 22px; }
.lesson-content p { margin-bottom: 10px; }

.media-container {
    background: #1e293b;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    text-align: center;
}

.media-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--color-primary);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

.lesson-meta {
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

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 32px 0 16px;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.activities-list {
    background: white;
    border-radius: 12px;
    overflow: hidden;
}

.activity-item {
    padding: 20px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background: #f8fafc;
}

.activity-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 16px;
    margin-bottom: 8px;
}

.activity-meta {
    display: flex;
    gap: 16px;
    margin-top: 12px;
    font-size: 13px;
    flex-wrap: wrap;
}

.meta-badge {
    padding: 4px 12px;
    background: #f1f5f9;
    border-radius: 6px;
    color: #475569;
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 16px;
}

.empty-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
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

<div class="lesson-header">
    <div class="breadcrumb">
        @if($lesson->concept && $lesson->concept->value)
        <span class="breadcrumb-item">
            {{ $lesson->concept->value->icon }} {{ $lesson->concept->value->name }}
        </span>
        <span class="breadcrumb-separator">←</span>
        @endif
        @if($lesson->concept)
        <span class="breadcrumb-item">
            💡 {{ $lesson->concept->name }}
        </span>
        <span class="breadcrumb-separator">←</span>
        @endif
        <span class="breadcrumb-item">
            📖 {{ $lesson->title }}
        </span>
    </div>
    
    <div class="lesson-title-row">
        @if($lesson->type == 'text')
        <span class="type-badge type-text">📝 نص</span>
        @elseif($lesson->type == 'video')
        <span class="type-badge type-video">🎥 فيديو</span>
        @elseif($lesson->type == 'audio')
        <span class="type-badge type-audio">🎵 صوت</span>
        @endif
        
        <span class="status-badge status-{{ $lesson->status }}">
            @if($lesson->status == 'active')
            ✅ نشط
            @else
            ⏸️ غير نشط
            @endif
        </span>
    </div>

    <h1 class="lesson-title">📚 {{ $lesson->title }}</h1>

    {{-- نعرض كل ما هو موجود بصرف النظر عن type (كان mixted الافتراضي لا يُعرض) — Issue 15 --}}
    @if($lesson->content)
    <div class="lesson-content">
        {!! safe_html($lesson->content) !!}
    </div>
    @endif

    @if($lesson->video_url)
    <div class="media-container">
        <a href="{{ $lesson->video_url }}" target="_blank" class="media-link">
            🎥 مشاهدة الفيديو
        </a>
    </div>
    @endif

    @if(!empty($lesson->video_file))
    <div class="media-container">
        <video controls style="width: 100%; max-width: 600px;">
            <source src="{{ asset('storage/' . ltrim($lesson->video_file, '/')) }}">
            المتصفح الخاص بك لا يدعم تشغيل الفيديو.
        </video>
    </div>
    @endif

    @if($lesson->audio_url)
    <div class="media-container">
        <audio controls style="width: 100%; max-width: 600px;">
            <source src="{{ $lesson->audio_url }}" type="audio/mpeg">
            المتصفح الخاص بك لا يدعم تشغيل الصوت.
        </audio>
    </div>
    @endif

    @if(!empty($lesson->audio_file))
    <div class="media-container">
        <audio controls style="width: 100%; max-width: 600px;">
            <source src="{{ asset('storage/' . ltrim($lesson->audio_file, '/')) }}">
            المتصفح الخاص بك لا يدعم تشغيل الصوت.
        </audio>
    </div>
    @endif

    <div class="lesson-meta">
        <div class="meta-item">
            <span class="meta-label">الترتيب</span>
            <span class="meta-value">#{{ $lesson->order }}</span>
        </div>
        @if($lesson->duration)
        <div class="meta-item">
            <span class="meta-label">المدة</span>
            <span class="meta-value">{{ $lesson->duration }} دقيقة</span>
        </div>
        @endif
        @if($lesson->points)
        <div class="meta-item">
            <span class="meta-label">النقاط</span>
            <span class="meta-value">{{ $lesson->points }} 🪙</span>
        </div>
        @endif
        <div class="meta-item">
            <span class="meta-label">عدد الأنشطة</span>
            <span class="meta-value">{{ $activitiesCount }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">تاريخ الإضافة</span>
            <span class="meta-value">{{ $lesson->created_at->format('Y-m-d') }}</span>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-primary">✏️ تعديل</a>
        @if($lesson->concept)
        <a href="{{ route('admin.concepts.show', $lesson->concept) }}" class="btn btn-secondary">⬅️ العودة للمفهوم</a>
        @endif
    </div>
</div>

<div class="section-header">
    <h2 class="section-title">🎯 الأنشطة ({{ $activitiesCount }})</h2>
    <a href="{{ route('admin.activities.create', ['lesson_id' => $lesson->id]) }}" class="btn btn-primary">➕ إضافة نشاط جديد</a>
</div>

@if($lesson->activities->isEmpty())
<div class="empty-state">
    <div class="empty-icon">🎯</div>
    <h3 class="empty-title">لا توجد أنشطة لهذا الدرس</h3>
    <p style="color: #64748b; margin-bottom: 20px;">ابدأ بإضافة الأنشطة التفاعلية لهذا الدرس</p>
    <a href="{{ route('admin.activities.create', ['lesson_id' => $lesson->id]) }}" class="btn btn-primary">➕ إضافة نشاط جديد</a>
</div>
@else
<div class="activities-list">
    @foreach($lesson->activities as $activity)
    <div class="activity-item">
        <h3 class="activity-name">{{ $activity->title }}</h3>
        @if($activity->description)
        <p style="color: #64748b; font-size: 14px; line-height: 1.6;">
            {{ \Illuminate\Support\Str::limit($activity->description, 150) }}
        </p>
        @endif
        <div class="activity-meta">
            <span class="meta-badge">الترتيب: #{{ $activity->order }}</span>
            @if($activity->points)
            <span class="meta-badge">النقاط: {{ $activity->points }} 🪙</span>
            @endif
            @if($activity->passing_score)
            <span class="meta-badge">درجة النجاح: {{ $activity->passing_score }}%</span>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
