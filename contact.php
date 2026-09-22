<?php
/**
 * AR Tourism Explorer - Contact Page
 */
$page_title = 'Contact Us';
$page_description = 'Get in touch with AR Tourism Explorer team';
$body_class = 'contact-page';

require_once __DIR__ . '/includes/header.php';

$contactSent = false;
$contactMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        
        if ($name && $email && $message) {
            // In production, send email here
            $contactSent = true;
            $contactMessage = 'Thank you for your message! We will get back to you soon.';
        } else {
            $contactMessage = 'Please fill in all required fields.';
        }
    }
}
?>

<div class="page-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #1a3a1c 100%); color: #fff; padding: 5rem 0 3rem; margin-top: 76px;">
    <div class="container">
        <h1 class="display-4 fw-bold"><i class="fas fa-envelope me-3"></i>Contact Us</h1>
        <p class="lead">Have questions? We'd love to hear from you</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if ($contactSent): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo e($contactMessage); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($contactMessage && !$contactSent): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo e($contactMessage); ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-paper-plane me-2"></i>Send Us a Message</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <?php echo csrfField(); ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-12">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject">
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary-custom">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><i class="fas fa-building me-2 text-primary"></i>AR Tourism Explorer HQ</p>
                        <p class="mb-2 text-muted">123 Tourism Street<br>City Center, 50000</p>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-phone me-2"></i>Contact Info</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><i class="fas fa-phone me-2 text-primary"></i>+60 12-345 6789</p>
                        <p class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i>info@artourism.example.com</p>
                        <p class="mb-0"><i class="fas fa-globe me-2 text-primary"></i>www.artourism.example.com</p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Business Hours</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">Monday - Friday: 9:00 AM - 6:00 PM</p>
                        <p class="mb-1">Saturday: 10:00 AM - 4:00 PM</p>
                        <p class="mb-0 text-muted">Sunday: Closed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
