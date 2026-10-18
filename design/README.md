# design/ — Thiết kế Lumière Booking

Tách từ `HotelBooking_Mockup.html` thành 2 phần độc lập:

| Thư mục | Dành cho | Dạng |
| --- | --- | --- |
| `web-admin/` | Dev web + demo khách | **Prototype HTML/CSS/JS bấm được** — mỗi màn AD-01…AD-18 một page (`pages/AD-xx.html`), menu/nút chuyển trang thật. Mở `web-admin/index.html`. |
| `mobile/` | Dev Flutter | **Bộ thiết kế (không phải HTML)**: ảnh PNG 22 màn, spec Markdown từng màn, design tokens, theme Flutter, icon SVG. Mở `mobile/README.md`. |
| `docs/ui-review.md` | Cả hai | Review UI v1 → v2 và quy ước design system |
| `tools/` | Người cập nhật | Script build + dữ liệu spec / liên kết |

## Công nghệ đề xuất (theo Basic Design §2.1)

| Phần | Ngôn ngữ / framework | Lý do |
| --- | --- | --- |
| Mobile app | **Dart – Flutter 3.27+**, Riverpod, go_router, Dio | 1 codebase iOS + Android, UI tùy biến sát thiết kế, máy dev đã có Flutter SDK |
| Web admin | **TypeScript – Next.js 15 (React) + Ant Design** | Bảng/lưới/form nhiều → Ant Design có sẵn; cùng TypeScript với backend |
| Backend API | **TypeScript – NestJS** + PostgreSQL 16 + Redis | Transaction chặt (khóa tồn kho), OpenAPI sinh client cho app & admin |

Nếu team mạnh PHP: thay Web admin + API bằng **Laravel 11** (admin dùng Filament hoặc Inertia + React) — DB và API không đổi.

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
