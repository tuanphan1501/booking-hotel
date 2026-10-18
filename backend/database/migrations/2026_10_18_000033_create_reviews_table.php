<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `reviews` table (Đánh giá / レビュー).
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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('booking_id');
            $table->bigInteger('user_id');
            $table->bigInteger('hotel_id');
            $table->smallInteger('rating_overall');
            $table->smallInteger('rating_cleanliness')->nullable();
            $table->smallInteger('rating_service')->nullable();
            $table->smallInteger('rating_location')->nullable();
            $table->smallInteger('rating_value')->nullable();
            $table->text('comment')->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->text('reply')->nullable();
            $table->bigInteger('replied_by')->nullable();
            $table->timestampTz('replied_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->softDeletesTz();
            $table->unique(['booking_id'], 'ux_reviews_booking_id');
            $table->comment('Đánh giá / レビュー');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE INDEX ix_reviews_1 ON reviews (hotel_id, status, created_at DESC)');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
