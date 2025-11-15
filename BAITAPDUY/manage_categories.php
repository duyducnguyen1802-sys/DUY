<?php
session_start();
require_once 'helpers.php';
require_once 'db_connect.php'; 

if (!is_user_logged_in()) {
    redirect('login.php');
}

$user_id = get_current_user_id();
$error = '';
$current_category = ['id' => null, 'name' => '']; 

// --- XỬ LÝ FORM TẠO/SỬA ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['name']);
    $action_type = $_POST['action_type'] ?? 'create';
    $category_id = $_POST['category_id'] ?? null;

    if (empty($category_name)) {
        $error = "Tên danh mục không được để trống.";
    }
    
    if (empty($error)) {
        try {
            if ($action_type == 'create') {
                $sql = "INSERT INTO categories (user_id, name) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id, $category_name]);
                $_SESSION['success_message'] = "Danh mục đã được tạo thành công.";
            
            } elseif ($action_type == 'update' && $category_id) {
                $sql = "UPDATE categories SET name = ? WHERE id = ? AND user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$category_name, $category_id, $user_id]);
                $_SESSION['success_message'] = "Danh mục đã được cập nhật thành công.";
            }
            
            redirect('manage_categories.php');

        } catch (PDOException $e) {
            $error = "Lỗi CSDL: " . $e->getMessage();
        }
    }
}

// --- XỬ LÝ THAO TÁC XÓA VÀ LẤY DỮ LIỆU SỬA (GET) ---

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? null;

    if ($id && is_numeric($id)) {
        try {
            if ($action == 'delete') {
                $pdo->beginTransaction();
                // 1. Xóa liên kết (task_categories)
                $pdo->prepare("DELETE FROM task_categories WHERE category_id = ?")->execute([$id]);
                // 2. Xóa danh mục
                $sql_delete = "DELETE FROM categories WHERE id = ? AND user_id = ?";
                $stmt_delete = $pdo->prepare($sql_delete);
                $stmt_delete->execute([$id, $user_id]);
                $pdo->commit();

                if ($stmt_delete->rowCount() > 0) {
                    $_SESSION['success_message'] = "Danh mục đã được xóa thành công.";
                } else {
                    $_SESSION['error_message'] = "Không tìm thấy danh mục để xóa hoặc bạn không có quyền.";
                }
                redirect('manage_categories.php');

            } elseif ($action == 'edit') {
                $sql_edit = "SELECT id, name FROM categories WHERE id = ? AND user_id = ?";
                $stmt_edit = $pdo->prepare($sql_edit);
                $stmt_edit->execute([$id, $user_id]);
                $current_category = $stmt_edit->fetch();
                if (!$current_category) {
                    $_SESSION['error_message'] = "Danh mục không tồn tại.";
                    redirect('manage_categories.php');
                }
            }
        } catch (PDOException $e) {
            $error = "Lỗi khi thực hiện thao tác: " . $e->getMessage();
        }
    }
}

// --- LẤY TẤT CẢ DANH MỤC ĐỂ HIỂN THỊ (READ) ---
$categories = [];
try {
    $sql_select = "SELECT id, name FROM categories WHERE user_id = ? ORDER BY name ASC";
    $stmt_select = $pdo->prepare($sql_select);
    $stmt_select->execute([$user_id]);
    $categories = $stmt_select->fetchAll();
} catch (PDOException $e) {
    $error = "Không thể tải danh sách danh mục: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Danh Mục</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h1>Quản Lý Danh Mục</h1>
            <a href="dashboard.php" class="btn btn-secondary">← Quay lại Dashboard</a>
        </div>

        <?php 
        display_session_message('success');
        display_session_message('error');
        if (!empty($error)): echo '<div class="alert alert-danger">'. $error .'</div>'; endif; 
        ?>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <?php echo $current_category['id'] ? 'Chỉnh Sửa Danh Mục' : 'Tạo Danh Mục Mới'; ?>
            </div>
            <div class="card-body">
                <form action="manage_categories.php" method="POST">
                    <input type="hidden" name="action_type" value="<?php echo $current_category['id'] ? 'update' : 'create'; ?>">
                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($current_category['id']); ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên Danh Mục:</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($current_category['name']); ?>" required maxlength="50">
                    </div>
                    
                    <button type="submit" class="btn btn-<?php echo $current_category['id'] ? 'warning' : 'primary'; ?>">
                        <?php echo $current_category['id'] ? 'Cập Nhật' : 'Tạo Mới'; ?>
                    </button>
                    <?php if ($current_category['id']): ?>
                        <a href="manage_categories.php" class="btn btn-secondary">Hủy</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <h3 class="mb-3">Danh Sách Hiện Có (<?php echo count($categories); ?>)</h3>
        <?php if (empty($categories)): ?>
            <div class="alert alert-info">Bạn chưa có danh mục nào.</div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($categories as $cat): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($cat['name']); ?>
                        <div>
                            <a href="manage_categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-warning me-2">Sửa</a>
                            <a href="manage_categories.php?action=delete&id=<?php echo $cat['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Bạn chắc chắn muốn xóa danh mục này?');">Xóa</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>