# Review UI/UX Web Admin — chuẩn quốc tế

Ngày review: 2026-10-18 · Người thực hiện: PH.Tuan · Phạm vi: AD-01 Đăng nhập, AD-02 Tổng quan, AD-19 Cài đặt cá nhân (đã code trong `backend/`).

Tiêu chí: i18n/l10n, WCAG 2.1 AA, responsive, định dạng dữ liệu theo vùng, an toàn khi upload.

## Kết quả

| # | Hạng mục | Vấn đề | Mức | Trạng thái |
| --- | --- | --- | --- | --- |
| 1 | i18n | Chỉ có VI / JA — thiếu tiếng Anh cho khách và nhân sự quốc tế (Basic Design yêu cầu app có `en`) | Cao | ✅ Thêm `en` cho Web Admin và thông báo lỗi API (`lang/en/*`) |
| 2 | Responsive | Dưới 1100 px menu bị ẩn hoàn toàn, không điều hướng được | Cao | ✅ Nút ☰ mở menu dạng ngăn kéo; đóng bằng Esc / bấm nền mờ |
| 3 | l10n | Thời gian không ghi múi giờ | TB | ✅ Hiển thị kèm `(UTC+07:00)`; múi giờ hệ thống `APP_TIMEZONE=Asia/Ho_Chi_Minh` |
| 4 | A11y (WCAG 2.4.1) | Thiếu link "Bỏ qua tới nội dung" | TB | ✅ Skip link hiện khi nhấn Tab |
| 5 | Nội dung | ADR / RevPAR khó hiểu với người mới | Thấp | ✅ `<abbr>` + giải thích đã dịch |
| 6 | l10n | Tên vai trò / hạng thành viên chỉ có VI, JA | Thấp | ✅ Ngôn ngữ khác dùng nhãn dịch của Enum |
| 7 | Upload | Ảnh đại diện có thể méo, nặng, sai tỉ lệ | TB | ✅ Hộp thoại xem lại & cắt ảnh (kéo, phóng to, xem trước 3 cỡ), xuất WebP 512×512 |
| 8 | Bảo mật upload | SVG có thể chứa script | Cao | ✅ Chỉ nhận JPG/PNG/WebP ≤ 2 MB, ≥ 96×96 px (kiểm tra cả trình duyệt và server) |

## Đã đạt sẵn

- `<html lang>` đổi theo ngôn ngữ; font Noto Sans JP cho tiếng Nhật, Be Vietnam Pro cho VI/EN.
- Không ghép chuỗi; mọi chữ qua key dịch (đã kiểm tra: không còn chuỗi viết cứng trong view).
- Tiền: vi `3.415.400 ₫` · ja `3,415,400₫` · en `3,415,400 ₫`; ngày theo locale (Carbon isoFormat).
- Ô nhập có nhãn luôn hiện, lỗi nằm dưới ô (`aria-invalid`, `aria-describedby`); vùng chạm ≥ 36–44 px; viền focus 2 px.
- Màu chữ phụ đạt tương phản AA (5,7:1); trạng thái không chỉ dựa vào màu (luôn có chữ / icon).
- Tôn trọng `prefers-reduced-motion`.
- Cảnh báo khi rời trang mà còn thay đổi chưa lưu (form Cài đặt cá nhân).

## Việc tiếp theo đề xuất

- Màn bật 2FA (hiện mã QR + mã dự phòng) trong AD-19; màn đổi mật khẩu.
- Chọn múi giờ hiển thị theo từng admin (`admin_users.timezone`) khi có chi nhánh ngoài Việt Nam.
- Kiểm thử với trình đọc màn hình (NVDA / VoiceOver) và Lighthouse Accessibility ≥ 95 trước khi phát hành.
