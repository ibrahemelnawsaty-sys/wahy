<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة حالة صريحة (pending/approved/rejected) وسبب الرفض للأنشطة العائلية.
 * كان النموذج يحوي boolean parent_approved فقط، فلا يمكن التمييز بين "معلّق" و"مرفوض"،
 * ما جعل الرفض مستحيلاً (Issue حرج: الرفض كان يؤدي للموافقة ومنح النقاط).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('family_activity_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('family_activity_submissions', 'status')) {
                $table->string('status', 20)->default('pending')->after('parent_approved');
            }
            if (!Schema::hasColumn('family_activity_submissions', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('parent_praise');
            }
        });

        // مواءمة الصفوف القائمة: المعتمدة سابقاً تأخذ status=approved
        if (Schema::hasColumn('family_activity_submissions', 'status')) {
            \Illuminate\Support\Facades\DB::table('family_activity_submissions')
                ->where('parent_approved', true)
                ->update(['status' => 'approved']);
        }
    }

    public function down(): void
    {
        Schema::table('family_activity_submissions', function (Blueprint $table) {
            foreach (['status', 'rejection_reason'] as $col) {
                if (Schema::hasColumn('family_activity_submissions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
