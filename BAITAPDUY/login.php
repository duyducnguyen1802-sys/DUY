<?php
session_start();
require_once 'db_connect.php'; 
require_once 'helpers.php'; 

if (is_user_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

// 🌟 SỬA LỖI: Chỉ xử lý $_POST khi form được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ Tên người dùng và Mật khẩu.";
    } else {
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                redirect('dashboard.php'); 
            } else {
                $error = "Tên người dùng hoặc Mật khẩu không chính xác.";
            }

        } catch (PDOException $e) {
            $error = "Lỗi truy vấn CSDL: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-lg" style="width: 350px;">
        <h2 class="card-title text-center mb-4"><i class="fas fa-sign-in-alt me-2"></i> Đăng Nhập</h2>

        <?php 
        display_session_message('success'); 
        if (!empty($error)): echo '<div class="alert alert-danger">'. $error .'</div>'; endif; 
        ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label"><i class="fas fa-user me-2"></i> Tên người dùng:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><i class="fas fa-lock me-2"></i> Mật khẩu:</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="text-end mt-1">
                    <a href="simple_reset.php" class="text-muted small">Quên mật khẩu?</a>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Đăng Nhập</button>
        </form>
        
        <p class="text-center mt-3">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>