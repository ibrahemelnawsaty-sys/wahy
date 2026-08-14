@extends('layouts.admin')

@section('title', 'حملة بريد جديدة')
@section('page-title', 'إرسال بريد جماعيّ')

@section('content')
<div class="ec-wrap">
    @if (session('success'))<div class="ec-alert ok">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="ec-alert err">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('admin.email-campaigns.store') }}" id="ecForm">
        @csrf

        <div class="ec-card">
            <label class="ec-label">العنوان (Subject)</label>
            <input type="text" name="subject" class="ec-input" value="{{ old('subject') }}" required placeholder="مثال: تحديث مهمّ من {{ setting('site_name','أثيل مكة') }}">
            <div class="ec-hint">يمكنك استخدام <code>{{ '{{name}}' }}</code> لإدراج اسم المستلِم.</div>
        </div>

        <div class="ec-card">
            <label class="ec-label">المتن (Body)</label>
            <div data-rich-editor="campaignBody" data-target="bodyHidden" dir="rtl" hidden>{!! safe_html(old('body')) !!}</div>
            <textarea name="body" id="bodyHidden" rows="8" class="ec-input" required>{{ old('body') }}</textarea>
            <div class="ec-hint">يُعقَّم المحتوى تلقائيًّا، ويظهر داخل قالب البريد الموحّد مع تذييل «إلغاء الاشتراك».</div>
        </div>

        <div class="ec-card">
            <label class="ec-label">إلى مَن؟</label>
            @php $at = old('audience_type', 'all'); @endphp
            <div class="ec-aud">
                <label><input type="radio" name="audience_type" value="all" {{ $at==='all'?'checked':'' }} onchange="ecAud()"> كل المستخدمين</label>
                <label><input type="radio" name="audience_type" value="role" {{ $at==='role'?'checked':'' }} onchange="ecAud()"> دور محدَّد</label>
                <label><input type="radio" name="audience_type" value="school" {{ $at==='school'?'checked':'' }} onchange="ecAud()"> مدرسة محدَّدة</label>
                <label><input type="radio" name="audience_type" value="custom" {{ $at==='custom'?'checked':'' }} onchange="ecAud()"> إيميلات مخصّصة</label>
            </div>

            <div id="ec-role" class="ec-sub" style="display:none;">
                <select name="audience_role" class="ec-input">
                    @foreach($roles as $k=>$v)<option value="{{ $k }}" {{ old('audience_role')===$k?'selected':'' }}>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div id="ec-school" class="ec-sub" style="display:none;">
                <select name="audience_school" class="ec-input">
                    <option value="">— اختر مدرسة —</option>
                    @foreach($schools as $s)<option value="{{ $s->id }}" {{ (string)old('audience_school')===(string)$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div id="ec-custom" class="ec-sub" style="display:none;">
                <textarea name="audience_custom" rows="4" class="ec-input" placeholder="بريد واحد في كل سطر (أو مفصولة بفاصلة)">{{ old('audience_custom') }}</textarea>
            </div>
        </div>

        <div class="ec-actions">
            <button type="submit" name="action" value="test" class="ec-btn test">✉️ إرسال تجريبيّ لي</button>
            <button type="submit" name="action" value="send" class="ec-btn send" onclick="return confirm('إرسال الحملة للجمهور المحدَّد؟');">🚀 إرسال الحملة</button>
        </div>
    </form>
</div>

<script>
function ecAud(){
    var v=(document.querySelector('input[name=audience_type]:checked')||{}).value;
    ['role','school','custom'].forEach(function(k){ document.getElementById('ec-'+k).style.display = (v===k)?'block':'none'; });
}
document.addEventListener('DOMContentLoaded', ecAud);
</script>

<style>
.ec-wrap{max-width:760px;margin:0 auto;}
.ec-alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600;font-size:14px;}
.ec-alert.ok{background:#dcfce7;color:#166534;} .ec-alert.err{background:#fee2e2;color:#991b1b;}
.ec-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;margin-bottom:18px;}
.ec-label{display:block;font-weight:800;margin-bottom:10px;color:#1e293b;font-size:15px;}
.ec-input{width:100%;padding:11px 14px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:14px;font-family:inherit;box-sizing:border-box;}
.ec-hint{color:#94a3b8;font-size:12.5px;margin-top:8px;}
.ec-aud{display:flex;flex-wrap:wrap;gap:14px;} .ec-aud label{display:flex;align-items:center;gap:6px;font-weight:600;font-size:14px;cursor:pointer;}
.ec-sub{margin-top:14px;}
.ec-actions{display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap;}
.ec-btn{border:none;padding:13px 28px;border-radius:12px;font-weight:800;font-size:15px;cursor:pointer;}
.ec-btn.test{background:#f1f5f9;color:#475569;} .ec-btn.send{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
[data-theme="dark"] .ec-card{background:#1e293b;border-color:#334155;} [data-theme="dark"] .ec-label{color:#f1f5f9;}
</style>
@endsection
