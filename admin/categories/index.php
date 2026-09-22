<?php
/**
 * AR Tourism Explorer - Admin: Category Management
 */
$page_title = 'Categories';
$body_class = 'admin-categories';

require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$message = '';
$messageType = '';

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
                $icon = sanitize($_POST['icon'] ?? 'fa-tag');
                $displayOrder = intval($_POST['display_order'] ?? 0);
                
                if ($name) {
                    $slug = generateSlug($name);
                    $sql = "INSERT INTO categories (name, slug, description, icon, status, display_order) VALUES (?, ?, ?, ?, 'active', ?)";
                    if (dbExecute($sql, [$name, $slug, $description, $icon, $displayOrder])) {
                        logActivity($_SESSION['user_id'], 'add_category', "Added category: $name");
                        $message = 'Category added successfully.';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to add category. Slug may already exist.';
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id'] ?? 0);
                $name = sanitize($_POST['name'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $icon = sanitize($_POST['icon'] ?? 'fa-tag');
                $displayOrder = intval($_POST['display_order'] ?? 0);
                $status = $_POST['status'] ?? 'active';
                
                if ($id && $name) {
                    $sql = "UPDATE categories SET name = ?, description = ?, icon = ?, display_order = ?, status = ? WHERE id = ?";
                    dbExecute($sql, [$name, $description, $icon, $displayOrder, $status, $id]);
                    logActivity($_SESSION['user_id'], 'edit_category', "Edited category: $name (ID: $id)");
                    $message = 'Category updated successfully.';
                    $messageType = 'success';
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id'] ?? 0);
                if ($id) {
                    $cat = dbQueryOne("SELECT name FROM categories WHERE id = ?", [$id]);
                    dbExecute("DELETE FROM categories WHERE id = ?", [$id]);
                    logActivity($_SESSION['user_id'], 'delete_category', "Deleted category: " . ($cat['name'] ?? "ID: $id"));
                    $message = 'Category deleted successfully.';
                    $messageType = 'success';
                }
                break;
                
            case 'toggle_status':
                $id = intval($_POST['id'] ?? 0);
                if ($id) {
                    dbExecute("UPDATE categories SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?", [$id]);
                    $message = 'Category status updated.';
                    $messageType = 'success';
                }
                break;
        }
    }
}

// Get all categories
$categories = dbQuery("SELECT *, (SELECT COUNT(*) FROM destinations WHERE category_id = categories.id) as destination_count FROM categories ORDER BY display_order ASC, name ASC");
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
    <!-- Include sidebar (simplified for this file) -->
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
                <a href="destinations/index.php" class="nav-link"><i class="fas fa-landmark"></i><span>Destinations</span></a>
                <a href="attractions/index.php" class="nav-link"><i class="fas fa-camera"></i><span>Attractions</span></a>
                <a href="index.php" class="nav-link active"><i class="fas fa-tags"></i><span>Categories</span></a>
            </div>
            <div class="nav-section">
                <span class="nav-section-title">AR Management</span>
                <a href="ar-posters/index.php" class="nav-link"><i class="fas fa-image"></i><span>AR Posters</span></a>
            </div>
            <div class="sidebar-footer">
                <a href="../../index.php" class="nav-link"><i class="fas fa-external-link-alt"></i><span>View Website</span></a>
                <a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </div>
        </nav>
    </aside>
    
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" id="topbarToggle"><i class="fas fa-bars"></i></button>
                <h1 class="topbar-title">Manage Categories</h1>
            </div>
        </header>
        
        <main class="admin-content">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo e($messageType); ?> alert-dismissible fade show" role="alert">
                    <?php echo e($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Category</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="row g-3">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="add">
                        <div class="col-md-4">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Historical">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Icon Class</label>
                            <input type="text" name="icon" class="form-control" placeholder="fa-landmark" value="fa-tag">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i>Add</button>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this category..."></textarea>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>All Categories</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Icon</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Destinations</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><i class="fas <?php echo e($cat['icon'] ?? 'fa-tag'); ?> text-primary"></i></td>
                                        <td><?php echo e($cat['name']); ?></td>
                                        <td><code><?php echo e($cat['slug']); ?></code></td>
                                        <td><?php echo e($cat['destination_count']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $cat['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo e(ucfirst($cat['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="editCategory(<?php echo e(json_encode($cat)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo e($cat['id']); ?>">
                                                    <?php echo csrfField(); ?>
                                                    <button class="btn btn-outline-danger" type="submit"><i class="fas fa-trash"></i></button>
                                                </form>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?php echo e($cat['id']); ?>">
                                                    <?php echo csrfField(); ?>
                                                    <button class="btn btn-outline-warning" type="submit" title="Toggle status">
                                                        <i class="fas fa-<?php echo $cat['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editCategoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" id="edit_description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon Class</label>
                            <input type="text" name="icon" class="form-control" id="edit_icon">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" id="edit_display_order">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" id="edit_status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <?php echo csrfField(); ?>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(category) {
            document.getElementById('edit_id').value = category.id;
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_description').value = category.description || '';
            document.getElementById('edit_icon').value = category.icon || 'fa-tag';
            document.getElementById('edit_display_order').value = category.display_order || 0;
            document.getElementById('edit_status').value = category.status || 'active';
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }
    </script>
</body>
</html>
