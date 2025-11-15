<?php
/**
 * helpers.php
 * Chứa các hàm hỗ trợ chung
 */

function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect($path) {
    header("Location: " . $path);
    exit;
}

function display_session_message($type = 'success') {
    $session_key = $type . '_message';
    
    if (isset($_SESSION[$session_key])) {
        $color_class = ($type == 'success') ? 'success' : 'danger';
        $message = $_SESSION[$session_key];
        
        // Sử dụng alert Bootstrap
        echo '<div class="alert alert-' . $color_class . ' my-3">' . htmlspecialchars($message) . '</div>';
        
        unset($_SESSION[$session_key]);
    }
}

function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}
?>