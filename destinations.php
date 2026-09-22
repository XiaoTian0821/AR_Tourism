<?php
/**
 * AR Tourism Explorer - Destinations Page
 */
$page_title = 'Destinations';
$page_description = 'Browse all tourism destinations available in AR Tourism Explorer';
$body_class = 'destinations-page';

require_once __DIR__ . '/includes/header.php';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Get categories for filter
$categories = dbQuery("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");

// Build query
$where = ["d.status = 'active'"];
$params = [];

if ($search) {
    $where[] = "(d.name LIKE ? OR d.short_description LIKE ? OR d.full_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where[] = "c.slug = ?";
    $params[] = $category;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM destinations d 
             LEFT JOIN categories c ON d.category_id = c.id 
             WHERE $whereClause";
$countResult = dbQueryOne($countSql, $params);
$totalItems = $countResult['total'] ?? 0;

// Get destinations
$perPage = ITEMS_PER_PAGE;
$pagination = getPagination($totalItems, $page, $perPage);

$sql = "SELECT d.*, c.name as category_name, c.slug as category_slug,
        (SELECT COUNT(*) FROM attractions a WHERE a.destination_id = d.id AND a.status = 'active') as attraction_count
        FROM destinations d 
        LEFT JOIN categories c ON d.category_id = c.id 
        WHERE $whereClause
        ORDER BY d.created_at DESC
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$destinations = dbQuery($sql, $params);
?>

<div class="page-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #1a3a1c 100%); color: #fff; padding: 4rem 0 2rem; margin-top: 76px;">
    <div class="container">
        <h1 class="display-4 fw-bold"><i class="fas fa-landmark me-3"></i>Destinations</h1>
        <p class="lead">Explore our curated list of tourism destinations</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <form action="" method="GET" class="row g-2">
                    <div class="col-md-8">
                        <input type="text" name="q" class="form-control" placeholder="Search destinations..." 
                               value="<?php echo e($search); ?>" aria-label="Search destinations">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-2"></i>Category
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item <?php echo empty($category) ? 'active' : ''; ?>" 
                               href="?<?php echo $search ? 'q=' . e($search) . '&' : ''; ?>">All Categories</a></li>
                        <?php foreach ($categories as $cat): ?>
                            <li><a class="dropdown-item <?php echo $category === $cat['slug'] ? 'active' : ''; ?>" 
                                   href="?category=<?php echo e($cat['slug']); ?><?php echo $search ? '&q=' . e($search) : ''; ?>">
                                <?php echo e($cat['name']); ?>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Results Count -->
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            Found <strong><?php echo $totalItems; ?></strong> destination(s)
            <?php if ($search): ?>
                for "<strong><?php echo e($search); ?></strong>"
            <?php endif; ?>
            <?php if ($category): ?>
                in <strong><?php echo e($category); ?></strong>
            <?php endif; ?>
        </div>

        <!-- Destinations Grid -->
        <?php if (empty($destinations)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>No destinations found</h3>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
                <a href="?" class="btn btn-primary-custom mt-3">Clear Filters</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($destinations as $dest): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <?php if ($dest['cover_image']): ?>
                                <img src="<?php echo APP_URL; ?>/assets/uploads/<?php echo e($dest['cover_image']); ?>" 
                                     class="card-img-top" alt="<?php echo e($dest['name']); ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'800\' height=\'400\'%3E%3Crect fill=\'%23e9ecef\' width=\'800\' height=\'400\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'24\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EImage Unavailable%3C/text%3E%3C/svg%3E'">
                            <?php else: ?>
                                <div class="card-img-top placeholder-img">
                                    <i class="fas fa-image fa-2x"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <span class="badge bg-primary mb-2"><?php echo e($dest['category_name'] ?? 'General'); ?></span>
                                <h5 class="card-title"><?php echo e($dest['name']); ?></h5>
                                <p class="card-text text-muted"><?php echo e(mb_substr($dest['short_description'] ?? '', 0, 100)); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo e($dest['location'] ?? 'Location not set'); ?>
                                    </small>
                                    <span class="badge bg-success">
                                        <i class="fas fa-camera me-1"></i><?php echo e($dest['attraction_count']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 pb-3">
                                <a href="<?php echo APP_URL; ?>/destinations.php?id=<?php echo e($dest['id']); ?>" 
                                   class="btn btn-primary-custom w-100">
                                    <i class="fas fa-arrow-right me-2"></i>View Details
                                </a>
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
