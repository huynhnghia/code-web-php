<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten = $_POST['ten'] ?? '';
    $dien_thoai = $_POST['dien_thoai'] ?? '';
    $user_ho_tro = $_POST['user_ho_tro'] ?? '';
    $loai = $_POST['loai'] ?? 'khach';
    
    $fields = ['ten', 'dien_thoai', 'user_ho_tro', 'loai'];
    $placeholders = ['?', '?', '?', '?'];
    $params = [$ten, $dien_thoai, $user_ho_tro, $loai];

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'mn_') === 0 || strpos($key, 'mt_') === 0 || strpos($key, 'mb_') === 0) {
            $fields[] = $key;
            // Tự động đổi dấu phẩy thành dấu chấm trước khi lưu vào DB
            $placeholders[] = '?';
            $params[] = str_replace(',', '.', $value); 
        }
    }

    $sql = "INSERT INTO khach_hang (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo "<script>alert('Đã tạo khách hàng thành công!'); window.location.href='xu_ly_mien_nam.php';</script>";
    } catch (PDOException $e) {
        $error = "Lỗi khi thêm: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo khách hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f0f0f0; padding-bottom: 80px; }
        .header { background-color: #e83e6d; color: white; display: flex; align-items: center; padding: 15px; position: sticky; top: 0; z-index: 100; }
        .header a { color: white; text-decoration: none; font-size: 20px; margin-right: 15px; }
        .header h2 { font-size: 18px; font-weight: normal; }
        .container { padding: 10px; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; font-size: 16px; color: #666; margin-bottom: 5px; }
        /* Đã đổi thành type="text" để cho phép gõ dấu phẩy */
        .form-group input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; background: white; }
        .row { display: flex; gap: 10px; margin-bottom: 10px; }
        .col { flex: 1; }
        .radio-group { display: flex; gap: 20px; margin: 15px 0; align-items: center; }
        .radio-group label { display: flex; align-items: center; gap: 5px; font-size: 18px; color: #333; font-weight: bold;}
        .radio-group input[type="radio"] { width: 20px; height: 20px; }
        .region-title { background: #e0e0e0; padding: 12px; text-align: center; font-weight: bold; color: #333; margin: 15px 0 10px 0; font-size: 18px; text-transform: uppercase; }
        .btn-save { background-color: #e83e6d; color: white; border: none; padding: 10px 20px; border-radius: 20px; font-size: 15px; font-weight: bold; position: fixed; bottom: 20px; right: 20px; cursor: pointer; display: flex; align-items: center; gap: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.2); z-index: 999;}
        .alert { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <a href="xu_ly_mien_nam.php"><i class="fa-solid fa-arrow-left"></i></a>
        <h2>Tạo khách hàng</h2>
    </div>

    <div class="container">
        <?php if (isset($error)): ?><div class="alert"><?= $error ?></div><?php endif; ?>

        <form method="POST" id="form_khach_hang">
            <div class="row">
                <div class="col"><div class="form-group"><label>Họ & Tên:</label><input type="text" name="ten" required></div></div>
                <div class="col"><div class="form-group"><label>Điện thoại:</label><input type="text" name="dien_thoai"></div></div>
            </div>
            <div class="form-group" style="width: 50%;"><label>User hỗ trợ:</label><input type="text" name="user_ho_tro"></div>
            <div class="radio-group">
                <label><input type="radio" name="loai" value="khach" checked> Khách</label>
                <label><input type="radio" name="loai" value="chu"> Chủ</label>
            </div>

            <!-- MIỀN NAM -->
            <div class="region-title">MIỀN NAM</div>
            <div class="row">
                <div class="col"><div class="form-group"><label>Cò 2 con bao</label><input type="text" name="mn_co_2_con_bao" value="0.8"></div></div>
                <div class="col"><div class="form-group"><label>Trúng 2 con bao</label><input type="text" name="mn_trung_2_con_bao" value="80"></div></div>
            </div>
            <div class="row">
                <div class="col"><div class="form-group"><label>Cò 2 con đầu đuôi</label><input type="text" name="mn_co_2_con_dau_duoi" value="0.83"></div></div>
                <div class="col"><div class="form-group"><label>Trúng 2 con đầu đuôi</label><input type="text" name="mn_trung_2_con_dau_duoi" value="80"></div></div>
            </div>
            <div class="row">
                <div class="col"><div class="form-group"><label>Cò đá thẳng</label><input type="text" name="mn_co_da_thang" value="0.805555"></div></div>
                <div class="col"><div class="form-group"><label>Trúng đá thẳng</label><input type="text" name="mn_trung_da_thang" value="550"></div></div>
            </div>
            <div style="font-size: 16px; color: #666; margin-bottom: 10px;">Cách trúng đá thẳng:</div>
            <div class="radio-group" style="margin-bottom: 15px;">
                <label style="font-weight: normal; font-size: 16px;"><input type="radio" name="mn_cach_trung_da_thang" value="mot_lan"> Một lần</label>
                <label style="font-weight: normal; font-size: 16px;"><input type="radio" name="mn_cach_trung_da_thang" value="ky_ruoi" checked> Ký rưỡi</label>
                <label style="font-weight: normal; font-size: 16px;"><input type="radio" name="mn_cach_trung_da_thang" value="nhieu_cap"> Nhiều cặp</label>
            </div>
            <div class="row">
                <div class="col"><div class="form-group"><label>Cò đá xiên</label><input type="text" name="mn_co_da_xien" value="0.75"></div></div>
                <div class="col"><div class="form-group"><label>Trúng đá xiên</label><input type="text" name="mn_trung_da_xien" value="550"></div></div>
            </div>
            <div style="font-size: 16px; color: #666; margin-bottom: 10px;">Cách trúng đá xiên:</div>
            <div class="radio-group" style="margin-bottom: 15px;">
                <label style="font-weight: normal; font-size: 16px;"><input type="radio" name="mn_cach_trung_da_xien" value="mot_lan"> Một lần</label>
                <label style="font-weight: normal; font-size: 16px;"><input type="radio" name="mn_cach_trung_da_xien" value="ky_ruoi" checked> Ký rưỡi</label>
                <label style="font-weight: normal; font-size: 16px;"><input type="radio" name="mn_cach_trung_da_xien" value="nhieu_cap"> Nhiều cặp</label>
            </div>
            <div class="row">
                <div class="col"><div class="form-group"><label>Cò 3 con XC</label><input type="text" name="mn_co_3_con_xc" value="0.78"></div></div>
                <div class="col"><div class="form-group"><label>Trúng 3 con XC</label><input type="text" name="mn_trung_3_con_xc" value="600"></div></div>
            </div>
            <div class="row">
                <div class="col"><div class="form-group"><label>Cò 3 con bao</label><input type="text" name="mn_co_3_con_bao" value="0.6739"></div></div>
                <div class="col"><div class="form-group"><label>Trúng 3 con bao</label><input type="text" name="mn_trung_3_con_bao" value="600"></div></div>
            </div>
            <div class="row">
                <div class="col"><div class="form-group"><label>Cò 4 con</label><input type="text" name="mn_co_4_con" value="0.75"></div></div>
                <div class="col"><div class="form-group"><label>Trúng 4 con</label><input type="text" name="mn_trung_4_con" value="5500"></div></div>
            </div>
            <div class="form-group"><label>Phần trăm tổng:</label><input type="text" name="mn_phan_tram_tong" value="100"></div>

            <!-- MIỀN TRUNG -->
            <div class="region-title">MIỀN TRUNG</div>
            <div class="row">
                <div class="col"><div class="form-group"><label>Cò 2 con bao</label><input type="text" name="mt_co_2_con_bao" value="0.80277"></div></div>
                <div class="col"><div class="form-group"><label>Trúng 2 con bao</label><input type="text" name="mt_trung_2_con_bao" value="80"></div></div>
            </div>
            <!-- (Các trường Miền Trung và Miền Bắc khác bạn giữ nguyên như cũ, hoặc copy theo mẫu trên) -->
            
            <div class="row">
                <div class="col"><div class="form-group"><label>Cò 4 con (MT)</label><input type="text" name="mt_co_4_con" value="0.75"></div></div>
                <div class="col"><div class="form-group"><label>Trúng 4 con (MT)</label><input type="text" name="mt_trung_4_con" value="5500"></div></div>
            </div>
            <div class="form-group"><label>Phần trăm tổng (MT):</label><input type="text" name="mt_phan_tram_tong" value="100"></div>

            <!-- MIỀN BẮC -->
            <div class="region-title">MIỀN BẮC</div>
            <div class="row">
                <div class="col"><div class="form-group"><label>Cò 2 con bao</label><input type="text" name="mb_co_2_con_bao" value="0.8111"></div></div>
                <div class="col"><div class="form-group"><label>Trúng 2 con bao</label><input type="text" name="mb_trung_2_con_bao" value="80"></div></div>
            </div>
            <!-- (Các trường Miền Bắc khác tương tự) -->
            
            <div class="form-group"><label>Phần trăm tổng (MB):</label><input type="text" name="mb_phan_tram_tong" value="100"></div>

            <button type="submit" class="btn-save">
                <i class="fa-regular fa-floppy-disk"></i> LƯU MỚI
            </button>
        </form>
    </div>

    <!-- SCRIPT TỰ ĐỘNG ĐỔI DẤU PHẨY THÀNH DẤU CHẤM -->
    <script>
        document.getElementById('form_khach_hang').addEventListener('submit', function(e) {
            var inputs = this.querySelectorAll('input[type="text"]');
            inputs.forEach(function(input) {
                // Nếu có dấu phẩy, đổi thành dấu chấm
                if (input.value.includes(',')) {
                    input.value = input.value.replace(/,/g, '.');
                }
            });
        });
    </script>
</body>
</html>