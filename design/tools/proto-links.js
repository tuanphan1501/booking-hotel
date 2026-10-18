// Liên kết giữa các màn trong prototype Web Admin (design/web-admin/).
// Mỗi rule xác định 1 phần tử trong màn hình và việc xảy ra khi bấm:
//   t: chuỗi chữ (tiếng Việt, như trong mockup) mà phần tử chứa
//   s: CSS selector giới hạn phạm vi tìm (VD ".btn", ".card", "td.link")
//   i: tên icon (VD "i-bell") — dùng cho nút chỉ có icon
//   n: lấy kết quả thứ n (mặc định 1) · all: true → áp cho mọi kết quả
//   go: màn đích (VD "AD-04", hoặc "AD-04?toast=Đã tạo") · toast: chỉ hiện thông báo
// Phần tử tìm được sẽ "leo" lên phần tử bấm được gần nhất (button, a, .btn, .card, .list-row, .chip…).

const T = (msg) => ({ toast: msg });

// thứ tự mục ở sidebar admin
const sideOrder = [
  "AD-02",
  "AD-03",
  "AD-05",
  "AD-08",
  "AD-06",
  "AD-07",
  "AD-09",
  "AD-10",
  "AD-11",
  "AD-12",
  "AD-13",
  "AD-14",
  "AD-15",
  "AD-16",
  "AD-17",
  "AD-18",
];
const SAVED = T("Đã lưu thay đổi");
const admin = {
  "AD-01": [
    { s: ".btn", t: "Đăng nhập", go: "AD-02" },
    { t: "Quên mật khẩu", ...T("Liên hệ Super Admin để đặt lại mật khẩu") },
  ],
  "AD-02": [
    { t: "Xem tất cả", go: "AD-03" },
    { s: "td.link", all: true, go: "AD-04" },
  ],
  "AD-03": [
    { s: ".btn", t: "Xuất CSV", ...T("Đang xuất CSV…") },
    { s: ".btn", t: "Đặt tại quầy", go: "AD-05" },
    { t: "Xóa lọc", ...T("Đã xóa bộ lọc") },
    { s: "td.link", all: true, go: "AD-04" },
  ],
  "AD-04": [
    { s: ".btn", t: "Hủy đặt phòng", ...T("Đã hủy · yêu cầu hoàn tiền chờ duyệt ở Thanh toán (AD-14)") },
    { s: ".btn", t: "Sửa", ...T("Mở form sửa thông tin khách") },
    { s: ".btn", t: "Nhận phòng", go: "AD-08?toast=Đã check-in · phòng chuyển Đang ở" },
    { s: ".btn", t: "Thêm dịch vụ phát sinh", ...T("Thêm dịch vụ phát sinh") },
    { s: ".btn", t: "Ghi nhận thanh toán", ...T("Đã ghi nhận thanh toán tại quầy") },
  ],
  "AD-05": [{ s: ".btn", t: "Tạo đặt phòng", go: "AD-04?toast=Đã tạo đặt phòng tại quầy" }],
  "AD-06": [
    { s: ".btn", t: "Sửa hàng loạt", ...T("Mở form sửa hàng loạt") },
    { s: ".btn", t: "Lưu 1 thay đổi", ...SAVED },
  ],
  "AD-07": [
    { s: ".btn", t: "Thêm loại phòng", ...T("Form thêm loại phòng") },
    { s: ".btn", t: "Hủy", ...T("Đã hủy chỉnh sửa") },
    { s: ".btn", t: "Lưu thay đổi", ...SAVED },
  ],
  "AD-08": [{ s: ".btn", t: "Thêm phòng", ...T("Form thêm phòng") }],
  "AD-09": [
    { s: ".btn", t: "Thêm gói giá", ...T("Form thêm gói giá") },
    { s: ".btn", t: "Thêm chính sách", ...T("Form thêm chính sách hủy") },
  ],
  "AD-10": [{ s: ".btn", t: "Thêm dịch vụ", ...T("Form thêm dịch vụ") }],
  "AD-11": [
    { s: ".btn", t: "Hủy", ...T("Đã hủy chỉnh sửa") },
    { s: ".btn", t: "Lưu voucher", ...T("Đã lưu voucher") },
  ],
  "AD-12": [
    { s: ".btn", t: "Ẩn đánh giá", ...T("Đã ẩn đánh giá") },
    { s: ".btn", t: "Duyệt", ...T("Đã duyệt & đăng phản hồi · khách được +50 điểm") },
  ],
  "AD-13": [
    { s: ".btn", t: "Khóa tài khoản", ...T("Đã khóa tài khoản") },
    { s: ".btn", t: "Điều chỉnh điểm", ...T("Form điều chỉnh điểm") },
    { s: "td.link", all: true, go: "AD-04" },
  ],
  "AD-14": [
    { s: "td.link", all: true, go: "AD-04" },
    { s: ".btn", t: "Đối soát", ...T("Đang đối soát với cổng thanh toán…") },
  ],
  "AD-15": [{ s: ".btn", t: "Xuất Excel", ...T("Đang xuất Excel…") }],
  "AD-16": [{ s: ".btn", t: "Lưu thay đổi", ...SAVED }],
  "AD-17": [{ s: ".btn", t: "Mời admin", ...T("Đã gửi lời mời qua email") }],
  "AD-18": [{ s: ".btn", t: "Lưu cấu hình", ...SAVED }],
};
for (const id in admin) {
  sideOrder.forEach((to, k) => admin[id].push({ s: ".side a", n: k + 1, go: to }));
  admin[id].push(
    { s: ".side .logo", go: "AD-02" },
    { s: ".avatar", go: "AD-01?toast=Đã đăng xuất" },
    { s: ".abar .ib", ...T("3 thông báo mới") }
  );
}

module.exports = admin;
