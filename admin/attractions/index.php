<?php
/**
 * AR Tourism Explorer - Admin: Attraction Management
 */
$page_title = 'Attractions';
$body_class = 'admin-attractions';

// require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$message = '';
$messageType = '';
$editAttr = null;

// Pagination and filters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$destination = isset($_GET['destination']) ? intval($_GET['destination']) : 0;
$perPage = ADMIN_ITEMS_PER_PAGE;

// Get destinations and categories for selects
$destinations = dbQuery("SELECT id, name FROM destinations WHERE status = 'active' ORDER BY name ASC");
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
                $destId = intval($_POST['destination_id'] ?? 0);
                $shortDesc = sanitize($_POST['short_description'] ?? '');
                $fullDesc = sanitize($_POST['full_description'] ?? '');
                $categoryId = intval($_POST['category_id'] ?? 0);
                $openingHours = sanitize($_POST['opening_hours'] ?? '');
                $entryInfo = sanitize($_POST['entry_information'] ?? '');
                $latitude = floatval($_POST['latitude'] ?? 0);
                $longitude = floatval($_POST['longitude'] ?? 0);
                $mapsUrl = sanitize($_POST['google_maps_url'] ?? '');
                $youtubeUrl = sanitize($_POST['youtube_url'] ?? '');
                $websiteUrl = sanitize($_POST['website_url'] ?? '');
                $status = $_POST['status'] ?? 'active';
                $displayOrder = intval($_POST['display_order'] ?? 0);
                
                // Handle main image upload
                $mainImage = '';
                if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFile($_FILES['main_image'], UPLOAD_PATH . 'attractions/');
                    if ($upload['success']) {
                        $mainImage = $upload['filename'];
                    }
                }
                
                if ($action === 'add') {
                    $slug = generateSlug($name);
                    $sql = "INSERT INTO attractions (destination_id, name, slug, short_description, full_description, main_image, 
                            youtube_url, website_url, google_maps_url, latitude, longitude, opening_hours, entry_information, 
                            category, status, display_order) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    dbExecute($sql, [$destId, $name, $slug, $shortDesc, $fullDesc, $mainImage, 
                                    $youtubeUrl, $websiteUrl, $mapsUrl, $latitude, $longitude, 
                                    $openingHours, $entryInfo, $categoryId ?: null, $status, $displayOrder]);
                    logActivity($_SESSION['user_id'], 'add_attraction', "Added attraction: $name");
                    $message = 'Attraction added successfully.';
                } else {
                    $id = intval($_POST['id'] ?? 0);
                    $updateFields = ["destination_id = ?", "name = ?", "short_description = ?", "full_description = ?", 
                                   "youtube_url = ?", "website_url = ?", "google_maps_url = ?", "latitude = ?", 
                                   "longitude = ?", "opening_hours = ?", "entry_information = ?", 
                                   "category = ?", "status = ?", "display_order = ?"];
                    $updateParams = [$destId, $name, $shortDesc, $fullDesc, $youtubeUrl, $websiteUrl, $mapsUrl, 
                                    $latitude, $longitude, $openingHours, $entryInfo, $categoryId ?: null, $status, $displayOrder];
                    
                    if ($mainImage) {
                        $updateFields[] = "main_image = ?";
                        $updateParams[] = $mainImage;
                    }
                    
                    $sql = "UPDATE attractions SET " . implode(', ', $updateFields) . " WHERE id = ?";
                    $updateParams[] = $id;
                    dbExecute($sql, $updateParams);
                    logActivity($_SESSION['user_id'], 'edit_attraction', "Edited attraction: $name (ID: $id)");
                    $message = 'Attraction updated successfully.';
                }
                $messageType = 'success';
                break;
                
            case 'delete':
                $id = intval($_POST['id'] ?? 0);
                $attr = dbQueryOne("SELECT name FROM attractions WHERE id = ?", [$id]);
                dbExecute("DELETE FROM attractions WHERE id = ?", [$id]);
                logActivity($_SESSION['user_id'], 'delete_attraction', "Deleted attraction: " . ($attr['name'] ?? "ID: $id"));
                $message = 'Attraction deleted successfully.';
                $messageType = 'success';
                break;
                
            case 'toggle_status':
                $id = intval($_POST['id'] ?? 0);
                dbExecute("UPDATE attractions SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?", [$id]);
                $message = 'Attraction status updated.';
                $messageType = 'success';
                break;
        }
    }
}

// Build query
$where = ["a.status = 'active'"];
$params = [];
if ($search) {
    $where[] = "(a.name LIKE ? OR a.short_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($destination) {
    $where[] = "a.destination_id = ?";
    $params[] = $destination;
}
$whereClause = 'WHERE ' . implode(' AND ', $where);

// Get total
$countSql = "SELECT COUNT(*) as total FROM attractions a $whereClause";
$countResult = dbQueryOne($countSql, $params);
$totalItems = $countResult['total'] ?? 0;
$pagination = getPagination($totalItems, $page, $perPage);

// Get attractions
$sql = "SELECT a.*, d.name as destination_name, c.name as category_name,
        (SELECT COUNT(*) FROM attraction_images i WHERE i.attraction_id = a.id) as image_count
        FROM attractions a
        LEFT JOIN destinations d ON a.destination_id = d.id
        LEFT JOIN categories c ON a.category_id = c.id
        $whereClause
        ORDER BY a.created_at DESC
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$attractions = dbQuery($sql, $params);

// If editing
if (isset($_GET['edit'])) {
    $editAttr = dbQueryOne("SELECT * FROM attractions WHERE id = ?", [intval($_GET['edit'])]);
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
                <a href="../destinations/index.php" class="nav-link"><i class="fas fa-landmark"></i><span>Destinations</span></a>
                <a href="index.php" class="nav-link active"><i class="fas fa-camera"></i><span>Attractions</span></a>
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
                <h1 class="topbar-title">Manage Attractions</h1>
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
                        <i class="fas fa-<?php echo $editAttr ? 'edit' : 'plus-circle'; ?> me-2"></i>
                        <?php echo $editAttr ? 'Edit Attraction' : 'Add New Attraction'; ?>
                    </h5>
                    <?php if ($editAttr): ?>
                        <a href="index.php" class="btn btn-sm btn-secondary"><i class="fas fa-times me-1"></i>Cancel</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" class="row g-3">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="<?php echo $editAttr ? 'edit' : 'add'; ?>">
                        <?php if ($editAttr): ?>
                            <input type="hidden" name="id" value="<?php echo e($editAttr['id']); ?>">
                        <?php endif; ?>
                        
                        <div class="col-md-6">
                            <label class="form-label">Attraction Name *</label>
                            <input type="text" name="name" class="form-control" required 
                                   value="<?php echo e($editAttr['name'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Destination *</label>
                            <select name="destination_id" class="form-select" required>
                                <option value="">-- Select Destination --</option>
                                <?php foreach ($destinations as $dest): ?>
                                    <option value="<?php echo e($dest['id']); ?>" 
                                        <?php echo ($editAttr['destination_id'] ?? 0) == $dest['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($dest['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo e($cat['id']); ?>" 
                                        <?php echo ($editAttr['category_id'] ?? 0) == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Opening Hours</label>
                            <input type="text" name="opening_hours" class="form-control" 
                                   value="<?php echo e($editAttr['opening_hours'] ?? ''); ?>" placeholder="e.g., 9:00 AM - 6:00 PM">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Entry Information</label>
                            <input type="text" name="entry_information" class="form-control" 
                                   value="<?php echo e($editAttr['entry_information'] ?? ''); ?>" placeholder="e.g., Free entry, RM 10">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="0.0000001" name="latitude" class="form-control" 
                                   value="<?php echo e($editAttr['latitude'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="0.0000001" name="longitude" class="form-control" 
                                   value="<?php echo e($editAttr['longitude'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" 
                                   value="<?php echo e($editAttr['display_order'] ?? 0); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($editAttr['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($editAttr['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Google Maps URL</label>
                            <input type="url" name="google_maps_url" class="form-control" 
                                   value="<?php echo e($editAttr['google_maps_url'] ?? ''); ?>" placeholder="https://maps.google.com/...">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" name="youtube_url" class="form-control" 
                                   value="<?php echo e($editAttr['youtube_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Website URL</label>
                            <input type="url" name="website_url" class="form-control" 
                                   value="<?php echo e($editAttr['website_url'] ?? ''); ?>" placeholder="https://example.com">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Main Image</label>
                            <input type="file" name="main_image" class="form-control" accept="image/*">
                            <?php if ($editAttr && $editAttr['main_image']): ?>
                                <small class="text-muted">Current: <?php echo e($editAttr['main_image']); ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" class="form-control" rows="2" 
                                      placeholder="Brief description for listing pages..."><?php echo e($editAttr['short_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Full Description</label>
                            <textarea name="full_description" class="form-control" rows="5" 
                                      placeholder="Detailed description..."><?php echo e($editAttr['full_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?php echo $editAttr ? 'Update Attraction' : 'Add Attraction'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- List -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>All Attractions</h5>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="text" name="q" class="form-control me-2" placeholder="Search..." value="<?php echo e($search); ?>">
                                <select name="destination" class="form-select me-2" style="max-width: 200px;">
                                    <option value="">All Destinations</option>
                                    <?php foreach ($destinations as $dest): ?>
                                        <option value="<?php echo e($dest['id']); ?>" <?php echo $destination == $dest['id'] ? 'selected' : ''; ?>>
                                            <?php echo e($dest['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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
                                    <th>Destination</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Images</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attractions as $attr): ?>
                                    <tr>
                                        <td>
                                            <?php if ($attr['main_image']): ?>
                                                <img src="../../assets/uploads/attractions/<?php echo e($attr['main_image']); ?>" 
                                                     class="rounded" style="width: 50px; height: 35px; object-fit: cover;"
                                                     onerror="this.style.display='none'">
                                            <?php else: ?>
                                                <div class="placeholder-img" style="width: 50px; height: 35px; border-radius: 5px;">
                                                    <i class="fas fa-image fa-xs"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e($attr['name']); ?></strong>
                                            <?php if ($attr['youtube_url']): ?>
                                                <i class="fab fa-youtube text-danger ms-1" title="Has video"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($attr['destination_name'] ?? '-'); ?></td>
                                        <td><?php echo e($attr['category_name'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $attr['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo e(ucfirst($attr['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo e($attr['image_count']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?edit=<?php echo e($attr['id']); ?>" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Delete this attraction?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo e($attr['id']); ?>">
                                                    <?php echo csrfField(); ?>
                                                    <button class="btn btn-outline-danger" type="submit" title="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?php echo e($attr['id']); ?>">
                                                    <?php echo csrfField(); ?>
                                                    <button class="btn btn-outline-warning" type="submit" title="Toggle status">
                                                        <i class="fas fa-<?php echo $attr['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($attractions)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No attractions found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <?php echo paginationHtml('?q=' . urlencode($search) . '&destination=' . $destination, $pagination); ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
