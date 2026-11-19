<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db.php';

// Copy y hệt 4 hàm này từ file xu_ly_tin_nhan.php sang
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

function get_calc_group($loai) {
    if (in_array($loai, ['baolo', 'baodao', 'baylo', 'baylodao', 'tamlo'])) return 'baolo';
    if (in_array($loai, ['dau', 'daudao'])) return 'dau';
    if (in_array($loai, ['dd', 'dddao', 'duoi', 'duoidao', 'da', 'dax'])) return 'dd';
    if (in_array($loai, ['xc', 'xcdao', 'xcdau', 'xcdaudao', 'xcduoi', 'xcduoidao'])) return 'xc';
    return $loai;
}

function get_ty_le($val, $default) {
    if (!isset($val) || trim($val) === '' || !is_numeric(trim($val))) {
        return (float)$default;
    }
    return (float)trim($val);
}

function parseTinNhan($chuoi, $ngay_chon) {
    $chuoi = mb_strtolower(trim($chuoi));
    $chuoi = preg_replace('/^\d+\)\s*/', '', $chuoi); 
    
    // BẢO VỆ TỪ KHÓA 3DAI VÀ 3D ĐỂ KHÔNG BỊ TÁCH NHẦM
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

        // Xử lý "3dai" (đã được đổi thành ba_dai)
        if ($tu === 'ba_dai') {
            $dang_chon_dai = $danh_sach_dai_hien_tai;
            continue;
        }

        // Nhận diện số
        if (is_numeric($tu) && strlen($tu) <= 3) {
            $cac_con_so[] = str_pad($tu, 2, '0', STR_PAD_LEFT);
        } 
        // Nhận diện tên đài (nếu có)
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
            
            // CHỈ LẤY SỐ VỪA NHẬP, KHÔNG DÙNG BIẾN LƯU TRỮ SỐ CŨ GÂY LỖI
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
    
    $ty_co_baolo = get_ty_le($khach_hang['mn_co_2_con_bao'] ?? '', 0.8);
    $ty_co_dd = get_ty_le($khach_hang['mn_co_2_con_dau_duoi'] ?? '', 0.83);
    $ty_co_xc = get_ty_le($khach_hang['mn_co_3_con_xc'] ?? '', 0.78);

    foreach ($mang_chi_tiet as $dong) {
        $calc_group = get_calc_group($dong['loai_cuoc']);
        
        $ty_le_co = $ty_co_baolo; 
        if ($calc_group == 'dau' || $calc_group == 'dd') { $ty_le_co = $ty_co_dd; } 
        elseif ($calc_group == 'xc') { $ty_le_co = $ty_co_xc; }

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

$khach_hang_id = $_GET['id'] ?? 0;
$ngay = $_GET['ngay'] ?? date('Y-m-d');

$stmt = $pdo->prepare("SELECT * FROM khach_hang WHERE id = ?");
$stmt->execute([$khach_hang_id]);
$kh = $stmt->fetch();
if (!$kh) { die("Không tìm thấy khách hàng"); }

$stmt_cuoc = $pdo->prepare("SELECT * FROM chi_tiet_cuoc WHERE khach_hang_id = ? AND ngay_cuoc = ? ORDER BY id ASC");
$stmt_cuoc->execute([$khach_hang_id, $ngay]);
$chi_tiets = $stmt_cuoc->fetchAll(PDO::FETCH_ASSOC);

$grouped_chi_tiets = [];
foreach ($chi_tiets as $row) {
    $key = $row['cau_lenh']; 
    if (!isset($grouped_chi_tiets[$key])) {
        $grouped_chi_tiets[$key] = [
            'cau_lenh' => $row['cau_lenh'], 'items' => [],
            'loai_xac' => ['baolo' => 0, 'dau' => 0, 'dd' => 0, 'xc' => 0],
            'loai_qua_co' => ['baolo' => 0, 'dau' => 0, 'dd' => 0, 'xc' => 0],
            'loai_trung' => ['baolo' => 0, 'dau' => 0, 'dd' => 0, 'xc' => 0],
            'tong_xac' => 0, 'tong_qua_co' => 0, 'tong_trung' => 0, 'created_at' => $row['created_at']
        ];
    }
    $calc_group = get_calc_group($row['loai_cuoc']);
    
    $grouped_chi_tiets[$key]['tong_xac'] += $row['tien_xac'];
    $grouped_chi_tiets[$key]['tong_trung'] += $row['tien_trung'];
    
    $grouped_chi_tiets[$key]['loai_xac'][$calc_group] += $row['tien_xac'];
    $grouped_chi_tiets[$key]['loai_trung'][$calc_group] += $row['tien_trung'];
}

// ĐỌC TỶ LỆ TỪ DATABASE
$ty_le_co_baolo = get_ty_le($kh['mn_co_2_con_bao'] ?? '', 0.8);
$ty_le_co_dd = get_ty_le($kh['mn_co_2_con_dau_duoi'] ?? '', 0.83);
$ty_le_co_xc = get_ty_le($kh['mn_co_3_con_xc'] ?? '', 0.78);

$tong_qua_co = 0; $tong_thang_thuc_te = 0; 
$loai_xac = ['2CB' => 0, '2CD' => 0, '3XC' => 0];
$loai_qua_co = ['2CB' => 0, '2CD' => 0, '3XC' => 0];
$loai_trung = ['2CB' => 0, '2CD' => 0, '3XC' => 0];

$ty_le_trung_baolo = get_ty_le($kh['mn_trung_2_con_bao'] ?? '', 80);
$ty_le_trung_dd = get_ty_le($kh['mn_trung_2_con_dau_duoi'] ?? '', 80);
$ty_le_trung_xc = get_ty_le($kh['mn_trung_3_con_xc'] ?? '', 650);

foreach ($grouped_chi_tiets as $key => $group) {
    // TÍNH TIỀN CÒ DỰA TRÊN TỔNG XÁC CỦA NHÓM
    $tien_co_baolo = $group['loai_xac']['baolo'] * $ty_le_co_baolo;
    $tien_co_dd = ($group['loai_xac']['dau'] + $group['loai_xac']['dd']) * $ty_le_co_dd;
    $tien_co_xc = $group['loai_xac']['xc'] * $ty_le_co_xc;
    
    $grouped_chi_tiets[$key]['tong_qua_co'] = round($tien_co_baolo + $tien_co_dd + $tien_co_xc, 2);
    $grouped_chi_tiets[$key]['loai_qua_co'] = [
        'baolo' => round($tien_co_baolo, 2),
        'dau' => round($group['loai_xac']['dau'] * $ty_le_co_dd, 2),
        'dd' => round($group['loai_xac']['dd'] * $ty_le_co_dd, 2),
        'xc' => round($tien_co_xc, 2)
    ];

    $thang_baolo = $group['loai_trung']['baolo'] * $ty_le_trung_baolo;
    $thang_dd = ($group['loai_trung']['dau'] + $group['loai_trung']['dd']) * $ty_le_trung_dd;
    $thang_xc = $group['loai_trung']['xc'] * $ty_le_trung_xc;

    $tong_thang_thuc_te += ($thang_baolo + $thang_dd + $thang_xc);
    $tong_qua_co += $grouped_chi_tiets[$key]['tong_qua_co'];

    $loai_xac['2CB'] += $group['loai_xac']['baolo'];
    $loai_xac['2CD'] += ($group['loai_xac']['dau'] + $group['loai_xac']['dd']);
    $loai_xac['3XC'] += $group['loai_xac']['xc'];
    
    $loai_qua_co['2CB'] += $grouped_chi_tiets[$key]['loai_qua_co']['baolo'];
    $loai_qua_co['2CD'] += ($grouped_chi_tiets[$key]['loai_qua_co']['dau'] + $grouped_chi_tiets[$key]['loai_qua_co']['dd']);
    $loai_qua_co['3XC'] += $grouped_chi_tiets[$key]['loai_qua_co']['xc'];
    
    $loai_trung['2CB'] += $group['loai_trung']['baolo'];
    $loai_trung['2CD'] += ($group['loai_trung']['dau'] + $group['loai_trung']['dd']);
    $loai_trung['3XC'] += $group['loai_trung']['xc'];
}

$thu_cuoi_cung = $tong_qua_co - $tong_thang_thuc_te;
$label_thu = ($thu_cuoi_cung < 0) ? 'Bù:' : 'Thu:';
$thu_hien_thi = abs($thu_cuoi_cung); 

$is_edit = isset($_GET['edit_cau_lenh']) && $_GET['edit_cau_lenh'] !== '';
$edit_value = $is_edit ? $_GET['edit_cau_lenh'] : '';
$show_form = (isset($_GET['nhap_moi']) && $_GET['nhap_moi'] == 1) || $is_edit; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no"> <!-- Tắt gạch chân số trên mobile -->
    <title>Xử lý TN - <?= htmlspecialchars($kh['ten']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #fff; padding-bottom: 80px; }
        
        .header { background-color: #e83e6d; color: white; display: flex; align-items: center; padding: 15px; position: sticky; top: 0; z-index: 100; font-size: 15px; justify-content: space-between;}
        .header-left { display: flex; align-items: center; }
        .header-left a { color: white; text-decoration: none; font-size: 20px; margin-right: 15px; }
        
        .date-picker-wrapper { 
            position: relative; display: flex; align-items: center; gap: 5px; 
            background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 5px;
            cursor: pointer; 
        }
        .date-picker-wrapper input[type="date"] { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; left: 0; top: 0;}
        .date-picker-wrapper span { font-size: 14px; font-weight: bold;}

        .customer-title { background: #f0f0f0; text-align: center; padding: 10px; font-weight: bold; font-size: 18px; color: #333; }

        .toolbar { background: #f0f0f0; padding: 8px 15px; display: flex; gap: 10px; align-items: center; border-bottom: 1px solid #ddd;}
        .btn-action { background: #e83e6d; color: white; border: none; padding: 8px 15px; border-radius: 4px; font-size: 14px; font-weight: bold; cursor: pointer;}
        .btn-delete { background: #ff5722; margin-left: auto; }
        .form-container { padding: 0; border-bottom: 1px solid #ddd; }
        .form-container textarea { width: 100%; height: 150px; padding: 15px; border: none; font-size: 18px; resize: none; outline: none; }
        .btn-save-data { background: #e83e6d; color: white; border: none; padding: 8px 15px; border-radius: 4px; font-size: 14px; font-weight: bold; cursor: pointer; display: none;}
        .parsed-box { padding: 10px 15px; border-bottom: 1px solid #ddd; display: none; gap: 5px; flex-wrap: wrap; }
        .parsed-item { background: #337ab7; color: white; padding: 4px 10px; border-radius: 4px; font-size: 16px; }

        .preview-table { width: 100%; border-collapse: collapse; margin-top: 10px;}
        .preview-table th, .preview-table td { border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 14px;}
        .preview-table th { background: #f9f9f9; }

        .summary-columns { display: flex; padding: 15px; gap: 10px; font-size: 16px; font-weight: bold; color: #555; border-bottom: 1px dashed #ddd; }
        .col { flex: 1; }
        .col-title { border-bottom: 1px solid #333; margin-bottom: 10px; padding-bottom: 5px; }
        .line-item { display: flex; justify-content: space-between; margin-bottom: 5px; font-weight: normal;}
        .line-item span.value { color: #e83e6d; font-weight: bold; }
        .line-item span.value-zero { color: #e83e6d; font-weight: normal; }

        .total-thu { color: #e83e6d; font-size: 16px; font-weight: bold; padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;}
        
        .message-item { padding: 15px; border-bottom: 1px solid #eee; }
        .message-text { font-size: 16px; color: #333; margin-bottom: 10px; word-break: break-word; }
        .message-text span.stt { font-weight: bold; color: #e83e6d; margin-right: 5px;}
        
        .sub-summary { display: flex; gap: 20px; font-size: 15px; margin-top: 10px;}
        .sub-col { flex: 1;}
        .sub-line { display: flex; justify-content: space-between; margin-bottom: 5px;}
        .sub-line span.val { color: #e83e6d; }
        .time-note { font-size: 13px; color: #888; display: flex; align-items: center; gap: 5px; margin-top: 10px;}
        .btn-edit { background: #e83e6d; color: white; border-radius: 50%; width: 35px; height: 35px; display: flex; justify-content: center; align-items: center; text-decoration: none; margin-left: auto;}

        .fab-add {
            position: fixed; bottom: 20px; right: 20px; width: 60px; height: 60px;
            background-color: #e83e6d; color: white; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 26px; box-shadow: 0 4px 10px rgba(232, 62, 109, 0.4);
            text-decoration: none; z-index: 999;
        }
        
        .empty-state { text-align: center; padding: 50px 20px; color: #888; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="xu_ly_mien_nam.php"><i class="fa-solid fa-arrow-left"></i></a>
            <div style="font-size: 16px; display: flex; gap: 5px;">
                <span>MIỀN NAM:</span>
                <span><?= htmlspecialchars($kh['ten']) ?></span>
            </div>
        </div>
        
        <div class="date-picker-wrapper">
            <i class="fa-regular fa-calendar-days"></i>
            <span><?= date('d/m/Y', strtotime($ngay)) ?></span>
            <input type="date" value="<?= $ngay ?>" onchange="window.location.href='xem_tin_nhan.php?id=<?= $khach_hang_id ?>&ngay=' + this.value;">
        </div>
    </div>

    <div class="customer-title"><?= htmlspecialchars($kh['ten']) ?></div>

    <!-- FORM XÓA ẨN -->
    <form id="form_xoa" method="POST" action="xu_ly_tin_nhan.php" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="khach_hang_id" value="<?= $khach_hang_id ?>">
        <input type="hidden" name="ngay_cuoc" value="<?= $ngay ?>">
        <input type="hidden" name="cau_lenh_can_xoa" id="input_cau_lenh_xoa" value="">
    </form>

    <?php if ($show_form): ?>
    <form method="POST" action="xu_ly_tin_nhan.php" id="main_form">
        <input type="hidden" name="khach_hang_id" value="<?= $khach_hang_id ?>">
        <input type="hidden" name="ngay_cuoc" id="input_ngay_cuoc" value="<?= $ngay ?>"> 
        <input type="hidden" name="old_cau_lenh" value="<?= htmlspecialchars($edit_value) ?>">
        
        <div class="toolbar">
            <button type="button" class="btn-action">LỌC KÝ TỰ</button>
            <button type="button" class="btn-action" onclick="parseSyntax()">TRA CÚ PHÁP</button>
            <button type="submit" class="btn-save-data" id="btn-luu">LƯU DỮ LIỆU</button>
            <?php if($is_edit): ?>
                <button type="button" class="btn-action btn-delete" onclick="xacNhanXoa('<?= addslashes($edit_value) ?>')">XÓA</button>
            <?php else: ?>
                <button type="button" class="btn-action btn-delete">XÓA</button>
            <?php endif; ?>
        </div>
        <div class="form-container">
            <textarea name="cau_lenh" id="input_text" placeholder="Nhập tin nhắn..."><?= htmlspecialchars($edit_value) ?></textarea>
        </div>
        <div class="parsed-box" id="parsed-box"></div>
        <div id="preview-area" style="display: none; padding: 0 15px; margin-top: 15px;"></div>
        
    </form>
    <?php endif; ?>

    <!-- NẾU KHÔNG CÓ TIN NHẮN VÀ KHÔNG MỞ FORM THÌ HIỂN THỊ THÔNG BÁO TRỐNG -->
    <?php if (empty($chi_tiets) && !$show_form): ?>
        <div class="empty-state">
            Ngày <?= date('d/m/Y', strtotime($ngay)) ?> chưa có tin.
        </div>
    <?php endif; ?>

    <!-- CHỈ HIỂN THỊ BẢNG TÍNH TOÁN KHI CÓ DỮ LIỆU -->
    <?php if (!empty($chi_tiets) || $show_form): ?>
        <div class="summary-columns">
            <div class="col">
                <div class="col-title">XÁC</div>
                <div class="line-item"><span>2CB:</span> <span class="<?= $loai_xac['2CB'] > 0 ? 'value' : 'value-zero' ?>"><?= $loai_xac['2CB'] ?></span></div>
                <div class="line-item"><span>2CD:</span> <span class="<?= $loai_xac['2CD'] > 0 ? 'value' : 'value-zero' ?>"><?= $loai_xac['2CD'] ?></span></div>
                <div class="line-item"><span>DaT:</span> <span class="value-zero">0</span></div>
                <div class="line-item"><span>DaX:</span> <span class="value-zero">0</span></div>
                <div class="line-item"><span>3C:</span> <span class="value-zero">0</span></div>
                <div class="line-item"><span>3XC:</span> <span class="<?= $loai_xac['3XC'] > 0 ? 'value' : 'value-zero' ?>"><?= $loai_xac['3XC'] ?></span></div>
                <div class="line-item"><span>4C:</span> <span class="value-zero">0</span></div>
            </div>
            <div class="col">
                <div class="col-title">QUA CÒ</div>
                <div class="line-item"><span>2CB:</span> <span class="<?= $loai_qua_co['2CB'] > 0 ? 'value' : 'value-zero' ?>"><?= number_format($loai_qua_co['2CB'], 2) ?></span></div>
                <div class="line-item"><span>2CD:</span> <span class="<?= $loai_qua_co['2CD'] > 0 ? 'value' : 'value-zero' ?>"><?= number_format($loai_qua_co['2CD'], 2) ?></span></div>
                <div class="line-item"><span>DaT:</span> <span class="value-zero">0</span></div>
                <div class="line-item"><span>DaX:</span> <span class="value-zero">0</span></div>
                <div class="line-item"><span>3C:</span> <span class="value-zero">0</span></div>
                <div class="line-item"><span>3XC:</span> <span class="<?= $loai_qua_co['3XC'] > 0 ? 'value' : 'value-zero' ?>"><?= number_format($loai_qua_co['3XC'], 2) ?></span></div>
                <div class="line-item"><span>4C:</span> <span class="value-zero">0</span></div>
            </div>
            <div class="col">
                <div class="col-title">TRÚNG</div>
                <div class="line-item"><span>2CB:</span> <span class="<?= $loai_trung['2CB'] > 0 ? 'value' : 'value-zero' ?>"><?= number_format($loai_trung['2CB'], 2) ?></span></div>
                <div class="line-item"><span>2CD:</span> <span class="<?= $loai_trung['2CD'] > 0 ? 'value' : 'value-zero' ?>"><?= number_format($loai_trung['2CD'], 2) ?></span></div>
                <div class="line-item"><span>DaT:</span> <span class="value-zero">0</span></div>
                <div class="line-item"><span>DaX:</span> <span class="value-zero">0</span></div>
                <div class="line-item"><span>3C:</span> <span class="value-zero">0</span></div>
                <div class="line-item"><span>3XC:</span> <span class="<?= $loai_trung['3XC'] > 0 ? 'value' : 'value-zero' ?>"><?= number_format($loai_trung['3XC'], 2) ?></span></div>
                <div class="line-item"><span>4C:</span> <span class="value-zero">0</span></div>
            </div>
        </div>

        <div class="total-thu">
            <div><?= $label_thu ?> <?= number_format($thu_hien_thi, 2) ?>*100% = <?= number_format($thu_hien_thi, 2) ?></div>
            <i class="fa-solid fa-calculator"></i>
        </div>

        <?php $stt = 1; foreach ($grouped_chi_tiets as $group): ?>
            <div class="message-item">
                <div class="message-text">
                    <span class="stt"><?= $stt++ ?>)</span> <?= htmlspecialchars($group['cau_lenh']) ?>
                </div>

                <div class="sub-summary">
                    <div class="sub-col">
                        <div class="sub-line"><span>2CB:</span> <span class="val"><?= $group['loai_xac']['baolo'] ?></span></div>
                        <div class="sub-line"><span>2CD:</span> <span class="val"><?= ($group['loai_xac']['dau'] + $group['loai_xac']['dd']) ?></span></div>
                        <div class="sub-line"><span>DaT:</span> <span class="val">0</span></div>
                        <div class="sub-line"><span>DaX:</span> <span class="val">0</span></div>
                        <div class="sub-line"><span>3C:</span> <span class="val">0</span></div>
                        <div class="sub-line"><span>3XC:</span> <span class="val"><?= $group['loai_xac']['xc'] ?></span></div>
                        <div class="sub-line"><span>4C:</span> <span class="val">0</span></div>
                    </div>
                    <div class="sub-col" style="text-align: left;">
                        <div class="sub-line"><span>2CB:</span> <span class="val"><?= number_format($group['loai_trung']['baolo']) ?></span></div>
                        <div class="sub-line"><span>2CD:</span> <span class="val"><?= number_format($group['loai_trung']['dau'] + $group['loai_trung']['dd']) ?></span></div>
                        <div class="sub-line"><span>DaT:</span> <span class="val">0</span></div>
                        <div class="sub-line"><span>DaX:</span> <span class="val">0</span></div>
                        <div class="sub-line"><span>3C:</span> <span class="val">0</span></div>
                        <div class="sub-line"><span>3XC:</span> <span class="val"><?= number_format($group['loai_trung']['xc']) ?></span></div>
                        <div class="sub-line"><span>4C:</span> <span class="val">0</span></div>
                    </div>
                </div>

                <div class="time-note">
                    <i class="fa-solid fa-chevron-down"></i> Ngày dò: <?= date('H:i d-m', strtotime($group['created_at'])) ?>
                    <a href="xem_tin_nhan.php?id=<?= $khach_hang_id ?>&ngay=<?= $ngay ?>&edit_cau_lenh=<?= urlencode($group['cau_lenh']) ?>" class="btn-edit">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!$show_form): ?>
    <a href="xem_tin_nhan.php?id=<?= $khach_hang_id ?>&nhap_moi=1&ngay=<?= $ngay ?>" class="fab-add">
        <i class="fa-regular fa-comment-dots"></i>
    </a>
    <?php endif; ?>

    <script>
        function xacNhanXoa(cau_lenh) {
            if (confirm('Bạn có chắc chắn muốn xóa câu lệnh này không?')) {
                document.getElementById('input_cau_lenh_xoa').value = cau_lenh;
                document.getElementById('form_xoa').submit();
            }
        }

        function parseSyntax() {
            var inputText = document.getElementById("input_text").value.trim();
            var parsedBox = document.getElementById("parsed-box");
            var btnLuu = document.getElementById("btn-luu");
            
            parsedBox.innerHTML = '';
            if(inputText === '') { alert('Vui lòng nhập dữ liệu!'); return; }

            var words = inputText.split(/\s+/);
            words.forEach(function(word) {
                var span = document.createElement("span");
                span.className = "parsed-item";
                span.innerText = word;
                parsedBox.appendChild(span);
            });
            parsedBox.style.display = "flex";

            var formData = new FormData();
            formData.append('cau_lenh', inputText);
            formData.append('khach_hang_id', <?= $khach_hang_id ?>);
            formData.append('ngay_cuoc', document.getElementById('input_ngay_cuoc').value);

            fetch('xem_tin_nhan.php?action=preview', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                if (html.trim() !== '') {
                    var previewDiv = document.getElementById('preview-area');
                    previewDiv.innerHTML = `
                        <h3>Chi tiết</h3>
                        <table class="preview-table">
                            <thead>
                                <tr><th>Đài</th><th>Số</th><th>C.Chơi</th><th>S.Tiền</th><th>Xác</th><th>Q.Cò</th></tr>
                            </thead>
                            <tbody id="preview-tbody">${html}</tbody>
                        </table>
                    `;
                    previewDiv.style.display = 'block';
                    
                    if (html.includes('Lỗi:')) {
                        btnLuu.style.display = "none";
                    } else {
                        btnLuu.style.display = "inline-block";
                    }
                } else {
                    alert('Không nhận diện được cú pháp. Vui lòng kiểm tra lại!');
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Có lỗi xảy ra khi tra cú pháp.');
            });
        }
    </script>
</body>
</html>