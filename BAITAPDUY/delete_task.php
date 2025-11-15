<?php
session_start(); 
require_once 'helpers.php';
require_once 'db_connect.php'; 

if (!is_user_logged_in()) {
    redirect('login.php');
}

$user_id = get_current_user_id();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Yêu cầu xóa không hợp lệ.";
    redirect('dashboard.php');
}

$task_id = $_GET['id'];

// Xóa liên kết danh mục (task_categories) sẽ tự động xóa nếu FOREIGN KEY có ON DELETE CASCADE.
// Tuy nhiên, thêm DELETE FROM task_categories cho rõ ràng (Nếu không dùng CASCADE).
// Giả sử CSDL đã có CASCADE, ta chỉ cần xóa task chính.

$sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$task_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Công việc đã được xóa thành công.";
    } else {
        $_SESSION['error_message'] = "Không tìm thấy công việc để xóa hoặc bạn không có quyền xóa.";
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Lỗi khi xóa công việc: " . $e->getMessage();
}

redirect('dashboard.php');
?>