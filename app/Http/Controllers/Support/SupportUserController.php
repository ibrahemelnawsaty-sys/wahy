<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * صلاحيات الدعم الفنيّ على حسابات المستخدمين — عرض/بحث/تعديل محدود/إعادة كلمة مرور/تفعيل-تعطيل.
 *
 * قيود المالك المُلزِمة:
 *  - لا مساس بحسابات super_admin إطلاقاً (كل فعل يبدأ بـ abort_if super_admin).
 *  - ممنوع تغيير role أو school_id.
 *  - الحقول المحروسة (status / password_change_required) عبر forceFill()->saveQuietly() لتجاوز حارس User::booted بشكل مقصود ومحصور.
 */
class SupportUserController extends Controller
{
    /**
     * قائمة كل المستخدمين مع بحث/فلترة.
     */
    public function index(Request $request)
    {
        $query = User::with('school');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('support.users.index', compact('users'));
    }

    /**
     * نموذج تعديل بيانات مستخدم (الاسم/البريد/الهاتف فقط).
     */
    public function edit(User $user)
    {
        abort_if($user->hasSuperAdminRole(), 403);

        return view('support.users.edit', compact('user'));
    }

    /**
     * تحديث الاسم/البريد/الهاتف فقط — حقول غير محروسة فيكفي update عاديّ.
     * ممنوع أيّ تعديل على role أو school_id.
     */
    public function update(Request $request, User $user)
    {
        abort_if($user->hasSuperAdminRole(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()
            ->route('support.users.index')
            ->with('success', 'تم تحديث بيانات المستخدم. ✅');
    }

    /**
     * إعادة تعيين كلمة المرور (+ خيار إجبار التغيير بالدخول التالي).
     * password_change_required محروس → forceFill/saveQuietly.
     */
    public function resetPassword(Request $request, User $user)
    {
        abort_if($user->hasSuperAdminRole(), 403);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'force' => 'nullable|boolean',
        ]);

        $force = $request->boolean('force');

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'password_change_required' => $force,
        ])->saveQuietly();

        // saveQuietly يتخطّى مستمعي الموديل (ومنهم سجلّ النشاط) — نسجّل يدوياً كضابط تعويضيّ
        // ليبقى فعل الدعم على حساب مستخدم قابلاً للتتبّع/المساءلة.
        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['forced' => $force])
            ->log('support_reset_password');

        NotificationService::send(
            $user->id,
            'تم تغيير كلمة مرورك',
            'قام فريق الدعم الفنيّ بإعادة تعيين كلمة مرورك. إن لم تطلب ذلك تواصل معنا فوراً.',
            'account',
            route('tickets.index'),
        );

        return redirect()
            ->route('support.users.index')
            ->with('success', 'تم إعادة تعيين كلمة المرور. ✅');
    }

    /**
     * تفعيل/تعطيل الحساب — status محروس → forceFill/saveQuietly.
     */
    public function toggleStatus(User $user)
    {
        abort_if($user->hasSuperAdminRole(), 403);

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';

        $user->forceFill(['status' => $newStatus])->saveQuietly();

        // saveQuietly يتخطّى سجلّ النشاط — نسجّل تغيير الحالة يدوياً للمساءلة.
        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['status' => $newStatus])
            ->log('support_toggle_status');

        $label = $newStatus === 'active' ? 'تفعيل' : 'تعطيل';

        return redirect()
            ->route('support.users.index')
            ->with('success', "تم {$label} الحساب. ✅");
    }
}
