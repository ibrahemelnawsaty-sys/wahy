<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pass-4 Batch 2 — the idempotency backbone of the central economy primitive.
 *
 * award_ledger records one row per logical award event, keyed uniquely by
 * (user_id, source_type, source_id). AwardService::award() does an
 * insertOrIgnore against this table; a 0 (duplicate) short-circuits the whole
 * award, making double-awards STRUCTURALLY impossible at the DB layer regardless
 * of any application-level guard. Additive only (no existing table touched);
 * fully reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('award_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source_type', 64);
            $table->string('source_id', 64);
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('coins')->default(0);
            $table->timestamps();

            // The real guarantee: one award per (user, event). Double-award => insert ignored.
            $table->unique(['user_id', 'source_type', 'source_id'], 'award_ledger_event_unique');
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('award_ledger');
    }
};
