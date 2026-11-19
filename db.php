<?php
// db.php — Kết nối PDO dùng chung, đọc cấu hình từ config.php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // dùng prepared statement thật
    ]);
} catch (PDOException $e) {
    // Không lộ chi tiết lỗi ra ngoài (tránh lộ user/pass/host)
    error_log('[DB] Kết nối thất bại: ' . $e->getMessage());
    http_response_code(500);
    die('Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.');
}

/**
 * Trả về đối tượng PDO dùng chung (singleton).
 * Dùng cho các file gọi kiểu: $pdo = db();
 */
function db(): PDO
{
    global $pdo;
    return $pdo;
}