<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds every foreign key of the schema (61 constraints) after all tables exist.
 *
 * No cascading deletes: master and transaction data are soft-deleted (deleted_at), so rows referenced
 * by bookings, payments or audit logs are never physically removed.
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
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('member_tier_id', 'fk_users_member_tier_id')->references('id')->on('member_tiers');
        });

        Schema::table('admin_users', function (Blueprint $table) {
            $table->foreign('hotel_id', 'fk_admin_users_hotel_id')->references('id')->on('hotels');
            $table->foreign('role_id', 'fk_admin_users_role_id')->references('id')->on('roles');
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreign('role_id', 'fk_role_permissions_role_id')->references('id')->on('roles');
            $table->foreign('permission_id', 'fk_role_permissions_permission_id')->references('id')->on('permissions');
        });

        Schema::table('device_tokens', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_device_tokens_user_id')->references('id')->on('users');
        });

        Schema::table('hotel_images', function (Blueprint $table) {
            $table->foreign('hotel_id', 'fk_hotel_images_hotel_id')->references('id')->on('hotels');
        });

        Schema::table('hotel_amenities', function (Blueprint $table) {
            $table->foreign('hotel_id', 'fk_hotel_amenities_hotel_id')->references('id')->on('hotels');
            $table->foreign('amenity_id', 'fk_hotel_amenities_amenity_id')->references('id')->on('amenities');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->foreign('hotel_id', 'fk_room_types_hotel_id')->references('id')->on('hotels');
        });

        Schema::table('room_type_images', function (Blueprint $table) {
            $table->foreign('room_type_id', 'fk_room_type_images_room_type_id')->references('id')->on('room_types');
        });

        Schema::table('room_type_amenities', function (Blueprint $table) {
            $table->foreign('room_type_id', 'fk_room_type_amenities_room_type_id')->references('id')->on('room_types');
            $table->foreign('amenity_id', 'fk_room_type_amenities_amenity_id')->references('id')->on('amenities');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->foreign('hotel_id', 'fk_rooms_hotel_id')->references('id')->on('hotels');
            $table->foreign('room_type_id', 'fk_rooms_room_type_id')->references('id')->on('room_types');
        });

        Schema::table('cancellation_policies', function (Blueprint $table) {
            $table->foreign('hotel_id', 'fk_cancellation_policies_hotel_id')->references('id')->on('hotels');
        });

        Schema::table('rate_plans', function (Blueprint $table) {
            $table->foreign('room_type_id', 'fk_rate_plans_room_type_id')->references('id')->on('room_types');
            $table->foreign('cancellation_policy_id', 'fk_rate_plans_cancellation_policy_id')->references('id')->on('cancellation_policies');
        });

        Schema::table('room_inventories', function (Blueprint $table) {
            $table->foreign('room_type_id', 'fk_room_inventories_room_type_id')->references('id')->on('room_types');
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->foreign('hotel_id', 'fk_promotions_hotel_id')->references('id')->on('hotels');
        });

        Schema::table('promotion_room_types', function (Blueprint $table) {
            $table->foreign('promotion_id', 'fk_promotion_room_types_promotion_id')->references('id')->on('promotions');
            $table->foreign('room_type_id', 'fk_promotion_room_types_room_type_id')->references('id')->on('room_types');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreign('hotel_id', 'fk_vouchers_hotel_id')->references('id')->on('hotels');
            $table->foreign('min_tier_id', 'fk_vouchers_min_tier_id')->references('id')->on('member_tiers');
        });

        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_user_vouchers_user_id')->references('id')->on('users');
            $table->foreign('voucher_id', 'fk_user_vouchers_voucher_id')->references('id')->on('vouchers');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_bookings_user_id')->references('id')->on('users');
            $table->foreign('hotel_id', 'fk_bookings_hotel_id')->references('id')->on('hotels');
            $table->foreign('promotion_id', 'fk_bookings_promotion_id')->references('id')->on('promotions');
            $table->foreign('voucher_id', 'fk_bookings_voucher_id')->references('id')->on('vouchers');
        });

        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->foreign('booking_id', 'fk_booking_rooms_booking_id')->references('id')->on('bookings');
            $table->foreign('room_type_id', 'fk_booking_rooms_room_type_id')->references('id')->on('room_types');
            $table->foreign('rate_plan_id', 'fk_booking_rooms_rate_plan_id')->references('id')->on('rate_plans');
            $table->foreign('room_id', 'fk_booking_rooms_room_id')->references('id')->on('rooms');
        });

        Schema::table('booking_room_nights', function (Blueprint $table) {
            $table->foreign('booking_room_id', 'fk_booking_room_nights_booking_room_id')->references('id')->on('booking_rooms');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreign('hotel_id', 'fk_services_hotel_id')->references('id')->on('hotels');
        });

        Schema::table('booking_services', function (Blueprint $table) {
            $table->foreign('booking_id', 'fk_booking_services_booking_id')->references('id')->on('bookings');
            $table->foreign('service_id', 'fk_booking_services_service_id')->references('id')->on('services');
        });

        Schema::table('booking_status_histories', function (Blueprint $table) {
            $table->foreign('booking_id', 'fk_booking_status_histories_booking_id')->references('id')->on('bookings');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('booking_id', 'fk_payments_booking_id')->references('id')->on('bookings');
            $table->foreign('collected_by', 'fk_payments_collected_by')->references('id')->on('admin_users');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->foreign('payment_id', 'fk_refunds_payment_id')->references('id')->on('payments');
            $table->foreign('booking_id', 'fk_refunds_booking_id')->references('id')->on('bookings');
            $table->foreign('requested_by', 'fk_refunds_requested_by')->references('id')->on('admin_users');
            $table->foreign('approved_by', 'fk_refunds_approved_by')->references('id')->on('admin_users');
        });

        Schema::table('voucher_usages', function (Blueprint $table) {
            $table->foreign('voucher_id', 'fk_voucher_usages_voucher_id')->references('id')->on('vouchers');
            $table->foreign('user_id', 'fk_voucher_usages_user_id')->references('id')->on('users');
            $table->foreign('booking_id', 'fk_voucher_usages_booking_id')->references('id')->on('bookings');
        });

        Schema::table('point_transactions', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_point_transactions_user_id')->references('id')->on('users');
            $table->foreign('booking_id', 'fk_point_transactions_booking_id')->references('id')->on('bookings');
            $table->foreign('created_by', 'fk_point_transactions_created_by')->references('id')->on('admin_users');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('booking_id', 'fk_reviews_booking_id')->references('id')->on('bookings');
            $table->foreign('user_id', 'fk_reviews_user_id')->references('id')->on('users');
            $table->foreign('hotel_id', 'fk_reviews_hotel_id')->references('id')->on('hotels');
            $table->foreign('replied_by', 'fk_reviews_replied_by')->references('id')->on('admin_users');
        });

        Schema::table('review_images', function (Blueprint $table) {
            $table->foreign('review_id', 'fk_review_images_review_id')->references('id')->on('reviews');
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_favorites_user_id')->references('id')->on('users');
            $table->foreign('hotel_id', 'fk_favorites_hotel_id')->references('id')->on('hotels');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_notifications_user_id')->references('id')->on('users');
        });

        Schema::table('system_settings', function (Blueprint $table) {
            $table->foreign('updated_by', 'fk_system_settings_updated_by')->references('id')->on('admin_users');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreign('admin_user_id', 'fk_audit_logs_admin_user_id')->references('id')->on('admin_users');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign('fk_audit_logs_admin_user_id');
        });

        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropForeign('fk_system_settings_updated_by');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign('fk_notifications_user_id');
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropForeign('fk_favorites_user_id');
            $table->dropForeign('fk_favorites_hotel_id');
        });

        Schema::table('review_images', function (Blueprint $table) {
            $table->dropForeign('fk_review_images_review_id');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign('fk_reviews_booking_id');
            $table->dropForeign('fk_reviews_user_id');
            $table->dropForeign('fk_reviews_hotel_id');
            $table->dropForeign('fk_reviews_replied_by');
        });

        Schema::table('point_transactions', function (Blueprint $table) {
            $table->dropForeign('fk_point_transactions_user_id');
            $table->dropForeign('fk_point_transactions_booking_id');
            $table->dropForeign('fk_point_transactions_created_by');
        });

        Schema::table('voucher_usages', function (Blueprint $table) {
            $table->dropForeign('fk_voucher_usages_voucher_id');
            $table->dropForeign('fk_voucher_usages_user_id');
            $table->dropForeign('fk_voucher_usages_booking_id');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign('fk_refunds_payment_id');
            $table->dropForeign('fk_refunds_booking_id');
            $table->dropForeign('fk_refunds_requested_by');
            $table->dropForeign('fk_refunds_approved_by');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('fk_payments_booking_id');
            $table->dropForeign('fk_payments_collected_by');
        });

        Schema::table('booking_status_histories', function (Blueprint $table) {
            $table->dropForeign('fk_booking_status_histories_booking_id');
        });

        Schema::table('booking_services', function (Blueprint $table) {
            $table->dropForeign('fk_booking_services_booking_id');
            $table->dropForeign('fk_booking_services_service_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign('fk_services_hotel_id');
        });

        Schema::table('booking_room_nights', function (Blueprint $table) {
            $table->dropForeign('fk_booking_room_nights_booking_room_id');
        });

        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->dropForeign('fk_booking_rooms_booking_id');
            $table->dropForeign('fk_booking_rooms_room_type_id');
            $table->dropForeign('fk_booking_rooms_rate_plan_id');
            $table->dropForeign('fk_booking_rooms_room_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign('fk_bookings_user_id');
            $table->dropForeign('fk_bookings_hotel_id');
            $table->dropForeign('fk_bookings_promotion_id');
            $table->dropForeign('fk_bookings_voucher_id');
        });

        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->dropForeign('fk_user_vouchers_user_id');
            $table->dropForeign('fk_user_vouchers_voucher_id');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign('fk_vouchers_hotel_id');
            $table->dropForeign('fk_vouchers_min_tier_id');
        });

        Schema::table('promotion_room_types', function (Blueprint $table) {
            $table->dropForeign('fk_promotion_room_types_promotion_id');
            $table->dropForeign('fk_promotion_room_types_room_type_id');
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign('fk_promotions_hotel_id');
        });

        Schema::table('room_inventories', function (Blueprint $table) {
            $table->dropForeign('fk_room_inventories_room_type_id');
        });

        Schema::table('rate_plans', function (Blueprint $table) {
            $table->dropForeign('fk_rate_plans_room_type_id');
            $table->dropForeign('fk_rate_plans_cancellation_policy_id');
        });

        Schema::table('cancellation_policies', function (Blueprint $table) {
            $table->dropForeign('fk_cancellation_policies_hotel_id');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign('fk_rooms_hotel_id');
            $table->dropForeign('fk_rooms_room_type_id');
        });

        Schema::table('room_type_amenities', function (Blueprint $table) {
            $table->dropForeign('fk_room_type_amenities_room_type_id');
            $table->dropForeign('fk_room_type_amenities_amenity_id');
        });

        Schema::table('room_type_images', function (Blueprint $table) {
            $table->dropForeign('fk_room_type_images_room_type_id');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->dropForeign('fk_room_types_hotel_id');
        });

        Schema::table('hotel_amenities', function (Blueprint $table) {
            $table->dropForeign('fk_hotel_amenities_hotel_id');
            $table->dropForeign('fk_hotel_amenities_amenity_id');
        });

        Schema::table('hotel_images', function (Blueprint $table) {
            $table->dropForeign('fk_hotel_images_hotel_id');
        });

        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropForeign('fk_device_tokens_user_id');
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropForeign('fk_role_permissions_role_id');
            $table->dropForeign('fk_role_permissions_permission_id');
        });

        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropForeign('fk_admin_users_hotel_id');
            $table->dropForeign('fk_admin_users_role_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('fk_users_member_tier_id');
        });
    }
};
