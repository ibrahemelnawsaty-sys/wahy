<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesActivityMedia;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lesson;
use Illuminate\Http\Request;

class ActivityManagementController extends Controller
{
    use HandlesActivityMedia;

    public function index(Request $request)
    {
        $query = Activity::with('lesson.concept.value');

        // Filter by lesson
        if ($request->filled('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $activities = $query->orderBy('order')->paginate(20);
        $lessons = Lesson::with('concept.value')->orderBy('order')->get();

        return view('admin.activities.index', compact('activities', 'lessons'));
    }

    public function create(Request $request)
    {
        $lessons = Lesson::with('concept.value')->orderBy('order')->get();
        $selectedLesson = $request->lesson_id;

        return view('admin.activities.create', compact('lessons', 'selectedLesson'));
    }

    public function store(Request $request)
    {
        // «max_file_size_mb» في النموذج بدل max_file_size لتفادي اصطدامه بحقل PHP السحريّ MAX_FILE_SIZE
        // (يُقارَن بلا حساسيّة أحرف فيرفض كلّ رفع >10 بايت = سبب عدم حفظ الوسائط). نُعيد ربطه بالعمود.
        $request->merge(['max_file_size' => $request->input('max_file_size_mb', $request->input('max_file_size'))]);

        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,exercise,project,creative,upload,practical,discussion,image_order',
            'question_type' => 'nullable|string', // Issue #16: قبول question_type كتنوع داخل النشاط
            'questions' => 'nullable|json',
            'points' => 'nullable|integer|min:0',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'manual_review' => 'nullable|boolean',
            'requires_parent_approval' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive,draft',

            // حقول خاصة بالاختبار
            'quiz_duration' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',

            // حقول خاصة بالمشروع
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'in:document,image,video,audio',
            'max_file_size' => 'nullable|integer|min:1|max:100',
        ]);

        // Auto-order within the same lesson
        if (! $request->filled('order')) {
            $validated['order'] = Activity::where('lesson_id', $validated['lesson_id'])->max('order') + 1;
        }

        // مفتاح "يتطلب موافقة/تصحيح المعلم يدوياً" (checkbox غير المُرسل = false)
        $validated['manual_review'] = $request->boolean('manual_review');
        $validated['requires_parent_approval'] = $request->boolean('requires_parent_approval');

        // Parse questions JSON
        if ($request->filled('questions')) {
            $validated['questions'] = json_decode($validated['questions'], true);
            $this->validateActivityQuestions($validated['questions']);
        }

        // allowed_file_types مصبوب array في الموديل فيُشفَّر تلقائياً؛ json_encode اليدويّ
        // كان يُنتج تشفيراً مزدوجاً (يُقرأ نصًّا لا مصفوفة → accept=".pdf") فحُذف.

        // الوسائط المتعددة (اختياريّة) — نجمعها بمرونة: فشلُها (تحقّق/تخزين) يجب ألّا يُسقِط النشاط
        // كلّه (كان استثناء التحقّق يعيدنا للنموذج بصمت فيختفي كلّ شيء). نحفظ النشاط دائماً ونُظهر
        // سبب فشل الوسائط إن وُجد.
        $media = [];
        $mediaError = null;
        try {
            $media = $this->collectUploadedActivityMedia($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $mediaError = collect($e->errors())->flatten()->implode(' ');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('activity-media-collect-failed@store', ['error' => $e->getMessage()]);
            $mediaError = 'خطأ أثناء حفظ الملفّ: ' . $e->getMessage();
        }
        if (! empty($media)) {
            $validated['media'] = $media;
        }

        // نشرٌ مباشر: محتوى الأدمن (منهجيّ موثوق، مُعتمَد تلقائياً) يجب أن يظهر للطلاب فوراً.
        // بدون هذا يبقى all_schools_mode='none' الافتراضيّ فيُولَد النشاط مخفيّاً عن كل الطلاب
        // (انحدار أدخلته إعادة هيكلة النشر التي استبدلت فلتر approval_status='approved'). القيمة
        // 'direct' تطابق بالضبط شرط الشفاء التوافقيّ في هجرة النشر (approved + lesson → direct).
        $validated['all_schools_mode'] = 'direct';

        Activity::create($validated);

        // الوسائط اختياريّة: لو تعذّر إرفاقها (نادر) نُنبّه دون إسقاط النشاط.
        $note = $mediaError ? ' — لكن تعذّر إرفاق بعض الوسائط.' : '';

        return redirect()
            ->route('admin.activities.index', ['lesson_id' => $validated['lesson_id']])
            ->with('success', 'تم إضافة النشاط بنجاح!' . $note);
    }

    public function show(Activity $activity)
    {
        $activity->load('lesson.concept.value');
        $submissionsCount = $activity->submissions()->count();

        return view('admin.activities.show', compact('activity', 'submissionsCount'));
    }

    public function edit(Activity $activity)
    {
        $lessons = Lesson::with('concept.value')->orderBy('order')->get();

        return view('admin.activities.edit', compact('activity', 'lessons'));
    }

    public function update(Request $request, Activity $activity)
    {
        // إعادة ربط حقل النموذج max_file_size_mb بعمود max_file_size (تفاديّاً لاصطدام MAX_FILE_SIZE).
        $request->merge(['max_file_size' => $request->input('max_file_size_mb', $request->input('max_file_size'))]);

        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,exercise,project,creative,upload,practical,discussion,image_order',
            'question_type' => 'nullable|string', // Issue #16
            'questions' => 'nullable|json',
            'points' => 'nullable|integer|min:0',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'manual_review' => 'nullable|boolean',
            'requires_parent_approval' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive,draft',

            // حقول خاصة بالاختبار
            'quiz_duration' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',

            // حقول خاصة بالمشروع
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'in:document,image,video,audio',
            'max_file_size' => 'nullable|integer|min:1|max:100',
        ]);

        // مفتاح "يتطلب موافقة/تصحيح المعلم يدوياً" (checkbox غير المُرسل = false)
        $validated['manual_review'] = $request->boolean('manual_review');
        $validated['requires_parent_approval'] = $request->boolean('requires_parent_approval');

        // Parse questions JSON
        if ($request->filled('questions')) {
            $validated['questions'] = json_decode($validated['questions'], true);
            $this->validateActivityQuestions($validated['questions']);
        }

        // allowed_file_types مصبوب array في الموديل فيُشفَّر تلقائياً؛ json_encode اليدويّ
        // كان يُنتج تشفيراً مزدوجاً (يُقرأ نصًّا لا مصفوفة → accept=".pdf") فحُذف.

        // الوسائط المتعددة: حذف المحدَّد (remove_media[]) + إضافة المرفوع الجديد
        $mergedMedia = $this->mergeActivityMedia($request, is_array($activity->media) ? $activity->media : []);
        if ($mergedMedia !== null) {
            $validated['media'] = $mergedMedia;
        }

        // إلغاء تأشير كل الأنواع يُلغي القيد (يُكتب []) لا عملية لاغية صامتة — يُفرَض لأنواع الرفع فقط.
        if (in_array($validated['type'] ?? $activity->type, ['project', 'upload', 'creative', 'practical'], true)) {
            $validated['allowed_file_types'] = array_values((array) $request->input('allowed_file_types', []));
        }

        $activity->update($validated);

        return redirect()
            ->route('admin.activities.index', ['lesson_id' => $validated['lesson_id']])
            ->with('success', 'تم تحديث النشاط بنجاح!');
    }

    /**
     * حارس خادمي لسلامة الأسئلة — يمنع تخزين نشاط بلا مفتاح إجابة صالح
     * (خصوصاً الإجابة القصيرة التي قد تُرسَل فارغة إن لم يُطلَق onchange قبل الحفظ).
     */
    private function validateActivityQuestions($questions): void
    {
        if (! is_array($questions)) {
            return;
        }

        foreach ($questions as $i => $q) {
            if (! is_array($q)) {
                continue;
            }
            $type = $q['type'] ?? $q['question_type'] ?? null;
            $n = $i + 1;

            if ($type === 'short_answer') {
                $answer = trim((string) ($q['correct_answer'] ?? $q['answer'] ?? ''));
                if ($answer === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'questions' => "السؤال رقم {$n}: يجب إدخال الإجابة الصحيحة لسؤال الإجابة القصيرة.",
                    ]);
                }
            }

            if ($type === 'letter_choice') {
                $word = trim((string) ($q['word'] ?? $q['target_word'] ?? ''));
                $letters = is_array($q['options'] ?? null)
                    ? array_filter(array_map(
                        fn ($o) => trim((string) (is_array($o) ? ($o['text'] ?? $o['label'] ?? '') : $o)),
                        $q['options'],
                    ))
                    : [];
                if ($word === '' && empty($letters)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'questions' => "السؤال رقم {$n}: أدخل حروف الكلمة لسؤال اختيار الحروف.",
                    ]);
                }
            }

            // اختيار متعدّد / صح-خطأ بلا مفتاح إجابة صالح → كان يُحفَظ فيرتدّ التصحيح الآليّ إلى
            // null (مراجعة يدويّة) ويذهب للمعلّم بلا سبب. نمنع الحفظ (نفس منطق المصحّح: hasAnswerKey).
            if (in_array($type, ['multiple_choice', 'true_false'], true)
                && ! \App\Services\ActivityGradingService::hasAnswerKey($q)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'questions' => "السؤال رقم {$n}: حدّد الإجابة الصحيحة لسؤال الاختيار.",
                ]);
            }
        }
    }

    public function destroy(Activity $activity)
    {
        // Check if activity has submissions
        if ($activity->submissions()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف النشاط لوجود إرساليات مرتبطة به!');
        }

        $lessonId = $activity->lesson_id;
        $activity->delete();

        return redirect()
            ->route('admin.activities.index', ['lesson_id' => $lessonId])
            ->with('success', 'تم حذف النشاط بنجاح!');
    }

    public function toggleStatus(Activity $activity)
    {
        $newStatus = $activity->status === 'active' ? 'inactive' : 'active';
        $activity->update(['status' => $newStatus]);

        return back()->with('success', 'تم تحديث حالة النشاط بنجاح!');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        $path = $request->file('image')->store('activities/images', 'public');
        $url = asset('storage/data/' . $path);

        return response()->json(['url' => $url]);
    }
}
