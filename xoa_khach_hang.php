<?php
require_once 'db.php';

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    // Xóa tất cả dữ liệu cược của khách này trước (nếu có)
    $pdo->prepare("DELETE FROM chi_tiet_cuoc WHERE khach_hang_id = ?")->execute([$id]);
    
    // Sau đó xóa khách hàng
    $pdo->prepare("DELETE FROM khach_hang WHERE id = ?")->execute([$id]);
}

// Xóa xong quay về trang danh sách
header("Location: xu_ly_mien_nam.php");
exit;
?>