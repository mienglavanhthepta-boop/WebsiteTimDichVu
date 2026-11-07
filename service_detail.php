<?php
require_once __DIR__ . '/config/db.php';

$serviceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($serviceId <= 0) {
    header('Location: search.php');
    exit;
}

// Xử lý khi người dùng GỬI YÊU CẦU ĐẶT DỊCH VỤ / LIÊN HỆ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_booking') {
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $customerAddress = trim($_POST['customer_address'] ?? '');
        $bookingDate = $_POST['booking_date'] ?? date('Y-m-d');
        $bookingTime = trim($_POST['booking_time'] ?? 'Ngay bây giờ (Trong 15 phút)');
        $note = trim($_POST['note'] ?? '');
        $estimatedPrice = (float)($_POST['estimated_price'] ?? 0);
        $customerId = isLoggedIn() ? (int)$_SESSION['user']['id'] : null;

        if ($customerName === '' || $customerPhone === '' || $customerAddress === '') {
            setFlash('error', 'Vui lòng nhập đầy đủ Họ tên, Số điện thoại và Địa chỉ cần phục vụ!');
        } else {
            $bookingCode = 'NEAR-' . rand(100000, 999999);
            $stmtBook = $pdo->prepare("
                INSERT INTO bookings (booking_code, service_id, customer_id, customer_name, customer_phone, customer_address, booking_date, booking_time, note, estimated_price, status)
                VALUES (:code, :sid, :cid, :name, :phone, :addr, :bdate, :btime, :note, :price, 'pending')
            ");
            $stmtBook->execute([
                ':code' => $bookingCode,
                ':sid' => $serviceId,
                ':cid' => $customerId,
                ':name' => $customerName,
                ':phone' => $customerPhone,
                ':addr' => $customerAddress,
                ':bdate' => $bookingDate,
                ':btime' => $bookingTime,
                ':note' => $note,
                ':price' => $estimatedPrice
            ]);

            // Lưu mã đặt lịch vào session để khách chưa đăng nhập cũng xem được lịch vừa đặt
            if (!isset($_SESSION['guest_bookings'])) {
                $_SESSION['guest_bookings'] = [];
            }
            $_SESSION['guest_bookings'][] = $bookingCode;

            setFlash('success', "Đặt dịch vụ thành công! Mã yêu cầu của bạn là $bookingCode. Nhà cung cấp sẽ liên hệ bạn trong ít phút.");
            header("Location: booking_history.php?code=" . urlencode($bookingCode));
            exit;
        }
    }

    // Xử lý khi người dùng GỬI ĐÁNH GIÁ (REVIEW)
    if ($_POST['action'] === 'submit_review') {
        $reviewerName = trim($_POST['reviewer_name'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');
        $userId = isLoggedIn() ? (int)$_SESSION['user']['id'] : null;

        if ($rating < 1 || $rating > 5) $rating = 5;

        if ($reviewerName === '' || $comment === '') {
            setFlash('error', 'Vui lòng nhập tên của bạn và nội dung đánh giá!');
        } else {
            $stmtRev = $pdo->prepare("
                INSERT INTO reviews (service_id, user_id, reviewer_name, rating, comment)
                VALUES (:sid, :uid, :name, :rating, :comment)
            ");
            $stmtRev->execute([
                ':sid' => $serviceId,
                ':uid' => $userId,
                ':name' => $reviewerName,
                ':rating' => $rating,
                ':comment' => $comment
            ]);
            setFlash('success', 'Cảm ơn bạn đã gửi đánh giá cho dịch vụ này!');
            header("Location: service_detail.php?id=$serviceId#reviews");
            exit;
        }
    }
}

// Tăng lượt xem dịch vụ
$pdo->prepare("UPDATE services SET views = views + 1 WHERE id = ?")->execute([$serviceId]);

// Truy vấn thông tin chi tiết dịch vụ
$stmt = $pdo->prepare("
    SELECT s.*,
           c.name AS category_name, c.icon AS category_icon, c.color AS category_color,
           u.fullname AS provider_name, u.email AS provider_email, u.phone AS provider_phone, u.is_verified AS provider_verified,
           COALESCE(AVG(r.rating), 5.0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM services s
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON s.provider_id = u.id
    LEFT JOIN reviews r ON r.service_id = s.id
    WHERE s.id = :id
    GROUP BY s.id
");
$stmt->execute([':id' => $serviceId]);
$service = $stmt->fetch();

if (!$service) {
    setFlash('error', 'Dịch vụ không tồn tại hoặc đã bị xóa.');
    header('Location: search.php');
    exit;
}

// Lấy danh sách đánh giá của dịch vụ này
$stmtRevs = $pdo->prepare("SELECT * FROM reviews WHERE service_id = ? ORDER BY created_at DESC");
$stmtRevs->execute([$serviceId]);
$reviews = $stmtRevs->fetchAll();

// Lấy các dịch vụ tương tự cùng danh mục
$stmtRelated = $pdo->prepare("
    SELECT s.*,
           c.name AS category_name, c.icon AS category_icon,
           u.fullname AS provider_name,
           COALESCE(AVG(r.rating), 5.0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM services s
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON s.provider_id = u.id
    LEFT JOIN reviews r ON r.service_id = s.id
    WHERE s.category_id = :cat_id AND s.id != :id AND s.status = 'active'
    GROUP BY s.id
    LIMIT 3
");
$stmtRelated->execute([':cat_id' => $service['category_id'], ':id' => $serviceId]);
$relatedServices = $stmtRelated->fetchAll();

$pageTitle = $service['title'];
require_once __DIR__ . '/includes/header.php';

$estMinutes = max(5, round((float)$service['distance_km'] * 8));
$priceLines = array_filter(array_map('trim', explode("\n", (string)$service['price_list'])));
?>

<div class="container">
    <!-- Breadcrumb & Nút Quản Lý Nhanh Dịch Vụ -->
    <div style="margin-top:20px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <div style="font-size:13.5px; color:var(--text-muted); display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <a href="index.php" style="color:var(--grab-green); font-weight:600;"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <a href="search.php?category_id=<?php echo (int)$service['category_id']; ?>" style="color:var(--grab-green); font-weight:600;">
                <?php echo htmlspecialchars($service['category_name']); ?>
            </a>
            <span>/</span>
            <span><?php echo htmlspecialchars($service['title']); ?></span>
        </div>

        <div style="display:flex; gap:8px; align-items:center;">
            <a href="service_form.php?id=<?php echo (int)$service['id']; ?>" class="btn btn-outline-grab btn-sm">
                <i class="fa-solid fa-pen-to-square"></i> Sửa dịch vụ
            </a>
            <form method="POST" action="provider_dashboard.php" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này không?');">
                <input type="hidden" name="action" value="delete_service">
                <input type="hidden" name="service_id" value="<?php echo (int)$service['id']; ?>">
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-trash"></i> Xóa dịch vụ
                </button>
            </form>
        </div>
    </div>

    <div class="detail-layout">
        <!-- CỘT NỘI DUNG CHÍNH BÊN TRÁI -->
        <div>
            <!-- Thông tin chính & Hình ảnh -->
            <div class="card-box" style="padding:0; overflow:hidden;">
                <div style="position:relative; height:360px; background:#eaeef2;">
                    <img src="<?php echo htmlspecialchars($service['image']); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>" style="width:100%; height:100%; object-fit:cover;">
                    <div class="badge-top-left" style="top:16px; left:16px;">
                        <span class="badge-grab-partner" style="font-size:13px; padding:6px 14px;">
                            <i class="fa-solid <?php echo htmlspecialchars($service['category_icon']); ?>"></i>
                            <?php echo htmlspecialchars($service['category_name']); ?>
                        </span>
                        <?php if ($service['is_home_service']): ?>
                            <span class="badge-home-service" style="font-size:13px; padding:6px 14px;">
                                <i class="fa-solid fa-motorcycle"></i> Hỗ trợ đến tận nơi
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="badge-distance" style="bottom:16px; right:16px; font-size:13.5px; padding:6px 14px;">
                        <i class="fa-solid fa-location-arrow"></i>
                        Cách bạn <?php echo number_format((float)$service['distance_km'], 1); ?> km • Di chuyển ~<?php echo $estMinutes; ?> phút
                    </div>
                </div>

                <div style="padding:26px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:12px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <?php echo renderStars($service['avg_rating']); ?>
                            <strong style="font-size:16px;"><?php echo number_format((float)$service['avg_rating'], 1); ?>/5.0</strong>
                            <span style="color:var(--text-muted); font-size:14px;">(<?php echo (int)$service['review_count']; ?> đánh giá)</span>
                        </div>
                        <div style="font-size:13.5px; color:var(--text-muted);">
                            <i class="fa-regular fa-eye"></i> <?php echo (int)$service['views']; ?> lượt xem
                        </div>
                    </div>

                    <h1 style="font-size:25px; font-weight:800; line-height:1.35; margin-bottom:16px;">
                        <?php echo htmlspecialchars($service['title']); ?>
                    </h1>

                    <div class="info-highlight-grid">
                        <div class="info-highlight-item">
                            <div class="info-highlight-icon"><i class="fa-solid fa-tag"></i></div>
                            <div>
                                <div style="font-size:12px; color:var(--text-muted); font-weight:600;">Giá tham khảo</div>
                                <div style="font-size:16px; font-weight:800; color:var(--grab-green-dark);">
                                    <?php echo formatPriceRange($service['price_min'], $service['price_max'], $service['price_unit']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="info-highlight-item">
                            <div class="info-highlight-icon"><i class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <div style="font-size:12px; color:var(--text-muted); font-weight:600;">Khu vực phục vụ</div>
                                <div style="font-size:14px; font-weight:700; color:var(--text-main);">
                                    <?php echo htmlspecialchars($service['district']); ?> • <?php echo htmlspecialchars($service['address']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="info-highlight-item">
                            <div class="info-highlight-icon"><i class="fa-solid fa-clock"></i></div>
                            <div>
                                <div style="font-size:12px; color:var(--text-muted); font-weight:600;">Giờ mở cửa</div>
                                <div style="font-size:14px; font-weight:700; color:var(--text-main);">
                                    <?php echo htmlspecialchars($service['open_hours']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 style="font-size:18px; font-weight:700; margin-bottom:10px;">
                        <i class="fa-solid fa-circle-info" style="color:var(--grab-green); margin-right:6px;"></i>
                        Mô tả chi tiết dịch vụ
                    </h3>
                    <p style="font-size:15px; color:var(--text-body); white-space:pre-line; line-height:1.75;">
                        <?php echo htmlspecialchars($service['description']); ?>
                    </p>
                </div>
            </div>

            <!-- BẢNG GIÁ THAM KHẢO NIÊM YẾT -->
            <div class="card-box">
                <h3 style="font-size:19px; font-weight:800; margin-bottom:6px; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-receipt" style="color:var(--grab-green);"></i>
                    Bảng giá tham khảo minh bạch
                </h3>
                <p style="font-size:13.5px; color:var(--text-muted); margin-bottom:16px;">
                    Giá được nhà cung cấp niêm yết công khai. Thợ sẽ báo giá chính xác trước khi tiến hành thực hiện.
                </p>

                <?php if (!empty($priceLines)): ?>
                    <div class="price-table-list">
                        <?php foreach ($priceLines as $line): ?>
                            <?php
                            $parts = explode(':', $line, 2);
                            $itemName = trim($parts[0]);
                            $itemPrice = isset($parts[1]) ? trim($parts[1]) : '';
                            ?>
                            <div class="price-table-item">
                                <span style="font-weight:600;">
                                    <i class="fa-solid fa-check-circle" style="color:var(--grab-green); margin-right:8px;"></i>
                                    <?php echo htmlspecialchars($itemName); ?>
                                </span>
                                <?php if ($itemPrice !== ''): ?>
                                    <strong style="color:var(--grab-green-dark); font-size:15px;"><?php echo htmlspecialchars($itemPrice); ?></strong>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="price-table-item">
                        <span>Đơn giá dịch vụ tiêu chuẩn</span>
                        <strong style="color:var(--grab-green-dark);"><?php echo formatPriceRange($service['price_min'], $service['price_max'], $service['price_unit']); ?></strong>
                    </div>
                <?php endif; ?>
            </div>

            <!-- BẢN ĐỒ VỊ TRÍ DỊCH VỤ QUANH MÌNH -->
            <div class="card-box">
                <h3 style="font-size:19px; font-weight:800; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-map-location-dot" style="color:var(--grab-green);"></i>
                    Vị trí trên bản đồ (<?php echo htmlspecialchars($service['address'] . ', ' . $service['district']); ?>)
                </h3>
                <div style="border-radius:12px; overflow:hidden; border:1px solid var(--border-color); height:270px;">
                    <?php $mapQuery = urlencode($service['address'] . ', ' . $service['district'] . ', TP Nha Trang, Khánh Hòa'); ?>
                    <iframe
                        width="100%"
                        height="270"
                        style="border:0;"
                        loading="lazy"
                        allowfullscreen
                        src="https://maps.google.com/maps?q=<?php echo $mapQuery; ?>&t=&z=15&ie=UTF8&iwloc=&output=embed">
                    </iframe>
                </div>
            </div>

            <!-- PHẦN ĐÁNH GIÁ & NHẬN XÉT CỦA KHÁCH HÀNG -->
            <div class="card-box" id="reviews">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
                    <h3 style="font-size:19px; font-weight:800; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-star" style="color:var(--grab-yellow);"></i>
                        Đánh giá từ khách hàng (<?php echo count($reviews); ?>)
                    </h3>
                    <div style="background:var(--grab-green-soft); padding:6px 14px; border-radius:99px; font-weight:700; color:var(--grab-green-dark);">
                        Điểm trung bình: <?php echo number_format((float)$service['avg_rating'], 1); ?> / 5.0 ⭐
                    </div>
                </div>

                <!-- Form viết đánh giá -->
                <form action="service_detail.php?id=<?php echo $serviceId; ?>" method="POST" style="background:#f8fafc; padding:18px; border-radius:12px; border:1px solid var(--border-color); margin-bottom:24px;">
                    <input type="hidden" name="action" value="submit_review">
                    <h4 style="font-size:15.5px; font-weight:700; margin-bottom:12px;">Gửi đánh giá trải nghiệm của bạn</h4>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:12px;">
                        <div>
                            <label class="form-label">Tên của bạn</label>
                            <input type="text" name="reviewer_name" class="form-control" required
                                   value="<?php echo htmlspecialchars($currentUser['fullname'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="form-label">Mức độ hài lòng</label>
                            <select name="rating" class="form-control">
                                <option value="5">⭐⭐⭐⭐⭐ (5 sao - Tuyệt vời)</option>
                                <option value="4">⭐⭐⭐⭐ (4 sao - Hài lòng)</option>
                                <option value="3">⭐⭐⭐ (3 sao - Bình thường)</option>
                                <option value="2">⭐⭐ (2 sao - Cần cải thiện)</option>
                                <option value="1">⭐ (1 sao - Kém)</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom:12px;">
                        <label class="form-label">Nhận xét chi tiết</label>
                        <textarea name="comment" rows="3" class="form-control" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-grab">
                        <i class="fa-solid fa-paper-plane"></i> Gửi đánh giá
                    </button>
                </form>

                <!-- Danh sách bình luận -->
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <?php if (empty($reviews)): ?>
                        <p style="color:var(--text-muted); text-align:center; padding:16px;">Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá dịch vụ này!</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div style="padding:16px; border:1px solid var(--border-color); border-radius:12px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                    <div style="font-weight:700; font-size:15px;">
                                        <i class="fa-solid fa-circle-user" style="color:var(--grab-green); margin-right:6px;"></i>
                                        <?php echo htmlspecialchars($rev['reviewer_name']); ?>
                                    </div>
                                    <div style="font-size:12.5px; color:var(--text-muted);">
                                        <?php echo date('d/m/Y H:i', strtotime($rev['created_at'])); ?>
                                    </div>
                                </div>
                                <div style="margin-bottom:8px;">
                                    <?php echo renderStars($rev['rating']); ?>
                                </div>
                                <p style="font-size:14.5px; color:var(--text-body);">
                                    <?php echo nl2br(htmlspecialchars($rev['comment'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- CỘT BÊN PHẢI: LIÊN HỆ NHÀ CUNG CẤP & ĐẶT LỊCH NHANH (GIỐNG GRAB) -->
        <aside class="provider-Booking-sidebar">
            <!-- Thẻ Nhà Cung Cấp -->
            <div class="card-box" style="border-top: 4px solid var(--grab-green);">
                <div style="display:flex; align-items:center; gap:14px; margin-bottom:16px;">
                    <div style="width:56px; height:56px; border-radius:50%; background:var(--grab-green-light); color:var(--grab-green); display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:800;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--grab-green-dark); font-weight:700;">
                            <i class="fa-solid fa-circle-check"></i> Đối tác đã xác thực
                        </div>
                        <h3 style="font-size:17px; font-weight:800;"><?php echo htmlspecialchars($service['provider_name']); ?></h3>
                        <div style="font-size:13px; color:var(--text-muted);">Phản hồi trung bình: ~5 phút</div>
                    </div>
                </div>

                <!-- Các nút Liên hệ trực tiếp -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:18px;">
                    <a href="tel:<?php echo htmlspecialchars($service['phone_contact']); ?>" class="btn btn-grab" style="width:100%;">
                        <i class="fa-solid fa-phone-volume"></i> <?php echo htmlspecialchars($service['phone_contact']); ?>
                    </a>
                    <a href="https://zalo.me/<?php echo htmlspecialchars($service['zalo_contact'] ?: $service['phone_contact']); ?>" target="_blank" class="btn btn-outline-grab" style="width:100%; border-color:#0068ff; color:#0068ff;">
                        <i class="fa-solid fa-comment-dots"></i> Chat Zalo
                    </a>
                </div>

                <hr style="border:none; border-top:1px dashed var(--border-color); margin:18px 0;">

                <!-- Form Đặt Dịch Vụ Tận Nơi Nhanh -->
                <h4 style="font-size:16.5px; font-weight:800; margin-bottom:6px; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-bolt" style="color:var(--grab-accent);"></i>
                    Đặt dịch vụ / Yêu cầu gọi lại
                </h4>
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:14px;">
                    Điền thông tin bên dưới để đặt thợ đến tận nơi hoặc giữ lịch hẹn trước.
                </p>

                <form action="service_detail.php?id=<?php echo $serviceId; ?>" method="POST">
                    <input type="hidden" name="action" value="create_booking">
                    <input type="hidden" name="estimated_price" value="<?php echo (float)$service['price_min']; ?>">

                    <div style="margin-bottom:12px;">
                        <label class="form-label">Họ và tên của bạn</label>
                        <input type="text" name="customer_name" class="form-control" required
                               value="<?php echo htmlspecialchars($currentUser['fullname'] ?? ''); ?>">
                    </div>

                    <div style="margin-bottom:12px;">
                        <label class="form-label">Số điện thoại liên hệ</label>
                        <input type="text" name="customer_phone" class="form-control" required
                               value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                    </div>

                    <div style="margin-bottom:12px;">
                        <label class="form-label">Địa chỉ cần phục vụ / Điểm hẹn</label>
                        <input type="text" name="customer_address" class="form-control" required
                               value="<?php echo htmlspecialchars($currentUser['address'] ?? ''); ?>">
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                        <div>
                            <label class="form-label">Ngày hẹn</label>
                            <input type="date" name="booking_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div>
                            <label class="form-label">Khung giờ</label>
                            <select name="booking_time" class="form-control">
                                <option value="Cần gấp (Có mặt sau 15p)">⚡ Cần gấp (15 phút)</option>
                                <option value="08:00 - 10:00">08:00 - 10:00</option>
                                <option value="10:00 - 12:00">10:00 - 12:00</option>
                                <option value="14:00 - 16:00">14:00 - 16:00</option>
                                <option value="16:00 - 18:00">16:00 - 18:00</option>
                                <option value="18:00 - 21:00">18:00 - 21:00</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label class="form-label">Ghi chú tình trạng / Yêu cầu</label>
                        <textarea name="note" rows="2" class="form-control"></textarea>
                    </div>

                    <div style="background:var(--grab-green-soft); padding:10px 14px; border-radius:8px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; font-size:13.5px;">
                        <span>Chi phí dự kiến từ:</span>
                        <strong style="font-size:16px; color:var(--grab-green-dark);"><?php echo formatPrice($service['price_min']); ?></strong>
                    </div>

                    <button type="submit" class="btn btn-grab btn-lg" style="width:100%;">
                        <i class="fa-solid fa-paper-plane"></i> Xác nhận đặt dịch vụ ngay
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <!-- DỊCH VỤ TƯƠNG TỰ -->
    <?php if (!empty($relatedServices)): ?>
        <div class="section-block" style="padding-top:0;">
            <h2 class="section-title" style="margin-bottom:18px;">
                <i class="fa-solid fa-layer-group" style="color:var(--grab-green);"></i>
                Các dịch vụ tương tự quanh khu vực
            </h2>
            <div class="services-grid">
                <?php foreach ($relatedServices as $srv): ?>
                    <?php include __DIR__ . '/includes/service_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

