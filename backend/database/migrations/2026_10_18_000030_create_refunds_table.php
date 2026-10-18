<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `refunds` table (Hoàn tiền / 返金).
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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_code', 30);
            $table->bigInteger('payment_id');
            $table->bigInteger('booking_id');
            $table->decimal('amount', 15, 0);
            $table->string('reason', 500);
            $table->string('status', 20)->default('PENDING');
            $table->string('provider_refund_id', 100)->nullable();
            $table->bigInteger('requested_by')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->jsonb('raw_response')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->index(['booking_id'], 'ix_refunds_1');
            $table->index(['status'], 'ix_refunds_2');
            $table->unique(['refund_code'], 'ux_refunds_refund_code');
            $table->comment('Hoàn tiền / 返金');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
