<?php
$pageTitle = 'Trang chủ';
require_once __DIR__ . '/includes/header.php';

// 1. Lấy danh sách danh mục kèm số lượng dịch vụ đang hoạt động
$stmtCat = $pdo->query("
    SELECT c.*, COUNT(s.id) AS total_services
    FROM categories c
    LEFT JOIN services s ON s.category_id = c.id AND s.status = 'active'
    GROUP BY c.id
    ORDER BY c.id ASC
");
$categories = $stmtCat->fetchAll();

// 2. Lấy danh sách Dịch vụ gần bạn nhất (theo khoảng cách distance_km tăng dần)
$stmtNear = $pdo->query("
    SELECT s.*, 
           c.name AS category_name, c.icon AS category_icon,
           u.fullname AS provider_name,
           COALESCE(AVG(r.rating), 5.0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM services s
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON s.provider_id = u.id
    LEFT JOIN reviews r ON r.service_id = s.id
    WHERE s.status = 'active'
    GROUP BY s.id
    ORDER BY s.distance_km ASC
    LIMIT 6
");
$nearbyServices = $stmtNear->fetchAll();

// 3. Lấy danh sách Dịch vụ Nổi bật & xem nhiều
$stmtFeatured = $pdo->query("
    SELECT s.*, 
           c.name AS category_name, c.icon AS category_icon,
           u.fullname AS provider_name,
           COALESCE(AVG(r.rating), 5.0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM services s
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON s.provider_id = u.id
    LEFT JOIN reviews r ON r.service_id = s.id
    WHERE s.status = 'active'
    GROUP BY s.id
    ORDER BY s.is_featured DESC, s.views DESC
    LIMIT 6
");
$featuredServices = $stmtFeatured->fetchAll();

// 4. Lấy các đánh giá mới nhất từ người dùng
$stmtReviews = $pdo->query("
    SELECT r.*, s.title AS service_title, s.id AS service_id
    FROM reviews r
    JOIN services s ON r.service_id = s.id
    ORDER BY r.created_at DESC
    LIMIT 3
");
$latestReviews = $stmtReviews->fetchAll();
?>

<!-- HERO BANNER GRAB STYLE -->
<section class="hero-grab" style="padding: 38px 0 64px;">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title" style="margin-bottom: 22px;">Cần tìm dịch vụ quanh mình? Gọi thợ có ngay!</h1>

            <!-- Thanh tìm kiếm thông minh: 3 phần cân đối hoàn hảo -->
            <form action="search.php" method="GET" class="search-box-card">
                <!-- 1. Vị trí / Khu vực (Bên trái) -->
                <div class="search-input-group">
                    <i class="fa-solid fa-location-dot"></i>
                    <select name="district">
                        <option value="">Toàn TP. Nha Trang</option>
                        <?php foreach ($districts as $dist): ?>
                            <option value="<?php echo htmlspecialchars($dist); ?>">
                                <?php echo htmlspecialchars($dist); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 2. Ô tìm kiếm dịch vụ (CHÍNH GIỮA TRUNG TÂM, RỘNG RÃI) -->
                <div class="search-input-group">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q">
                </div>

                <!-- 3. Nút tìm kiếm (Bên phải) -->
                <button type="submit" class="btn btn-grab btn-lg" style="height:50px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
                </button>
            </form>

            <!-- Gợi ý nhanh & Định vị GPS (nằm gọn trên 1 hàng) -->
            <div class="hero-quick-tags">
                <button type="button" id="btnDetectLocation" onclick="detectMyLocation()" class="hero-quick-tag" style="cursor:pointer; background:rgba(0,0,0,0.28); border-color:rgba(255,255,255,0.5);">
                    <i class="fa-solid fa-location-crosshairs"></i> Định vị gần tôi nhất
                </button>
                <a href="search.php?category_id=1" class="hero-quick-tag">🛵 Sửa xe</a>
                <a href="search.php?category_id=2" class="hero-quick-tag">📱 Sửa điện thoại</a>
                <a href="search.php?category_id=3" class="hero-quick-tag">✂️ Cắt tóc</a>
                <a href="search.php?category_id=4" class="hero-quick-tag">🎓 Gia sư</a>
                <a href="search.php?category_id=5" class="hero-quick-tag">💻 Sửa máy tính</a>
                <a href="search.php?category_id=6" class="hero-quick-tag">🧹 Dọn nhà</a>
            </div>
        </div>
    </div>
</section>

<!-- DANH MỤC DỊCH VỤ (LƯỚI 4 CỘT × 2 HÀNG RÕ RÀNG, ĐẸP MẮT) -->
<section class="category-section">
    <div class="container">
        <div class="category-card-wrapper">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:18px;">
                <h2 style="font-size:19px; font-weight:800; color:var(--text-main); display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-grip" style="color:var(--grab-green);"></i> Danh mục dịch vụ quanh bạn
                </h2>
                <a href="search.php" style="font-size:14px; font-weight:700; color:var(--grab-green-dark);">
                    Xem tất cả danh mục <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="category-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="search.php?category_id=<?php echo (int)$cat['id']; ?>" class="category-item">
                        <div class="category-icon-circle" style="background: <?php echo htmlspecialchars($cat['color']); ?>;">
                            <i class="fa-solid <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                        </div>
                        <div class="category-info">
                            <div class="category-name"><?php echo htmlspecialchars($cat['name']); ?></div>
                            <div class="category-count">
                                <i class="fa-solid fa-circle-check" style="color:var(--grab-green); font-size:11px;"></i>
                                <?php echo (int)$cat['total_services']; ?> đơn vị sẵn sàng
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- DỊCH VỤ QUANH MÌNH (SẮP XẾP THEO KHOẢNG CÁCH KM) -->
<section class="section-block">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <span style="width:38px;height:38px;border-radius:10px;background:var(--grab-green-light);color:var(--grab-green-dark);display:inline-flex;align-items:center;justify-content:center;font-size:18px;">
                        <i class="fa-solid fa-location-crosshairs"></i>
                    </span>
                    Dịch vụ ở gần bạn nhất
                </h2>
                <p class="section-subtitle">Ưu tiên hiển thị các thợ và cửa hàng trong bán kính 0.8 km – 3.0 km có mặt nhanh từ 10 - 20 phút</p>
            </div>
            <a href="search.php?sort=nearest" class="btn btn-outline-grab">
                Xem tất cả quanh đây <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="services-grid">
            <?php foreach ($nearbyServices as $srv): ?>
                <?php include __DIR__ . '/includes/service_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- QUY TRÌNH 4 BƯỚC SỬ DỤNG DỄ DÀNG -->
<section class="section-block" style="background:#fff; border-top:1px solid var(--border-color); border-bottom:1px solid var(--border-color); margin:10px 0;">
    <div class="container">
        <div style="text-align:center; max-width:680px; margin:0 auto 28px;">
            <span style="color:var(--grab-green-dark); background:var(--grab-green-light); padding:4px 14px; border-radius:99px; font-weight:700; font-size:12.5px;">
                Tiện lợi và minh bạch
            </span>
            <h2 style="font-size:25px; font-weight:800; margin-top:10px;">4 bước tìm và đặt dịch vụ quanh mình</h2>
        </div>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon" style="background:var(--grab-green-light); color:var(--grab-green-dark);">
                    <i class="fa-solid fa-magnifying-glass-location"></i>
                </div>
                <h3 style="font-size:16.5px; font-weight:700; margin-bottom:6px;">1. Tìm kiếm theo khu vực</h3>
                <p style="font-size:13.5px; color:var(--text-secondary);">Chọn danh mục dịch vụ và khu vực tại TP. Nha Trang để lọc ngay những đơn vị gần nhất.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon" style="background:#dbeafe; color:#1d4ed8;">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h3 style="font-size:16.5px; font-weight:700; margin-bottom:6px;">2. Xem giá và đánh giá</h3>
                <p style="font-size:13.5px; color:var(--text-secondary);">Tham khảo bảng giá niêm yết công khai và nhận xét thực tế từ khách hàng trước.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon" style="background:#fef3c7; color:#d97706;">
                    <i class="fa-solid fa-phone-volume"></i>
                </div>
                <h3 style="font-size:16.5px; font-weight:700; margin-bottom:6px;">3. Liên hệ hoặc đặt lịch</h3>
                <p style="font-size:13.5px; color:var(--text-secondary);">Gọi trực tiếp, nhắn Zalo hoặc gửi phiếu đặt lịch hẹn thợ đến tận nhà hoàn toàn miễn phí.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon" style="background:#fce7f3; color:#be185d;">
                    <i class="fa-solid fa-star"></i>
                </div>
                <h3 style="font-size:16.5px; font-weight:700; margin-bottom:6px;">4. Nghiệm thu và chấm sao</h3>
                <p style="font-size:13.5px; color:var(--text-secondary);">Thanh toán trực tiếp sau khi hài lòng và để lại đánh giá giúp cộng đồng.</p>
            </div>
        </div>
    </div>
</section>

<!-- DỊCH VỤ NỔI BẬT & ĐỐI TÁC UY TÍN -->
<section class="section-block">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <span style="width:38px;height:38px;border-radius:10px;background:#fef3c7;color:#d97706;display:inline-flex;align-items:center;justify-content:center;font-size:18px;">
                        <i class="fa-solid fa-crown"></i>
                    </span>
                    Đối tác dịch vụ nổi bật và uy tín
                </h2>
                <p class="section-subtitle">Những đơn vị có lượt khách đặt và điểm đánh giá cao nhất trong tuần</p>
            </div>
            <a href="search.php?sort=popular" class="btn btn-outline-grab">
                Xem thêm đối tác <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="services-grid">
            <?php foreach ($featuredServices as $srv): ?>
                <?php include __DIR__ . '/includes/service_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ĐÁNH GIÁ MỚI NHẤT & BANNER ĐỐI TÁC -->
<section class="section-block" style="padding-bottom:48px;">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <span style="width:38px;height:38px;border-radius:10px;background:var(--grab-green-light);color:var(--grab-green-dark);display:inline-flex;align-items:center;justify-content:center;font-size:18px;">
                        <i class="fa-solid fa-comments"></i>
                    </span>
                    Đánh giá thực tế từ khách hàng
                </h2>
                <p class="section-subtitle">Phản hồi từ những người dùng vừa trải nghiệm dịch vụ quanh khu vực</p>
            </div>
        </div>

        <div class="services-grid" style="margin-bottom:36px;">
            <?php foreach ($latestReviews as $rev): ?>
                <div class="card-box" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                            <div style="font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                                <span class="user-avatar-sm"><?php echo mb_strtoupper(mb_substr($rev['reviewer_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?></span>
                                <?php echo htmlspecialchars($rev['reviewer_name']); ?>
                            </div>
                            <?php echo renderStars($rev['rating']); ?>
                        </div>
                        <p style="font-size:14px; color:var(--text-secondary); font-style:italic; margin-bottom:16px; line-height:1.6;">
                            "<?php echo htmlspecialchars($rev['comment']); ?>"
                        </p>
                    </div>
                    <div style="border-top:1px dashed var(--border-color); padding-top:12px; font-size:13px; color:var(--text-muted);">
                        Dịch vụ: <a href="service_detail.php?id=<?php echo (int)$rev['service_id']; ?>" style="color:var(--grab-green-dark); font-weight:700;">
                            <?php echo htmlspecialchars($rev['service_title']); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Banner Đăng ký Nhà Cung Cấp -->
        <div style="background:linear-gradient(135deg, #007a35 0%, #00B14F 100%); border-radius:var(--radius-lg); padding:34px 38px; color:#fff; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:24px; box-shadow:var(--shadow-md);">
            <div style="max-width:660px;">
                <span style="background:rgba(255,255,255,0.2); padding:5px 14px; border-radius:99px; font-size:12px; font-weight:700;">
                    Dành cho cá nhân và cửa hàng cung cấp dịch vụ
                </span>
                <h2 style="font-size:25px; font-weight:800; margin:12px 0 8px;">Mở rộng khách hàng quanh khu vực cùng NearService</h2>
                <p style="opacity:0.95; font-size:14.5px;">
                    Bạn là thợ sửa xe, kỹ thuật viên điện thoại, máy tính, thợ cắt tóc, gia sư hay đơn vị vệ sinh? Đăng dịch vụ của bạn lên hệ thống ngay hôm nay để khách hàng quanh khu vực dễ dàng tìm thấy!
                </p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="service_form.php" class="btn btn-lg" style="background:#fff; color:var(--grab-green-dark);">
                    <i class="fa-solid fa-plus-circle"></i> Đăng dịch vụ ngay
                </a>
                <a href="provider_dashboard.php" class="btn btn-lg" style="background:rgba(0,0,0,0.22); color:#fff; border:1px solid rgba(255,255,255,0.4);">
                    <i class="fa-solid fa-store"></i> Quản lý dịch vụ
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
