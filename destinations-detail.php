<?php
/**
 * AR Tourism Explorer - Destination Detail Page
 */
$page_title = 'Destination Details';
$body_class = 'destination-detail-page';

require_once __DIR__ . '/includes/header.php';

// Get destination ID
$destId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$destSlug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if ($destId) {
    $dest = dbQueryOne("SELECT * FROM destinations WHERE id = ? AND status = 'active'", [$destId]);
} elseif ($destSlug) {
    $dest = dbQueryOne("SELECT * FROM destinations WHERE slug = ? AND status = 'active'", [$destSlug]);
} else {
    redirect(APP_URL . '/destinations.php');
}

if (!$dest) {
    http_response_code(404);
    $page_title = 'Destination Not Found';
    ?>
    <div class="container py-5 text-center">
        <i class="fas fa-exclamation-circle fa-4x text-muted mb-3"></i>
        <h2>Destination Not Found</h2>
        <p class="text-muted">The destination you're looking for doesn't exist or has been removed.</p>
        <a href="<?php echo APP_URL; ?>/destinations.php" class="btn btn-primary-custom mt-3">
            <i class="fas fa-arrow-left me-2"></i>Back to Destinations
        </a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Get attractions for this destination
$attractions = dbQuery("
    SELECT a.*, 
           (SELECT COUNT(*) FROM attraction_images i WHERE i.attraction_id = a.id) as image_count,
           (SELECT image_path FROM attraction_images WHERE attraction_id = a.id ORDER BY display_order ASC LIMIT 1) as first_image
    FROM attractions a
    WHERE a.destination_id = ? AND a.status = 'active'
    ORDER BY a.display_order ASC, a.created_at ASC
", [$dest['id']]);

// Get AR posters associated with this destination
$arPosters = dbQuery("
    SELECT p.* FROM ar_posters p
    WHERE p.status = 'active' AND p.target_status = 'ready'
    LIMIT 3
");

$page_title = $dest['name'] . ' - ' . APP_NAME;
$page_description = $dest['short_description'] ?? 'Explore ' . $dest['name'] . ' destination';
?>

<!-- Page Header -->
<div class="page-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #1a3a1c 100%); color: #fff; padding: 5rem 0 3rem; margin-top: 76px;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/destinations.php" class="text-white-50">Destinations</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?php echo e($dest['name']); ?></li>
            </ol>
        </nav>
        <h1 class="display-4 fw-bold mb-3"><?php echo e($dest['name']); ?></h1>
        <p class="lead mb-3"><?php echo e($dest['location'] ?? 'Tourism Destination'); ?></p>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($dest['google_maps_url']): ?>
                <a href="<?php echo e($dest['google_maps_url']); ?>" target="_blank" class="btn btn-light">
                    <i class="fas fa-map-marker-alt me-2"></i>View on Map
                </a>
            <?php endif; ?>
            <?php if (!empty($arPosters)): ?>
                <a href="<?php echo APP_URL; ?>/ar.php" class="btn btn-primary-custom">
                    <i class="fas fa-vr-cardboard me-2"></i>AR Experience
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Cover Image -->
                <?php if ($dest['cover_image']): ?>
                    <div class="card mb-4 overflow-hidden">
                        <img src="<?php echo APP_URL; ?>/assets/uploads/<?php echo e($dest['cover_image']); ?>" 
                             class="card-img-top" alt="<?php echo e($dest['name']); ?>"
                             style="height: 400px; object-fit: cover;"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'1200\' height=\'400\'%3E%3Crect fill=\'%23e9ecef\' width=\'1200\' height=\'400\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'24\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3ECover Image%3C/text%3E%3C/svg%3E'">
                    </div>
                <?php endif; ?>
                
                <!-- Description -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title h4 mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>About This Destination</h2>
                        <div class="text-muted">
                            <?php echo nl2br(e($dest['full_description'] ?? $dest['short_description'] ?? 'No description available.')); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Attractions -->
                <h2 class="h3 mb-4"><i class="fas fa-camera me-2 text-primary"></i>Attractions</h2>
                
                <?php if (empty($attractions)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No attractions have been added to this destination yet.
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($attractions as $attr): ?>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="position-relative">
                                        <?php if ($attr['main_image']): ?>
                                            <img src="<?php echo APP_URL; ?>/assets/uploads/<?php echo e($attr['main_image']); ?>" 
                                                 class="card-img-top" alt="<?php echo e($attr['name']); ?>"
                                                 style="height: 200px; object-fit: cover;"
                                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'200\'%3E%3Crect fill=\'%23e9ecef\' width=\'400\' height=\'200\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'18\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EImage%3C/text%3E%3C/svg%3E'">
                                        <?php else: ?>
                                            <div class="card-img-top placeholder-img" style="height: 200px;">
                                                <i class="fas fa-image fa-2x"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo e($attr['name']); ?></h5>
                                        <p class="card-text text-muted small"><?php echo e(mb_substr($attr['short_description'] ?? '', 0, 100)); ?>...</p>
                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            <?php if ($attr['google_maps_url']): ?>
                                                <a href="<?php echo e($attr['google_maps_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Map
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($attr['youtube_url']): ?>
                                                <a href="<?php echo e($attr['youtube_url']); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                                    <i class="fab fa-youtube me-1"></i>Video
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?php echo APP_URL; ?>/attractions.php?id=<?php echo e($attr['id']); ?>" class="btn btn-sm btn-primary-custom">
                                                <i class="fas fa-arrow-right me-1"></i>Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Location Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($dest['location']): ?>
                            <p class="mb-2"><i class="fas fa-location-arrow me-2 text-primary"></i><?php echo e($dest['location']); ?></p>
                        <?php endif; ?>
                        <?php if ($dest['latitude'] && $dest['longitude']): ?>
                            <p class="mb-2 text-muted small">
                                <i class="fas fa-globe me-1"></i>
                                Lat: <?php echo e($dest['latitude']); ?>, Lng: <?php echo e($dest['longitude']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($dest['google_maps_url']): ?>
                            <a href="<?php echo e($dest['google_maps_url']); ?>" target="_blank" class="btn btn-primary w-100">
                                <i class="fas fa-directions me-2"></i>Get Directions
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- AR Experience -->
                <?php if (!empty($arPosters)): ?>
                    <div class="card mb-4 bg-dark text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-vr-cardboard fa-3x mb-3" style="color: var(--secondary-color);"></i>
                            <h5 class="card-title">AR Experience Available</h5>
                            <p class="card-text small">Scan tourism posters to unlock augmented reality content for this destination.</p>
                            <a href="<?php echo APP_URL; ?>/ar.php" class="btn btn-primary-custom w-100">
                                <i class="fas fa-play me-2"></i>Start AR Tour
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Share -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-share-alt me-2"></i>Share This Destination</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(getCurrentUrl()); ?>" 
                               target="_blank" class="btn btn-outline-primary btn-sm" aria-label="Share on Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(getCurrentUrl()); ?>&text=<?php echo urlencode($dest['name']); ?>" 
                               target="_blank" class="btn btn-outline-info btn-sm" aria-label="Share on Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode($dest['name'] . ' - ' . getCurrentUrl()); ?>" 
                               target="_blank" class="btn btn-outline-success btn-sm" aria-label="Share on WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyLink()" aria-label="Copy link">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
    }).catch(() => {
        prompt('Copy this link:', url);
    });
}
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
