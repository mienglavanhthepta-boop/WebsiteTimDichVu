<?php
/**
 * Component hiển thị 1 thẻ Dịch vụ (Service Card) chuẩn phong cách Grab
 * Biến đầu vào: $srv (mảng dữ liệu dịch vụ từ câu truy vấn)
 */
$avgRating = !empty($srv['avg_rating']) ? number_format((float)$srv['avg_rating'], 1) : '5.0';
$reviewCount = isset($srv['review_count']) ? (int)$srv['review_count'] : 0;
$estMinutes = max(5, round((float)$srv['distance_km'] * 8));
$defaultImg = 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80';
$imgSrc = !empty($srv['image']) ? $srv['image'] : $defaultImg;
?>
<div class="service-card">
    <a href="service_detail.php?id=<?php echo (int)$srv['id']; ?>" class="service-thumb">
        <img src="<?php echo htmlspecialchars($imgSrc); ?>"
             alt="<?php echo htmlspecialchars($srv['title']); ?>"
             loading="lazy"
             onerror="this.onerror=null;this.src='<?php echo $defaultImg; ?>';">
        <div class="badge-top-left">
            <?php if (!empty($srv['is_featured'])): ?>
                <span class="badge-grab-partner">
                    <i class="fa-solid fa-circle-check"></i> Đối tác uy tín
                </span>
            <?php endif; ?>
            <?php if (!empty($srv['is_home_service'])): ?>
                <span class="badge-home-service">
                    <i class="fa-solid fa-motorcycle"></i> Tận nơi
                </span>
            <?php endif; ?>
        </div>
        <div class="badge-distance">
            <i class="fa-solid fa-location-arrow"></i>
            <?php echo number_format((float)$srv['distance_km'], 1); ?> km • ~<?php echo $estMinutes; ?> phút
        </div>
    </a>

    <div class="service-body">
        <div class="service-cat-row">
            <span class="service-cat-tag">
                <i class="fa-solid <?php echo htmlspecialchars($srv['category_icon'] ?? 'fa-tag'); ?>"></i>
                <?php echo htmlspecialchars($srv['category_name'] ?? 'Dịch vụ'); ?>
            </span>
            <div class="service-rating">
                <i class="fa-solid fa-star" style="color:var(--grab-yellow);"></i>
                <span><?php echo $avgRating; ?></span>
                <span style="color:var(--text-muted); font-weight:500;">(<?php echo $reviewCount; ?>)</span>
            </div>
        </div>

        <a href="service_detail.php?id=<?php echo (int)$srv['id']; ?>" class="service-title" title="<?php echo htmlspecialchars($srv['title']); ?>">
            <?php echo htmlspecialchars($srv['title']); ?>
        </a>

        <div class="service-meta">
            <div class="service-meta-item" title="<?php echo htmlspecialchars($srv['provider_name'] ?? 'Nhà cung cấp'); ?>">
                <i class="fa-solid fa-user-check"></i>
                <span><strong><?php echo htmlspecialchars($srv['provider_name'] ?? 'Nhà cung cấp'); ?></strong></span>
            </div>
            <div class="service-meta-item" title="<?php echo htmlspecialchars($srv['district'] . ' - ' . $srv['address']); ?>">
                <i class="fa-solid fa-location-dot"></i>
                <span><strong><?php echo htmlspecialchars($srv['district']); ?></strong> • <?php echo htmlspecialchars($srv['address']); ?></span>
            </div>
            <div class="service-meta-item">
                <i class="fa-solid fa-clock"></i>
                <span>Hoạt động: <?php echo htmlspecialchars($srv['open_hours']); ?></span>
            </div>
        </div>

        <div class="service-footer">
            <div>
                <span class="price-label">Giá tham khảo từ</span>
                <span class="price-value">
                    <?php echo formatPrice($srv['price_min']); ?>
                    <small style="font-size:12px; font-weight:600; color:var(--text-muted);">/ <?php echo htmlspecialchars($srv['price_unit']); ?></small>
                </span>
            </div>
            <div style="display:flex; gap:6px; align-items:center;">
                <a href="tel:<?php echo htmlspecialchars($srv['phone_contact']); ?>" class="btn btn-outline-grab btn-sm" title="Gọi ngay: <?php echo htmlspecialchars($srv['phone_contact']); ?>">
                    <i class="fa-solid fa-phone"></i>
                </a>
                <a href="service_detail.php?id=<?php echo (int)$srv['id']; ?>" class="btn btn-grab btn-sm">
                    Xem và đặt <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
