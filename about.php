<?php
/**
 * AR Tourism Explorer - About Page
 */
$page_title = 'About Us';
$page_description = 'Learn about AR Tourism Explorer and our mission to bring augmented reality to tourism';
$body_class = 'about-page';

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #1a3a1c 100%); color: #fff; padding: 5rem 0 3rem; margin-top: 76px;">
    <div class="container">
        <h1 class="display-4 fw-bold"><i class="fas fa-info-circle me-3"></i>About Us</h1>
        <p class="lead">Bringing augmented reality to tourism experiences</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h2 class="h3 mb-4">Our Mission</h2>
                <p class="text-muted mb-4">
                    AR Tourism Explorer aims to revolutionize the way tourists discover and interact with destinations. 
                    By combining augmented reality technology with traditional tourism materials, we create immersive 
                    experiences that bring information to life.
                </p>
                <p class="text-muted mb-4">
                    Our platform allows visitors to simply point their smartphone camera at a tourism poster or 
                    brochure, and instantly access rich multimedia content including videos, maps, and detailed 
                    attraction information - all overlaid in augmented reality.
                </p>
                
                <h3 class="h4 mb-3">How It Works</h3>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="step-number me-3">
                                <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">1</span>
                            </div>
                            <div>
                                <h5 class="h6 mb-1">Open AR Experience</h5>
                                <p class="small text-muted">Launch the AR page on your smartphone browser</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="step-number me-3">
                                <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">2</span>
                            </div>
                            <div>
                                <h5 class="h6 mb-1">Select a Poster</h5>
                                <p class="small text-muted">Choose a tourism poster from the available options</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="step-number me-3">
                                <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">3</span>
                            </div>
                            <div>
                                <h5 class="h6 mb-1">Point Your Camera</h5>
                                <p class="small text-muted">Point your camera at the tourism poster</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="step-number me-3">
                                <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">4</span>
                            </div>
                            <div>
                                <h5 class="h6 mb-1">Explore AR Content</h5>
                                <p class="small text-muted">Tap hotspots to view attractions and videos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h3 mb-4">Technology Stack</h2>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="tech-item">
                                    <i class="fas fa-server text-primary"></i>
                                    <span>PHP 8.3+</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tech-item">
                                    <i class="fas fa-database text-primary"></i>
                                    <span>MySQL 8+</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tech-item">
                                    <i class="fas fa-vr-cardboard text-warning"></i>
                                    <span>MindAR Image Tracking</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tech-item">
                                    <i class="fas fa-cube text-info"></i>
                                    <span>Three.js</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tech-item">
                                    <i class="fab fa-bootstrap text-purple"></i>
                                    <span>Bootstrap 5</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tech-item">
                                    <i class="fas fa-mobile-alt text-success"></i>
                                    <span>Mobile-First Design</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Features -->
        <div class="row g-4 mt-4">
            <div class="col-12">
                <h2 class="h3 mb-4 text-center">Key Features</h2>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-qrcode fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">QR Code Integration</h5>
                        <p class="card-text text-muted">Quick access to AR experiences through QR codes on tourism materials</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-video fa-3x text-danger mb-3"></i>
                        <h5 class="card-title">Video Support</h5>
                        <p class="card-text text-muted">YouTube video integration for attraction previews and tours</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-map-marked-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Map Integration</h5>
                        <p class="card-text text-muted">Google Maps integration for easy navigation to attractions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
