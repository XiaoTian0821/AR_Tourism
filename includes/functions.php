<?php
/**
 * AR Tourism Explorer - Helper Functions
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

/**
 * Generate slug from text
 * @param string $text
 * @return string
 */
function generateSlug(string $text): string {
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = preg_replace('/[^\w\s-]/u', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return preg_replace('/^-+|-+$/', '', $text);
}

/**
 * Format date for display
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate(string $date, string $format = 'M d, Y'): string {
    if (empty($date) || $date === '0000-00-00' || $date === null) {
        return '-';
    }
    try {
        return date($format, strtotime($date));
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Format datetime for display
 * @param string $datetime
 * @param string $format
 * @return string
 */
function formatDateTime(string $datetime, string $format = 'M d, Y H:i'): string {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00' || $datetime === null) {
        return '-';
    }
    try {
        return date($format, strtotime($datetime));
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Get base URL
 * @return string
 */
function getBaseUrl(): string {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = dirname($_SERVER['SCRIPT_NAME']);
    if ($dir === '/') $dir = '';
    return $protocol . '://' . $host . $dir;
}

/**
 * Get current page URL
 * @return string
 */
function getCurrentUrl(): string {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host . $_SERVER['REQUEST_URI'];
}

/**
 * Redirect to URL
 * @param string $url
 * @param int $statusCode
 */
function redirect(string $url, int $statusCode = 302): void {
    header("Location: " . $url, true, $statusCode);
    exit;
}

/**
 * Upload file with validation
 * @param array $file
 * @param string $destination
 * @param array $allowedTypes
 * @param int $maxSize
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function uploadFile(array $file, string $destination, array $allowedTypes = null, int $maxSize = null): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filename' => '', 'error' => 'Upload error: ' . $file['error']];
    }
    
    $allowedTypes = $allowedTypes ?? ALLOWED_IMAGE_TYPES;
    $maxSize = $maxSize ?? MAX_UPLOAD_SIZE;
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'filename' => '', 'error' => 'File too large. Maximum: ' . ($maxSize / 1024 / 1024) . 'MB'];
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'filename' => '', 'error' => 'Invalid file type'];
    }
    
    // Generate safe filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = uniqid('upload_', true) . '.' . $extension;
    $destinationPath = $destination . '/' . $newFilename;
    
    // Ensure directory exists
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
        // Create thumbnail for images
        if (strpos($mimeType, 'image/') === 0) {
            createThumbnail($destinationPath, $destination . '/thumbs/', 300);
        }
        
        return ['success' => true, 'filename' => $newFilename, 'error' => ''];
    }
    
    return ['success' => false, 'filename' => '', 'error' => 'Failed to move uploaded file'];
}

/**
 * Create thumbnail for image
 * @param string $sourcePath
 * @param string $destinationPath
 * @param int $maxWidth
 */
function createThumbnail(string $sourcePath, string $destinationPath, int $maxWidth = 300): void {
    if (!is_dir($destinationPath)) {
        mkdir($destinationPath, 0755, true);
    }
    
    $filename = basename($sourcePath);
    $thumbPath = $destinationPath . $filename;
    
    // Check if thumbnail already exists
    if (file_exists($thumbPath)) {
        return;
    }
    
    // Get image info
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return;
    }
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    
    // Calculate new dimensions
    if ($width > $maxWidth) {
        $newHeight = ($height / $width) * $maxWidth;
        $newWidth = $maxWidth;
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    $newWidth = max(1, (int) round($newWidth));
    $newHeight = max(1, (int) round($newHeight));
    
    // Create image from source
    $source = imagecreatefromstring(file_get_contents($sourcePath));
    if (!$source) {
        return;
    }
    
    // Create thumbnail
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save thumbnail
    $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($thumb, $thumbPath, 85);
            break;
        case 'png':
            imagepng($thumb, $thumbPath, 8);
            break;
        case 'gif':
            imagegif($thumb, $thumbPath);
            break;
        case 'webp':
            imagewebp($thumb, $thumbPath, 85);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($thumb);
}

/**
 * Extract YouTube video ID from URL
 * @param string $url
 * @return string|null
 */
function extractYouTubeId(string $url): ?string {
    $patterns = [
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
        '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
        '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    // Check if it's already a video ID (11 characters)
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
        return $url;
    }
    
    return null;
}

/**
 * Get YouTube embed URL
 * @param string $url
 * @return string|null
 */
function getYouTubeEmbedUrl(string $url): ?string {
    $videoId = extractYouTubeId($url);
    if ($videoId) {
        return 'https://www.youtube.com/embed/' . $videoId;
    }
    return null;
}

/**
 * Pagination helper
 * @param int $total
 * @param int $page
 * @param int $perPage
 * @return array
 */
function getPagination(int $total, int $page, int $perPage): array {
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $total,
        'per_page' => $perPage,
        'offset' => $offset,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}

/**
 * Generate pagination HTML
 * @param string $baseUrl
 * @param array $pagination
 * @return string
 */
function paginationHtml(string $baseUrl, array $pagination): string {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($pagination['has_prev']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . '?page=' . ($pagination['current_page'] - 1)) . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $pagination['current_page'] ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . e($baseUrl . '?page=' . $i) . '">' . $i . '</a></li>';
    }
    
    // Next button
    if ($pagination['has_next']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . '?page=' . ($pagination['current_page'] + 1)) . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Get system settings
 * @param string $key
 * @param string $default
 * @return string
 */
function getSetting(string $key, string $default = ''): string {
    $sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
    $result = dbQueryOne($sql, [$key]);
    return $result ? $result['setting_value'] : $default;
}

/**
 * Set system setting
 * @param string $key
 * @param string $value
 */
function setSetting(string $key, string $value): void {
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?";
    dbExecute($sql, [$key, $value, $value]);
}

/**
 * Check if HTTPS is available
 * @return bool
 */
function isHTTPS(): bool {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443');
}

/**
 * Get QR code data URL (simple implementation)
 * @param string $text
 * @return string
 */
function generateQRCode(string $text): string {
    // Use a QR code API or library
    // For now, return empty - implement with a library in production
    return '';
}

/**
 * Clean datetime string
 * @param string $datetime
 * @return string
 */
function cleanDatetime(string $datetime): string {
    return date('Y-m-d H:i:s', strtotime($datetime));
}

/**
 * Get statistics for dashboard
 * @return array
 */
function getDashboardStats(): array {
    $stats = [];
    
    $stats['destinations'] = dbQueryOne("SELECT COUNT(*) as count FROM destinations WHERE status = 'active'")['count'] ?? 0;
    $stats['attractions'] = dbQueryOne("SELECT COUNT(*) as count FROM attractions WHERE status = 'active'")['count'] ?? 0;
    $stats['ar_posters'] = dbQueryOne("SELECT COUNT(*) as count FROM ar_posters WHERE status = 'active'")['count'] ?? 0;
    $stats['ar_hotspots'] = dbQueryOne("SELECT COUNT(*) as count FROM ar_hotspots WHERE status = 'active'")['count'] ?? 0;
    $stats['categories'] = dbQueryOne("SELECT COUNT(*) as count FROM categories WHERE status = 'active'")['count'] ?? 0;
    $stats['users'] = dbQueryOne("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0;
    
    return $stats;
}

/**
 * Get recent activity
 * @param int $limit
 * @return array
 */
function getRecentActivity(int $limit = 10): array {
    $sql = "SELECT al.*, u.full_name, u.username 
            FROM activity_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            ORDER BY al.created_at DESC 
            LIMIT ?";
    return dbQuery($sql, [$limit]);
}

/**
 * Get recent content
 * @param string $type
 * @param int $limit
 * @return array
 */
function getRecentContent(string $type, int $limit = 5): array {
    switch ($type) {
        case 'attractions':
            $sql = "SELECT a.*, d.name as destination_name 
                    FROM attractions a 
                    LEFT JOIN destinations d ON a.destination_id = d.id 
                    ORDER BY a.created_at DESC 
                    LIMIT ?";
            return dbQuery($sql, [$limit]);
            
        case 'posters':
            $sql = "SELECT * FROM ar_posters ORDER BY created_at DESC LIMIT ?";
            return dbQuery($sql, [$limit]);
            
        case 'destinations':
            $sql = "SELECT d.*, c.name as category_name 
                    FROM destinations d 
                    LEFT JOIN categories c ON d.category_id = c.id 
                    ORDER BY d.created_at DESC 
                    LIMIT ?";
            return dbQuery($sql, [$limit]);
            
        default:
            return [];
    }
}

/**
 * Check if file exists
 * @param string $path
 * @return bool
 */
function fileExists(string $path): bool {
    return file_exists(__DIR__ . '/../' . $path);
}

/**
 * Get file URL
 * @param string $path
 * @return string
 */
function getFileUrl(string $path): string {
    return APP_URL . '/' . ltrim($path, '/');
}
?>
