<?php
require_once __DIR__ . '/config/db.php';

// Để hỗ trợ thuyết trình Demo mượt mà: Nếu chưa đăng nhập thì mặc định xem dưới quyền Đối tác mẫu (ID = 2)
$activeProviderId = isLoggedIn() ? (int)$_SESSION['user']['id'] : 2;
$isAdmin = isLoggedIn() && $_SESSION['user']['role'] === 'admin';

// Xử lý các thao tác Quản lý dịch vụ & Đơn đặt lịch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // 1. Bật / Tắt trạng thái dịch vụ
    if ($_POST['action'] === 'toggle_service_status') {
        $srvId = (int)($_POST['service_id'] ?? 0);
        $stmtCheck = $pdo->prepare("SELECT status FROM services WHERE id = ?");
        $stmtCheck->execute([$srvId]);
        $row = $stmtCheck->fetch();
        if ($row) {
            $newStatus = ($row['status'] === 'active') ? 'inactive' : 'active';
            $pdo->prepare("UPDATE services SET status = ? WHERE id = ?")->execute([$newStatus, $srvId]);
            setFlash('success', 'Đã cập nhật trạng thái hiển thị của dịch vụ!');
        }
        header('Location: provider_dashboard.php');
        exit;
    }

    // 2. Xóa dịch vụ
    if ($_POST['action'] === 'delete_service') {
        $srvId = (int)($_POST['service_id'] ?? 0);
        $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$srvId]);
        setFlash('warning', 'Đã xóa dịch vụ khỏi hệ thống thành công.');
        header('Location: provider_dashboard.php');
        exit;
    }

    // 3. Cập nhật trạng thái đơn đặt lịch của khách hàng
    if ($_POST['action'] === 'update_booking_status') {
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $newStatus = $_POST['new_status'] ?? 'confirmed';
        if (in_array($newStatus, ['pending', 'confirmed', 'completed', 'cancelled'])) {
            $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$newStatus, $bookingId]);
            setFlash('success', 'Đã cập nhật trạng thái đơn đặt dịch vụ của khách hàng!');
        }
        header('Location: provider_dashboard.php#bookings');
        exit;
    }
}

// Mặc định hiển thị đầy đủ danh sách dịch vụ để dễ nhìn, dễ thao tác; có thể lọc riêng dịch vụ cá nhân với ?view=mine
$viewAll = (!isset($_GET['view']) || $_GET['view'] !== 'mine');

if ($viewAll) {
    $stmtServices = $pdo->query("
        SELECT s.*, c.name AS category_name, u.fullname AS provider_name,
               COALESCE(AVG(r.rating), 5.0) AS avg_rating, COUNT(r.id) AS review_count
        FROM services s
        JOIN categories c ON s.category_id = c.id
        JOIN users u ON s.provider_id = u.id
        LEFT JOIN reviews r ON r.service_id = s.id
        GROUP BY s.id
        ORDER BY s.created_at DESC
    ");
    $myServices = $stmtServices->fetchAll();
} else {
    $stmtServices = $pdo->prepare("
        SELECT s.*, c.name AS category_name, u.fullname AS provider_name,
               COALESCE(AVG(r.rating), 5.0) AS avg_rating, COUNT(r.id) AS review_count
        FROM services s
        JOIN categories c ON s.category_id = c.id
        JOIN users u ON s.provider_id = u.id
        LEFT JOIN reviews r ON r.service_id = s.id
        WHERE s.provider_id = ?
        GROUP BY s.id
        ORDER BY s.created_at DESC
    ");
    $stmtServices->execute([$activeProviderId]);
    $myServices = $stmtServices->fetchAll();
    // Nếu tài khoản vừa đăng ký chưa có dịch vụ nào, lấy danh sách mẫu nếu bật chế độ xem tất cả
}

// Tính toán số liệu thống kê
$totalServices = count($myServices);
$totalViews = 0;
$sumRating = 0;
foreach ($myServices as $item) {
    $totalViews += (int)$item['views'];
    $sumRating += (float)$item['avg_rating'];
}
$avgProviderRating = $totalServices > 0 ? number_format($sumRating / $totalServices, 1) : '5.0';

// Lấy danh sách yêu cầu đặt lịch từ khách hàng
$stmtBookings = $pdo->query("
    SELECT b.*, s.title AS service_title, s.provider_id
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    ORDER BY b.created_at DESC
");
$allBookings = $stmtBookings->fetchAll();

$pageTitle = 'Kênh đối tác - Quản lý dịch vụ';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding:28px 20px 50px;">
    <!-- Tiêu đề kênh đối tác -->
    <div style="background:linear-gradient(135deg, #008e3f 0%, #00B14F 100%); border-radius:var(--radius-md); padding:26px 30px; color:#fff; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:18px; margin-bottom:26px; box-shadow:var(--shadow-md);">
        <div>
            <div style="font-size:13px; font-weight:700; opacity:0.9; margin-bottom:4px;">
                <i class="fa-solid fa-store"></i> Trung tâm đối tác NearService
            </div>
            <h1 style="font-size:24px; font-weight:800;">
                Kênh người cung cấp và quản lý dịch vụ
            </h1>
            <p style="font-size:14px; opacity:0.95; margin-top:4px;">
                Đăng tải dịch vụ mới, cập nhật bảng giá tham khảo và tiếp nhận yêu cầu từ khách hàng quanh khu vực.
            </p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <?php if (!$viewAll): ?>
                <a href="provider_dashboard.php?view=all" class="btn" style="background:rgba(255,255,255,0.2); color:#fff; border:1px solid rgba(255,255,255,0.4);">
                    <i class="fa-solid fa-list"></i> Xem toàn bộ dịch vụ hệ thống
                </a>
            <?php else: ?>
                <a href="provider_dashboard.php?view=mine" class="btn" style="background:rgba(255,255,255,0.2); color:#fff; border:1px solid rgba(255,255,255,0.4);">
                    <i class="fa-solid fa-user-check"></i> Lọc theo đối tác hiện tại
                </a>
            <?php endif; ?>
            <a href="service_form.php" class="btn btn-lg" style="background:#fff; color:var(--grab-green-dark); font-weight:800;">
                <i class="fa-solid fa-plus-circle"></i> Đăng dịch vụ mới
            </a>
        </div>
    </div>

    <!-- 4 THẺ THỐNG KÊ NHANH -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--grab-green-light); color:var(--grab-green);">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <div>
                <div style="font-size:13px; color:var(--text-muted);">Dịch vụ đang quản lý</div>
                <div style="font-size:24px; font-weight:800;"><?php echo $totalServices; ?> dịch vụ</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe; color:#1d4ed8;">
                <i class="fa-solid fa-eye"></i>
            </div>
            <div>
                <div style="font-size:13px; color:var(--text-muted);">Tổng lượt khách xem</div>
                <div style="font-size:24px; font-weight:800;"><?php echo number_format($totalViews); ?> lượt</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7; color:#d97706;">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <div style="font-size:13px; color:var(--text-muted);">Yêu cầu đặt dịch vụ</div>
                <div style="font-size:24px; font-weight:800;"><?php echo count($allBookings); ?> đơn</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fce7f3; color:#be185d;">
                <i class="fa-solid fa-star"></i>
            </div>
            <div>
                <div style="font-size:13px; color:var(--text-muted);">Đánh giá trung bình</div>
                <div style="font-size:24px; font-weight:800;"><?php echo $avgProviderRating; ?> / 5.0 ⭐</div>
            </div>
        </div>
    </div>

    <!-- BẢNG 1: DANH SÁCH DỊCH VỤ ĐÃ ĐĂNG -->
    <div class="card-box">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
            <div>
                <h2 style="font-size:19px; font-weight:800;">
                    <i class="fa-solid fa-list-check" style="color:var(--grab-green); margin-right:8px;"></i>
                    Danh sách dịch vụ đang cung cấp
                </h2>
                <p style="font-size:13.5px; color:var(--text-muted);">Bạn có thể thêm mới, chỉnh sửa thông tin, bảng giá hoặc tạm ẩn/xóa dịch vụ bất kỳ lúc nào.</p>
            </div>
            <a href="service_form.php" class="btn btn-grab">
                <i class="fa-solid fa-plus"></i> Thêm dịch vụ mới
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Tên dịch vụ và nhà cung cấp</th>
                        <th>Danh mục</th>
                        <th>Khu vực và khoảng cách</th>
                        <th>Giá tham khảo</th>
                        <th>Đánh giá / Lượt xem</th>
                        <th>Trạng thái</th>
                        <th style="text-align:right;">Thao tác quản lý</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($myServices)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:32px; color:var(--text-muted);">
                                Bạn chưa đăng dịch vụ nào. Hãy nhấn nút <strong>"Thêm dịch vụ mới"</strong> hoặc <a href="provider_dashboard.php?view=all" style="color:var(--grab-green); font-weight:700;">xem toàn bộ dịch vụ mẫu</a>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($myServices as $srv): ?>
                            <tr>
                                <td style="width:80px;">
                                    <img src="<?php echo htmlspecialchars($srv['image']); ?>" alt="" style="width:72px; height:54px; object-fit:cover; border-radius:8px;">
                                </td>
                                <td style="max-width:260px;">
                                    <a href="service_detail.php?id=<?php echo (int)$srv['id']; ?>" style="font-weight:700; color:var(--text-dark); display:block; margin-bottom:3px;">
                                        <?php echo htmlspecialchars($srv['title']); ?>
                                    </a>
                                    <span style="font-size:12px; color:var(--text-muted);">
                                        <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($srv['provider_name']); ?> • 📞 <?php echo htmlspecialchars($srv['phone_contact']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="service-cat-tag"><?php echo htmlspecialchars($srv['category_name']); ?></span>
                                </td>
                                <td>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($srv['district']); ?></div>
                                    <div style="font-size:12px; color:var(--text-muted);">Cách tâm ~<?php echo number_format((float)$srv['distance_km'], 1); ?> km</div>
                                </td>
                                <td>
                                    <strong style="color:var(--grab-green-dark);">
                                        <?php echo formatPriceRange($srv['price_min'], $srv['price_max'], $srv['price_unit']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div>⭐ <strong><?php echo number_format((float)$srv['avg_rating'], 1); ?></strong> (<?php echo (int)$srv['review_count']; ?>)</div>
                                    <div style="font-size:12px; color:var(--text-muted);"><?php echo (int)$srv['views']; ?> lượt xem</div>
                                </td>
                                <td>
                                    <?php if ($srv['status'] === 'active'): ?>
                                        <span class="status-badge status-active"><i class="fa-solid fa-circle-check"></i> Đang nhận khách</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive"><i class="fa-solid fa-eye-slash"></i> Đang tạm ẩn</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <div style="display:inline-flex; gap:6px;">
                                        <a href="service_detail.php?id=<?php echo (int)$srv['id']; ?>" class="btn btn-light btn-sm" title="Xem trang chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="service_form.php?id=<?php echo (int)$srv['id']; ?>" class="btn btn-outline-grab btn-sm" title="Chỉnh sửa dịch vụ">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </a>
                                        <form method="POST" action="provider_dashboard.php" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_service_status">
                                            <input type="hidden" name="service_id" value="<?php echo (int)$srv['id']; ?>">
                                            <button type="submit" class="btn btn-light btn-sm" title="Bật/Tắt hiển thị">
                                                <i class="fa-solid fa-power-off"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="provider_dashboard.php" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này?');">
                                            <input type="hidden" name="action" value="delete_service">
                                            <input type="hidden" name="service_id" value="<?php echo (int)$srv['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Xóa dịch vụ">
                                                <i class="fa-solid fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- BẢNG 2: QUẢN LÝ YÊU CẦU / ĐẶT LỊCH TỪ KHÁCH HÀNG -->
    <div class="card-box" id="bookings" style="margin-bottom:0;">
        <div style="margin-bottom:18px;">
            <h2 style="font-size:19px; font-weight:800;">
                <i class="fa-solid fa-bell" style="color:var(--grab-accent); margin-right:8px;"></i>
                Yêu cầu đặt lịch và gọi thợ từ khách hàng
            </h2>
            <p style="font-size:13.5px; color:var(--text-muted);">
                Tiếp nhận yêu cầu của khách, liên hệ qua SĐT và cập nhật tiến độ xử lý đơn hàng.
            </p>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng và SĐT</th>
                        <th>Dịch vụ yêu cầu</th>
                        <th>Địa chỉ và thời gian hẹn</th>
                        <th>Ghi chú của khách</th>
                        <th>Trạng thái</th>
                        <th style="text-align:right;">Cập nhật tiến độ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allBookings as $b): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--grab-green-dark);">#<?php echo htmlspecialchars($b['booking_code']); ?></strong>
                            </td>
                            <td>
                                <div style="font-weight:700;"><?php echo htmlspecialchars($b['customer_name']); ?></div>
                                <a href="tel:<?php echo htmlspecialchars($b['customer_phone']); ?>" style="color:var(--grab-green); font-weight:600; font-size:13px;">
                                    <i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($b['customer_phone']); ?>
                                </a>
                            </td>
                            <td style="max-width:220px; font-weight:600;">
                                <?php echo htmlspecialchars($b['service_title']); ?>
                            </td>
                            <td>
                                <div><i class="fa-solid fa-location-dot" style="color:var(--grab-green);"></i> <?php echo htmlspecialchars($b['customer_address']); ?></div>
                                <div style="font-size:12.5px; color:var(--text-muted);">
                                    🕒 <?php echo date('d/m/Y', strtotime($b['booking_date'])); ?> - <?php echo htmlspecialchars($b['booking_time']); ?>
                                </div>
                            </td>
                            <td style="max-width:220px; font-size:13px; color:var(--text-body);">
                                <?php echo htmlspecialchars($b['note'] ?: 'Không có ghi chú'); ?>
                            </td>
                            <td>
                                <?php if ($b['status'] === 'pending'): ?>
                                    <span class="status-badge status-pending">Chờ xác nhận</span>
                                <?php elseif ($b['status'] === 'confirmed'): ?>
                                    <span class="status-badge status-confirmed">Đã nhận / Đang đi</span>
                                <?php elseif ($b['status'] === 'completed'): ?>
                                    <span class="status-badge status-completed">Hoàn thành</span>
                                <?php else: ?>
                                    <span class="status-badge status-cancelled">Đã hủy</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;">
                                <form method="POST" action="provider_dashboard.php" style="display:inline-flex; gap:6px; align-items:center;">
                                    <input type="hidden" name="action" value="update_booking_status">
                                    <input type="hidden" name="booking_id" value="<?php echo (int)$b['id']; ?>">
                                    <select name="new_status" class="form-control" style="padding:5px 8px; font-size:12.5px; width:auto;">
                                        <option value="pending" <?php echo $b['status'] === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                        <option value="confirmed" <?php echo $b['status'] === 'confirmed' ? 'selected' : ''; ?>>Nhận đơn (Đang đến)</option>
                                        <option value="completed" <?php echo $b['status'] === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                        <option value="cancelled" <?php echo $b['status'] === 'cancelled' ? 'selected' : ''; ?>>Hủy đơn</option>
                                    </select>
                                    <button type="submit" class="btn btn-grab btn-sm">Lưu</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

