<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `payments` table (Giao dịch thanh toán / 決済).
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_code', 30);
            $table->bigInteger('booking_id');
            $table->string('method', 20);
            $table->decimal('amount', 15, 0);
            $table->char('currency', 3)->default('VND');
            $table->string('status', 20)->default('PENDING');
            $table->string('provider_txn_id', 100)->nullable();
            $table->string('pay_url', 1000)->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->string('failure_reason', 500)->nullable();
            $table->jsonb('raw_response')->nullable();
            $table->bigInteger('collected_by')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->index(['booking_id'], 'ix_payments_1');
            $table->unique(['method', 'provider_txn_id'], 'ux_payments_2');
            $table->unique(['payment_code'], 'ux_payments_payment_code');
            $table->comment('Giao dịch thanh toán / 決済');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
