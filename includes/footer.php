    </main>
    <!-- End Main Content -->
    
    <!-- Footer -->
    <footer class="footer" id="footer">
        <div class="footer-top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <h4 class="footer-brand">
                            <i class="fas fa-vr-cardboard me-2"></i>
                            <?php echo APP_NAME; ?>
                        </h4>
                        <p class="text-muted">Discover destinations through augmented reality. Point your camera at tourism posters to unlock interactive experiences.</p>
                        <div class="social-links">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                        <h5>Quick Links</h5>
                        <ul class="footer-links">
                            <li><a href="<?php echo APP_URL; ?>/"><i class="fas fa-chevron-right me-1"></i>Home</a></li>
                            <li><a href="<?php echo APP_URL; ?>/destinations.php"><i class="fas fa-chevron-right me-1"></i>Destinations</a></li>
                            <li><a href="<?php echo APP_URL; ?>/attractions.php"><i class="fas fa-chevron-right me-1"></i>Attractions</a></li>
                            <li><a href="<?php echo APP_URL; ?>/ar.php"><i class="fas fa-chevron-right me-1"></i>AR Experience</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                        <h5>Resources</h5>
                        <ul class="footer-links">
                            <li><a href="<?php echo APP_URL; ?>/about.php"><i class="fas fa-chevron-right me-1"></i>About Us</a></li>
                            <li><a href="<?php echo APP_URL; ?>/contact.php"><i class="fas fa-chevron-right me-1"></i>Contact</a></li>
                            <li><a href="#"><i class="fas fa-chevron-right me-1"></i>Privacy Policy</a></li>
                            <li><a href="#"><i class="fas fa-chevron-right me-1"></i>Terms of Use</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <h5>AR Experience</h5>
                        <p>Scan tourism posters to unlock augmented reality content.</p>
                        <div class="qr-section">
                            <p class="small text-muted">Scan to start AR experience</p>
                            <div class="qr-code">
                                <img src="<?php echo APP_URL; ?>/assets/images/qr-placeholder.svg" alt="QR Code for AR Experience" class="img-fluid" style="max-width: 120px;" onerror="this.style.display='none'">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0 text-muted small">Powered by PHP & MindAR</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?php echo e($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Global Variables -->
    <script>
        const APP_CONFIG = {
            baseUrl: '<?php echo APP_URL; ?>',
            isHTTPS: <?php echo isHTTPS() ? 'true' : 'false'; ?>,
            csrfToken: '<?php echo generateCSRFToken(); ?>'
        };
    </script>
</body>
</html>
