<?php
require_once __DIR__ . '/config/db.php';

// Xử lý thêm danh mục mới hoặc xóa đánh giá/dịch vụ trong trang Quản trị
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    if ($_POST['admin_action'] === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-screwdriver-wrench');
        $color = trim($_POST['color'] ?? '#00B14F');
        $desc = trim($_POST['description'] ?? '');
        if ($name !== '') {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, color, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $icon, $color, $desc]);
            setFlash('success', 'Đã thêm danh mục dịch vụ mới thành công!');
        }
        header('Location: admin_dashboard.php');
        exit;
    }

    if ($_POST['admin_action'] === 'delete_review') {
        $revId = (int)$_POST['review_id'];
        $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$revId]);
        setFlash('warning', 'Đã xóa đánh giá thành công.');
        header('Location: admin_dashboard.php#reviews-section');
        exit;
    }
}

$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalServices = (int)$pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$totalBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$totalReviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

$categories = $pdo->query("
    SELECT c.*, COUNT(s.id) AS service_count
    FROM categories c
    LEFT JOIN services s ON s.category_id = c.id
    GROUP BY c.id
    ORDER BY c.id ASC
")->fetchAll();

$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
$reviews = $pdo->query("
    SELECT r.*, s.title AS service_title
    FROM reviews r
    JOIN services s ON r.service_id = s.id
    ORDER BY r.created_at DESC
")->fetchAll();

$pageTitle = 'Quản trị hệ thống';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding:28px 20px 56px;">
    <!-- Banner quản trị hệ thống -->
    <div style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color:#fff; border-radius:var(--radius-md); padding:26px 30px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; box-shadow:var(--shadow-sm);">
        <div>
            <span class="badge-grab-partner" style="margin-bottom:8px;">
                <i class="fa-solid fa-shield-halved"></i> Quản trị viên hệ thống
            </span>
            <h1 style="font-size:24px; font-weight:800;">Bảng điều khiển quản trị hệ thống</h1>
            <p style="font-size:14px; color:#cbd5e1; margin-top:4px;">
                Quản lý danh mục dịch vụ, tài khoản người dùng, đối tác cung cấp và đánh giá trên toàn nền tảng
            </p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="service_form.php" class="btn btn-outline-grab" style="background:rgba(255,255,255,0.1); color:#fff; border-color:rgba(255,255,255,0.3);">
                <i class="fa-solid fa-plus"></i> Thêm dịch vụ mới
            </a>
            <a href="provider_dashboard.php?view=all" class="btn btn-grab">
                <i class="fa-solid fa-briefcase"></i> Quản lý toàn bộ dịch vụ và đơn hàng
            </a>
        </div>
    </div>

    <!-- 4 Thẻ Thống Kê Tổng Quan -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--grab-green-light); color:var(--grab-green-dark);">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div style="font-size:13px; color:var(--text-muted); font-weight:500;">Tổng tài khoản</div>
                <div style="font-size:24px; font-weight:800;"><?php echo $totalUsers; ?> người dùng</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe; color:#1d4ed8;">
                <i class="fa-solid fa-store"></i>
            </div>
            <div>
                <div style="font-size:13px; color:var(--text-muted); font-weight:500;">Tổng dịch vụ hoạt động</div>
                <div style="font-size:24px; font-weight:800;"><?php echo $totalServices; ?> dịch vụ</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7; color:#d97706;">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <div style="font-size:13px; color:var(--text-muted); font-weight:500;">Tổng đơn đặt lịch</div>
                <div style="font-size:24px; font-weight:800;"><?php echo $totalBookings; ?> yêu cầu</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fce7f3; color:#be185d;">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div>
                <div style="font-size:13px; color:var(--text-muted); font-weight:500;">Đánh giá khách hàng</div>
                <div style="font-size:24px; font-weight:800;"><?php echo $totalReviews; ?> nhận xét</div>
            </div>
        </div>
    </div>

    <!-- PHẦN 1: QUẢN LÝ DANH MỤC DỊCH VỤ -->
    <div class="card-box">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:18px;">
            <div>
                <h2 style="font-size:19px; font-weight:800; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-layer-group" style="color:var(--grab-green);"></i>
                    Quản lý danh mục dịch vụ (<?php echo count($categories); ?>)
                </h2>
                <p style="font-size:13.5px; color:var(--text-muted);">Các nhóm ngành dịch vụ quanh mình đang hiển thị trên trang chủ</p>
            </div>

            <!-- Form thêm danh mục mới gọn đẹp -->
            <form method="POST" action="admin_dashboard.php" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                <input type="hidden" name="admin_action" value="add_category">
                <input type="text" name="name" class="form-control" style="width:210px;" required>
                <input type="text" name="icon" class="form-control" style="width:160px;">
                <input type="text" name="description" class="form-control" style="width:240px;">
                <button type="submit" class="btn btn-grab">
                    <i class="fa-solid fa-plus"></i> Thêm danh mục
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:70px;">Mã</th>
                        <th>Biểu tượng và tên danh mục</th>
                        <th>Mô tả dịch vụ</th>
                        <th style="width:150px;">Số lượng dịch vụ</th>
                        <th style="width:130px; text-align:right;">Xem dịch vụ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><strong>#<?php echo (int)$c['id']; ?></strong></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="display:inline-flex; width:36px; height:36px; border-radius:10px; background:<?php echo htmlspecialchars($c['color']); ?>; color:#fff; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                                        <i class="fa-solid <?php echo htmlspecialchars($c['icon']); ?>"></i>
                                    </span>
                                    <strong style="font-size:15px;"><?php echo htmlspecialchars($c['name']); ?></strong>
                                </div>
                            </td>
                            <td style="color:var(--text-secondary);">
                                <?php echo htmlspecialchars($c['description'] ?: 'Dịch vụ tiện ích quanh khu vực'); ?>
                            </td>
                            <td>
                                <span class="status-badge status-active"><?php echo (int)$c['service_count']; ?> dịch vụ</span>
                            </td>
                            <td style="text-align:right;">
                                <a href="search.php?category_id=<?php echo (int)$c['id']; ?>" class="btn btn-light btn-sm">
                                    Xem <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PHẦN 2: DANH SÁCH NGƯỜI DÙNG & NHÀ CUNG CẤP -->
    <div class="card-box">
        <div style="margin-bottom:16px;">
            <h2 style="font-size:19px; font-weight:800; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-users-gear" style="color:var(--grab-green);"></i>
                Danh sách tài khoản hệ thống
            </h2>
            <p style="font-size:13.5px; color:var(--text-muted);">Thông tin người dùng tìm dịch vụ và các đối tác cung cấp dịch vụ trên nền tảng</p>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>Họ và tên</th>
                        <th>Email đăng nhập</th>
                        <th>Số điện thoại</th>
                        <th>Khu vực và địa chỉ</th>
                        <th>Vai trò</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong>#<?php echo (int)$u['id']; ?></strong></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span class="user-avatar-sm"><?php echo mb_strtoupper(mb_substr($u['fullname'], 0, 1, 'UTF-8'), 'UTF-8'); ?></span>
                                    <strong><?php echo htmlspecialchars($u['fullname']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><strong><?php echo htmlspecialchars($u['phone']); ?></strong></td>
                            <td style="color:var(--text-secondary);">
                                <strong><?php echo htmlspecialchars($u['district']); ?></strong>
                                <?php if (!empty($u['address'])): ?> - <?php echo htmlspecialchars($u['address']); ?><?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="status-badge status-cancelled"><i class="fa-solid fa-shield-halved"></i> Quản trị viên</span>
                                <?php elseif ($u['role'] === 'provider'): ?>
                                    <span class="status-badge status-completed"><i class="fa-solid fa-store"></i> Nhà cung cấp</span>
                                <?php else: ?>
                                    <span class="status-badge status-confirmed"><i class="fa-solid fa-user"></i> Khách hàng</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PHẦN 3: QUẢN LÝ ĐÁNH GIÁ CỦA NGƯỜI DÙNG -->
    <div class="card-box" id="reviews-section" style="margin-bottom:0;">
        <div style="margin-bottom:16px;">
            <h2 style="font-size:19px; font-weight:800; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-star" style="color:var(--grab-yellow);"></i>
                Quản lý đánh giá và nhận xét của khách hàng
            </h2>
            <p style="font-size:13.5px; color:var(--text-muted);">Kiểm duyệt các đánh giá thực tế về chất lượng dịch vụ</p>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Dịch vụ được đánh giá</th>
                        <th style="width:130px;">Mức sao</th>
                        <th>Nội dung nhận xét</th>
                        <th style="width:140px;">Thời gian</th>
                        <th style="width:90px; text-align:right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($r['reviewer_name']); ?></strong></td>
                            <td>
                                <a href="service_detail.php?id=<?php echo (int)$r['service_id']; ?>" style="color:var(--grab-green-dark); font-weight:600;">
                                    <?php echo htmlspecialchars($r['service_title']); ?>
                                </a>
                            </td>
                            <td><?php echo renderStars($r['rating']); ?></td>
                            <td style="color:var(--text-secondary);"><?php echo htmlspecialchars($r['comment']); ?></td>
                            <td style="font-size:12.5px; color:var(--text-muted);">
                                <?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?>
                            </td>
                            <td style="text-align:right;">
                                <form method="POST" action="admin_dashboard.php" onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?');">
                                    <input type="hidden" name="admin_action" value="delete_review">
                                    <input type="hidden" name="review_id" value="<?php echo (int)$r['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Xóa đánh giá">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
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
