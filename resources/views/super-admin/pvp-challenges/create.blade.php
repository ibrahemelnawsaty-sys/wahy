@extends('layouts.admin')

@section('title', 'إنشاء تحدي PvP')
@section('page-title', 'إنشاء تحدي PvP')

@push('styles')
<style>
/* Wahy dark-mode coverage — pvp-challenges/create (ألوان inline مُصلَّبة) */
html[data-theme="dark"] #sa-pvp-create div[style*="background: #fff"],
html[data-theme="dark"] #sa-pvp-create div[style*="background:#fff"] {
    background: rgba(30, 41, 59, 0.85) !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35) !important;
}
html[data-theme="dark"] #sa-pvp-create h2[style*="#1e293b"],
html[data-theme="dark"] #sa-pvp-create label[style*="#1e293b"],
html[data-theme="dark"] #sa-pvp-create span[style*="#1e293b"] { color: #F1F5F9 !important; }
html[data-theme="dark"] #sa-pvp-create small[style*="#94a3b8"],
html[data-theme="dark"] #sa-pvp-create a[style*="#64748b"] { color: #94A3B8 !important; }
html[data-theme="dark"] #sa-pvp-create input[type="text"],
html[data-theme="dark"] #sa-pvp-create input[type="number"],
html[data-theme="dark"] #sa-pvp-create select,
html[data-theme="dark"] #sa-pvp-create textarea {
    background: rgba(15, 23, 42, 0.6) !important;
    color: #F1F5F9 !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
}
html[data-theme="dark"] #sa-pvp-create a[style*="background: #f1f5f9"] {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #CBD5E1 !important;
}
</style>
@endpush

@section('content')
<div id="sa-pvp-create" style="padding: 24px; max-width: 900px; margin: 0 auto;">
    <a href="{{ route('admin.pvp-challenges.index') }}"
       style="display: inline-flex; align-items: center; gap: 6px; color: #64748b; text-decoration: none; margin-bottom: 18px; font-size: 14px;">
        <i class="fas fa-arrow-right" aria-hidden="true"></i> رجوع إلى التحديات
    </a>

    <div style="background: #fff; border-radius: 14px; padding: 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.06);">
        <h2 style="margin: 0 0 24px; font-size: 22px; font-weight: 700; color: #1e293b;">
            ⚔️ {{ isset($challenge) ? 'تعديل تحدي PvP' : 'إنشاء تحدي PvP جديد' }}
        </h2>

        @if($errors->any())
        <div role="alert" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 10px; margin-bottom: 18px;">
            <strong>تأكد من المدخلات:</strong>
            <ul style="margin: 8px 0 0; padding-right: 20px;">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ isset($challenge) ? route('admin.pvp-challenges.update', $challenge->id) : route('admin.pvp-challenges.store') }}">
            @csrf
            @isset($challenge) @method('PUT') @endisset

            <div style="margin-bottom: 20px;">
                <label for="title" style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b;">
                    عنوان التحدي *
                </label>
                <input type="text" id="title" name="title" required maxlength="255"
                       value="{{ old('title', $challenge->title ?? '') }}"
                       style="width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="value_id" style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b;">
                    ربط التحدي بقيمة (اختياري)
                </label>
                <select id="value_id" name="value_id"
                        style="width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; background: #fff;">
                    <option value="">— تحدي عام (يظهر لكل المدارس) —</option>
                    @foreach($values as $value)
                    <option value="{{ $value->id }}" {{ old('value_id', $challenge->value_id ?? '') == $value->id ? 'selected' : '' }}>
                        {{ $value->name }}
                    </option>
                    @endforeach
                </select>
                <small style="color: #94a3b8; font-size: 13px;">
                    إذا اخترت قيمة، التحدي يظهر فقط للطلاب في المدارس التي فعّلت هذه القيمة.
                </small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label for="time_limit" style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b;">
                        الوقت الإجمالي (ثواني) *
                    </label>
                    <input type="number" id="time_limit" name="time_limit" required min="30" max="1800"
                           value="{{ old('time_limit', $challenge->time_limit ?? 600) }}"
                           style="width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px;">
                    <small style="color: #94a3b8; font-size: 13px;">30 إلى 1800 ثانية</small>
                </div>

                <div>
                    <label for="difficulty" style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b;">
                        مستوى الصعوبة
                    </label>
                    <select id="difficulty" name="difficulty"
                            style="width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; background: #fff;">
                        @php $diffVal = old('difficulty', $challenge->difficulty ?? 'medium'); @endphp
                        <option value="easy" {{ $diffVal === 'easy' ? 'selected' : '' }}>سهل</option>
                        <option value="medium" {{ $diffVal === 'medium' ? 'selected' : '' }}>متوسط</option>
                        <option value="hard" {{ $diffVal === 'hard' ? 'selected' : '' }}>صعب</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b;">
                    الأسئلة *
                    <small style="font-weight: 400; color: #94a3b8;">— أضِف كل سؤال وخياراته، وحدّد الإجابة الصحيحة ودرجة السؤال</small>
                </label>
                <div id="pvpQuestions"></div>
                <button type="button" onclick="addPvpQuestion()"
                        style="margin-top: 6px; width: 100%; background: #eef2ff; color: #4338ca; border: 2px dashed #c7d2fe; border-radius: 12px; padding: 13px 18px; font-weight: 700; font-size: 15px; cursor: pointer;">
                    ➕ إضافة سؤال
                </button>
                <small style="display:block; color: #94a3b8; font-size: 13px; margin-top: 6px;">
                    كلّما أجاب الطالب أسرع حصل على درجة أعلى للسؤال (تناقص تلقائي مع الوقت). الحد الأقصى 50 سؤالًا.
                </small>
                <input type="hidden" name="questions_json" id="questions_json">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $challenge->is_active ?? true) ? 'checked' : '' }}
                           style="width: 18px; height: 18px;">
                    <span style="font-weight: 600; color: #1e293b;">تفعيل التحدي فور إنشائه</span>
                </label>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit"
                        style="background: linear-gradient(135deg, #8b5cf6, #ec4899); color: #fff; padding: 14px 32px; border: none; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer;">
                    💾 {{ isset($challenge) ? 'حفظ التعديلات' : 'حفظ التحدي' }}
                </button>
                <a href="{{ route('admin.pvp-challenges.index') }}"
                   style="background: #f1f5f9; color: #475569; padding: 14px 24px; border-radius: 12px; font-weight: 600; text-decoration: none;">إلغاء</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    let uid = 0;
    const wrap = document.getElementById('pvpQuestions');

    function optionRow(cardUid, i, checked, text) {
        return `
        <label style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <input type="radio" name="correct-${cardUid}" value="${i}" ${checked ? 'checked' : ''} title="الإجابة الصحيحة" style="width:18px;height:18px;flex:none;">
            <input type="text" class="pvp-opt" placeholder="خيار ${i + 1}" value="${text ? String(text).replace(/"/g, '&quot;') : ''}" style="flex:1;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:8px;">
        </label>`;
    }

    window.addPvpQuestion = function (data) {
        if (wrap.querySelectorAll('.pvp-q-card').length >= 50) { alert('الحد الأقصى 50 سؤالًا'); return; }
        const u = ++uid;
        data = data || {};
        const type = data.type || 'multiple_choice';
        const opts = data.options || [];
        const card = document.createElement('div');
        card.className = 'pvp-q-card';
        card.dataset.uid = u;
        card.style.cssText = 'border:2px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:12px;background:#f8fafc;';
        let mcRows = '';
        for (let i = 0; i < 4; i++) {
            const o = opts[i];
            const isCorrect = (type === 'multiple_choice') && (data.correct === i || (data.correct == null && i === 0));
            mcRows += optionRow(u, i, isCorrect, o ? (o.text || o) : '');
        }
        card.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px;">
            <strong class="pvp-q-title" style="color:#1e293b;">سؤال</strong>
            <div style="display:flex;align-items:center;gap:10px;">
                <label style="font-size:13px;color:#475569;display:flex;align-items:center;gap:6px;">الدرجة
                    <input type="number" class="pvp-q-points" value="${data.points || 100}" min="1" max="1000" style="width:74px;padding:6px 8px;border:1.5px solid #e2e8f0;border-radius:8px;">
                </label>
                <button type="button" class="pvp-q-remove" title="حذف السؤال" style="background:#fef2f2;color:#dc2626;border:none;border-radius:8px;padding:7px 11px;cursor:pointer;font-weight:800;">✕</button>
            </div>
        </div>
        <input type="text" class="pvp-q-text" placeholder="نص السؤال" value="${data.text ? String(data.text).replace(/"/g, '&quot;') : ''}" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;margin-bottom:10px;">
        <select class="pvp-q-type" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;margin-bottom:10px;background:#fff;">
            <option value="multiple_choice" ${type === 'multiple_choice' ? 'selected' : ''}>اختيار من متعدد</option>
            <option value="true_false" ${type === 'true_false' ? 'selected' : ''}>صح أو خطأ</option>
        </select>
        <div class="pvp-q-mc" style="${type === 'true_false' ? 'display:none;' : ''}">${mcRows}</div>
        <div class="pvp-q-tf" style="${type === 'true_false' ? '' : 'display:none;'}">
            <label style="margin-inline-end:18px;"><input type="radio" name="tf-${u}" value="true" ${data.correct !== 'false' ? 'checked' : ''}> ✅ صح</label>
            <label><input type="radio" name="tf-${u}" value="false" ${data.correct === 'false' ? 'checked' : ''}> ❌ خطأ</label>
        </div>`;
        wrap.appendChild(card);
        card.querySelector('.pvp-q-remove').addEventListener('click', function () { card.remove(); renumber(); });
        card.querySelector('.pvp-q-type').addEventListener('change', function () {
            card.querySelector('.pvp-q-mc').style.display = this.value === 'true_false' ? 'none' : '';
            card.querySelector('.pvp-q-tf').style.display = this.value === 'true_false' ? '' : 'none';
        });
        renumber();
    };

    function renumber() {
        wrap.querySelectorAll('.pvp-q-card').forEach((c, i) => {
            c.querySelector('.pvp-q-title').textContent = 'سؤال #' + (i + 1);
        });
    }

    function collect() {
        const out = [];
        const cards = wrap.querySelectorAll('.pvp-q-card');
        for (let idx = 0; idx < cards.length; idx++) {
            const c = cards[idx];
            const text = c.querySelector('.pvp-q-text').value.trim();
            const type = c.querySelector('.pvp-q-type').value;
            const points = parseInt(c.querySelector('.pvp-q-points').value, 10) || 100;
            if (!text) return { error: `السؤال #${idx + 1}: النص مطلوب` };
            if (type === 'true_false') {
                const tf = c.querySelector('.pvp-q-tf input[type="radio"]:checked');
                out.push({ text, type, options: [], correct: tf ? tf.value : 'true', points });
            } else {
                const rows = c.querySelectorAll('.pvp-q-mc label');
                const options = [];
                let correct = null;
                rows.forEach((row) => {
                    const t = row.querySelector('.pvp-opt').value.trim();
                    const radio = row.querySelector('input[type="radio"]');
                    if (t) {
                        options.push({ text: t });
                        if (radio.checked) correct = options.length - 1;
                    }
                });
                if (options.length < 2) return { error: `السؤال #${idx + 1}: أضِف خيارين على الأقل` };
                if (correct === null) return { error: `السؤال #${idx + 1}: حدّد الإجابة الصحيحة (بخيار غير فارغ)` };
                out.push({ text, type, options, correct, points });
            }
        }
        return { data: out };
    }

    // seed: من old عند خطأ تحقّق، وإلا سؤال واحد فارغ
    const seeded = @json(old('questions_json') ? json_decode(old('questions_json'), true) : ($seedQuestions ?? null));
    if (Array.isArray(seeded) && seeded.length) {
        seeded.forEach((q) => window.addPvpQuestion(q));
    } else {
        window.addPvpQuestion();
    }

    const form = document.querySelector('#sa-pvp-create form');
    form.addEventListener('submit', function (e) {
        const res = collect();
        if (res.error) { e.preventDefault(); alert(res.error); return; }
        if (!res.data.length) { e.preventDefault(); alert('أضِف سؤالًا واحدًا على الأقل'); return; }
        document.getElementById('questions_json').value = JSON.stringify(res.data);
    });
})();
</script>
@endpush
@endsection
