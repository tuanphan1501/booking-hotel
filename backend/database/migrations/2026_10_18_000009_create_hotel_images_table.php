<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `hotel_images` table (Ảnh khách sạn / ホテル画像).
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
        Schema::create('hotel_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('hotel_id');
            $table->string('url', 500);
            $table->string('caption', 200)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->index(['hotel_id', 'sort_order'], 'ix_hotel_images_1');
            $table->comment('Ảnh khách sạn / ホテル画像');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_images');
    }
};
