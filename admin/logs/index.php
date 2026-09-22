<?php
/**
 * AR Tourism Explorer - Admin: Activity Logs
 */
$page_title = 'Activity Logs';
$body_class = 'admin-logs';

// require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$limit = intval($_GET['limit']) ? min(intval($_GET['limit']), 100) : 50;
$perPage = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];
if ($search) {
    $where[] = "(al.action LIKE ? OR al.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total
$countSql = "SELECT COUNT(*) as total FROM activity_logs al $whereClause";
$countResult = dbQueryOne($countSql, $params);
$totalItems = $countResult['total'] ?? 0;
$pagination = getPagination($totalItems, $page, $perPage);

// Get logs
$sql = "SELECT al.*, u.username, u.full_name 
        FROM activity_logs al 
        LEFT JOIN users u ON al.user_id = u.id 
        $whereClause
        ORDER BY al.created_at DESC
        LIMIT $perPage OFFSET $offset";
$logs = dbQuery($sql, $params);

// Clear logs
if (isset($_GET['clear']) && $_GET['clear'] === 'confirm') {
    dbExecute("DELETE FROM activity_logs");
    logActivity($_SESSION['user_id'], 'clear_logs', 'Cleared all activity logs');
    redirect('index.php?cleared=1');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="<?php echo APP_URL; ?>/" class="sidebar-brand">
                <i class="fas fa-vr-cardboard"></i>
                <span>AR Tourism</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-section-title">Main</span>
                <a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            </div>
            <div class="nav-section">
                <span class="nav-section-title">System</span>
                <a href="index.php" class="nav-link active"><i class="fas fa-history"></i><span>Activity Logs</span></a>
            </div>
            <div class="sidebar-footer">
                <a href="../../index.php" class="nav-link"><i class="fas fa-external-link-alt"></i><span>View Website</span></a>
                <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </div>
        </nav>
    </aside>
    
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" id="topbarToggle"><i class="fas fa-bars"></i></button>
                <h1 class="topbar-title">Activity Logs</h1>
            </div>
        </header>
        
        <main class="admin-content">
            <?php if (isset($_GET['cleared'])): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>All activity logs have been cleared.
                </div>
            <?php endif; ?>
            
            <!-- Search and Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="q" class="form-control" placeholder="Search logs..." value="<?php echo e($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="limit" class="form-select" onchange="this.form.submit()">
                                <option value="20" <?php echo $limit === 20 ? 'selected' : ''; ?>>20 per page</option>
                                <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50 per page</option>
                                <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100 per page</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Logs Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                    <a href="?clear=confirm" class="btn btn-sm btn-danger" onclick="return confirm('Clear all activity logs? This cannot be undone.')">
                        <i class="fas fa-trash me-1"></i>Clear Logs
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo formatDateTime($log['created_at'], 'M d, Y<br>H:i:s'); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($log['username']): ?>
                                                <strong><?php echo e($log['full_name'] ?? $log['username']); ?></strong>
                                                <br><small class="text-muted">@<?php echo e($log['username']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">System</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($log['action']) {
                                                    'login' => 'success',
                                                    'logout' => 'secondary',
                                                    'add' => 'primary',
                                                    'edit' => 'warning',
                                                    'delete' => 'danger',
                                                    default => 'info'
                                                }; 
                                            ?>">
                                                <?php echo e($log['action']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo e($log['description'] ?? '-'); ?></td>
                                        <td><code><?php echo e($log['ip_address'] ?? '-'); ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No activity logs found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <?php echo paginationHtml('?q=' . urlencode($search) . '&limit=' . $limit, $pagination); ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
