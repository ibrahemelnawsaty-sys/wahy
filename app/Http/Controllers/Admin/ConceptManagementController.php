<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Concept;
use App\Models\Value;

class ConceptManagementController extends Controller
{
    /**
     * عرض قائمة المفاهيم
     */
    public function index(Request $request)
    {
        $query = Concept::with('value', 'lessons');
        
        // فلترة حسب القيمة
        if ($request->filled('value_id')) {
            $query->where('value_id', $request->value_id);
        }
        
        // بحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $concepts = $query->orderBy('order')->paginate(20);
        $values = Value::where('status', 'active')->orderBy('order')->get();
        
        return view('admin.concepts.index', compact('concepts', 'values'));
    }

    /**
     * عرض صفحة إضافة مفهوم
     */
    public function create(Request $request)
    {
        $values = Value::where('status', 'active')->orderBy('order')->get();
        $selectedValue = $request->value_id;
        
        return view('admin.concepts.create', compact('values', 'selectedValue'));
    }

    /**
     * حفظ مفهوم جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'value_id' => 'required|exists:values,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ]);

        // إذا لم يتم تحديد الترتيب، خليه آخر واحد في نفس القيمة
        if (!$request->filled('order')) {
            $validated['order'] = Concept::where('value_id', $validated['value_id'])->max('order') + 1;
        }

        Concept::create($validated);

        return redirect()
            ->route('admin.concepts.index', ['value_id' => $validated['value_id']])
            ->with('success', 'تم إضافة المفهوم بنجاح! ✅');
    }

    /**
     * عرض تفاصيل المفهوم مع المعاني
     */
    public function show(Concept $concept)
    {
        $concept->load(['value', 'lessons']);
        $lessonsCount = $concept->lessons()->count();

        return view('admin.concepts.show', compact('concept', 'lessonsCount'));
    }

    /**
     * عرض صفحة تعديل مفهوم
     */
    public function edit(Concept $concept)
    {
        $values = Value::where('status', 'active')->orderBy('order')->get();
        return view('admin.concepts.edit', compact('concept', 'values'));
    }

    /**
     * تحديث بيانات المفهوم
     */
    public function update(Request $request, Concept $concept)
    {
        $validated = $request->validate([
            'value_id' => 'required|exists:values,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ]);

        $concept->update($validated);

        return redirect()
            ->route('admin.concepts.index', ['value_id' => $validated['value_id']])
            ->with('success', 'تم تحديث المفهوم بنجاح! ✅');
    }

    /**
     * حذف مفهوم
     */
    public function destroy(Concept $concept)
    {
        // التحقق من وجود معاني مرتبطة
        if ($concept->lessons()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف المفهوم لوجود دروس مرتبطة به!');
        }

        $valueId = $concept->value_id;
        $concept->delete();

        return redirect()
            ->route('admin.concepts.index', ['value_id' => $valueId])
            ->with('success', 'تم حذف المفهوم بنجاح! 🗑️');
    }
}

