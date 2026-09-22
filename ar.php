<?php
/**
 * AR Tourism Explorer - AR Experience Page
 * Main AR scanning page using MindAR Image Tracking
 */
$page_title = 'AR Experience';
$page_description = 'Start augmented reality experience by scanning tourism posters';
$body_class = 'ar-page';

require_once __DIR__ . '/includes/header.php';

// Get active AR posters
$ar_posters = dbQuery("SELECT * FROM ar_posters WHERE status = 'active' AND target_status = 'ready' ORDER BY created_at DESC");

// Check HTTPS requirement
$requiresHTTPS = !isHTTPS() && strpos($_SERVER['HTTP_HOST'], 'localhost') === false && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false;
?>

<div class="ar-hero" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 60vh; display: flex; align-items: center; color: #fff; margin-top: 76px;">
    <div class="container text-center">
        <i class="fas fa-vr-cardboard ar-hero-icon mb-4" style="font-size: 5rem; color: var(--secondary-color);"></i>
        <h1 class="display-3 fw-bold mb-3">AR Tourism Experience</h1>
        <p class="lead mb-4">Point your camera at a tourism poster to unlock augmented reality content</p>
        
        <?php if ($requiresHTTPS): ?>
            <div class="alert alert-warning d-inline-block" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Camera access requires <strong>HTTPS</strong>. Please access this site via HTTPS URL.
            </div>
        <?php else: ?>
            <a href="#ar-container" class="btn btn-primary-custom btn-lg px-5 py-3">
                <i class="fas fa-camera me-2"></i>Start AR Experience
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- HTTPS Warning (hidden if OK) -->
<div id="https-warning" style="display: <?php echo $requiresHTTPS ? 'block' : 'none'; ?>;">
    <div class="container py-4">
        <div class="alert alert-danger">
            <h4><i class="fas fa-exclamation-circle me-2"></i>HTTPS Required</h4>
            <p>Camera access in browsers requires a secure context (HTTPS). Since you're not using HTTPS, 
            the AR experience will not work. Please:</p>
            <ul>
                <li>Use <strong>localhost</strong> for local development, or</li>
                <li>Deploy to an <strong>HTTPS-enabled server</strong>, or</li>
                <li>Use a tool like <strong>Ngrok</strong> to create a secure tunnel</li>
            </ul>
        </div>
    </div>
</div>

<section class="section" id="ar-container" style="background: #000;">
    <div class="container-fluid p-0">
        <!-- AR Camera View -->
        <div id="ar-camera-container" style="display: none; position: relative;">
            <video id="ar-video" autoplay playsinline muted style="width: 100%; height: auto; display: block;"></video>
            
            <!-- AR Overlay Canvas -->
            <canvas id="ar-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></canvas>
            
            <!-- AR CSS3D Overlay -->
            <div id="ar-content-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></div>
        </div>
        
        <!-- AR Status Messages -->
        <div id="ar-status" class="text-center py-5">
            <div class="container">
                <div id="ar-status-icon" style="font-size: 4rem; color: var(--secondary-color); margin-bottom: 1rem;">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h2 id="ar-status-text">Prepare for AR Experience</h2>
                <p id="ar-status-desc" class="text-muted">Select a poster below to begin</p>
            </div>
        </div>
        
        <!-- AR Controls -->
        <div id="ar-controls" style="display: none; position: fixed; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.8); padding: 1rem; z-index: 1000;">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <button id="ar-back-btn" class="btn btn-light" onclick="window.history.back()">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </button>
                    <div class="text-white text-center">
                        <small id="ar-tracking-status">Tracking: Not Started</small>
                    </div>
                    <button id="ar-mute-btn" class="btn btn-light">
                        <i class="fas fa-volume-mute me-1" id="ar-mute-icon"></i>Mute
                    </button>
                </div>
            </div>
        </div>
        
        <!-- AR Information Card (hidden by default) -->
        <div id="ar-info-card" style="display: none; position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%); 
             background: #fff; border-radius: 15px; padding: 1.5rem; max-width: 400px; width: 90%; 
             box-shadow: 0 10px 40px rgba(0,0,0,0.3); z-index: 1001;">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" onclick="closeARInfoCard()"></button>
            <div id="ar-info-card-content"></div>
        </div>
    </div>
</section>

<!-- Poster Selection -->
<section class="section" id="poster-selection">
    <div class="container">
        <h2 class="section-title">Select a Poster</h2>
        <p class="section-subtitle">Choose a tourism poster to begin the AR experience</p>
        
        <?php if (empty($ar_posters)): ?>
            <div class="text-center py-5">
                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                <h3>No AR Posters Available</h3>
                <p class="text-muted">There are no AR posters ready for scanning at this time.</p>
                <a href="<?php echo APP_URL; ?>/" class="btn btn-primary-custom mt-3">Return to Home</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($ar_posters as $poster): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 ar-poster-card" onclick="startARExperience(<?php echo e($poster['id']); ?>)" 
                             style="cursor: pointer; transition: transform 0.3s;">
                            <div class="card-body text-center">
                                <?php if ($poster['poster_image']): ?>
                                    <img src="<?php echo APP_URL; ?>/assets/uploads/<?php echo e($poster['poster_image']); ?>" 
                                         class="img-fluid rounded mb-3" alt="<?php echo e($poster['name']); ?>"
                                         style="max-height: 250px; object-fit: cover;"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23e9ecef\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'18\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EPoster Image%3C/text%3E%3C/svg%3E'">
                                <?php else: ?>
                                    <div class="placeholder-img rounded mb-3" style="height: 200px;">
                                        <i class="fas fa-image fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                                <h5 class="card-title"><?php echo e($poster['name']); ?></h5>
                                <p class="card-text text-muted small"><?php echo e(mb_substr($poster['description'] ?? '', 0, 100)); ?>...</p>
                                <button class="btn btn-primary-custom w-100" onclick="event.stopPropagation(); startARExperience(<?php echo e($poster['id']); ?>)">
                                    <i class="fas fa-vr-cardboard me-2"></i>Start AR
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- How It Works -->
<section class="section bg-light">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Simple steps to enjoy the AR experience</p>
        
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="step-circle mb-3">
                    <i class="fas fa-mobile-alt" style="font-size: 2rem; color: var(--primary-color);"></i>
                </div>
                <h5>1. Open on Mobile</h5>
                <p class="text-muted small">Use your smartphone camera</p>
            </div>
            <div class="col-md-3">
                <div class="step-circle mb-3">
                    <i class="fas fa-qrcode" style="font-size: 2rem; color: var(--primary-color);"></i>
                </div>
                <h5>2. Select Poster</h5>
                <p class="text-muted small">Choose a tourism poster</p>
            </div>
            <div class="col-md-3">
                <div class="step-circle mb-3">
                    <i class="fas fa-camera" style="font-size: 2rem; color: var(--primary-color);"></i>
                </div>
                <h5>3. Point Camera</h5>
                <p class="text-muted small">Scan the poster with your camera</p>
            </div>
            <div class="col-md-3">
                <div class="step-circle mb-3">
                    <i class="fas fa-vr-cardboard" style="font-size: 2rem; color: var(--primary-color);"></i>
                </div>
                <h5>4. Explore AR</h5>
                <p class="text-muted small">Interact with AR content</p>
            </div>
        </div>
    </div>
</section>

<!-- QR Code Section -->
<section class="section">
    <div class="container text-center">
        <h2 class="section-title">Scan to Start</h2>
        <p class="section-subtitle">Or scan this QR code with your phone camera</p>
        <div class="qr-code-inline" style="background: #fff; padding: 2rem; border-radius: 15px; display: inline-block; box-shadow: var(--shadow);">
            <img src="<?php echo APP_URL; ?>/assets/images/ar-qr-final.png" alt="QR Code for AR Experience" style="max-width: 200px;"
                 onerror="this.outerHTML='<div style=\'width:200px;height:200px;background:#e9ecef;display:flex;align-items:center;justify-content:center;border-radius:10px;\'><i class=\'fas fa-qrcode fa-3x text-muted\'></i></div>'">
            <p class="mt-2 small text-muted">Scan with your phone camera</p>
        </div>
    </div>
</section>

<script>
let currentPosterId = null;
let arSession = null;

// Start AR Experience
function startARExperience(posterId) {
    currentPosterId = posterId;
    
    // Show loading
    document.getElementById('ar-status').style.display = 'none';
    document.getElementById('poster-selection').style.display = 'none';
    document.getElementById('ar-camera-container').style.display = 'block';
    document.getElementById('ar-controls').style.display = 'block';
    
    // Update status
    updateARStatus('loading', 'Loading AR Experience...', 'Please wait while we prepare the camera');
    
    // Initialize AR
    initARExperience(posterId);
}

// Initialize AR Experience
function initARExperience(posterId) {
    // Get poster data
    fetch('<?php echo APP_URL; ?>/api/ar/poster.php?id=' + posterId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateARStatus('ready', 'Poster Loaded', 'Point your camera at the poster to begin');
                startCamera(data.poster);
            } else {
                updateARStatus('error', 'Error', data.message || 'Failed to load poster data');
            }
        })
        .catch(error => {
            console.error('AR Init Error:', error);
            updateARStatus('error', 'Error', 'Failed to initialize AR experience');
        });
}

// Start Camera
function startCamera(poster) {
    const video = document.getElementById('ar-video');
    
    // Request camera access
    navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: 'environment',
            width: { ideal: 1280 },
            height: { ideal: 720 }
        },
        audio: false
    })
    .then(stream => {
        video.srcObject = stream;
        updateARStatus('tracking', 'Camera Active', 'Point your camera at the tourism poster');
        
        // Start AR detection (simplified - in production would use MindAR)
        startARTracking(video, poster);
    })
    .catch(error => {
        console.error('Camera Error:', error);
        updateARStatus('error', 'Camera Error', 'Unable to access camera. Please allow camera permissions.');
    });
}

// AR Tracking (simplified)
function startARTracking(video, poster) {
    // In production, this would use MindAR library
    // For now, simulate detection after 3 seconds
    setTimeout(() => {
        // Simulate successful detection
        updateARStatus('detected', 'Poster Detected!', 'Tap on hotspots to explore attractions');
        
        // Load hotspots
        loadARHotspots(poster.id);
    }, 3000);
}

// Load AR Hotspots
function loadARHotspots(posterId) {
    fetch('<?php echo APP_URL; ?>/api/ar/hotspots.php?poster_id=' + posterId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.hotspots.length > 0) {
                displayARHotspots(data.hotspots);
            } else {
                updateARStatus('info', 'No Hotspots', 'This poster has no AR content configured');
            }
        })
        .catch(error => {
            console.error('Hotspot Load Error:', error);
        });
}

// Display AR Hotspots
function displayARHotspots(hotspots) {
    const overlay = document.getElementById('ar-content-overlay');
    overlay.innerHTML = '';
    
    hotspots.forEach(hotspot => {
        const el = document.createElement('div');
        el.className = 'ar-hotspot-marker';
        el.style.cssText = `
            position: absolute;
            left: ${hotspot.x * 100}%;
            top: ${hotspot.y * 100}%;
            width: ${hotspot.width * 100}%;
            height: ${hotspot.height * 100}%;
            background: rgba(44, 95, 45, 0.7);
            border: 3px solid #fff;
            border-radius: 50%;
            cursor: pointer;
            pointer-events: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s;
        `;
        el.innerHTML = `<i class="fas fa-plus" style="color: #fff; font-size: 1.5rem;"></i>`;
        el.onclick = () => showARInfoCard(hotspot);
        overlay.appendChild(el);
    });
}

// Show AR Info Card
function showARInfoCard(hotspot) {
    const card = document.getElementById('ar-info-card');
    const content = document.getElementById('ar-info-card-content');
    
    let html = `
        <div class="text-center">
            <h5 class="mb-3">${hotspot.hotspot_name || 'Attraction'}</h5>
    `;
    
    if (hotspot.attraction_id) {
        html += `<p class="text-muted">${hotspot.description || 'Tap to learn more'}</p>`;
        html += `
            <div class="d-grid gap-2 mt-3">
                <a href="#" class="btn btn-primary btn-sm"><i class="fas fa-info-circle me-1"></i>More Info</a>
                <a href="#" class="btn btn-success btn-sm"><i class="fas fa-map-marker-alt me-1"></i>View Map</a>
                <a href="#" class="btn btn-danger btn-sm"><i class="fab fa-youtube me-1"></i>Watch Video</a>
            </div>
        `;
    }
    
    html += `</div>`;
    content.innerHTML = html;
    card.style.display = 'block';
}

// Close AR Info Card
function closeARInfoCard() {
    document.getElementById('ar-info-card').style.display = 'none';
}

// Update AR Status
function updateARStatus(status, text, desc) {
    const icon = document.getElementById('ar-status-icon');
    const statusText = document.getElementById('ar-status-text');
    const statusDesc = document.getElementById('ar-status-desc');
    const trackingStatus = document.getElementById('ar-tracking-status');
    
    const icons = {
        loading: 'fa-spinner fa-spin',
        ready: 'fa-qrcode',
        tracking: 'fa-camera',
        detected: 'fa-check-circle',
        error: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    icon.innerHTML = `<i class="fas ${icons[status] || 'fa-question'}"></i>`;
    statusText.textContent = text;
    statusDesc.textContent = desc || '';
    
    if (trackingStatus) {
        trackingStatus.textContent = 'Tracking: ' + text;
    }
}

// Back button handler
document.getElementById('ar-back-btn').addEventListener('click', function() {
    // Stop camera
    const video = document.getElementById('ar-video');
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
    }
    
    // Hide AR elements
    document.getElementById('ar-camera-container').style.display = 'none';
    document.getElementById('ar-controls').style.display = 'none';
    document.getElementById('ar-info-card').style.display = 'none';
    document.getElementById('ar-content-overlay').innerHTML = '';
    
    // Show selection again
    document.getElementById('poster-selection').style.display = 'block';
    document.getElementById('ar-status').style.display = 'block';
    
    currentPosterId = null;
});
</script>

<style>
.ar-hero-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.ar-poster-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
}

.ar-hotspot-marker:hover {
    transform: scale(1.1);
    background: rgba(244, 162, 97, 0.8);
}

.step-circle {
    width: 80px;
    height: 80px;
    background: rgba(44, 95, 45, 0.1);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.qr-code-inline img {
    width: 200px;
    height: 200px;
    object-fit: contain;
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
