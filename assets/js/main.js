// ==========================================================================
// JAVASCRIPT TƯƠNG TÁC: WEBSITE TÌM DỊCH VỤ QUANH MÌNH (GRAB-STYLE)
// ==========================================================================

document.addEventListener('DOMContentLoaded', function () {
    // Tự động ẩn thông báo Flash sau 5 giây
    const flashAlert = document.querySelector('.alert-auto-dismiss');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.transition = 'opacity 0.4s ease';
            flashAlert.style.opacity = '0';
            setTimeout(() => flashAlert.remove(), 400);
        }, 5000);
    }

    // Xử lý chọn vị trí hiện tại trên thanh Header
    const headerDistrictSelect = document.getElementById('headerDistrictSelect');
    if (headerDistrictSelect) {
        headerDistrictSelect.addEventListener('change', function () {
            const district = this.value;
            const currentUrl = new URL(window.location.href);
            if (district) {
                window.location.href = 'search.php?district=' + encodeURIComponent(district);
            } else {
                window.location.href = 'search.php';
            }
        });
    }
});

/**
 * Giả lập định vị GPS quanh người dùng kiểu Grab
 */
function detectMyLocation() {
    const btn = document.getElementById('btnDetectLocation');
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang định vị GPS...';
    }
    setTimeout(() => {
        const districts = ['Phường Lộc Thọ', 'Phường Vĩnh Phước', 'Phường Vĩnh Hải', 'Phường Phước Hải', 'Phường Tân Lập'];
        const picked = districts[0]; // Mặc định gợi ý trung tâm Phường Lộc Thọ - TP. Nha Trang
        window.location.href = 'search.php?sort=nearest&near=1';
    }, 600);
}

/**
 * Điền nhanh tài khoản Demo ở trang Đăng nhập
 */
function fillDemoAccount(email, password) {
    const emailInput = document.getElementById('loginEmail');
    const passInput = document.getElementById('loginPassword');
    if (emailInput && passInput) {
        emailInput.value = email;
        passInput.value = password;
        document.getElementById('loginForm').submit();
    }
}

