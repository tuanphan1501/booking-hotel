<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `booking_room_nights` table (Giá từng đêm / 泊別料金).
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
        Schema::create('booking_room_nights', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('booking_room_id');
            $table->date('stay_date');
            $table->decimal('price', 15, 0);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->unique(['booking_room_id', 'stay_date'], 'ux_booking_room_nights_1');
            $table->comment('Giá từng đêm / 泊別料金');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_room_nights');
    }
};
