<?php
// ====== SỬA CÁC GIÁ TRỊ NÀY ======
const DB_DSN  = 'mysql:host=sql105.infinityfree.com;dbname=if0_43459962_azir;charset=utf8mb4';
const DB_USER = 'if0_43459962';
const DB_PASS = '7UwOzLy4l0c2d0e';

// Chỉ dùng khi hosting chỉ cho "cron gọi URL" (không chạy được php CLI).
// Gọi: https://ten-mien-cua-ban/crawl.php?key=<CRON_KEY>
// Hãy đổi thành chuỗi ngẫu nhiên dài.
const CRON_KEY = '1';

// ====== NGUỒN DỮ LIỆU: RSS chính thức của xskt.com.vn ======
const FEEDS = [
    'XSMB' => 'https://xskt.com.vn/rss-feed/mien-bac-xsmb.rss',
    'XSMT' => 'https://xskt.com.vn/rss-feed/mien-trung-xsmt.rss',
    'XSMN' => 'https://xskt.com.vn/rss-feed/mien-nam-xsmn.rss',
];

date_default_timezone_set('Asia/Ho_Chi_Minh');
