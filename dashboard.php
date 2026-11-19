<?php
session_start(); 
require_once 'db.php'; 

if (isset($_GET['set_date'])) {
    $_SESSION['ngay_lam_viec'] = $_GET['set_date'];
    header("Location: dashboard.php");
    exit;
}

$ngay_lam_viec = $_SESSION['ngay_lam_viec'] ?? date('Y-m-d');
$username = $_SESSION['username'] ?? "admin"; 
$ngay_het_han_raw = $_SESSION['ngay_het_han'] ?? "2027-02-15"; 
$ngay_het_han = date('d-m-Y', strtotime($ngay_het_han_raw));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f4f4; padding-bottom: 70px; }

        /* Header */
        .header { background-color: #e83e6d; color: white; display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; position: sticky; top: 0; z-index: 100; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left a { color: white; text-decoration: none; font-size: 22px; }
        .header-right { display: flex; align-items: center; gap: 10px; }
        
        .icon-box { border: 1px solid white; border-radius: 4px; padding: 5px 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; text-decoration: none; color: white;}
        
        /* Khung chọn ngày */
        .date-picker-wrapper { position: relative; display: flex; align-items: center; gap: 5px; color: white; cursor: pointer; font-size: 14px; }
        .date-picker-wrapper input[type="date"] { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; left: 0; top: 0;}
        .date-picker-wrapper span { font-size: 12px; font-weight: bold;}

        /* Menu List */
        .menu-list { background-color: white; }
        .menu-item { display: flex; align-items: center; padding: 15px; border-bottom: 1px solid #eee; color: #333; text-decoration: none; font-size: 16px; transition: background 0.2s; }
        .menu-item:hover { background-color: #f9f9f9; }
        .menu-item i { color: #e83e6d; font-size: 20px; width: 35px; text-align: center; margin-right: 15px; }

        /* Account Info */
        .account-info { padding: 15px; font-size: 14px; color: #333; border-bottom: 1px solid #eee; background-color: white; line-height: 1.6; }

        /* Bottom Nav */
        .bottom-nav { position: fixed; bottom: 0; left: 0; width: 100%; background-color: #e83e6d; display: flex; justify-content: space-between; padding: 15px; z-index: 100; }
        .bottom-nav a { color: white; text-decoration: none; font-size: 14px; font-weight: bold; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <!-- Nút 3 gạch chuyển về trang chủ dashboard (vì đang ở dashboard nên nó giữ nguyên) -->
            <a href="dashboard.php"><i class="fa-solid fa-bars"></i></a>
        </div>
        <div class="header-right">
            <a href="dashboard.php" class="icon-box"><i class="fa-solid fa-house"></i></a>
            <div class="date-picker-wrapper">
                <i class="fa-regular fa-calendar-days" style="font-size: 18px;"></i>
                <span><?= date('d-m-Y', strtotime($ngay_lam_viec)) ?></span>
                <input type="date" value="<?= $ngay_lam_viec ?>" onchange="window.location.href='dashboard.php?set_date=' + this.value;">
            </div>
        </div>
    </div>

    <!-- MENU LIST (Nội dung chính) -->
    <div class="menu-list">
        <a href="xu_ly_mien_nam.php" class="menu-item">
            <i class="fa-regular fa-comments"></i> Xử lý Miền Nam
        </a>
        <a href="#" class="menu-item">
            <i class="fa-regular fa-comments"></i> Xử lý Miền Trung
        </a>
        <a href="#" class="menu-item">
            <i class="fa-regular fa-comments"></i> Xử lý Miền Bắc
        </a>
        <a href="ketqua.php" class="menu-item">
            <i class="fa-regular fa-calendar-check"></i> Kết quả xổ số
        </a>
        <a href="#" class="menu-item">
            <i class="fa-solid fa-calculator"></i> Tổng kết ngày
        </a>
        <a href="#" class="menu-item">
            <i class="fa-solid fa-file-invoice"></i> Thống kê ngày
        </a>
        <a href="loai_cuoc.php" class="menu-item">
            <i class="fa-regular fa-folder"></i> Loại cược
        </a>
        <a href="he_quy_doi.php" class="menu-item">
            <i class="fa-regular fa-folder-open"></i> Hệ quy đổi
        </a>
        <a href="#" class="menu-item">
            <i class="fa-solid fa-gears"></i> Cài đặt
        </a>
        <a href="#" class="menu-item" style="border-bottom: none;">
            <i class="fa-solid fa-key"></i> Mã giới thiệu
        </a>
    </div>

    <div class="account-info">
        <div><strong>Sử dụng đến :</strong> <?= $ngay_het_han ?></div>
        <div><strong>Tài khoản :</strong> <?= htmlspecialchars($username) ?></div>
    </div>

    <div class="bottom-nav">
        <a href="#"><i class="fa-solid fa-book-open"></i> HƯỚNG DẪN</a>
        <a href="logout.php">LOGOUT <i class="fa-solid fa-arrow-right-from-bracket"></i></a>
    </div>
</body>
</html>