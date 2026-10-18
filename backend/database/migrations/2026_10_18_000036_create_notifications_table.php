<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `notifications` table (Thông báo / お知らせ).
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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('type', 30);
            $table->string('title', 200);
            $table->text('body');
            $table->jsonb('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestampTz('sent_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->comment('Thông báo / お知らせ');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE INDEX ix_notifications_1 ON notifications (user_id, is_read, created_at DESC)');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
