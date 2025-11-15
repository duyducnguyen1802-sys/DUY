<?php
session_start();
require_once 'helpers.php'; 

// Xóa tất cả dữ liệu session và hủy phiên làm việc
session_unset();
session_destroy();

// Chuyển hướng người dùng về trang đăng nhập
redirect('login.php');
?>