<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `bookings` table (Đặt phòng / 予約).
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20);
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('hotel_id');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->smallInteger('nights');
            $table->smallInteger('adults');
            $table->smallInteger('children')->default(0);
            $table->string('guest_name', 100);
            $table->string('guest_email', 255);
            $table->string('guest_phone', 20);
            $table->time('estimated_arrival')->nullable();
            $table->text('special_request')->nullable();
            $table->string('status', 20)->default('PENDING_PAYMENT');
            $table->string('source', 20);
            $table->decimal('room_amount', 15, 0)->default(0);
            $table->decimal('service_amount', 15, 0)->default(0);
            $table->bigInteger('promotion_id')->nullable();
            $table->decimal('promotion_discount', 15, 0)->default(0);
            $table->bigInteger('voucher_id')->nullable();
            $table->decimal('voucher_discount', 15, 0)->default(0);
            $table->decimal('service_charge', 15, 0)->default(0);
            $table->decimal('tax_amount', 15, 0)->default(0);
            $table->decimal('total_amount', 15, 0)->default(0);
            $table->integer('points_used')->default(0);
            $table->decimal('points_amount', 15, 0)->default(0);
            $table->decimal('payable_amount', 15, 0)->default(0);
            $table->decimal('paid_amount', 15, 0)->default(0);
            $table->decimal('refunded_amount', 15, 0)->default(0);
            $table->char('currency', 3)->default('VND');
            $table->integer('points_earned')->default(0);
            $table->jsonb('cancel_policy_snapshot');
            $table->timestampTz('hold_expires_at')->nullable();
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampTz('checked_in_at')->nullable();
            $table->timestampTz('checked_out_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->string('cancel_reason', 500)->nullable();
            $table->decimal('cancel_fee', 15, 0)->default(0);
            $table->string('cancelled_by', 10)->nullable();
            $table->text('internal_note')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletesTz();
            $table->index(['hotel_id', 'check_in_date'], 'ix_bookings_2');
            $table->index(['status', 'hold_expires_at'], 'ix_bookings_3');
            $table->index(['guest_phone'], 'ix_bookings_4');
            $table->unique(['booking_code'], 'ux_bookings_booking_code');
            $table->comment('Đặt phòng / 予約');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE INDEX ix_bookings_1 ON bookings (user_id, created_at DESC)');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
