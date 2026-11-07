<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'website_tim_dich_vu');

/**
 * Kết nối MySQL (PDO) và tự động khởi tạo / đồng bộ CSDL từ database.sql
 */
function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // Kết nối tới MySQL Server trước
        $dsnServer = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
        $pdoServer = new PDO($dsnServer, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
        ]);

        // Kiểm tra xem database đã tồn tại và có bảng services chưa
        $stmt = $pdoServer->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        $dbExists = $stmt->fetch();
        $stmt->closeCursor();

        $needInit = false;
        if (!$dbExists) {
            $needInit = true;
        } else {
            $pdoServer->exec("USE `" . DB_NAME . "`");
            $stmtTbl = $pdoServer->query("SHOW TABLES LIKE 'services'");
            $checkTable = $stmtTbl->fetch();
            $stmtTbl->closeCursor();
            if (!$checkTable) {
                $needInit = true;
            } else {
                // Tự động cập nhật dữ liệu sang TP. Nha Trang nếu DB cũ vẫn còn lưu 'Quận 1'
                $stmtOld = $pdoServer->query("SELECT COUNT(*) AS cnt FROM services WHERE district = 'Quận 1'");
                $oldData = $stmtOld->fetch();
                $stmtOld->closeCursor();
                if ($oldData && (int)$oldData['cnt'] > 0) {
                    $needInit = true;
                } else {
                    // Đồng bộ cách viết hoa chuẩn câu tiếng Việt cho dữ liệu mẫu mà không xóa dữ liệu người dùng tự đăng
                    $stmtTitleCase = $pdoServer->query("SELECT COUNT(*) AS cnt FROM services WHERE id = 1 AND title LIKE 'Cứu Hộ &%'");
                    $hasTitleCase = $stmtTitleCase->fetch();
                    $stmtTitleCase->closeCursor();
                    if ($hasTitleCase && (int)$hasTitleCase['cnt'] > 0) {
                        $pdoServer->exec("
                            UPDATE `users` SET `fullname` = 'Quản trị viên hệ thống' WHERE `id` = 1;
                            UPDATE `users` SET `fullname` = 'Nguyễn Minh Tuấn (Đối tác)' WHERE `id` = 2;
                            UPDATE `users` SET `fullname` = 'Trần Hoàng Nam (Thợ điện máy)' WHERE `id` = 3;
                            UPDATE `users` SET `fullname` = 'Lê Thị Thu Hà (Gia sư và dọn nhà)' WHERE `id` = 4;
                            UPDATE `users` SET `fullname` = 'Phạm Quốc Bảo (Barber và sửa xe)' WHERE `id` = 5;
                            UPDATE `users` SET `fullname` = 'Lê Hoàng Khách (Người dùng)' WHERE `id` = 6;
                            UPDATE `categories` SET `name` = 'Cắt tóc và Barber' WHERE `id` = 3;
                            UPDATE `categories` SET `name` = 'Sửa máy tính và IT' WHERE `id` = 5;
                            UPDATE `categories` SET `name` = 'Dọn nhà và vệ sinh' WHERE `id` = 6;
                            UPDATE `categories` SET `name` = 'Điện lạnh và điện nước' WHERE `id` = 7;
                            UPDATE `services` SET `title` = 'Cứu hộ và sửa xe máy lưu động 24/7 Minh Tuấn Nha Trang', `open_hours` = '24/24 (Cả lễ và Chủ nhật)' WHERE `id` = 1;
                            UPDATE `services` SET `title` = 'iFix Nha Trang - Sửa điện thoại và thay pin, ép kính lấy liền' WHERE `id` = 2;
                            UPDATE `services` SET `title` = 'The Gentlemen Barber Shop Nha Trang - Cắt tóc tại tiệm và tại nhà' WHERE `id` = 3;
                            UPDATE `services` SET `title` = 'Trung tâm gia sư Nha Trang - Dạy kèm Toán, Lý, Hóa và Tiếng Anh IELTS' WHERE `id` = 4;
                            UPDATE `services` SET `title` = 'Bác sĩ máy tính Nha Trang - Cài Win, vệ sinh và sửa laptop tận nơi' WHERE `id` = 5;
                            UPDATE `services` SET `title` = 'GreenClean Nha Trang - Dọn nhà, căn hộ, homestay và giặt sofa' WHERE `id` = 6;
                            UPDATE `services` SET `title` = 'Điện lạnh Phố Biển - Vệ sinh máy lạnh, sửa tủ lạnh và điện nước' WHERE `id` = 7;
                            UPDATE `services` SET `title` = 'HappyPet Spa Nha Trang - Tắm thú cưng, cắt tỉa lông và khách sạn chó mèo' WHERE `id` = 8;
                            UPDATE `services` SET `title` = 'Tiệm sửa xe và bảo dưỡng tay ga chuyên nghiệp Quốc Bảo Nha Trang' WHERE `id` = 9;
                        ");
                    }
                    // Đồng bộ mã đặt lịch sang tiền tố NEAR- và bỏ chú thích vai trò trong tên tài khoản mẫu
                    $pdoServer->exec("
                        UPDATE `bookings` SET `booking_code` = REPLACE(`booking_code`, 'GRAB-', 'NEAR-') WHERE `booking_code` LIKE 'GRAB-%';
                        UPDATE `users` SET `fullname` = 'Nguyễn Minh Tuấn' WHERE `id` = 2 AND `fullname` LIKE 'Nguyễn Minh Tuấn (%';
                        UPDATE `users` SET `fullname` = 'Trần Hoàng Nam' WHERE `id` = 3 AND `fullname` LIKE 'Trần Hoàng Nam (%';
                        UPDATE `users` SET `fullname` = 'Lê Thị Thu Hà' WHERE `id` = 4 AND `fullname` LIKE 'Lê Thị Thu Hà (%';
                        UPDATE `users` SET `fullname` = 'Phạm Quốc Bảo' WHERE `id` = 5 AND `fullname` LIKE 'Phạm Quốc Bảo (%';
                        UPDATE `users` SET `fullname` = 'Lê Hoàng Khách' WHERE `id` = 6 AND `fullname` LIKE 'Lê Hoàng Khách (%';
                    ");

                    // Tự động bổ sung đủ 7 dịch vụ cho mỗi danh mục (tổng 56 dịch vụ mẫu) nếu chưa có
                    $stmtCountMore = $pdoServer->query("SELECT COUNT(*) AS cnt FROM services WHERE id BETWEEN 10 AND 56");
                    $moreData = $stmtCountMore->fetch();
                    $stmtCountMore->closeCursor();
                    if (!$moreData || (int)$moreData['cnt'] < 47) {
                        $pdoServer->exec("
                            INSERT IGNORE INTO `services` (`id`, `provider_id`, `category_id`, `title`, `description`, `price_list`, `price_min`, `price_max`, `price_unit`, `address`, `district`, `distance_km`, `phone_contact`, `zalo_contact`, `open_hours`, `image`, `is_home_service`, `is_featured`, `status`, `views`) VALUES
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
                            'Đội ngũ vệ sinh công nghiệp chuyên nghiệp với máy chà sàn, máy hút bụi công suất lớn. Nhận tổng vệ sinh nhà vừa xây xong, nhà mới sửa chữa, biệt thự, nhà hàng, khách sạn và lau kính mặt ngoài tại TP. Nha Trang.',
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
                        ");
                    }
                }
            }
        }

        // Tự động nạp file database.sql nếu chưa có dữ liệu hoặc cần đồng bộ sang TP. Nha Trang
        if ($needInit) {
            $sqlFile = __DIR__ . '/../database.sql';
            if (file_exists($sqlFile)) {
                $sqlContent = file_get_contents($sqlFile);
                $stmtInit = $pdoServer->prepare($sqlContent);
                $stmtInit->execute();
                do {
                    // Giải phóng toàn bộ các resultset của multi-query
                } while ($stmtInit->nextRowset());
                $stmtInit->closeCursor();
            }
        }
        $pdoServer = null;

        // Kết nối chính thức vào database website_tim_dich_vu
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        return $pdo;
    } catch (PDOException $e) {
        die("<div style='font-family:sans-serif;max-width:650px;margin:50px auto;padding:24px;border-radius:12px;background:#fff3f3;border:1px solid #ffcdd2;color:#b71c1c;'>
                <h3>⚠️ Không thể kết nối Cơ sở dữ liệu MySQL</h3>
                <p>Vui lòng kiểm tra lại <strong>XAMPP Control Panel</strong> và đảm bảo module <strong>MySQL</strong> đang ở trạng thái Running.</p>
                <p><small>Chi tiết lỗi: " . htmlspecialchars($e->getMessage()) . "</small></p>
            </div>");
    }
}

$pdo = getDBConnection();

/**
 * Danh sách các khu vực (Phường/Xã thuộc Toàn TP. Nha Trang) hỗ trợ tìm kiếm quanh mình
 */
function getDistricts() {
    return [
        'Phường Lộc Thọ',
        'Phường Tân Lập',
        'Phường Phước Tiến',
        'Phường Phước Hải',
        'Phường Vĩnh Hải',
        'Phường Vĩnh Phước',
        'Phường Vĩnh Hòa',
        'Phường Vĩnh Nguyên',
        'Phường Xương Huân',
        'Xã Vĩnh Ngọc',
        'Xã Vĩnh Thạnh'
    ];
}

/**
 * Định dạng tiền tệ VNĐ
 */
function formatPrice($price) {
    if ($price <= 0) return 'Miễn phí';
    return number_format((float)$price, 0, ',', '.') . 'đ';
}

/**
 * Định dạng khoảng giá tham khảo
 */
function formatPriceRange($min, $max, $unit = 'lần') {
    if ($min == $max || $max <= 0) {
        return formatPrice($min) . ($unit ? " / $unit" : '');
    }
    return number_format((float)$min, 0, ',', '.') . 'đ - ' . number_format((float)$max, 0, ',', '.') . 'đ' . ($unit ? " / $unit" : '');
}

/**
 * Hiển thị ngôi sao đánh giá HTML
 */
function renderStars($rating) {
    $rating = round((float)$rating * 2) / 2;
    $html = '<span class="star-rating">';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="fa-solid fa-star"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        } else {
            $html .= '<i class="fa-regular fa-star"></i>';
        }
    }
    $html .= '</span>';
    return $html;
}

/**
 * Kiểm tra trạng thái đăng nhập
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Lấy thông tin user đang đăng nhập
 */
function currentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Gán thông báo Flash Message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

/**
 * Lấy và xóa thông báo Flash Message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
