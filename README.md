<p><img src="design/brand/vivustay-logo.svg" alt="Vivu Stay" height="48"></p>

# Vivu Stay

Hệ thống đặt phòng cho chuỗi khách sạn **Vivu Stay**: Mobile App cho khách + Web Admin cho vận hành + API dùng chung.

| Phần | Công nghệ | Thư mục |
| --- | --- | --- |
| Web Admin + API | **Laravel 13** (PHP 8.3), Blade, Sanctum, PostgreSQL 16, Redis 7 | `backend/` |
| Mobile App | **Flutter** (Dart) — iOS & Android | _(sắp có)_ · thiết kế ở `design/mobile/` |
| Thiết kế | Prototype Web Admin, bộ thiết kế Mobile, logo | `design/` |

Ngôn ngữ giao diện: Tiếng Việt · 日本語 · English.

## Chạy nhanh ở máy local

```bash
docker compose up -d --wait          # PostgreSQL :5433, Redis :6379, Mailpit :8025
cd backend && composer install && cp .env.example .env && php artisan key:generate
php artisan storage:link && php artisan migrate:fresh --seed
php artisan serve                    # http://127.0.0.1:8000/admin
```

Chi tiết, tài khoản demo và quy ước code: [`backend/README.md`](backend/README.md).

## Tài liệu

| File | Nội dung |
| --- | --- |
| `HotelBooking_BasicDesign.docx` | Thiết kế cơ bản (chức năng, màn hình, nghiệp vụ, API) |
| `HotelBooking_DatabaseDesign.xlsx`, `HotelBooking_schema.sql` | Thiết kế DB (PostgreSQL 16) |
| `HotelBooking_Mockup.html` | Mockup gốc (nguồn sinh `design/`) |
| `design/docs/ui-review.md`, `design/docs/ui-ux-review-admin.md` | Review UI và chuẩn quốc tế |
| `design/brand/` | Logo & hướng dẫn thương hiệu |

## Quy trình làm việc

- Nhánh `develop` là nhánh tích hợp; mỗi tính năng tạo nhánh riêng từ `develop` và merge qua Pull Request.
- CI (`.github/workflows/backend.yml`) chạy Pint, Larastan và test trên mỗi Pull Request.
