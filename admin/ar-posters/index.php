<?php
/**
 * AR Tourism Explorer - Admin: AR Poster Management
 */
$page_title = 'AR Posters';
$body_class = 'admin-ar-posters';

require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$message = '';
$messageType = '';
$editPoster = null;

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$perPage = ADMIN_ITEMS_PER_PAGE;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $message = 'Security error. Please try again.';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add':
                $name = sanitize($_POST['name'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $status = $_POST['status'] ?? 'active';
                
                // Handle poster upload
                $posterImage = '';
                if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFile($_FILES['poster_image'], UPLOAD_PATH . 'ar-posters/');
                    if ($upload['success']) {
                        $posterImage = $upload['filename'];
                    }
                }
                
                if ($name) {
                    $slug = generateSlug($name);
                    $sql = "INSERT INTO ar_posters (name, slug, description, poster_image, status) VALUES (?, ?, ?, ?, ?)";
                    dbExecute($sql, [$name, $slug, $description, $posterImage, $status]);
                    logActivity($_SESSION['user_id'], 'add_ar_poster', "Added AR poster: $name");
                    $message = 'AR Poster added successfully.';
                }
                $messageType = 'success';
                break;
                
            case 'edit':
                $id = intval($_POST['id'] ?? 0);
                $name = sanitize($_POST['name'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $status = $_POST['status'] ?? 'active';
                
                // Handle new poster upload
                $posterImage = '';
                if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFile($_FILES['poster_image'], UPLOAD_PATH . 'ar-posters/');
                    if ($upload['success']) {
                        $posterImage = $upload['filename'];
                    }
                }
                
                if ($id && $name) {
                    $updateFields = ["name = ?", "description = ?", "status = ?"];
                    $updateParams = [$name, $description, $status];
                    
                    if ($posterImage) {
                        $updateFields[] = "poster_image = ?";
                        $updateParams[] = $posterImage;
                        // Reset target status when poster is replaced
                        $updateFields[] = "target_status = 'not_compiled'";
                        $updateParams[] = 'not_compiled';
                    }
                    
                    $sql = "UPDATE ar_posters SET " . implode(', ', $updateFields) . " WHERE id = ?";
                    $updateParams[] = $id;
                    dbExecute($sql, $updateParams);
                    logActivity($_SESSION['user_id'], 'edit_ar_poster', "Edited AR poster: $name (ID: $id)");
                    $message = 'AR Poster updated successfully.';
                }
                $messageType = 'success';
                break;
                
            case 'delete':
                $id = intval($_POST['id'] ?? 0);
                $poster = dbQueryOne("SELECT name FROM ar_posters WHERE id = ?", [$id]);
                dbExecute("DELETE FROM ar_posters WHERE id = ?", [$id]);
                logActivity($_SESSION['user_id'], 'delete_ar_poster', "Deleted AR poster: " . ($poster['name'] ?? "ID: $id"));
                $message = 'AR Poster deleted successfully.';
                $messageType = 'success';
                break;
                
            case 'compile':
                $id = intval($_POST['id'] ?? 0);
                // In a real implementation, this would trigger MindAR compilation
                // For now, we'll simulate a successful compilation
                $sql = "UPDATE ar_posters SET target_status = 'ready', target_compiled_at = NOW() WHERE id = ?";
                dbExecute($sql, [$id]);
                logActivity($_SESSION['user_id'], 'compile_ar_target', "Compiled AR target for poster ID: $id");
                $message = 'AR Target compiled successfully. The poster is now ready for AR scanning.';
                $messageType = 'success';
                break;
                
            case 'toggle_status':
                $id = intval($_POST['id'] ?? 0);
                dbExecute("UPDATE ar_posters SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?", [$id]);
                $message = 'AR Poster status updated.';
                $messageType = 'success';
                break;
        }
    }
}

// Get posters
$where = [];
$params = [];
if ($search) {
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "SELECT COUNT(*) as total FROM ar_posters $whereClause";
$countResult = dbQueryOne($countSql, $params);
$totalItems = $countResult['total'] ?? 0;
$pagination = getPagination($totalItems, $page, $perPage);

$sql = "SELECT * FROM ar_posters $whereClause ORDER BY created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$posters = dbQuery($sql, $params);

// If editing
if (isset($_GET['edit'])) {
    $editPoster = dbQueryOne("SELECT * FROM ar_posters WHERE id = ?", [intval($_GET['edit'])]);
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
    <style>
        .poster-preview {
            max-width: 150px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .target-status {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
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
                <span class="nav-section-title">Tourism</span>
                <a href="../destinations/index.php" class="nav-link"><i class="fas fa-landmark"></i><span>Destinations</span></a>
                <a href="../attractions/index.php" class="nav-link"><i class="fas fa-camera"></i><span>Attractions</span></a>
            </div>
            <div class="nav-section">
                <span class="nav-section-title">AR Management</span>
                <a href="index.php" class="nav-link active"><i class="fas fa-image"></i><span>AR Posters</span></a>
                <a href="hotspots/index.php" class="nav-link"><i class="fas fa-map-marker-alt"></i><span>Hotspots</span></a>
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
                <h1 class="topbar-title">Manage AR Posters</h1>
            </div>
        </header>
        
        <main class="admin-content">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo e($messageType); ?> alert-dismissible fade show" role="alert">
                    <?php echo e($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Add/Edit Form -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?php echo $editPoster ? 'edit' : 'plus-circle'; ?> me-2"></i>
                        <?php echo $editPoster ? 'Edit AR Poster' : 'Add New AR Poster'; ?>
                    </h5>
                    <?php if ($editPoster): ?>
                        <a href="index.php" class="btn btn-sm btn-secondary"><i class="fas fa-times me-1"></i>Cancel</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" class="row g-3">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="<?php echo $editPoster ? 'edit' : 'add'; ?>">
                        <?php if ($editPoster): ?>
                            <input type="hidden" name="id" value="<?php echo e($editPoster['id']); ?>">
                        <?php endif; ?>
                        
                        <div class="col-md-6">
                            <label class="form-label">Poster Name *</label>
                            <input type="text" name="name" class="form-control" required 
                                   value="<?php echo e($editPoster['name'] ?? ''); ?>" placeholder="e.g., Penang Heritage Tour">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($editPoster['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($editPoster['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Target Status</label>
                            <input type="text" class="form-control" value="<?php echo e(ucfirst(str_replace('_', ' ', $editPoster['target_status'] ?? 'not_compiled'))); ?>" readonly>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" 
                                      placeholder="Brief description of this AR poster..."><?php echo e($editPoster['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Poster Image *</label>
                            <input type="file" name="poster_image" class="form-control" accept="image/*" required>
                            <small class="text-muted">Upload a high-quality poster image (minimum 1000px for best AR recognition)</small>
                            <?php if ($editPoster && $editPoster['poster_image'] && file_exists(UPLOAD_PATH . 'ar-posters/' . basename($editPoster['poster_image']))): ?>
                                <div class="mt-2">
                                    <img src="../../assets/uploads/ar-posters/<?php echo rawurlencode(basename($editPoster['poster_image'])); ?>" 
                                         class="poster-preview" alt="Current poster">
                                    <br><small class="text-muted"><?php echo e($editPoster['poster_image']); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Target File</label>
                            <?php if ($editPoster && $editPoster['target_file']): ?>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?php echo APP_URL; ?>/assets/ar-targets/<?php echo e($editPoster['target_file']); ?>" readonly>
                                    <a href="<?php echo APP_URL; ?>/assets/ar-targets/<?php echo e($editPoster['target_file']); ?>" class="btn btn-outline-primary" download>
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                <input type="text" class="form-control" value="Not compiled yet" readonly>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?php echo $editPoster ? 'Update Poster' : 'Add Poster'; ?>
                            </button>
                            <?php if ($editPoster): ?>
                                <button type="submit" name="action" value="compile" class="btn btn-warning ms-2" onclick="return confirm('Compile AR target? This may take a moment.')">
                                    <i class="fas fa-cube me-1"></i>Compile Target
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- List -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>All AR Posters</h5>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="text" name="q" class="form-control me-2" placeholder="Search..." value="<?php echo e($search); ?>">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Preview</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Target Status</th>
                                    <th>Status</th>
                                    <th>Compiled</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posters as $poster): ?>
                                    <tr>
                                        <td>
                                            <?php if ($poster['poster_image'] && file_exists(UPLOAD_PATH . 'ar-posters/' . basename($poster['poster_image']))): ?>
                                                <img src="../../assets/uploads/ar-posters/<?php echo rawurlencode(basename($poster['poster_image'])); ?>" 
                                                     class="poster-preview" alt="<?php echo e($poster['name']); ?>"
                                                     onerror="this.style.display='none'">
                                            <?php else: ?>
                                                <div class="placeholder-img" style="width: 80px; height: 50px; border-radius: 5px;">
                                                    <i class="fas fa-image fa-sm"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e($poster['name']); ?></strong>
                                            <br><small class="text-muted"><?php echo e($poster['slug']); ?></small>
                                        </td>
                                        <td class="text-muted small"><?php echo e(mb_substr($poster['description'] ?? '', 0, 60)); ?><?php echo strlen($poster['description'] ?? '') > 60 ? '...' : ''; ?></td>
                                        <td>
                                            <span class="badge target-status bg-<?php echo $poster['target_status'] === 'ready' ? 'success' : ($poster['target_status'] === 'compiling' ? 'warning' : 'secondary'); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $poster['target_status']))); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $poster['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo e(ucfirst($poster['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $poster['target_compiled_at'] ? formatDate($poster['target_compiled_at']) : '-'; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?edit=<?php echo e($poster['id']); ?>" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="hotspots/index.php?poster_id=<?php echo e($poster['id']); ?>" class="btn btn-outline-info" title="Manage Hotspots">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </a>
                                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Delete this AR poster?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo e($poster['id']); ?>">
                                                    <?php echo csrfField(); ?>
                                                    <button class="btn btn-outline-danger" type="submit" title="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?php echo e($poster['id']); ?>">
                                                    <?php echo csrfField(); ?>
                                                    <button class="btn btn-outline-warning" type="submit" title="Toggle status">
                                                        <i class="fas fa-<?php echo $poster['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($posters)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No AR posters found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <?php echo paginationHtml('?q=' . urlencode($search), $pagination); ?>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>AR Target Compilation Guide</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Upload a high-quality poster image (JPEG/PNG, minimum 1000x1000 pixels recommended)</li>
                        <li>Add hotspots to the poster by clicking "Manage Hotspots"</li>
                        <li>Assign attractions or content to each hotspot</li>
                        <li>Click "Compile Target" to generate the MindAR .mind file</li>
                        <li>Once compiled, the poster is ready for AR scanning</li>
                    </ol>
                    <p class="text-muted small mt-3">
                        <strong>Note:</strong> The MindAR compiler can be accessed at 
                        <a href="https://hiukim.github.io/mind-ar-js-docs/tools/mindar-image" target="_blank">MindAR Studio</a>. 
                        For this demo, clicking "Compile Target" simulates successful compilation.
                    </p>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
