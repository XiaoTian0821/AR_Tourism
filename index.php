<?php
/**
 * AR Tourism Explorer - Homepage
 */
$page_title = 'Home';
$page_description = 'Discover destinations through augmented reality. Scan posters to unlock interactive experiences.';
$body_class = 'home-page';

require_once __DIR__ . '/includes/header.php';

// Fetch homepage data
$destinations = dbQuery("
    SELECT d.*, c.name as category_name, c.slug as category_slug
    FROM destinations d
    LEFT JOIN categories c ON d.category_id = c.id
    WHERE d.status = 'active'
    ORDER BY d.created_at DESC
    LIMIT 6
");

$attractions = dbQuery("
    SELECT a.*, d.name as destination_name, d.slug as destination_slug, i.image_path
    FROM attractions a
    LEFT JOIN destinations d ON a.destination_id = d.id
    LEFT JOIN attraction_images i ON a.id = i.attraction_id
    WHERE a.status = 'active'
    ORDER BY a.created_at DESC
    LIMIT 9
");

$categories = dbQuery("
    SELECT c.*, COUNT(d.id) as destination_count
    FROM categories c
    LEFT JOIN destinations d ON c.id = d.category_id
    WHERE c.status = 'active'
    GROUP BY c.id
    ORDER BY c.display_order ASC, c.name ASC
");

$stats = getDashboardStats();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Explore Heritage<br>
                        <span class="text-gradient">Through AR</span>
                    </h1>
                    <p class="hero-subtitle">
                        Point your camera at tourism posters to unlock interactive augmented reality experiences. 
                        Discover attractions, watch videos, and explore maps - all through your smartphone.
                    </p>
                    <div class="hero-buttons">
                        <a href="<?php echo APP_URL; ?>/ar.php" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-vr-cardboard me-2"></i>Start AR Experience
                        </a>
                        <a href="<?php echo APP_URL; ?>/destinations.php" class="btn btn-outline-custom btn-lg">
                            <i class="fas fa-compass me-2"></i>Browse Destinations
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block">
                <div class="text-center">
                    <img src="<?php echo APP_URL; ?>/assets/images/ar-qr-final.png"
                         alt="QR Code for AR Experience" 
                         style="max-width: 200px; background: white; padding: 10px; border-radius: 10px;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['destinations']; ?></div>
                    <div class="stat-label">Destinations</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['attractions']; ?></div>
                    <div class="stat-label">Attractions</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['ar_posters']; ?></div>
                    <div class="stat-label">AR Posters</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['categories']; ?></div>
                    <div class="stat-label">Categories</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section bg-light">
    <div class="container">
        <h2 class="section-title">Explore by Category</h2>
        <p class="section-subtitle">Find attractions that match your interests</p>
        
        <div class="category-pills">
            <?php foreach ($categories as $cat): ?>
                <a href="<?php echo APP_URL; ?>/destinations.php?category=<?php echo e($cat['slug']); ?>" 
                   class="category-pill">
                    <i class="fas <?php echo e($cat['icon'] ?? 'fa-tag'); ?> me-1"></i>
                    <?php echo e($cat['name']); ?>
                    <span class="badge bg-secondary ms-1"><?php echo e($cat['destination_count']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Destinations Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Featured Destinations</h2>
        <p class="section-subtitle">Discover amazing places to visit</p>
        
        <div class="row g-4">
            <?php foreach ($destinations as $dest): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card destination-card">
                        <?php if ($dest['cover_image']): ?>
                            <img src="<?php echo APP_URL; ?>/assets/uploads/<?php echo e($dest['cover_image']); ?>" 
                                 class="card-img-top" alt="<?php echo e($dest['name']); ?>"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'800\' height=\'400\'%3E%3Crect fill=\'%23e9ecef\' width=\'800\' height=\'400\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'24\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EImage Unavailable%3C/text%3E%3C/svg%3E'">
                        <?php else: ?>
                            <div class="card-img-top placeholder-img">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-img-overlay">
                            <span class="badge bg-primary mb-2"><?php echo e($dest['category_name'] ?? 'General'); ?></span>
                            <h3 class="card-title"><?php echo e($dest['name']); ?></h3>
                            <p class="card-text"><?php echo e(mb_substr($dest['short_description'] ?? '', 0, 100)); ?>...</p>
                            <a href="<?php echo APP_URL; ?>/destinations.php?id=<?php echo e($dest['id']); ?>" 
                               class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-right me-1"></i>Explore
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?php echo APP_URL; ?>/destinations.php" class="btn btn-primary-custom btn-lg">
                <i class="fas fa-map-marked-alt me-2"></i>View All Destinations
            </a>
        </div>
    </div>
</section>

<!-- AR Experience Section -->
<section class="ar-section section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 ar-content">
                <div class="ar-icon">
                    <i class="fas fa-vr-cardboard"></i>
                </div>
                <h2 class="display-4 fw-bold mb-4">Immersive AR Experience</h2>
                <p class="lead mb-4">
                    Point your camera at any tourism poster and discover hidden layers of information. 
                    View attraction details, watch videos, and open maps - all in augmented reality.
                </p>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Scan posters to unlock content</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>View interactive information cards</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Watch attraction videos</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Open locations in Google Maps</li>
                </ul>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo APP_URL; ?>/ar.php" class="btn btn-primary-custom btn-lg">
                        <i class="fas fa-play me-2"></i>Start AR Now
                    </a>
                    <a href="<?php echo APP_URL; ?>/contact.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center mt-4 mt-lg-0">
                <div class="position-relative">
                    <i class="fas fa-mobile-alt" style="font-size: 12rem; opacity: 0.2;"></i>
                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                        <img src="<?php echo APP_URL; ?>/assets/images/ar-qr-final.png"
                             alt="QR Code for AR Experience" 
                             class="qr-code-img"
                             style="background: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <p class="mt-2 fw-bold">Scan to Begin</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Attractions Section -->
<section class="section bg-light">
    <div class="container">
        <h2 class="section-title">Popular Attractions</h2>
        <p class="section-subtitle">Most visited places by tourists</p>
        
        <div class="row g-4">
            <?php foreach ($attractions as $attr): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card attraction-card h-100">
                        <div class="position-relative">
                            <?php if ($attr['main_image']): ?>
                                <img src="<?php echo APP_URL; ?>/assets/uploads/<?php echo e($attr['main_image']); ?>" 
                                     class="card-img-top" alt="<?php echo e($attr['name']); ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'800\' height=\'400\'%3E%3Crect fill=\'%23e9ecef\' width=\'800\' height=\'400\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'24\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EImage Unavailable%3C/text%3E%3C/svg%3E'">
                            <?php else: ?>
                                <div class="card-img-top placeholder-img">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <?php if ($attr['youtube_url']): ?>
                                <span class="attraction-badge">
                                    <i class="fab fa-youtube me-1"></i>Has Video
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
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?php echo APP_URL; ?>/attractions.php" class="btn btn-primary-custom btn-lg">
                <i class="fas fa-th me-2"></i>View All Attractions
            </a>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Search Destinations</h2>
        <p class="section-subtitle">Find exactly what you're looking for</p>
        
        <form action="<?php echo APP_URL; ?>/destinations.php" method="GET" class="search-box">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search destinations, attractions..." 
                       aria-label="Search">
                <button type="submit" class="btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
