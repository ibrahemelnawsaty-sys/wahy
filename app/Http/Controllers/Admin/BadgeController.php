<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BadgeController extends Controller
{
    /**
     * قواعد التحقّق المشتركة (إنشاء/تحديث).
     */
    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            // نستبعد svg: ملفّ SVG يُقدَّم على نفس الأصل وقد يحمل JS (stored-XSS عند فتح الرابط مباشرة).
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'condition_type' => 'required|in:'.implode(',', array_keys(Badge::CONDITION_TYPES)),
            // min:1 — القيمة 0 تمنح الشارة لكل طالب فوراً (current >= 0 دائماً صحيح).
            'condition_value' => 'required|integer|min:1',
            'coins_reward' => 'required|integer|min:0',
            // لون hex صالح فقط (كما يُنتجه منتقي الألوان) — يمنع حقن قيمة CSS/كسر العرض.
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'type' => 'required|in:achievement,streak,special',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * رسائل تحقّق عربية مخصّصة.
     */
    private function messages(): array
    {
        return [
            'color.regex' => 'صيغة اللون غير صحيحة — استخدم قيمة hex مثل ‎#667eea.',
            'condition_value.min' => 'قيمة الشرط يجب أن تكون 1 على الأقل.',
        ];
    }

    /**
     * عرض قائمة الشارات.
     */
    public function index(Request $request)
    {
        $query = Badge::withCount('users');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('condition_type')) {
            $query->where('condition_type', $request->condition_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $badges = $query->orderBy('order')->orderBy('id')->paginate(20)->withQueryString();

        $conditionTypes = Badge::CONDITION_TYPES;

        return view('admin.badges.index', compact('badges', 'conditionTypes'));
    }

    /**
     * عرض صفحة إضافة شارة.
     */
    public function create()
    {
        $conditionTypes = Badge::CONDITION_TYPES;

        return view('admin.badges.create', compact('conditionTypes'));
    }

    /**
     * حفظ شارة جديدة.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        // الصورة عمود خارج $fillable — تُعالج بالإسناد المباشر بعد الإنشاء.
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('badges', 'public');
        }
        unset($validated['image']);

        if (! $request->filled('order')) {
            $validated['order'] = (int) Badge::max('order') + 1;
        }

        $badge = Badge::create($validated);

        if ($imagePath !== null) {
            $badge->image = $imagePath;
            $badge->save();
        }

        return redirect()
            ->route('admin.badges.index')
            ->with('success', 'تم إضافة الشارة بنجاح! ✅');
    }

    /**
     * عرض تفاصيل الشارة.
     */
    public function show(Badge $badge)
    {
        $badge->loadCount('users');

        return view('admin.badges.show', compact('badge'));
    }

    /**
     * عرض صفحة تعديل الشارة.
     */
    public function edit(Badge $badge)
    {
        $conditionTypes = Badge::CONDITION_TYPES;

        return view('admin.badges.edit', compact('badge', 'conditionTypes'));
    }

    /**
     * تحديث بيانات الشارة.
     */
    public function update(Request $request, Badge $badge)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $newImagePath = null;
        if ($request->hasFile('image')) {
            if ($badge->image && Storage::disk('public')->exists($badge->image)) {
                Storage::disk('public')->delete($badge->image);
            }
            $newImagePath = $request->file('image')->store('badges', 'public');
        }
        unset($validated['image']);

        $badge->update($validated);

        // الصورة عمود خارج $fillable — تُعالج بالإسناد المباشر.
        if ($newImagePath !== null) {
            $badge->image = $newImagePath;
            $badge->save();
        }

        return redirect()
            ->route('admin.badges.index')
            ->with('success', 'تم تحديث الشارة بنجاح! ✅');
    }

    /**
     * حذف شارة (محميّ إن كانت مكتسبة).
     */
    public function destroy(Badge $badge)
    {
        if ($badge->users()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الشارة لأنّ طلّاباً اكتسبوها بالفعل! ❌');
        }

        if ($badge->image && Storage::disk('public')->exists($badge->image)) {
            Storage::disk('public')->delete($badge->image);
        }

        $badge->delete();

        return redirect()
            ->route('admin.badges.index')
            ->with('success', 'تم حذف الشارة بنجاح! 🗑️');
    }

    /**
     * تغيير حالة الشارة (نشط/غير نشط).
     */
    public function toggleStatus(Badge $badge)
    {
        $newStatus = $badge->status === 'active' ? 'inactive' : 'active';
        $badge->update(['status' => $newStatus]);

        return back()->with('success', 'تم تغيير حالة الشارة! ✅');
    }
}
