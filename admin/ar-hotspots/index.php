<?php
/**
 * AR Tourism Explorer - Admin: AR Hotspot Editor
 * Visual editor for managing AR hotspots on posters
 */
$page_title = 'AR Hotspot Editor';
$body_class = 'admin-hotspot-editor';

// require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$posterId = isset($_GET['poster_id']) ? intval($_GET['poster_id']) : 0;
$message = '';
$messageType = '';

// Get poster
$poster = dbQueryOne("SELECT * FROM ar_posters WHERE id = ?", [$posterId]);
if (!$poster) {
    redirect(APP_URL . '/admin/ar-posters/index.php');
}

// Get existing hotspots
$hotspots = dbQuery("SELECT * FROM ar_hotspots WHERE poster_id = ? ORDER BY z_index ASC, id ASC", [$posterId]);

// Get all attractions for assignment
$attractions = dbQuery("SELECT id, name, destination_id FROM attractions WHERE status = 'active' ORDER BY name ASC");

// Handle hotspot actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $message = 'Security error. Please try again.';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add_hotspot':
                $x = floatval($_POST['x'] ?? 0.5);
                $y = floatval($_POST['y'] ?? 0.5);
                $width = floatval($_POST['width'] ?? 0.2);
                $height = floatval($_POST['height'] ?? 0.2);
                $attractionId = intval($_POST['attraction_id'] ?? 0);
                $hotspotName = sanitize($_POST['hotspot_name'] ?? '');
                $contentType = sanitize($_POST['content_type'] ?? 'info');
                
                $sql = "INSERT INTO ar_hotspots (poster_id, attraction_id, hotspot_name, x, y, width, height, content_type, z_index) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                dbExecute($sql, [$posterId, $attractionId ?: null, $hotspotName, $x, $y, $width, $height, $contentType, count($hotspots) + 1]);
                logActivity($_SESSION['user_id'], 'add_hotspot', "Added hotspot to poster ID: $posterId");
                $message = 'Hotspot added successfully.';
                $messageType = 'success';
                $hotspots = dbQuery("SELECT * FROM ar_hotspots WHERE poster_id = ? ORDER BY z_index ASC, id ASC", [$posterId]);
                break;
                
            case 'update_hotspot':
                $id = intval($_POST['id'] ?? 0);
                $x = floatval($_POST['x'] ?? 0.5);
                $y = floatval($_POST['y'] ?? 0.5);
                $width = floatval($_POST['width'] ?? 0.2);
                $height = floatval($_POST['height'] ?? 0.2);
                $attractionId = intval($_POST['attraction_id'] ?? 0);
                $hotspotName = sanitize($_POST['hotspot_name'] ?? '');
                $contentType = sanitize($_POST['content_type'] ?? 'info');
                $zIndex = intval($_POST['z_index'] ?? 1);
                
                $sql = "UPDATE ar_hotspots SET attraction_id = ?, hotspot_name = ?, x = ?, y = ?, width = ?, height = ?, content_type = ?, z_index = ? WHERE id = ? AND poster_id = ?";
                dbExecute($sql, [$attractionId, $hotspotName, $x, $y, $width, $height, $contentType, $zIndex, $id, $posterId]);
                logActivity($_SESSION['user_id'], 'edit_hotspot', "Updated hotspot ID: $id on poster ID: $posterId");
                $message = 'Hotspot updated successfully.';
                $messageType = 'success';
                $hotspots = dbQuery("SELECT * FROM ar_hotspots WHERE poster_id = ? ORDER BY z_index ASC, id ASC", [$posterId]);
                break;
                
            case 'delete_hotspot':
                $id = intval($_POST['id'] ?? 0);
                dbExecute("DELETE FROM ar_hotspots WHERE id = ? AND poster_id = ?", [$id, $posterId]);
                logActivity($_SESSION['user_id'], 'delete_hotspot', "Deleted hotspot ID: $id from poster ID: $posterId");
                $message = 'Hotspot deleted successfully.';
                $messageType = 'success';
                $hotspots = dbQuery("SELECT * FROM ar_hotspots WHERE poster_id = ? ORDER BY z_index ASC, id ASC", [$posterId]);
                break;
        }
    }
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
        .hotspot-editor {
            position: relative;
            display: inline-block;
            max-width: 100%;
        }
        .hotspot-editor img {
            display: block;
            max-width: 100%;
        }
        .hotspot-marker {
            position: absolute;
            border: 3px solid #ff6b6b;
            border-radius: 50%;
            cursor: move;
            background: rgba(255, 107, 107, 0.3);
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: bold;
            font-size: 12px;
            user-select: none;
        }
        .hotspot-marker:hover,
        .hotspot-marker.selected {
            background: rgba(255, 107, 107, 0.6);
            z-index: 10;
        }
        .hotspot-marker .resize-handle {
            position: absolute;
            bottom: -5px;
            right: -5px;
            width: 12px;
            height: 12px;
            background: #fff;
            border: 2px solid #ff6b6b;
            border-radius: 50%;
            cursor: se-resize;
        }
        .hotspot-marker .delete-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 20px;
            height: 20px;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        .hotspot-marker:hover .delete-btn,
        .hotspot-marker.selected .delete-btn {
            display: flex;
        }
        .editor-panel {
            background: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }
        .hotspot-list-item {
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: background 0.2s;
        }
        .hotspot-list-item:hover,
        .hotspot-list-item.selected {
            background: var(--light-color);
        }
        .hotspot-list-item.selected {
            border-left: 3px solid var(--primary-color);
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
                <a href="../index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            </div>
            <div class="nav-section">
                <span class="nav-section-title">AR Management</span>
                <a href="index.php" class="nav-link"><i class="fas fa-image"></i><span>AR Posters</span></a>
                <a href="index.php" class="nav-link active"><i class="fas fa-map-marker-alt"></i><span>Hotspots</span></a>
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
                <h1 class="topbar-title">AR Hotspot Editor</h1>
            </div>
            <div class="topbar-right">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Posters
                </a>
            </div>
        </header>
        
        <main class="admin-content">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo e($messageType); ?> alert-dismissible fade show" role="alert">
                    <?php echo e($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <!-- Editor Area -->
                <div class="col-lg-8">
                    <div class="editor-panel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i><?php echo e($poster['name']); ?></h5>
                            <div class="btn-group">
                                <button class="btn btn-primary btn-sm" onclick="addHotspotMode()">
                                    <i class="fas fa-plus me-1"></i>Add Hotspot
                                </button>
                                <button class="btn btn-success btn-sm" onclick="saveHotspots()">
                                    <i class="fas fa-save me-1"></i>Save All
                                </button>
                            </div>
                        </div>
                        
                        <!-- Poster Preview with Hotspots -->
                        <div class="hotspot-editor-container text-center" style="background: #f8f9fa; padding: 1rem; border-radius: 10px;">
                            <?php if ($poster['poster_image'] && file_exists(UPLOAD_PATH . 'ar-posters/' . basename($poster['poster_image']))): ?>
                                <div class="hotspot-editor" id="hotspotEditor" style="position: relative; display: inline-block; max-width: 100%;">
                                    <img src="../../assets/uploads/ar-posters/<?php echo rawurlencode(basename($poster['poster_image'])); ?>" 
                                         id="posterImage" alt="<?php echo e($poster['name']); ?>"
                                         style="max-width: 100%; height: auto; display: block;">
                                    
                                    <!-- Hotspot Markers -->
                                    <?php foreach ($hotspots as $index => $hotspot): ?>
                                        <div class="hotspot-marker" 
                                             data-id="<?php echo e($hotspot['id']); ?>"
                                             style="left: calc(<?php echo e($hotspot['x']); ?> * 100% - 25px); 
                                                    top: calc(<?php echo e($hotspot['y']); ?> * 100% - 25px); 
                                                    width: 50px; height: 50px;"
                                             title="<?php echo e($hotspot['hotspot_name'] ?: 'Hotspot ' . ($index + 1)); ?>">
                                            <span><?php echo $index + 1; ?></span>
                                            <button class="delete-btn" onclick="deleteHotspot(<?php echo e($hotspot['id']); ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <div class="resize-handle"></div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- Click to add hotspot overlay -->
                                    <div id="addHotspotOverlay" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: crosshair;"></div>
                                </div>
                            <?php else: ?>
                                <div class="placeholder-img" style="width: 400px; height: 300px; margin: 0 auto;">
                                    <i class="fas fa-image fa-3x"></i>
                                    <p class="mt-2">No poster image available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3 text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Click on the poster to add a hotspot, or click an existing hotspot to edit it.
                        </div>
                    </div>
                </div>
                
                <!-- Properties Panel -->
                <div class="col-lg-4">
                    <div class="editor-panel">
                        <h5 class="mb-3"><i class="fas fa-cog me-2"></i>Hotspot Properties</h5>
                        
                        <?php if (!empty($hotspots)): ?>
                            <!-- Hotspot List -->
                            <div class="mb-3">
                                <label class="form-label">Select Hotspot</label>
                                <select class="form-select" id="hotspotSelect" onchange="selectHotspot(this.value)">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($hotspots as $index => $hotspot): ?>
                                        <option value="<?php echo e($hotspot['id']); ?>">
                                            Hotspot <?php echo $index + 1; ?> - <?php echo e($hotspot['hotspot_name'] ?: 'Unnamed'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Hotspot Properties Form -->
                            <form id="hotspotPropertiesForm" method="POST" action="">
                                <input type="hidden" name="action" value="update_hotspot">
                                <input type="hidden" name="id" id="prop_id">
                                <?php echo csrfField(); ?>
                                
                                <div class="mb-3">
                                    <label class="form-label">Hotspot Name</label>
                                    <input type="text" name="hotspot_name" class="form-control" id="prop_name" placeholder="e.g., Main Entrance">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Assign Attraction</label>
                                    <select name="attraction_id" class="form-select" id="prop_attraction">
                                        <option value="">-- None --</option>
                                        <?php foreach ($attractions as $attr): ?>
                                            <option value="<?php echo e($attr['id']); ?>"><?php echo e($attr['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Content Type</label>
                                    <select name="content_type" class="form-select" id="prop_type">
                                        <option value="info">Information</option>
                                        <option value="video">Video</option>
                                        <option value="image">Image</option>
                                    </select>
                                </div>
                                
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Position X</label>
                                        <input type="number" step="0.01" name="x" class="form-control" id="prop_x" min="0" max="1">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Position Y</label>
                                        <input type="number" step="0.01" name="y" class="form-control" id="prop_y" min="0" max="1">
                                    </div>
                                </div>
                                
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Width</label>
                                        <input type="number" step="0.01" name="width" class="form-control" id="prop_width" min="0.05" max="1">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Height</label>
                                        <input type="number" step="0.01" name="height" class="form-control" id="prop_height" min="0.05" max="1">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Z-Index</label>
                                    <input type="number" name="z_index" class="form-control" id="prop_zindex" value="1">
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i>Update Hotspot
                                </button>
                            </form>
                            
                            <hr>
                            
                            <form method="POST" action="" onsubmit="return confirm('Delete this hotspot?')">
                                <input type="hidden" name="action" value="delete_hotspot">
                                <input type="hidden" name="id" id="delete_hotspot_id">
                                <?php echo csrfField(); ?>
                                <button type="button" class="btn btn-danger w-100" onclick="document.getElementById('delete_hotspot_id').value = document.getElementById('hotspotSelect').value; this.form.submit();">
                                    <i class="fas fa-trash me-1"></i>Delete Hotspot
                                </button>
                            </form>
                            
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                                <p>No hotspots configured yet.<br>Click "Add Hotspot" to begin.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Add Hotspot Form (shown when in add mode) -->
            <div class="card mt-4" id="addHotspotCard" style="display: none;">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-plus me-2"></i>Add New Hotspot</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="addHotspotForm">
                        <input type="hidden" name="action" value="add_hotspot">
                        <input type="hidden" name="x" id="add_x" value="0.5">
                        <input type="hidden" name="y" id="add_y" value="0.5">
                        <input type="hidden" name="width" value="0.2">
                        <input type="hidden" name="height" value="0.2">
                        <?php echo csrfField(); ?>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Hotspot Name</label>
                                <input type="text" name="hotspot_name" class="form-control" placeholder="e.g., Main Entrance">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Assign Attraction</label>
                                <select name="attraction_id" class="form-select">
                                    <option value="">-- None --</option>
                                    <?php foreach ($attractions as $attr): ?>
                                        <option value="<?php echo e($attr['id']); ?>"><?php echo e($attr['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Content Type</label>
                                <select name="content_type" class="form-select">
                                    <option value="info">Information</option>
                                    <option value="video">Video</option>
                                    <option value="image">Image</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>Confirm Position
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="cancelAddHotspot()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedHotspotId = null;
        let addMode = false;
        let hotspotCount = <?php echo count($hotspots); ?>;
        
        // Select hotspot from dropdown
        function selectHotspot(id) {
            selectedHotspotId = id ? parseInt(id) : null;
            
            // Update visual selection
            document.querySelectorAll('.hotspot-marker').forEach(marker => {
                marker.classList.remove('selected');
                if (marker.dataset.id == id) {
                    marker.classList.add('selected');
                }
            });
            
            // Populate form
            if (selectedHotspotId) {
                const marker = document.querySelector(`.hotspot-marker[data-id="${selectedHotspotId}"]`);
                if (marker) {
                    document.getElementById('prop_id').value = selectedHotspotId;
                    document.getElementById('prop_name').value = marker.title || '';
                    // Parse position from style
                    const style = marker.style.left;
                    const width = 50; // marker width in px
                    document.getElementById('prop_x').value = (parseFloat(style) / marker.parentElement.offsetWidth).toFixed(4);
                    document.getElementById('prop_y').value = (parseFloat(marker.style.top) / marker.parentElement.offsetHeight).toFixed(4);
                }
            }
        }
        
        // Add hotspot mode
        function addHotspotMode() {
            addMode = true;
            document.getElementById('addHotspotCard').style.display = 'block';
            document.getElementById('addHotspotOverlay').style.display = 'block';
            document.getElementById('addHotspotOverlay').onclick = function(e) {
                const rect = this.parentElement.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width).toFixed(4);
                const y = ((e.clientY - rect.top) / rect.height).toFixed(4);
                document.getElementById('add_x').value = x;
                document.getElementById('add_y').value = y;
                document.getElementById('addHotspotForm').submit();
            };
        }
        
        function cancelAddHotspot() {
            addMode = false;
            document.getElementById('addHotspotCard').style.display = 'none';
            document.getElementById('addHotspotOverlay').style.display = 'none';
        }
        
        // Delete hotspot
        function deleteHotspot(id) {
            if (confirm('Delete this hotspot?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_hotspot">
                    <input type="hidden" name="id" value="${id}">
                    <?php echo csrfField(); ?>
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Save all hotspots
        function saveHotspots() {
            // Trigger form submission to save all
            const form = document.getElementById('hotspotPropertiesForm');
            if (!form) {
                alert('Add a hotspot before saving.');
                return;
            }

            form.submit();
        }
        
        // Click on marker to select
        document.querySelectorAll('.hotspot-marker').forEach(marker => {
            marker.addEventListener('click', function(e) {
                e.stopPropagation();
                selectHotspot(this.dataset.id);
            });
        });
        
        // Click on empty area to deselect
        document.getElementById('hotspotEditor')?.addEventListener('click', function(e) {
            if (e.target === this || e.target.tagName === 'IMG') {
                selectHotspot(null);
            }
        });
    </script>
</body>
</html>
