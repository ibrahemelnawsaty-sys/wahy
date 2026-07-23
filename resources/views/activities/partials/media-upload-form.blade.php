{{--
    قسم رفع «الوسائط المتعددة» لنموذج تعريف/تعديل النشاط — مشترك (أدمن/معلّم).
    يتطلّب (اختياري) $activity لعرض الوسائط الحالية وحذفها عبر remove_media[].
    ⚠️ النموذج الحاوي يجب أن يكون enctype="multipart/form-data".
    يُعالَج الرفع خادميًّا عبر HandlesActivityMedia::collectUploadedActivityMedia/mergeActivityMedia،
    ويُعرَض للطالب عبر activities/partials/media.blade.php.
--}}
<style>
    .amu-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-top:20px; }
    .amu-card h3 { font-size:18px; font-weight:800; color:#1e293b; margin:0 0 6px; display:flex; align-items:center; gap:8px; }
    .amu-hint { font-size:13px; color:#94a3b8; margin-bottom:18px; }
    .amu-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; }
    .amu-upload { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; width:100%; min-height:150px; box-sizing:border-box; border:2px dashed #cbd5e1; border-radius:14px; padding:20px 14px; text-align:center; cursor:pointer; transition:border-color .2s, background .2s; background:#fafbfc; }
    .amu-upload:hover { border-color:#667eea; background:#f0f4ff; }
    .amu-upload input[type=file] { display:none; }
    .amu-upload .i { font-size:34px; line-height:1; }
    .amu-upload .t { color:#334155; font-size:14px; font-weight:700; }
    .amu-upload .h { color:#94a3b8; font-size:12px; }
    .amu-preview { display:none; margin-top:12px; padding:12px; border-radius:12px; background:#f1f5f9; }
    .amu-preview img, .amu-preview video, .amu-preview audio { max-width:100%; border-radius:10px; }
    .amu-existing { display:flex; flex-direction:column; gap:8px; margin-bottom:16px; }
    .amu-existing label { display:flex; align-items:center; gap:8px; padding:9px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-weight:600; color:#334155; }
</style>

<div class="amu-card">
    <h3>📎 الوسائط المتعددة (اختياري)</h3>
    <p class="amu-hint">أرفق ملفّات وسائط (صور/صوت/فيديو/مستندات) لتعزيز النشاط — تظهر للطالب داخل النشاط.</p>

    @isset($activity)
        @php
            $__existingMedia = is_array($activity->media ?? null) ? $activity->media : [];
            if (empty($__existingMedia) && ! empty($activity->attachment)) {
                $__existingMedia = [['type' => null, 'path' => $activity->attachment, 'name' => basename($activity->attachment)]];
            }
        @endphp
        @if(! empty($__existingMedia))
            <div style="font-weight:700; color:#334155; margin-bottom:8px;">الوسائط الحالية (حدّد ما تريد حذفه):</div>
            <div class="amu-existing">
                @foreach($__existingMedia as $__i => $__m)
                    @php $__p = $__m['path'] ?? ''; @endphp
                    <label>
                        <input type="checkbox" name="remove_media[]" value="{{ $__i }}">
                        <span>📎 {{ $__m['name'] ?? basename($__p) }}</span>
                        @if($__p)
                            <a href="{{ \Illuminate\Support\Str::startsWith($__p, ['http://','https://','/']) ? $__p : \Illuminate\Support\Facades\Storage::disk('public')->url($__p) }}" target="_blank" rel="noopener" style="margin-inline-start:auto; font-size:13px;">عرض</a>
                        @endif
                    </label>
                @endforeach
            </div>
            <div style="font-size:12.5px; color:#94a3b8; margin-bottom:16px;">المحدَّدة ستُحذف عند الحفظ.</div>
        @endif
    @endisset

    <div class="amu-grid">
        <div>
            <label class="amu-upload"><div class="i">🖼️</div><div class="t">إرفاق صورة</div><div class="h">JPG, PNG — عدّة صور</div>
                <input type="file" name="image[]" accept="image/*" multiple onchange="amuPreview(this,'amuImg')"></label>
            <div class="amu-preview" id="amuImg"></div>
        </div>
        <div>
            <label class="amu-upload"><div class="i">🎵</div><div class="t">إرفاق صوت</div><div class="h">MP3, WAV — عدّة مقاطع</div>
                <input type="file" name="audio[]" accept="audio/*" multiple onchange="amuPreview(this,'amuAud')"></label>
            <div class="amu-preview" id="amuAud"></div>
        </div>
        <div>
            <label class="amu-upload"><div class="i">🎬</div><div class="t">إرفاق فيديو</div><div class="h">MP4, WebM — حتى 100MB</div>
                <input type="file" name="video[]" accept="video/*" multiple onchange="amuPreview(this,'amuVid')"></label>
            <div class="amu-preview" id="amuVid"></div>
        </div>
        <div>
            <label class="amu-upload"><div class="i">📄</div><div class="t">مستند / ملف</div><div class="h">PDF, DOCX, PPTX</div>
                <input type="file" name="document[]" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx" multiple onchange="amuPreview(this,'amuDoc')"></label>
            <div class="amu-preview" id="amuDoc"></div>
        </div>
    </div>
</div>

<script>
function amuPreview(input, id){
    var p = document.getElementById(id);
    if(!input.files || !input.files.length){ p.style.display='none'; p.innerHTML=''; return; }
    p.style.display='block'; p.innerHTML='';
    var esc = function(s){ return String(s).replace(/[<>&"]/g, function(c){ return {'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c]; }); };
    Array.from(input.files).forEach(function(f){
        var url = URL.createObjectURL(f), box = document.createElement('div'), nm = esc(f.name);
        box.style.marginBottom = '10px';
        if(f.type.startsWith('image/')) box.innerHTML = '<img src="'+url+'" style="max-height:180px;max-width:100%;">';
        else if(f.type.startsWith('audio/')) box.innerHTML = '<audio controls src="'+url+'" style="width:100%;"></audio>';
        else if(f.type.startsWith('video/')) box.innerHTML = '<video controls src="'+url+'" style="max-height:220px;width:100%;"></video>';
        else box.innerHTML = '<div style="display:flex;align-items:center;gap:8px;"><span style="font-size:26px;">📄</span><span>'+nm+'</span></div>';
        box.innerHTML += '<div style="margin-top:5px;font-size:12.5px;color:#64748b;">'+nm+' ('+(f.size/1024/1024).toFixed(2)+' MB)</div>';
        p.appendChild(box);
    });
}
</script>
