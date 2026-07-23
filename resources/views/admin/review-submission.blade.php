@extends('layouts.admin')

@section('title', 'مراجعة التقديم')
@section('page-title', 'مراجعة نشاط الطالب')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- زر العودة -->
    <a href="{{ route('admin.pending-submissions') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition">
        <span>←</span>
        التقديمات المعلقة
    </a>

    <!-- معلومات الطالب والنشاط -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- بطاقة الطالب -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span>👨‍🎓</span>
                معلومات الطالب
            </h3>
            <div class="flex items-center gap-4">
                @if($submission->student?->avatar)
                    <img src="{{ $submission->student->avatar_url }}" class="w-16 h-16 rounded-xl object-cover">
                @else
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                        {{ mb_substr($submission->student?->name ?? '?', 0, 1) }}
                    </div>
                @endif
                <div>
                    <div class="text-xl font-bold text-gray-800">{{ $submission->student?->name ?? 'غير معروف' }}</div>
                    <div class="text-gray-500">{{ $submission->student?->email ?? '-' }}</div>
                    <div class="text-sm text-gray-400 mt-1">
                        🏫 {{ $submission->student?->school?->name ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- بطاقة النشاط -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span>📋</span>
                معلومات النشاط
            </h3>
            <div class="space-y-3">
                <div>
                    <div class="text-sm text-gray-500">اسم النشاط</div>
                    <div class="font-semibold text-gray-800">{{ $submission->activity?->title ?? 'نشاط محذوف' }}</div>
                </div>
                @if($submission->activity?->lesson)
                <div>
                    <div class="text-sm text-gray-500">الدرس</div>
                    <div class="text-gray-700">{{ $submission->activity->lesson->title }}</div>
                </div>
                @endif
                @if($submission->activity?->lesson?->meaning?->concept?->value)
                <div>
                    <div class="text-sm text-gray-500">القيمة</div>
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">
                        💎 {{ $submission->activity->lesson->concept->value->name }}
                    </span>
                </div>
                @endif
                <div>
                    <div class="text-sm text-gray-500">تاريخ التقديم</div>
                    <div class="text-gray-700">{{ $submission->submitted_at?->format('Y/m/d H:i') ?? $submission->created_at->format('Y/m/d H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- إجابة الطالب -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span>📝</span>
            إجابة الطالب
        </h3>

        @php
            // إجابة الطالب: نصّ، ترتيب صور [{image_url,selected_order}], رفع ملف {note,file,file_url},
            // قائمة، أو كائن كويز. نتعرّف على صيغة رفع الملف صراحةً (كانت تُطبَع JSON خامًا).
            $rawAns = $submission->answer;
            $ansDecoded = is_string($rawAns) ? json_decode($rawAns, true) : (is_array($rawAns) ? $rawAns : null);
            $ansImages = [];
            $ansText = null;
            $ansFile = null;
            if (is_array($ansDecoded)) {
                $isList = array_is_list($ansDecoded);
                if ($isList) {
                    foreach ($ansDecoded as $it) {
                        // http(s) فقط — إجابة من الطالب؛ منع javascript:/data: (XSS)
                        if (is_array($it) && ! empty($it['image_url']) && preg_match('~^https?://~i', (string) $it['image_url'])) {
                            $ansImages[] = ['url' => (string) $it['image_url'], 'order' => $it['selected_order'] ?? ($it['order'] ?? null)];
                        }
                    }
                }
                if (! empty($ansImages)) {
                    usort($ansImages, fn ($a, $b) => ((int) ($a['order'] ?? 0)) <=> ((int) ($b['order'] ?? 0)));
                } elseif (array_key_exists('note', $ansDecoded) || array_key_exists('file', $ansDecoded)) {
                    // رفع ملف: {note, file, file_url} — الملاحظة نصّ، والملف مرفق
                    $ansText = is_scalar($ansDecoded['note'] ?? null) ? (string) $ansDecoded['note'] : null;
                    $ansFile = is_string($ansDecoded['file'] ?? null) ? $ansDecoded['file'] : null;
                } elseif ($isList) {
                    $ansText = implode('، ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE), $ansDecoded));
                } else {
                    $lines = [];
                    foreach ($ansDecoded as $k => $v) {
                        $val = is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE);
                        $lines[] = (is_numeric($k) ? ('السؤال ' . ((int) $k + 1) . ': ') : ($k . ': ')) . $val;
                    }
                    $ansText = implode("\n", $lines);
                }
            } elseif (is_string($rawAns)) {
                $ansText = $rawAns;
            }

            // احتياط عمود file_path القديم
            if (! $ansFile && $submission->file_path) { $ansFile = $submission->file_path; }

            // بناء رابط الملف من مسار تخزين التطبيق (نتجاهل file_url العميل — قد يُحقَن عبر API).
            // نرفض المسارات المطلقة (://) والتجاوز (..) فلا نعرض إلا ملفّات تخزين التطبيق.
            $ansFileUrl = null;
            $ansExt = '';
            if (is_string($ansFile) && $ansFile !== '' && ! preg_match('~://|\.\.~', $ansFile)) {
                $ansFileUrl = asset('storage/app/public/data/' . ltrim($ansFile, '/'));
                $ansExt = strtolower(pathinfo($ansFile, PATHINFO_EXTENSION));
            } else {
                $ansFile = null;
            }
            $ansFileKind = in_array($ansExt, ['jpg','jpeg','png','gif','webp','bmp','svg'], true) ? 'image'
                : (in_array($ansExt, ['mp3','wav','ogg','m4a','aac'], true) ? 'audio'
                : (in_array($ansExt, ['mp4','mov','webm','avi','mkv'], true) ? 'video' : 'file'));
            $ansFileMeta = match(true) {
                $ansExt === 'pdf' => ['📄', 'فتح / تحميل PDF'],
                in_array($ansExt, ['doc','docx'], true) => ['📝', 'فتح / تحميل مستند'],
                in_array($ansExt, ['xls','xlsx','csv'], true) => ['📊', 'فتح / تحميل جدول'],
                in_array($ansExt, ['ppt','pptx'], true) => ['📈', 'فتح / تحميل عرض تقديميّ'],
                default => ['📎', 'فتح / تحميل الملف'],
            };

            $ansIsUrl = ($ansText !== null && preg_match('~^\s*https?://\S+\s*$~i', $ansText) === 1);
        @endphp

        @if(! empty($ansImages))
            {{-- ترتيب الصور: صور مصغّرة مرقّمة قابلة للضغط بترتيب الطالب --}}
            <div class="flex flex-wrap gap-4">
                @foreach($ansImages as $it)
                    <div style="position:relative;width:130px;">
                        <span style="position:absolute;top:-8px;inset-inline-start:-8px;width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-weight:800;font-size:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,.25);z-index:1;">{{ $it['order'] ?? ($loop->index + 1) }}</span>
                        <a href="{{ $it['url'] }}" target="_blank" rel="noopener noreferrer" title="فتح الصورة">
                            <img src="{{ $it['url'] }}" alt="صورة {{ $loop->index + 1 }}" loading="lazy" style="width:130px;height:130px;object-fit:cover;border-radius:12px;border:2px solid rgba(102,126,234,.35);display:block;">
                        </a>
                    </div>
                @endforeach
            </div>
        @elseif($ansIsUrl)
            <a href="{{ trim($ansText) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-3 bg-blue-50 text-blue-700 rounded-lg font-bold hover:bg-blue-100" style="word-break:break-all;">🔗 فتح رابط الطالب</a>
        @elseif($ansText !== null && trim($ansText) !== '')
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-gray-800" style="white-space:pre-wrap;word-break:break-word;overflow-wrap:anywhere;">{{ \Illuminate\Support\Str::limit($ansText, 3000) }}</div>
            </div>
        @elseif(! $ansFile)
            <div class="text-gray-400 text-center py-4">لا توجد إجابة نصية</div>
        @endif

        {{-- مرفق الطالب: معاينة حسب النوع (صورة/صوت/فيديو) أو رابط تحميل (PDF/مستند/غيره) --}}
        @if($ansFile && $ansFileUrl)
            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <div class="text-sm text-gray-500 mb-2">📎 مرفق الطالب</div>
                @if($ansFileKind === 'image')
                    <a href="{{ $ansFileUrl }}" target="_blank" rel="noopener noreferrer" title="فتح الصورة كاملة">
                        <img src="{{ $ansFileUrl }}" alt="مرفق الطالب" loading="lazy" style="max-width:100%;max-height:420px;border-radius:12px;border:1px solid #e5e7eb;display:block;">
                    </a>
                @elseif($ansFileKind === 'audio')
                    <audio controls preload="metadata" src="{{ $ansFileUrl }}" style="width:100%;max-width:420px;"></audio>
                @elseif($ansFileKind === 'video')
                    <video controls preload="metadata" src="{{ $ansFileUrl }}" style="width:100%;max-width:520px;border-radius:12px;"></video>
                @else
                    <a href="{{ $ansFileUrl }}" target="_blank" rel="noopener noreferrer" download
                       class="inline-flex items-center gap-2 px-4 py-3 bg-white text-blue-700 rounded-lg font-bold hover:bg-blue-100 border border-blue-200" style="word-break:break-all;">
                        <span>{{ $ansFileMeta[0] }}</span>
                        {{ $ansFileMeta[1] }}
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- نموذج المراجعة -->
    <form action="{{ route('admin.save-review', $submission->id) }}" method="POST" class="bg-white rounded-xl shadow-sm p-6">
        @csrf
        
        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span>⚖️</span>
            التقييم والمراجعة
        </h3>

        <div class="space-y-6">
            <!-- حالة المراجعة -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">حالة التقديم</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-500 transition has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                        <input type="radio" name="status" value="approved" class="w-5 h-5 text-green-500" required>
                        <span class="text-2xl">✅</span>
                        <div>
                            <div class="font-semibold text-gray-800">قبول</div>
                            <div class="text-sm text-gray-500">الإجابة صحيحة ومقبولة</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-red-500 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                        <input type="radio" name="status" value="rejected" class="w-5 h-5 text-red-500">
                        <span class="text-2xl">❌</span>
                        <div>
                            <div class="font-semibold text-gray-800">رفض</div>
                            <div class="text-sm text-gray-500">الإجابة غير مقبولة</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- الدرجة -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الدرجة (من 100)</label>
                <input type="number" name="score" min="0" max="100" value="{{ $submission->activity?->points ?? 10 }}"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <p class="text-sm text-gray-500 mt-1">الدرجة الافتراضية للنشاط: {{ $submission->activity?->points ?? 10 }}</p>
            </div>

            <!-- الملاحظات -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات للطالب (اختياري)</label>
                <textarea name="feedback" rows="4" 
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                          placeholder="اكتب ملاحظاتك هنا...">{{ $submission->feedback }}</textarea>
            </div>

            <!-- أزرار الإرسال -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 py-3 px-6 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-semibold hover:shadow-lg transition">
                    ✅ حفظ المراجعة
                </button>
                <a href="{{ route('admin.pending-submissions') }}" class="py-3 px-6 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
                    إلغاء
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
