# Review UI v1 → v2 & design system

> Trích từ tab "Review & design system" của HotelBooking_Mockup.html. Áp dụng chung cho Mobile và Web Admin.

## 19 vấn đề đã sửa

| # | Mức | Vị trí | Vấn đề ở v1 | Cách sửa ở v2 |
| --- | --- | --- | --- | --- |
| 1 | Cao | Toàn bộ | Icon bằng emoji: mỗi hệ điều hành vẽ khác nhau, không đổi màu theo trạng thái, trông nghiệp dư. | Bộ icon SVG nét 1,75px thống nhất (64 icon), màu theo currentColor. Dev dùng lucide-react / lucide-react-native cùng tên. |
| 2 | Cao | Mobile | Chữ 10–11px ở nhiều chỗ (giá, ghi chú, tab bar): khó đọc, dưới mức khuyến nghị iOS/Android. | Thang chữ cố định: 24/20/17/15/13/12px. Không dùng dưới 12px. Giá dùng số tabular để thẳng cột. |
| 3 | Cao | Mobile | Vùng chạm 16–24px (nút back, checkbox, stepper) — dưới chuẩn 44×44pt, dễ bấm nhầm. | Mọi nút/ô chọn có vùng chạm ≥ 44px; nút chính cao 48px; ô nhập cao 48px. |
| 4 | Cao | Toàn bộ | Không có đa ngôn ngữ; chuỗi viết cứng; ngày/tiền định dạng thủ công kiểu Việt. | Chuyển VI/JA tức thì (MB-01, MB-16b, thanh admin). Ngày & tiền định dạng bằng Intl theo locale: “T6, 20/11” ⇄ “11/20(金)”, “3.415.400 ₫” ⇄ “3,415,400₫”. |
| 5 | Cao | MB-08 | Không có màn thanh toán thất bại; khách không biết đã bị trừ tiền chưa, có giữ phòng không. | Thêm MB-08e: nêu lý do theo mã ngân hàng, khẳng định chưa trừ tiền, giữ đồng hồ, nút Thử lại / Đổi phương thức. |
| 6 | Cao | MB-04 | Không có trạng thái hết phòng; danh sách rỗng là ngõ cụt. | Thêm MB-04e: gợi ý đổi ngày, khách sạn khác, ngày gần nhất còn phòng. |
| 7 | Cao | MB-11 | Hủy phòng chỉ là một nút đỏ; không xác nhận lại, không bắt chọn lý do. | Thêm bottom sheet MB-11c: hiện phí hủy/tiền hoàn từ API cancel-preview, bắt buộc lý do, nút an toàn “Giữ đặt phòng” bên trái. |
| 8 | Cao | Toàn bộ | Màu không thống nhất: chàm/tím, xanh lá, cam, xanh dương cùng xuất hiện; chàm và xanh dương là màu Thủy — kỵ mệnh Hỏa. | Một tông duy nhất theo mệnh Hỏa: đỏ son cho mọi hành động, nâu gụ cho chữ & sidebar (thay navy), vàng đồng chỉ cho hạng thành viên, trạng thái dùng dải ấm (đỏ thẫm, cam đất, nâu). Không dùng xanh dương, xanh lá, tím, đen tuyền. |
| 9 | TB | Toàn bộ | Chữ phụ #8A97A6 trên nền trắng chỉ ~3:1, không đạt WCAG AA. | Chữ phụ #7A615A (5,7:1), chữ thứ cấp #5C4640 (8,6:1) — đều là nâu ấm cùng tông. |
| 10 | TB | MB-03 | Ô ngày chỉ là text, không có màn chọn ngày; không thấy giá theo ngày. | Thêm MB-03b lịch khoảng ngày: giá thấp nhất dưới mỗi ngày, ngày hết phòng gạch ngang. |
| 11 | TB | MB-02 | Form không có nhãn (chỉ placeholder), không có trạng thái lỗi; thiếu màn OTP. | Nhãn luôn hiện trên ô; lỗi hiển thị dưới ô kèm số lần thử còn lại; thêm MB-02b OTP 6 ô có đếm ngược. |
| 12 | TB | MB-09/11 | Mã đặt phòng và QR không nổi bật, lẫn với thông tin khác. | Thiết kế “thẻ chìa khóa”: mã lớn, vạch xé, ngày nhận/trả, QR — điểm nhấn thị giác duy nhất của app. |
| 13 | TB | Mobile | Ảnh chỉ là dải gradient, không thể hiện phòng/khách sạn. | Minh họa theo ngữ cảnh (biển, phố, đèn lồng, phòng, suite, hồ bơi) làm chỗ cho ảnh thật; tỉ lệ ảnh cố định 16:9 / 4:3. |
| 14 | TB | Tab bar | Tên tab “Đặt phòng” trùng với nút “Đặt phòng” — người dùng nhầm là tạo đặt phòng mới. | Đổi tab thành “Chuyến đi”; nút hành động giữ “Đặt phòng”. |
| 15 | TB | Admin | Không có màn đăng nhập, đặt tại quầy, loại phòng, gói giá, dịch vụ, khách hàng, thanh toán, chi nhánh, phân quyền, cấu hình. | Bổ sung đủ AD-01 → AD-18 với cùng khuôn: thanh trên, sidebar nhóm theo nghiệp vụ, danh sách + drawer sửa. |
| 16 | TB | AD-06 | Không biết ô nào đã sửa chưa lưu; không có chú giải ngừng bán. | Ô sửa có viền đỏ son; nút “Lưu 1 thay đổi” đếm số ô; chú giải màu đầy đủ. |
| 17 | Thấp | Admin | Sidebar dùng ký tự ▤ ▦ thay icon; nhóm menu viết hoa toàn bộ khó đọc. | Icon SVG, nhóm menu chữ thường, có bộ chọn chi nhánh ở đầu sidebar. |
| 18 | Thấp | Toàn bộ | Giá có lúc “đ”, lúc “VND”, lúc “tr”, lúc “k”. | Một quy tắc: số đầy đủ + “₫”; số rút gọn chỉ ở KPI admin (Intl compact). |
| 19 | Thấp | Toàn bộ | Không có trạng thái focus bàn phím cho web admin. | Viền focus 2px đỏ son cho mọi phần tử tương tác. |

## Quy ước

### Chữ

Be Vietnam Pro cho tiếng Việt (thiết kế riêng cho dấu tiếng Việt), Noto Sans JP cho tiếng Nhật. Thang: 24 / 20 / 17 / 15 / 13 / 12 px. Số tiền dùng tabular-nums.

### Khoảng cách & bo góc

Lưới 4px (4 · 8 · 12 · 16 · 24). Bo góc theo cấp: 8 ô nhỏ, 12 ô nhập/ảnh, 18 thẻ, 24 bottom sheet. Nút chính cao 48px, vùng chạm tối thiểu 44px.

### Quy tắc đa ngôn ngữ cho dev

Mọi chuỗi qua i18n key (packages/i18n/vi.json, ja.json). Không ghép chuỗi; dùng tham số {count}, {date}. Ngày và tiền định dạng bằng Intl theo locale, không tự viết. Dữ liệu master có cột _ja; thiếu bản dịch thì hiển thị bản VI. Bố cục chừa 30% chiều dài cho chuỗi tiếng Nhật/Việt dài hơn.

### Chú thích số

Bật “Chú thích” ở thanh trên để hiện số hồng trên từng phần tử. Số này trùng với cột No trong file HotelBooking_ScreenSpec.xlsx (画面項目定義).

Màu chi tiết: xem `mobile/tokens/design-tokens.json`.
