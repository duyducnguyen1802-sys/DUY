<?php
session_start(); 
require_once 'helpers.php';
require_once 'db_connect.php'; 

if (!is_user_logged_in()) {
    redirect('login.php');
}

$user_id = get_current_user_id();
$error = '';
$categories = [];

// --- 1. LẤY TẤT CẢ DANH MỤC CỦA NGƯỜI DÙNG ---
try {
    $sql_cat = "SELECT id, name FROM categories WHERE user_id = ? ORDER BY name ASC";
    $stmt_cat = $pdo->prepare($sql_cat);
    $stmt_cat->execute([$user_id]);
    $categories = $stmt_cat->fetchAll();
} catch (PDOException $e) {
    $error = "Lỗi khi tải danh mục: " . $e->getMessage();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {
    
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = trim($_POST['due_date']);
    $selected_categories = $_POST['categories'] ?? []; 

    if (empty($title)) {
        $error = "Tiêu đề công việc không được để trống.";
    } 
    
    if (empty($error)) {
        
        $pdo->beginTransaction();
        
        try {
            // Bước 1: Thêm công việc vào bảng tasks
            $sql_task = "INSERT INTO tasks (user_id, title, description, due_date) 
                         VALUES (?, ?, ?, ?)";
            $stmt_task = $pdo->prepare($sql_task);
            $stmt_task->execute([$user_id, $title, $description, $due_date]);
            
            $task_id = $pdo->lastInsertId();

            // Bước 2: Thêm liên kết danh mục vào bảng task_categories
            if (!empty($selected_categories)) {
                $sql_link = "INSERT INTO task_categories (task_id, category_id) VALUES (?, ?)";
                $stmt_link = $pdo->prepare($sql_link);

                foreach ($selected_categories as $category_id) {
                    if (is_numeric($category_id)) {
                        $stmt_link->execute([$task_id, $category_id]);
                    }
                }
            }
            
            $pdo->commit();
            
            $_SESSION['success_message'] = "Công việc mới đã được thêm thành công!";
            redirect('dashboard.php');

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Lỗi khi thêm công việc: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Công Việc Mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css"> </head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-clipboard-list me-2"></i> Ứng Dụng Quản Lý Công Việc</a>
            <span class="navbar-text ms-auto text-white">Chào mừng, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4 text-primary"><i class="fas fa-plus-circle me-2"></i> Thêm Công Việc Mới</h1>
        <p><a href="dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i> Quay lại Dashboard</a></p>

        <?php if (!empty($error)): echo '<div class="alert alert-danger mt-3">'. $error .'</div>'; endif; ?>

        <div class="card p-4 shadow-sm">
            <form action="create_task.php" method="POST">
                
                <div class="mb-3">
                    <label for="title" class="form-label"><i class="fas fa-heading me-2"></i> Tiêu đề công việc:</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label"><i class="fas fa-align-left me-2"></i> Mô tả chi tiết (Tùy chọn):</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="due_date" class="form-label"><i class="fas fa-calendar-alt me-2"></i> Ngày hết hạn (Tùy chọn):</label>
                    <input type="date" class="form-control" id="due_date" name="due_date">
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-tags me-2"></i> Chọn Danh Mục (Tùy chọn):</label>
                    <?php if (empty($categories)): ?>
                        <div class="alert alert-warning p-2">
                            Bạn chưa có danh mục nào. <a href="manage_categories.php">Tạo mới ngay!</a>
                        </div>
                    <?php else: ?>
                        <div class="border p-3 rounded bg-light" style="max-height: 150px; overflow-y: auto;">
                            <?php foreach ($categories as $cat): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="<?php echo $cat['id']; ?>" id="cat_<?php echo $cat['id']; ?>">
                                    <label class="form-check-label" for="cat_<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save me-2"></i> Tạo Công Việc</button>
            </form>
        </div>
    </div>

    <footer class="footer mt-5">
        <div class="container">
            <span>© <?php echo date("Y"); ?> Ứng Dụng Quản Lý Công Việc.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>