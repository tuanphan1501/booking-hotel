<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `favorites` table (Yêu thích / お気に入り).
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
        Schema::create('favorites', function (Blueprint $table) {
            $table->bigInteger('user_id');
            $table->bigInteger('hotel_id');
            $table->timestampTz('created_at')->useCurrent();
            $table->primary(['user_id', 'hotel_id']);
            $table->comment('Yêu thích / お気に入り');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
