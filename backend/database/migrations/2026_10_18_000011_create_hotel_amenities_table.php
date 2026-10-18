<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `hotel_amenities` table (Tiện nghi khách sạn / ホテル設備).
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
        Schema::create('hotel_amenities', function (Blueprint $table) {
            $table->bigInteger('hotel_id');
            $table->bigInteger('amenity_id');
            $table->primary(['hotel_id', 'amenity_id']);
            $table->comment('Tiện nghi khách sạn / ホテル設備');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_amenities');
    }
};
