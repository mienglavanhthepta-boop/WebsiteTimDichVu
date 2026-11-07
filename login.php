<?php
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Hỗ trợ kiểm tra cả mật khẩu plain-text mẫu ('123456') lẫn password_verify
    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role'],
            'address' => $user['address'],
            'district' => $user['district']
        ];

        setFlash('success', 'Chào mừng ' . $user['fullname'] . ' đã đăng nhập vào NearService!');
        if ($user['role'] === 'provider') {
            header('Location: provider_dashboard.php');
        } elseif ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        setFlash('error', 'Email hoặc mật khẩu không chính xác! Vui lòng thử lại.');
    }
}

$pageTitle = 'Đăng nhập tài khoản';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding:40px 20px 60px; max-width:520px;">
    <div class="card-box" style="padding:32px;">
        <div style="text-align:center; margin-bottom:24px;">
            <div style="width:56px; height:56px; border-radius:16px; background:var(--grab-green); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:26px; margin-bottom:12px;">
                <i class="fa-solid fa-user-lock"></i>
            </div>
            <h1 style="font-size:24px; font-weight:800;">Đăng nhập NearService</h1>
            <p style="font-size:14px; color:var(--text-muted); margin-top:4px;">
                Đăng nhập để đặt lịch dịch vụ hoặc quản lý dịch vụ của bạn
            </p>
        </div>

        <form id="loginForm" action="login.php" method="POST">
            <div style="margin-bottom:16px;">
                <label class="form-label">Địa chỉ email</label>
                <input type="email" id="loginEmail" name="email" class="form-control" required>
            </div>

            <div style="margin-bottom:20px;">
                <label class="form-label">Mật khẩu</label>
                <input type="password" id="loginPassword" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-grab btn-lg" style="width:100%;">
                <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập ngay
            </button>
        </form>

        <!-- Hộp đăng nhập 1-click -->
        <div style="margin-top:24px; padding-top:20px; border-top:1px dashed var(--border-color);">
            <div style="font-size:13px; font-weight:700; color:var(--text-muted); margin-bottom:10px; text-align:center;">
                Đăng nhập
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <button type="button" onclick="fillDemoAccount('doitac@gmail.com', '123456')" class="btn btn-outline-grab btn-sm" style="width:100%; justify-content:space-between;">
                    <span><i class="fa-solid fa-store"></i> Nhà cung cấp dịch vụ</span>
                    <code>doitac@gmail.com</code>
                </button>
                <button type="button" onclick="fillDemoAccount('khachhang@gmail.com', '123456')" class="btn btn-light btn-sm" style="width:100%; justify-content:space-between;">
                    <span><i class="fa-solid fa-user"></i> Khách hàng tìm dịch vụ</span>
                    <code>khachhang@gmail.com</code>
                </button>
                <button type="button" onclick="fillDemoAccount('admin@gmail.com', '123456')" class="btn btn-light btn-sm" style="width:100%; justify-content:space-between;">
                    <span><i class="fa-solid fa-shield-halved"></i> Quản trị viên hệ thống</span>
                    <code>admin@gmail.com</code>
                </button>
            </div>
        </div>

        <div style="text-align:center; margin-top:20px; font-size:14px;">
            Chưa có tài khoản? <a href="register.php" style="color:var(--grab-green); font-weight:700;">Đăng ký miễn phí ngay</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

