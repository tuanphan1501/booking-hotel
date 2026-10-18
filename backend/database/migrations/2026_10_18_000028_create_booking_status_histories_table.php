<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `booking_status_histories` table (Lịch sử trạng thái đặt phòng / 予約ステータス履歴).
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
        Schema::create('booking_status_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('booking_id');
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->string('actor_type', 10);
            $table->bigInteger('actor_id')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['booking_id', 'created_at'], 'ix_booking_status_histories_1');
            $table->comment('Lịch sử trạng thái đặt phòng / 予約ステータス履歴');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_status_histories');
    }
};
