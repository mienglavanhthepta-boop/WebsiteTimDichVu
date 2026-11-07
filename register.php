<?php
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$defaultRole = ($_GET['role'] ?? '') === 'provider' ? 'provider' : 'customer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['customer', 'provider']) ? $_POST['role'] : 'customer';
    $district = trim($_POST['district'] ?? 'Phường Lộc Thọ');
    $address = trim($_POST['address'] ?? '');

    if ($fullname === '' || $email === '' || $phone === '' || $password === '') {
        setFlash('error', 'Vui lòng điền đầy đủ thông tin bắt buộc!');
    } else {
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            setFlash('error', 'Email này đã được sử dụng! Vui lòng chọn email khác.');
        } else {
            $stmtIns = $pdo->prepare("
                INSERT INTO users (fullname, email, phone, password, role, address, district, is_verified)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmtIns->execute([$fullname, $email, $phone, $password, $role, $address, $district]);
            $newUserId = (int)$pdo->lastInsertId();

            $_SESSION['user'] = [
                'id' => $newUserId,
                'fullname' => $fullname,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'address' => $address,
                'district' => $district
            ];

            setFlash('success', 'Đăng ký tài khoản thành công! Chào mừng bạn đến với NearService.');
            if ($role === 'provider') {
                header('Location: provider_dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        }
    }
}

$pageTitle = 'Đăng ký tài khoản';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding:36px 20px 60px; max-width:600px;">
    <div class="card-box" style="padding:32px;">
        <div style="text-align:center; margin-bottom:24px;">
            <div style="width:56px; height:56px; border-radius:16px; background:var(--grab-green); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:26px; margin-bottom:12px;">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 style="font-size:24px; font-weight:800;">Đăng ký tài khoản NearService</h1>
            <p style="font-size:14px; color:var(--text-muted); margin-top:4px;">
                Tham gia nền tảng tìm kiếm và cung cấp dịch vụ quanh mình
            </p>
        </div>

        <form action="register.php" method="POST">
            <div style="margin-bottom:16px;">
                <label class="form-label">Bạn muốn đăng ký với vai trò nào?</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <label style="border:1.5px solid var(--grab-green); background:var(--grab-green-soft); padding:12px; border-radius:10px; cursor:pointer; display:flex; align-items:center; gap:10px; font-weight:700;">
                        <input type="radio" name="role" value="customer" <?php echo $defaultRole === 'customer' ? 'checked' : ''; ?> style="accent-color:var(--grab-green);">
                        <span>👤 Người tìm dịch vụ</span>
                    </label>
                    <label style="border:1.5px solid var(--grab-green); background:var(--grab-green-soft); padding:12px; border-radius:10px; cursor:pointer; display:flex; align-items:center; gap:10px; font-weight:700;">
                        <input type="radio" name="role" value="provider" <?php echo $defaultRole === 'provider' ? 'checked' : ''; ?> style="accent-color:var(--grab-green);">
                        <span>🏪 Nhà cung cấp dịch vụ</span>
                    </label>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label class="form-label">Họ và tên</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label class="form-label">Địa chỉ email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1.6fr; gap:14px; margin-bottom:22px;">
                <div>
                    <label class="form-label">Khu vực</label>
                    <select name="district" class="form-control">
                        <?php foreach ($districts as $dist): ?>
                            <option value="<?php echo htmlspecialchars($dist); ?>"><?php echo htmlspecialchars($dist); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Địa chỉ chi tiết</label>
                    <input type="text" name="address" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-grab btn-lg" style="width:100%;">
                <i class="fa-solid fa-check-circle"></i> Hoàn tất đăng ký
            </button>
        </form>

        <div style="text-align:center; margin-top:20px; font-size:14px;">
            Đã có tài khoản? <a href="login.php" style="color:var(--grab-green); font-weight:700;">Đăng nhập tại đây</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

