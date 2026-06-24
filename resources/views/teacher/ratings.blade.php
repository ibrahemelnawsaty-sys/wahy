@extends('layouts.teacher')

@section('title', 'التقييمات')

@section('content')

<div class="container">
    <div class="page-header">
        <h1 class="page-title">⭐ التقييمات من الطلاب</h1>
    </div>
    
    <!-- ملخص التقييمات -->
    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: center;">
            <!-- المتوسط الإجمالي -->
            <div style="text-align: center;">
                <div style="font-size: 60px; font-weight: 700; color: #fbbf24; margin-bottom: 10px;">
                    {{ number_format($averageRating ?? 0, 1) }}
                </div>
                <div style="display: flex; gap: 5px; justify-content: center; margin-bottom: 10px;">
                    @for($i = 1; $i <= 5; $i++)
                        <span style="font-size: 28px; color: {{ $i <= round($averageRating ?? 0) ? '#fbbf24' : '#e5e7eb' }};">⭐</span>
                    @endfor
                </div>
                <div style="color: #718096; font-size: 14px;">
                    من أصل {{ $ratings->total() }} تقييم
                </div>
            </div>
            
            <!-- توزيع التقييمات -->
            <div>
                @for($star = 5; $star >= 1; $star--)
                    @php
                        $count = $ratingDistribution[$star] ?? 0;
                        $total = $ratings->total() ?: 1;
                        $percentage = ($count / $total) * 100;
                    @endphp
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <span style="font-size: 16px; width: 80px;">{{ $star }} نجوم</span>
                        <div style="flex: 1; background: #e5e7eb; border-radius: 10px; height: 10px; overflow: hidden;">
                            <div style="background: #fbbf24; height: 100%; width: {{ $percentage }}%; transition: width 0.3s;"></div>
                        </div>
                        <span style="font-size: 14px; color: #718096; width: 60px; text-align: left;">{{ $count }} ({{ number_format($percentage, 0) }}%)</span>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    
    <!-- قائمة التقييمات -->
    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 20px;">جميع التقييمات</h3>
        
        <div style="display: grid; gap: 20px;">
            @forelse($ratings as $rating)
            <div style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                    <div>
                        <div style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">
                            👤 {{ $rating->student->name }}
                        </div>
                        <div style="display: flex; gap: 3px;">
                            @for($i = 1; $i <= 5; $i++)
                                <span style="font-size: 18px; color: {{ $i <= $rating->rating ? '#fbbf24' : '#e5e7eb' }};">⭐</span>
                            @endfor
                        </div>
                    </div>
                    <div style="color: #718096; font-size: 13px;">
                        {{ $rating->created_at->diffForHumans() }}
                    </div>
                </div>
                
                @if($rating->comment)
                <div style="background: #f7fafc; border-radius: 10px; padding: 15px; color: #4a5568; font-size: 14px; line-height: 1.6;">
                    "{{ $rating->comment }}"
                </div>
                @endif
            </div>
            @empty
            <div style="text-align: center; padding: 60px 20px; color: #718096;">
                <div style="font-size: 60px; margin-bottom: 15px;">⭐</div>
                <p style="font-size: 16px;">لم تتلقَ أي تقييمات بعد</p>
            </div>
            @endforelse
        </div>
        
        <div style="margin-top: 30px;">
            {{ $ratings->links() }}
        </div>
    </div>
</div>

@endsection
