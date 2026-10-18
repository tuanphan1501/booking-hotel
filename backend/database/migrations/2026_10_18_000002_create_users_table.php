<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `users` table (Khách hàng (thành viên) / 会員).
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('member_code', 20);
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('password_hash', 255)->nullable();
            $table->string('full_name', 100);
            $table->string('avatar_url', 500)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->char('nationality', 2)->nullable();
            $table->string('language', 5)->default('vi');
            $table->bigInteger('member_tier_id');
            $table->integer('point_balance')->default(0);
            $table->decimal('spending_12m', 15, 0)->default(0);
            $table->string('social_provider', 20)->nullable();
            $table->string('social_id', 255)->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampTz('email_verified_at')->nullable();
            $table->timestampTz('phone_verified_at')->nullable();
            $table->timestampTz('last_login_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->softDeletesTz();
            $table->unique(['social_provider', 'social_id'], 'ux_users_3');
            $table->index(['member_tier_id'], 'ix_users_4');
            $table->unique(['member_code'], 'ux_users_member_code');
            $table->comment('Khách hàng (thành viên) / 会員');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE UNIQUE INDEX ux_users_1 ON users (email) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX ux_users_2 ON users (phone) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
