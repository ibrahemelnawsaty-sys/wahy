<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Concept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Lesson::with('concept.value');

        // Filter by concept
        if ($request->filled('concept_id')) {
            $query->where('concept_id', $request->concept_id);
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
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $lessons = $query->orderBy('order')->paginate(20);
        $concepts = Concept::with('value')->orderBy('order')->get();

        return view('admin.lessons.index', compact('lessons', 'concepts'));
    }

    public function create(Request $request)
    {
        $concepts = Concept::with('value')->orderBy('order')->get();
        $selectedConcept = $request->concept_id;

        return view('admin.lessons.create', compact('concepts', 'selectedConcept'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'concept_id' => 'required|exists:concepts,id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|in:text,video,audio,mixed',
            'video_url' => 'nullable|url|max:500',
            'audio_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|mimes:mp4,mov,avi,wmv,webm|max:51200',
            'audio_file' => 'nullable|mimes:mp3,wav,ogg,m4a|max:10240',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'duration' => 'nullable|integer|min:0',
            'points' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,draft,archived',
            // حقول نظام Streak
            'streak_enabled' => 'nullable|boolean',
            'streak_min_days' => 'nullable|integer|min:1|max:30',
            'streak_max_days' => 'nullable|integer|min:1|max:60',
            'streak_bonus_points' => 'nullable|integer|min:0',
        ]);

        // معالجة checkbox الـ streak
        $validated['streak_enabled'] = $request->has('streak_enabled');
        
        // إذا لم يفعّل النظام، نصفّر القيم
        if (!$validated['streak_enabled']) {
            $validated['streak_min_days'] = null;
            $validated['streak_max_days'] = null;
            $validated['streak_bonus_points'] = 0;
        }

        // تعيين قيم افتراضية للحقول التي لا تقبل null
        $validated['duration'] = $validated['duration'] ?? 0;
        $validated['points'] = $validated['points'] ?? 0;

        // رفع ملف الفيديو
        if ($request->hasFile('video_file')) {
            $validated['video_file'] = $request->file('video_file')->store('lessons/videos', 'public');
        }

        // رفع ملف الصوت
        if ($request->hasFile('audio_file')) {
            $validated['audio_file'] = $request->file('audio_file')->store('lessons/audio', 'public');
        }

        // رفع الصور
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('lessons/images', 'public');
            }
            $validated['images'] = $images;
        }

        // Auto-order within the same concept
        if (!$request->filled('order')) {
            $validated['order'] = Lesson::where('concept_id', $validated['concept_id'])->max('order') + 1;
        }

        Lesson::create($validated);

        return redirect()
            ->route('admin.lessons.index', ['concept_id' => $validated['concept_id']])
            ->with('success', 'تم إضافة الدرس بنجاح!');
    }

    public function show(Lesson $lesson)
    {
        $lesson->load('concept.value', 'activities');
        $activitiesCount = $lesson->activities()->count();

        return view('admin.lessons.show', compact('lesson', 'activitiesCount'));
    }

    public function edit(Lesson $lesson)
    {
        $concepts = Concept::with('value')->orderBy('order')->get();

        return view('admin.lessons.edit', compact('lesson', 'concepts'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'concept_id' => 'required|exists:concepts,id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|in:text,video,audio,mixed',
            'video_url' => 'nullable|url|max:500',
            'audio_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|mimes:mp4,mov,avi,wmv,webm|max:51200',
            'audio_file' => 'nullable|mimes:mp3,wav,ogg,m4a|max:10240',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'duration' => 'nullable|integer|min:0',
            'points' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,draft,archived',
            // حقول نظام Streak
            'streak_enabled' => 'nullable|boolean',
            'streak_min_days' => 'nullable|integer|min:1|max:30',
            'streak_max_days' => 'nullable|integer|min:1|max:60',
            'streak_bonus_points' => 'nullable|integer|min:0',
        ]);

        // معالجة checkbox الـ streak
        $validated['streak_enabled'] = $request->has('streak_enabled');
        
        // إذا لم يفعّل النظام، نصفّر القيم
        if (!$validated['streak_enabled']) {
            $validated['streak_min_days'] = null;
            $validated['streak_max_days'] = null;
            $validated['streak_bonus_points'] = 0;
        }

        // تعيين قيم افتراضية للحقول التي لا تقبل null
        $validated['duration'] = $validated['duration'] ?? 0;
        $validated['points'] = $validated['points'] ?? 0;

        // رفع ملف الفيديو الجديد
        if ($request->hasFile('video_file')) {
            // حذف الملف القديم إن وجد
            if ($lesson->video_file && Storage::disk('public')->exists($lesson->video_file)) {
                Storage::disk('public')->delete($lesson->video_file);
            }
            $validated['video_file'] = $request->file('video_file')->store('lessons/videos', 'public');
        }

        // رفع ملف الصوت الجديد
        if ($request->hasFile('audio_file')) {
            // حذف الملف القديم إن وجد
            if ($lesson->audio_file && Storage::disk('public')->exists($lesson->audio_file)) {
                Storage::disk('public')->delete($lesson->audio_file);
            }
            $validated['audio_file'] = $request->file('audio_file')->store('lessons/audio', 'public');
        }

        // رفع الصور الجديدة
        if ($request->hasFile('images')) {
            // حذف الصور القديمة إن وجدت
            if ($lesson->images && is_array($lesson->images)) {
                foreach ($lesson->images as $oldImage) {
                    if (Storage::disk('public')->exists($oldImage)) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }
            }
            
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('lessons/images', 'public');
            }
            $validated['images'] = $images;
        } else {
            // الحفاظ على الصور القديمة إذا لم يتم رفع صور جديدة
            $validated['images'] = $lesson->images;
        }

        $lesson->update($validated);

        return redirect()
            ->route('admin.lessons.index', ['concept_id' => $validated['concept_id']])
            ->with('success', 'تم تحديث الدرس بنجاح!');
    }

    public function destroy(Lesson $lesson)
    {
        // Check if lesson has activities
        if ($lesson->activities()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الدرس لوجود أنشطة مرتبطة به!');
        }

        // حذف الملفات المرتبطة
        if ($lesson->video_file && Storage::disk('public')->exists($lesson->video_file)) {
            Storage::disk('public')->delete($lesson->video_file);
        }
        
        if ($lesson->audio_file && Storage::disk('public')->exists($lesson->audio_file)) {
            Storage::disk('public')->delete($lesson->audio_file);
        }
        
        if ($lesson->images && is_array($lesson->images)) {
            foreach ($lesson->images as $image) {
                if (Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        $conceptId = $lesson->concept_id;
        $lesson->delete();

        return redirect()
            ->route('admin.lessons.index', ['concept_id' => $conceptId])
            ->with('success', 'تم حذف الدرس بنجاح!');
    }

    public function toggleStatus(Lesson $lesson)
    {
        $newStatus = $lesson->status === 'active' ? 'draft' : 'active';
        $lesson->update(['status' => $newStatus]);

        return back()->with('success', 'تم تحديث حالة الدرس بنجاح!');
    }
}

