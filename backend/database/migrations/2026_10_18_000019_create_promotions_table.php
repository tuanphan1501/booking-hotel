<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `promotions` table (Khuyến mãi tự động / 自動適用プロモーション).
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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('hotel_id')->nullable();
            $table->string('name', 200);
            $table->string('name_ja', 200)->nullable();
            $table->string('type', 20);
            $table->decimal('discount_percent', 5, 2);
            $table->smallInteger('min_nights')->nullable();
            $table->smallInteger('min_advance_days')->nullable();
            $table->smallInteger('max_advance_days')->nullable();
            $table->date('stay_start_date');
            $table->date('stay_end_date');
            $table->timestampTz('booking_start_at')->nullable();
            $table->timestampTz('booking_end_at')->nullable();
            $table->smallInteger('priority')->default(0);
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletesTz();
            $table->index(['hotel_id', 'status', 'stay_start_date', 'stay_end_date'], 'ix_promotions_1');
            $table->comment('Khuyến mãi tự động / 自動適用プロモーション');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
