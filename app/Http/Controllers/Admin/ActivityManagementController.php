<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lesson;
use Illuminate\Http\Request;

class ActivityManagementController extends Controller
{
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
            $query->where(function($q) use ($search) {
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
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,exercise,project,creative,upload,practical,discussion,image_order',
            'question_type' => 'nullable|string', // Issue #16: قبول question_type كتنوع داخل النشاط
            'questions' => 'nullable|json',
            'points' => 'nullable|integer|min:0',
            'passing_score' => 'nullable|integer|min:0|max:100',
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
        if (!$request->filled('order')) {
            $validated['order'] = Activity::where('lesson_id', $validated['lesson_id'])->max('order') + 1;
        }

        // Parse questions JSON
        if ($request->filled('questions')) {
            $validated['questions'] = json_decode($validated['questions'], true);
        }
        
        // تحويل أنواع الملفات المسموحة إلى JSON
        if (isset($validated['allowed_file_types'])) {
            $validated['allowed_file_types'] = json_encode($validated['allowed_file_types']);
        }

        Activity::create($validated);

        return redirect()
            ->route('admin.activities.index', ['lesson_id' => $validated['lesson_id']])
            ->with('success', 'تم إضافة النشاط بنجاح!');
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
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,exercise,project,creative,upload,practical,discussion,image_order',
            'question_type' => 'nullable|string', // Issue #16
            'questions' => 'nullable|json',
            'points' => 'nullable|integer|min:0',
            'passing_score' => 'nullable|integer|min:0|max:100',
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

        // Parse questions JSON
        if ($request->filled('questions')) {
            $validated['questions'] = json_decode($validated['questions'], true);
        }
        
        // تحويل أنواع الملفات المسموحة إلى JSON
        if (isset($validated['allowed_file_types'])) {
            $validated['allowed_file_types'] = json_encode($validated['allowed_file_types']);
        }

        $activity->update($validated);

        return redirect()
            ->route('admin.activities.index', ['lesson_id' => $validated['lesson_id']])
            ->with('success', 'تم تحديث النشاط بنجاح!');
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
        $url = asset('storage/app/public/data/' . $path);

        return response()->json(['url' => $url]);
    }
}

