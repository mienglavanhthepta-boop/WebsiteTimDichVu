<?php
$pageTitle = 'Tìm kiếm và lọc dịch vụ quanh mình';
require_once __DIR__ . '/includes/header.php';

// Lấy các tham số lọc từ URL (GET)
$q = trim($_GET['q'] ?? '');
$categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : 0;
$district = trim($_GET['district'] ?? '');
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : 0;
$homeService = isset($_GET['home_service']) && $_GET['home_service'] == '1' ? 1 : 0;
$sort = $_GET['sort'] ?? 'nearest';

// Lấy toàn bộ danh mục cho bộ lọc
$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

// Xây dựng câu truy vấn động an toàn bằng Prepared Statement
$whereClauses = ["s.status = 'active'"];
$params = [];

if ($q !== '') {
    $whereClauses[] = "(s.title LIKE :q1 OR s.description LIKE :q2 OR s.price_list LIKE :q3 OR s.address LIKE :q4 OR c.name LIKE :q5)";
    $kw = '%' . $q . '%';
    $params[':q1'] = $kw;
    $params[':q2'] = $kw;
    $params[':q3'] = $kw;
    $params[':q4'] = $kw;
    $params[':q5'] = $kw;
}

if ($categoryId > 0) {
    $whereClauses[] = "s.category_id = :cat_id";
    $params[':cat_id'] = $categoryId;
}

if ($district !== '') {
    $whereClauses[] = "s.district = :district";
    $params[':district'] = $district;
}

if ($maxPrice > 0) {
    $whereClauses[] = "s.price_min <= :max_price";
    $params[':max_price'] = $maxPrice;
}

if ($homeService === 1) {
    $whereClauses[] = "s.is_home_service = 1";
}

$whereSql = implode(' AND ', $whereClauses);

// Sắp xếp kết quả
$orderBySql = "s.distance_km ASC, s.is_featured DESC";
if ($sort === 'popular') {
    $orderBySql = "s.is_featured DESC, s.views DESC";
} elseif ($sort === 'price_asc') {
    $orderBySql = "s.price_min ASC";
} elseif ($sort === 'price_desc') {
    $orderBySql = "s.price_min DESC";
} elseif ($sort === 'rating') {
    $orderBySql = "avg_rating DESC, review_count DESC";
} elseif ($sort === 'newest') {
    $orderBySql = "s.created_at DESC";
}

$sql = "
    SELECT s.*,
           c.name AS category_name, c.icon AS category_icon,
           u.fullname AS provider_name,
           COALESCE(AVG(r.rating), 5.0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM services s
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON s.provider_id = u.id
    LEFT JOIN reviews r ON r.service_id = s.id
    WHERE $whereSql
    GROUP BY s.id
    ORDER BY $orderBySql
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();
?>

<div class="container">
    <!-- Thanh tiêu đề trang Tìm Kiếm -->
    <div style="background:#fff; border-radius:var(--radius-md); padding:20px 24px; margin-top:22px; border:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px;">
        <div>
            <h1 style="font-size:22px; font-weight:800;">
                <i class="fa-solid fa-compass" style="color:var(--grab-green); margin-right:6px;"></i>
                Khám phá và lọc dịch vụ quanh mình
            </h1>
            <p style="font-size:13.5px; color:var(--text-muted); margin-top:2px;">
                Tìm thấy <strong style="color:var(--grab-green-dark);"><?php echo count($services); ?></strong> dịch vụ phù hợp
                <?php if ($district): ?>tại <strong><?php echo htmlspecialchars($district); ?></strong><?php endif; ?>
                <?php if ($q): ?>với từ khóa "<strong><?php echo htmlspecialchars($q); ?></strong>"<?php endif; ?>
            </p>
        </div>

        <!-- Sắp xếp nhanh -->
        <div style="display:flex; align-items:center; gap:10px;">
            <span style="font-size:13.5px; font-weight:600; color:var(--text-muted);">Sắp xếp theo:</span>
            <form method="GET" action="search.php" style="display:inline-block;">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($q); ?>">
                <input type="hidden" name="category_id" value="<?php echo $categoryId ?: ''; ?>">
                <input type="hidden" name="district" value="<?php echo htmlspecialchars($district); ?>">
                <input type="hidden" name="max_price" value="<?php echo $maxPrice ?: ''; ?>">
                <input type="hidden" name="home_service" value="<?php echo $homeService; ?>">
                <select name="sort" class="form-control" onchange="this.form.submit()" style="width:auto; font-weight:600;">
                    <option value="nearest" <?php echo $sort === 'nearest' ? 'selected' : ''; ?>>📍 Gần bạn nhất (km)</option>
                    <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>🔥 Phổ biến và uy tín nhất</option>
                    <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>⭐ Đánh giá cao nhất</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>💰 Giá thấp đến cao</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>💎 Giá cao đến thấp</option>
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>🆕 Mới đăng gần đây</option>
                </select>
            </form>
        </div>
    </div>

    <div class="search-layout">
        <!-- CỘT BỘ LỌC BÊN TRÁI -->
        <aside class="filter-sidebar">
            <form action="search.php" method="GET">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">

                <div class="filter-group">
                    <div class="filter-title">
                        <i class="fa-solid fa-magnifying-glass" style="color:var(--grab-green);"></i> Từ khóa dịch vụ
                    </div>
                    <input type="text" name="q" class="form-control" value="<?php echo htmlspecialchars($q); ?>">
                </div>

                <div class="filter-group">
                    <div class="filter-title">
                        <i class="fa-solid fa-layer-group" style="color:var(--grab-green);"></i> Danh mục dịch vụ
                    </div>
                    <select name="category_id" class="form-control">
                        <option value="">-- Tất cả danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo ($categoryId === (int)$cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <div class="filter-title">
                        <i class="fa-solid fa-location-dot" style="color:var(--grab-green);"></i> Khu vực (Quận/Huyện)
                    </div>
                    <select name="district" class="form-control">
                        <option value="">-- Toàn TP. Nha Trang --</option>
                        <?php foreach ($districts as $dist): ?>
                            <option value="<?php echo htmlspecialchars($dist); ?>" <?php echo ($district === $dist) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dist); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <div class="filter-title">
                        <i class="fa-solid fa-wallet" style="color:var(--grab-green);"></i> Mức giá khởi điểm
                    </div>
                    <select name="max_price" class="form-control">
                        <option value="">-- Mọi mức giá --</option>
                        <option value="80000" <?php echo $maxPrice == 80000 ? 'selected' : ''; ?>>Dưới 80.000đ</option>
                        <option value="150000" <?php echo $maxPrice == 150000 ? 'selected' : ''; ?>>Dưới 150.000đ</option>
                        <option value="300000" <?php echo $maxPrice == 300000 ? 'selected' : ''; ?>>Dưới 300.000đ</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:14px; font-weight:600;">
                        <input type="checkbox" name="home_service" value="1" <?php echo $homeService ? 'checked' : ''; ?> style="width:17px; height:17px; accent-color:var(--grab-green);">
                        <span>🛵 Chỉ hiện dịch vụ hỗ trợ tận nơi</span>
                    </label>
                </div>

                <div style="display:flex; flex-direction:column; gap:8px;">
                    <button type="submit" class="btn btn-grab" style="width:100%;">
                        <i class="fa-solid fa-filter"></i> Áp dụng bộ lọc
                    </button>
                    <a href="search.php" class="btn btn-light" style="width:100%;">
                        <i class="fa-solid fa-rotate-left"></i> Đặt lại bộ lọc
                    </a>
                </div>
            </form>
        </aside>

        <!-- CỘT KẾT QUẢ BÊN PHẢI -->
        <div>
            <!-- Thanh chọn nhanh danh mục -->
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px;">
                <a href="search.php" class="btn btn-sm <?php echo $categoryId === 0 ? 'btn-grab' : 'btn-light'; ?>">
                    Tất cả
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="search.php?category_id=<?php echo (int)$cat['id']; ?>"
                       class="btn btn-sm <?php echo $categoryId === (int)$cat['id'] ? 'btn-grab' : 'btn-light'; ?>">
                        <i class="fa-solid <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($services)): ?>
                <div class="card-box" style="text-align:center; padding:50px 24px;">
                    <div style="font-size:48px; color:var(--grab-green); margin-bottom:12px;">
                        <i class="fa-solid fa-magnifying-glass-location"></i>
                    </div>
                    <h3 style="font-size:20px; font-weight:700; margin-bottom:8px;">Không tìm thấy dịch vụ nào khớp với bộ lọc</h3>
                    <p style="color:var(--text-muted); margin-bottom:20px;">Bạn hãy thử mở rộng khu vực tìm kiếm hoặc xóa bớt điều kiện lọc nhé.</p>
                    <a href="search.php" class="btn btn-grab">Xem tất cả dịch vụ quanh bạn</a>
                </div>
            <?php else: ?>
                <div class="services-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <?php foreach ($services as $srv): ?>
                        <?php include __DIR__ . '/includes/service_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

