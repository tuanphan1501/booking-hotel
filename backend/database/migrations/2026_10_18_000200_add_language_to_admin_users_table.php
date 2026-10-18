<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the administrator's display language (vi | ja).
 *
 * Chosen on AD-19 Personal settings; also used for notification emails sent to the administrator.
 * Not part of the original DB design — proposed in PR #2.
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
            $table->string('language', 5)->default('vi');
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
            $table->dropColumn('language');
        });
    }
};
