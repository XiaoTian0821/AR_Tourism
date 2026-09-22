<?php
/**
 * AR Tourism Explorer - QR Code Generator API
 * Generates QR codes for URLs
 */

header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Get URL parameter
$url = isset($_GET['url']) ? sanitize($_GET['url']) : '';
$size = isset($_GET['size']) ? intval($_GET['size']) : 200;
$margin = isset($_GET['margin']) ? intval($_GET['margin']) : 2;

if (empty($url)) {
    http_response_code(400);
    die('URL parameter is required');
}

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    die('Invalid URL');
}

// Generate QR code using Google Charts API (simple and reliable)
$qrcodeUrl = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . 
             '&cht=qr&chl=' . urlencode($url) . 
             '&choe=UTF-8&chld=' . ($margin > 0 ? 'M' : '0') . '%7C0';

// Output the QR code image
header('Content-Type: image/png');
readfile($qrcodeUrl);
exit;

/**
 * Simple sanitization function
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
