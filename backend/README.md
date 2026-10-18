# Vivu Stay — Backend (Laravel 13)

Web Admin (Blade, session) và API cho Mobile App (`/api/v1`, Sanctum token) trong cùng một project Laravel.

## Yêu cầu

- PHP 8.3+ với extension `pdo_pgsql`, `mbstring`, `fileinfo`, `openssl`
- Composer 2
- Docker Desktop (PostgreSQL 16, Redis 7, Mailpit) — file `../docker-compose.yml`

## Chạy ở máy local

```bash
# 1. Hạ tầng (từ thư mục gốc dự án)
docker compose up -d --wait

# 2. Backend
cd backend
composer install
cp .env.example .env        # lần đầu
php artisan key:generate    # lần đầu
php artisan storage:link    # lần đầu — phục vụ ảnh đại diện
php artisan migrate:fresh --seed
php artisan serve           # http://127.0.0.1:8000  →  /admin
```

| Dịch vụ | Địa chỉ |
| --- | --- |
| Web Admin | http://127.0.0.1:8000/admin |
| API | http://127.0.0.1:8000/api/v1 |
| Mailpit (email gửi đi) | http://localhost:8025 |
| PostgreSQL | `127.0.0.1:5433`, DB `vivustay` (test: `vivustay_test`) |

### Tài khoản demo (chỉ môi trường local/staging)

Mật khẩu chung lấy từ `SEED_DEMO_PASSWORD` trong `.env`. Email Super Admin lấy từ `SEED_SUPER_ADMIN_EMAIL`.

| Email | Vai trò | Phạm vi |
| --- | --- | --- |
| `SEED_SUPER_ADMIN_EMAIL` | Super Admin | Toàn chuỗi |
| `le.hanh@vivustay.vn` | Quản lý chi nhánh | Vivu Stay Đà Nẵng |
| `letan.danang@vivustay.vn` | Lễ tân | Vivu Stay Đà Nẵng |
| `ketoan@vivustay.vn` | Kế toán | Toàn chuỗi |
| `marketing@vivustay.vn` | Marketing | Toàn chuỗi |

Khách hàng (đăng nhập API): `minh.nguyen@gmail.com` (Gold), `yuki.tanaka@example.jp`, `mai.anh@gmail.com`, `locked.user@example.com` (bị khóa).

## Kiểm tra chất lượng

```bash
composer test        # = php artisan test (PostgreSQL vivustay_test)
vendor/bin/pint      # format (PSR-12 / Laravel preset)
vendor/bin/phpstan analyse --memory-limit=1G   # Larastan level 6
```

CI chạy cả 3 bước trên mỗi Pull Request (`.github/workflows/backend.yml`).

## Cấu trúc

```
app/
  Data/            DTO bất biến (IssuedTokens, DashboardSummary, AdminProfileData…)
  Enums/           Mã code theo thiết kế DB (BookingStatus, RoleCode, ErrorCode…)
  Exceptions/      BusinessException — lỗi nghiệp vụ có mã
  Http/
    Controllers/   Admin/* (Blade) · Api/V1/* (JSON) — chỉ điều phối
    Middleware/    SetAdminLocale · SetApiLocale (Accept-Language)
    Requests/      Validate đầu vào
    Resources/     Định dạng JSON trả về
  Models/          Eloquent (strict mode ngoài production)
  Navigation/      Định nghĩa menu admin + lọc theo quyền
  Services/        Nghiệp vụ: Auth, Dashboard, Admin profile
  Support/         PhoneNumber, MoneyFormatter, ApiError
  View/Components/ Blade component có logic (Sidebar)
database/
  migrations/      Mỗi bảng 1 migration (sinh từ HotelBooking_schema.sql) + khóa ngoại + bổ sung
  seeders/         Dữ liệu nền + dữ liệu demo (khách sạn, tồn kho, đặt phòng 15 ngày)
lang/{vi,ja,en}/   admin · errors · enums · validation
routes/            admin.php · api_v1.php · web.php
```

## Quy ước code

- Comment, tên hàm, tên biến bằng **tiếng Anh**; docblock đầy đủ `@param`, `@return`, `@throws`.
- Mỗi class có `@author` và `@created`.
- Controller mỏng → Service xử lý nghiệp vụ → lỗi ném `BusinessException(ErrorCode::…)`.
- Lỗi API luôn có dạng `{ "code", "message", "details" }`; `message` dịch theo `Accept-Language` (vi / ja / en).
- Quyền admin: mã permission (VD `booking.view`) dùng trực tiếp với `@can` / `can:` (Gate::before).
