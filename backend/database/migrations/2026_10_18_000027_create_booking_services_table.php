<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `booking_services` table (Dịch vụ trong đặt phòng / 予約オプション).
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
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('booking_id');
            $table->bigInteger('service_id');
            $table->date('service_date')->nullable();
            $table->time('service_time')->nullable();
            $table->smallInteger('quantity');
            $table->decimal('unit_price', 15, 0);
            $table->decimal('amount', 15, 0);
            $table->string('note', 500)->nullable();
            $table->string('status', 20)->default('BOOKED');
            $table->boolean('added_at_counter')->default(false);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->index(['booking_id'], 'ix_booking_services_1');
            $table->comment('Dịch vụ trong đặt phòng / 予約オプション');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};
