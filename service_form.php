<?php
require_once __DIR__ . '/config/db.php';

$serviceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = ($serviceId > 0);
$activeProviderId = isLoggedIn() ? (int)$_SESSION['user']['id'] : 2;

$serviceData = [
    'title' => '',
    'category_id' => 1,
    'description' => '',
    'price_list' => '',
    'price_min' => '',
    'price_max' => '',
    'price_unit' => '',
    'address' => '',
    'district' => 'Phường Lộc Thọ',
    'distance_km' => '',
    'phone_contact' => '',
    'zalo_contact' => '',
    'open_hours' => '',
    'image' => '',
    'is_home_service' => 1,
    'is_featured' => 1,
    'status' => 'active'
];

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $found = $stmt->fetch();
    if ($found) {
        $serviceData = $found;
    } else {
        setFlash('error', 'Không tìm thấy dịch vụ cần chỉnh sửa.');
        header('Location: provider_dashboard.php');
        exit;
    }
}

// Xử lý lưu thông tin dịch vụ (Thêm mới hoặc Cập nhật)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 1);
    $description = trim($_POST['description'] ?? '');
    $priceList = trim($_POST['price_list'] ?? '');
    $priceMin = (float)($_POST['price_min'] ?? 0);
    $priceMax = (float)($_POST['price_max'] ?? 0);
    $priceUnit = trim($_POST['price_unit'] ?? 'lần');
    $address = trim($_POST['address'] ?? '');
    $district = trim($_POST['district'] ?? 'Phường Lộc Thọ');
    $distanceKm = (float)($_POST['distance_km'] ?? 1.5);
    $phoneContact = trim($_POST['phone_contact'] ?? '');
    $zaloContact = trim($_POST['zalo_contact'] ?? $phoneContact);
    $openHours = trim($_POST['open_hours'] ?? '07:30 - 21:30');
    $imageUrl = trim($_POST['image'] ?? '');
    $isHomeService = isset($_POST['is_home_service']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';

    // Xử lý upload file ảnh từ máy tính nếu người dùng chọn file
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $newFileName = 'service_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $newFileName)) {
                $imageUrl = 'assets/uploads/' . $newFileName;
            }
        }
    }

    if ($imageUrl === '') {
        $imageUrl = 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80';
    }

    if ($title === '' || $address === '' || $phoneContact === '') {
        setFlash('error', 'Vui lòng điền đầy đủ Tên dịch vụ, Địa chỉ và Số điện thoại liên hệ!');
    } else {
        if ($isEdit) {
            $stmtUpdate = $pdo->prepare("
                UPDATE services
                SET category_id = :cat_id,
                    title = :title,
                    description = :desc,
                    price_list = :plist,
                    price_min = :pmin,
                    price_max = :pmax,
                    price_unit = :punit,
                    address = :addr,
                    district = :dist,
                    distance_km = :km,
                    phone_contact = :phone,
                    zalo_contact = :zalo,
                    open_hours = :hours,
                    image = :img,
                    is_home_service = :home,
                    status = :status
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ':cat_id' => $categoryId,
                ':title' => $title,
                ':desc' => $description,
                ':plist' => $priceList,
                ':pmin' => $priceMin,
                ':pmax' => $priceMax,
                ':punit' => $priceUnit,
                ':addr' => $address,
                ':dist' => $district,
                ':km' => $distanceKm,
                ':phone' => $phoneContact,
                ':zalo' => $zaloContact,
                ':hours' => $openHours,
                ':img' => $imageUrl,
                ':home' => $isHomeService,
                ':status' => $status,
                ':id' => $serviceId
            ]);
            setFlash('success', 'Đã cập nhật thông tin dịch vụ thành công!');
            header("Location: service_detail.php?id=$serviceId");
            exit;
        } else {
            $stmtInsert = $pdo->prepare("
                INSERT INTO services (provider_id, category_id, title, description, price_list, price_min, price_max, price_unit, address, district, distance_km, phone_contact, zalo_contact, open_hours, image, is_home_service, is_featured, status)
                VALUES (:pid, :cat_id, :title, :desc, :plist, :pmin, :pmax, :punit, :addr, :dist, :km, :phone, :zalo, :hours, :img, :home, 1, :status)
            ");
            $stmtInsert->execute([
                ':pid' => $activeProviderId,
                ':cat_id' => $categoryId,
                ':title' => $title,
                ':desc' => $description,
                ':plist' => $priceList,
                ':pmin' => $priceMin,
                ':pmax' => $priceMax,
                ':punit' => $priceUnit,
                ':addr' => $address,
                ':dist' => $district,
                ':km' => $distanceKm,
                ':phone' => $phoneContact,
                ':zalo' => $zaloContact,
                ':hours' => $openHours,
                ':img' => $imageUrl,
                ':home' => $isHomeService,
                ':status' => $status
            ]);
            $newId = (int)$pdo->lastInsertId();
            setFlash('success', 'Đăng dịch vụ mới thành công! Dịch vụ của bạn đã xuất hiện trên hệ thống tìm kiếm quanh mình.');
            header("Location: service_detail.php?id=$newId");
            exit;
        }
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
$pageTitle = $isEdit ? 'Chỉnh sửa dịch vụ' : 'Đăng dịch vụ mới';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding:28px 20px 50px; max-width:920px;">
    <div class="card-box">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:22px; padding-bottom:16px; border-bottom:1px solid var(--border-color);">
            <div>
                <h1 style="font-size:23px; font-weight:800;">
                    <i class="fa-solid <?php echo $isEdit ? 'fa-pen-to-square' : 'fa-plus-circle'; ?>" style="color:var(--grab-green); margin-right:8px;"></i>
                    <?php echo $isEdit ? 'Chỉnh sửa thông tin dịch vụ' : 'Đăng dịch vụ mới lên NearService'; ?>
                </h1>
                <p style="font-size:13.5px; color:var(--text-muted); margin-top:4px;">
                    Cung cấp thông tin chi tiết, khu vực hoạt động và bảng giá tham khảo để khách hàng quanh bạn dễ dàng liên hệ.
                </p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <?php if (!$isEdit): ?>
                    <button type="button" onclick="fillSampleService()" class="btn btn-outline-grab">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Điền mẫu nhanh
                    </button>
                <?php endif; ?>
                <a href="provider_dashboard.php" class="btn btn-light">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại kênh đối tác
                </a>
            </div>
        </div>

        <script>
        function fillSampleService() {
            document.querySelector('input[name="title"]').value = 'Cứu hộ và sửa xe máy lưu động 24/7 tại Nha Trang';
            document.querySelector('select[name="category_id"]').value = '1';
            document.querySelector('input[name="price_min"]').value = '50000';
            document.querySelector('input[name="price_max"]').value = '350000';
            document.querySelector('input[name="price_unit"]').value = 'lần';
            document.querySelector('input[name="address"]').value = '86 Trần Phú, TP. Nha Trang';
            document.querySelector('select[name="district"]').value = 'Phường Lộc Thọ';
            document.querySelector('input[name="distance_km"]').value = '0.9';
            document.querySelector('input[name="phone_contact"]').value = '0909123456';
            document.querySelector('input[name="zalo_contact"]').value = '0909123456';
            document.querySelector('textarea[name="description"]').value = 'Chuyên cứu hộ xe máy thủng lốp, hết bình ắc quy, chết máy tận nơi tại khu vực TP. Nha Trang chỉ sau 15 phút gọi. Báo giá minh bạch trước khi làm.';
        }
        </script>

        <form action="service_form.php<?php echo $isEdit ? '?id=' . $serviceId : ''; ?>" method="POST" enctype="multipart/form-data">
            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:18px; margin-bottom:16px;">
                <div>
                    <label class="form-label">Tên dịch vụ </label>
                    <input type="text" name="title" class="form-control" required
                           value="<?php echo htmlspecialchars((string)$serviceData['title']); ?>">
                </div>
                <div>
                    <label class="form-label">Danh mục dịch vụ</label>
                    <select name="category_id" class="form-control" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo ((int)$serviceData['category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:18px; margin-bottom:16px;">
                <div>
                    <label class="form-label">Giá thấp nhất (VNĐ)</label>
                    <input type="number" name="price_min" class="form-control" required min="0" step="5000"
                           value="<?php echo $serviceData['price_min'] !== '' ? (float)$serviceData['price_min'] : ''; ?>">
                </div>
                <div>
                    <label class="form-label">Giá cao nhất (VNĐ)</label>
                    <input type="number" name="price_max" class="form-control" min="0" step="5000"
                           value="<?php echo $serviceData['price_max'] !== '' ? (float)$serviceData['price_max'] : ''; ?>">
                </div>
                <div>
                    <label class="form-label">Đơn vị tính</label>
                    <input type="text" name="price_unit" class="form-control"
                           value="<?php echo htmlspecialchars((string)$serviceData['price_unit']); ?>">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1.5fr 1fr 0.8fr; gap:18px; margin-bottom:16px;">
                <div>
                    <label class="form-label">Địa chỉ cụ thể</label>
                    <input type="text" name="address" class="form-control" required
                           value="<?php echo htmlspecialchars((string)$serviceData['address']); ?>">
                </div>
                <div>
                    <label class="form-label">Khu vực</label>
                    <select name="district" class="form-control" required>
                        <?php foreach ($districts as $dist): ?>
                            <option value="<?php echo htmlspecialchars($dist); ?>" <?php echo ($serviceData['district'] === $dist) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dist); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Khoảng cách tâm</label>
                    <input type="number" name="distance_km" class="form-control" step="0.1" min="0.2" max="15"
                           value="<?php echo $serviceData['distance_km'] !== '' ? (float)$serviceData['distance_km'] : ''; ?>">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:18px; margin-bottom:16px;">
                <div>
                    <label class="form-label">Số điện thoại liên hệ</label>
                    <input type="text" name="phone_contact" class="form-control" required
                           value="<?php echo htmlspecialchars((string)$serviceData['phone_contact']); ?>">
                </div>
                <div>
                    <label class="form-label">Số Zalo nhận tin nhắn</label>
                    <input type="text" name="zalo_contact" class="form-control"
                           value="<?php echo htmlspecialchars((string)$serviceData['zalo_contact']); ?>">
                </div>
                <div>
                    <label class="form-label">Khung giờ hoạt động</label>
                    <input type="text" name="open_hours" class="form-control"
                           value="<?php echo htmlspecialchars((string)$serviceData['open_hours']); ?>">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">Mô tả chi tiết dịch vụ và cam kết chất lượng</label>
                <textarea name="description" rows="4" class="form-control" required><?php echo htmlspecialchars((string)$serviceData['description']); ?></textarea>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">Bảng giá tham khảo chi tiết</label>
                <textarea name="price_list" rows="4" class="form-control"><?php echo htmlspecialchars((string)$serviceData['price_list']); ?></textarea>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px; margin-bottom:18px;">
                <div>
                    <label class="form-label">Link ảnh đại diện dịch vụ (URL)</label>
                    <input type="text" name="image" class="form-control"
                           value="<?php echo htmlspecialchars((string)$serviceData['image']); ?>">
                </div>
                <div>
                    <label class="form-label">Hoặc tải ảnh trực tiếp từ máy tính</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                </div>
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; background:var(--grab-green-soft); padding:14px 18px; border-radius:10px; margin-bottom:22px;">
                <label style="display:flex; align-items:center; gap:10px; font-weight:700; cursor:pointer;">
                    <input type="checkbox" name="is_home_service" value="1" <?php echo !empty($serviceData['is_home_service']) ? 'checked' : ''; ?> style="width:18px; height:18px; accent-color:var(--grab-green);">
                    <span>🛵 Có hỗ trợ phục vụ tận nơi / lưu động tại nhà khách hàng</span>
                </label>

                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:13.5px; font-weight:600;">Trạng thái:</span>
                    <select name="status" class="form-control" style="width:auto; padding:6px 12px;">
                        <option value="active" <?php echo $serviceData['status'] === 'active' ? 'selected' : ''; ?>>🟢 Đang hoạt động</option>
                        <option value="inactive" <?php echo $serviceData['status'] === 'inactive' ? 'selected' : ''; ?>>⚪ Tạm nghỉ</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <a href="provider_dashboard.php" class="btn btn-light btn-lg">Hủy bỏ</a>
                <button type="submit" class="btn btn-grab btn-lg">
                    <i class="fa-solid fa-floppy-disk"></i> <?php echo $isEdit ? 'Lưu cập nhật dịch vụ' : 'Đăng dịch vụ ngay'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

