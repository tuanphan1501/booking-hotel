<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `system_settings` table (Cấu hình hệ thống / システム設定).
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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value');
            $table->string('description', 500)->nullable();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('updated_by')->nullable();
            $table->comment('Cấu hình hệ thống / システム設定');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
