<?php
/**
 * AR Tourism Explorer - Attractions Page
 */
$page_title = 'Attractions';
$page_description = 'Browse all attractions available in AR Tourism Explorer';
$body_class = 'attractions-page';

require_once __DIR__ . '/includes/header.php';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$destination = isset($_GET['destination']) ? intval($_GET['destination']) : 0;

// Get destinations for filter
$destinations = dbQuery("SELECT id, name FROM destinations WHERE status = 'active' ORDER BY name ASC");

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

$whereClause = implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM attractions a WHERE $whereClause";
$countResult = dbQueryOne($countSql, $params);
$totalItems = $countResult['total'] ?? 0;

// Get attractions
$perPage = ITEMS_PER_PAGE;
$pagination = getPagination($totalItems, $page, $perPage);

$sql = "SELECT a.*, d.name as destination_name, d.slug as destination_slug,
        (SELECT COUNT(*) FROM attraction_images i WHERE i.attraction_id = a.id) as image_count
        FROM attractions a 
        LEFT JOIN destinations d ON a.destination_id = d.id 
        WHERE $whereClause
        ORDER BY a.created_at DESC
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$attractions = dbQuery($sql, $params);
?>

<div class="page-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #1a3a1c 100%); color: #fff; padding: 4rem 0 2rem; margin-top: 76px;">
    <div class="container">
        <h1 class="display-4 fw-bold"><i class="fas fa-camera me-3"></i>Attractions</h1>
        <p class="lead">Discover amazing places to visit</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <form action="" method="GET" class="row g-2">
                    <div class="col-md-8">
                        <input type="text" name="q" class="form-control" placeholder="Search attractions..." 
                               value="<?php echo e($search); ?>" aria-label="Search attractions">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <select name="destination" class="form-select" onchange="this.form.submit()">
                    <option value="">All Destinations</option>
                    <?php foreach ($destinations as $dest): ?>
                        <option value="<?php echo e($dest['id']); ?>" <?php echo $destination == $dest['id'] ? 'selected' : ''; ?>>
                            <?php echo e($dest['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Results Count -->
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            Found <strong><?php echo $totalItems; ?></strong> attraction(s)
        </div>

        <!-- Attractions Grid -->
        <?php if (empty($attractions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>No attractions found</h3>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
                <a href="?" class="btn btn-primary-custom mt-3">Clear Filters</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($attractions as $attr): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="position-relative">
                                <?php if ($attr['main_image']): ?>
                                    <img src="<?php echo APP_URL; ?>/assets/uploads/<?php echo e($attr['main_image']); ?>" 
                                         class="card-img-top" alt="<?php echo e($attr['name']); ?>"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'800\' height=\'400\'%3E%3Crect fill=\'%23e9ecef\' width=\'800\' height=\'400\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'24\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EImage Unavailable%3C/text%3E%3C/svg%3E'">
                                <?php else: ?>
                                    <div class="card-img-top placeholder-img">
                                        <i class="fas fa-image fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                                <?php if ($attr['youtube_url']): ?>
                                    <span class="attraction-badge">
                                        <i class="fab fa-youtube me-1"></i>Video
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <small class="text-muted"><?php echo e($attr['destination_name'] ?? 'Destination'); ?></small>
                                <h5 class="card-title mt-1"><?php echo e($attr['name']); ?></h5>
                                <p class="card-text"><?php echo e(mb_substr($attr['short_description'] ?? '', 0, 120)); ?>...</p>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <?php if ($attr['google_maps_url']): ?>
                                        <a href="<?php echo e($attr['google_maps_url']); ?>" target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-map-marker-alt me-1"></i>Map
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($attr['youtube_url']): ?>
                                        <a href="<?php echo e($attr['youtube_url']); ?>" target="_blank" 
                                           class="btn btn-sm btn-outline-danger">
                                            <i class="fab fa-youtube me-1"></i>Video
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo APP_URL; ?>/attractions.php?id=<?php echo e($attr['id']); ?>" 
                                       class="btn btn-sm btn-primary-custom">
                                        <i class="fas fa-info-circle me-1"></i>Details
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 pb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-image me-1"></i><?php echo e($attr['image_count']); ?> photos
                                    </small>
                                    <?php if ($attr['opening_hours']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i><?php echo e($attr['opening_hours']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php echo paginationHtml('?', $pagination); ?>
        <?php endif; ?>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
