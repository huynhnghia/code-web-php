<?php
// loai_cuoc.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Giữ nguyên mảng dữ liệu
$data_loai_cuoc = [
    'baodao' => [
        ['baodao', 'Bao đảo'], ['baolodao', 'Bao đảo'], ['bdao', 'Bao đảo'], 
        ['bldao', 'Bao đảo'], ['daob', 'Bao đảo'], ['daolo', 'Bao đảo'], 
        ['db', 'Bao đảo'], ['dbao', 'Bao đảo'], ['ldao', 'Bao đảo'], ['lodao', 'Bao đảo']
    ],
    'baolo' => [
        ['b', 'Bao lô'], ['bao', 'Bao lô'], ['baolo', 'Bao lô'], 
        ['bl', 'Bao lô'], ['blo', 'Bao lô'], ['l', 'Bao lô'], ['lo', 'Bao lô']
    ],
    'baylo' => [
        ['bay', 'Bảy lô'], ['baylo', 'Bảy lô'], ['baylobao', 'Bảy lô'], ['bbaylo', 'Bảy lô']
    ],
    'baylodao' => [
        ['baobaylodao', 'Bảy lô đảo'], ['baydao', 'Bảy lô đảo'], ['baylodao', 'Bảy lô đảo'], ['bbaylodao', 'Bảy lô đảo']
    ],
    'da' => [
        ['dat', 'Đá thẳng'], ['xienquay', 'Đá thẳng'], ['xq', 'Đá thẳng'], ['xquay', 'Đá thẳng']
    ],
    'dau' => [
        ['d', 'Đầu'], ['dau', 'Đầu']
    ],
    'daudao' => [
        ['daudao', 'Đầu đảo']
    ],
    'dax' => [
        ['da', 'Đá xiên'], ['dax', 'Đá xiên'], ['dx', 'Đá xiên'], ['xien', 'Đá xiên'], 
        ['cheo', 'Đá xiên vòng'], ['dav', 'Đá xiên vòng'], ['dv', 'Đá xiên vòng']
    ],
    'dd' => [
        ['daudui', 'Đầu đuôi'], ['dauduoi', 'Đầu đuôi'], ['dd', 'Đầu đuôi']
    ],
    'dddao' => [
        ['dauduoidao', 'Đầu đuôi đảo'], ['dddao', 'Đầu đuôi đảo']
    ],
    'duoi' => [
        ['de', 'Đuôi'], ['dit', 'Đuôi'], ['du', 'Đuôi'], ['dui', 'Đuôi'], ['duoi', 'Đuôi']
    ],
    'duoidao' => [
        ['daodui', 'Đuôi đảo'], ['duidao', 'Đuôi đảo'], ['duoidao', 'Đuôi đảo']
    ],
    'tamlo' => [ ['tamlo', 'Tám lô'] ],
    'xba' => [ ['xba', 'Xiên 3'] ],
    'xbo' => [ ['xbo', 'Xiên 4'] ],
    'xc' => [
        ['tl', 'Xỉu chủ'], ['ts', 'Xỉu chủ'], ['x', 'Xỉu chủ'], ['xc', 'Xỉu chủ'], ['xchu', 'Xỉu chủ'], 
        ['xieu', 'Xỉu chủ'], ['xiu', 'Xỉu chủ'], ['xiuchu', 'Xỉu chủ'], ['Xccap', 'Xỉu chủ cặp']
    ],
    'xcdao' => [
        ['daox', 'Xỉu chủ đảo'], ['daoxc', 'Xỉu chủ đảo'], ['dxc', 'Xỉu chủ đảo'], ['tdao', 'Xỉu chủ đảo'], 
        ['tldao', 'Xỉu chủ đảo'], ['xcd', 'Xỉu chủ đảo'], ['xcdao', 'Xỉu chủ đảo'], ['xd', 'Xỉu chủ đảo'], ['xdao', 'Xỉu chủ đảo']
    ],
    'xcdau' => [
        ['tldau', 'Xỉu chủ đầu'], ['xcdau', 'Xỉu chủ đầu'], ['xchudau', 'Xỉu chủ đầu'], 
        ['xdau', 'Xỉu chủ đầu'], ['xiuchudau', 'Xỉu chủ đầu']
    ],
    'xcdaudao' => [
        ['daotldau', 'Xỉu chủ đầu đảo'], ['daoxcdau', 'Xỉu chủ đầu đảo'], ['daoxdau', 'Xỉu chủ đầu đảo'], 
        ['tldaudao', 'Xỉu chủ đầu đảo'], ['xcdaudao', 'Xỉu chủ đầu đảo'], ['xdaudao', 'Xỉu chủ đầu đảo']
    ],
    'xcduoi' => [
        ['tldui', 'Xỉu chủ đuôi'], ['tlduoi', 'Xỉu chủ đuôi'], ['xcdui', 'Xỉu chủ đuôi'], 
        ['xcduoi', 'Xỉu chủ đuôi'], ['xdui', 'Xỉu chủ đuôi'], ['xduoi', 'Xỉu chủ đuôi']
    ],
    'xcduoidao' => [
        ['daoxcdui', 'Xỉu chủ đuôi đảo'], ['daoxcduoi', 'Xỉu chủ đuôi đảo'], ['daoxdui', 'Xỉu chủ đuôi đảo'], 
        ['daoxduoi', 'Xỉu chủ đuôi đảo'], ['tlduidao', 'Xỉu chủ đuôi đảo'], ['tlduoidao', 'Xỉu chủ đuôi đảo'], 
        ['xcduidao', 'Xỉu chủ đuôi đảo'], ['xcduoidao', 'Xỉu chủ đuôi đảo'], ['xduidao', 'Xỉu chủ đuôi đảo'], ['xduoidao', 'Xỉu chủ đuôi đảo']
    ],
    'xdao' => [ ['xiendao', 'Xiendao'] ],
    'xien' => [ ['xin', 'Xiên'] ]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loại Cược</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #fff; }

        /* Header */
        .header {
            background-color: #e83e6d; color: white; display: flex; align-items: center;
            padding: 15px; position: sticky; top: 0; z-index: 100;
        }
        .header .back-btn {
            font-size: 24px; color: white; text-decoration: none; margin-right: 15px;
            display: flex; align-items: center;
        }
        .header .title { font-size: 16px; }

        /* Table Container */
        .table-container { width: 100%; overflow-x: auto; }

        table {
            width: 100%; border-collapse: collapse;
        }

        /* Cột Tiêu đề cố định khi cuộn */
        thead th {
            position: sticky; top: 56px; /* 56px là chiều cao của header */
            background-color: #f8f9fa; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;
            padding: 10px; text-align: left; font-weight: normal; color: #333; z-index: 90;
        }

        /* Đường kẻ dọc giữa các cột */
        th, td { border-right: 1px solid #ddd; }
        th:last-child, td:last-child { border-right: none; }

        /* Nội dung các dòng */
        td { padding: 8px 10px; font-size: 16px; color: #333; border-bottom: 1px solid #eee; }
        
        /* Cột STT */
        .col-stt { width: 40px; text-align: center; }

        /* Dòng tên nhóm (baodao, baolo...) */
        .group-title-row td {
            font-weight: bold; font-size: 18px; padding: 10px; background-color: #fff;
            border-bottom: 1px solid #ddd; border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <a href="dashboard.php" class="back-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        </a>
        <div class="title">Loại cược</div>
    </div>

    <!-- NỘI DUNG BẢNG -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="col-stt">#</th>
                    <th>Key ID</th>
                    <th>Tên</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data_loai_cuoc as $group_name => $items): ?>
                    
                    <!-- Dòng hiển thị Tên Nhóm -->
                    <tr class="group-title-row">
                        <td colspan="3"><?= htmlspecialchars($group_name) ?></td>
                    </tr>

                    <!-- Các dòng dữ liệu bên trong nhóm -->
                    <?php 
                    $stt = 1; 
                    foreach ($items as $item): 
                    ?>
                        <tr>
                            <td class="col-stt"><?= $stt++ ?></td>
                            <td><?= htmlspecialchars($item[0]) ?></td>
                            <td><?= htmlspecialchars($item[1]) ?></td>
                        </tr>
                    <?php endforeach; ?>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>