<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `point_transactions` table (Giao dịch điểm / ポイント履歴).
 *
 * Source of truth: HotelBooking_DatabaseDesign.xlsx. Foreign keys are added in
 * 2026_10_18_000040_add_foreign_keys.php so tables can be created in any order.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
return new class extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('booking_id')->nullable();
            $table->string('type', 20);
            $table->integer('points');
            $table->integer('balance_after');
            $table->integer('remaining')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('note', 500)->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->comment('Giao dịch điểm / ポイント履歴');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE INDEX ix_point_transactions_1 ON point_transactions (user_id, created_at DESC)');
        DB::statement('CREATE INDEX ix_point_transactions_2 ON point_transactions (expires_at) WHERE remaining > 0');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
