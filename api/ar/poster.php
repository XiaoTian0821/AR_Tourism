<?php
/**
 * AR Tourism Explorer - API: Get Poster Data
 * Returns poster and hotspot data for AR experience
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../includes/database.php';

$response = ['success' => false, 'message' => ''];

// Get poster ID
$posterId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$posterId) {
    $response['message'] = 'Invalid poster ID';
    echo json_encode($response);
    exit;
}

// Get poster data
$poster = dbQueryOne("
    SELECT * FROM ar_posters 
    WHERE id = ? AND status = 'active'
", [$posterId]);

if (!$poster) {
    $response['message'] = 'Poster not found or inactive';
    echo json_encode($response);
    exit;
}

// Get hotspots
$hotspots = dbQuery("
    SELECT * FROM ar_hotspots 
    WHERE poster_id = ? AND status = 'active'
    ORDER BY z_index ASC, id ASC
", [$posterId]);

// Add target file path
$poster['target_url'] = APP_URL . '/' . MINDAR_TARGET_DIR . $poster['target_file'];

// Combine response
$response['success'] = true;
$response['poster'] = $poster;
$response['hotspots'] = $hotspots;

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
