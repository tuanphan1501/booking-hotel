<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `hotels` table (Khách sạn (chi nhánh) / ホテル（支店）).
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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->string('name', 200);
            $table->string('name_ja', 200)->nullable();
            $table->text('description')->nullable();
            $table->text('description_ja')->nullable();
            $table->smallInteger('star_rating')->nullable();
            $table->string('address', 500);
            $table->string('city', 100);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->time('check_in_time')->default('14:00');
            $table->time('check_out_time')->default('12:00');
            $table->decimal('rating_avg', 2, 1)->default(0);
            $table->integer('review_count')->default(0);
            $table->string('status', 20)->default('DRAFT');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletesTz();
            $table->index(['city', 'status'], 'ix_hotels_1');
            $table->unique(['code'], 'ux_hotels_code');
            $table->comment('Khách sạn (chi nhánh) / ホテル（支店）');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
