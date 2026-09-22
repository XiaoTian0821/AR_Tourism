<?php
/**
 * AR Tourism Explorer - Admin: Destination Management
 */
$page_title = 'Destinations';
$body_class = 'admin-destinations';

require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$message = '';
$messageType = '';
$editDest = null;

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$perPage = ADMIN_ITEMS_PER_PAGE;

// Get categories for select
$categories = dbQuery("SELECT id, name FROM categories ORDER BY name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $message = 'Security error. Please try again.';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add':
            case 'edit':
                $name = sanitize($_POST['name'] ?? '');
                $shortDesc = sanitize($_POST['short_description'] ?? '');
                $fullDesc = sanitize($_POST['full_description'] ?? '');
                $location = sanitize($_POST['location'] ?? '');
                $latitude = floatval($_POST['latitude'] ?? 0);
                $longitude = floatval($_POST['longitude'] ?? 0);
                $mapsUrl = sanitize($_POST['google_maps_url'] ?? '');
                $categoryId = intval($_POST['category_id'] ?? 0);
                $status = $_POST['status'] ?? 'active';
                
                // Handle image upload
                $coverImage = '';
                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFile($_FILES['cover_image'], UPLOAD_PATH . 'destinations/');
                    if ($upload['success']) {
                        $coverImage = $upload['filename'];
                    }
                }
                
                if ($action === 'add') {
                    $slug = generateSlug($name);
                    $sql = "INSERT INTO destinations (name, slug, short_description, full_description, cover_image, location, latitude, longitude, google_maps_url, category_id, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    dbExecute($sql, [$name, $slug, $shortDesc, $fullDesc, $coverImage, $location, $latitude, $longitude, $mapsUrl, $categoryId ?: null, $status]);
                    logActivity($_SESSION['user_id'], 'add_destination', "Added destination: $name");
                    $message = 'Destination added successfully.';
                } else {
                    $id = intval($_POST['id'] ?? 0);
                    $updateFields = ["name = ?", "short_description = ?", "full_description = ?", "location = ?", 
                                   "latitude = ?", "longitude = ?", "google_maps_url = ?", "category_id = ?", "status = ?"];
                    $updateParams = [$name, $shortDesc, $fullDesc, $location, $latitude, $longitude, $mapsUrl, $categoryId ?: null, $status];
                    
                    if ($coverImage) {
                        $updateFields[] = "cover_image = ?";
                        $updateParams[] = $coverImage;
                    }
                    
                    $sql = "UPDATE destinations SET " . implode(', ', $updateFields) . " WHERE id = ?";
                    $updateParams[] = $id;
                    dbExecute($sql, $updateParams);
                    logActivity($_SESSION['user_id'], 'edit_destination', "Edited destination: $name (ID: $id)");
                    $message = 'Destination updated successfully.';
                }
                $messageType = 'success';
                break;
                
            case 'delete':
                $id = intval($_POST['id'] ?? 0);
                $dest = dbQueryOne("SELECT name FROM destinations WHERE id = ?", [$id]);
                dbExecute("DELETE FROM destinations WHERE id = ?", [$id]);
                logActivity($_SESSION['user_id'], 'delete_destination', "Deleted destination: " . ($dest['name'] ?? "ID: $id"));
                $message = 'Destination deleted successfully.';
                $messageType = 'success';
                break;
                
            case 'toggle_status':
                $id = intval($_POST['id'] ?? 0);
                dbExecute("UPDATE destinations SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?", [$id]);
                $message = 'Destination status updated.';
                $messageType = 'success';
                break;
        }
    }
}

// Get destinations
$where = [];
$params = [];
if ($search) {
    $where[] = "(name LIKE ? OR short_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "SELECT COUNT(*) as total FROM destinations $whereClause";
$countResult = dbQueryOne($countSql, $params);
$totalItems = $countResult['total'] ?? 0;
$pagination = getPagination($totalItems, $page, $perPage);

$sql = "SELECT d.*, c.name as category_name,
        (SELECT COUNT(*) FROM attractions a WHERE a.destination_id = d.id) as attraction_count
        FROM destinations d
        LEFT JOIN categories c ON d.category_id = c.id
        $whereClause
        ORDER BY d.created_at DESC
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$destinations = dbQuery($sql, $params);

// If editing, load the destination
if (isset($_GET['edit'])) {
    $editDest = dbQueryOne("SELECT * FROM destinations WHERE id = ?", [intval($_GET['edit'])]);
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
                <span class="nav-section-title">Tourism</span>
                <a href="index.php" class="nav-link active"><i class="fas fa-landmark"></i><span>Destinations</span></a>
                <a href="../attractions/index.php" class="nav-link"><i class="fas fa-camera"></i><span>Attractions</span></a>
                <a href="../categories/index.php" class="nav-link"><i class="fas fa-tags"></i><span>Categories</span></a>
            </div>
            <div class="nav-section">
                <span class="nav-section-title">AR Management</span>
                <a href="../ar-posters/index.php" class="nav-link"><i class="fas fa-image"></i><span>AR Posters</span></a>
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
                <h1 class="topbar-title">Manage Destinations</h1>
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
                        <i class="fas fa-<?php echo $editDest ? 'edit' : 'plus-circle'; ?> me-2"></i>
                        <?php echo $editDest ? 'Edit Destination' : 'Add New Destination'; ?>
                    </h5>
                    <?php if ($editDest): ?>
                        <a href="index.php" class="btn btn-sm btn-secondary"><i class="fas fa-times me-1"></i>Cancel</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" class="row g-3">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="<?php echo $editDest ? 'edit' : 'add'; ?>">
                        <?php if ($editDest): ?>
                            <input type="hidden" name="id" value="<?php echo e($editDest['id']); ?>">
                        <?php endif; ?>
                        
                        <div class="col-md-6">
                            <label class="form-label">Destination Name *</label>
                            <input type="text" name="name" class="form-control" required 
                                   value="<?php echo e($editDest['name'] ?? ''); ?>" placeholder="e.g., Heritage District">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo e($cat['id']); ?>" 
                                        <?php echo ($editDest['category_id'] ?? 0) == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" 
                                   value="<?php echo e($editDest['location'] ?? ''); ?>" placeholder="e.g., George Town, Penang">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="0.0000001" name="latitude" class="form-control" 
                                   value="<?php echo e($editDest['latitude'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="0.0000001" name="longitude" class="form-control" 
                                   value="<?php echo e($editDest['longitude'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Google Maps URL</label>
                            <input type="url" name="google_maps_url" class="form-control" 
                                   value="<?php echo e($editDest['google_maps_url'] ?? ''); ?>" placeholder="https://maps.google.com/...">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Cover Image</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <?php if ($editDest && $editDest['cover_image']): ?>
                                <small class="text-muted">Current: <?php echo e($editDest['cover_image']); ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" class="form-control" rows="2" 
                                      placeholder="Brief description for listing pages..."><?php echo e($editDest['short_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Full Description</label>
                            <textarea name="full_description" class="form-control" rows="5" 
                                      placeholder="Detailed description..."><?php echo e($editDest['full_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($editDest['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($editDest['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?php echo $editDest ? 'Update Destination' : 'Add Destination'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Search and List -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>All Destinations</h5>
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
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Attractions</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($destinations as $dest): ?>
                                    <tr>
                                        <td>
                                            <?php if ($dest['cover_image']): ?>
                                                <img src="../../assets/uploads/destinations/<?php echo e($dest['cover_image']); ?>" 
                                                     class="rounded" style="width: 60px; height: 40px; object-fit: cover;"
                                                     onerror="this.style.display='none'">
                                            <?php else: ?>
                                                <div class="placeholder-img" style="width: 60px; height: 40px; border-radius: 5px;">
                                                    <i class="fas fa-image fa-xs"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e($dest['name']); ?></strong>
                                            <br><small class="text-muted"><?php echo e($dest['location'] ?? '-'); ?></small>
                                        </td>
                                        <td><?php echo e($dest['category_name'] ?? '-'); ?></td>
                                        <td><?php echo e($dest['attraction_count']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $dest['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo e(ucfirst($dest['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($dest['created_at']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?edit=<?php echo e($dest['id']); ?>" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Delete this destination? This will also delete all associated attractions.')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo e($dest['id']); ?>">
                                                    <?php echo csrfField(); ?>
                                                    <button class="btn btn-outline-danger" type="submit" title="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?php echo e($dest['id']); ?>">
                                                    <?php echo csrfField(); ?>
                                                    <button class="btn btn-outline-warning" type="submit" title="Toggle status">
                                                        <i class="fas fa-<?php echo $dest['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($destinations)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No destinations found</td>
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
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
