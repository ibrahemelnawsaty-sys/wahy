<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Value;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ValueManagementController extends Controller
{
    /**
     * عرض قائمة القيم
     */
    public function index(Request $request)
    {
        $query = Value::with('creator', 'concepts');
        
        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // بحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $values = $query->orderBy('order')->paginate(20);
        
        return view('admin.values.index', compact('values'));
    }

    /**
     * عرض صفحة إضافة قيمة
     */
    public function create()
    {
        return view('admin.values.create');
    }

    /**
     * حفظ قيمة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['created_by'] = Auth::id();
        
        // رفع الصورة إذا تم إرسالها
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('values', 'public');
        }
        
        // إذا لم يتم تحديد الترتيب، خليه آخر واحد
        if (!$request->filled('order')) {
            $validated['order'] = Value::max('order') + 1;
        }

        Value::create($validated);

        return redirect()
            ->route('admin.values.index')
            ->with('success', 'تم إضافة القيمة بنجاح! ✅');
    }

    /**
     * عرض تفاصيل القيمة مع المفاهيم
     */
    public function show(Value $value)
    {
        $value->load(['concepts.lessons', 'creator']);
        $conceptsCount = $value->concepts()->count();
        $lessonsCount = $value->concepts()->withCount('lessons')->get()->sum('lessons_count');
        
        return view('admin.values.show', compact('value', 'conceptsCount', 'lessonsCount'));
    }

    /**
     * عرض صفحة تعديل قيمة
     */
    public function edit(Value $value)
    {
        return view('admin.values.edit', compact('value'));
    }

    /**
     * تحديث بيانات القيمة
     */
    public function update(Request $request, Value $value)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        // رفع الصورة الجديدة إذا تم إرسالها
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إن وجدت
            if ($value->image && Storage::disk('public')->exists($value->image)) {
                Storage::disk('public')->delete($value->image);
            }
            $validated['image'] = $request->file('image')->store('values', 'public');
        }

        $value->update($validated);

        return redirect()
            ->route('admin.values.index')
            ->with('success', 'تم تحديث القيمة بنجاح! ✅');
    }

    /**
     * حذف قيمة
     */
    public function destroy(Value $value)
    {
        // التحقق من وجود مفاهيم مرتبطة
        if ($value->concepts()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف القيمة لوجود مفاهيم مرتبطة بها! ❌');
        }

        // حذف الصورة المرتبطة إن وجدت
        if ($value->image && Storage::disk('public')->exists($value->image)) {
            Storage::disk('public')->delete($value->image);
        }

        $value->delete();

        return redirect()
            ->route('admin.values.index')
            ->with('success', 'تم حذف القيمة بنجاح! 🗑️');
    }

    /**
     * تغيير حالة القيمة
     */
    public function toggleStatus(Value $value)
    {
        $newStatus = $value->status === 'active' ? 'inactive' : 'active';
        $value->update(['status' => $newStatus]);

        return back()->with('success', 'تم تغيير حالة القيمة! ✅');
    }
}

