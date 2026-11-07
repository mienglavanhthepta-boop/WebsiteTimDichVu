<?php
require_once __DIR__ . '/../config/db.php';
$currentUser = currentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
$selectedDistrict = $_GET['district'] ?? '';
$districts = getDistricts();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - NearService' : 'NearService - Tìm dịch vụ uy tín gần bạn'; ?></title>
    <!-- Google Fonts: Be Vietnam Pro (Chuẩn tiếng Việt) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom Grab-Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=7">
</head>
<body>

<!-- Thanh Topbar: Chọn vị trí quanh bạn & Thông tin nhanh -->
<div class="topbar">
    <div class="container topbar-inner">
        <div class="topbar-left">
            <div class="topbar-location">
                <i class="fa-solid fa-location-dot"></i>
                <span>TP. Nha Trang</span>
            </div>
            <span style="font-size:12.5px; color:#cbd5e1; display:inline-flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-circle-check" style="color:#22c55e; font-size:11px;"></i> Dịch vụ tiện ích quanh bạn
            </span>
        </div>

        <div class="topbar-right">
            <a href="admin_dashboard.php"><i class="fa-solid fa-shield-halved" style="color:#4ade80;"></i> Trang quản trị</a>
            <span style="opacity:0.3;">|</span>
            <span><i class="fa-solid fa-phone-volume" style="color:#4ade80;"></i> Tổng đài: <strong>1900 6868</strong></span>
        </div>
    </div>
</div>

<!-- Thanh Điều Hướng Chính (Navbar) -->
<header class="navbar">
    <div class="container navbar-inner">
        <!-- Logo Thương hiệu -->
        <a href="index.php" class="brand-logo">
            <div class="brand-logo-icon">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <div>
                <div class="brand-text-main">Near<span>Service</span></div>
                <span class="brand-text-sub">Tìm dịch vụ uy tín gần bạn</span>
            </div>
        </a>

        <!-- Menu Điều Hướng Trung Tâm -->
        <nav class="nav-links">
            <a href="index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-house"></i> Trang chủ
            </a>
            <a href="search.php" class="nav-link <?php echo $currentPage === 'search.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-compass"></i> Tìm dịch vụ
            </a>
            <a href="booking_history.php" class="nav-link <?php echo $currentPage === 'booking_history.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-calendar-check"></i> Lịch đã đặt
            </a>
            <a href="provider_dashboard.php" class="nav-link <?php echo in_array($currentPage, ['provider_dashboard.php', 'service_form.php']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-store"></i> Kênh đối tác
            </a>
            <a href="admin_dashboard.php" class="nav-link <?php echo $currentPage === 'admin_dashboard.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge-high"></i> Quản trị
            </a>
        </nav>

        <!-- Cụm Nút Hành Động & Tài Khoản -->
        <div class="nav-actions">
            <a href="service_form.php" class="btn btn-grab btn-sm">
                <i class="fa-solid fa-plus-circle"></i> Đăng dịch vụ
            </a>

            <?php if ($currentUser): ?>
                <a href="profile.php" class="user-pill" title="Cập nhật hồ sơ cá nhân">
                    <span class="user-avatar-sm">
                        <?php echo mb_strtoupper(mb_substr($currentUser['fullname'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                    </span>
                    <span style="max-width:130px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?php echo htmlspecialchars($currentUser['fullname']); ?>
                    </span>
                </a>
                <a href="logout.php" class="btn btn-danger btn-sm" title="Đăng xuất">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-grab btn-sm">
                    <i class="fa-solid fa-user"></i> Đăng nhập
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($flash): ?>
    <div class="container">
        <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> alert-auto-dismiss">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fa-solid <?php echo $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>" style="font-size:18px;"></i>
                <span><?php echo htmlspecialchars($flash['message']); ?></span>
            </div>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:16px;color:inherit;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>
<?php endif; ?>
