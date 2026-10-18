<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `services` table (Dịch vụ thêm / オプションサービス).
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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('hotel_id');
            $table->string('code', 20);
            $table->string('category', 20);
            $table->string('name', 200);
            $table->string('name_ja', 200)->nullable();
            $table->text('description')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->decimal('price', 15, 0);
            $table->string('unit', 20);
            $table->boolean('bookable_in_app')->default(true);
            $table->smallInteger('lead_time_hours')->default(0);
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletesTz();
            $table->comment('Dịch vụ thêm / オプションサービス');
        });

        // Partial / expression indexes and CHECK constraints are not expressible with the schema builder.
        DB::statement('CREATE UNIQUE INDEX ux_services_1 ON services (hotel_id, code) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
