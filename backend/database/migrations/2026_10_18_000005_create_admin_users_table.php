<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `admin_users` table (Tài khoản quản trị / 管理者アカウント).
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
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('hotel_id')->nullable();
            $table->bigInteger('role_id');
            $table->string('email', 255);
            $table->string('password_hash', 255);
            $table->string('full_name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->smallInteger('failed_login_count')->default(0);
            $table->string('mfa_secret', 255)->nullable();
            $table->timestampTz('last_login_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletesTz();
            $table->index(['hotel_id'], 'ix_admin_users_2');
            $table->comment('Tài khoản quản trị / 管理者アカウント');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE UNIQUE INDEX ux_admin_users_1 ON admin_users (email) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
