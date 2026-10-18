<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widens `admin_users.mfa_secret` from VARCHAR(255) to TEXT.
 *
 * - The TOTP secret is encrypted at rest (AdminUser cast "encrypted"); the encrypted payload is
 *   longer than 255 characters. Deviation from the DB design — update the design sheet accordingly.
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
        Schema::table('admin_users', function (Blueprint $table) {
            $table->text('mfa_secret')->nullable()->change();
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->string('mfa_secret', 255)->nullable()->change();
        });
    }
};
