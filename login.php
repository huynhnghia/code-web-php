<?php
// login.php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Đã sửa thành so sánh chuỗi thường để khớp với pass 123456
        if ($user && $password === $user['password']) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // [THÊM MỚI] Lưu ngày hết hạn vào session. 
            // Nếu trong DB không có cột này, mặc định lấy ngày 2027-02-15
            $_SESSION['ngay_het_han'] = $user['ngay_het_han'] ?? '2027-02-15'; 
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Sai tên đăng nhập hoặc mật khẩu.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <style>
        body { font-family: Arial; background: #667eea; display: flex;
               justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: #fff; padding: 30px 40px; border-radius: 12px;
               box-shadow: 0 10px 30px rgba(0,0,0,.3); width: 320px; }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 8px 0; box-sizing: border-box;
                border: 1px solid #ccc; border-radius: 6px; }
        button { width: 100%; padding: 10px; background: #667eea; color: #fff;
                 border: none; border-radius: 6px; cursor: pointer; margin-top: 10px; }
        button:hover { background: #764ba2; }
        .err { color: #d33; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Đăng nhập</h2>
        <?php if ($error): ?>
            <div class="err"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>