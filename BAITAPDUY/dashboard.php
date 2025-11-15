<?php
session_start(); 
require_once 'helpers.php'; 
require_once 'db_connect.php'; 

// Kiểm tra đăng nhập
if (!is_user_logged_in()) {
    redirect('login.php');
}

$user_id = get_current_user_id();
$username = $_SESSION['username'];
$tasks = []; 
$error_db = '';

// Lấy giá trị lọc từ URL. Mặc định là 'all'
$filter_status = $_GET['status_filter'] ?? 'all'; 

// Xây dựng truy vấn SQL (ĐỌC DỮ LIỆU CÔNG VIỆC VÀ LỌC)
$sql = "SELECT id, title, description, due_date, status
        FROM tasks 
        WHERE user_id = ?";
$params = [$user_id];

// Logic lọc
if ($filter_status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

// Thêm sắp xếp
$sql .= " ORDER BY due_date ASC, created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); 
    $tasks = $stmt->fetchAll(); 
} catch (PDOException $e) {
    $error_db = "Không thể tải danh sách công việc: " . $e->getMessage();
}

/**
 * Hàm hỗ trợ hiển thị badge trạng thái Bootstrap
 */
function get_status_badge($status) {
    switch ($status) {
        case 'completed':
            return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Hoàn thành</span>';
        case 'in_progress':
            return '<span class="badge bg-primary"><i class="fas fa-hourglass-half me-1"></i>Đang tiến hành</span>';
        default:
            return '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-start me-1"></i>Đang chờ</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quản Lý Công Việc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css"> </head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-clipboard-list me-2"></i> Ứng Dụng Quản Lý Công Việc</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link text-white me-3">Chào mừng, <strong><?php echo htmlspecialchars($username); ?></strong></span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Đăng Xuất</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4 text-primary">Dashboard Công Việc</h1>

        <?php 
        display_session_message('success');
        display_session_message('error');
        if (!empty($error_db)): echo '<div class="alert alert-danger">'. $error_db .'</div>'; endif; 
        ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="btn-group">
                <a href="create_task.php" class="btn btn-success me-2"><i class="fas fa-plus-circle me-2"></i> Thêm Công Việc Mới</a>
                <a href="manage_categories.php" class="btn btn-info text-white"><i class="fas fa-tags me-2"></i> Quản Lý Danh Mục</a>
            </div>

            <form action="dashboard.php" method="GET" class="d-flex align-items-center">
                <label for="status_filter" class="form-label me-2 mb-0 fw-bold">Lọc theo Trạng thái:</label>
                <select class="form-select w-auto shadow-sm" id="status_filter" name="status_filter" onchange="this.form.submit()">
                    <option value="all" <?php echo ($filter_status == 'all') ? 'selected' : ''; ?>>-- Tất cả --</option>
                    <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Đang chờ</option>
                    <option value="in_progress" <?php echo ($filter_status == 'in_progress') ? 'selected' : ''; ?>>Đang tiến hành</option>
                    <option value="completed" <?php echo ($filter_status == 'completed') ? 'selected' : ''; ?>>Hoàn thành</option>
                </select>
            </form>
        </div>

        <h2 class="mb-3">Công Việc Của Bạn</h2>

        <?php if (empty($tasks)): ?>
            <div class="alert alert-info text-center py-4" role="alert">
                <i class="fas fa-info-circle me-2"></i> Không tìm thấy công việc nào với bộ lọc "<?php echo htmlspecialchars($filter_status == 'all' ? 'Tất cả' : ucfirst(str_replace('_', ' ', $filter_status))); ?>". Hãy tạo một công việc mới!
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($tasks as $task): ?>
                    <div class="col">
                        <div class="card h-100 task-card">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-truncate mb-2"><?php echo htmlspecialchars($task['title']); ?></h5>
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i> Hạn: **<?php echo $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : 'N/A'; ?>**
                                </h6>
                                <p class="card-text flex-grow-1 text-muted small"><?php echo nl2br(htmlspecialchars(substr($task['description'] ?? '', 0, 150) . (strlen($task['description'] ?? '') > 150 ? '...' : ''))); ?></p>
                                
                                <div class="mb-3">
                                    Trạng thái: <?php echo get_status_badge($task['status']); ?>
                                </div>
                                
                                <div class="mt-auto d-flex flex-wrap gap-2">
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i> Sửa</a>
                                    
                                    <?php if ($task['status'] !== 'completed'): ?>
                                    <a href="update_status.php?id=<?php echo $task['id']; ?>&status=completed" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i> Hoàn Thành</a>
                                    <?php else: ?>
                                    <a href="update_status.php?id=<?php echo $task['id']; ?>&status=pending" class="btn btn-sm btn-secondary"><i class="fas fa-redo-alt me-1"></i> Chưa Xong</a>
                                    <?php endif; ?>
                                    
                                    <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa công việc này?');"><i class="fas fa-trash-alt me-1"></i> Xóa</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <footer class="footer mt-5">
        <div class="container">
            <span>© <?php echo date("Y"); ?> Ứng Dụng Quản Lý Công Việc.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>