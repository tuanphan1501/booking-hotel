<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `audit_logs` table (Nhật ký thao tác / 操作ログ).
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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('admin_user_id')->nullable();
            $table->string('action', 30);
            $table->string('entity', 50);
            $table->string('entity_id', 50)->nullable();
            $table->jsonb('old_value')->nullable();
            $table->jsonb('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['entity', 'entity_id'], 'ix_audit_logs_1');
            $table->comment('Nhật ký thao tác / 操作ログ');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE INDEX ix_audit_logs_2 ON audit_logs (admin_user_id, created_at DESC)');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
