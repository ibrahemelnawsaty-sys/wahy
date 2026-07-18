<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * يضيف مرحلة اعتماد مدير المدرسة قبل اعتماد السوبر أدمن.
     * الافتراضي approved يحمي كل الأنشطة القائمة وأنشطة الأدمن فتبقى مرئية.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('activities', 'school_approval_status')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->enum('school_approval_status', ['pending', 'approved', 'rejected'])->default('approved')->after('rejection_reason');
                $table->foreignId('school_approved_by')->nullable()->after('school_approval_status')->constrained('users')->nullOnDelete();
                $table->timestamp('school_approved_at')->nullable()->after('school_approved_by');
                $table->text('school_rejection_reason')->nullable()->after('school_approved_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_approved_by');
            $table->dropColumn(['school_approval_status', 'school_approved_at', 'school_rejection_reason']);
        });
    }
};
