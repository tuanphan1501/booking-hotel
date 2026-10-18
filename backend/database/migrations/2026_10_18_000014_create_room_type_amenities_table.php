<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `room_type_amenities` table (Tiện nghi loại phòng / 客室タイプ設備).
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
        Schema::create('room_type_amenities', function (Blueprint $table) {
            $table->bigInteger('room_type_id');
            $table->bigInteger('amenity_id');
            $table->primary(['room_type_id', 'amenity_id']);
            $table->comment('Tiện nghi loại phòng / 客室タイプ設備');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('room_type_amenities');
    }
};
