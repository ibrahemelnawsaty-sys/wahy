<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // نقاط المدارس
        if (!Schema::hasTable('school_points')) {
            Schema::create('school_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
                $table->integer('points')->default(0);
                $table->string('source'); // student_activity, teacher_activity, etc.
                $table->string('description')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                
                $table->index(['school_id', 'created_at']);
            });
        }

        // إضافة عمود إجمالي النقاط للمدارس
        if (Schema::hasTable('schools') && !Schema::hasColumn('schools', 'total_points')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->integer('total_points')->default(0)->after('status');
                $table->integer('weekly_points')->default(0)->after('total_points');
                $table->integer('monthly_points')->default(0)->after('weekly_points');
            });
        }

        // إضافة أعمدة للمستخدمين
        if (!Schema::hasColumn('users', 'total_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('total_points')->default(0);
                $table->integer('weekly_points')->default(0)->after('total_points');
                $table->integer('monthly_points')->default(0)->after('weekly_points');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_points');
        
        if (Schema::hasColumn('schools', 'total_points')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn(['total_points', 'weekly_points', 'monthly_points']);
            });
        }

        if (Schema::hasColumn('users', 'total_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['total_points', 'weekly_points', 'monthly_points']);
            });
        }
    }
};
