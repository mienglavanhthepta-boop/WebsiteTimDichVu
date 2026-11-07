-- ==========================================================
-- CƠ SỞ DỮ LIỆU: WEBSITE TÌM DỊCH VỤ QUANH MÌNH (TP. NHA TRANG)
-- Database: website_tim_dich_vu
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `website_tim_dich_vu`
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `website_tim_dich_vu`;

-- Xóa bảng cũ nếu tồn tại để khởi tạo sạch
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- 1. BẢNG NGƯỜI DÙNG (USERS)
-- Mật khẩu mặc định của tất cả tài khoản mẫu là: 123456
-- ==========================================================
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fullname` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('customer', 'provider', 'admin') DEFAULT 'customer',
    `address` VARCHAR(255) DEFAULT NULL,
    `district` VARCHAR(100) DEFAULT 'Phường Lộc Thọ',
    `avatar` VARCHAR(255) DEFAULT NULL,
    `is_verified` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `fullname`, `email`, `phone`, `password`, `role`, `address`, `district`, `is_verified`) VALUES
(1, 'Quản trị viên hệ thống', 'admin@gmail.com', '0901000111', '123456', 'admin', '78 Trần Phú, TP. Nha Trang', 'Phường Lộc Thọ', 1),
(2, 'Nguyễn Minh Tuấn', 'doitac@gmail.com', '0909123456', '123456', 'provider', '02 Nguyễn Đình Chiểu, TP. Nha Trang', 'Phường Vĩnh Phước', 1),
(3, 'Trần Hoàng Nam', 'hoangnam@gmail.com', '0918234567', '123456', 'provider', '115 Thái Nguyên, TP. Nha Trang', 'Phường Phước Tiến', 1),
(4, 'Lê Thị Thu Hà', 'thuha@gmail.com', '0987654321', '123456', 'provider', '88 Lê Hồng Phong, TP. Nha Trang', 'Phường Phước Hải', 1),
(5, 'Phạm Quốc Bảo', 'quocbao@gmail.com', '0933445566', '123456', 'provider', '210 Đường 2 Tháng 4, TP. Nha Trang', 'Phường Vĩnh Hải', 1),
(6, 'Lê Hoàng Khách', 'khachhang@gmail.com', '0977889900', '123456', 'customer', '45 Nguyễn Thiện Thuật, TP. Nha Trang', 'Phường Tân Lập', 1),
(7, 'Nguyễn Ngọc Mai', 'ngocmai@gmail.com', '0966112233', '123456', 'customer', '32 Phạm Văn Đồng, TP. Nha Trang', 'Phường Vĩnh Hòa', 1);

-- ==========================================================
-- 2. BẢNG DANH MỤC DỊCH VỤ (CATEGORIES)
-- ==========================================================
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(100) NOT NULL,
    `color` VARCHAR(30) DEFAULT '#00B14F',
    `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `color`, `description`) VALUES
(1, 'Sửa xe lưu động', 'sua-xe', 'fa-motorcycle', '#00B14F', 'Cứu hộ xe máy, vá vỏ, thay ắc quy, sửa xe tận nơi 24/7 tại Nha Trang'),
(2, 'Sửa điện thoại', 'sua-dien-thoai', 'fa-mobile-screen-button', '#0088FF', 'Thay pin, ép kính, thay màn hình iPhone, Samsung, Xiaomi lấy liền'),
(3, 'Cắt tóc và Barber', 'cat-toc', 'fa-scissors', '#FF6B00', 'Cắt tóc nam nữ, tạo kiểu, gội đầu thư giãn tại tiệm và tại nhà'),
(4, 'Gia sư dạy kèm', 'gia-su', 'fa-graduation-cap', '#8E44AD', 'Gia sư Toán, Lý, Hóa, Tiếng Anh IELTS, Tin học văn phòng 1 kèm 1'),
(5, 'Sửa máy tính và IT', 'sua-may-tinh', 'fa-laptop-medical', '#E74C3C', 'Cài Win, vệ sinh laptop, nâng cấp SSD/RAM, sửa mạng Wi-Fi tận nơi'),
(6, 'Dọn nhà và vệ sinh', 'don-nha', 'fa-broom', '#16A085', 'Giúp việc theo giờ, tổng vệ sinh nhà cửa, homestay, giặt sofa, nệm'),
(7, 'Điện lạnh và điện nước', 'dien-lanh', 'fa-snowflake', '#2980B9', 'Vệ sinh máy lạnh, sửa tủ lạnh, máy giặt, khắc phục sự cố điện nước'),
(8, 'Chăm sóc thú cưng', 'thu-cung', 'fa-paw', '#D35400', 'Spa tắm rửa, cắt tỉa lông chó mèo, trông giữ và khám tại nhà');

-- ==========================================================
-- 3. BẢNG DỊCH VỤ (SERVICES - KHU VỰC TP. NHA TRANG)
-- ==========================================================
CREATE TABLE `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `price_list` TEXT DEFAULT NULL,
    `price_min` DECIMAL(12,0) NOT NULL DEFAULT 0,
    `price_max` DECIMAL(12,0) NOT NULL DEFAULT 0,
    `price_unit` VARCHAR(50) DEFAULT 'lần',
    `address` VARCHAR(255) NOT NULL,
    `district` VARCHAR(100) NOT NULL,
    `distance_km` DECIMAL(4,1) DEFAULT 1.2,
    `phone_contact` VARCHAR(20) NOT NULL,
    `zalo_contact` VARCHAR(20) DEFAULT NULL,
    `open_hours` VARCHAR(100) DEFAULT '07:00 - 22:00',
    `image` VARCHAR(500) DEFAULT NULL,
    `is_home_service` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `views` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`provider_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`id`, `provider_id`, `category_id`, `title`, `description`, `price_list`, `price_min`, `price_max`, `price_unit`, `address`, `district`, `distance_km`, `phone_contact`, `zalo_contact`, `open_hours`, `image`, `is_home_service`, `is_featured`, `status`, `views`) VALUES
(1, 2, 1, 'Cứu hộ và sửa xe máy lưu động 24/7 Minh Tuấn Nha Trang',
'Chuyên cứu hộ xe máy chết máy, thủng lốp, hết bình ắc quy, mất chìa khóa dọc tuyến đường biển Trần Phú, Phạm Văn Đồng và toàn TP. Nha Trang chỉ sau 15 phút gọi. Đội ngũ thợ lành nghề hơn 8 năm kinh nghiệm, báo giá minh bạch trước khi sửa, cam kết phụ tùng chính hãng Honda/Yamaha.',
'Vá vỏ xe số / tay ga tận nơi: 40.000đ - 60.000đ\nThay ruột xe chính hãng: 90.000đ - 120.000đ\nKích bình / Sạc ắc quy tận nơi: 50.000đ\nThay bình ắc quy mới (bảo hành 12 tháng): 320.000đ - 450.000đ\nSửa xe chết máy / bảo dưỡng tận nơi: 80.000đ - 200.000đ',
40000, 450000, 'lần', '68 Trần Phú, TP. Nha Trang', 'Phường Lộc Thọ', 0.8, '0909123456', '0909123456', '24/24 (Cả lễ và Chủ nhật)',
'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=800&q=80', 1, 1, 'active', 342),

(2, 3, 2, 'iFix Nha Trang - Sửa điện thoại và thay pin, ép kính lấy liền',
'Trung tâm sửa chữa điện thoại uy tín tại Nha Trang chuyên iPhone, iPad, Samsung, Oppo, Xiaomi. Hỗ trợ kỹ thuật viên đến tận nhà/khách sạn thay pin, thay màn hình trực tiếp trước mặt khách hàng trong 30 phút. Bảo hành điện tử 12 tháng 1 đổi 1.',
'Thay pin iPhone dung lượng chuẩn/cao: 250.000đ - 650.000đ\nÉp kính màn hình điện thoại các dòng: 300.000đ - 900.000đ\nThay màn hình nguyên bộ chính hãng: 800.000đ - 3.500.000đ\nSửa lỗi chân sạc, loa, mic, vào nước biển: 150.000đ - 500.000đ\nKiểm tra & vệ sinh máy hoàn toàn miễn phí: 0đ',
150000, 3500000, 'máy', '115 Thái Nguyên, TP. Nha Trang', 'Phường Phước Tiến', 1.2, '0918234567', '0918234567', '08:00 - 21:30',
'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80', 1, 1, 'active', 289),

(3, 5, 3, 'The Gentlemen Barber Shop Nha Trang - Cắt tóc tại tiệm và tại nhà',
'Trải nghiệm dịch vụ cắt tóc nam chuẩn Barber hiện đại kết hợp thư giãn tại trung tâm Nha Trang. Nhận cắt tóc tận nhà/khách sạn cho người bận rộn, người lớn tuổi và trẻ nhỏ. Combo 7 bước đầy đủ từ tư vấn kiểu tóc, cắt, cạo mặt, lột mụn đến vuốt sáp tạo kiểu.',
'Combo cắt tóc + xả + tạo kiểu tại tiệm: 70.000đ\nCombo VIP 7 bước (Cắt, gội, massage, cạo mặt, ráy tai): 120.000đ\nDịch vụ cắt tóc tận nhà tại Nha Trang: 100.000đ/người\nUốn tóc Premlock / Layer Hàn Quốc: 250.000đ - 400.000đ\nNhuộm màu thời trang cao cấp: 200.000đ - 350.000đ',
70000, 400000, 'lượt', '210 Đường 2 Tháng 4, TP. Nha Trang', 'Phường Vĩnh Hải', 1.9, '0933445566', '0933445566', '08:30 - 21:00',
'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=800&q=80', 1, 1, 'active', 415),

(4, 4, 4, 'Trung tâm gia sư Nha Trang - Dạy kèm Toán, Lý, Hóa và Tiếng Anh IELTS',
'Đội ngũ giáo viên và sinh viên giỏi từ ĐH Nha Trang, ĐH Khánh Hòa có chứng chỉ IELTS 7.0+. Nhận dạy kèm 1-1 tại nhà khắp các phường thuộc TP. Nha Trang cho học sinh cấp 1, 2, 3, luyện thi chuyển cấp, thi THPT Quốc gia và Tiếng Anh giao tiếp.',
'Gia sư Tiểu học (Toán, Tiếng Việt, Tiếng Anh): 100.000đ - 130.000đ/buổi\nGia sư THCS (Lớp 6 - Lớp 9, luyện thi vào lớp 10): 130.000đ - 180.000đ/buổi\nGia sư THPT & luyện thi Đại học: 160.000đ - 220.000đ/buổi\nGia sư Tiếng Anh giao tiếp & IELTS 1 kèm 1: 200.000đ - 300.000đ/buổi\nHọc thử buổi đầu tiên đánh giá năng lực: Miễn phí',
100000, 300000, 'buổi', '88 Lê Hồng Phong, TP. Nha Trang', 'Phường Phước Hải', 1.5, '0987654321', '0987654321', '07:30 - 21:00',
'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80', 1, 1, 'active', 260),

(5, 2, 5, 'Bác sĩ máy tính Nha Trang - Cài Win, vệ sinh và sửa laptop tận nơi',
'Dịch vụ IT Helpdesk và sửa chữa máy tính, laptop, máy in tận nhà, trường học & văn phòng tại khu vực ĐH Nha Trang và toàn thành phố trong 20 phút. Khắc phục máy chạy chậm, màn hình xanh, mất mạng, cứu dữ liệu, tra keo tản nhiệt MX-4 chính hãng.',
'Cài đặt Windows 10/11, macOS + trọn bộ phần mềm văn phòng, đồ họa: 100.000đ\nVệ sinh laptop chuyên sâu + tra keo tản nhiệt MX-4: 120.000đ\nCombo cài Win + vệ sinh laptop toàn diện: 190.000đ\nNâng cấp ổ cứng SSD 256GB / 512GB chính hãng: 450.000đ - 780.000đ\nSửa lỗi mạng Wi-Fi, bấm đầu dây mạng, đổ mực máy in: 80.000đ - 150.000đ',
80000, 780000, 'máy', '02 Nguyễn Đình Chiểu, TP. Nha Trang', 'Phường Vĩnh Phước', 0.9, '0909123456', '0909123456', '07:00 - 22:00',
'https://images.unsplash.com/photo-1588702547923-7093a6c3ba33?auto=format&fit=crop&w=800&q=80', 1, 1, 'active', 512),

(6, 4, 6, 'GreenClean Nha Trang - Dọn nhà, căn hộ, homestay và giặt sofa',
'Dịch vụ dọn dẹp vệ sinh nhà cửa, căn hộ Mường Thanh, Gold Coast, Scenia Bay, homestay theo giờ chuẩn 5 sao tại TP. Nha Trang. Nhân viên lý lịch rõ ràng, mang đầy đủ dụng cụ và hóa chất sinh học an toàn. Có giặt ghế sofa, nệm, rèm cửa tận nơi.',
'Dọn nhà / homestay theo giờ (Gói lẻ 2 - 4 giờ): 70.000đ - 85.000đ/giờ\nTổng vệ sinh căn hộ / nhà sau xây dựng: 15.000đ/m2\nGiặt ghế sofa tại nhà (hút bụi, diệt khuẩn hơi nước nóng): 250.000đ - 350.000đ/bộ\nGiặt nệm cao su / lò xo khử mùi tận nơi: 200.000đ - 300.000đ/tấm\nGiặt rèm cửa tháo lắp tận nơi: 35.000đ/kg',
70000, 450000, 'giờ', '45 Nguyễn Thiện Thuật, TP. Nha Trang', 'Phường Tân Lập', 1.0, '0987654321', '0987654321', '06:30 - 20:30',
'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80', 1, 1, 'active', 398),

(7, 3, 7, 'Điện lạnh Phố Biển - Vệ sinh máy lạnh, sửa tủ lạnh và điện nước',
'Chuyên vệ sinh, bơm gas máy lạnh treo tường & âm trần cho gia đình, khách sạn tại TP. Nha Trang. Sửa máy giặt, tủ lạnh, bình nóng lạnh và xử lý sự cố chập điện, rò rỉ ống nước tại nhà. Có mặt sau 20 phút, bảo hành 6 tháng.',
'Vệ sinh máy lạnh treo tường trọn gói (từ 2 máy): 120.000đ/máy\nVệ sinh máy lạnh lẻ 1 máy + kiểm tra gas: 150.000đ/máy\nBơm bổ sung gas R22 / R32 / R410A: 150.000đ - 250.000đ\nVệ sinh lồng máy giặt cửa trên / cửa ngang: 250.000đ - 350.000đ\nSửa chập điện, thay vòi nước, sửa máy nước nóng: 120.000đ - 300.000đ',
120000, 500000, 'lần', '102 Quang Trung, TP. Nha Trang', 'Phường Xương Huân', 1.6, '0918234567', '0918234567', '07:00 - 21:30',
'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 215),

(8, 5, 8, 'HappyPet Spa Nha Trang - Tắm thú cưng, cắt tỉa lông và khách sạn chó mèo',
'Spa chăm sóc thú cưng chuẩn Hàn Quốc tại Nha Trang. Nhận đón trả thú cưng tận nhà hoặc tắm sấy, cắt tỉa lông nghệ thuật, vệ sinh tai, cắt móng. Môi trường sạch sẽ, khử khuẩn UV mỗi ngày, đội ngũ groomer yêu thương động vật.',
'Combo tắm + sấy + vệ sinh 7 bước (dưới 5kg): 120.000đ\nCombo tắm + cắt tỉa tạo kiểu toàn thân: 200.000đ - 320.000đ\nCạo lông máu / cạo vệ sinh bàn chân, bụng: 80.000đ - 150.000đ\nKhách sạn trông giữ chó mèo phòng điều hòa: 120.000đ - 200.000đ/ngày\nĐón - trả thú cưng tận nhà nội thành Nha Trang: Miễn phí',
80000, 320000, 'lượt', '32 Phạm Văn Đồng, TP. Nha Trang', 'Phường Vĩnh Hòa', 2.4, '0933445566', '0933445566', '08:00 - 20:30',
'https://images.unsplash.com/photo-1516734212186-a967f81ad0d7?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 184),

(9, 2, 1, 'Tiệm sửa xe và bảo dưỡng tay ga chuyên nghiệp Quốc Bảo Nha Trang',
'Chuyên làm nồi xe tay ga (Honda SH, AirBlade, Vision, Vario, Vespa), thay nhớt chính hãng Motul/Liqui Moly, căn chỉnh phun xăng điện tử FI giúp xe chạy êm, bốc và tiết kiệm xăng. Có dịch vụ nhận và giao xe tận nhà tại TP. Nha Trang.',
'Thay nhớt máy tổng hợp cao cấp: 130.000đ - 280.000đ\nVệ sinh nồi xe tay ga chống rung đầu: 100.000đ\nLàm trọn bộ nồi xe tay ga (bảo hành 10.000km): 450.000đ - 850.000đ\nXúc kim phun xăng điện tử + vệ sinh họng ga: 150.000đ\nBảo dưỡng tổng quát 15 hạng mục: 200.000đ',
100000, 850000, 'xe', '150 Hoàng Hoa Thám, TP. Nha Trang', 'Phường Lộc Thọ', 1.3, '0909123456', '0909123456', '07:30 - 20:00',
'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 167),

(10, 5, 1, 'Cứu hộ xe máy và thay ắc quy tận nơi 24/24 Thành Đạt Nha Trang',
'Đội cứu hộ xe máy lưu động túc trực 24/24 tại khu vực phía Bắc và trung tâm TP. Nha Trang. Hỗ trợ vá lốp không săm, thay săm lốp, câu bình ắc quy, mở khóa xe thông minh Smartkey và sửa xe chết máy do ngập nước.',
'Vá lốp không săm xe tay ga tận nơi: 50.000đ\nThay bình ắc quy GS / Globe chính hãng: 330.000đ - 480.000đ\nMở khóa xe / làm lại chìa khóa Smartkey: 150.000đ - 450.000đ\nCứu hộ xe ngập nước, chết bugi ban đêm: 80.000đ - 180.000đ',
50000, 480000, 'lần', '85 Đường 2 Tháng 4, TP. Nha Trang', 'Phường Vĩnh Hải', 1.1, '0933445566', '0933445566', '24/24',
'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 195),

(11, 3, 2, 'FastCare Nha Trang - Thay màn hình, mặt kính iPhone và Samsung lấy ngay',
'Chuyên ép kính chân không công nghệ cao, thay màn hình OLED/AMOLED và thay pin chính hãng Pisen, EUtev cho các dòng iPhone, Samsung Galaxy, Xiaomi. Khách hàng xem trực tiếp toàn bộ quá trình thao tác kỹ thuật, lấy máy ngay sau 30 - 45 phút.',
'Thay mặt kính cảm ứng iPhone các dòng: 280.000đ - 850.000đ\nThay pin Pisen dung lượng siêu cao (Bảo hành 12 tháng): 320.000đ - 750.000đ\nThay kính lưng iPhone bằng laser không bung máy: 250.000đ - 500.000đ\nSửa lỗi Face ID, camera rung, mất sóng: 300.000đ - 900.000đ',
250000, 900000, 'máy', '62 Nguyễn Thiện Thuật, TP. Nha Trang', 'Phường Tân Lập', 0.9, '0918234567', '0918234567', '08:00 - 21:00',
'https://images.unsplash.com/photo-1512499617640-c74ae3a79d37?auto=format&fit=crop&w=800&q=80', 1, 1, 'active', 246),

(12, 2, 2, 'Tiệm sửa điện thoại Minh Khôi - Chuyên sửa phần cứng và cứu máy vô nước',
'Nhận xử lý nhanh các ca điện thoại rơi nước biển, sập nguồn, chạm áp, treo logo, quên mật khẩu màn hình và tài khoản iCloud/Google. Có hỗ trợ nhận và giao máy tận nơi miễn phí trong nội thành TP. Nha Trang.',
'Sấy khô và xử lý điện thoại rơi nước biển khẩn cấp: 150.000đ - 300.000đ\nThay cụm chân sạc nhanh Type-C / Lightning: 180.000đ - 350.000đ\nThay loa trong, loa ngoài, mic đàm thoại: 150.000đ - 300.000đ\nChạy lại phần mềm, mở khóa mật khẩu: 100.000đ - 250.000đ',
100000, 350000, 'máy', '124 Hùng Vương, TP. Nha Trang', 'Phường Lộc Thọ', 1.4, '0909123456', '0909123456', '08:30 - 21:30',
'https://images.unsplash.com/photo-1597740985671-2a8a3b80502e?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 178),

(13, 5, 3, 'Hải Âu Barber Studio - Cắt tóc nam, uốn định hình và gội đầu thư giãn',
'Không gian Barber trẻ trung, chuyên các kiểu tóc Undercut, Side Part, Mohican, Mullet và Layer hiện đại. Đội ngũ thợ cắt tỉ mỉ, sử dụng sáp và gôm cao cấp, kết hợp gội đầu massage cổ vai gáy giúp giải tỏa căng thẳng.',
'Cắt tóc nam thiết kế theo khuôn mặt + xả tóc: 60.000đ\nCombo cắt + gội massage + cạo mặt + đắp mặt nạ lạnh: 110.000đ\nUốn phồng chân tóc / uốn con sâu / uốn Layer: 220.000đ - 350.000đ\nTẩy tóc và nhuộm màu khói thời trang: 250.000đ - 450.000đ',
60000, 450000, 'lượt', '48 Ngô Gia Tự, TP. Nha Trang', 'Phường Phước Tiến', 1.0, '0933445566', '0933445566', '08:30 - 21:00',
'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 230),

(14, 4, 3, 'Salon Tóc Đẹp Nha Trang - Cắt tạo kiểu nam nữ và phục hồi tóc chuyên sâu',
'Salon chăm sóc tóc toàn diện cho cả nam và nữ tại TP. Nha Trang. Chuyên cắt tóc bob, layer nữ, uốn sóng lơi Hàn Quốc, duỗi hơi nước và phục hồi keratin cho tóc hư tổn. Có nhận trang điểm và làm tóc dự tiệc tại nhà.',
'Cắt tạo kiểu tóc nữ + gội sấy tạo phồng: 90.000đ - 150.000đ\nGội đầu dưỡng sinh thảo dược 60 phút: 120.000đ\nUốn sóng nước / duỗi phồng tự nhiên: 350.000đ - 650.000đ\nLàm tóc và trang điểm dự tiệc tận nhà: 250.000đ - 450.000đ',
90000, 650000, 'lượt', '95 Lê Hồng Phong, TP. Nha Trang', 'Phường Phước Hải', 1.6, '0987654321', '0987654321', '08:00 - 20:30',
'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 192),

(15, 4, 4, 'Gia sư Tiếng Anh giao tiếp và luyện thi IELTS tại nhà TP. Nha Trang',
'Chuyên dạy kèm Tiếng Anh giao tiếp phản xạ cho người đi làm trong ngành du lịch - khách sạn tại Nha Trang và luyện thi chứng chỉ IELTS, TOEIC từ mất gốc đến 7.0+. Lộ trình cá nhân hóa theo trình độ từng học viên.',
'Tiếng Anh giao tiếp thực tế cho người đi làm: 180.000đ - 250.000đ/buổi\nLuyện thi IELTS 4 kỹ năng (Nghe, Nói, Đọc, Viết): 220.000đ - 320.000đ/buổi\nTiếng Anh thiếu nhi chuẩn Cambridge (Starters, Movers, Flyers): 140.000đ/buổi\nKiểm tra trình độ đầu vào và tư vấn lộ trình: Miễn phí',
140000, 320000, 'buổi', '18 Đoàn Trần Nghiệp, TP. Nha Trang', 'Phường Vĩnh Phước', 1.1, '0987654321', '0987654321', '08:00 - 21:00',
'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 210),

(16, 4, 4, 'Gia sư Tiểu học và luyện chữ đẹp tận nhà cô Mai Nha Trang',
'Giáo viên Tiểu học giàu kinh nghiệm nhận rèn chữ đẹp, dạy kèm Toán - Tiếng Việt và chuẩn bị hành trang cho bé chuẩn bị vào lớp 1 tại TP. Nha Trang. Phương pháp sư phạm nhẹ nhàng, kiên nhẫn, giúp bé yêu thích việc học.',
'Hành trang cho bé chuẩn bị vào lớp 1 (Đọc, viết, tính toán): 100.000đ/buổi\nLuyện viết chữ đẹp, sửa lỗi chính tả tiểu học: 110.000đ/buổi\nBồi dưỡng Toán tư duy và Tiếng Việt lớp 1 - lớp 5: 120.000đ - 150.000đ/buổi',
100000, 150000, 'buổi', '36 Tô Hiến Thành, TP. Nha Trang', 'Phường Tân Lập', 1.3, '0987654321', '0987654321', '07:30 - 20:00',
'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 175),

(17, 2, 5, 'Trung tâm Laptop24h Nha Trang - Sửa mainboard, thay bàn phím và màn hình laptop',
'Chuyên sửa chữa phần cứng laptop Dell, Asus, HP, Lenovo, Acer, MacBook lấy liền tại Nha Trang. Thay bàn phím bị liệt, thay pin phồng, thay màn hình bị sọc/vỡ, sửa bản lề gãy và nâng cấp RAM/SSD tốc độ cao.',
'Thay bàn phím laptop các dòng phổ thông: 220.000đ - 450.000đ\nThay pin laptop chính hãng bảo hành 12 tháng: 380.000đ - 750.000đ\nThay màn hình laptop Full HD chống chói: 950.000đ - 1.850.000đ\nPhục hồi và hàn gia cố bản lề laptop bị gãy: 180.000đ - 300.000đ',
180000, 1850000, 'máy', '74 Thống Nhất, TP. Nha Trang', 'Phường Phước Tiến', 1.2, '0909123456', '0909123456', '08:00 - 21:00',
'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 228),

(18, 3, 5, 'Dịch vụ mạng Wi-Fi, lắp đặt camera và đổ mực máy in tận nơi Nha Trang',
'Nhận khắc phục sự cố mất mạng Internet, cấu hình bộ kích sóng Wi-Fi Mesh xuyên tường cho nhà tầng và khách sạn, lắp đặt camera an ninh giám sát qua điện thoại, sửa máy in kẹt giấy và nạp mực máy in tận nơi.',
'Nạp mực máy in Canon / HP / Brother tận nơi: 90.000đ - 130.000đ/hộp\nSửa lỗi mất mạng, đi lại dây mạng LAN, cấu hình Router Wi-Fi: 100.000đ - 200.000đ\nLắp đặt và cài đặt Camera Wi-Fi xoay 360 độ: 150.000đ/cái (công lắp)\nSửa máy in kẹt giấy, bản in bị mờ hoặc đen: 120.000đ - 250.000đ',
90000, 250000, 'lần', '50 Điện Biên Phủ, TP. Nha Trang', 'Phường Vĩnh Hòa', 2.0, '0918234567', '0918234567', '07:30 - 20:30',
'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 164),

(19, 4, 6, 'Vệ sinh công nghiệp An Khang - Tổng vệ sinh nhà mới và đánh bóng sàn Nha Trang',
'Đội ngũ vệ sinh công nghiệp chuyên nghiệp với máy chà sàn, máy hút bụi công suất lớn. Nhận tổng vệ sinh nhà vừa xây xong, nhà mới sửa chữa, biệtự, nhà hàng, khách sạn và lau kính mặt ngoài tại TP. Nha Trang.',
'Tổng vệ sinh nhà ở / công trình sau xây dựng trọn gói: 12.000đ - 18.000đ/m2\nGói dọn vệ sinh tổng quát nhà phố định kỳ (2 nhân viên trong 4 giờ): 550.000đ\nChà sàn, đánh bóng sàn đá gạch bằng máy công nghiệp: 15.000đ/m2\nVệ sinh cửa kính, vách kính căn hộ và showroom: 10.000đ - 15.000đ/m2',
300000, 1500000, 'gói', '140 Đường số 4 KĐT Lê Hồng Phong, TP. Nha Trang', 'Phường Phước Hải', 1.8, '0987654321', '0987654321', '07:00 - 19:30',
'https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 205),

(20, 4, 6, 'Dịch vụ giặt ghế sofa, giặt nệm và rèm cửa khử khuẩn tận nhà Nha Trang',
'Chuyên giặt sạch sâu các loại ghế sofa nỉ, vải, da, nệm cao su Kymdan, nệm lò xo và thảm trải sàn ngay tại nhà bằng công nghệ phun hút hơi nước nóng diệt khuẩn 99%, khử sạch mùi hôi ẩm mốc và vết ố bẩn.',
'Giặt bộ ghế sofa vải / nỉ / da tận nhà: 250.000đ - 350.000đ/bộ\nGiặt nệm ngủ khử mùi và diệt khuẩn bằng hơi nước nóng: 200.000đ - 280.000đ/tấm\nGiặt thảm trang trí phòng khách / văn phòng: 150.000đ - 300.000đ/tấm\nTháo lắp, giặt sấy thơm rèm cửa gia đình: 35.000đ/kg',
150000, 350000, 'lần', '28 Yersin, TP. Nha Trang', 'Phường Lộc Thọ', 0.7, '0987654321', '0987654321', '07:00 - 20:00',
'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 240),

(21, 3, 7, 'Thợ điện nước tận nhà 24/7 Hoàng Gia - Sửa chập điện, ống nước và máy bơm',
'Đội thợ điện nước lành nghề có mặt nhanh sau 15 phút tại TP. Nha Trang. Chuyên xử lý mất điện cục bộ, nhảy aptomat, thay bóng đèn LED, sửa rò rỉ đường ống nước âm tường, thông nghẹt bồn rửa và sửa máy bơm nước.',
'Kiểm tra và xử lý chập điện, nhảy CB/Aptomat khẩn cấp: 120.000đ - 250.000đ\nThay vòi nước, van khóa, phao bồn nước, bộ xả bồn cầu: 80.000đ - 180.000đ\nSửa chữa hoặc lắp mới máy bơm nước tăng áp gia đình: 150.000đ - 300.000đ\nThông tắc chậu rửa chén, lavabo, đường thoát sàn: 150.000đ - 350.000đ',
80000, 350000, 'lần', '112 Dã Tượng, TP. Nha Trang', 'Phường Vĩnh Nguyên', 1.9, '0918234567', '0918234567', '24/24',
'https://images.unsplash.com/photo-1581092921461-eab62e97a780?auto=format&fit=crop&w=800&q=80', 1, 1, 'active', 274),

(22, 3, 7, 'Trung tâm điện lạnh Khánh Hòa - Sửa máy giặt, tủ lạnh và tháo lắp điều hòa',
'Chuyên tháo lắp, di dời máy lạnh sang vị trí mới, sửa máy lạnh không lạnh, chảy nước, kêu to; sửa tủ lạnh không đông đá, máy giặt báo lỗi không vắt và bình nước nóng lạnh tại khắp các phường xã TP. Nha Trang.',
'Công tháo lắp, di dời bộ máy lạnh treo tường: 250.000đ - 400.000đ\nSửa máy lạnh chảy nước, nghẹt đường ống thoát: 100.000đ - 150.000đ\nThay sò lạnh, rơ-le, bơm gas tủ lạnh tận nhà: 180.000đ - 450.000đ\nSửa bo mạch, thay cấp nước, xả nước máy giặt: 200.000đ - 450.000đ',
100000, 450000, 'lần', '65 Đường 2 Tháng 4, TP. Nha Trang', 'Phường Vĩnh Phước', 1.3, '0918234567', '0918234567', '07:00 - 21:00',
'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 188),

(23, 5, 8, 'Phòng khám thú y PetCare Nha Trang - Tiêm phòng, siêu âm và khám tại nhà',
'Đội ngũ bác sĩ thú y tận tâm tại TP. Nha Trang nhận khám chữa bệnh, tiêm vắc-xin định kỳ, tẩy giun, nhỏ gáy trị ve rận, siêu âm thai và triệt sản an toàn cho chó mèo. Có dịch vụ bác sĩ đến khám và tiêm phòng tận nhà.',
'Khám tổng quát và tư vấn dinh dưỡng thú cưng: 50.000đ - 100.000đ\nTiêm vắc-xin phòng 5 bệnh / 7 bệnh cho chó mèo: 150.000đ - 250.000đ/mũi\nTẩy giun và nhỏ gáy trị ve rận, bọ chét: 60.000đ - 180.000đ\nBác sĩ thú y đến khám và tiêm thuốc tận nhà: Phụ phí 50.000đ',
50000, 250000, 'lần', '54 Nguyễn Trãi, TP. Nha Trang', 'Phường Phước Tiến', 1.1, '0933445566', '0933445566', '07:30 - 21:00',
'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 219),

(24, 5, 8, 'Mèo Cún House Nha Trang - Spa cắt tỉa lông và trông giữ thú cưng cuối tuần',
'Tiệm spa và lưu trú thú cưng ấm cúng ngay trung tâm Nha Trang. Chuyên tắm khử mùi bằng sữa tắm hữu cơ, chải gỡ rối lông cho Poodle, Phốc Sóc, Mèo Anh lông dài và nhận giữ thú cưng theo ngày khi chủ đi công tác, du lịch.',
'Tắm spa khử mùi + vệ sinh tai + cắt mài móng + vắt tuyến hôi: 100.000đ - 180.000đ\nCắt tỉa lông tạo kiểu Teddy / Bo tròn mặt cho Poodle, Bichon: 180.000đ - 280.000đ\nLưu trú thú cưng phòng riêng sạch sẽ (kèm 2 bữa ăn/ngày): 100.000đ - 160.000đ/ngày',
100000, 280000, 'lượt', '19 Biệt Thự, TP. Nha Trang', 'Phường Tân Lập', 0.9, '0933445566', '0933445566', '08:00 - 21:00',
'https://images.unsplash.com/photo-1537151608828-ea2b11777ee8?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 197),

(25, 2, 1, 'Sửa xe máy lưu động đêm khuya Hoàng Long Nha Trang',
'Chuyên cứu hộ xe máy ban đêm trên mọi tuyến đường TP. Nha Trang. Xử lý nhanh đứt dây ga, đứt dây curoa, bể vỏ, hết bình, chết bugi và hết xăng giữa đường với chi phí hợp lý.',
'Vá vỏ xe máy ban đêm tận nơi: 50.000đ - 70.000đ\nThay dây curoa / dây ga / dây phanh tận nơi: 120.000đ - 380.000đ\nThay bugi NGK / Denso chính hãng: 90.000đ - 180.000đ',
50000, 380000, 'lần', '220 Lê Hồng Phong, TP. Nha Trang', 'Phường Phước Hải', 1.5, '0909123456', '0909123456', '18:00 - 06:00',
'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 156),

(26, 5, 1, 'Đội cứu hộ xe máy và kích bình ắc quy Cửa Bé Nha Trang',
'Hỗ trợ nhanh khu vực Cửa Bé, cảng Cầu Đá, An Viên và trung tâm TP. Nha Trang. Nhận kích bình ắc quy xe tay ga, xe mô tô phân khối lớn và sửa chữa lưu động tại chỗ.',
'Kích bình ắc quy xe máy / mô tô tận nơi: 50.000đ\nThay lốp không săm Chengshin / Michelin: 380.000đ - 750.000đ\nSửa chữa hệ thống đề, sạc, đèn chiếu sáng: 80.000đ - 250.000đ',
50000, 750000, 'lần', '89 Hoàng Diệu, TP. Nha Trang', 'Phường Vĩnh Nguyên', 2.1, '0933445566', '0933445566', '06:00 - 23:00',
'https://images.unsplash.com/photo-1558981285-6f0c94958bb6?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 142),

(27, 2, 1, 'Tiệm sửa xe số và xe côn tay chuyên nghiệp Tuấn Kiệt',
'Chuyên bảo dưỡng, căn chỉnh côn tay Exciter, Winner, Raider và các dòng xe số phổ thông. Nhận thay nhông sên dĩa, phục hồi phuộc nhún, làm máy êm bốc có bảo hành dài hạn.',
'Căn chỉnh côn, tăng sên, bơm lốp, tra dầu: 40.000đ\nThay bộ nhông sên dĩa DID / Recto chính hãng: 320.000đ - 550.000đ\nPhục hồi phuộc trước / phuộc sau chống xì nhớt: 180.000đ - 350.000đ',
40000, 550000, 'xe', '42 Phan Bội Châu, TP. Nha Trang', 'Phường Xương Huân', 1.4, '0909123456', '0909123456', '07:00 - 20:00',
'https://images.unsplash.com/photo-1558980664-3a031cf67ea8?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 138),

(28, 5, 1, 'Dịch vụ vá vỏ lưu động và thay lốp Michelin chính hãng Nha Trang',
'Chuyên vá ép nhiệt, vá nấm lốp không săm và thay các dòng lốp Michelin, Pirelli, Maxxis chống đinh an toàn tại khu vực Vĩnh Ngọc, Vĩnh Thạnh và nội thành Nha Trang.',
'Vá nấm lốp không săm chuyên dụng: 60.000đ/lỗ\nThay van vòi cao su / van kim loại chống xì hơi: 50.000đ\nThay lốp xe tay ga chính hãng tận nơi: 420.000đ - 850.000đ',
50000, 850000, 'lần', '15 Lương Định Của, TP. Nha Trang', 'Xã Vĩnh Ngọc', 2.6, '0933445566', '0933445566', '06:30 - 22:00',
'https://images.unsplash.com/photo-1558981359-219d6364c9c8?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 129),

(29, 3, 2, 'Trung tâm bảo hành và sửa chữa Apple CarePlus Nha Trang',
'Chuyên sửa chữa chuyên sâu các thiết bị Apple: iPhone, iPad, Apple Watch, AirPods. Thay pin chuẩn dung lượng báo phần trăm, sửa lỗi mất nguồn, mất âm thanh với linh kiện zin bóc máy.',
'Thay pin iPhone chuẩn chẩn đoán Apple: 380.000đ - 890.000đ\nÉp kính cảm ứng Apple Watch / iPad các dòng: 350.000đ - 1.200.000đ\nThay pin tai nghe AirPods 1 / 2 / Pro: 250.000đ/bên',
250000, 1200000, 'máy', '86 Trần Phú, TP. Nha Trang', 'Phường Lộc Thọ', 0.8, '0918234567', '0918234567', '08:00 - 21:30',
'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 214),

(30, 2, 2, 'Sửa điện thoại tận nơi 30 phút Gia Hưng Mobile Nha Trang',
'Kỹ thuật viên di chuyển tận nhà, quán cà phê hoặc khách sạn để thay pin, thay màn hình, thay chân sạc điện thoại ngay trước mặt khách hàng, tiết kiệm thời gian đi lại.',
'Thay pin điện thoại tận nơi (miễn phí công di chuyển): 250.000đ - 600.000đ\nThay màn hình iPhone / Samsung / Oppo tận nơi: 650.000đ - 2.500.000đ\nDán cường lực chống nhìn trộm + vệ sinh loa mic: 80.000đ',
80000, 2500000, 'máy', '178 Đường 2 Tháng 4, TP. Nha Trang', 'Phường Vĩnh Hải', 1.7, '0909123456', '0909123456', '08:00 - 21:00',
'https://images.unsplash.com/photo-1556656793-08538906a9f8?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 182),

(31, 3, 2, 'Hoàng Vũ Mobile - Chuyên ép cổ cáp màn hình và thay vỏ iPhone',
'Giải pháp tiết kiệm chi phí cho màn hình bị xanh trắng màn, sọc nước, liệt cảm ứng bằng công nghệ ép cổ cáp hiện đại mà không cần thay nguyên bộ màn hình đắt đỏ.',
'Ép cổ cáp màn hình iPhone 13 Pro / 13 Pro Max / 14 Pro Max: 700.000đ - 1.200.000đ\nThay khung sườn, độ vỏ iPhone đẹp như mới: 450.000đ - 950.000đ\nThay kính camera sau chống trầy: 100.000đ - 200.000đ',
100000, 1200000, 'máy', '64 Đồng Nai, TP. Nha Trang', 'Phường Phước Hải', 1.5, '0918234567', '0918234567', '08:30 - 20:30',
'https://images.unsplash.com/photo-1601784551446-20c9e07cdbdb?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 165),

(32, 2, 2, 'Dịch vụ thay pin điện thoại tận nhà và văn phòng Nha Trang',
'Chuyên phân phối và lắp đặt pin dung lượng cao cho iPhone, Samsung, Xiaomi, Realme, Vivo. Bảo hành 12 tháng đổi mới ngay nếu pin phù hoặc tụt phần trăm bất thường.',
'Pin chuẩn dung lượng các dòng máy Android: 220.000đ - 420.000đ\nPin dung lượng siêu cao cho iPhone X - 14 Pro Max: 350.000đ - 780.000đ\nThay cụm rung, nút nguồn, nút âm lượng bị lờn: 150.000đ - 280.000đ',
150000, 780000, 'máy', '12 Nguyễn Đình Chiểu, TP. Nha Trang', 'Phường Vĩnh Phước', 1.0, '0909123456', '0909123456', '07:30 - 21:00',
'https://images.unsplash.com/photo-1585060544812-6b45742d762f?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 153),

(33, 5, 3, 'Ocean Barber Shop Nha Trang - Cắt tóc nam phong cách cổ điển',
'Tiệm cắt tóc nam phong cách Classic Chicano & Gentleman gần biển Nha Trang. Thợ cắt kỹ lưỡng từng đường tông đơ, cạo khăn nóng thư giãn và tạo kiểu Pomade sang trọng.',
'Cắt tóc nam Classic + cạo khăn nóng + vuốt Pomade: 80.000đ\nTỉa và tạo dáng râu quai nón chuyên nghiệp: 50.000đ\nNhuộm phủ bạc tự nhiên cho quý ông: 150.000đ - 220.000đ',
50000, 220000, 'lượt', '34 Nguyễn Thị Minh Khai, TP. Nha Trang', 'Phường Lộc Thọ', 0.7, '0933445566', '0933445566', '08:30 - 21:00',
'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 208),

(34, 5, 3, 'Tiệm cắt tóc lưu động tận nhà cho trẻ nhỏ và người lớn tuổi',
'Dịch vụ thợ cắt tóc đến tận nhà riêng hoặc bệnh viện tại TP. Nha Trang để cắt tóc cho em bé sợ ra tiệm và người cao tuổi đi lại khó khăn. Dụng cụ êm ái, vệ sinh sạch sẽ.',
'Cắt tóc tận nhà cho người lớn tuổi / người bệnh: 80.000đ - 100.000đ\nCắt tóc tận nhà cho bé trai / bé gái: 70.000đ - 90.000đ\nCombo cắt tóc gia đình từ 3 người trở lên: 70.000đ/người',
70000, 100000, 'người', '72 Ngô Đức Kế, TP. Nha Trang', 'Phường Tân Lập', 1.1, '0933445566', '0933445566', '07:30 - 19:30',
'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 176),

(35, 4, 3, 'Luxury Hair Studio Nha Trang - Nhuộm balayage và uốn tóc nữ',
'Chuyên kỹ thuật nhuộm Balayage, Highlight, Ombre và uốn phục hồi Olaplex cao cấp giúp mái tóc mềm mượt, bền màu, không khô xơ. Tư vấn màu tóc tôn da miễn phí.',
'Cắt thiết kế tóc nữ Layer / Hime / Bob: 100.000đ - 150.000đ\nNhuộm màu thời trang / phủ bóng collagen: 300.000đ - 600.000đ\nHấp dầu phục hồi chuyên sâu Keratin: 200.000đ - 350.000đ',
100000, 600000, 'lượt', '19 Lê Đại Hành, TP. Nha Trang', 'Phường Phước Tiến', 1.2, '0987654321', '0987654321', '08:30 - 20:30',
'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 184),

(36, 5, 3, 'King Barber Nha Trang - Cắt tóc nam, cạo mặt và lấy ráy tai êm ái',
'Phục vụ trọn gói cắt tóc nam đẹp, gội đầu dưỡng sinh, cạo mặt sáng da và lấy ráy tai bằng dụng cụ khử trùng riêng biệt kèm đèn soi chuyên dụng cực kỳ êm dịu.',
'Cắt tóc nam + xả tạo kiểu nhanh gọn: 60.000đ\nLấy ráy tai thư giãn + rửa mắt + massage mặt: 70.000đ\nTrọn gói Combo Hoàng Đế 9 bước (60 phút): 150.000đ',
60000, 150000, 'lượt', '88 Phạm Văn Đồng, TP. Nha Trang', 'Phường Vĩnh Hòa', 2.2, '0933445566', '0933445566', '08:00 - 21:00',
'https://images.unsplash.com/photo-1517832606608-810c760f8b5b?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 162),

(37, 4, 4, 'Gia sư Toán, Lý, Hóa cấp 2 và cấp 3 thầy Nam ĐH Nha Trang',
'Nhận lấy lại gốc kiến thức môn Toán, Vật Lý, Hóa Học cho học sinh mất căn bản và bồi dưỡng học sinh khá giỏi ôn thi chuyển cấp lớp 9 lên lớp 10, ôn thi tốt nghiệp THPT.',
'Kèm Toán - Lý - Hóa THCS (Lớp 6 - Lớp 9): 130.000đ - 160.000đ/buổi\nKèm Toán - Lý - Hóa THPT (Lớp 10 - Lớp 12): 160.000đ - 200.000đ/buổi\nNhóm học 2 - 3 học sinh tại nhà: 100.000đ/học sinh/buổi',
100000, 200000, 'buổi', '08 Nguyễn Đình Chiểu, TP. Nha Trang', 'Phường Vĩnh Phước', 0.9, '0987654321', '0987654321', '07:30 - 21:00',
'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 189),

(38, 2, 4, 'Dạy kèm Tin học văn phòng Word, Excel và lập trình cơ bản tại nhà',
'Hướng dẫn thực hành trực tiếp trên máy tính các kỹ năng Word, Excel kế toán, PowerPoint thuyết trình, Canva thiết kế và lập trình Scratch/Python/Web cơ bản cho học sinh và người đi làm.',
'Tin học văn phòng thực chiến (Word, Excel, PowerPoint): 150.000đ/buổi\nLuyện thi chứng chỉ Tin học MOS / IC3 / CNTT cơ bản: 160.000đ/buổi\nKèm lập trình cơ bản C++, Python, HTML/CSS/PHP: 180.000đ - 250.000đ/buổi',
150000, 250000, 'buổi', '45 Mê Linh, TP. Nha Trang', 'Phường Phước Tiến', 1.2, '0909123456', '0909123456', '08:00 - 21:00',
'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 171),

(39, 4, 4, 'Gia sư dạy đàn Piano, Guitar và Ukulele tại nhà TP. Nha Trang',
'Giáo viên tốt nghiệp chuyên ngành Âm nhạc nhận dạy kèm 1-1 tại nhà các môn Piano cổ điển/đệm hát, Guitar Acoustic và Ukulele cho trẻ em và người lớn mới bắt đầu tại Nha Trang.',
'Học đàn Guitar đệm hát / Ukulele tại nhà: 150.000đ - 180.000đ/buổi\nHọc đàn Piano / Organ cơ bản đến nâng cao: 180.000đ - 250.000đ/buổi\nTư vấn chọn mua nhạc cụ phù hợp: Miễn phí',
150000, 250000, 'buổi', '60 Biệt Thự, TP. Nha Trang', 'Phường Lộc Thọ', 0.8, '0987654321', '0987654321', '08:00 - 20:30',
'https://images.unsplash.com/photo-1511379938547-c1f69419868d?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 160),

(40, 4, 4, 'Gia sư Tiếng Trung và Tiếng Hàn giao tiếp du lịch tại Nha Trang',
'Chuyên đào tạo cấp tốc Tiếng Trung, Tiếng Hàn, Tiếng Nga giao tiếp chuyên ngành nhà hàng, khách sạn, spa và bán hàng cho nhân sự làm việc tại trung tâm du lịch TP. Nha Trang.',
'Tiếng Trung giao tiếp thương mại & du lịch (HSK 1 - 4): 160.000đ - 220.000đ/buổi\nTiếng Hàn giao tiếp & luyện thi TOPIK I, II: 160.000đ - 220.000đ/buổi\nLớp kèm nhóm 2 - 4 nhân viên tại cửa hàng/khách sạn: 250.000đ/ca',
160000, 250000, 'buổi', '105 Phong Châu, TP. Nha Trang', 'Phường Phước Hải', 1.9, '0987654321', '0987654321', '08:00 - 21:00',
'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 148),

(41, 2, 5, 'Dịch vụ lắp ráp PC đồ họa, gaming và tối ưu Windows tận nhà Nha Trang',
'Nhận tư vấn cấu hình, lắp ráp PC Gaming, máy trạm đồ họa Render 3D, đi dây gọn đẹp, cài đặt Windows chuẩn mượt và xử lý lỗi văng game, tụt FPS tận nhà tại TP. Nha Trang.',
'Công lắp ráp PC hoàn chỉnh + đi dây thẩm mỹ + cài Win: 150.000đ - 250.000đ\nVệ sinh thùng máy PC + tra keo tản nhiệt CPU & VGA: 120.000đ - 180.000đ\nCài đặt bộ phần mềm Adobe, AutoCAD, 3dsMax, Lumion: 80.000đ - 150.000đ',
80000, 250000, 'lần', '29 Hồng Bàng, TP. Nha Trang', 'Phường Tân Lập', 1.0, '0909123456', '0909123456', '08:00 - 21:30',
'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 203),

(42, 3, 5, 'Cứu dữ liệu ổ cứng, thẻ nhớ và sửa MacBook chuyên sâu Nha Trang',
'Chuyên phục hồi dữ liệu bị xóa nhầm, format, ổ cứng HDD/SSD chết cơ hoặc không nhận diện; sửa chữa MacBook Air, MacBook Pro, iMac và cài đặt macOS/Bootcamp Windows lấy liền.',
'Cài đặt hệ điều hành macOS + ứng dụng văn phòng cho MacBook: 120.000đ - 180.000đ\nVệ sinh MacBook + thay keo tản nhiệt cao cấp: 150.000đ\nCứu dữ liệu USB, thẻ nhớ máy ảnh, ổ cứng di động: 200.000đ - 800.000đ',
120000, 800000, 'máy', '92 Lý Tự Trọng, TP. Nha Trang', 'Phường Lộc Thọ', 0.9, '0918234567', '0918234567', '08:00 - 20:30',
'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 186),

(43, 2, 5, 'Sửa chữa máy tính văn phòng và bảo trì hệ thống mạng doanh nghiệp',
'Cung cấp dịch vụ IT tận nơi cho văn phòng công ty, khách sạn, kế toán tại Nha Trang. Sửa lỗi chia sẻ máy in qua mạng LAN, cài đặt chữ ký số, phần mềm MISA và bảo trì định kỳ.',
'Khắc phục lỗi máy in không in được qua mạng LAN/Wi-Fi: 80.000đ - 120.000đ\nKiểm tra và diệt virus, tối ưu máy tính văn phòng: 100.000đ/máy\nBảo trì trọn gói hệ thống máy tính công ty (từ 5 máy): 80.000đ/máy',
80000, 200000, 'máy', '132 Lê Hồng Phong, TP. Nha Trang', 'Phường Phước Hải', 1.6, '0909123456', '0909123456', '07:30 - 20:00',
'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 152),

(44, 3, 5, 'Vệ sinh laptop lấy liền 30 phút và nâng cấp SSD chính hãng Nha Trang',
'Giúp laptop hạ nhiệt từ 10 - 15 độ C, hết kêu quạt gió và chạy nhanh gấp 5 lần nhờ nâng cấp ổ cứng SSD NVMe và RAM chính hãng Kingston, Samsung, Crucial.',
'Vệ sinh laptop lấy liền 30 phút trước mặt khách: 100.000đ\nNâng cấp RAM Laptop 8GB / 16GB DDR4/DDR5: 380.000đ - 750.000đ\nThay sạc laptop zin các hãng Dell, HP, Asus, Lenovo: 220.000đ - 420.000đ',
100000, 750000, 'máy', '310 Đường 2 Tháng 4, TP. Nha Trang', 'Phường Vĩnh Hải', 1.8, '0918234567', '0918234567', '07:30 - 21:00',
'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 169),

(45, 4, 6, 'Giúp việc nhà theo giờ tận tâm Cô Tư Nha Trang',
'Nhận dọn dẹp nhà cửa, lau chùi bếp núc, giặt ủi quần áo và sơ chế nấu ăn gia đình theo giờ hoặc định kỳ hàng tuần tại trung tâm TP. Nha Trang. Thật thà, sạch sẽ, kỹ lưỡng.',
'Giúp việc nhà lẻ theo giờ (tối thiểu 2 giờ): 70.000đ/giờ\nCombo dọn dẹp + nấu bữa cơm gia đình (3 giờ): 200.000đ\nGói dọn dẹp cố định tuần 3 buổi (mỗi buổi 3 giờ): 550.000đ/tuần',
70000, 550000, 'giờ', '55 Nguyễn Thị Minh Khai, TP. Nha Trang', 'Phường Tân Lập', 0.9, '0987654321', '0987654321', '06:30 - 19:30',
'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 194),

(46, 4, 6, 'Dịch vụ dọn phòng căn hộ du lịch và homestay theo ngày Nha Trang',
'Chuyên nhận dọn phòng check-out, thay chăn ga gối nệm và bổ sung vật dụng cho các chủ căn hộ Airbnb, Condotel Panorama, Mường Thanh Viễn Triều, Gold Coast tại Nha Trang.',
'Dọn phòng căn hộ Studio / 1 phòng ngủ sau khi khách trả phòng: 90.000đ - 120.000đ/căn\nDọn căn hộ 2 - 3 phòng ngủ: 150.000đ - 220.000đ/căn\nGiặt ủi bộ chăn ga gối trắng khách sạn: 40.000đ/bộ',
90000, 220000, 'căn', '05 Hòn Chồng, TP. Nha Trang', 'Phường Vĩnh Phước', 1.4, '0987654321', '0987654321', '07:00 - 20:00',
'https://images.unsplash.com/photo-1563453392212-326f5e854473?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 181),

(47, 4, 6, 'Vệ sinh máy hút mùi, bếp ga và tẩy dầu mỡ nhà bếp chuyên sâu',
'Tẩy sạch hoàn toàn lớp dầu mỡ lâu ngày bám trên lưới lọc máy hút mùi, bếp từ, bếp ga, tường gạch ốp bếp và lò nướng bằng dung dịch sinh học an toàn không mùi độc hại.',
'Tháo rửa vệ sinh máy hút mùi nhà bếp + thay than hoạt tính: 180.000đ - 280.000đ\nTổng vệ sinh trọn gói khu vực gian bếp gia đình: 250.000đ - 400.000đ\nTẩy cặn canxi vách kính phòng tắm và bồn sứ: 180.000đ - 300.000đ',
180000, 400000, 'lần', '22 Hai Bà Trưng, TP. Nha Trang', 'Phường Xương Huân', 1.3, '0987654321', '0987654321', '07:30 - 19:30',
'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 166),

(48, 4, 6, 'Dịch vụ phun thuốc diệt muỗi, côn trùng và khử khuẩn nhà ở Nha Trang',
'Sử dụng chế phẩm sinh học nhập khẩu được Bộ Y Tế cấp phép để diệt muỗi, kiến ba khoang, gián Đức, mối mọt cho nhà vườn, biệt thự, quán ăn tại Vĩnh Thạnh, Vĩnh Ngọc và Nha Trang.',
'Phun diệt muỗi và côn trùng nhà phố / căn hộ dưới 100m2: 250.000đ - 350.000đ\nĐặt hộp nhử mối và diệt mối tận gốc công trình: 400.000đ - 850.000đ\nPhun khử khuẩn Cloramin B / Nano bạc: 200.000đ - 350.000đ',
200000, 850000, 'lần', '40 Đường 23 Tháng 10, TP. Nha Trang', 'Xã Vĩnh Thạnh', 2.8, '0987654321', '0987654321', '07:00 - 20:00',
'https://images.unsplash.com/photo-1583947215259-38e31be8751f?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 149),

(49, 3, 7, 'Vệ sinh máy lạnh giá rẻ tận tâm Điện Lạnh Minh Đức Nha Trang',
'Đội ngũ thợ điện lạnh chuyên nghiệp nhận xịt rửa vệ sinh máy lạnh treo tường, máy lạnh tủ đứng, máy lạnh âm trần cassette cho gia đình, quán cà phê, văn phòng tại trung tâm Nha Trang.',
'Vệ sinh máy lạnh treo tường (1 - 2 HP): 120.000đ - 150.000đ/máy\nVệ sinh máy lạnh âm trần / tủ đứng công suất lớn: 280.000đ - 400.000đ/máy\nBơm bổ sung gas máy lạnh thiếu hụt: 150.000đ - 250.000đ',
120000, 400000, 'máy', '76 Hùng Vương, TP. Nha Trang', 'Phường Lộc Thọ', 0.8, '0918234567', '0918234567', '07:00 - 21:00',
'https://images.unsplash.com/photo-1585338107529-13afc5f02586?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 212),

(50, 3, 7, 'Thợ sửa bình nước nóng năng lượng mặt trời và bình nóng lạnh Nha Trang',
'Chuyên súc rửa ống thủy tinh bình nước nóng năng lượng mặt trời, thay ron cao su bị rò nước, hàn ống nhiệt PPR và sửa bình nóng lạnh gián tiếp Ariston, Ferroli không nóng.',
'Vệ sinh bình nóng lạnh + thay thanh magie chống cặn: 150.000đ - 250.000đ\nThay ron, sửa rò nước bình năng lượng mặt trời: 180.000đ - 350.000đ\nThay ống thủy tinh thu nhiệt bị vỡ: 200.000đ - 300.000đ/ống',
150000, 350000, 'lần', '145 Nguyễn Khuyến, TP. Nha Trang', 'Phường Vĩnh Hải', 1.9, '0918234567', '0918234567', '07:00 - 20:00',
'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 173),

(51, 3, 7, 'Sửa bếp từ, lò vi sóng và nồi chiên không dầu tận nhà Nha Trang',
'Chuyên sửa chữa các thiết bị điện gia dụng nhà bếp: bếp điện từ đôi/đơn báo lỗi E0, E1, E2, không nhận nồi; lò vi sóng không nóng, đánh lửa; nồi chiên không dầu và máy rửa chén.',
'Kiểm tra và sửa lò vi sóng không nóng / liệt phím: 150.000đ - 300.000đ\nSửa bo mạch công suất bếp từ / bếp hồng ngoại: 200.000đ - 450.000đ\nSửa nồi cơm điện cao tần, nồi chiên không dầu: 120.000đ - 250.000đ',
120000, 450000, 'máy', '33 Trịnh Phong, TP. Nha Trang', 'Phường Phước Tiến', 1.1, '0918234567', '0918234567', '07:30 - 20:30',
'https://images.unsplash.com/photo-1556911261-6bd341186b2f?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 158),

(52, 3, 7, 'Dịch vụ dò tìm rò rỉ nước ngầm và chống thấm nhà vệ sinh Nha Trang',
'Sử dụng máy siêu âm dò tìm chính xác vị trí vỡ ống nước ngầm làm tăng tiền nước đột biến mà không cần đục phá nhiều, kết hợp xử lý chống thấm cổ ống và sàn nhà vệ sinh.',
'Khảo sát và dò tìm vị trí rò rỉ ống nước bằng máy siêu âm: 250.000đ - 500.000đ\nHàn nối và đi nổi đường ống cấp thoát nước mới: 150.000đ - 400.000đ\nXử lý chống thấm cổ ống xuyên sàn nhà tắm: 300.000đ - 600.000đ',
150000, 600000, 'lần', '68 Cầu Dứa Phú Nông, TP. Nha Trang', 'Xã Vĩnh Ngọc', 2.5, '0918234567', '0918234567', '07:00 - 19:30',
'https://images.unsplash.com/photo-1585704032915-c3400ca199e7?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 144),

(53, 5, 8, 'Dịch vụ tắm chó mèo tận nhà lưu động PetGo Nha Trang',
'Dành cho các bé chó mèo sợ ra đường hoặc giống chó lớn (Alaska, Husky, Golden, Becgie) khó chở đi spa. Nhân viên mang đầy đủ sữa tắm, bàn chải, máy sấy công suất lớn đến tận nhà phục vụ.',
'Tắm sấy vệ sinh tận nhà cho mèo / chó nhỏ dưới 7kg: 130.000đ\nTắm sấy tận nhà cho chó trung bình (7kg - 15kg): 180.000đ\nTắm sấy chải lông rụng cho chó lớn (trên 15kg): 250.000đ - 350.000đ',
130000, 350000, 'lượt', '44 Trần Quang Khải, TP. Nha Trang', 'Phường Lộc Thọ', 0.8, '0933445566', '0933445566', '08:00 - 20:00',
'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 185),

(54, 5, 8, 'Khách sạn thú cưng có camera 24/7 Pawradise Nha Trang',
'Hệ thống phòng lưu trú thú cưng cao cấp có điều hòa mát mẻ, sân chơi cỏ nhân tạo tắm nắng mỗi sáng và camera trực tuyến để chủ nhân xem bé mọi lúc trên điện thoại.',
'Phòng Standard cho mèo / cún nhỏ: 120.000đ/ngày\nPhòng VIP rộng rãi có ban công riêng: 180.000đ - 250.000đ/ngày\nGói gửi theo tuần (giảm 15% + tặng 1 lần tắm spa miễn phí): 750.000đ/tuần',
120000, 750000, 'ngày', '77 Thích Quảng Đức, TP. Nha Trang', 'Phường Phước Hải', 1.6, '0933445566', '0933445566', '07:00 - 21:30',
'https://images.unsplash.com/photo-1541599540903-216a46ca1dc0?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 168),

(55, 5, 8, 'Cắt tỉa lông nghệ thuật và nhuộm lông thú cưng Coco Pet Nha Trang',
'Chuyên tạo kiểu lông thú cưng phong cách Nhật - Hàn, nhuộm tai/đuôi bằng màu nhuộm thực vật an toàn cho da thú cưng và ủ bùn khoáng phục hồi lông xơ rối.',
'Combo tắm + cắt tạo kiểu toàn thân theo yêu cầu: 200.000đ - 320.000đ\nỦ bùn khoáng biển sâu trị nấm ngứa, rụng lông: 120.000đ\nNhuộm thẩm mỹ tai và đuôi bằng màu hữu cơ: 150.000đ - 250.000đ',
120000, 320000, 'lượt', '25 Phạm Văn Đồng, TP. Nha Trang', 'Phường Vĩnh Phước', 1.5, '0933445566', '0933445566', '08:00 - 20:30',
'https://images.unsplash.com/photo-1591769225440-811ad7d6eab3?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 154),

(56, 5, 8, 'Dịch vụ dắt chó đi dạo và huấn luyện vâng lời cơ bản tại Nha Trang',
'Nhận dắt cún đi dạo vận động dọc công viên biển Nha Trang mỗi sáng/chiều cho chủ bận rộn và huấn luyện các kỹ năng đi vệ sinh đúng chỗ, ngồi, nằm, bắt tay, không sủa bậy.',
'Dắt thú cưng đi dạo vận động 60 phút: 70.000đ/buổi\nHuấn luyện đi vệ sinh đúng chỗ & vâng lời cơ bản tại nhà: 180.000đ/buổi\nGói chăm sóc & cho thú cưng ăn tại nhà khi chủ vắng mặt: 100.000đ/lần',
70000, 180000, 'buổi', '110 Điện Biên Phủ, TP. Nha Trang', 'Phường Vĩnh Hòa', 2.1, '0933445566', '0933445566', '06:00 - 20:00',
'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=800&q=80', 1, 0, 'active', 146);

-- ==========================================================
-- 4. BẢNG ĐÁNH GIÁ DỊCH VỤ (REVIEWS)
-- ==========================================================
CREATE TABLE `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `service_id` INT NOT NULL,
    `user_id` INT DEFAULT NULL,
    `reviewer_name` VARCHAR(100) NOT NULL,
    `rating` INT NOT NULL,
    `comment` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reviews` (`service_id`, `user_id`, `reviewer_name`, `rating`, `comment`, `created_at`) VALUES
(1, 6, 'Lê Hoàng Khách', 5, 'Xe mình bị bể bánh lúc 10h đêm ở đường Trần Phú (Phường Lộc Thọ), gọi anh Tuấn tầm 12 phút là có mặt. Giá vá xe đúng như niêm yết trên web, rất nhiệt tình!', '2026-11-01 22:30:00'),
(1, 7, 'Nguyễn Ngọc Mai', 5, 'Dịch vụ cứu hộ nhanh như đặt Grab. Thay bình ắc quy tận nơi ở Vĩnh Hòa có phiếu bảo hành đàng hoàng, rất yên tâm.', '2026-11-02 14:15:00'),
(2, 6, 'Lê Hoàng Khách', 5, 'Thay pin iPhone 13 Pro Max tận nơi luôn, kỹ thuật viên làm cẩn thận, kiểm tra áp suất chống nước đầy đủ.', '2026-11-03 10:20:00'),
(3, 6, 'Trần Minh Quân', 5, 'Barber ở đường 2 Tháng 4 cắt rất kỹ, tư vấn kiểu tóc hợp khuôn mặt. Đặt lịch trước qua web đến nơi không phải chờ.', '2026-11-03 16:45:00'),
(4, 7, 'Nguyễn Ngọc Mai', 5, 'Cô giáo dạy kèm môn Toán và Tiếng Anh cho bé nhà mình ở Phước Hải rất tận tâm, sau 1 tháng cháu tiến bộ rõ rệt.', '2026-11-04 09:00:00'),
(5, 6, 'Lê Hoàng Khách', 5, 'Laptop mình bị nóng với chậm, gọi anh kỹ thuật gần ĐH Nha Trang qua vệ sinh tra keo MX-4 với nâng cấp SSD chạy mượt hẳn!', '2026-11-04 15:10:00'),
(6, 7, 'Nguyễn Ngọc Mai', 4, 'Nhân viên dọn căn hộ rất sạch sẽ, đúng giờ, mang đầy đủ máy hút bụi và dung dịch tẩy rửa thơm dễ chịu.', '2026-11-04 18:00:00'),
(7, 6, 'Phạm Thanh Sơn', 5, 'Vệ sinh 2 máy lạnh tại nhà giá chỉ 120k/máy, thợ có áo trùm hứng nước sạch sẽ không làm bẩn tường.', '2026-11-05 08:30:00'),
(8, 7, 'Nguyễn Ngọc Mai', 5, 'Bé Corgi nhà mình đi spa về thơm phức, tỉa lông tròn xoe nhìn cưng xỉu!', '2026-11-05 11:20:00');

-- ==========================================================
-- 5. BẢNG ĐẶT LỊCH / YÊU CẦU DỊCH VỤ (BOOKINGS)
-- ==========================================================
CREATE TABLE `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_code` VARCHAR(30) NOT NULL,
    `service_id` INT NOT NULL,
    `customer_id` INT DEFAULT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `customer_address` VARCHAR(255) NOT NULL,
    `booking_date` DATE NOT NULL,
    `booking_time` VARCHAR(50) NOT NULL,
    `note` TEXT DEFAULT NULL,
    `estimated_price` DECIMAL(12,0) DEFAULT 0,
    `status` ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `bookings` (`booking_code`, `service_id`, `customer_id`, `customer_name`, `customer_phone`, `customer_address`, `booking_date`, `booking_time`, `note`, `estimated_price`, `status`) VALUES
('NEAR-261101', 1, 6, 'Lê Hoàng Khách', '0977889900', '45 Nguyễn Thiện Thuật, Phường Tân Lập, TP. Nha Trang', '2026-11-05', '14:30', 'Xe tay ga AirBlade bị hết bình không đề được, nhờ anh mang đồ kích bình giúp.', 50000, 'completed'),
('NEAR-261102', 5, 6, 'Lê Hoàng Khách', '0977889900', '02 Nguyễn Đình Chiểu, Phường Vĩnh Phước, TP. Nha Trang', '2026-11-06', '09:00', 'Vệ sinh laptop Asus Gaming và cài lại Windows 11 bản quyền.', 190000, 'confirmed'),
('NEAR-261103', 6, 7, 'Nguyễn Ngọc Mai', '0966112233', '32 Phạm Văn Đồng, Phường Vĩnh Hòa, TP. Nha Trang', '2026-11-06', '15:00', 'Dọn vệ sinh căn hộ 2 phòng ngủ trong 3 giờ và giặt 1 bộ ghế sofa.', 460000, 'pending');
