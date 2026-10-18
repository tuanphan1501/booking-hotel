<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `cancellation_policies` table (Chính sách hủy / キャンセルポリシー).
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
        Schema::create('cancellation_policies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('hotel_id');
            $table->string('name', 200);
            $table->boolean('is_refundable')->default(true);
            $table->integer('free_cancel_hours')->default(48);
            $table->decimal('penalty_percent', 5, 2)->default(100);
            $table->decimal('no_show_percent', 5, 2)->default(100);
            $table->text('description')->nullable();
            $table->text('description_ja')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletesTz();
            $table->comment('Chính sách hủy / キャンセルポリシー');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('cancellation_policies');
    }
};
