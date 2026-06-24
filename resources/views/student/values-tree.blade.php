@extends('layouts.student-app')

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
<div style="padding: 30px;">
    <!-- Page Header -->
    <div style="margin-bottom: 40px; text-align: center;">
        <h1 style="font-size: 36px; font-weight: 700; color: #1a202c; margin-bottom: 15px;">شجرة القيم 🌳</h1>
        <p style="color: #718096; font-size: 18px;">استكشف القيم الأخلاقية وتعلمها خطوة بخطوة</p>
    </div>

    <!-- Progress Overview -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; color: white; text-align: center;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 8px;">{{ $totalPoints }}</div>
            <div style="opacity: 0.9; font-size: 16px;">إجمالي النقاط</div>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 25px; border-radius: 15px; color: white; text-align: center;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 8px;">{{ $completedLessons }}</div>
            <div style="opacity: 0.9; font-size: 16px;">دروس مكتملة</div>
        </div>
        <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 15px; color: white; text-align: center;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 8px;">{{ $badges }}</div>
            <div style="opacity: 0.9; font-size: 16px;">شارة محققة</div>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 15px; color: white; text-align: center;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 8px;">{{ $crowns }}</div>
            <div style="opacity: 0.9; font-size: 16px;">تاج مكتسب</div>
        </div>
    </div>

    <!-- Values Tree -->
    <div style="display: grid; gap: 30px;">
        @foreach($values as $value)
        <div style="background: white; border-radius: 20px; padding: 35px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); border-right: 6px solid {{ $value->color ?? '#667eea' }};">
            
            <!-- Value Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #f7fafc;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, {{ $value->color ?? '#667eea' }} 0%, {{ $value->color_end ?? '#764ba2' }} 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px;">
                        {{ $value->emoji ?? '⭐' }}
                    </div>
                    <div>
                        <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 5px;">{{ $value->name }}</h2>
                        <p style="color: #718096; font-size: 15px;">{{ $value->description }}</p>
                    </div>
                </div>
                @if($value->is_mastered)
                <div style="font-size: 48px;" title="قيمة متقنة!">👑</div>
                @endif
            </div>

            <!-- Progress Bar -->
            <div style="margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 600; color: #2d3748;">التقدم</span>
                    <span style="font-size: 14px; font-weight: 700; color: {{ $value->color ?? '#667eea' }};">{{ $value->progress }}%</span>
                </div>
                <div style="width: 100%; height: 12px; background: #f7fafc; border-radius: 20px; overflow: hidden;">
                    <div style="width: {{ $value->progress }}%; height: 100%; background: linear-gradient(90deg, {{ $value->color ?? '#667eea' }} 0%, {{ $value->color_end ?? '#764ba2' }} 100%); border-radius: 20px; transition: width 0.5s ease;"></div>
                </div>
            </div>

            <!-- Concepts -->
            <div style="display: grid; gap: 20px;">
                @foreach($value->concepts as $concept)
                <div style="background: #f7fafc; padding: 25px; border-radius: 15px; border-right: 4px solid {{ $concept->is_unlocked ? ($value->color ?? '#667eea') : '#e2e8f0' }}; opacity: {{ $concept->is_unlocked ? '1' : '0.6' }};">
                    
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                                @if($concept->is_unlocked)
                                    <span style="font-size: 24px;">📖</span>
                                @else
                                    <span style="font-size: 24px;">🔒</span>
                                @endif
                                <h3 style="font-size: 20px; font-weight: 700; color: #2d3748;">{{ $concept->name }}</h3>
                            </div>
                            <p style="color: #718096; font-size: 14px;">{{ $concept->description }}</p>
                        </div>
                        @if($concept->is_unlocked)
                        <button onclick="toggleConcept({{ $concept->id }})" style="padding: 10px 20px; background: {{ $value->color ?? '#667eea' }}; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                            عرض المعاني
                        </button>
                        @endif
                    </div>

                    <!-- Meanings (collapsed by default) -->
                    @if($concept->is_unlocked)
                    <div id="concept-{{ $concept->id }}" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
                        <div style="display: grid; gap: 15px;">
                            @foreach($concept->lessons as $lesson)
                            <div style="background: white; padding: 20px; border-radius: 12px; border-right: 3px solid {{ $lesson->is_completed ? '#43e97b' : '#cbd5e0' }};">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                            @if($lesson->is_completed)
                                                <span style="font-size: 20px;">✅</span>
                                            @else
                                                <span style="font-size: 20px;">📚</span>
                                            @endif
                                            <h4 style="font-size: 17px; font-weight: 600; color: #2d3748;">{{ $lesson->title }}</h4>
                                        </div>
                                        <p style="color: #718096; font-size: 13px;">{{ $lesson->lessons_count ?? 0 }} درس</p>
                                    </div>
                                    <a href="{{ route('student.lesson', $lesson->id) }}" style="padding: 10px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        @if($lesson->is_completed) مراجعة @else ابدأ @endif
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
function toggleConcept(conceptId) {
    const element = document.getElementById('concept-' + conceptId);
    if (element.style.display === 'none' || element.style.display === '') {
        element.style.display = 'block';
    } else {
        element.style.display = 'none';
    }
}
</script>
</div>
@endsection
