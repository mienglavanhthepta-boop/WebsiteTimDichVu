<?php
require_once __DIR__ . '/config/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($fullname !== '' && $phone !== '') {
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, phone = ?, district = ?, address = ? WHERE id = ?");
        $stmt->execute([$fullname, $phone, $district, $address, $userId]);

        $_SESSION['user']['fullname'] = $fullname;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['district'] = $district;
        $_SESSION['user']['address'] = $address;

        setFlash('success', 'Đã cập nhật thông tin tài khoản thành công!');
        header('Location: profile.php');
        exit;
    }
}

$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

$pageTitle = 'Thông tin tài khoản';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding:36px 20px 60px; max-width:650px;">
    <div class="card-box">
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px; padding-bottom:18px; border-bottom:1px solid var(--border-color);">
            <div style="width:64px; height:64px; border-radius:50%; background:var(--grab-green-light); color:var(--grab-green); display:flex; align-items:center; justify-content:center; font-size:28px;">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <span class="badge-grab-partner" style="margin-bottom:4px;">
                    <?php echo $user['role'] === 'provider' ? 'Đối tác cung cấp dịch vụ' : ($user['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng'); ?>
                </span>
                <h1 style="font-size:22px; font-weight:800;"><?php echo htmlspecialchars($user['fullname']); ?></h1>
                <div style="font-size:13.5px; color:var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
        </div>

        <form action="profile.php" method="POST">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label class="form-label">Họ và tên</label>
                    <input type="text" name="fullname" class="form-control" required value="<?php echo htmlspecialchars($user['fullname']); ?>">
                </div>
                <div>
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" required value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1.6fr; gap:16px; margin-bottom:22px;">
                <div>
                    <label class="form-label">Khu vực mặc định</label>
                    <select name="district" class="form-control">
                        <?php foreach ($districts as $dist): ?>
                            <option value="<?php echo htmlspecialchars($dist); ?>" <?php echo ($user['district'] === $dist) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dist); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Địa chỉ liên hệ</label>
                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($user['address']); ?>">
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center;">
                <a href="booking_history.php" class="btn btn-light">
                    <i class="fa-solid fa-calendar-check"></i> Xem lịch đặt dịch vụ
                </a>
                <button type="submit" class="btn btn-grab">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

