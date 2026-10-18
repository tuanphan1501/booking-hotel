<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `room_inventories` table (Tồn kho & giá theo ngày / 日別在庫・料金).
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
        Schema::create('room_inventories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('room_type_id');
            $table->date('stay_date');
            $table->smallInteger('total_rooms');
            $table->smallInteger('booked_rooms')->default(0);
            $table->smallInteger('blocked_rooms')->default(0);
            $table->decimal('price', 15, 0)->nullable();
            $table->boolean('stop_sell')->default(false);
            $table->integer('version')->default(0);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->unique(['room_type_id', 'stay_date'], 'ux_room_inventories_1');
            $table->comment('Tồn kho & giá theo ngày / 日別在庫・料金');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('ALTER TABLE room_inventories ADD CONSTRAINT ck_room_inventories_2 CHECK (booked_rooms + blocked_rooms <= total_rooms)');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('room_inventories');
    }
};
