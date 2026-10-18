<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `role_permissions` table (Quyền của vai trò / ロール権限).
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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->bigInteger('role_id');
            $table->bigInteger('permission_id');
            $table->primary(['role_id', 'permission_id']);
            $table->comment('Quyền của vai trò / ロール権限');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
