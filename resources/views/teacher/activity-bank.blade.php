@extends('layouts.teacher')

@section('title', 'بنك الأنشطة')

@push('styles')
<style>
    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-up { animation: slideInUp 0.6s ease-out; }
    .hover-lift { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
    .bank-tab { padding: 12px 24px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer; border: 2px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.15); color: white; transition: all 0.3s; backdrop-filter: blur(10px); }
    .bank-tab.active { background: white; color: #667eea; border-color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="animate-up" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 25px; padding: 35px; margin-bottom: 24px; box-shadow: 0 15px 50px rgba(102, 126, 234, 0.3); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: white; margin-bottom: 8px;">📚 بنك الأنشطة</h1>
            <p style="color: rgba(255,255,255,0.95); font-size: 16px;">إضافة نشاط إبداعي للفصل أو نشاط عام للبنك</p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button type="button" onclick="showAddActivityModal()" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white; padding: 15px 24px; border-radius: 15px; border: 2px solid rgba(255,255,255,0.3); font-weight: 700; font-size: 15px; cursor: pointer; transition: all 0.3s;"
                onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                ⚡ إضافة سريعة
            </button>
            <a href="{{ route('teacher.activity-bank.create') }}" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white; padding: 15px 24px; border-radius: 15px; border: 2px solid rgba(255,255,255,0.3); font-weight: 700; font-size: 15px; text-decoration: none; transition: all 0.3s;"
                onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                ➕ نشاط مفصّل
            </a>
        </div>
    </div>
    {{-- Tabs --}}
    <div style="display: flex; gap: 8px; margin-top: 24px;">
        <button class="bank-tab active" id="tab-activities" onclick="switchTab('activities')">
            📚 الأنشطة ({{ $stats['total'] ?? $activities->total() }})
        </button>
        <button class="bank-tab" id="tab-questions" onclick="switchTab('questions')">
            ❓ الأسئلة ({{ isset($questions) ? $questions->total() : 0 }})
        </button>
    </div>
</div>

{{-- Alerts --}}
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

{{-- ═══════════════════════════════════════════ TAB: الأنشطة --}}
<div id="panel-activities" class="tab-panel active">

    {{-- Stats --}}
    <div class="animate-up" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="hover-lift" style="background: white; border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="font-size: 40px; margin-bottom: 10px;">📚</div>
            <div style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 5px;">{{ $stats['total'] ?? $activities->total() }}</div>
            <div style="color: #718096; font-size: 13px; font-weight: 600;">أنشطتي</div>
        </div>
        <div class="hover-lift" style="background: white; border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 2px solid #fbbf24;">
            <div style="font-size: 40px; margin-bottom: 10px;">⏳</div>
            <div style="font-size: 28px; font-weight: 700; color: #f59e0b; margin-bottom: 5px;">{{ $stats['pending'] ?? 0 }}</div>
            <div style="color: #718096; font-size: 13px; font-weight: 600;">في انتظار الموافقة</div>
        </div>
        <div class="hover-lift" style="background: white; border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 2px solid #10b981;">
            <div style="font-size: 40px; margin-bottom: 10px;">✅</div>
            <div style="font-size: 28px; font-weight: 700; color: #10b981; margin-bottom: 5px;">{{ $stats['approved'] ?? 0 }}</div>
            <div style="color: #718096; font-size: 13px; font-weight: 600;">معتمدة</div>
        </div>
        <div class="hover-lift" style="background: white; border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 2px solid #6366f1;">
            <div style="font-size: 40px; margin-bottom: 10px;">🌐</div>
            <div style="font-size: 28px; font-weight: 700; color: #6366f1; margin-bottom: 5px;">{{ $stats['shared_activities'] ?? 0 }}</div>
            <div style="color: #718096; font-size: 13px; font-weight: 600;">أنشطة مشتركة</div>
        </div>
    </div>

    {{-- Activities List --}}
    <div class="animate-up" style="background: white; border-radius: 25px; padding: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
        <h2 style="font-size: 24px; font-weight: 700; color: #1a202c; margin-bottom: 25px;">قائمة الأنشطة</h2>

        @if($activities->isEmpty())
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;">📚</div>
            <h3 style="font-size: 20px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">لا توجد أنشطة بعد</h3>
            <p style="color: #718096; margin-bottom: 20px;">ابدأ بإضافة نشاط جديد إلى بنك الأنشطة</p>
            <button onclick="showAddActivityModal()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border-radius: 12px; border: none; font-weight: 700; font-size: 16px; cursor: pointer;">
                ➕ إضافة نشاط جديد
            </button>
        </div>
        @else
        <div style="display: grid; gap: 20px;">
            @foreach($activities as $activity)
            <div class="hover-lift" style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 18px; padding: 25px; border: 2px solid #e2e8f0; position: relative; overflow: hidden;">
                <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 6px 15px rgba(102, 126, 234, 0.3);">
                                {{ $activity->is_creative ? '✨' : '📚' }}
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 20px; font-weight: 700; color: #1a202c; margin-bottom: 5px;">{{ $activity->title }}</h3>
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                    @if($activity->is_creative)
                                    <span style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 700;">✨ إبداعي</span>
                                    @endif
                                    <span style="background: #e2e8f0; color: #4a5568; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">{{ ucfirst($activity->type) }}</span>
                                    <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">⭐ {{ $activity->points }} نقطة</span>
                                    @if($activity->bonus_points > 0)
                                    <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">🎁 +{{ $activity->bonus_points }} إضافية</span>
                                    @endif
                                    @if($activity->created_by == auth()->id())
                                        @if($activity->approval_status === 'pending')
                                        <span style="background: #fef3c7; color: #d97706; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">⏳ في انتظار الموافقة</span>
                                        @elseif($activity->approval_status === 'approved')
                                        <span style="background: #dcfce7; color: #16a34a; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">✅ معتمد</span>
                                        @elseif($activity->approval_status === 'rejected')
                                        <span style="background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">❌ مرفوض</span>
                                        @endif
                                    @else
                                        <span style="background: #e0e7ff; color: #4f46e5; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">🌐 مشترك</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($activity->description)
                        <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin-bottom: 15px;">{{ Str::limit($activity->description, 150) }}</p>
                        @endif
                        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                            @if($activity->classroom)
                            <div style="display: flex; align-items: center; gap: 6px; color: #718096; font-size: 13px;">
                                <span>📚</span><span>{{ $activity->classroom->name }}</span>
                            </div>
                            @endif
                            @if($activity->lesson)
                            <div style="display: flex; align-items: center; gap: 6px; color: #718096; font-size: 13px;">
                                <span>📖</span><span>{{ $activity->lesson->title }}</span>
                            </div>
                            @endif
                            <div style="display: flex; align-items: center; gap: 6px; color: #718096; font-size: 13px;">
                                <span>📅</span><span>{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; flex-direction: column;">
                        @if($activity->status === 'active')
                        <span style="background: #dcfce7; color: #166534; padding: 6px 12px; border-radius: 10px; font-size: 12px; font-weight: 700;">✅ نشط</span>
                        @elseif($activity->status === 'draft')
                        <span style="background: #fef3c7; color: #92400e; padding: 6px 12px; border-radius: 10px; font-size: 12px; font-weight: 700;">📝 مسودة</span>
                        @else
                        <span style="background: #fee2e2; color: #991b1b; padding: 6px 12px; border-radius: 10px; font-size: 12px; font-weight: 700;">⏸️ غير نشط</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        {{-- Pagination --}}
        <div style="margin-top: 30px; display: flex; justify-content: center;">
            {{ $activities->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════ TAB: الأسئلة --}}
<div id="panel-questions" class="tab-panel">
    <div class="animate-up" style="background: white; border-radius: 25px; padding: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: 700; color: #1a202c;">❓ أسئلتي في البنك</h2>
            <a href="{{ route('teacher.question-bank.create') }}" style="background: linear-gradient(135deg, #f093fb, #f5576c); color: white; padding: 12px 24px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px;">
                ➕ إضافة سؤال جديد
            </a>
        </div>

        @if(isset($questions) && $questions->count() > 0)
        @foreach($questions as $question)
        <div class="hover-lift" style="background: linear-gradient(135deg, #fdf4ff, #fce7f3); border-radius: 16px; padding: 20px; margin-bottom: 14px; border: 2px solid #f0abfc;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f093fb, #f5576c); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">❓</div>
                <div style="flex: 1;">
                    <h3 style="font-size: 17px; font-weight: 700; color: #1a202c; margin-bottom: 6px;">{{ $question->title }}</h3>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <span style="background: #e0e7ff; color: #4338ca; padding: 3px 10px; border-radius: 8px; font-size: 12px; font-weight: 600;">{{ $question->question_type }}</span>
                        @php
                            $qStatusColor = $question->status === 'approved' ? '#dcfce7:#15803d' : ($question->status === 'rejected' ? '#fee2e2:#dc2626' : '#fef3c7:#d97706');
                            [$qBg, $qFg] = explode(':', $qStatusColor);
                        @endphp
                        <span style="background: {{ $qBg }}; color: {{ $qFg }}; padding: 3px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">
                            {{ $question->status === 'approved' ? '✅ معتمد' : ($question->status === 'rejected' ? '❌ مرفوض' : '⏳ بانتظار الموافقة') }}
                        </span>
                        <span style="background: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 8px; font-size: 12px;">{{ $question->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @if(isset($questions) && $questions->hasPages())
        <div style="margin-top: 24px; display: flex; justify-content: center;">
            {{ $questions->links() }}
        </div>
        @endif
        @else
        <div style="text-align: center; padding: 60px; color: #94a3b8;">
            <div style="font-size: 60px; margin-bottom: 16px;">📭</div>
            <p style="font-size: 16px; font-weight: 600;">لا توجد أسئلة بعد</p>
            <a href="{{ route('teacher.question-bank.create') }}" style="display: inline-block; margin-top: 16px; background: linear-gradient(135deg, #f093fb, #f5576c); color: white; padding: 12px 24px; border-radius: 12px; font-weight: 700; text-decoration: none;">
                ➕ أضف أول سؤال
            </a>
        </div>
        @endif
    </div>
</div>

{{-- Add Activity Modal — منسّق ومتجاوب على الجوال (Issue 48) --}}
<style>
    .ab-modal { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 9999; overflow-y: auto; padding: 16px; backdrop-filter: blur(4px); }
    .ab-modal.active { display: flex; align-items: flex-start; justify-content: center; }
    .ab-modal-card {
        max-width: 720px;
        width: 100%;
        margin: 30px auto;
        background: white;
        border-radius: 20px;
        padding: 28px;
        box-shadow: 0 20px 60px rgba(0,0,0,.25);
        position: relative;
    }
    .ab-modal-close {
        position: absolute; top: 14px; left: 14px;
        background: #f1f5f9; border: none;
        width: 36px; height: 36px; border-radius: 50%;
        cursor: pointer; font-size: 18px; color: #475569;
        display: flex; align-items: center; justify-content: center;
    }
    .ab-modal-card h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 22px; text-align: center; }
    .ab-form-grid { display: grid; gap: 16px; }
    .ab-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ab-field label { display: block; font-weight: 700; color: #0f172a; margin-bottom: 6px; font-size: 14px; }
    .ab-field input, .ab-field textarea, .ab-field select {
        width: 100%;
        padding: 11px 13px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        font-family: inherit;
        background: #fafafa;
        transition: .15s;
        -webkit-appearance: none;
        appearance: none;
    }
    .ab-field input:focus, .ab-field textarea:focus, .ab-field select:focus {
        outline: none;
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
    }
    .ab-field select {
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: left 12px center;
        background-size: 12px;
        padding-inline-end: 36px;
    }
    .ab-checkbox-row {
        display: flex; align-items: center; gap: 10px;
        background: #f8fafc; border: 1px solid #e2e8f0;
        padding: 12px 14px; border-radius: 10px; cursor: pointer;
    }
    .ab-checkbox-row input[type=checkbox] { width: 18px; height: 18px; cursor: pointer; }
    .ab-actions { display: flex; gap: 12px; margin-top: 8px; }
    .ab-btn {
        flex: 1; padding: 13px; border-radius: 10px; border: none;
        font-weight: 700; font-size: 15px; cursor: pointer; font-family: inherit;
        touch-action: manipulation;
    }
    .ab-btn-primary { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: white; }
    .ab-btn-secondary { background: #f1f5f9; color: #475569; }
    @media (max-width: 640px) {
        .ab-form-row { grid-template-columns: 1fr; }
        .ab-modal-card { margin: 16px auto; padding: 22px 18px; border-radius: 16px; }
        .ab-modal-card h2 { font-size: 19px; }
    }
</style>

<div id="addActivityModal" class="ab-modal">
    <div class="ab-modal-card">
        <button onclick="closeAddActivityModal()" class="ab-modal-close" aria-label="إغلاق">✕</button>
        <h2>➕ إضافة نشاط جديد</h2>

        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 14px;border-radius:10px;margin-bottom:14px;font-size:13px;">
                @foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
            </div>
        @endif

        <form id="addActivityForm" method="POST" action="{{ route('teacher.activity-bank.store') }}">
            @csrf
            <div class="ab-form-grid">
                <div class="ab-field">
                    <label>عنوان النشاط *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="مثال: قيمة الصدق في حياتنا">
                </div>

                <div class="ab-field">
                    <label>وصف النشاط</label>
                    <textarea name="description" rows="3" placeholder="اكتب وصفاً مختصراً للنشاط...">{{ old('description') }}</textarea>
                </div>

                <div class="ab-form-row">
                    <div class="ab-field">
                        <label>نوع النشاط *</label>
                        <select name="type" required>
                            <option value="quiz">📝 اختبار</option>
                            <option value="exercise">💪 تمرين</option>
                            <option value="project">🏗️ مشروع</option>
                            <option value="creative">✨ إبداعي</option>
                        </select>
                    </div>
                    <div class="ab-field">
                        <label>الدرس المرتبط</label>
                        <select name="lesson_id">
                            <option value="">— بدون درس —</option>
                            @foreach(\App\Models\Lesson::orderBy('title')->get(['id', 'title']) as $lesson)
                                <option value="{{ $lesson->id }}" {{ old('lesson_id') == $lesson->id ? 'selected' : '' }}>{{ $lesson->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ab-field">
                    <label>الفصل (للنشاط الإبداعي)</label>
                    <select name="classroom_id" id="classroomSelect">
                        <option value="">— بدون فصل —</option>
                        @foreach(auth()->user()->teachingClassrooms as $classroom)
                            <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ab-form-row">
                    <div class="ab-field">
                        <label>النقاط *</label>
                        <input type="number" name="points" value="{{ old('points', 20) }}" min="1" max="100" required>
                    </div>
                    <div class="ab-field">
                        <label>نقاط إضافية</label>
                        <input type="number" name="bonus_points" value="{{ old('bonus_points', 0) }}" min="0" max="50">
                    </div>
                </div>

                <label class="ab-checkbox-row">
                    <input type="checkbox" name="is_creative" id="isCreative" value="1" {{ old('is_creative') ? 'checked' : '' }}>
                    <span>نشاط إبداعي (جماعي للفصل)</span>
                </label>

                <div class="ab-field">
                    <label>الحالة *</label>
                    <select name="status" required>
                        <option value="active">🟢 نشط</option>
                        <option value="draft">📝 مسودة</option>
                        <option value="inactive">⚪ غير نشط</option>
                    </select>
                </div>

                <div class="ab-actions">
                    <button type="submit" class="ab-btn ab-btn-primary">💾 حفظ النشاط</button>
                    <button type="button" onclick="closeAddActivityModal()" class="ab-btn ab-btn-secondary">إلغاء</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.bank-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
}

function showAddActivityModal() {
    document.getElementById('addActivityModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeAddActivityModal() {
    document.getElementById('addActivityModal').classList.remove('active');
    document.body.style.overflow = '';
}

// إغلاق عند الضغط على الخلفية
document.getElementById('addActivityModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddActivityModal();
});

document.getElementById('isCreative').addEventListener('change', function() {
    const classroomSelect = document.getElementById('classroomSelect');
    if (this.checked) {
        classroomSelect.required = true;
        classroomSelect.closest('div').style.display = 'block';
    } else {
        classroomSelect.required = false;
    }
});

// إعادة فتح النافذة تلقائياً عند ظهور أخطاء التحقق (Issue #48)
@if($errors->any() && old('title'))
    showAddActivityModal();
@endif
</script>

@endsection
