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
        Schema::table('activities', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_activity_bank');
            $table->unsignedBigInteger('featured_by')->nullable()->after('is_featured');
            $table->timestamp('featured_at')->nullable()->after('featured_by');
            $table->text('featured_reason')->nullable()->after('featured_at');
            $table->boolean('is_family_activity')->default(false)->after('is_team_activity');

            $table->foreign('featured_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['featured_by']);
            $table->dropColumn(['is_featured', 'featured_by', 'featured_at', 'featured_reason', 'is_family_activity']);
        });
    }
};
