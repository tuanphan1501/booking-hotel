<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `rate_plans` table (Gói giá / 料金プラン).
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
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('room_type_id');
            $table->bigInteger('cancellation_policy_id');
            $table->string('code', 20);
            $table->string('name', 200);
            $table->string('name_ja', 200)->nullable();
            $table->boolean('breakfast_included')->default(false);
            $table->string('price_adjust_type', 10)->default('NONE');
            $table->decimal('price_adjust_value', 15, 2)->default(0);
            $table->smallInteger('min_nights')->default(1);
            $table->smallInteger('advance_days_min')->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletesTz();
            $table->comment('Gói giá / 料金プラン');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE UNIQUE INDEX ux_rate_plans_1 ON rate_plans (room_type_id, code) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_plans');
    }
};
