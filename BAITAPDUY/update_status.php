<?php
session_start(); 
require_once 'helpers.php';
require_once 'db_connect.php'; 

if (!is_user_logged_in()) {
    redirect('login.php');
}

$user_id = get_current_user_id();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Yêu cầu cập nhật trạng thái không hợp lệ.";
    redirect('dashboard.php');
}

$task_id = $_GET['id'];
$new_status = $_GET['status'] ?? 'pending'; 

$valid_statuses = ['pending', 'in_progress', 'completed'];
if (!in_array($new_status, $valid_statuses)) {
    $_SESSION['error_message'] = "Trạng thái công việc không hợp lệ.";
    redirect('dashboard.php');
}

$sql = "UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_status, $task_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Trạng thái đã được cập nhật thành: " . ucfirst(str_replace('_', ' ', $new_status));
    } else {
        $_SESSION['error_message'] = "Không thể cập nhật trạng thái.";
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Lỗi khi cập nhật trạng thái: " . $e->getMessage();
}

redirect('dashboard.php');
?>