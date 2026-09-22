<?php
/**
 * AR Tourism Explorer - API: Get Hotspots
 * Returns hotspot data for a poster
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
$posterId = isset($_GET['poster_id']) ? intval($_GET['poster_id']) : 0;

if (!$posterId) {
    $response['message'] = 'Invalid poster ID';
    echo json_encode($response);
    exit;
}

// Check if poster exists and is active
$poster = dbQueryOne("SELECT id, name, target_status FROM ar_posters WHERE id = ? AND status = 'active'", [$posterId]);

if (!$poster) {
    $response['message'] = 'Poster not found or inactive';
    echo json_encode($response);
    exit;
}

if ($poster['target_status'] !== 'ready') {
    $response['message'] = 'AR target not compiled yet';
    echo json_encode($response);
    exit;
}

// Get hotspots with attraction data
$hotspots = dbQuery("
    SELECT h.*, a.name as attraction_name, a.short_description as attraction_description,
           a.main_image as attraction_image, a.youtube_url, a.google_maps_url,
           a.opening_hours, a.latitude, a.longitude
    FROM ar_hotspots h
    LEFT JOIN attractions a ON h.attraction_id = a.id
    WHERE h.poster_id = ? AND h.status = 'active'
    ORDER BY h.z_index ASC, h.id ASC
", [$posterId]);

$response['success'] = true;
$response['poster_id'] = $posterId;
$response['hotspots'] = $hotspots;

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
