<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `voucher_usages` table (Lịch sử dùng voucher / クーポン利用履歴).
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
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('voucher_id');
            $table->bigInteger('user_id');
            $table->bigInteger('booking_id');
            $table->decimal('discount_amount', 15, 0);
            $table->string('status', 20)->default('USED');
            $table->timestampTz('used_at')->useCurrent();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->index(['voucher_id', 'user_id', 'status'], 'ix_voucher_usages_1');
            $table->unique(['booking_id'], 'ux_voucher_usages_2');
            $table->comment('Lịch sử dùng voucher / クーポン利用履歴');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
    }
};
