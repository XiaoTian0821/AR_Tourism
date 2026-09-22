<?php
/**
 * AR Tourism Explorer - Admin Login
 */
$page_title = 'Admin Login';
$body_class = 'admin-login-page';

require_once __DIR__ . '/../includes/header.php';

// Check if already logged in
if (isLoggedIn()) {
    redirect(APP_URL . '/admin/');
}

$error = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Security error. Please try again.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $result = loginUser($username, $password, $remember);
            
            if ($result['success']) {
                $logoutUrl = APP_URL . '/admin/logout.php';
                echo "<script>
                    alert('Login successful!');
                    window.location.href = '" . ($redirect ? APP_URL . $redirect : APP_URL . '/admin/') . "';
                </script>";
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="login-card">
                    <div class="text-center mb-4">
                        <a href="<?php echo APP_URL; ?>/" class="text-decoration-none">
                            <i class="fas fa-vr-cardboard" style="font-size: 3rem; color: var(--primary-color);"></i>
                            <h2 class="mt-2 mb-0"><?php echo APP_NAME; ?></h2>
                        </a>
                        <p class="text-muted">Admin Login</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo e($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <?php echo csrfField(); ?>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username or Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Enter username or email" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary-custom w-100 py-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <a href="<?php echo APP_URL; ?>/" class="text-muted">
                            <i class="fas fa-arrow-left me-1"></i>Back to Website
                        </a>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <small class="text-muted">Default credentials: admin / admin123</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.login-container {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    padding: 2rem 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
}

.login-card {
    background: #fff;
    border-radius: 15px;
    padding: 2.5rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.login-card .form-control {
    border-radius: 10px;
}

.login-card .input-group-text {
    border-radius: 10px 0 0 10px;
    background: var(--light-color);
}

.login-card .form-control {
    border-radius: 0 10px 10px 0;
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
