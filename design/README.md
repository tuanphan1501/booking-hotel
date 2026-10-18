# design/ — Thiết kế Vivu Stay

Tách từ `HotelBooking_Mockup.html` thành 2 phần độc lập:

| Thư mục | Dành cho | Dạng |
| --- | --- | --- |
| `web-admin/` | Dev web + demo khách | **Prototype HTML/CSS/JS bấm được** — mỗi màn AD-01…AD-18 một page (`pages/AD-xx.html`), menu/nút chuyển trang thật. Mở `web-admin/index.html`. |
| `mobile/` | Dev Flutter | **Bộ thiết kế (không phải HTML)**: ảnh PNG 22 màn, spec Markdown từng màn, design tokens, theme Flutter, icon SVG. Mở `mobile/README.md`. |
| `docs/ui-review.md` | Cả hai | Review UI v1 → v2 và quy ước design system |
| `docs/ui-ux-review-admin.md` | Web | Review UI/UX chuẩn quốc tế của Web Admin đã code |
| `brand/` | Cả hai | Logo Vivu Stay (SVG, PNG, icon app) và hướng dẫn |
| `tools/` | Người cập nhật | Script build + dữ liệu spec / liên kết |

## Công nghệ đã chốt

| Phần | Ngôn ngữ / framework |
| --- | --- |
| Mobile app | **Dart – Flutter 3.27+**, Riverpod, go_router, Dio |
| Web admin + API | **PHP 8.3 – Laravel 13** (Blade, Sanctum) — code tại `backend/` |
| Database | PostgreSQL 16 + Redis 7 |

## Cập nhật

```
cd design
npm install              # lần đầu: cài Prettier
npm run build            # sinh lại toàn bộ + format code (cần Chrome/Edge để xuất PNG)
npm run build:no-png     # bỏ qua xuất ảnh
npm run format           # chỉ format HTML/CSS/JS
```

- Spec màn mobile: sửa `tools/mobile-specs.js`; phần chung: `tools/mobile-readme.md`.
- Liên kết prototype admin: sửa `tools/proto-links.js`.
- File viết tay (không bị build ghi đè): `web-admin/css/prototype.css`, `web-admin/js/prototype.js`, `mobile/tokens/`, `mobile/flutter/`.
- `build.js` cắt CSS/JS theo **số dòng** của file mockup gốc — nếu file gốc đổi số dòng, cập nhật các số dòng trong script.
