<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * @group Authentication
 *
 * مسارات تسجيل الدخول والخروج وإدارة الحساب للتطبيق المحمول.
 */
class AuthApiController extends Controller
{
    /**
     * تسجيل الدخول (Login).
     *
     * يرجع Bearer Token عند نجاح المصادقة. استخدم التوكن في Header
     * `Authorization: Bearer {token}` لكل الطلبات المحمية بعدها.
     *
     * @unauthenticated
     *
     * @bodyParam email string required البريد الإلكتروني. Example: student@example.com
     * @bodyParam password string required كلمة المرور (6+ حروف). Example: password123
     *
     * @response 200 {
     *   "success": true,
     *   "message": "تم تسجيل الدخول بنجاح",
     *   "data": {
     *     "token": "1|abcdef1234567890",
     *     "user": {
     *       "id": 1,
     *       "name": "أحمد محمد",
     *       "email": "student@example.com",
     *       "role": "student",
     *       "avatar": null,
     *       "school_id": 5
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "البريد الإلكتروني أو كلمة المرور غير صحيحة"
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "حسابك غير مفعل. يرجى التواصل مع الإدارة"
     * }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // S5 — حد المعدّل: 5 محاولات/دقيقة لكل (ip|hash(email)) لمنع التخمين دون حظر المستخدم الشرعي
        $throttleKey = 'api-login:' . $request->ip() . '|' . hash('sha256', mb_strtolower((string) $request->email));

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'عدد كبير من المحاولات. يرجى المحاولة بعد قليل',
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return response()->json([
                'success' => false,
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ], 401);
        }

        // Check if account is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'حسابك غير مفعل. يرجى التواصل مع الإدارة',
            ], 403);
        }

        // كلمة المرور صحيحة والحساب نشط: تصفير عدّاد المحاولات
        RateLimiter::clear($throttleKey);

        // S6 — مطابقة تدفّق 2FA للويب: لا تُصدر التوكن قبل التحقق من الكود
        if ($user->two_factor_enabled && setting('enable_2fa', true)) {
            $code = (string) random_int(100000, 999999);

            $user->two_factor_code = $code;
            $user->two_factor_expires_at = Carbon::now()->addMinutes(10);
            $user->save();

            try {
                Mail::to($user->email)->queue(new TwoFactorCodeMail($code, $user->name));
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ في إرسال كود التحقق. يرجى المحاولة لاحقاً',
                ], 500);
            }

            return response()->json([
                'success' => false,
                'code' => '2fa_required',
                'message' => 'تم إرسال كود التحقق إلى بريدك الإلكتروني',
                'data' => [
                    'user_id' => $user->id,
                ],
            ], 200);
        }

        // Create token (مستخدم بلا 2FA: خطوة واحدة)
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'school_id' => $user->school_id,
                ],
            ],
        ], 200);
    }

    /**
     * التحقق من كود 2FA وإصدار التوكن (Verify Two-Factor).
     *
     * يُستدعى بعد ردّ `2fa_required` من /login. يتحقق من الكود وانتهاء صلاحيته
     * وعندها فقط يُصدر Bearer Token.
     *
     * @unauthenticated
     *
     * @bodyParam user_id integer required معرّف المستخدم المُعاد من /login. Example: 1
     * @bodyParam code string required كود التحقق المكوّن من 6 أرقام. Example: 123456
     *
     * @response 200 {"success": true, "message": "تم تسجيل الدخول بنجاح", "data": {"token": "1|abc", "user": {}}}
     * @response 401 {"success": false, "message": "كود التحقق غير صحيح"}
     * @response 429 {"success": false, "message": "عدد كبير من المحاولات. يرجى المحاولة بعد قليل"}
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'code' => 'required|digits:6',
        ]);

        // حد المعدّل لمنع تخمين الكود: 5 محاولات/دقيقة لكل (ip|user_id)
        $throttleKey = 'api-2fa:' . $request->ip() . '|' . $request->user_id;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'عدد كبير من المحاولات. يرجى المحاولة بعد قليل',
            ], 429);
        }

        $user = User::find($request->user_id);

        // مستخدم غير صالح أو لا ينتظر تحققاً: ردّ عام دون كشف
        if (! $user || ! $user->two_factor_enabled || $user->two_factor_code === null) {
            RateLimiter::hit($throttleKey, 60);

            return response()->json([
                'success' => false,
                'message' => 'كود التحقق غير صحيح أو منتهي الصلاحية',
            ], 401);
        }

        // انتهاء صلاحية الكود
        if ($user->two_factor_expires_at === null || Carbon::now()->greaterThan($user->two_factor_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'انتهت صلاحية كود التحقق. يرجى تسجيل الدخول مرة أخرى',
            ], 401);
        }

        // مقارنة آمنة للكود
        if (! hash_equals((string) $user->two_factor_code, (string) $request->code)) {
            RateLimiter::hit($throttleKey, 60);

            return response()->json([
                'success' => false,
                'message' => 'كود التحقق غير صحيح',
            ], 401);
        }

        // نجاح: مسح الكود وعدّاد المحاولات ثم إصدار التوكن
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        RateLimiter::clear($throttleKey);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'school_id' => $user->school_id,
                ],
            ],
        ], 200);
    }

    /**
     * تسجيل الخروج (Logout).
     *
     * يحذف الـ token الحالي فقط (لا يؤثر على tokens أخرى للمستخدم).
     *
     * @authenticated
     *
     * @response 200 {"success": true, "message": "تم تسجيل الخروج بنجاح"}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ], 200);
    }

    /**
     * Get User Profile
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load(['school', 'classrooms']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'school' => $user->school ? [
                    'id' => $user->school->id,
                    'name' => $user->school->name,
                    'logo' => $user->school->logo,
                ] : null,
                'classrooms' => $user->classrooms->map(function ($classroom) {
                    return [
                        'id' => $classroom->id,
                        'name' => $classroom->name,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'avatar' => 'sometimes|image|max:2048',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'data' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
            ],
        ], 200);
    }

    /**
     * Change Password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور الحالية غير صحيحة',
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        // S7 — إبطال كل التوكنات الأخرى مع إبقاء التوكن الحالي حتى لا يُطرد المستخدم أثناء الطلب
        $user->tokens()
            ->where('id', '!=', $request->user()->currentAccessToken()->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح',
        ], 200);
    }
}
