# WBS & lịch trình — Vivu Stay

Kế hoạch triển khai từ **20/10/2026** đến **18/01/2027** (64 ngày làm việc ≈ 13 tuần).

- **1 dòng = 1 ngày làm việc (8 giờ)** của 1 developer, làm T2–T6; nghỉ 01/01/2027.
- Mỗi task xong khi: code đã format, có test, `flutter analyze` / Pint / PHPStan sạch, đã tạo PR vào `develop`.
- Cột **Trạng thái**: ☐ chưa làm · ◐ đang làm · ☑ xong. Cập nhật khi merge PR.
- Bản CSV để mở bằng Excel / import Jira: [wbs.csv](wbs.csv).

## Đã xong (17–19/10/2026)

| Hạng mục  | Kết quả                                                                                               |
| --------- | ----------------------------------------------------------------------------------------------------- |
| Thiết kế  | Prototype Web Admin AD-01…AD-19, design kit mobile MB-01…MB-16b, logo Vivu Stay                       |
| Backend   | Laravel 13 + PostgreSQL + Redis, 40 migration, seeder dữ liệu demo, CI                                |
| Web Admin | AD-01 Đăng nhập (2FA), AD-02 Tổng quan, AD-19 Cài đặt cá nhân (avatar)                                |
| Mobile    | Khung Flutter, MB-01 Chào mừng, MB-02 Đăng nhập, MB-03 Trang chủ, MB-16 / MB-16b Tài khoản & ngôn ngữ |

## Tổng quan giai đoạn

| Giai đoạn                           | Số ngày | Bắt đầu    | Kết thúc   |
| ----------------------------------- | ------: | ---------- | ---------- |
| 1. Hoàn thiện đăng nhập / tài khoản |       5 | 20/10/2026 | 26/10/2026 |
| 2. Tìm kiếm & khách sạn             |       9 | 27/10/2026 | 06/11/2026 |
| 3. Đặt phòng & thanh toán           |       8 | 09/11/2026 | 18/11/2026 |
| 4. Chuyến đi & thành viên           |       9 | 19/11/2026 | 01/12/2026 |
| 5. Web Admin vận hành               |      21 | 02/12/2026 | 30/12/2026 |
| 6. Kiểm thử & phát hành             |      12 | 31/12/2026 | 18/01/2027 |

## 1. Hoàn thiện đăng nhập / tài khoản

|   # | Ngày          | Tuần | Task                           | Việc trong ngày                                                                           | Phần         | Trạng thái |
| --: | ------------- | ---: | ------------------------------ | ----------------------------------------------------------------------------------------- | ------------ | :--------: |
|   1 | T3 20/10/2026 |    1 | API đăng ký + OTP              | POST /auth/otp, /auth/register; bảng otp_codes, rate-limit, mail/SMS log; feature test    | API          |     ☐      |
|   2 | T4 21/10/2026 |    1 | MB-02 tab Đăng ký + MB-02b OTP | Form đăng ký, đồng ý điều khoản, màn OTP 6 số, đếm ngược gửi lại; widget test             | Mobile       |     ☐      |
|   3 | T5 22/10/2026 |    1 | Quên / đặt lại mật khẩu        | API reset bằng OTP + màn đặt mật khẩu mới trên app                                        | API + Mobile |     ☐      |
|   4 | T6 23/10/2026 |    1 | Đăng nhập Google               | POST /auth/social (verify id_token), google_sign_in Android/iOS/web — cần OAuth Client ID | API + Mobile |     ☐      |
|   5 | T2 26/10/2026 |    2 | Đăng nhập Apple                | sign_in_with_apple, verify token Apple — cần Apple Developer                              | API + Mobile |     ☐      |

## 2. Tìm kiếm & khách sạn

|   # | Ngày          | Tuần | Task                                  | Việc trong ngày                                                             | Phần   | Trạng thái |
| --: | ------------- | ---: | ------------------------------------- | --------------------------------------------------------------------------- | ------ | :--------: |
|   6 | T3 27/10/2026 |    2 | API trang chủ                         | GET /home: banner, ưu đãi, khách sạn nổi bật (cache Redis); resource + test | API    |     ☐      |
|   7 | T4 28/10/2026 |    2 | MB-03 nối API + MB-03b chọn ngày      | Home lấy dữ liệu thật, bottom sheet lịch, chọn khách/phòng                  | Mobile |     ☐      |
|   8 | T5 29/10/2026 |    2 | API tìm phòng trống (ngày 1)          | Query tồn kho theo khoảng ngày, số khách, số phòng; index DB                | API    |     ☐      |
|   9 | T6 30/10/2026 |    2 | API tìm phòng trống (ngày 2)          | Tính giá theo đêm + khuyến mãi, sắp xếp/lọc, phân trang; test               | API    |     ☐      |
|  10 | T2 02/11/2026 |    3 | MB-04 Kết quả tìm kiếm                | Danh sách, sắp xếp, bộ lọc, skeleton loading                                | Mobile |     ☐      |
|  11 | T3 03/11/2026 |    3 | MB-04e Trạng thái rỗng + lọc nâng cao | Empty state, gợi ý đổi ngày, lưu bộ lọc                                     | Mobile |     ☐      |
|  12 | T4 04/11/2026 |    3 | API chi tiết khách sạn                | GET /hotels/:id, loại phòng, gói giá, tiện nghi, ảnh                        | API    |     ☐      |
|  13 | T5 05/11/2026 |    3 | MB-05 Chi tiết khách sạn              | Gallery ảnh, tiện nghi, đánh giá, bản đồ                                    | Mobile |     ☐      |
|  14 | T6 06/11/2026 |    3 | MB-06 Loại phòng & gói giá            | Chọn gói giá, chính sách hủy, giữ chỗ → returnTo khi chưa login             | Mobile |     ☐      |

## 3. Đặt phòng & thanh toán

|   # | Ngày          | Tuần | Task                                 | Việc trong ngày                                              | Phần   | Trạng thái |
| --: | ------------- | ---: | ------------------------------------ | ------------------------------------------------------------ | ------ | :--------: |
|  15 | T2 09/11/2026 |    4 | API tạo booking (ngày 1)             | Giữ phòng (lock tồn kho), mã booking, hết hạn giữ chỗ (job)  | API    |     ☐      |
|  16 | T3 10/11/2026 |    4 | API tạo booking (ngày 2)             | Áp voucher, dịch vụ thêm, thuế phí, idempotency key; test    | API    |     ☐      |
|  17 | T4 11/11/2026 |    4 | MB-07 Thông tin khách & dịch vụ thêm | Form khách, yêu cầu đặc biệt, chọn dịch vụ, chọn voucher     | Mobile |     ☐      |
|  18 | T5 12/11/2026 |    4 | Cổng thanh toán (ngày 1)             | Tích hợp VNPay sandbox: tạo URL, chữ ký, return URL          | API    |     ☐      |
|  19 | T6 13/11/2026 |    4 | Cổng thanh toán (ngày 2)             | IPN/webhook, đối soát trạng thái, MoMo (nếu cần); test       | API    |     ☐      |
|  20 | T2 16/11/2026 |    5 | MB-08 Xác nhận & thanh toán          | Tóm tắt giá, mở cổng thanh toán (WebView), đếm ngược giữ chỗ | Mobile |     ☐      |
|  21 | T3 17/11/2026 |    5 | MB-08e Thất bại + MB-09 Thành công   | Xử lý kết quả, thử lại, chia sẻ mã booking                   | Mobile |     ☐      |
|  22 | T4 18/11/2026 |    5 | Email xác nhận đặt phòng             | Mail template vi/ja/en qua queue, file PDF xác nhận          | API    |     ☐      |

## 4. Chuyến đi & thành viên

|   # | Ngày          | Tuần | Task                             | Việc trong ngày                                               | Phần         | Trạng thái |
| --: | ------------- | ---: | -------------------------------- | ------------------------------------------------------------- | ------------ | :--------: |
|  23 | T5 19/11/2026 |    5 | API chuyến đi                    | GET /bookings (sắp tới/đã qua/đã hủy), chi tiết booking       | API          |     ☐      |
|  24 | T6 20/11/2026 |    5 | API hủy booking + hoàn tiền      | Tính phí theo chính sách hủy, tạo yêu cầu hoàn tiền; test     | API          |     ☐      |
|  25 | T2 23/11/2026 |    6 | MB-10 Chuyến đi + MB-11 Chi tiết | Tab chuyến đi, chi tiết booking, QR check-in                  | Mobile       |     ☐      |
|  26 | T3 24/11/2026 |    6 | MB-11c Xác nhận hủy              | Bottom sheet hủy, hiển thị phí/hoàn tiền, cập nhật trạng thái | Mobile       |     ☐      |
|  27 | T4 25/11/2026 |    6 | MB-12 Viết đánh giá              | API + màn đánh giá sao, nội dung, ảnh                         | API + Mobile |     ☐      |
|  28 | T5 26/11/2026 |    6 | MB-13 Thành viên & điểm          | API hạng thành viên, lịch sử điểm + màn hình                  | API + Mobile |     ☐      |
|  29 | T6 27/11/2026 |    6 | MB-14 Ví voucher                 | API voucher của tôi, lưu voucher, chọn khi đặt phòng          | API + Mobile |     ☐      |
|  30 | T2 30/11/2026 |    7 | Push notification (ngày 1)       | Firebase FCM: đăng ký device token, gửi qua queue             | API + Mobile |     ☐      |
|  31 | T3 01/12/2026 |    7 | MB-15 Thông báo (ngày 2)         | Danh sách thông báo, đã đọc, mở đúng màn hình (deep link)     | API + Mobile |     ☐      |

## 5. Web Admin vận hành

|   # | Ngày          | Tuần | Task                              | Việc trong ngày                                                   | Phần      | Trạng thái |
| --: | ------------- | ---: | --------------------------------- | ----------------------------------------------------------------- | --------- | :--------: |
|  32 | T4 02/12/2026 |    7 | Khung màn danh sách Admin         | Component bảng dữ liệu, bộ lọc, phân trang, export CSV dùng chung | Web Admin |     ☐      |
|  33 | T5 03/12/2026 |    7 | AD-03 Danh sách đặt phòng         | Lọc theo trạng thái/ngày/khách sạn, tìm kiếm                      | Web Admin |     ☐      |
|  34 | T6 04/12/2026 |    7 | AD-04 Chi tiết đặt phòng (ngày 1) | Thông tin booking, lịch sử, check-in/check-out                    | Web Admin |     ☐      |
|  35 | T2 07/12/2026 |    8 | AD-04 Chi tiết đặt phòng (ngày 2) | Hủy, hoàn tiền, ghi chú nội bộ, audit log                         | Web Admin |     ☐      |
|  36 | T3 08/12/2026 |    8 | AD-05 Đặt phòng tại quầy          | Tạo booking walk-in, thu tiền mặt/thẻ                             | Web Admin |     ☐      |
|  37 | T4 09/12/2026 |    8 | AD-08 Phòng & tình trạng buồng    | Sơ đồ phòng, trạng thái dọn phòng                                 | Web Admin |     ☐      |
|  38 | T5 10/12/2026 |    8 | AD-06 Giá & tồn kho (ngày 1)      | Lịch giá theo ngày, sửa từng ô                                    | Web Admin |     ☐      |
|  39 | T6 11/12/2026 |    8 | AD-06 Giá & tồn kho (ngày 2)      | Cập nhật hàng loạt theo khoảng ngày, đóng/mở bán                  | Web Admin |     ☐      |
|  40 | T2 14/12/2026 |    9 | AD-07 Loại phòng                  | CRUD loại phòng, upload ảnh, tiện nghi                            | Web Admin |     ☐      |
|  41 | T3 15/12/2026 |    9 | AD-09 Gói giá & chính sách hủy    | CRUD gói giá, quy tắc phí hủy                                     | Web Admin |     ☐      |
|  42 | T4 16/12/2026 |    9 | AD-10 Dịch vụ thêm                | CRUD dịch vụ, giá, áp dụng theo khách sạn                         | Web Admin |     ☐      |
|  43 | T5 17/12/2026 |    9 | AD-11 Khuyến mãi & voucher        | CRUD khuyến mãi, phát hành voucher, giới hạn sử dụng              | Web Admin |     ☐      |
|  44 | T6 18/12/2026 |    9 | AD-12 Đánh giá                    | Duyệt/ẩn đánh giá, phản hồi khách                                 | Web Admin |     ☐      |
|  45 | T2 21/12/2026 |   10 | AD-13 Khách hàng & điểm           | Danh sách khách, cộng/trừ điểm, khóa tài khoản                    | Web Admin |     ☐      |
|  46 | T3 22/12/2026 |   10 | AD-14 Thanh toán & hoàn tiền      | Danh sách giao dịch, duyệt hoàn tiền, đối soát                    | Web Admin |     ☐      |
|  47 | T4 23/12/2026 |   10 | AD-15 Báo cáo (ngày 1)            | Doanh thu, công suất phòng (biểu đồ)                              | Web Admin |     ☐      |
|  48 | T5 24/12/2026 |   10 | AD-15 Báo cáo (ngày 2)            | Lọc theo kỳ/khách sạn, xuất Excel                                 | Web Admin |     ☐      |
|  49 | T6 25/12/2026 |   10 | AD-16 Khách sạn (chi nhánh)       | CRUD khách sạn, ảnh, vị trí bản đồ                                | Web Admin |     ☐      |
|  50 | T2 28/12/2026 |   11 | AD-17 Admin & phân quyền          | CRUD admin, gán vai trò, ma trận quyền                            | Web Admin |     ☐      |
|  51 | T3 29/12/2026 |   11 | AD-18 Cấu hình hệ thống           | Thuế phí, email, cổng thanh toán, điểm thưởng                     | Web Admin |     ☐      |
|  52 | T4 30/12/2026 |   11 | AD-02 Tổng quan — số liệu thật    | Rà soát KPI, việc cần làm theo dữ liệu thật                       | Web Admin |     ☐      |

## 6. Kiểm thử & phát hành

|   # | Ngày          | Tuần | Task                           | Việc trong ngày                                         | Phần            | Trạng thái |
| --: | ------------- | ---: | ------------------------------ | ------------------------------------------------------- | --------------- | :--------: |
|  53 | T5 31/12/2026 |   11 | CI mobile + bổ sung test       | GitHub Actions: flutter analyze/test; tăng coverage API | DevOps          |     ☐      |
|  54 | T2 04/01/2027 |   12 | Rà soát bảo mật                | Phân quyền, rate-limit, OWASP, log nhạy cảm             | API + Web Admin |     ☐      |
|  55 | T3 05/01/2027 |   12 | Tối ưu hiệu năng               | Index DB, N+1, cache, kích thước ảnh                    | API + Mobile    |     ☐      |
|  56 | T4 06/01/2027 |   12 | UAT (ngày 1)                   | Viết test case theo màn hình, test luồng đặt phòng      | QA              |     ☐      |
|  57 | T5 07/01/2027 |   12 | UAT (ngày 2)                   | Test Web Admin, đa ngôn ngữ vi/ja/en                    | QA              |     ☐      |
|  58 | T6 08/01/2027 |   12 | Sửa lỗi UAT                    | Fix bug ưu tiên cao, test lại                           | All             |     ☐      |
|  59 | T2 11/01/2027 |   13 | Deploy staging (ngày 1)        | Server, domain, SSL, Docker, biến môi trường            | DevOps          |     ☐      |
|  60 | T3 12/01/2027 |   13 | Deploy staging (ngày 2)        | Backup DB, queue worker, giám sát lỗi (Sentry)          | DevOps          |     ☐      |
|  61 | T4 13/01/2027 |   13 | Build iOS TestFlight           | Trên macOS: signing, upload TestFlight                  | Mobile          |     ☐      |
|  62 | T5 14/01/2027 |   13 | Build Android internal testing | Keystore, AAB, Google Play internal track               | Mobile          |     ☐      |
|  63 | T6 15/01/2027 |   13 | Hồ sơ lên store                | Ảnh chụp, mô tả vi/ja/en, chính sách quyền riêng tư     | Mobile          |     ☐      |
|  64 | T2 18/01/2027 |   14 | Phát hành production           | Deploy production, nộp review App Store / Google Play   | All             |     ☐      |

## Rủi ro & phụ thuộc

| Rủi ro                                                 | Ảnh hưởng           | Cách xử lý                                                               |
| ------------------------------------------------------ | ------------------- | ------------------------------------------------------------------------ |
| Chưa có Google OAuth Client ID / Apple Developer       | Task #4, #5 bị chặn | Chủ dự án tạo trước 23/10/2026; nếu chưa có thì đổi chỗ với task kế tiếp |
| Chưa có tài khoản cổng thanh toán (VNPay/MoMo sandbox) | Task #18–#21        | Đăng ký sandbox trước 12/11/2026                                         |
| Build iOS cần macOS + Xcode                            | Task #61            | Khởi động MacBook vào macOS, chạy `scripts/run-on-iphone.sh`             |
| Duyệt App Store / Google Play mất 1–7 ngày             | Ngày ra mắt         | Nộp sớm bản TestFlight / internal để được góp ý                          |
