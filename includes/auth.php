<?php
/**
 * AR Tourism Explorer - Authentication System
 * Session-based authentication with CSRF protection
 */

require_once __DIR__ . '/database.php';

/**
 * Log activity
 * @param int|null $userId
 * @param string $action
 * @param string $description
 */
function logActivity(?int $userId, string $action, string $description): void {
    $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)";
    dbExecute($sql, [
        $userId,
        $action,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    
    $sql = "SELECT id, username, email, full_name, role, status FROM users WHERE id = ? AND status = 'active'";
    return dbQueryOne($sql, [$_SESSION['user_id']]);
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRFToken(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Get CSRF token HTML input
 * @return string
 */
function csrfField(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCSRFToken() . '">';
}

/**
 * Login user
 * @param string $username
 * @param string $password
 * @param bool $remember
 * @return array ['success' => bool, 'message' => string]
 */
function loginUser(string $username, string $password, bool $remember = false): array {
    // Check login attempts
    $loginKey = 'login_attempts_' . ip2long($_SERVER['REMOTE_ADDR']);
    if (isset($_SESSION[$loginKey]) && $_SESSION[$loginKey]['attempts'] >= MAX_LOGIN_ATTEMPTS) {
        if (time() - $_SESSION[$loginKey]['last_attempt'] < LOGIN_THROTTLE_TIME) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }
        unset($_SESSION[$loginKey]);
    }
    
    // Find user
    $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
    $user = dbQueryOne($sql, [$username, $username]);
    
    if (!$user) {
        recordLoginAttempt();
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        recordLoginAttempt();
        logActivity($user['id'] ?? null, 'login_failed', 'Failed login attempt for ' . $username);
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }
    
    // Clear login attempts on success
    unset($_SESSION[$loginKey]);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_username'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_full_name'] = $user['full_name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in_at'] = time();
    
    // Update last login
    $stmt = getDB()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Log activity
    logActivity($user['id'], 'login', 'Admin logged in from ' . $_SERVER['REMOTE_ADDR']);
    
    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), '/');
        // Store token hash in database (optional enhancement)
    }
    
    return ['success' => true, 'message' => 'Login successful.'];
}

/**
 * Record failed login attempt
 */
function recordLoginAttempt(): void {
    $loginKey = 'login_attempts_' . ip2long($_SERVER['REMOTE_ADDR']);
    if (!isset($_SESSION[$loginKey])) {
        $_SESSION[$loginKey] = ['attempts' => 0, 'last_attempt' => 0];
    }
    $_SESSION[$loginKey]['attempts']++;
    $_SESSION[$loginKey]['last_attempt'] = time();
}

/**
 * Logout user
 */
function logoutUser(): void {
    $userId = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['user_username'] ?? 'unknown';
    
    logActivity($userId, 'logout', 'Admin logged out');
    
    // Clear all session data
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Require admin authentication
 */
function requireAdmin(): void {
    if (!isLoggedIn() || !isAdmin()) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        header('Location: ' . APP_URL . '/admin/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Require authentication (any logged in user)
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/admin/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Check if current page is admin
 * @return bool
 */
function isAdminPage(): bool {
    return strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
}
?>
