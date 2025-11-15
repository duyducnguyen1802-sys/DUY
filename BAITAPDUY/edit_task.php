<?php
session_start();
require_once 'helpers.php';
require_once 'db_connect.php'; 

if (!is_user_logged_in()) {
    redirect('login.php');
}

$user_id = get_current_user_id();
$task_id = $_GET['id'] ?? null;
$error = '';
$task = null;
$all_categories = []; 
$assigned_category_ids = []; 

// --- 1. LẤY DỮ LIỆU CÔNG VIỆC HIỆN TẠI VÀ DANH MỤC LIÊN QUAN ---
if (!$task_id || !is_numeric($task_id)) {
    $_SESSION['error_message'] = "ID công việc không hợp lệ.";
    redirect('dashboard.php');
}

try {
    // Lấy chi tiết công việc
    $sql_task = "SELECT id, title, description, due_date, status 
                 FROM tasks 
                 WHERE id = ? AND user_id = ?";
    $stmt_task = $pdo->prepare($sql_task);
    $stmt_task->execute([$task_id, $user_id]);
    $task = $stmt_task->fetch();

    if (!$task) {
        $_SESSION['error_message'] = "Không tìm