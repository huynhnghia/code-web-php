<?php
session_start();
require_once 'db.php'; 

$ngay_lam_viec = $_SESSION['ngay_lam_viec'] ?? date('Y-m-d');

// Cập nhật ngày nếu chọn từ Header
if (isset($_GET['set_date'])) {
    $ngay_lam_viec = $_GET['set_date'];
    $_SESSION['ngay_lam_viec'] = $ngay_lam_viec;
    header("Location: xu_ly_mien_nam.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM khach_hang ORDER BY id DESC");
$danh_sach_khach_hang = $stmt->fetchAll(PDO::FETCH_ASSOC);

function demSoTinNhan($pdo, $khach_hang_id, $ngay) {
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT cau_lenh) FROM chi_tiet_cuoc WHERE khach_hang_id = ? AND ngay_cuoc = ?");
    $stmt->execute([$khach_hang_id, $ngay]);
    return $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xử lý Miền Nam</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #fff; padding-bottom: 90px; overflow-x: hidden; } 
        
        /* HEADER MỚI */
        .header { background-color: #e83e6d; color: white; display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; position: sticky; top: 0; z-index: 100; }
        .header-left { display: flex; align-items: center; gap: 15px; font-size: 22px; }
        .header-left a { color: white; text-decoration: none; }
        .header-left .title { font-size: 18px; text-transform: uppercase; }
        .header-right { display: flex; align-items: center; gap: 10px; font-size: 14px; }
        
        /* Nút icon trên header */
        .icon-box { border: 1px solid white; border-radius: 4px; padding: 5px 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; text-decoration: none; color: white;}
        
        /* Khung chọn ngày */
        .date-picker-wrapper { 
            position: relative; display: flex; align-items: center; gap: 5px; 
            color: white; cursor: pointer; font-size: 14px;
        }
        .date-picker-wrapper input[type="date"] { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; left: 0; top: 0;}
        .date-picker-wrapper span { font-size: 12px; font-weight: bold;}

        .btn-do-lai { background: linear-gradient(to right, #f0548b, #e83e6d); color: white; padding: 8px 15px; border-radius: 0 20px 20px 0; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: bold; text-decoration: none; margin-top: 10px; margin-bottom: 10px; box-shadow: 2px 2px 5px rgba(0,0,0,0.2); }

        .customer-list { background-color: white; border-top: 1px solid #ddd; }
        .customer-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; position: relative; overflow: hidden; }
        
        .customer-info-link { display: flex; flex-grow: 1; padding: 15px; text-decoration: none; color: #333; z-index: 2; }
        .customer-info { display: flex; flex-direction: row; align-items: center; gap: 5px; }
        .customer-name { font-size: 18px; color: #333; font-weight: normal; }
        .customer-type { font-size: 14px; color: #888; }

        .customer-actions { display: flex; align-items: center; gap: 8px; padding-right: 15px; z-index: 2; transition: transform 0.3s ease; background: #fff;}
        .badge-tin { background: #f0548b; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        
        .btn-group-custom { display: flex; align-items: center; }
        .btn-arrow { background: #e83e6d; color: white; padding: 8px 10px 8px 15px; border-radius: 20px 0 0 20px; display: flex; align-items: center; justify-content: center; border-right: 1px solid rgba(255,255,255,0.3); cursor: pointer; }
        .btn-chat { background: #e83e6d; color: white; padding: 8px 15px 8px 10px; border-radius: 0 20px 20px 0; display: flex; align-items: center; gap: 3px; font-size: 16px; text-decoration: none; }
        .btn-chat .fa-circle-plus { font-size: 10px; margin-left: -5px; margin-top: 5px;}

        .action-menu { position: absolute; right: 0; top: 0; bottom: 0; display: flex; align-items: center; transform: translateX(100%); transition: transform 0.3s ease; z-index: 1; }
        .action-menu a { color: white; text-decoration: none; height: 100%; display: flex; align-items: center; justify-content: center; padding: 0 15px; font-size: 13px; }
        .btn-act-new { background: #ea5a7a; } 
        .btn-act-edit { background: #e35d6a; } 
        .btn-act-delete { background: #d63939; } 

        .customer-item.active .customer-actions { transform: translateX(-100%); opacity: 0; pointer-events: none;}
        .customer-item.active .action-menu { transform: translateX(0); }
        .customer-item.active .btn-arrow i { transform: rotate(180deg); }

        /* MENU TRƯỢT (SIDEBAR) */
        .sidebar { position: fixed; top: 0; left: -300px; width: 280px; height: 100%; background: #fff; z-index: 1000; transition: left 0.3s ease; overflow-y: auto; box-shadow: 2px 0 10px rgba(0,0,0,0.2); }
        .sidebar.active { left: 0; }
        .sidebar-header { background: #e83e6d; padding: 15px; color: white; display: flex; align-items: center; justify-content: space-between;}
        .sidebar-menu { list-style: none; }
        .sidebar-menu li { border-bottom: 1px solid #eee; }
        .sidebar-menu a { display: flex; align-items: center; padding: 15px 20px; text-decoration: none; color: #333; font-size: 16px; }
        .sidebar-menu a i { width: 30px; color: #e83e6d; font-size: 18px; }
        .sidebar-footer { padding: 15px; font-size: 14px; color: #333; line-height: 1.6; background: #f9f9f9; }
        
        /* Lớp phủ đen mờ khi mở menu */
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; display: none; }
        .overlay.active { display: block; }

        .fab-add { position: fixed; bottom: 80px; right: 20px; width: 60px; height: 60px; background-color: #e83e6d; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 26px; box-shadow: 0 4px 10px rgba(232, 62, 109, 0.4); text-decoration: none; z-index: 999; }
    </style>
</head>
<body>
    <!-- LỚP PHỦ ĐEN MỜ -->
    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <!-- MENU TRƯỢT (SIDEBAR) -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-bars" style="font-size: 20px;"></i>
            <i class="fa-solid fa-house" style="cursor: pointer;" onclick="window.location.href='dashboard.php'"></i>
        </div>
        <ul class="sidebar-menu">
            <li><a href="xu_ly_mien_nam.php"><i class="fa-regular fa-comments"></i> Xử lý Miền Nam</a></li>
            <li><a href="#"><i class="fa-regular fa-comments"></i> Xử lý Miền Trung</a></li>
            <li><a href="#"><i class="fa-regular fa-comments"></i> Xử lý Miền Bắc</a></li>
            <li><a href="ketqua.php"><i class="fa-regular fa-calendar-check"></i> Kết quả xổ số</a></li>
            <li><a href="#"><i class="fa-solid fa-calculator"></i> Tổng kết ngày</a></li>
            <li><a href="#"><i class="fa-solid fa-file-invoice"></i> Thống kê ngày</a></li>
            <li><a href="loai_cuoc.php"><i class="fa-regular fa-folder"></i> Loại cược</a></li>
            <li><a href="he_quy_doi.php"><i class="fa-regular fa-folder-open"></i> Hệ quy đổi</a></li>
            <li><a href="#"><i class="fa-solid fa-gears"></i> Cài đặt</a></li>
            <li><a href="#"><i class="fa-solid fa-key"></i> Mã giới thiệu</a></li>
        </ul>
        <div class="sidebar-footer">
            <div><strong>Sử dụng đến :</strong> 15-02-2027</div>
            <div><strong>Tài khoản :</strong> admin</div>
        </div>
    </div>

    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <!-- Nút 3 gạch gọi hàm toggleMenu() -->
            <a href="javascript:void(0);" onclick="toggleMenu()"><i class="fa-solid fa-bars"></i></a>
            <span class="title">MIỀN NAM</span>
        </div>
        <div class="header-right">
            <!-- Nút danh sách (giữ nguyên) -->
            <div class="icon-box"><i class="fa-solid fa-list-ul"></i></div>
            
            <!-- Nút Home chuyển về dashboard.php -->
            <a href="dashboard.php" class="icon-box"><i class="fa-solid fa-house"></i></a>
            
            <!-- Khung chọn ngày (Lịch) -->
            <div class="date-picker-wrapper">
                <i class="fa-regular fa-calendar-days" style="font-size: 18px;"></i>
                <span><?= date('d-m-Y', strtotime($ngay_lam_viec)) ?></span>
                <input type="date" value="<?= $ngay_lam_viec ?>" onchange="window.location.href='xu_ly_mien_nam.php?set_date=' + this.value;">
            </div>
        </div>
    </div>

    <a href="#" class="btn-do-lai" onclick="alert('Chức năng này đang được phát triển!'); return false;">
        <i class="fa-solid fa-gear"></i> Dò lại hết tin miền nam
    </a>

    <div class="customer-list">
        <?php foreach ($danh_sach_khach_hang as $kh): 
            $so_tin = demSoTinNhan($pdo, $kh['id'], $ngay_lam_viec);
            $link_xem_tin = "xem_tin_nhan.php?id=" . $kh['id'] . "&ngay=" . $ngay_lam_viec;
        ?>
            <div class="customer-item" id="item-<?= $kh['id'] ?>">
                
                <div class="action-menu">
                    <a href="them_khach_hang.php" class="btn-act-new">Tạo mới</a>
                    <a href="cap_nhat_khach_hang.php?id=<?= $kh['id'] ?>" class="btn-act-edit">Sửa</a>
                    <a href="xoa_khach_hang.php?id=<?= $kh['id'] ?>" class="btn-act-delete" onclick="return confirm('Bạn có chắc muốn xóa khách hàng <?= htmlspecialchars($kh['ten']) ?>?');">Xóa</a>
                </div>

                <a href="<?= $link_xem_tin ?>" class="customer-info-link">
                    <div class="customer-info">
                        <span class="customer-name"><?= htmlspecialchars($kh['ten']) ?></span>
                        <span class="customer-type">(<?= $kh['loai'] == 'chu' ? 'Chủ' : 'Khách' ?>)</span>
                    </div>
                </a>
                
                <div class="customer-actions">
                    <?php if ($so_tin > 0): ?>
                        <div class="badge-tin"><?= $so_tin ?> tin</div>
                    <?php endif; ?>
                    
                    <div class="btn-group-custom">
                        <div class="btn-arrow" onclick="toggleActionMenu(<?= $kh['id'] ?>, event)">
                            <i class="fa-solid fa-chevron-left"></i>
                        </div>
                        <a href="<?= $link_xem_tin ?>" class="btn-chat">
                            <i class="fa-regular fa-comment-dots"></i>
                            <i class="fa-solid fa-circle-plus"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="them_khach_hang.php" class="fab-add">
        <i class="fa-solid fa-user-plus"></i>
    </a>

    <script>
        // Hàm bật/tắt Menu Trượt (Sidebar)
        function toggleMenu() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Hàm bật/tắt Menu hành động (Tạo mới, Sửa, Xóa) của từng khách
        function toggleActionMenu(id, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            var currentItem = document.getElementById('item-' + id);
            var allItems = document.querySelectorAll('.customer-item');
            allItems.forEach(function(item) {
                if (item.id !== 'item-' + id) item.classList.remove('active');
            });
            currentItem.classList.toggle('active');
        }

        document.addEventListener('click', function(event) {
            if (!event.target.closest('.customer-item')) {
                document.querySelectorAll('.customer-item').forEach(function(item) {
                    item.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>