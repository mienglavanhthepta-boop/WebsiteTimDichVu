# NearService – Website Tìm Kiếm Và Đặt Lịch Dịch Vụ Uy Tín Gần Bạn (TP. Nha Trang)

Ứng dụng web kết nối khách hàng với các cửa hàng và thợ dịch vụ tiện ích quanh khu vực **TP. Nha Trang** (Sửa xe lưu động, Sửa điện thoại, Cắt tóc & Barber, Gia sư dạy kèm, Sửa máy tính & IT, Dọn nhà & vệ sinh, Điện lạnh & điện nước, Chăm sóc thú cưng).

---

## 👥 Bảng phân công thực hiện công việc (Contributors)

| STT | Họ và Tên | Công việc | Mức độ hoàn thành (%) |
| :-: | :---: | :--- | :-: |
| 1 | **Trưởng nhóm**<br/>**Mienglavanh Thepta** | - Thiết kế cơ sở dữ liệu và giao diện chung (`database.sql`, `config/db.php`, `includes/`, `assets/`)<br/>- Đăng nhập, Đăng ký và Hồ sơ cá nhân (`login.php`, `register.php`, `logout.php`, `profile.php`)<br/>- Kênh đối tác và Đăng/Quản lý dịch vụ (`provider_dashboard.php`, `service_form.php`)<br/>- Trang Quản trị hệ thống (`admin_dashboard.php`)<br/>- Kiểm thử và viết Báo cáo | 100% |
| 2 | **Xaiyadeth Phonexai** | - Trang chủ, Tìm kiếm và Lọc dịch vụ (`index.php`, `search.php`)<br/>- Chi tiết dịch vụ, Đặt lịch hẹn và Đánh giá (`service_detail.php`)<br/>- Trang Lịch đã đặt (`booking_history.php`)<br/>- Kiểm thử và viết Báo cáo | 100% |

---

## 🚀 Công nghệ sử dụng
- **Backend:** PHP 8.2 (PDO Prepared Statements)
- **Database:** MySQL / MariaDB (XAMPP) – Tự động khởi tạo CSDL `tim_dich_vu_db` và 56 dịch vụ mẫu tại TP. Nha Trang
- **Frontend:** HTML5, CSS3, JavaScript, Google Fonts (`Be Vietnam Pro`), Font Awesome 6

---

## ⚙️ Hướng dẫn cài đặt và chạy dự án
1. Cài đặt và mở **XAMPP Control Panel**, khởi động **Apache** và **MySQL**.
2. Đặt thư mục dự án tại `C:\xampp\htdocs\WebsiteTimDichVu`.
3. Truy cập trên trình duyệt: `http://localhost/WebsiteTimDichVu/` (Hệ thống sẽ tự động tạo database và dữ liệu mẫu).
4. **Tài khoản dùng thử** (Mật khẩu chung: `123456`):
   - **Nhà cung cấp dịch vụ:** `doitac@gmail.com`
   - **Khách hàng tìm dịch vụ:** `khachhang@gmail.com`
   - **Quản trị viên hệ thống:** `admin@gmail.com`
