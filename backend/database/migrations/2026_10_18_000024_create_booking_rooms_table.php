<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `booking_rooms` table (Phòng trong đặt phòng / 予約客室).
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
        Schema::create('booking_rooms', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('booking_id');
            $table->bigInteger('room_type_id');
            $table->bigInteger('rate_plan_id');
            $table->bigInteger('room_id')->nullable();
            $table->smallInteger('adults');
            $table->smallInteger('children')->default(0);
            $table->decimal('subtotal', 15, 0);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->index(['booking_id'], 'ix_booking_rooms_1');
            $table->index(['room_id'], 'ix_booking_rooms_2');
            $table->comment('Phòng trong đặt phòng / 予約客室');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_rooms');
    }
};
