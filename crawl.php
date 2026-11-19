<?php
declare(strict_types=1);

/**
 * Lấy kết quả 3 miền từ RSS và lưu vào MySQL. An toàn khi chạy lặp lại
 * (đã có thì cập nhật, không tạo trùng).
 *
 * Cron (chạy PHP trực tiếp):
 *   45 16,17,18,19 * * *  /usr/bin/php /duong/dan/crawl.php >> /duong/dan/crawl.log 2>&1
 *
 * Cron kiểu gọi URL (hosting không có php CLI):
 *   curl -s "https://ten-mien/crawl.php?key=CRON_KEY"
 */

require_once __DIR__ . '/lib.php';

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
    if (!hash_equals(CRON_KEY, (string) ($_GET['key'] ?? ''))) {
        http_response_code(403);
        exit("Forbidden\n");
    }
}

foreach (crawl_all(db()) as $line) {
    echo date('c'), ' ', $line, "\n";
}
