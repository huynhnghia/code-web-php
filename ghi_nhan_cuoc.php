<?php
require_once 'db.php';

$khach_hang_id = $_GET['khach_hang_id'] ?? 0;

// Lấy thông tin khách hàng
$stmt = $pdo->prepare("SELECT * FROM khach_hang WHERE id = ?");
$stmt->execute([$khach_hang_id]);
$khach_hang = $stmt->fetch();

if (!$khach_hang) {
    die("Không tìm thấy khách hàng!");
}

// Xử lý lưu cược
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mien = $_POST['mien'];
    // ... Lấy các giá trị từ POST ...
    
    $stmt = $pdo->prepare("INSERT INTO cuoc (khach_hang_id, mien, co_2_con_bao, trung_2_con_bao, co_da_thang, trung_da_thang, cach_trung_da_thang) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $khach_hang_id, 
        $mien, 
        $_POST['co_2_con_bao'], 
        $_POST['trung_2_con_bao'],
        $_POST['co_da_thang'],
        $_POST['trung_da_thang'],
        $_POST['cach_trung_da_thang']
    ]);
    
    echo "<script>alert('Đã lưu cược thành công!'); window.location.href='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghi nhận cược</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Copy CSS từ trang them_khach_hang.php sang đây */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f5f5f5; }
        .header { background-color: #e83e6d; color: white; display: flex; align-items: center; padding: 15px; position: sticky; top: 0; z-index: 100; }
        .header a { color: white; text-decoration: none; font-size: 20px; margin-right: 15px; }
        .header h2 { font-size: 18px; font-weight: normal; }
        .container { padding: 15px; }
        .customer-info { background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .customer-info h3 { font-size: 16px; color: #333; margin-bottom: 5px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; color: #666; margin-bottom: 5px; }
        .form-group input[type="text"], .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn-save { background-color: #e83e6d; color: white; border: none; padding: 12px; width: 100%; border-radius: 25px; font-size: 16px; font-weight: bold; margin-top: 20px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="header">
        <a href="dashboard.php"><i class="fa-solid fa-arrow-left"></i></a>
        <h2>Ghi nhận cược</h2>
    </div>

    <div class="container">
        <div class="customer-info">
            <h3>Khách hàng: <?= htmlspecialchars($khach_hang['ten']) ?></h3>
            <p style="font-size: 13px; color: #666;">SĐT: <?= htmlspecialchars($khach_hang['dien_thoai']) ?> | Loại: <?= $khach_hang['loai'] == 'chu' ? 'Chủ' : 'Khách' ?></p>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label>Chọn Miền:</label>
                <select name="mien">
                    <option value="mien_nam">Miền Nam</option>
                    <option value="mien_trung">Miền Trung</option>
                    <option value="mien_bac">Miền Bắc</option>
                </select>
            </div>

            <!-- Copy các trường nhập liệu tỷ lệ cược từ file them_khach_hang.php sang đây -->
            <div class="form-group">
                <label>Có 2 con bao</label>
                <input type="text" name="co_2_con_bao" value="0.75">
            </div>
            <div class="form-group">
                <label>Trúng 2 con bao</label>
                <input type="text" name="trung_2_con_bao" value="75">
            </div>
            
            <button type="submit" class="btn-save">LƯU CƯỢC</button>
        </form>
    </div>

</body>
</html>