-- Hotel Booking System — PostgreSQL 16 DDL (generated from HotelBooking_DatabaseDesign.xlsx)

-- Hạng thành viên / 会員ランク
CREATE TABLE member_tiers (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  code VARCHAR(20) NOT NULL,
  name_vi VARCHAR(50) NOT NULL,
  name_ja VARCHAR(50) NOT NULL,
  min_spending_12m DECIMAL(15,0) NOT NULL,
  point_multiplier DECIMAL(3,2) NOT NULL DEFAULT 1.00,
  benefits TEXT,
  sort_order SMALLINT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_member_tiers_code ON member_tiers (code);
COMMENT ON TABLE member_tiers IS 'Hạng thành viên / 会員ランク';

-- Khách hàng (thành viên) / 会員
CREATE TABLE users (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  member_code VARCHAR(20) NOT NULL,
  email VARCHAR(255),
  phone VARCHAR(20),
  password_hash VARCHAR(255),
  full_name VARCHAR(100) NOT NULL,
  avatar_url VARCHAR(500),
  date_of_birth DATE,
  gender VARCHAR(10),
  nationality CHAR(2),
  language VARCHAR(5) NOT NULL DEFAULT 'vi',
  member_tier_id BIGINT NOT NULL,
  point_balance INTEGER NOT NULL DEFAULT 0,
  spending_12m DECIMAL(15,0) NOT NULL DEFAULT 0,
  social_provider VARCHAR(20),
  social_id VARCHAR(255),
  status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
  email_verified_at TIMESTAMPTZ,
  phone_verified_at TIMESTAMPTZ,
  last_login_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_users_1 ON users (email) WHERE deleted_at IS NULL;
CREATE UNIQUE INDEX ux_users_2 ON users (phone) WHERE deleted_at IS NULL;
CREATE UNIQUE INDEX ux_users_3 ON users (social_provider, social_id);
CREATE INDEX ix_users_4 ON users (member_tier_id);
CREATE UNIQUE INDEX ux_users_member_code ON users (member_code);
COMMENT ON TABLE users IS 'Khách hàng (thành viên) / 会員';

-- Vai trò / ロール
CREATE TABLE roles (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  code VARCHAR(30) NOT NULL,
  name_vi VARCHAR(100) NOT NULL,
  name_ja VARCHAR(100) NOT NULL,
  description TEXT,
  is_system BOOLEAN NOT NULL DEFAULT false,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_roles_code ON roles (code);
COMMENT ON TABLE roles IS 'Vai trò / ロール';

-- Khách sạn (chi nhánh) / ホテル（支店）
CREATE TABLE hotels (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  code VARCHAR(20) NOT NULL,
  name VARCHAR(200) NOT NULL,
  name_ja VARCHAR(200),
  description TEXT,
  description_ja TEXT,
  star_rating SMALLINT,
  address VARCHAR(500) NOT NULL,
  city VARCHAR(100) NOT NULL,
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  phone VARCHAR(20),
  email VARCHAR(255),
  check_in_time TIME NOT NULL DEFAULT '14:00',
  check_out_time TIME NOT NULL DEFAULT '12:00',
  rating_avg DECIMAL(2,1) NOT NULL DEFAULT 0,
  review_count INTEGER NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'DRAFT',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE INDEX ix_hotels_1 ON hotels (city, status);
CREATE UNIQUE INDEX ux_hotels_code ON hotels (code);
COMMENT ON TABLE hotels IS 'Khách sạn (chi nhánh) / ホテル（支店）';

-- Tài khoản quản trị / 管理者アカウント
CREATE TABLE admin_users (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  hotel_id BIGINT,
  role_id BIGINT NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
  failed_login_count SMALLINT NOT NULL DEFAULT 0,
  mfa_secret VARCHAR(255),
  last_login_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_admin_users_1 ON admin_users (email) WHERE deleted_at IS NULL;
CREATE INDEX ix_admin_users_2 ON admin_users (hotel_id);
COMMENT ON TABLE admin_users IS 'Tài khoản quản trị / 管理者アカウント';

-- Quyền / 権限
CREATE TABLE permissions (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  code VARCHAR(50) NOT NULL,
  module VARCHAR(30) NOT NULL,
  name_vi VARCHAR(100) NOT NULL,
  name_ja VARCHAR(100) NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_permissions_code ON permissions (code);
COMMENT ON TABLE permissions IS 'Quyền / 権限';

-- Quyền của vai trò / ロール権限
CREATE TABLE role_permissions (
  role_id BIGINT NOT NULL,
  permission_id BIGINT NOT NULL,
  PRIMARY KEY (role_id, permission_id)
);
COMMENT ON TABLE role_permissions IS 'Quyền của vai trò / ロール権限';

-- Token thiết bị / デバイストークン
CREATE TABLE device_tokens (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  user_id BIGINT NOT NULL,
  token VARCHAR(500) NOT NULL,
  platform VARCHAR(10) NOT NULL,
  last_used_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);
CREATE INDEX ix_device_tokens_1 ON device_tokens (user_id);
CREATE UNIQUE INDEX ux_device_tokens_token ON device_tokens (token);
COMMENT ON TABLE device_tokens IS 'Token thiết bị / デバイストークン';

-- Ảnh khách sạn / ホテル画像
CREATE TABLE hotel_images (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  hotel_id BIGINT NOT NULL,
  url VARCHAR(500) NOT NULL,
  caption VARCHAR(200),
  sort_order SMALLINT NOT NULL DEFAULT 0,
  is_cover BOOLEAN NOT NULL DEFAULT false,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  PRIMARY KEY (id)
);
CREATE INDEX ix_hotel_images_1 ON hotel_images (hotel_id, sort_order);
COMMENT ON TABLE hotel_images IS 'Ảnh khách sạn / ホテル画像';

-- Tiện nghi / 設備・アメニティ
CREATE TABLE amenities (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  code VARCHAR(30) NOT NULL,
  scope VARCHAR(10) NOT NULL,
  name_vi VARCHAR(100) NOT NULL,
  name_ja VARCHAR(100) NOT NULL,
  name_en VARCHAR(100) NOT NULL,
  icon VARCHAR(50),
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_amenities_code ON amenities (code);
COMMENT ON TABLE amenities IS 'Tiện nghi / 設備・アメニティ';

-- Tiện nghi khách sạn / ホテル設備
CREATE TABLE hotel_amenities (
  hotel_id BIGINT NOT NULL,
  amenity_id BIGINT NOT NULL,
  PRIMARY KEY (hotel_id, amenity_id)
);
COMMENT ON TABLE hotel_amenities IS 'Tiện nghi khách sạn / ホテル設備';

-- Loại phòng / 客室タイプ
CREATE TABLE room_types (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  hotel_id BIGINT NOT NULL,
  code VARCHAR(20) NOT NULL,
  name VARCHAR(200) NOT NULL,
  name_ja VARCHAR(200),
  description TEXT,
  description_ja TEXT,
  area_m2 DECIMAL(6,1),
  bed_type VARCHAR(20),
  max_adults SMALLINT NOT NULL,
  max_children SMALLINT NOT NULL DEFAULT 0,
  view_type VARCHAR(20),
  smoking BOOLEAN NOT NULL DEFAULT false,
  base_price DECIMAL(15,0) NOT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_room_types_1 ON room_types (hotel_id, code) WHERE deleted_at IS NULL;
COMMENT ON TABLE room_types IS 'Loại phòng / 客室タイプ';

-- Ảnh loại phòng / 客室タイプ画像
CREATE TABLE room_type_images (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  room_type_id BIGINT NOT NULL,
  url VARCHAR(500) NOT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  PRIMARY KEY (id)
);
CREATE INDEX ix_room_type_images_1 ON room_type_images (room_type_id, sort_order);
COMMENT ON TABLE room_type_images IS 'Ảnh loại phòng / 客室タイプ画像';

-- Tiện nghi loại phòng / 客室タイプ設備
CREATE TABLE room_type_amenities (
  room_type_id BIGINT NOT NULL,
  amenity_id BIGINT NOT NULL,
  PRIMARY KEY (room_type_id, amenity_id)
);
COMMENT ON TABLE room_type_amenities IS 'Tiện nghi loại phòng / 客室タイプ設備';

-- Phòng / 客室
CREATE TABLE rooms (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  hotel_id BIGINT NOT NULL,
  room_type_id BIGINT NOT NULL,
  room_number VARCHAR(10) NOT NULL,
  floor SMALLINT,
  status VARCHAR(20) NOT NULL DEFAULT 'AVAILABLE',
  note VARCHAR(500),
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_rooms_1 ON rooms (hotel_id, room_number) WHERE deleted_at IS NULL;
CREATE INDEX ix_rooms_2 ON rooms (room_type_id, status);
COMMENT ON TABLE rooms IS 'Phòng / 客室';

-- Chính sách hủy / キャンセルポリシー
CREATE TABLE cancellation_policies (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  hotel_id BIGINT NOT NULL,
  name VARCHAR(200) NOT NULL,
  is_refundable BOOLEAN NOT NULL DEFAULT true,
  free_cancel_hours INTEGER NOT NULL DEFAULT 48,
  penalty_percent DECIMAL(5,2) NOT NULL DEFAULT 100,
  no_show_percent DECIMAL(5,2) NOT NULL DEFAULT 100,
  description TEXT,
  description_ja TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
COMMENT ON TABLE cancellation_policies IS 'Chính sách hủy / キャンセルポリシー';

-- Gói giá / 料金プラン
CREATE TABLE rate_plans (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  room_type_id BIGINT NOT NULL,
  cancellation_policy_id BIGINT NOT NULL,
  code VARCHAR(20) NOT NULL,
  name VARCHAR(200) NOT NULL,
  name_ja VARCHAR(200),
  breakfast_included BOOLEAN NOT NULL DEFAULT false,
  price_adjust_type VARCHAR(10) NOT NULL DEFAULT 'NONE',
  price_adjust_value DECIMAL(15,2) NOT NULL DEFAULT 0,
  min_nights SMALLINT NOT NULL DEFAULT 1,
  advance_days_min SMALLINT,
  status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_rate_plans_1 ON rate_plans (room_type_id, code) WHERE deleted_at IS NULL;
COMMENT ON TABLE rate_plans IS 'Gói giá / 料金プラン';

-- Tồn kho & giá theo ngày / 日別在庫・料金
CREATE TABLE room_inventories (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  room_type_id BIGINT NOT NULL,
  stay_date DATE NOT NULL,
  total_rooms SMALLINT NOT NULL,
  booked_rooms SMALLINT NOT NULL DEFAULT 0,
  blocked_rooms SMALLINT NOT NULL DEFAULT 0,
  price DECIMAL(15,0),
  stop_sell BOOLEAN NOT NULL DEFAULT false,
  version INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_room_inventories_1 ON room_inventories (room_type_id, stay_date);
ALTER TABLE room_inventories ADD CONSTRAINT ck_room_inventories_2 CHECK (booked_rooms + blocked_rooms <= total_rooms);
COMMENT ON TABLE room_inventories IS 'Tồn kho & giá theo ngày / 日別在庫・料金';

-- Khuyến mãi tự động / 自動適用プロモーション
CREATE TABLE promotions (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  hotel_id BIGINT,
  name VARCHAR(200) NOT NULL,
  name_ja VARCHAR(200),
  type VARCHAR(20) NOT NULL,
  discount_percent DECIMAL(5,2) NOT NULL,
  min_nights SMALLINT,
  min_advance_days SMALLINT,
  max_advance_days SMALLINT,
  stay_start_date DATE NOT NULL,
  stay_end_date DATE NOT NULL,
  booking_start_at TIMESTAMPTZ,
  booking_end_at TIMESTAMPTZ,
  priority SMALLINT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE INDEX ix_promotions_1 ON promotions (hotel_id, status, stay_start_date, stay_end_date);
COMMENT ON TABLE promotions IS 'Khuyến mãi tự động / 自動適用プロモーション';

-- Loại phòng áp dụng KM / プロモーション対象客室
CREATE TABLE promotion_room_types (
  promotion_id BIGINT NOT NULL,
  room_type_id BIGINT NOT NULL,
  PRIMARY KEY (promotion_id, room_type_id)
);
COMMENT ON TABLE promotion_room_types IS 'Loại phòng áp dụng KM / プロモーション対象客室';

-- Voucher / mã giảm giá / クーポン
CREATE TABLE vouchers (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  code VARCHAR(30) NOT NULL,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  hotel_id BIGINT,
  discount_type VARCHAR(10) NOT NULL,
  discount_value DECIMAL(15,2) NOT NULL,
  max_discount DECIMAL(15,0),
  min_order_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
  min_tier_id BIGINT,
  is_public BOOLEAN NOT NULL DEFAULT true,
  start_at TIMESTAMPTZ NOT NULL,
  end_at TIMESTAMPTZ NOT NULL,
  usage_limit INTEGER,
  usage_per_user SMALLINT NOT NULL DEFAULT 1,
  used_count INTEGER NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_vouchers_1 ON vouchers (UPPER(code)) WHERE deleted_at IS NULL;
CREATE INDEX ix_vouchers_2 ON vouchers (status, start_at, end_at);
COMMENT ON TABLE vouchers IS 'Voucher / mã giảm giá / クーポン';

-- Ví voucher của khách / 会員保有クーポン
CREATE TABLE user_vouchers (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  user_id BIGINT NOT NULL,
  voucher_id BIGINT NOT NULL,
  source VARCHAR(20) NOT NULL,
  received_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_user_vouchers_1 ON user_vouchers (user_id, voucher_id);
COMMENT ON TABLE user_vouchers IS 'Ví voucher của khách / 会員保有クーポン';

-- Đặt phòng / 予約
CREATE TABLE bookings (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  booking_code VARCHAR(20) NOT NULL,
  user_id BIGINT,
  hotel_id BIGINT NOT NULL,
  check_in_date DATE NOT NULL,
  check_out_date DATE NOT NULL,
  nights SMALLINT NOT NULL,
  adults SMALLINT NOT NULL,
  children SMALLINT NOT NULL DEFAULT 0,
  guest_name VARCHAR(100) NOT NULL,
  guest_email VARCHAR(255) NOT NULL,
  guest_phone VARCHAR(20) NOT NULL,
  estimated_arrival TIME,
  special_request TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'PENDING_PAYMENT',
  source VARCHAR(20) NOT NULL,
  room_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
  service_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
  promotion_id BIGINT,
  promotion_discount DECIMAL(15,0) NOT NULL DEFAULT 0,
  voucher_id BIGINT,
  voucher_discount DECIMAL(15,0) NOT NULL DEFAULT 0,
  service_charge DECIMAL(15,0) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
  total_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
  points_used INTEGER NOT NULL DEFAULT 0,
  points_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
  payable_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
  refunded_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'VND',
  points_earned INTEGER NOT NULL DEFAULT 0,
  cancel_policy_snapshot JSONB NOT NULL,
  hold_expires_at TIMESTAMPTZ,
  confirmed_at TIMESTAMPTZ,
  checked_in_at TIMESTAMPTZ,
  checked_out_at TIMESTAMPTZ,
  cancelled_at TIMESTAMPTZ,
  cancel_reason VARCHAR(500),
  cancel_fee DECIMAL(15,0) NOT NULL DEFAULT 0,
  cancelled_by VARCHAR(10),
  internal_note TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE INDEX ix_bookings_1 ON bookings (user_id, created_at DESC);
CREATE INDEX ix_bookings_2 ON bookings (hotel_id, check_in_date);
CREATE INDEX ix_bookings_3 ON bookings (status, hold_expires_at);
CREATE INDEX ix_bookings_4 ON bookings (guest_phone);
CREATE UNIQUE INDEX ux_bookings_booking_code ON bookings (booking_code);
COMMENT ON TABLE bookings IS 'Đặt phòng / 予約';

-- Phòng trong đặt phòng / 予約客室
CREATE TABLE booking_rooms (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  booking_id BIGINT NOT NULL,
  room_type_id BIGINT NOT NULL,
  rate_plan_id BIGINT NOT NULL,
  room_id BIGINT,
  adults SMALLINT NOT NULL,
  children SMALLINT NOT NULL DEFAULT 0,
  subtotal DECIMAL(15,0) NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  PRIMARY KEY (id)
);
CREATE INDEX ix_booking_rooms_1 ON booking_rooms (booking_id);
CREATE INDEX ix_booking_rooms_2 ON booking_rooms (room_id);
COMMENT ON TABLE booking_rooms IS 'Phòng trong đặt phòng / 予約客室';

-- Giá từng đêm / 泊別料金
CREATE TABLE booking_room_nights (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  booking_room_id BIGINT NOT NULL,
  stay_date DATE NOT NULL,
  price DECIMAL(15,0) NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_booking_room_nights_1 ON booking_room_nights (booking_room_id, stay_date);
COMMENT ON TABLE booking_room_nights IS 'Giá từng đêm / 泊別料金';

-- Dịch vụ thêm / オプションサービス
CREATE TABLE services (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  hotel_id BIGINT NOT NULL,
  code VARCHAR(20) NOT NULL,
  category VARCHAR(20) NOT NULL,
  name VARCHAR(200) NOT NULL,
  name_ja VARCHAR(200),
  description TEXT,
  image_url VARCHAR(500),
  price DECIMAL(15,0) NOT NULL,
  unit VARCHAR(20) NOT NULL,
  bookable_in_app BOOLEAN NOT NULL DEFAULT true,
  lead_time_hours SMALLINT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX ux_services_1 ON services (hotel_id, code) WHERE deleted_at IS NULL;
COMMENT ON TABLE services IS 'Dịch vụ thêm / オプションサービス';

-- Dịch vụ trong đặt phòng / 予約オプション
CREATE TABLE booking_services (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  booking_id BIGINT NOT NULL,
  service_id BIGINT NOT NULL,
  service_date DATE,
  service_time TIME,
  quantity SMALLINT NOT NULL,
  unit_price DECIMAL(15,0) NOT NULL,
  amount DECIMAL(15,0) NOT NULL,
  note VARCHAR(500),
  status VARCHAR(20) NOT NULL DEFAULT 'BOOKED',
  added_at_counter BOOLEAN NOT NULL DEFAULT false,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  PRIMARY KEY (id)
);
CREATE INDEX ix_booking_services_1 ON booking_services (booking_id);
COMMENT ON TABLE booking_services IS 'Dịch vụ trong đặt phòng / 予約オプション';

-- Lịch sử trạng thái đặt phòng / 予約ステータス履歴
CREATE TABLE booking_status_histories (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  booking_id BIGINT NOT NULL,
  from_status VARCHAR(20),
  to_status VARCHAR(20) NOT NULL,
  actor_type VARCHAR(10) NOT NULL,
  actor_id BIGINT,
  note VARCHAR(500),
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);
CREATE INDEX ix_booking_status_histories_1 ON booking_status_histories (booking_id, created_at);
COMMENT ON TABLE booking_status_histories IS 'Lịch sử trạng thái đặt phòng / 予約ステータス履歴';

-- Giao dịch thanh toán / 決済
CREATE TABLE payments (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  payment_code VARCHAR(30) NOT NULL,
  booking_id BIGINT NOT NULL,
  method VARCHAR(20) NOT NULL,
  amount DECIMAL(15,0) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'VND',
  status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  provider_txn_id VARCHAR(100),
  pay_url VARCHAR(1000),
  paid_at TIMESTAMPTZ,
  failure_reason VARCHAR(500),
  raw_response JSONB,
  collected_by BIGINT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  PRIMARY KEY (id)
);
CREATE INDEX ix_payments_1 ON payments (booking_id);
CREATE UNIQUE INDEX ux_payments_2 ON payments (method, provider_txn_id);
CREATE UNIQUE INDEX ux_payments_payment_code ON payments (payment_code);
COMMENT ON TABLE payments IS 'Giao dịch thanh toán / 決済';

-- Hoàn tiền / 返金
CREATE TABLE refunds (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  refund_code VARCHAR(30) NOT NULL,
  payment_id BIGINT NOT NULL,
  booking_id BIGINT NOT NULL,
  amount DECIMAL(15,0) NOT NULL,
  reason VARCHAR(500) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  provider_refund_id VARCHAR(100),
  requested_by BIGINT,
  approved_by BIGINT,
  processed_at TIMESTAMPTZ,
  raw_response JSONB,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  PRIMARY KEY (id)
);
CREATE INDEX ix_refunds_1 ON refunds (booking_id);
CREATE INDEX ix_refunds_2 ON refunds (status);
CREATE UNIQUE INDEX ux_refunds_refund_code ON refunds (refund_code);
COMMENT ON TABLE refunds IS 'Hoàn tiền / 返金';

-- Lịch sử dùng voucher / クーポン利用履歴
CREATE TABLE voucher_usages (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  voucher_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  booking_id BIGINT NOT NULL,
  discount_amount DECIMAL(15,0) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'USED',
  used_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);
CREATE INDEX ix_voucher_usages_1 ON voucher_usages (voucher_id, user_id, status);
CREATE UNIQUE INDEX ux_voucher_usages_2 ON voucher_usages (booking_id);
COMMENT ON TABLE voucher_usages IS 'Lịch sử dùng voucher / クーポン利用履歴';

-- Giao dịch điểm / ポイント履歴
CREATE TABLE point_transactions (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  user_id BIGINT NOT NULL,
  booking_id BIGINT,
  type VARCHAR(20) NOT NULL,
  points INTEGER NOT NULL,
  balance_after INTEGER NOT NULL,
  remaining INTEGER,
  expires_at DATE,
  note VARCHAR(500),
  created_by BIGINT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);
CREATE INDEX ix_point_transactions_1 ON point_transactions (user_id, created_at DESC);
CREATE INDEX ix_point_transactions_2 ON point_transactions (expires_at) WHERE remaining > 0;
COMMENT ON TABLE point_transactions IS 'Giao dịch điểm / ポイント履歴';

-- Đánh giá / レビュー
CREATE TABLE reviews (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  booking_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  hotel_id BIGINT NOT NULL,
  rating_overall SMALLINT NOT NULL,
  rating_cleanliness SMALLINT,
  rating_service SMALLINT,
  rating_location SMALLINT,
  rating_value SMALLINT,
  comment TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  reply TEXT,
  replied_by BIGINT,
  replied_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
CREATE INDEX ix_reviews_1 ON reviews (hotel_id, status, created_at DESC);
CREATE UNIQUE INDEX ux_reviews_booking_id ON reviews (booking_id);
COMMENT ON TABLE reviews IS 'Đánh giá / レビュー';

-- Ảnh đánh giá / レビュー画像
CREATE TABLE review_images (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  review_id BIGINT NOT NULL,
  url VARCHAR(500) NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);
CREATE INDEX ix_review_images_1 ON review_images (review_id);
COMMENT ON TABLE review_images IS 'Ảnh đánh giá / レビュー画像';

-- Yêu thích / お気に入り
CREATE TABLE favorites (
  user_id BIGINT NOT NULL,
  hotel_id BIGINT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (user_id, hotel_id)
);
COMMENT ON TABLE favorites IS 'Yêu thích / お気に入り';

-- Thông báo / お知らせ
CREATE TABLE notifications (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  user_id BIGINT,
  type VARCHAR(30) NOT NULL,
  title VARCHAR(200) NOT NULL,
  body TEXT NOT NULL,
  data JSONB,
  is_read BOOLEAN NOT NULL DEFAULT false,
  sent_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);
CREATE INDEX ix_notifications_1 ON notifications (user_id, is_read, created_at DESC);
COMMENT ON TABLE notifications IS 'Thông báo / お知らせ';

-- Banner trang chủ / ホームバナー
CREATE TABLE banners (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  title VARCHAR(200) NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  link_type VARCHAR(20),
  link_value VARCHAR(500),
  start_at TIMESTAMPTZ NOT NULL,
  end_at TIMESTAMPTZ NOT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  created_by BIGINT,
  updated_by BIGINT,
  deleted_at TIMESTAMPTZ,
  PRIMARY KEY (id)
);
COMMENT ON TABLE banners IS 'Banner trang chủ / ホームバナー';

-- Cấu hình hệ thống / システム設定
CREATE TABLE system_settings (
  "key" VARCHAR(100) NOT NULL,
  "value" TEXT NOT NULL,
  description VARCHAR(500),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_by BIGINT,
  PRIMARY KEY (key)
);
COMMENT ON TABLE system_settings IS 'Cấu hình hệ thống / システム設定';

-- Nhật ký thao tác / 操作ログ
CREATE TABLE audit_logs (
  id BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
  admin_user_id BIGINT,
  action VARCHAR(30) NOT NULL,
  entity VARCHAR(50) NOT NULL,
  entity_id VARCHAR(50),
  old_value JSONB,
  new_value JSONB,
  ip_address VARCHAR(45),
  user_agent VARCHAR(500),
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);
CREATE INDEX ix_audit_logs_1 ON audit_logs (entity, entity_id);
CREATE INDEX ix_audit_logs_2 ON audit_logs (admin_user_id, created_at DESC);
COMMENT ON TABLE audit_logs IS 'Nhật ký thao tác / 操作ログ';

-- Foreign keys
ALTER TABLE users ADD CONSTRAINT fk_users_member_tier_id FOREIGN KEY (member_tier_id) REFERENCES member_tiers(id);
ALTER TABLE admin_users ADD CONSTRAINT fk_admin_users_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE admin_users ADD CONSTRAINT fk_admin_users_role_id FOREIGN KEY (role_id) REFERENCES roles(id);
ALTER TABLE role_permissions ADD CONSTRAINT fk_role_permissions_role_id FOREIGN KEY (role_id) REFERENCES roles(id);
ALTER TABLE role_permissions ADD CONSTRAINT fk_role_permissions_permission_id FOREIGN KEY (permission_id) REFERENCES permissions(id);
ALTER TABLE device_tokens ADD CONSTRAINT fk_device_tokens_user_id FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE hotel_images ADD CONSTRAINT fk_hotel_images_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE hotel_amenities ADD CONSTRAINT fk_hotel_amenities_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE hotel_amenities ADD CONSTRAINT fk_hotel_amenities_amenity_id FOREIGN KEY (amenity_id) REFERENCES amenities(id);
ALTER TABLE room_types ADD CONSTRAINT fk_room_types_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE room_type_images ADD CONSTRAINT fk_room_type_images_room_type_id FOREIGN KEY (room_type_id) REFERENCES room_types(id);
ALTER TABLE room_type_amenities ADD CONSTRAINT fk_room_type_amenities_room_type_id FOREIGN KEY (room_type_id) REFERENCES room_types(id);
ALTER TABLE room_type_amenities ADD CONSTRAINT fk_room_type_amenities_amenity_id FOREIGN KEY (amenity_id) REFERENCES amenities(id);
ALTER TABLE rooms ADD CONSTRAINT fk_rooms_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE rooms ADD CONSTRAINT fk_rooms_room_type_id FOREIGN KEY (room_type_id) REFERENCES room_types(id);
ALTER TABLE cancellation_policies ADD CONSTRAINT fk_cancellation_policies_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE rate_plans ADD CONSTRAINT fk_rate_plans_room_type_id FOREIGN KEY (room_type_id) REFERENCES room_types(id);
ALTER TABLE rate_plans ADD CONSTRAINT fk_rate_plans_cancellation_policy_id FOREIGN KEY (cancellation_policy_id) REFERENCES cancellation_policies(id);
ALTER TABLE room_inventories ADD CONSTRAINT fk_room_inventories_room_type_id FOREIGN KEY (room_type_id) REFERENCES room_types(id);
ALTER TABLE promotions ADD CONSTRAINT fk_promotions_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE promotion_room_types ADD CONSTRAINT fk_promotion_room_types_promotion_id FOREIGN KEY (promotion_id) REFERENCES promotions(id);
ALTER TABLE promotion_room_types ADD CONSTRAINT fk_promotion_room_types_room_type_id FOREIGN KEY (room_type_id) REFERENCES room_types(id);
ALTER TABLE vouchers ADD CONSTRAINT fk_vouchers_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE vouchers ADD CONSTRAINT fk_vouchers_min_tier_id FOREIGN KEY (min_tier_id) REFERENCES member_tiers(id);
ALTER TABLE user_vouchers ADD CONSTRAINT fk_user_vouchers_user_id FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE user_vouchers ADD CONSTRAINT fk_user_vouchers_voucher_id FOREIGN KEY (voucher_id) REFERENCES vouchers(id);
ALTER TABLE bookings ADD CONSTRAINT fk_bookings_user_id FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE bookings ADD CONSTRAINT fk_bookings_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE bookings ADD CONSTRAINT fk_bookings_promotion_id FOREIGN KEY (promotion_id) REFERENCES promotions(id);
ALTER TABLE bookings ADD CONSTRAINT fk_bookings_voucher_id FOREIGN KEY (voucher_id) REFERENCES vouchers(id);
ALTER TABLE booking_rooms ADD CONSTRAINT fk_booking_rooms_booking_id FOREIGN KEY (booking_id) REFERENCES bookings(id);
ALTER TABLE booking_rooms ADD CONSTRAINT fk_booking_rooms_room_type_id FOREIGN KEY (room_type_id) REFERENCES room_types(id);
ALTER TABLE booking_rooms ADD CONSTRAINT fk_booking_rooms_rate_plan_id FOREIGN KEY (rate_plan_id) REFERENCES rate_plans(id);
ALTER TABLE booking_rooms ADD CONSTRAINT fk_booking_rooms_room_id FOREIGN KEY (room_id) REFERENCES rooms(id);
ALTER TABLE booking_room_nights ADD CONSTRAINT fk_booking_room_nights_booking_room_id FOREIGN KEY (booking_room_id) REFERENCES booking_rooms(id);
ALTER TABLE services ADD CONSTRAINT fk_services_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE booking_services ADD CONSTRAINT fk_booking_services_booking_id FOREIGN KEY (booking_id) REFERENCES bookings(id);
ALTER TABLE booking_services ADD CONSTRAINT fk_booking_services_service_id FOREIGN KEY (service_id) REFERENCES services(id);
ALTER TABLE booking_status_histories ADD CONSTRAINT fk_booking_status_histories_booking_id FOREIGN KEY (booking_id) REFERENCES bookings(id);
ALTER TABLE payments ADD CONSTRAINT fk_payments_booking_id FOREIGN KEY (booking_id) REFERENCES bookings(id);
ALTER TABLE payments ADD CONSTRAINT fk_payments_collected_by FOREIGN KEY (collected_by) REFERENCES admin_users(id);
ALTER TABLE refunds ADD CONSTRAINT fk_refunds_payment_id FOREIGN KEY (payment_id) REFERENCES payments(id);
ALTER TABLE refunds ADD CONSTRAINT fk_refunds_booking_id FOREIGN KEY (booking_id) REFERENCES bookings(id);
ALTER TABLE refunds ADD CONSTRAINT fk_refunds_requested_by FOREIGN KEY (requested_by) REFERENCES admin_users(id);
ALTER TABLE refunds ADD CONSTRAINT fk_refunds_approved_by FOREIGN KEY (approved_by) REFERENCES admin_users(id);
ALTER TABLE voucher_usages ADD CONSTRAINT fk_voucher_usages_voucher_id FOREIGN KEY (voucher_id) REFERENCES vouchers(id);
ALTER TABLE voucher_usages ADD CONSTRAINT fk_voucher_usages_user_id FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE voucher_usages ADD CONSTRAINT fk_voucher_usages_booking_id FOREIGN KEY (booking_id) REFERENCES bookings(id);
ALTER TABLE point_transactions ADD CONSTRAINT fk_point_transactions_user_id FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE point_transactions ADD CONSTRAINT fk_point_transactions_booking_id FOREIGN KEY (booking_id) REFERENCES bookings(id);
ALTER TABLE point_transactions ADD CONSTRAINT fk_point_transactions_created_by FOREIGN KEY (created_by) REFERENCES admin_users(id);
ALTER TABLE reviews ADD CONSTRAINT fk_reviews_booking_id FOREIGN KEY (booking_id) REFERENCES bookings(id);
ALTER TABLE reviews ADD CONSTRAINT fk_reviews_user_id FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE reviews ADD CONSTRAINT fk_reviews_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE reviews ADD CONSTRAINT fk_reviews_replied_by FOREIGN KEY (replied_by) REFERENCES admin_users(id);
ALTER TABLE review_images ADD CONSTRAINT fk_review_images_review_id FOREIGN KEY (review_id) REFERENCES reviews(id);
ALTER TABLE favorites ADD CONSTRAINT fk_favorites_user_id FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE favorites ADD CONSTRAINT fk_favorites_hotel_id FOREIGN KEY (hotel_id) REFERENCES hotels(id);
ALTER TABLE notifications ADD CONSTRAINT fk_notifications_user_id FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE system_settings ADD CONSTRAINT fk_system_settings_updated_by FOREIGN KEY (updated_by) REFERENCES admin_users(id);
ALTER TABLE audit_logs ADD CONSTRAINT fk_audit_logs_admin_user_id FOREIGN KEY (admin_user_id) REFERENCES admin_users(id);
