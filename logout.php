<?php
require_once __DIR__ . '/config/db.php';
unset($_SESSION['user']);
setFlash('success', 'Bạn đã đăng xuất khỏi hệ thống thành công.');
header('Location: index.php');
exit;
?>

