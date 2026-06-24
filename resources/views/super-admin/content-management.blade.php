@extends('layouts.super-admin')

@section('content')
<div style="padding: 30px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">إدارة القيم والمحتوى</h1>
            <p style="color: #718096; font-size: 16px;">إدارة القيم، المفاهيم، المعاني، والدروس</p>
        </div>
        <button onclick="showAddValueModal()" style="padding: 15px 30px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; box-shadow: 0 5px 15px rgba(67, 233, 123, 0.3); transition: all 0.3s; display: flex; align-items: center; gap: 10px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(67, 233, 123, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(67, 233, 123, 0.3)'">
            <span style="font-size: 20px;">+</span>
            إضافة قيمة جديدة
        </button>
    </div>

    <!-- Content Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalValues }}</div>
            <div style="opacity: 0.9;">قيمة أخلاقية</div>
        </div>

        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalConcepts }}</div>
            <div style="opacity: 0.9;">مفهوم</div>
        </div>

        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalMeanings }}</div>
            <div style="opacity: 0.9;">معنى</div>
        </div>

        <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalLessons }}</div>
            <div style="opacity: 0.9;">درس</div>
        </div>

        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalActivities }}</div>
            <div style="opacity: 0.9;">نشاط</div>
        </div>
    </div>

    <!-- Values Tree Management -->
    <div style="display: grid; gap: 30px;">
        @foreach($values as $value)
        <div style="background: white; border-radius: 20px; padding: 35px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); border-right: 6px solid {{ $value->color ?? '#667eea' }};">
            
            <!-- Value Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 3px solid #f7fafc;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, {{ $value->color ?? '#667eea' }} 0%, {{ $value->color_end ?? '#764ba2' }} 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px;">
                        {{ $value->emoji ?? '⭐' }}
                    </div>
                    <div>
                        <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 5px;">{{ $value->name }}</h2>
                        <p style="color: #718096; font-size: 15px;">{{ $value->description }}</p>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button onclick="editValue({{ $value->id }})" style="padding: 12px 24px; background: #667eea; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#5568d3'" onmouseout="this.style.background='#667eea'">
                        تعديل
                    </button>
                    <button onclick="toggleValue({{ $value->id }})" style="padding: 12px; background: #f7fafc; color: #667eea; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; font-size: 20px; transition: all 0.3s;" onclick="toggleConcepts({{ $value->id }})" title="عرض/إخفاء المفاهيم">
                        ▼
                    </button>
                </div>
            </div>

            <!-- Value Stats -->
            <div style="display: flex; gap: 15px; margin-bottom: 25px;">
                <div style="padding: 12px 20px; background: #f7fafc; border-radius: 10px; font-size: 14px;">
                    <span style="font-weight: 700; color: #667eea;">{{ $value->concepts_count }}</span>
                    <span style="color: #718096;"> مفهوم</span>
                </div>
                <div style="padding: 12px 20px; background: #f7fafc; border-radius: 10px; font-size: 14px;">
                    <span style="font-weight: 700; color: #4facfe;">{{ $value->meanings_count }}</span>
                    <span style="color: #718096;"> معنى</span>
                </div>
                <div style="padding: 12px 20px; background: #f7fafc; border-radius: 10px; font-size: 14px;">
                    <span style="font-weight: 700; color: #43e97b;">{{ $value->lessons_count }}</span>
                    <span style="color: #718096;"> درس</span>
                </div>
                <div style="padding: 12px 20px; background: #f7fafc; border-radius: 10px; font-size: 14px;">
                    <span style="font-weight: 700; color: #fa709a;">{{ $value->activities_count }}</span>
                    <span style="color: #718096;"> نشاط</span>
                </div>
            </div>

            <!-- Concepts (Collapsible) -->
            <div id="concepts-{{ $value->id }}" style="display: none;">
                <button onclick="showAddConceptModal({{ $value->id }})" style="width: 100%; padding: 15px; background: #f7fafc; color: #667eea; border: 2px dashed #e2e8f0; border-radius: 12px; cursor: pointer; font-weight: 600; margin-bottom: 20px; transition: all 0.3s;" onmouseover="this.style.background='#edf2f7'; this.style.borderColor='#667eea'" onmouseout="this.style.background='#f7fafc'; this.style.borderColor='#e2e8f0'">
                    + إضافة مفهوم جديد
                </button>
                
                <div style="display: grid; gap: 20px;">
                    @foreach($value->concepts as $concept)
                    <div style="background: #f7fafc; padding: 25px; border-radius: 15px; border-right: 4px solid {{ $value->color ?? '#667eea' }};">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <div style="flex: 1;">
                                <h3 style="font-size: 20px; font-weight: 700; color: #2d3748; margin-bottom: 8px;">📖 {{ $concept->name }}</h3>
                                <p style="color: #718096; font-size: 14px; margin-bottom: 10px;">{{ $concept->description }}</p>
                                <div style="display: flex; gap: 10px;">
                                    <span style="font-size: 13px; color: #667eea; font-weight: 600;">{{ $concept->meanings_count }} معنى</span>
                                    <span style="font-size: 13px; color: #43e97b; font-weight: 600;">{{ $concept->lessons_count }} درس</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button onclick="editConcept({{ $concept->id }})" style="padding: 10px 18px; background: white; color: #667eea; border: 2px solid #667eea; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; transition: all 0.3s;" onmouseover="this.style.background='#667eea'; this.style.color='white'" onmouseout="this.style.background='white'; this.style.color='#667eea'">
                                    تعديل
                                </button>
                                <button onclick="toggleConcept({{ $concept->id }})" style="padding: 10px; background: white; color: #667eea; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.3s;">
                                    ▼
                                </button>
                            </div>
                        </div>

                        <!-- Meanings (Nested Collapsible) -->
                        <div id="meanings-{{ $concept->id }}" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 2px solid #e2e8f0;">
                            <button onclick="showAddMeaningModal({{ $concept->id }})" style="width: 100%; padding: 12px; background: white; color: #4facfe; border: 2px dashed #e2e8f0; border-radius: 10px; cursor: pointer; font-weight: 600; margin-bottom: 15px; font-size: 13px;" onmouseover="this.style.borderColor='#4facfe'" onmouseout="this.style.borderColor='#e2e8f0'">
                                + إضافة معنى
                            </button>
                            
                            <div style="display: grid; gap: 12px;">
                                @foreach($concept->meanings as $meaning)
                                <div style="background: white; padding: 18px; border-radius: 10px; border-right: 3px solid #4facfe;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div style="flex: 1;">
                                            <h4 style="font-size: 16px; font-weight: 600; color: #2d3748; margin-bottom: 5px;">📚 {{ $meaning->title }}</h4>
                                            <span style="font-size: 12px; color: #718096;">{{ $meaning->lessons_count }} دروس</span>
                                        </div>
                                        <div style="display: flex; gap: 6px;">
                                            <button onclick="editMeaning({{ $meaning->id }})" style="padding: 8px 15px; background: #4facfe; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                                تعديل
                                            </button>
                                            <a href="{{ route('super-admin.meaning.lessons', $meaning->id) }}" style="padding: 8px 15px; background: #43e97b; color: white; border: none; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-block;">
                                                الدروس
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
function toggleConcepts(valueId) {
    const element = document.getElementById('concepts-' + valueId);
    element.style.display = element.style.display === 'none' ? 'block' : 'none';
}

function toggleConcept(conceptId) {
    const element = document.getElementById('meanings-' + conceptId);
    element.style.display = element.style.display === 'none' ? 'block' : 'none';
}

function showAddValueModal() {
    showInfo('سيتم فتح نافذة إضافة قيمة جديدة', 'إضافة قيمة');
}

function showAddConceptModal(valueId) {
    showInfo('سيتم فتح نافذة إضافة مفهوم للقيمة #' + valueId, 'إضافة مفهوم');
}

function showAddMeaningModal(conceptId) {
    showInfo('سيتم فتح نافذة إضافة معنى للمفهوم #' + conceptId, 'إضافة معنى');
}

function editValue(id) {
    showInfo('سيتم فتح نافذة تعديل القيمة #' + id, 'تعديل قيمة');
}

function editConcept(id) {
    showInfo('سيتم فتح نافذة تعديل المفهوم #' + id, 'تعديل مفهوم');
}

function editMeaning(id) {
    showInfo('سيتم فتح نافذة تعديل المعنى #' + id, 'تعديل معنى');
}
</script>
@endsection
