@extends('layouts.teacher')

@section('title', 'نتائج التمرين')

@section('content')
<div class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 class="page-title">📊 نتائج: {{ $exercise->title }}</h1>
            <p style="color: #64748b; margin-top: 5px;">{{ count($exercise->questions ?? []) }} سؤال • {{ $exercise->difficulty == 'easy' ? 'سهل' : ($exercise->difficulty == 'medium' ? 'متوسط' : 'صعب') }}</p>
        </div>
        <a href="{{ route('teacher.exercises') }}" style="background: #f1f5f9; color: #475569; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: 600;">← العودة</a>
    </div>

    {{-- إحصائيات --}}
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 14px; padding: 20px; color: white; text-align: center;">
            <div style="font-size: 32px; font-weight: 800;">{{ $stats['total_attempts'] }}</div>
            <div style="font-size: 13px; opacity: 0.9;">محاولة</div>
        </div>
        <div style="background: linear-gradient(135deg, #10b981, #059669); border-radius: 14px; padding: 20px; color: white; text-align: center;">
            <div style="font-size: 32px; font-weight: 800;">{{ $stats['avg_score'] }}%</div>
            <div style="font-size: 13px; opacity: 0.9;">المتوسط</div>
        </div>
        <div style="background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 14px; padding: 20px; color: white; text-align: center;">
            <div style="font-size: 32px; font-weight: 800;">{{ $stats['highest_score'] }}%</div>
            <div style="font-size: 13px; opacity: 0.9;">أعلى درجة</div>
        </div>
        <div style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 14px; padding: 20px; color: white; text-align: center;">
            <div style="font-size: 32px; font-weight: 800;">{{ $stats['pass_rate'] }}%</div>
            <div style="font-size: 13px; opacity: 0.9;">نسبة النجاح</div>
        </div>
        <div style="background: linear-gradient(135deg, #ec4899, #db2777); border-radius: 14px; padding: 20px; color: white; text-align: center;">
            <div style="font-size: 32px; font-weight: 800;">{{ gmdate('i:s', $stats['avg_time']) }}</div>
            <div style="font-size: 13px; opacity: 0.9;">متوسط الوقت</div>
        </div>
    </div>

    {{-- جدول النتائج --}}
    <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc;">
                    <th style="padding: 14px 20px; text-align: right; font-weight: 700; color: #475569;">#</th>
                    <th style="padding: 14px 20px; text-align: right; font-weight: 700; color: #475569;">الطالب</th>
                    <th style="padding: 14px 20px; text-align: center; font-weight: 700; color: #475569;">الدرجة</th>
                    <th style="padding: 14px 20px; text-align: center; font-weight: 700; color: #475569;">الإجابات الصحيحة</th>
                    <th style="padding: 14px 20px; text-align: center; font-weight: 700; color: #475569;">الوقت</th>
                    <th style="padding: 14px 20px; text-align: center; font-weight: 700; color: #475569;">التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $index => $attempt)
                <tr style="border-top: 1px solid #f1f5f9;">
                    <td style="padding: 14px 20px;">
                        @if($index == 0) 🥇 @elseif($index == 1) 🥈 @elseif($index == 2) 🥉 @else {{ $index + 1 }} @endif
                    </td>
                    <td style="padding: 14px 20px; font-weight: 600; color: #1e293b;">{{ $attempt->student->name ?? 'غير معروف' }}</td>
                    <td style="padding: 14px 20px; text-align: center;">
                        <span style="background: {{ $attempt->score >= 80 ? '#dcfce7' : ($attempt->score >= 60 ? '#fef3c7' : '#fef2f2') }}; color: {{ $attempt->score >= 80 ? '#16a34a' : ($attempt->score >= 60 ? '#d97706' : '#dc2626') }}; padding: 4px 16px; border-radius: 20px; font-weight: 700;">{{ $attempt->score }}%</span>
                    </td>
                    <td style="padding: 14px 20px; text-align: center; color: #475569;">{{ $attempt->correct_answers }}/{{ $attempt->total_questions }}</td>
                    <td style="padding: 14px 20px; text-align: center; color: #475569;">{{ $attempt->time_taken ? gmdate('i:s', $attempt->time_taken) : '-' }}</td>
                    <td style="padding: 14px 20px; text-align: center; color: #94a3b8; font-size: 13px;">{{ $attempt->completed_at?->diffForHumans() ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">لا توجد محاولات بعد</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
