<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the administrator's profile picture (AD-19 Personal settings).
 *
 * - Stores the path on the "public" disk, e.g. "avatars/admins/12/3f9c….webp".
 * - Not part of the original DB design — add it to HotelBooking_DatabaseDesign.xlsx.
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
            $table->string('avatar_path', 500)->nullable();
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
            $table->dropColumn('avatar_path');
        });
    }
};
