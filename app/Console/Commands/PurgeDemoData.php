<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * حذف حسابات الديمو وبياناتها المباشرة (نقاط/عملات/تسليمات/سلاسل/…) — أداة تنظيف تُشغَّل يدويّاً
 * بعد انتهاء دورة العروض. غير رجعيّ: يتطلّب تأكيداً (أو --force). كل حذف محروسٌ بوجود الجدول/العمود،
 * وكلّه داخل معاملة واحدة (فشلٌ ⟶ لا حذف جزئيّ). (خطّة docs/DEMO_ACCOUNTS_PLAN.md — الدفعة 8.)
 */
class PurgeDemoData extends Command
{
    protected $signature = 'demo:purge {--force : تنفيذ بلا سؤال تأكيد} {--schools : حذف مدارس الديمو أيضاً (بعد حساباتها)}';

    protected $description = 'حذف حسابات الديمو وبياناتها المباشرة (تنظيف بعد العروض).';

    public function handle(): int
    {
        $userIds = User::where('is_demo', true)->pluck('id')->all();
        $schoolCount = School::where('is_demo', true)->count();

        if (empty($userIds) && $schoolCount === 0) {
            $this->info('لا توجد حسابات أو مدارس ديمو للحذف.');

            return self::SUCCESS;
        }

        $this->warn('سيُحذف نهائيّاً: ' . count($userIds) . ' حساب ديمو'
            . ($this->option('schools') ? " و{$schoolCount} مدرسة ديمو" : '') . ' وكلّ بياناتهم المباشرة.');

        if (! $this->option('force') && ! $this->confirm('متابعة الحذف النهائيّ؟ (لا رجعة)')) {
            $this->info('أُلغيت العمليّة.');

            return self::SUCCESS;
        }

        try {
            DB::transaction(function () use ($userIds) {
                if (! empty($userIds)) {
                    // الأبناء المرتبطون بالمستخدم عبر أعمدة مختلفة (محروس بوجود الجدول/العمود)
                    $this->purge('activity_submissions', 'student_id', $userIds);
                    $this->purge('points', 'user_id', $userIds);
                    $this->purge('coins', 'user_id', $userIds);
                    $this->purge('streaks', 'user_id', $userIds);
                    $this->purge('activity_user_streaks', 'user_id', $userIds);
                    $this->purge('teacher_points', 'teacher_id', $userIds);
                    $this->purge('parent_points', 'parent_id', $userIds);
                    $this->purge('school_points', 'user_id', $userIds);
                    $this->purge('classroom_student', 'student_id', $userIds);
                    $this->purge('parent_student', 'parent_id', $userIds);
                    $this->purge('parent_student', 'student_id', $userIds);
                    $this->purge('badge_user', 'user_id', $userIds);
                    $this->purge('user_purchases', 'user_id', $userIds);
                    $this->purge('notifications', 'notifiable_id', $userIds);
                    $this->purge('notifications', 'user_id', $userIds);

                    // أخيراً المستخدمون (mass delete — لا يمرّ بحارس model events، مقبولٌ في CLI)
                    User::whereIn('id', $userIds)->delete();
                }
            });
        } catch (\Throwable $e) {
            // المعاملة تراجعت بالكامل — لا حذف جزئيّ. غالباً قيد FK لجدول لم يُدرَج في التنظيف.
            $this->error('فشل الحذف (تراجعت المعاملة بالكامل، لا حذف جزئيّ): ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('تمّ حذف ' . count($userIds) . ' حساب ديمو وبياناتهم المباشرة.');

        if ($this->option('schools')) {
            $deleted = School::where('is_demo', true)->delete();
            $this->info("تمّ حذف {$deleted} مدرسة ديمو.");
        }

        return self::SUCCESS;
    }

    /**
     * حذف صفوف جدولٍ بالمعرّفات — فقط إن وُجد الجدول والعمود.
     *
     * @param  array<int, int>  $ids
     */
    private function purge(string $table, string $column, array $ids): void
    {
        if (empty($ids) || ! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)->whereIn($column, $ids)->delete();
    }
}
