<?php
/**
 * AR Tourism Explorer - Attraction Detail Page
 */
$page_title = 'Attraction Details';
$body_class = 'attraction-detail-page';

require_once __DIR__ . '/includes/header.php';

// Get attraction ID
$attrId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$attrSlug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if ($attrId) {
    $attraction = dbQueryOne("
        SELECT a.*, d.name as destination_name, d.slug as destination_slug, c.name as category_name
        FROM attractions a
        LEFT JOIN destinations d ON a.destination_id = d.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.id = ? AND a.status = 'active'
    ", [$attrId]);
} elseif ($attrSlug) {
    $attraction = dbQueryOne("
        SELECT a.*, d.name as destination_name, d.slug as destination_slug, c.name as category_name
        FROM attractions a
        LEFT JOIN destinations d ON a.destination_id = d.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.slug = ? AND a.status = 'active'
    ", [$attrSlug]);
} else {
    redirect(APP_URL . '/attractions.php');
}

if (!$attraction) {
    http_response_code(404);
    $page_title = 'Attraction Not Found';
    ?>
    <div class="container py-5 text-center">
        <i class="fas fa-exclamation-circle fa-4x text-muted mb-3"></i>
        <h2>Attraction Not Found</h2>
        <p class="text-muted">The attraction you're looking for doesn't exist or has been removed.</p>
        <a href="<?php echo APP_URL; ?>/attractions.php" class="btn btn-primary-custom mt-3">
            <i class="fas fa-arrow-left me-2"></i>Back to Attractions
        </a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Get attraction images
$images = dbQuery("
    SELECT * FROM attraction_images 
    WHERE attraction_id = ? 
    ORDER BY display_order ASC, id ASC
", [$attraction['id']]);

// Get related attractions
$relatedAttractions = dbQuery("
    SELECT id, name, slug, main_image, short_description
    FROM attractions
    WHERE destination_id = ? AND id != ? AND status = 'active'
    ORDER BY display_order ASC, created_at DESC
    LIMIT 4
", [$attraction['destination_id'], $attraction['id']]);

// YouTube embed URL
$youtubeEmbed = $attraction['youtube_url'] ? getYouTubeEmbedUrl($attraction['youtube_url']) : null;

$page_title = $attraction['name'] . ' - ' . APP_NAME;
$page_description = $attraction['short_description'] ?? 'Explore ' . $attraction['name'] . ' attraction';
?>

<!-- Page Header -->
<div class="page-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #1a3a1c 100%); color: #fff; padding: 5rem 0 3rem; margin-top: 76px;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/destinations.php" class="text-white-50">Destinations</a></li>
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/destinations-detail.php?id=<?php echo e($attraction['destination_id']); ?>" class="text-white-50"><?php echo e($attraction['destination_name']); ?></a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?php echo e($attraction['name']); ?></li>
            </ol>
        </nav>
        <h1 class="display-4 fw-bold mb-3"><?php echo e($attraction['name']); ?></h1>
        <div class="d-flex flex-wrap gap-3">
            <?php if ($attraction['category_name']): ?>
                <span class="badge bg-light text-dark"><i class="fas fa-tag me-1"></i><?php echo e($attraction['category_name']); ?></span>
            <?php endif; ?>
            <?php if ($attraction['youtube_url']): ?>
                <span class="badge bg-danger"><i class="fab fa-youtube me-1"></i>Has Video</span>
            <?php endif; ?>
            <?php if (!empty($images)): ?>
                <span class="badge bg-info"><i class="fas fa-images me-1"></i><?php echo count($images); ?> Photos</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Main Image -->
                <?php if ($attraction['main_image']): ?>
                    <div class="card mb-4 overflow-hidden">
                        <img src="<?php echo APP_URL; ?>/assets/uploads/<?php echo e($attraction['main_image']); ?>" 
                             class="card-img-top" alt="<?php echo e($attraction['name']); ?>"
                             style="height: 450px; object-fit: cover;"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'1200\' height=\'450\'%3E%3Crect fill=\'%23e9ecef\' width=\'1200\' height=\'450\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'24\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EMain Image%3C/text%3E%3C/svg%3E'">
                    </div>
                <?php endif; ?>
                
                <!-- YouTube Video -->
                <?php if ($youtubeEmbed): ?>
                    <div class="card mb-4">
                        <div class="card-body p-0">
                            <div class="ratio ratio-16x9">
                                <iframe src="<?php echo e($youtubeEmbed); ?>?rel=0&modestbranding=1" 
                                        allowfullscreen 
                                        title="<?php echo e($attraction['name']); ?> Video"></iframe>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Description -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title h4 mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>About</h2>
                        <div class="text-muted">
                            <?php echo nl2br(e($attraction['full_description'] ?? $attraction['short_description'] ?? 'No description available.')); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Gallery -->
                <?php if (!empty($images)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-images me-2"></i>Photo Gallery (<?php echo count($images); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <?php foreach ($images as $img): ?>
                                    <div class="col-6 col-md-4">
                                        <img src="<?php echo APP_URL; ?>/assets/uploads/<?php echo e($img['image_path']); ?>" 
                                             class="img-fluid rounded" alt="<?php echo e($img['caption'] ?: 'Gallery image'); ?>"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'200\'%3E%3Crect fill=\'%23e9ecef\' width=\'300\' height=\'200\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'16\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EImage%3C/text%3E%3C/svg%3E'">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Related Attractions -->
                <?php if (!empty($relatedAttractions)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-th me-2"></i>Related Attractions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <?php foreach ($relatedAttractions as $related): ?>
                                    <div class="col-6 col-md-3">
                                        <a href="<?php echo APP_URL; ?>/attractions.php?id=<?php echo e($related['id']); ?>" class="text-decoration-none">
                                            <div class="card h-100">
                                                <?php if ($related['main_image']): ?>
                                                    <img src="<?php echo APP_URL; ?>/assets/uploads/<?php echo e($related['main_image']); ?>" 
                                                         class="card-img-top" alt="<?php echo e($related['name']); ?>"
                                                         style="height: 120px; object-fit: cover;"
                                                         onerror="this.style.display='none'">
                                                <?php endif; ?>
                                                <div class="card-body p-2">
                                                    <p class="card-text small text-truncate"><?php echo e($related['name']); ?></p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($attraction['opening_hours']): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-clock me-2 text-primary"></i>Opening Hours</strong>
                                <p class="mb-0 text-muted"><?php echo e($attraction['opening_hours']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($attraction['entry_information']): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-ticket-alt me-2 text-primary"></i>Entry Information</strong>
                                <p class="mb-0 text-muted"><?php echo e($attraction['entry_information']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($attraction['latitude'] && $attraction['longitude']): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-globe me-2 text-primary"></i>Coordinates</strong>
                                <p class="mb-0 text-muted small">
                                    <?php echo e($attraction['latitude']); ?>, <?php echo e($attraction['longitude']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($attraction['website_url']): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-globe me-2 text-primary"></i>Website</strong>
                                <p class="mb-0">
                                    <a href="<?php echo e($attraction['website_url']); ?>" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-external-link-alt me-1"></i>Visit Website
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Location Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($attraction['google_maps_url']): ?>
                            <a href="<?php echo e($attraction['google_maps_url']); ?>" target="_blank" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-directions me-2"></i>Get Directions
                            </a>
                        <?php endif; ?>
                        <?php if ($attraction['latitude'] && $attraction['longitude']): ?>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-map me-1"></i>
                                Lat: <?php echo e($attraction['latitude']); ?>, Lng: <?php echo e($attraction['longitude']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- AR Experience Card -->
                <div class="card mb-4 bg-dark text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-vr-cardboard fa-3x mb-3" style="color: var(--secondary-color);"></i>
                        <h5 class="card-title">AR Experience</h5>
                        <p class="card-text small">Scan tourism posters to see this attraction in augmented reality.</p>
                        <a href="<?php echo APP_URL; ?>/ar.php" class="btn btn-primary-custom w-100">
                            <i class="fas fa-play me-2"></i>Start AR Tour
                        </a>
                    </div>
                </div>
                
                <!-- Share Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-share-alt me-2"></i>Share</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(getCurrentUrl()); ?>" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(getCurrentUrl()); ?>&text=<?php echo urlencode($attraction['name']); ?>" 
                               target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode($attraction['name'] . ' - ' . getCurrentUrl()); ?>" 
                               target="_blank" class="btn btn-outline-success btn-sm">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyLink()">
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
