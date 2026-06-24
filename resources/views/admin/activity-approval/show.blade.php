@extends('layouts.admin')

@section('title', 'تفاصيل النشاط')

@section('content')
<div class="admin-page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="{{ route('admin.activity-approval.index') }}" style="color: #6366f1; text-decoration: none;">← العودة للقائمة</a>
        <h1 style="margin-top: 10px;">{{ $activity->title }}</h1>
    </div>
    @if($activity->approval_status === 'pending')
    <div style="display: flex; gap: 10px;">
        <form action="{{ route('admin.activity-approval.approve', $activity) }}" method="POST">
            @csrf
            <button type="submit" style="background: #10b981; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px;">
                ✅ الموافقة على النشاط
            </button>
        </form>
        <button onclick="showRejectModal()" style="background: #ef4444; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px;">
            ❌ رفض النشاط
        </button>
    </div>
    @endif
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
    <!-- تفاصيل النشاط -->
    <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <h3 style="margin-bottom: 20px; color: #374151;">📝 تفاصيل النشاط</h3>
        
        <div style="margin-bottom: 15px;">
            <label style="font-weight: 600; color: #6b7280;">الوصف:</label>
            <p style="margin-top: 5px;">{{ $activity->description ?: 'لا يوجد وصف' }}</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 20px;">
            <div style="background: #f9fafb; padding: 15px; border-radius: 10px;">
                <div style="font-size: 12px; color: #6b7280;">النوع</div>
                <div style="font-weight: 600; margin-top: 5px;">
                    @php
                        $typeLabels = ['quiz' => 'اختبار', 'exercise' => 'تمرين', 'project' => 'مشروع', 'creative' => 'إبداعي'];
                    @endphp
                    {{ $typeLabels[$activity->type] ?? $activity->type }}
                </div>
            </div>
            <div style="background: #f9fafb; padding: 15px; border-radius: 10px;">
                <div style="font-size: 12px; color: #6b7280;">النقاط</div>
                <div style="font-weight: 600; margin-top: 5px;">{{ $activity->points }} نقطة</div>
            </div>
            <div style="background: #f9fafb; padding: 15px; border-radius: 10px;">
                <div style="font-size: 12px; color: #6b7280;">درجة النجاح</div>
                <div style="font-weight: 600; margin-top: 5px;">{{ $activity->passing_score ?? 0 }}%</div>
            </div>
            <div style="background: #f9fafb; padding: 15px; border-radius: 10px;">
                <div style="font-size: 12px; color: #6b7280;">الحالة</div>
                <div style="font-weight: 600; margin-top: 5px;">
                    @php
                        $statusAr = match($activity->status) {
                            'active' => 'نشط',
                            'draft' => 'مسودة',
                            'inactive' => 'غير نشط',
                            default => $activity->status
                        };
                    @endphp
                    {{ $statusAr }}
                </div>
            </div>
        </div>

        @if($activity->lesson)
        <div style="margin-top: 20px; padding: 15px; background: #eff6ff; border-radius: 10px;">
            <div style="font-size: 12px; color: #3b82f6;">📚 الدرس المرتبط</div>
            <div style="font-weight: 600; margin-top: 5px;">{{ $activity->lesson->title }}</div>
            <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                {{ $activity->lesson->concept->value->name ?? '' }} > 
                {{ $activity->lesson->concept->name ?? '' }} > 
                {{ $activity->lesson->title ?? '' }}
            </div>
        </div>
        @endif

        @if($activity->questions && count($activity->questions) > 0)
        <div style="margin-top: 20px;">
            @php
                $firstQ = $activity->questions[0] ?? [];
                $isTeacherImageFormat = isset($firstQ['image_url']);
            @endphp

            @if($activity->type === 'image_order' && $isTeacherImageFormat)
                {{-- Teacher format: [{image_url, caption, order}] --}}
                <h4 style="margin-bottom: 15px;">🖼️ صور النشاط ({{ count($activity->questions) }})</h4>
                <p style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">الطالب سيرتب هذه الصور بالترتيب الصحيح.</p>
                <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                    @foreach($activity->questions as $index => $img)
                    <div style="text-align: center; background: #f9fafb; border: 2px solid #e2e8f0; border-radius: 10px; padding: 10px;">
                        <div style="background: #6366f1; color: white; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; margin-bottom: 6px;">{{ $img['order'] ?? $index + 1 }}</div>
                        @if(!empty($img['image_url']))
                            <div>
                                <img src="{{ $img['image_url'] }}" alt="{{ $img['caption'] ?? 'صورة' }}"
                                     style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;"
                                     onerror="this.outerHTML='<div style=\'width:120px;height:120px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#dc2626;font-size:13px;\'>❌ غير متاحة</div>';">
                            </div>
                        @else
                            <div style="width:120px;height:120px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:32px;">🖼️</div>
                        @endif
                        @if(!empty($img['caption']))
                            <div style="font-size: 11px; color: #6b7280; margin-top: 5px;">{{ $img['caption'] }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @else
                <h4 style="margin-bottom: 15px;">📋 الأسئلة ({{ count($activity->questions) }})</h4>
                @foreach($activity->questions as $index => $question)
                <div style="background: #f9fafb; padding: 15px; border-radius: 10px; margin-bottom: 10px;">
                    <div style="font-weight: 600;">{{ $index + 1 }}. {{ $question['text'] ?? $question['question'] ?? 'سؤال' }}</div>
                    
                    {{-- Admin image_order question type (inside a quiz) --}}
                    @if(isset($question['type']) && $question['type'] === 'image_order' && !empty($question['images']))
                        <p style="color: #94a3b8; font-size: 13px; margin: 8px 0;">الطالب سيرتب هذه الصور</p>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px;">
                            @foreach($question['images'] as $imgIdx => $img)
                            <div style="text-align: center; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px;">
                                <div style="background: #6366f1; color: white; border-radius: 50%; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; margin-bottom: 4px;">{{ $imgIdx + 1 }}</div>
                                @if(!empty($img['url']))
                                    <div><img src="{{ $img['url'] }}" alt="{{ $img['description'] ?? '' }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 6px;" onerror="this.outerHTML='<div style=\'width:100px;height:100px;background:#fee2e2;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#dc2626;font-size:12px;\'>❌</div>';"></div>
                                @else
                                    <div style="width:100px;height:100px;background:#f1f5f9;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:28px;">🖼️</div>
                                @endif
                                @if(!empty($img['description']))
                                    <div style="font-size: 10px; color: #6b7280; margin-top: 4px;">{{ $img['description'] }}</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @elseif(isset($question['options']))
                        <ul style="margin-top: 10px; padding-right: 20px;">
                            @foreach($question['options'] as $option)
                            <li>{{ is_string($option) ? $option : json_encode($option) }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                @endforeach
            @endif
        </div>
        @endif
    </div>

    <!-- معلومات المعلم والحالة -->
    <div>
        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <h3 style="margin-bottom: 20px; color: #374151;">👨‍🏫 معلومات المعلم</h3>
            @if($activity->creator)
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    {{ mb_substr($activity->creator->name, 0, 1) }}
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 18px;">{{ $activity->creator->name }}</div>
                    <div style="color: #6b7280;">{{ $activity->creator->email }}</div>
                    <div style="color: #6b7280; font-size: 13px;">{{ $activity->creator->school->name ?? '' }}</div>
                </div>
            </div>
            @endif
        </div>

        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <h3 style="margin-bottom: 20px; color: #374151;">📊 حالة الموافقة</h3>
            @php
                $statusColors = ['pending' => '#f59e0b', 'approved' => '#10b981', 'rejected' => '#ef4444'];
                $statusLabels = ['pending' => 'في الانتظار', 'approved' => 'معتمد', 'rejected' => 'مرفوض'];
            @endphp
            <div style="background: {{ $statusColors[$activity->approval_status] ?? '#6b7280' }}20; color: {{ $statusColors[$activity->approval_status] ?? '#6b7280' }}; padding: 15px; border-radius: 10px; text-align: center; font-weight: 600; font-size: 18px;">
                {{ $statusLabels[$activity->approval_status] ?? $activity->approval_status }}
            </div>

            @if($activity->approved_at)
            <div style="margin-top: 15px; font-size: 13px; color: #6b7280;">
                <div>📅 تاريخ المراجعة: {{ $activity->approved_at->format('Y/m/d H:i') }}</div>
                @if($activity->approver)
                <div>👤 بواسطة: {{ $activity->approver->name }}</div>
                @endif
            </div>
            @endif

            @if($activity->rejection_reason)
            <div style="margin-top: 15px; background: #fef2f2; padding: 15px; border-radius: 10px;">
                <div style="font-weight: 600; color: #ef4444; margin-bottom: 5px;">سبب الرفض:</div>
                <p style="color: #991b1b;">{{ $activity->rejection_reason }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal الرفض -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 15px; max-width: 500px; width: 90%;">
        <h3 style="margin-bottom: 20px;">❌ رفض النشاط</h3>
        <form action="{{ route('admin.activity-approval.reject', $activity) }}" method="POST">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">سبب الرفض:</label>
                <textarea name="rejection_reason" required rows="4" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px;"></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeRejectModal()" style="background: #e5e7eb; color: #374151; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">إلغاء</button>
                <button type="submit" style="background: #ef4444; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">تأكيد الرفض</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal() { document.getElementById('rejectModal').style.display = 'flex'; }
function closeRejectModal() { document.getElementById('rejectModal').style.display = 'none'; }
document.getElementById('rejectModal').addEventListener('click', function(e) { if (e.target === this) closeRejectModal(); });
</script>
@endsection
