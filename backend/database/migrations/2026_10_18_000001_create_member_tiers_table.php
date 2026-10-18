<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `member_tiers` table (Hạng thành viên / 会員ランク).
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
        Schema::create('member_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->string('name_vi', 50);
            $table->string('name_ja', 50);
            $table->decimal('min_spending_12m', 15, 0);
            $table->decimal('point_multiplier', 3, 2)->default(1.00);
            $table->text('benefits')->nullable();
            $table->smallInteger('sort_order');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->unique(['code'], 'ux_member_tiers_code');
            $table->comment('Hạng thành viên / 会員ランク');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('member_tiers');
    }
};
