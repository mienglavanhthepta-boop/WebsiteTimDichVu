<?php
require_once __DIR__ . '/config/db.php';

// Xử lý hủy lịch đặt nếu đang ở trạng thái pending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $cancelId = (int)$_POST['cancel_booking_id'];
    $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND status = 'pending'")->execute([$cancelId]);
    setFlash('warning', 'Đã hủy yêu cầu đặt dịch vụ thành công.');
    header('Location: booking_history.php');
    exit;
}

$pageTitle = 'Lịch đặt dịch vụ của tôi';
require_once __DIR__ . '/includes/header.php';

$searchPhone = trim($_GET['phone'] ?? '');
$searchCode = trim($_GET['code'] ?? '');

$where = [];
$params = [];

if ($searchCode !== '') {
    $where[] = "b.booking_code = :code";
    $params[':code'] = $searchCode;
} elseif ($searchPhone !== '') {
    $where[] = "b.customer_phone LIKE :phone";
    $params[':phone'] = '%' . $searchPhone . '%';
} elseif (isLoggedIn()) {
    $where[] = "(b.customer_id = :uid OR b.customer_phone = :uphone)";
    $params[':uid'] = (int)$_SESSION['user']['id'];
    $params[':uphone'] = $_SESSION['user']['phone'];
} elseif (!empty($_SESSION['guest_bookings'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['guest_bookings']), '?'));
    $where[] = "b.booking_code IN ($placeholders)";
    $params = array_values($_SESSION['guest_bookings']);
} else {
    // Mặc định hiển thị các lịch đặt mẫu để thuận tiện demo
    $where[] = "1 = 1";
}

$whereSql = implode(' AND ', $where);
$sql = "
    SELECT b.*,
           s.title AS service_title, s.phone_contact AS service_phone, s.district AS service_district, s.image AS service_image,
           c.name AS category_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE $whereSql
    ORDER BY b.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

function getBookingStatusBadge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="status-badge status-pending"><i class="fa-solid fa-hourglass-half"></i> Chờ nhà cung cấp xác nhận</span>';
        case 'confirmed':
            return '<span class="status-badge status-confirmed"><i class="fa-solid fa-motorcycle"></i> Đã nhận đơn / Đang đến</span>';
        case 'completed':
            return '<span class="status-badge status-completed"><i class="fa-solid fa-circle-check"></i> Đã hoàn thành</span>';
        case 'cancelled':
            return '<span class="status-badge status-cancelled"><i class="fa-solid fa-ban"></i> Đã hủy</span>';
        default:
            return '<span class="status-badge">' . htmlspecialchars($status) . '</span>';
    }
}
?>

<div class="container" style="padding:28px 20px 50px;">
    <div class="card-box" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
        <div>
            <h1 style="font-size:23px; font-weight:800;">
                <i class="fa-solid fa-calendar-check" style="color:var(--grab-green); margin-right:8px;"></i>
                Theo dõi lịch đặt và yêu cầu dịch vụ
            </h1>
            <p style="font-size:14px; color:var(--text-muted); margin-top:4px;">
                Tra cứu trạng thái các yêu cầu gọi thợ hoặc đặt lịch dịch vụ quanh mình
            </p>
        </div>

        <!-- Form tra cứu nhanh theo SĐT hoặc Mã đơn -->
        <form action="booking_history.php" method="GET" style="display:flex; gap:8px; flex-wrap:wrap;">
            <input type="text" name="phone" class="form-control" style="width:220px;" value="<?php echo htmlspecialchars($searchPhone); ?>">
            <button type="submit" class="btn btn-grab">
                <i class="fa-solid fa-magnifying-glass"></i> Tra cứu
            </button>
            <?php if ($searchPhone || $searchCode): ?>
                <a href="booking_history.php" class="btn btn-light">Xem tất cả</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="card-box" style="text-align:center; padding:50px 20px;">
            <i class="fa-regular fa-calendar-xmark" style="font-size:48px; color:var(--text-muted); margin-bottom:14px;"></i>
            <h3 style="font-size:19px; font-weight:700; margin-bottom:8px;">Chưa có lịch đặt dịch vụ nào</h3>
            <p style="color:var(--text-muted); margin-bottom:20px;">Bạn có thể tìm kiếm các dịch vụ quanh mình và đặt lịch hẹn ngay hôm nay!</p>
            <a href="search.php" class="btn btn-grab">Khám phá dịch vụ ngay</a>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:18px;">
            <?php foreach ($bookings as $b): ?>
                <div class="card-box" style="margin-bottom:0; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:20px;">
                    <div style="display:flex; gap:18px; align-items:flex-start; flex:1; min-width:300px;">
                        <img src="<?php echo htmlspecialchars($b['service_image']); ?>" alt="" style="width:110px; height:85px; object-fit:cover; border-radius:10px; flex-shrink:0;">
                        <div>
                            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:6px;">
                                <span style="font-weight:800; color:var(--grab-green-dark); background:var(--grab-green-light); padding:3px 10px; border-radius:6px; font-size:12.5px;">
                                    #<?php echo htmlspecialchars($b['booking_code']); ?>
                                </span>
                                <?php echo getBookingStatusBadge($b['status']); ?>
                                <span style="font-size:12.5px; color:var(--text-muted);">
                                    <i class="fa-regular fa-clock"></i> Ngày hẹn: <strong><?php echo date('d/m/Y', strtotime($b['booking_date'])); ?> (<?php echo htmlspecialchars($b['booking_time']); ?>)</strong>
                                </span>
                            </div>

                            <h3 style="font-size:17px; font-weight:800; margin-bottom:6px;">
                                <a href="service_detail.php?id=<?php echo (int)$b['service_id']; ?>" style="color:var(--text-dark);">
                                    <?php echo htmlspecialchars($b['service_title']); ?>
                                </a>
                            </h3>

                            <div style="font-size:13.5px; color:var(--text-body); display:flex; flex-direction:column; gap:3px;">
                                <div><i class="fa-solid fa-user" style="color:var(--grab-green); width:16px;"></i> Khách hàng: <strong><?php echo htmlspecialchars($b['customer_name']); ?></strong> - SĐT: <strong><?php echo htmlspecialchars($b['customer_phone']); ?></strong></div>
                                <div><i class="fa-solid fa-location-dot" style="color:var(--grab-green); width:16px;"></i> Địa chỉ phục vụ: <?php echo htmlspecialchars($b['customer_address']); ?></div>
                                <?php if (!empty($b['note'])): ?>
                                    <div><i class="fa-solid fa-note-sticky" style="color:var(--grab-green); width:16px;"></i> Ghi chú: <em><?php echo htmlspecialchars($b['note']); ?></em></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div style="text-align:right; min-width:190px;">
                        <div style="font-size:12px; color:var(--text-muted);">Chi phí tham khảo</div>
                        <div style="font-size:18px; font-weight:800; color:var(--grab-green-dark); margin-bottom:10px;">
                            <?php echo formatPrice($b['estimated_price']); ?>
                        </div>

                        <div style="display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap;">
                            <a href="tel:<?php echo htmlspecialchars($b['service_phone']); ?>" class="btn btn-outline-grab btn-sm">
                                <i class="fa-solid fa-phone"></i> Gọi thợ
                            </a>
                            <a href="service_detail.php?id=<?php echo (int)$b['service_id']; ?>#reviews" class="btn btn-grab btn-sm">
                                <i class="fa-solid fa-star"></i> Đánh giá
                            </a>
                            <?php if ($b['status'] === 'pending'): ?>
                                <form method="POST" action="booking_history.php" onsubmit="return confirm('Bạn chắc chắn muốn hủy yêu cầu này?');">
                                    <input type="hidden" name="cancel_booking_id" value="<?php echo (int)$b['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Hủy</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

