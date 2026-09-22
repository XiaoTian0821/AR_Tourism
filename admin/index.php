<?php
/**
 * AR Tourism Explorer - Admin Dashboard
 */
$page_title = 'Dashboard';
$body_class = 'admin-dashboard';

require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$user = getCurrentUser();
$stats = getDashboardStats();
$recentActivity = getRecentActivity(10);
$recentAttractions = getRecentContent('attractions', 5);
$recentPosters = getRecentContent('posters', 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> - <?php echo APP_NAME; ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="<?php echo APP_URL; ?>/" class="sidebar-brand">
                <i class="fas fa-vr-cardboard"></i>
                <span><?php echo APP_NAME; ?></span>
            </a>
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-section-title">Main</span>
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <div class="nav-section">
                <span class="nav-section-title">Tourism</span>
                <a href="destinations/index.php" class="nav-link">
                    <i class="fas fa-landmark"></i>
                    <span>Destinations</span>
                </a>
                <a href="attractions/index.php" class="nav-link">
                    <i class="fas fa-camera"></i>
                    <span>Attractions</span>
                </a>
                <a href="categories/index.php" class="nav-link">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </div>
            
            <div class="nav-section">
                <span class="nav-section-title">AR Management</span>
                <a href="ar-posters/index.php" class="nav-link">
                    <i class="fas fa-image"></i>
                    <span>AR Posters</span>
                </a>
                <a href="ar-hotspots/index.php" class="nav-link">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Hotspots</span>
                </a>
                <a href="ar-targets/index.php" class="nav-link">
                    <i class="fas fa-cube"></i>
                    <span>Target Compilation</span>
                </a>
            </div>
            
            <div class="nav-section">
                <span class="nav-section-title">System</span>
                <a href="users/index.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="logs/index.php" class="nav-link">
                    <i class="fas fa-history"></i>
                    <span>Activity Logs</span>
                </a>
                <a href="settings/index.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
            
            <div class="sidebar-footer">
                <a href="<?php echo APP_URL; ?>/" class="nav-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>View Website</span>
                </a>
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="admin-main">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" id="topbarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="topbar-title">Dashboard</h1>
            </div>
            <div class="topbar-right">
                <div class="user-menu">
                    <span class="user-name"><i class="fas fa-user-circle me-1"></i><?php echo e($user['full_name'] ?? $user['username']); ?></span>
                    <span class="user-role badge bg-success"><?php echo e(ucfirst($user['role'])); ?></span>
                </div>
            </div>
        </header>
        
        <!-- Content Area -->
        <main class="admin-content">
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card stat-card-primary">
                        <div class="stat-card-icon">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <div class="stat-card-content">
                            <h3><?php echo $stats['destinations']; ?></h3>
                            <p>Destinations</p>
                        </div>
                        <a href="destinations/index.php" class="stat-card-link">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card stat-card-success">
                        <div class="stat-card-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div class="stat-card-content">
                            <h3><?php echo $stats['attractions']; ?></h3>
                            <p>Attractions</p>
                        </div>
                        <a href="attractions/index.php" class="stat-card-link">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card stat-card-warning">
                        <div class="stat-card-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="stat-card-content">
                            <h3><?php echo $stats['ar_posters']; ?></h3>
                            <p>AR Posters</p>
                        </div>
                        <a href="ar-posters/index.php" class="stat-card-link">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card stat-card-info">
                        <div class="stat-card-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="stat-card-content">
                            <h3><?php echo $stats['ar_hotspots']; ?></h3>
                            <p>AR Hotspots</p>
                        </div>
                        <a href="ar-hotspots/index.php" class="stat-card-link">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-3">
                                    <a href="destinations/add.php" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-2"></i>New Destination
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a href="attractions/add.php" class="btn btn-success w-100">
                                        <i class="fas fa-plus me-2"></i>New Attraction
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a href="ar-posters/add.php" class="btn btn-warning w-100">
                                        <i class="fas fa-plus me-2"></i>New AR Poster
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a href="categories/add.php" class="btn btn-info w-100">
                                        <i class="fas fa-plus me-2"></i>New Category
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Content -->
            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Recent Attractions -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-camera me-2"></i>Recent Attractions</h5>
                            <a href="attractions/index.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Destination</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentAttractions as $attr): ?>
                                            <tr>
                                                <td><?php echo e($attr['name']); ?></td>
                                                <td><?php echo e($attr['destination_name'] ?? '-'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $attr['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo e(ucfirst($attr['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($attr['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recentAttractions)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No attractions found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Posters -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-image me-2"></i>Recent AR Posters</h5>
                            <a href="ar-posters/index.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Target</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentPosters as $poster): ?>
                                            <tr>
                                                <td><?php echo e($poster['name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $poster['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo e(ucfirst($poster['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $poster['target_status'] === 'ready' ? 'success' : 'warning'; ?>">
                                                        <?php echo e(ucfirst(str_replace('_', ' ', $poster['target_status']))); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($poster['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recentPosters)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No AR posters found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Activity Log -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($recentActivity as $log): ?>
                                <div class="activity-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="activity-icon me-3">
                                            <i class="fas fa-<?php 
                                                echo match($log['action']) {
                                                    'login' => 'sign-in-alt text-success',
                                                    'logout' => 'sign-out-alt text-muted',
                                                    'add' => 'plus-circle text-primary',
                                                    'edit' => 'edit text-warning',
                                                    'delete' => 'trash text-danger',
                                                    default => 'circle text-secondary'
                                                }; 
                                            ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small"><?php echo e($log['description'] ?? $log['action']); ?></p>
                                            <small class="text-muted"><?php echo formatDateTime($log['created_at'], 'M d, Y H:i'); ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($recentActivity)): ?>
                                <p class="text-muted text-center py-3">No recent activity</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.toggle('collapsed');
        });
        
        document.getElementById('topbarToggle').addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.toggle('collapsed');
        });
    </script>
</body>
</html>
