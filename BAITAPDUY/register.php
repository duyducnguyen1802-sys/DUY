<?php
session_start(); 
require_once 'db_connect.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']); 

    if (empty($username) || empty($password)) {
        $error = "Tên đăng nhập và Mật khẩu không được để trống.";
    } 
    
    if (empty($error)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $hashed_password, $email]);
            
            $_SESSION['success_message'] = 'Đăng ký tài khoản thành công! Vui lòng Đăng nhập.';
            header("Location: login.php");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                $error = "Tên đăng nhập hoặc Email này đã được sử dụng.";
            } else {
                $error = "Đã xảy ra lỗi hệ thống: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css"> </head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card login-register-card shadow-lg">
        <h2 class="card-title text-center mb-4"><i class="fas fa-user-plus me-2"></i> Đăng Ký</h2>

        <?php if (!empty($error)): echo '<div class="alert alert-danger mt-3">'. $error .'</div>'; endif; ?>

        <form action="register.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label"><i class="fas fa-user me-2"></i> Tên người dùng:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><i class="fas fa-lock me-2"></i> Mật khẩu:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i> Email (Tùy chọn):</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            <button type="submit" class="btn btn-success w-100 mt-3"><i class="fas fa-user-plus me-2"></i> Đăng Ký</button>
        </form>
        
        <p class="text-center mt-3"><a href="simple_reset.php" class="small">Quên mật khẩu?</a></p>
        <p class="text-center mt-3">Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>