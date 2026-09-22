<?php
/**
 * AR Tourism Explorer - Discover Penang Poster
 * Beautiful tourism poster for Penang, Malaysia with AR capabilities
 */
$page_title = 'Discover Penang - AR Tourism Poster';
$page_description = 'Scan this poster to explore Penang through augmented reality';

require_once __DIR__ . '/includes/header.php';
?>

<style>
@page {
    size: A4;
    margin: 0;
}

body {
    margin: 0;
    padding: 0;
    background: #f5f5f5;
}

.poster-container {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    background: linear-gradient(180deg, #87CEEB 0%, #B0E0E6 30%, #E0F6FF 60%, #F0F8FF 100%);
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

/* Cloud decorations */
.cloud {
    position: absolute;
    background: white;
    border-radius: 100px;
    opacity: 0.9;
}

.cloud::before {
    content: '';
    position: absolute;
    background: white;
    border-radius: 100px;
}

.cloud1 {
    width: 120px;
    height: 40px;
    top: 50px;
    left: 50px;
}

.cloud1::before {
    width: 50px;
    height: 50px;
    top: -25px;
    left: 20px;
}

.cloud2 {
    width: 100px;
    height: 35px;
    top: 80px;
    right: 80px;
}

.cloud2::before {
    width: 45px;
    height: 45px;
    top: -20px;
    right: 20px;
}

/* Header Section */
.poster-header {
    position: relative;
    text-align: center;
    padding: 40px 30px 30px;
    z-index: 10;
}

.poster-title-main {
    font-family: 'Brush Script MT', cursive;
    font-size: 72px;
    font-weight: 700;
    color: #1a5490;
    text-shadow: 3px 3px 6px rgba(0,0,0,0.1);
    margin: 0;
    line-height: 1;
}

.poster-title-sub {
    font-family: 'Arial', sans-serif;
    font-size: 16px;
    color: #666;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-top: 10px;
}

.poster-slogan {
    position: absolute;
    top: 40px;
    right: 40px;
    font-family: 'Arial', sans-serif;
    font-size: 14px;
    color: #fff;
    background: rgba(26, 84, 144, 0.8);
    padding: 10px 15px;
    border-radius: 20px;
    transform: rotate(15deg);
}

/* Hero Image Section */
.poster-hero {
    position: relative;
    height: 280px;
    margin: 0 30px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    z-index: 5;
}

.poster-hero-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, transparent 50%),
                url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 400"%3E%3Cdefs%3E%3ClinearGradient id="sky" x1="0%25" y1="0%25" x2="0%25" y2="100%25"%3E%3Cstop offset="0%25" style="stop-color:%2387CEEB"/%3E%3Cstop offset="100%25" style="stop-color:%23B0E0E6"/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill="url(%23sky)" width="800" height="400"/%3E%3Cpath d="M0 280 Q200 200 400 260 T800 220 L800 400 L0 400 Z" fill="%232a9d8f" opacity="0.6"/%3E%3Cpath d="M0 320 Q300 280 500 300 T800 280 L800 400 L0 400 Z" fill="%23264653" opacity="0.4"/%3E%3C/svg%3E') center/cover;
}

/* Penang Bridge in hero */
.poster-bridge {
    position: absolute;
    top: 60px;
    left: 0;
    right: 0;
    height: 80px;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 80'%3E%3Cpath d='M0 60 L100 40 L200 50 L300 35 L400 45 L500 30 L600 40 L700 35 L800 50 L800 80 L0 80 Z' fill='%23666'/%3E%3Crect x='100' y='20' width='8' height='40' fill='%23888'/%3E%3Crect x='300' y='15' width='8' height='45' fill='%23888'/%3E%3Crect x='500' y='10' width='8' height='50' fill='%23888'/%3E%3Crect x='700' y='18' width='8' height='42' fill='%23888'/%3E%3Cpath d='M100 20 L400 5 L700 18' stroke='%23333' stroke-width='2' fill='none'/%3E%3C/svg%3E") center/cover no-repeat;
}

.poster-hero-content {
    position: absolute;
    bottom: 20px;
    left: 30px;
    color: white;
    z-index: 2;
}

.poster-hero-title {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 5px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.poster-hero-subtitle {
    font-size: 14px;
    opacity: 0.9;
}

/* Content Sections */
.poster-content {
    padding: 20px 30px;
    position: relative;
    z-index: 10;
}

/* Attraction Cards */
.poster-attractions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.poster-attraction-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
}

.poster-attraction-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.poster-attraction-img {
    height: 140px;
    background-size: cover;
    background-position: center;
    position: relative;
}

.poster-attraction-img::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 50%;
    background: linear-gradient(transparent, rgba(0,0,0,0.3));
}

.poster-attraction-body {
    padding: 12px;
}

.poster-attraction-name {
    font-family: 'Playfair Display', serif;
    font-size: 16px;
    font-weight: 700;
    color: #1a5490;
    margin-bottom: 5px;
}

.poster-attraction-desc {
    font-size: 11px;
    color: #666;
    line-height: 1.4;
}

.poster-attraction-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: linear-gradient(135deg, #f4a261 0%, #e76f51 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 10px;
    font-weight: 700;
    z-index: 2;
}

/* Big Attraction Card */
.poster-attraction-card.featured {
    grid-column: span 2;
    display: flex;
    flex-direction: row;
}

.poster-attraction-card.featured .poster-attraction-img {
    width: 200px;
    height: auto;
    min-height: 150px;
}

.poster-attraction-card.featured .poster-attraction-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Icon Row */
.poster-icons {
    display: flex;
    justify-content: space-around;
    padding: 20px 0;
    margin: 20px 0;
    background: rgba(255,255,255,0.7);
    border-radius: 15px;
}

.poster-icon-item {
    text-align: center;
}

.poster-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #1a5490 0%, #2c7bb6 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    color: white;
    font-size: 20px;
}

.poster-icon-text {
    font-size: 11px;
    color: #666;
    font-weight: 500;
}

/* AR Section */
.poster-ar-section {
    background: linear-gradient(135deg, #1a5490 0%, #0d3b66 100%);
    border-radius: 15px;
    padding: 25px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 20px;
}

.poster-ar-content {
    flex: 1;
}

.poster-ar-title {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    margin-bottom: 10px;
}

.poster-ar-desc {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 15px;
}

.poster-ar-steps {
    display: flex;
    gap: 20px;
}

.poster-ar-step {
    text-align: center;
}

.poster-ar-step-num {
    width: 30px;
    height: 30px;
    background: #f4a261;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin-bottom: 5px;
}

.poster-ar-step-text {
    font-size: 11px;
    opacity: 0.9;
}

.poster-ar-qr {
    width: 100px;
    height: 100px;
    background: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 20px;
}

.poster-ar-qr i {
    font-size: 50px;
    color: #1a5490;
}

/* Footer */
.poster-footer {
    background: #1a1a2e;
    color: white;
    padding: 20px 30px;
    text-align: center;
    margin-top: 20px;
}

.poster-footer-title {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    margin-bottom: 10px;
}

.poster-footer-url {
    font-size: 14px;
    color: #48cae4;
    margin-bottom: 10px;
}

.poster-footer-text {
    font-size: 11px;
    opacity: 0.7;
}

.poster-footer-social {
    margin-top: 15px;
}

.poster-footer-social a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    color: white;
    margin: 0 5px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s;
}

.poster-footer-social a:hover {
    background: #f4a261;
    transform: translateY(-3px);
}

/* AR Hotspot Markers */
.poster-ar-markers {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    z-index: 100;
}

.poster-ar-marker {
    position: absolute;
    width: 40px;
    height: 40px;
    background: rgba(244, 162, 97, 0.9);
    border: 3px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    animation: ar-pulse 2s infinite;
}

.poster-ar-marker::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    border: 2px solid rgba(244, 162, 97, 0.5);
    border-radius: 50%;
    animation: ar-ring 2s infinite;
}

@keyframes ar-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

@keyframes ar-ring {
    0% { transform: scale(1); opacity: 1; }
    100% { transform: scale(1.5); opacity: 0; }
}

/* Print styles */
@media print {
    .poster-container {
        box-shadow: none;
        margin: 0;
    }
    
    body {
        background: white;
    }
}

/* Responsive */
@media screen and (max-width: 800px) {
    .poster-container {
        width: 100%;
        min-height: auto;
    }
    
    .poster-attractions {
        grid-template-columns: 1fr;
    }
    
    .poster-attraction-card.featured {
        flex-direction: column;
    }
    
    .poster-attraction-card.featured .poster-attraction-img {
        width: 100%;
        height: 150px;
    }
    
    .poster-ar-section {
        flex-direction: column;
        text-align: center;
    }
    
    .poster-ar-qr {
        margin-left: 0;
        margin-top: 20px;
    }
    
    .poster-ar-steps {
        justify-content: center;
    }
}
</style>

<div class="poster-container" id="posterPrint">
    <!-- AR Markers (for reference) -->
    <div class="poster-ar-markers">
        <!-- Kek Lok Si Temple - Top Left -->
        <div class="poster-ar-marker" style="top: 45%; left: 15%;" title="Kek Lok Si Temple">1</div>
        <!-- Penang Hill - Top Right -->
        <div class="poster-ar-marker" style="top: 30%; left: 75%;" title="Penang Hill">2</div>
        <!-- Penang Bridge - Top Center -->
        <div class="poster-ar-marker" style="top: 20%; left: 50%;" title="Penang Bridge">3</div>
        <!-- Georgetown - Center Right -->
        <div class="poster-ar-marker" style="top: 55%; left: 70%;" title="Georgetown">4</div>
        <!-- Batu Ferringhi - Bottom Left -->
        <div class="poster-ar-marker" style="top: 75%; left: 20%;" title="Batu Ferringhi">5</div>
        <!-- Penang Food - Bottom Center -->
        <div class="poster-ar-marker" style="top: 80%; left: 50%;" title="Penang Food">6</div>
        <!-- Cheong Fatt Tze Mansion - Bottom Right -->
        <div class="poster-ar-marker" style="top: 70%; left: 80%;" title="Cheong Fatt Tze Mansion">7</div>
    </div>
    
    <!-- Clouds -->
    <div class="cloud cloud1"></div>
    <div class="cloud cloud2"></div>
    
    <!-- Slogan -->
    <div class="poster-slogan">
        Same Island<br>Many Stories ✓
    </div>
    
    <!-- Header -->
    <div class="poster-header">
        <h1 class="poster-title-main">Discover Penang</h1>
        <p class="poster-title-sub">Island of Heritage, Culture & Nature</p>
    </div>
    
    <!-- Hero Section with Bridge -->
    <div class="poster-hero">
        <div class="poster-hero-bg"></div>
        <div class="poster-bridge"></div>
        <div class="poster-hero-content">
            <h2 class="poster-hero-title">Penang Bridge</h2>
            <p class="poster-hero-subtitle">Iconic bridge connecting island to mainland</p>
        </div>
    </div>
    
    <!-- Content -->
    <div class="poster-content">
        <!-- Attraction Cards -->
        <div class="poster-attractions">
            <!-- Kek Lok Si Temple (Featured) -->
            <div class="poster-attraction-card featured">
                <div class="poster-attraction-img" style="background: linear-gradient(135deg, #e76f51 0%, #f4a261 100%);">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 60px; color: white; opacity: 0.8;">
                        <i class="fas fa-temple"></i>
                    </div>
                </div>
                <div class="poster-attraction-body">
                    <div class="poster-attraction-badge">AR Hotspot #1</div>
                    <h3 class="poster-attraction-name">Kek Lok Si Temple</h3>
                    <p class="poster-attraction-desc">One of the largest and most beautiful temples in Malaysia, rich in culture and history. Features a stunning 30-meter tall Guanyin statue.</p>
                </div>
            </div>
            
            <!-- Penang Hill -->
            <div class="poster-attraction-card">
                <div class="poster-attraction-img" style="background: linear-gradient(135deg, #2a9d8f 0%, #264653 100%);">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 40px; color: white; opacity: 0.8;">
                        <i class="fas fa-mountain"></i>
                    </div>
                </div>
                <div class="poster-attraction-body">
                    <div class="poster-attraction-badge">AR Hotspot #2</div>
                    <h3 class="poster-attraction-name">Penang Hill</h3>
                    <p class="poster-attraction-desc">Cool weather, lush greenery and breathtaking views of Georgetown and the sea.</p>
                </div>
            </div>
            
            <!-- Georgetown -->
            <div class="poster-attraction-card">
                <div class="poster-attraction-img" style="background: linear-gradient(135deg, #e9c46a 0%, #f4a261 100%);">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 40px; color: white; opacity: 0.8;">
                        <i class="fas fa-landmark"></i>
                    </div>
                </div>
                <div class="poster-attraction-body">
                    <div class="poster-attraction-badge">AR Hotspot #4</div>
                    <h3 class="poster-attraction-name">Georgetown</h3>
                    <p class="poster-attraction-desc">Explore heritage streets, colorful street art and UNESCO World Heritage sites.</p>
                </div>
            </div>
            
            <!-- Batu Ferringhi -->
            <div class="poster-attraction-card">
                <div class="poster-attraction-img" style="background: linear-gradient(135deg, #48cae4 0%, #00b4d8 100%);">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 40px; color: white; opacity: 0.8;">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                </div>
                <div class="poster-attraction-body">
                    <div class="poster-attraction-badge">AR Hotspot #5</div>
                    <h3 class="poster-attraction-name">Batu Ferringhi</h3>
                    <p class="poster-attraction-desc">Relax on the beach, shop at the night market and enjoy the seaside vibes.</p>
                </div>
            </div>
            
            <!-- Penang Food -->
            <div class="poster-attraction-card">
                <div class="poster-attraction-img" style="background: linear-gradient(135deg, #e76f51 0%, #f4a261 100%);">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 40px; color: white; opacity: 0.8;">
                        <i class="fas fa-utensils"></i>
                    </div>
                </div>
                <div class="poster-attraction-body">
                    <div class="poster-attraction-badge">AR Hotspot #6</div>
                    <h3 class="poster-attraction-name">Penang Food</h3>
                    <p class="poster-attraction-desc">A paradise for food lovers! Try the famous char kway teow, assam laksa, nasi kandar and more.</p>
                </div>
            </div>
            
            <!-- Cheong Fatt Tze Mansion -->
            <div class="poster-attraction-card">
                <div class="poster-attraction-img" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 40px; color: white; opacity: 0.8;">
                        <i class="fas fa-home"></i>
                    </div>
                </div>
                <div class="poster-attraction-body">
                    <div class="poster-attraction-badge">AR Hotspot #7</div>
                    <h3 class="poster-attraction-name">Cheong Fatt Tze Mansion</h3>
                    <p class="poster-attraction-desc">A blend of Chinese and Western architecture, full of history and charm.</p>
                </div>
            </div>
        </div>
        
        <!-- Icons Row -->
        <div class="poster-icons">
            <div class="poster-icon-item">
                <div class="poster-icon"><i class="fas fa-temple"></i></div>
                <div class="poster-icon-text">Cultural<br>Heritage</div>
            </div>
            <div class="poster-icon-item">
                <div class="poster-icon"><i class="fas fa-mountain"></i></div>
                <div class="poster-icon-text">Natural<br>Beauty</div>
            </div>
            <div class="poster-icon-item">
                <div class="poster-icon"><i class="fas fa-utensils"></i></div>
                <div class="poster-icon-text">Delicious<br>Food</div>
            </div>
            <div class="poster-icon-item">
                <div class="poster-icon"><i class="fas fa-camera"></i></div>
                <div class="poster-icon-text">Photo<br>Spots</div>
            </div>
            <div class="poster-icon-item">
                <div class="poster-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="poster-icon-text">Shopping<br>& Local Life</div>
            </div>
            <div class="poster-icon-item">
                <div class="poster-icon"><i class="fas fa-water"></i></div>
                <div class="poster-icon-text">Beach<br>& Relaxation</div>
            </div>
        </div>
        
        <!-- AR Section -->
        <div class="poster-ar-section">
            <div class="poster-ar-content">
                <h2 class="poster-ar-title"><i class="fas fa-qrcode me-2"></i>Scan to Explore</h2>
                <p class="poster-ar-desc">Point your camera at this poster to unlock augmented reality content</p>
                <div class="poster-ar-steps">
                    <div class="poster-ar-step">
                        <div class="poster-ar-step-num">1</div>
                        <div class="poster-ar-step-text">Open AR App</div>
                    </div>
                    <div class="poster-ar-step">
                        <div class="poster-ar-step-num">2</div>
                        <div class="poster-ar-step-text">Select Poster</div>
                    </div>
                    <div class="poster-ar-step">
                        <div class="poster-ar-step-num">3</div>
                        <div class="poster-ar-step-text">Scan & Explore</div>
                    </div>
                </div>
            </div>
            <div class="qr-code-container">
                <img src="<?php echo APP_URL; ?>/assets/images/ar-qr-final.png"
                     alt="QR Code for AR Experience" 
                     class="qr-code-img"
                     style="background: white; padding: 10px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="poster-footer">
        <h3 class="poster-footer-title">Visit Penang <i class="fas fa-heart" style="color: #e76f51;"></i></h3>
        <p class="poster-footer-url">www.artourism.example.com</p>
        <p class="poster-footer-text">Powered by AR Tourism Explorer | Scan any tourism poster to begin your AR journey</p>
        <div class="poster-footer-social">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
        </div>
        <div class="poster-qr-footer" style="margin-top: 15px;">
            <p style="font-size: 12px; opacity: 0.8; margin-bottom: 8px;">Scan to start AR Experience:</p>
            <div class="qr-code-container">
                <img src="<?php echo APP_URL; ?>/assets/images/ar-qr-final.png"
                     alt="QR Code for AR Experience" 
                     class="qr-code-img"
                     style="background: white; padding: 5px; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>

<script>
// Print functionality
document.addEventListener('DOMContentLoaded', function() {
    // Auto-open print dialog if ?print parameter exists
    if (window.location.search.includes('print')) {
        setTimeout(function() {
            window.print();
        }, 500);
    }
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
