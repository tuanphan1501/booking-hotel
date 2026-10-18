<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `room_types` table (Loại phòng / 客室タイプ).
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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('hotel_id');
            $table->string('code', 20);
            $table->string('name', 200);
            $table->string('name_ja', 200)->nullable();
            $table->text('description')->nullable();
            $table->text('description_ja')->nullable();
            $table->decimal('area_m2', 6, 1)->nullable();
            $table->string('bed_type', 20)->nullable();
            $table->smallInteger('max_adults');
            $table->smallInteger('max_children')->default(0);
            $table->string('view_type', 20)->nullable();
            $table->boolean('smoking')->default(false);
            $table->decimal('base_price', 15, 0);
            $table->smallInteger('sort_order')->default(0);
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletesTz();
            $table->comment('Loại phòng / 客室タイプ');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE UNIQUE INDEX ux_room_types_1 ON room_types (hotel_id, code) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
