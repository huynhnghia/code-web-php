<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db.php';

// Hàm dịch tên đài
function getTenDaiDayDu($viet_tat) {
    $map = [
        'bt' => 'Bến Tre', 'btre' => 'Bến Tre', 'vt' => 'Vũng Tàu', 'vtau' => 'Vũng Tàu',
        'bl' => 'Bạc Liêu', 'bli' => 'Bạc Liêu', 'blieu' => 'Bạc Liêu',
        'dn' => 'Đồng Nai', 'ct' => 'Cần Thơ', 'st' => 'Sóc Trăng', 'tp' => 'TP. HCM', 
        'hcm' => 'TP. HCM', 'cm' => 'Cà Mau', 'dt' => 'Đồng Tháp', 'tn' => 'Tây Ninh', 
        'ag' => 'An Giang', 'bth' => 'Bình Thuận', 'vl' => 'Vĩnh Long', 'bd' => 'Bình Dương', 
        'tv' => 'Trà Vinh', 'hg' => 'Hậu Giang', 'la' => 'Long An', 'bp' => 'Bình Phước',
        'tg' => 'Tiền Giang', 'kg' => 'Kiên Giang', 'dl' => 'Đà Lạt'
    ];
    return $map[$viet_tat] ?? $viet_tat;
}

// Hàm lấy danh sách đài theo thứ
function getDaiTheoThu($thu) {
    $lich_dai = [
        1 => ['tp', 'dt', 'cm'], 
        2 => ['bt', 'vt', 'bli'], 
        3 => ['dn', 'ct', 'st'],
        4 => ['tn', 'ag', 'bth'], 
        5 => ['vl', 'bd', 'tv'], 
        6 => ['tp', 'hg', 'la', 'bp'], 
        7 => ['tg', 'kg', 'dl']
    ];
    return $lich_dai[$thu] ?? ['bt', 'vt', 'bli'];
}

// Hàm kiểm tra trúng thưởng
function kiemTraTrung($pdo, $ngay, $dai, $con_so, $loai_cuoc) {
    $ten_dai_db = getTenDaiDayDu($dai);
    $stmt = $pdo->prepare("SELECT * FROM draws WHERE draw_date = ? AND region = 'XSMN' AND station = ?");
    $stmt->execute([$ngay, $ten_dai_db]);
    $kq = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$kq) return 0; 

    $con_so_str = str_pad($con_so, 2, '0', STR_PAD_LEFT);
    $prizes_data = json_decode($kq['prizes'], true);
    if (!$prizes_data) return 0;

    $get_last2 = function($giai) use ($prizes_data) {
        if (isset($prizes_data[$giai])) {
            $nums = [];
            foreach ($prizes_data[$giai] as $so) { $nums[] = substr($so, -2); }
            return $nums;
        }
        return [];
    };

    $loai_lower = strtolower($loai_cuoc);

    if (in_array($loai_lower, ['baolo', 'baodao', 'baylo', 'baylodao', 'tamlo'])) {
        foreach ($prizes_data as $giai => $cac_con_so) {
            if (is_array($cac_con_so)) {
                foreach ($cac_con_so as $so) {
                    if (substr($so, -2) == $con_so_str) return 1; 
                }
            }
        }
    } elseif (in_array($loai_lower, ['dau', 'daudao'])) {
        $g8 = $get_last2('8');
        if (in_array($con_so_str, $g8)) return 1;
    } elseif (in_array($loai_lower, ['duoi', 'duoidao'])) {
        $gdb = $get_last2('ĐB');
        if (in_array($con_so_str, $gdb)) return 1;
    } elseif (in_array($loai_lower, ['dd', 'dddao'])) {
        $g8 = $get_last2('8');
        $gdb = $get_last2('ĐB');
        if (in_array($con_so_str, $g8) || in_array($con_so_str, $gdb)) return 1;
    } elseif (in_array($loai_lower, ['da', 'dax'])) {
        foreach ($prizes_data as $giai => $cac_con_so) {
            if (is_array($cac_con_so)) {
                foreach ($cac_con_so as $so) {
                    if (substr($so, -2) == $con_so_str) return 1; 
                }
            }
        }
    } elseif (in_array($loai_lower, ['xc', 'xcdao', 'xcdau', 'xcdaudao', 'xcduoi', 'xcduoidao'])) {
        foreach ($prizes_data as $giai => $cac_con_so) {
            if (is_array($cac_con_so)) {
                foreach ($cac_con_so as $so) {
                    if (substr($so, -3) == str_pad($con_so, 3, '0', STR_PAD_LEFT)) return 1; 
                }
            }
        }
    }
    return 0;
}

// Hàm nhóm loại cược để tính toán
function get_calc_group($loai) {
    if (in_array($loai, ['baolo', 'baodao', 'baylo', 'baylodao', 'tamlo'])) return 'baolo';
    if (in_array($loai, ['dau', 'daudao'])) return 'dau';
    if (in_array($loai, ['dd', 'dddao', 'duoi', 'duoidao', 'da', 'dax'])) return 'dd';
    if (in_array($loai, ['xc', 'xcdao', 'xcdau', 'xcdaudao', 'xcduoi', 'xcduoidao'])) return 'xc';
    return $loai;
}

// HÀM BÓC TÁCH TIN NHẮN
function parseTinNhan($chuoi, $ngay_chon) {
    $chuoi = mb_strtolower(trim($chuoi));
    $chuoi = preg_replace('/^\d+\)\s*/', '', $chuoi); 
    
    // BẢO VỆ TỪ KHÓA 3DAI VÀ 3D
    $chuoi = preg_replace('/(?<![a-z0-9])3\s*đài(?![a-z0-9])/iu', ' ba_dai ', $chuoi);
    $chuoi = preg_replace('/(?<![a-z0-9])3\s*dai(?![a-z0-9])/iu', ' ba_dai ', $chuoi);
    $chuoi = preg_replace('/(?<![a-z0-9])3\s*d(?![a-z0-9])/iu', ' ba_dai ', $chuoi);
    
    $chuoi = preg_replace('/([a-z]+)(\d+)/', '$1 $2', $chuoi);
    $chuoi = preg_replace('/(\d+)([a-z]+)/', '$1 $2', $chuoi); 
    $chuoi = preg_replace('/\s+/', ' ', $chuoi);

    $cac_tu = explode(' ', trim($chuoi));
    
    $thu_cua_ngay = date('N', strtotime($ngay_chon)); 
    $danh_sach_dai_hien_tai = getDaiTheoThu($thu_cua_ngay);
    $dai_mac_dinh = $danh_sach_dai_hien_tai[0]; 
    
    $dai_map = [
        'vt' => 'vt', 'vtau' => 'vt', 'bt' => 'bt', 'btre' => 'bt',
        'bl' => 'bli', 'bli' => 'bli', 'blieu' => 'bli', 'dn' => 'dn',
        'ct' => 'ct', 'st' => 'st', 'tp' => 'tp', 'hcm' => 'tp', 'cm' => 'cm', 
        'dt' => 'dt', 'tn' => 'tn', 'ag' => 'ag', 'bth' => 'bth', 'vl' => 'vl', 
        'bd' => 'bd', 'tv' => 'tv', 'hg' => 'hg', 'la' => 'la', 
        'bp' => 'bp', 'tg' => 'tg', 'kg' => 'kg', 'dl' => 'dl'
    ];
    
    $loai_map = [
        'baodao' => 'baodao', 'baolodao' => 'baodao', 'bdao' => 'baodao', 'bldao' => 'baodao', 'daob' => 'baodao', 'daolo' => 'baodao', 'db' => 'baodao', 'dbao' => 'baodao', 'ldao' => 'baodao', 'lodao' => 'baodao',
        'b' => 'baolo', 'bao' => 'baolo', 'baolo' => 'baolo', 'bl' => 'baolo', 'blo' => 'baolo', 'l' => 'baolo', 'lo' => 'baolo',
        'bay' => 'baylo', 'baylo' => 'baylo', 'baylobao' => 'baylo', 'bbaylo' => 'baylo',
        'baobaylodao' => 'baylodao', 'baydao' => 'baylodao', 'baylodao' => 'baylodao', 'bbaylodao' => 'baylodao',
        'dat' => 'da', 'xienquay' => 'da', 'xq' => 'da', 'xquay' => 'da',
        'd' => 'dau', 'dau' => 'dau', 'daudao' => 'daudao',
        'da' => 'dax', 'dax' => 'dax', 'dx' => 'dax', 'xien' => 'dax', 'cheo' => 'dax', 'dav' => 'dax', 'dv' => 'dax',
        'daudui' => 'dd', 'dauduoi' => 'dd', 'dd' => 'dd',
        'dauduoidao' => 'dddao', 'dddao' => 'dddao',
        'de' => 'duoi', 'dit' => 'duoi', 'du' => 'duoi', 'dui' => 'duoi', 'duoi' => 'duoi',
        'daodui' => 'duoidao', 'duidao' => 'duoidao', 'duoidao' => 'duoidao',
        'tamlo' => 'tamlo', 'xba' => 'xba', 'xbo' => 'xbo',
        'tl' => 'xc', 'ts' => 'xc', 'x' => 'xc', 'xc' => 'xc', 'xchu' => 'xc', 'xieu' => 'xc', 'xiu' => 'xc', 'xiuchu' => 'xc', 'xccap' => 'xc',
        'daox' => 'xcdao', 'daoxc' => 'xcdao', 'dxc' => 'xcdao', 'tdao' => 'xcdao', 'tldao' => 'xcdao', 'xcd' => 'xcdao', 'xcdao' => 'xcdao', 'xd' => 'xcdao', 'xdao' => 'xcdao',
        'tldau' => 'xcdau', 'xcdau' => 'xcdau', 'xchudau' => 'xcdau', 'xdau' => 'xcdau', 'xiuchudau' => 'xcdau',
        'daotldau' => 'xcdaudao', 'daoxcdau' => 'xcdaudao', 'daoxdau' => 'xcdaudao', 'tldaudao' => 'xcdaudao', 'xcdaudao' => 'xcdaudao', 'xdaudao' => 'xcdaudao',
        'tldui' => 'xcduoi', 'tlduoi' => 'xcduoi', 'xcdui' => 'xcduoi', 'xcduoi' => 'xcduoi', 'xdui' => 'xcduoi', 'xduoi' => 'xcduoi',
        'daoxcdui' => 'xcduoidao', 'daoxcduoi' => 'xcduoidao', 'daoxdui' => 'xcduoidao', 'daoxduoi' => 'xcduoidao', 'tlduidao' => 'xcduoidao', 'tlduoidao' => 'xcduoidao', 'xcduidao' => 'xcduoidao', 'xcduoidao' => 'xcduoidao', 'xduidao' => 'xcduoidao', 'xduoidao' => 'xcduoidao',
        'xiendao' => 'xdao', 'xin' => 'xien'
    ];

    $ket_qua = [];
    $dang_chon_dai = [$dai_mac_dinh]; 
    $cac_con_so = [];

    $count = count($cac_tu);
    for ($i = 0; $i < $count; $i++) {
        $tu = $cac_tu[$i];

        // Xử lý "3dai"
        if ($tu === 'ba_dai') {
            $dang_chon_dai = $danh_sach_dai_hien_tai;
            continue;
        }

        // Nhận diện số
        if (is_numeric($tu) && strlen($tu) <= 3) {
            $cac_con_so[] = str_pad($tu, 2, '0', STR_PAD_LEFT);
        } 
        // Nhận diện tên đài
        elseif (isset($dai_map[$tu])) {
            $ma_dai = $dai_map[$tu];
            if (!in_array($ma_dai, $danh_sach_dai_hien_tai)) {
                return ['error' => "Lỗi: <b>" . htmlspecialchars($tu) . "</b> Không xác nhận được (Không quay trong ngày này)"];
            }
            
            if (count($dang_chon_dai) == 1 && $dang_chon_dai[0] == $dai_mac_dinh) {
                $dang_chon_dai = []; 
            }
            if (!in_array($ma_dai, $dang_chon_dai)) {
                $dang_chon_dai[] = $ma_dai; 
            }
        } 
        // Nhận diện loại cược
        elseif (isset($loai_map[$tu])) {
            $loai_cuoc = $loai_map[$tu];
            $so_tien = 0;
            
            for ($j = $i + 1; $j < $count; $j++) {
                if (is_numeric($cac_tu[$j])) {
                    $so_tien = (int)$cac_tu[$j];
                    $i = $j; 
                    if ($i + 1 < $count && ($cac_tu[$i+1] == 'n' || $cac_tu[$i+1] == 'k')) {
                        $i++;
                    }
                    break;
                } elseif ($cac_tu[$j] == 'n' || $cac_tu[$j] == 'k') {
                    break; 
                } elseif (isset($loai_map[$cac_tu[$j]]) || isset($dai_map[$cac_tu[$j]]) || $cac_tu[$j] === 'ba_dai') {
                    break;
                }
            }
            
            // CHỈ LẤY SỐ VỪA NHẬP, KHÔNG DÙNG SỐ CŨ
            if ($so_tien > 0 && !empty($cac_con_so)) {
                foreach ($dang_chon_dai as $dai) {
                    foreach ($cac_con_so as $con_so) {
                        $ket_qua[] = [
                            'dai' => $dai, 
                            'so' => $con_so, 
                            'loai_cuoc' => $loai_cuoc, 
                            'so_tien' => $so_tien
                        ];
                    }
                }
            }
            
            $cac_con_so = []; 
            $dang_chon_dai = [$dai_mac_dinh]; 
        }
    }
    
    return $ket_qua;
}

// XỬ LÝ LỆNH XÓA TIN NHẮN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $khach_hang_id = $_POST['khach_hang_id'] ?? 0;
    $ngay_cuoc = $_POST['ngay_cuoc'] ?? date('Y-m-d');
    $cau_lenh_can_xoa = $_POST['cau_lenh_can_xoa'] ?? '';

    if ($khach_hang_id > 0 && $cau_lenh_can_xoa !== '') {
        $stmt = $pdo->prepare("DELETE FROM chi_tiet_cuoc WHERE khach_hang_id = ? AND ngay_cuoc = ? AND cau_lenh = ?");
        $stmt->execute([$khach_hang_id, $ngay_cuoc, $cau_lenh_can_xoa]);
    }
    header("Location: xem_tin_nhan.php?id=" . $khach_hang_id . "&ngay=" . $ngay_cuoc);
    exit;
}

// XỬ LÝ LƯU DỮ LIỆU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cau_lenh'])) {
    try {
        $khach_hang_id = $_POST['khach_hang_id'] ?? 0;
        
        if ($khach_hang_id == 0) {
            die("<h3 style='color:red; text-align:center; margin-top:50px;'>Lỗi: Không nhận được ID khách hàng!</h3>");
        }

        $cau_lenh = trim($_POST['cau_lenh']);
        $ngay_cuoc = $_POST['ngay_cuoc'] ?? date('Y-m-d'); 
        $old_cau_lenh = $_POST['old_cau_lenh'] ?? ''; 
        
        $stmt_kh = $pdo->prepare("SELECT * FROM khach_hang WHERE id = ?");
        $stmt_kh->execute([$khach_hang_id]);
        $khach_hang = $stmt_kh->fetch(PDO::FETCH_ASSOC);

        if (!$khach_hang) {
            die("<h3 style='color:red; text-align:center; margin-top:50px;'>Lỗi: Không tìm thấy khách hàng!</h3>");
        }

        $mang_chi_tiet = parseTinNhan($cau_lenh, $ngay_cuoc);

        if (isset($mang_chi_tiet['error'])) {
            echo "<script>alert('" . strip_tags($mang_chi_tiet['error']) . "'); window.history.back();</script>";
            exit;
        }
        
        if ($old_cau_lenh !== '') {
            $pdo->prepare("DELETE FROM chi_tiet_cuoc WHERE khach_hang_id = ? AND ngay_cuoc = ? AND cau_lenh = ?")->execute([$khach_hang_id, $ngay_cuoc, $old_cau_lenh]);
        }

        $stmt = $pdo->prepare("INSERT INTO chi_tiet_cuoc (khach_hang_id, ngay_cuoc, dai, con_so, loai_cuoc, so_tien, tien_xac, tien_qua_co, tien_trung, cau_lenh) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($mang_chi_tiet as $dong) {
            $calc_group = get_calc_group($dong['loai_cuoc']);
            
            $ty_le_co = 0.8; 
            if ($calc_group == 'baolo') { $ty_le_co = $khach_hang['mn_co_2_con_bao'] ?? 0.8; } 
            elseif ($calc_group == 'dau' || $calc_group == 'dd') { $ty_le_co = $khach_hang['mn_co_2_con_dau_duoi'] ?? 0.83; } 
            elseif ($calc_group == 'xc') { $ty_le_co = $khach_hang['mn_co_3_con_xc'] ?? 0.78; }

            $xac = 0;
            if ($calc_group == 'dau') { $xac = $dong['so_tien']; }
            elseif ($calc_group == 'dd') { $xac = $dong['so_tien'] * 2; }
            elseif ($calc_group == 'baolo') { $xac = $dong['so_tien'] * 18; }
            elseif ($calc_group == 'xc') { $xac = $dong['so_tien'] * 2; }
            
            $qua_co = round($xac * $ty_le_co, 2);
            
            $is_trung = kiemTraTrung($pdo, $ngay_cuoc, $dong['dai'], $dong['so'], $dong['loai_cuoc']);
            $tien_trung_hien_thi = ($is_trung == 1) ? $dong['so_tien'] : 0;
            
            $stmt->execute([
                $khach_hang_id, $ngay_cuoc, $dong['dai'], $dong['so'], $dong['loai_cuoc'], $dong['so_tien'], 
                $xac, $qua_co, $tien_trung_hien_thi, $cau_lenh
            ]);
        }
        
        header("Location: xem_tin_nhan.php?id=" . $khach_hang_id . "&ngay=" . $ngay_cuoc);
        exit;
    } catch (PDOException $e) {
        die("Lỗi Database: " . $e->getMessage()); 
    }
} else {
    // PHẦN PREVIEW TRẢ CÚ PHÁP
    if (isset($_GET['action']) && $_GET['action'] == 'preview' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $khach_hang_id = $_POST['khach_hang_id'] ?? 0;
        $cau_lenh = trim($_POST['cau_lenh']);
        $ngay_cuoc = $_POST['ngay_cuoc'] ?? date('Y-m-d'); 
        
        $stmt_kh = $pdo->prepare("SELECT * FROM khach_hang WHERE id = ?");
        $stmt_kh->execute([$khach_hang_id]);
        $khach_hang = $stmt_kh->fetch(PDO::FETCH_ASSOC);

        $mang_chi_tiet = parseTinNhan($cau_lenh, $ngay_cuoc);
        
        if (isset($mang_chi_tiet['error'])) {
            echo "<tr><td colspan='6' style='color: #d33; font-weight: bold; padding: 10px;'>" . $mang_chi_tiet['error'] . "</td></tr>";
            exit;
        }
        
        foreach ($mang_chi_tiet as $dong) {
            $calc_group = get_calc_group($dong['loai_cuoc']);
            
            $ty_le_co = 0.8; 
            if ($calc_group == 'baolo') { $ty_le_co = $khach_hang['mn_co_2_con_bao'] ?? 0.8; } 
            elseif ($calc_group == 'dau' || $calc_group == 'dd') { $ty_le_co = $khach_hang['mn_co_2_con_dau_duoi'] ?? 0.83; } 
            elseif ($calc_group == 'xc') { $ty_le_co = $khach_hang['mn_co_3_con_xc'] ?? 0.78; }

            $xac = 0;
            if ($calc_group == 'dau') { $xac = $dong['so_tien']; }
            elseif ($calc_group == 'dd') { $xac = $dong['so_tien'] * 2; }
            elseif ($calc_group == 'baolo') { $xac = $dong['so_tien'] * 18; }
            elseif ($calc_group == 'xc') { $xac = $dong['so_tien'] * 2; }
            
            $qua_co = round($xac * $ty_le_co, 2);

            echo "<tr>
                <td>".htmlspecialchars($dong['dai'])."</td>
                <td>".htmlspecialchars($dong['so'])."</td>
                <td>".htmlspecialchars($dong['loai_cuoc'])."</td>
                <td>".htmlspecialchars($dong['so_tien'])."</td>
                <td>".htmlspecialchars($xac)."</td>
                <td>".number_format($qua_co, 2)."</td>
            </tr>";
        }
        exit; 
    }
    echo "Không có dữ liệu gửi lên.";
}
?>