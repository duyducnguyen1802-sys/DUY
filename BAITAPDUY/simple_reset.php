<?php
session_start();
require_once 'db_connect.php'; 
require_once 'helpers.php'; 

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'] ?? null;

    if (empty($username) || empty($email) || empty($new_password)) {
        $error = "Vui lòng nhập đầy đủ Tên người dùng, Email và Mật khẩu mới.";
    } elseif (strlen($new_password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        try {
            // 1. Kiểm tra username và email có khớp nhau không
            $sql_check = "SELECT id FROM users WHERE username = ? AND email = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$username, $email]);
            $user = $stmt_check->fetch();

            if ($user) {
                // 2. Hash mật khẩu mới và cập nhật
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update = "UPDATE users SET password = ? WHERE id = ?";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([$hashed_password, $user['id']]);

                $_SESSION['success_message'] = "Đặt lại mật khẩu thành công! Vui lòng đăng nhập.";
                redirect('login.php');

            } else {
                $error = "Tên đăng nhập và Email không khớp với bất kỳ tài khoản nào.";
            }

        } catch (PDOException $e) {
            $error = "Lỗi khi cập nhật mật khẩu: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt Lại Mật khẩu Đơn Giản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card login-register-card shadow-lg">
        <h2 class="card-title text-center mb-4"><i class="fas fa-sync-alt me-2"></i> Đặt Lại Mật khẩu</h2>

        <?php 
        if (!empty($error)): echo '<div class="alert alert-danger mt-3">'. $error .'</div>'; endif; 
        ?>

        <form action="simple_reset.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label"><i class="fas fa-user me-2"></i> Tên đăng nhập:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i> Email đăng ký:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="new_password" class="form-label"><i class="fas fa-lock me-2"></i> Mật khẩu mới:</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mt-3"><i class="fas fa-save me-2"></i> Đặt Lại Mật khẩu</button>
        </form>
        
        <p class="text-center mt-4"><a href="login.php">← Quay lại Đăng nhập</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>