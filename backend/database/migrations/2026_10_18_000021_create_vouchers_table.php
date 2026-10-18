<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `vouchers` table (Voucher / mã giảm giá / クーポン).
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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30);
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->bigInteger('hotel_id')->nullable();
            $table->string('discount_type', 10);
            $table->decimal('discount_value', 15, 2);
            $table->decimal('max_discount', 15, 0)->nullable();
            $table->decimal('min_order_amount', 15, 0)->default(0);
            $table->bigInteger('min_tier_id')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestampTz('start_at');
            $table->timestampTz('end_at');
            $table->integer('usage_limit')->nullable();
            $table->smallInteger('usage_per_user')->default(1);
            $table->integer('used_count')->default(0);
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletesTz();
            $table->index(['status', 'start_at', 'end_at'], 'ix_vouchers_2');
            $table->comment('Voucher / mã giảm giá / クーポン');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE UNIQUE INDEX ux_vouchers_1 ON vouchers (UPPER(code)) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
