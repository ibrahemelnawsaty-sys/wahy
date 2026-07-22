<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إصلاح بيانات لمرّة واحدة (issue #18):
     *
     * مستخدمون سجّلوا عبر رابط/باركود المدرسة واختاروا كلمة مرورهم بأنفسهم (مُخزَّنة مُجزّأة في
     * registration_requests.password)، لكنّهم أُنشئوا قبل إصلاح SchoolAdminController::approveRequest
     * — فأُعطوا كلمة مرور «مؤقتة» وأُجبِروا على تغييرها عند الدخول رغم أنّهم هم من وضعها.
     * نُعيد لهؤلاء كلمة مرورهم المختارة ونُلغي علَم الإجبار.
     *
     * ★ تضييق أمنيّ حرِج: علَم password_change_required=true ليس حكراً على هذا العطل — يضبطه أيضاً
     * إعادة تعيين الدعم (SupportUserController) مع كلمة مرور جديدة شرعيّة. فنقصُر الاسترجاع على من
     * **لم يُعدَّل بعد اعتماده** (users.updated_at ضمن 10 دقائق من approved_at): المعالجة الفوريّة بعد
     * الاعتماد خلال ثوانٍ، وإعادة تعيين الدعم/تعديل الأدمن اللاحق بعد ساعات/أيام فيُستبعَد — فلا
     * نُبطِل كلمة مرور شرعيّة جديدة أبداً (الاتّجاه آمن دائماً).
     *
     * ⚠️ مقايضة: من عُدِّل صفّه بعد الاعتماد لسبب حميد (دخول بـ«تذكّرني» يكتب remember_token، تعديل
     * أدمن…) يُستبعَد أيضاً ويبقى مُجبَراً — لكنّه غير مقفول (يُتمّ التغيير المفروض بالكلمة المُرسَلة
     * إليه، والمسار يعمل بعد إصلاح الـ403). نُسجّل هؤلاء المتخطَّين ليُعالَجهم المالك يدوياً إن لزم.
     *
     * المطابقة بـuser_id (يُملأ عند الاعتماد) وإلا بالبريد. كتابة خام بـDB (تتجاوز حارس User::booted
     * ولا تُعيد تجزئة القيمة المجزّأة — cast hashed). idempotent. لا تراجع.
     */
    public function up(): void
    {
        if (! Schema::hasTable('registration_requests')
            || ! Schema::hasColumn('users', 'password_change_required')) {
            return;
        }

        $requests = DB::table('registration_requests')
            ->where('status', 'approved')
            ->whereNotNull('approved_at')
            ->whereNotNull('password')
            ->where('password', '!=', '')
            ->get(['user_id', 'email', 'password', 'approved_at']);

        $restored = 0;
        $skipped = [];

        foreach ($requests as $req) {
            $userQuery = DB::table('users')->where('password_change_required', true);

            if (! empty($req->user_id)) {
                $userQuery->where('id', $req->user_id);
            } else {
                $userQuery->where('email', $req->email);
            }

            $user = (clone $userQuery)->first(['id', 'email', 'updated_at']);
            if (! $user) {
                continue; // لا مستخدم ما زال مُجبَراً يطابق هذا الطلب
            }

            $cutoff = Carbon::parse($req->approved_at)->addMinutes(10);
            $touchedAfterApproval = $user->updated_at !== null
                && Carbon::parse($user->updated_at)->greaterThan($cutoff);

            if ($touchedAfterApproval) {
                // عُدِّل بعد الاعتماد — لا نُخاطر بإبطال كلمة قد تكون شرعيّة جديدة. نسجّله فقط.
                $skipped[] = $user->email;

                continue;
            }

            DB::table('users')->where('id', $user->id)->update([
                'password' => $req->password,          // كلمة المستخدم المختارة (مُجزّأة already)
                'password_change_required' => false,   // لا إجبار — هو من اختارها
            ]);
            $restored++;
        }

        if ($restored > 0 || ! empty($skipped)) {
            Log::info("issue#18: استُرجعت كلمة المرور المختارة لـ{$restored} مستخدم(ين).");
        }
        if (! empty($skipped)) {
            Log::warning(
                'issue#18: مستخدمون مسجَّلون ذاتياً ما زالوا مُجبَرين على تغيير كلمة المرور لكن عُدِّل '
                . 'صفّهم بعد الاعتماد فتُخُطُّوا في الاسترجاع الآليّ (عالِجهم يدوياً إن لزم — يمكنهم أيضاً '
                . 'إتمام التغيير المفروض بالكلمة المُرسَلة إليهم): ' . implode(', ', $skipped)
            );
        }
    }

    public function down(): void
    {
        // لا تراجع: لن نُعيد فرض تغيير كلمة المرور على مستخدم اختار كلمته بنفسه.
    }
};
