<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    // Dùng kết nối có sẵn trong db.php của bạn (file đó gán biến $pdo).
    global $pdo;
    if (!($pdo instanceof PDO)) {
        require_once __DIR__ . '/db.php';
    }
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

function http_get(string $url, int $timeout = 25): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_ENCODING       => '',
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; KQXS-Reader/1.0)',
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($body === false || $code !== 200) {
        throw new RuntimeException("Không tải được $url (HTTP $code) $err");
    }
    return $body;
}

/**
 * Ngày thật của kỳ quay. Lấy từ <link> dạng .../ngay-10-11-2026
 * (pubDate trong feed MN/MT không đáng tin vì là ngày build feed).
 */
function draw_date_from_item(string $link, string $title): ?string
{
    if (preg_match('~ngay-(\d{1,2})-(\d{1,2})-(\d{4})~', $link, $m)) {
        return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
    }
    // Dự phòng: tiêu đề "... NGÀY 10/11 (Thứ Ba)" không có năm
    if (preg_match('~(\d{1,2})/(\d{1,2})~', $title, $m)) {
        $year = (int) date('Y');
        $ts   = mktime(0, 0, 0, (int) $m[2], (int) $m[1], $year);
        if ($ts > time() + 86400) {
            $year--;
        }
        return sprintf('%04d-%02d-%02d', $year, (int) $m[2], (int) $m[1]);
    }
    return null;
}

/**
 * Tách phần <description> thành [tên đài => [nhãn giải => [các số]]].
 *
 * XSMB: không có tiêu đề đài, các dòng "ĐB: ...", "1: ...", ... "7: ..."
 * XSMT/XSMN: mỗi đài bắt đầu bằng dòng "[Tên đài]", giải 7 và giải 8 trong
 *            feed bị dính nhau: "7: 8078: 82" nghĩa là G7 = 807, G8 = 82.
 */
function parse_description(string $desc, string $region): array
{
    $out     = [];
    $station = null;
    if ($region === 'XSMB') {
        $station       = 'Miền Bắc';
        $out[$station] = [];
    }

    foreach (preg_split('/\R/u', $desc) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if (preg_match('/^\[(.+)\]$/u', $line, $m)) {
            $station       = trim($m[1]);
            $out[$station] = [];
            continue;
        }
        if ($station === null || !preg_match('/^(ĐB|\d):\s*(.+)$/u', $line, $m)) {
            continue;
        }
        [$label, $value] = [$m[1], trim($m[2])];

        if ($label === '7' && preg_match('/^(\d{3})8:\s*(\d{2})$/', $value, $mm)) {
            $out[$station]['7'] = [$mm[1]];
            $out[$station]['8'] = [$mm[2]];
            continue;
        }

        $nums = array_values(array_filter(
            preg_split('/\s*-\s*/', $value),
            fn ($n) => $n !== '' && ctype_digit($n)
        ));
        if ($nums) {
            $out[$station][$label] = $nums;
        }
    }
    return $out;
}

/** @return array<int, array{region:string,date:string,station:string,prizes:array}> */
function parse_feed(string $xml, string $region): array
{
    $prev = libxml_use_internal_errors(true);
    $rss  = simplexml_load_string($xml);
    libxml_use_internal_errors($prev);

    if ($rss === false || !isset($rss->channel->item)) {
        throw new RuntimeException("RSS $region không hợp lệ");
    }

    $draws = [];
    foreach ($rss->channel->item as $item) {
        $date = draw_date_from_item((string) $item->link, (string) $item->title);
        if ($date === null) {
            continue;
        }
        foreach (parse_description((string) $item->description, $region) as $station => $prizes) {
            // Không có giải đặc biệt = kỳ quay chưa xong/không đầy đủ -> bỏ qua
            if (empty($prizes['ĐB'])) {
                continue;
            }
            $draws[] = [
                'region'  => $region,
                'date'    => $date,
                'station' => (string) $station,
                'prizes'  => $prizes,
            ];
        }
    }
    return $draws;
}

function save_draw(PDO $pdo, array $d): void
{
    $sql = 'INSERT INTO draws (region, draw_date, station, prizes) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE prizes = VALUES(prizes)';
    $pdo->prepare($sql)->execute([
        $d['region'],
        $d['date'],
        $d['station'],
        json_encode($d['prizes'], JSON_UNESCAPED_UNICODE),
    ]);
}

/** Cào cả 3 feed, lưu DB. Trả về các dòng log. */
function crawl_all(PDO $pdo, int $timeout = 25): array
{
    $log = [];
    foreach (FEEDS as $region => $url) {
        try {
            $draws = parse_feed(http_get($url, $timeout), $region);
            foreach ($draws as $d) {
                save_draw($pdo, $d);
            }
            $log[] = "$region: lưu/cập nhật " . count($draws) . ' đài-ngày';
        } catch (Throwable $e) {
            $log[] = "$region LỖI: " . $e->getMessage();
        }
    }
    return $log;
}

/** Chỉ cho 1 request "giành" được khe này trong mỗi $interval giây (chống gọi dồn). */
function claim_slot(PDO $pdo, string $key, int $interval): bool
{
    $now = time();
    $pdo->prepare('INSERT IGNORE INTO meta (k, v) VALUES (?, ?)')->execute([$key, '0']);
    $st = $pdo->prepare('UPDATE meta SET v = ? WHERE k = ? AND CAST(v AS UNSIGNED) < ?');
    $st->execute([(string) $now, $key, $now - $interval]);
    return $st->rowCount() === 1;
}

/** Thứ tự các giải khi hiển thị và khi tính lô tô: từ giải nhỏ nhất đến Đặc Biệt. */
const PRIZE_ROWS = [
    '8' => 'Giải Tám', '7' => 'Giải Bảy', '6' => 'Giải Sáu', '5' => 'Giải Năm',
    '4' => 'Giải Tư', '3' => 'Giải Ba', '2' => 'Giải Nhì', '1' => 'Giải Nhất', 'ĐB' => 'Đặc Biệt',
];

/**
 * Bảng lô tô theo 2 số cuối của mọi giải.
 *  'dau'  [đầu]  => các đuôi (theo thứ tự xuất hiện, bảng bên phải)
 *  'duoi' [đuôi] => các đầu  (bảng bên trái)
 */
function loto_tables(array $prizes): array
{
    $dau  = array_fill(0, 10, []);
    $duoi = array_fill(0, 10, []);
    foreach (array_keys(PRIZE_ROWS) as $k) {
        foreach ($prizes[$k] ?? [] as $n) {
            $n = (string) $n;
            if (strlen($n) < 2) {
                continue;
            }
            $h = (int) $n[-2];
            $t = (int) $n[-1];
            $dau[$h][]  = $t;
            $duoi[$t][] = $h;
        }
    }
    return ['dau' => $dau, 'duoi' => $duoi];
}

/** [1,6,6] -> "1,6<sup>2</sup>" (số lần lặp ghi nhỏ phía trên) */
function loto_cell_html(array $list): string
{
    $parts = [];
    foreach (array_count_values($list) as $digit => $count) {
        $parts[] = $digit . ($count > 1 ? '<sup>' . $count . '</sup>' : '');
    }
    return implode(',', $parts);
}

/**
 * Thay cho cron: gọi mỗi khi có người xem trang "hôm nay".
 * - Trước 16h30 thì chưa có kết quả mới -> bỏ qua.
 * - Hôm nay đã đủ 3 miền -> bỏ qua.
 * - Chỉ 1 lượt truy cập được đi lấy dữ liệu trong mỗi $minInterval giây
 *   (các lượt khác đọc thẳng từ DB, không phải chờ).
 */
function refresh_if_stale(PDO $pdo, int $minInterval = 600): void
{
    if ((int) date('Hi') < 1630) {
        return;
    }

    $st = $pdo->prepare('SELECT COUNT(DISTINCT region) FROM draws WHERE draw_date = ?');
    $st->execute([date('Y-m-d')]);
    if ((int) $st->fetchColumn() >= 3) {
        return;
    }

    if (!claim_slot($pdo, 'last_fetch', $minInterval)) {
        return;
    }

    try {
        crawl_all($pdo, 8);
    } catch (Throwable $e) {
        // im lặng: trang vẫn hiển thị dữ liệu đang có
    }
}

/** Kết quả của 1 ngày, đã gom theo miền. Dùng được trong bất kỳ trang PHP nào của bạn. */
function get_results(PDO $pdo, string $date): array
{
    $st = $pdo->prepare(
        "SELECT region, station, prizes FROM draws WHERE draw_date = ?
         ORDER BY FIELD(region, 'XSMB', 'XSMT', 'XSMN'), station"
    );
    $st->execute([$date]);

    $grouped = ['XSMB' => [], 'XSMT' => [], 'XSMN' => []];
    foreach ($st as $row) {
        $grouped[$row['region']][$row['station']] = json_decode($row['prizes'], true) ?: [];
    }
    return $grouped;
}
