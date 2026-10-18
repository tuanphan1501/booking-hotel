<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `promotion_room_types` table (Loại phòng áp dụng KM / プロモーション対象客室).
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
        Schema::create('promotion_room_types', function (Blueprint $table) {
            $table->bigInteger('promotion_id');
            $table->bigInteger('room_type_id');
            $table->primary(['promotion_id', 'room_type_id']);
            $table->comment('Loại phòng áp dụng KM / プロモーション対象客室');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_room_types');
    }
};
