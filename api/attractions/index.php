<?php
/**
 * AR Tourism Explorer - API: Get Attractions
 * Returns attraction data for frontend use
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

$response = ['success' => false, 'message' => '', 'data' => []];

// Get query parameters
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$destination = isset($_GET['destination']) ? intval($_GET['destination']) : 0;
$limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 100) : 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Build query
$where = ["a.status = 'active'"];
$params = [];

if ($search) {
    $where[] = "(a.name LIKE ? OR a.short_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($destination) {
    $where[] = "a.destination_id = ?";
    $params[] = $destination;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM attractions a WHERE $whereClause";
$countResult = dbQueryOne($countSql, $params);
$totalItems = $countResult['total'] ?? 0;

// Get attractions
$sql = "SELECT a.*, d.name as destination_name, d.slug as destination_slug,
        (SELECT COUNT(*) FROM attraction_images i WHERE i.attraction_id = a.id) as image_count,
        (SELECT image_path FROM attraction_images WHERE attraction_id = a.id ORDER BY display_order ASC LIMIT 1) as first_image
        FROM attractions a 
        LEFT JOIN destinations d ON a.destination_id = d.id 
        WHERE $whereClause
        ORDER BY a.created_at DESC
        LIMIT $limit OFFSET $offset";
$attractions = dbQuery($sql, $params);

// Format response
$formatted = [];
foreach ($attractions as $attr) {
    $formatted[] = [
        'id' => $attr['id'],
        'name' => $attr['name'],
        'slug' => $attr['slug'],
        'short_description' => $attr['short_description'],
        'main_image' => $attr['main_image'] ? APP_URL . '/assets/uploads/' . $attr['main_image'] : null,
        'first_image' => $attr['first_image'] ? APP_URL . '/assets/uploads/' . $attr['first_image'] : null,
        'youtube_url' => $attr['youtube_url'],
        'google_maps_url' => $attr['google_maps_url'],
        'latitude' => $attr['latitude'],
        'longitude' => $attr['longitude'],
        'opening_hours' => $attr['opening_hours'],
        'entry_information' => $attr['entry_information'],
        'destination_name' => $attr['destination_name'],
        'image_count' => $attr['image_count'],
        'created_at' => $attr['created_at']
    ];
}

$response['success'] = true;
$response['data'] = $formatted;
$response['pagination'] = [
    'current_page' => $page,
    'total_pages' => ceil($totalItems / $limit),
    'total_items' => $totalItems,
    'per_page' => $limit
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
