<?php
/**
 * AR Tourism Explorer - API: Get Destinations
 * Returns destination data for frontend use
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
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 100) : 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Build query
$where = ["d.status = 'active'"];
$params = [];

if ($search) {
    $where[] = "(d.name LIKE ? OR d.short_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where[] = "c.slug = ?";
    $params[] = $category;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM destinations d 
             LEFT JOIN categories c ON d.category_id = c.id 
             WHERE $whereClause";
$countResult = dbQueryOne($countSql, $params);
$totalItems = $countResult['total'] ?? 0;

// Get destinations
$sql = "SELECT d.*, c.name as category_name, c.slug as category_slug,
        (SELECT COUNT(*) FROM attractions a WHERE a.destination_id = d.id AND a.status = 'active') as attraction_count
        FROM destinations d 
        LEFT JOIN categories c ON d.category_id = c.id 
        WHERE $whereClause
        ORDER BY d.created_at DESC
        LIMIT $limit OFFSET $offset";
$destinations = dbQuery($sql, $params);

// Format response
$formatted = [];
foreach ($destinations as $dest) {
    $formatted[] = [
        'id' => $dest['id'],
        'name' => $dest['name'],
        'slug' => $dest['slug'],
        'short_description' => $dest['short_description'],
        'cover_image' => $dest['cover_image'] ? APP_URL . '/assets/uploads/' . $dest['cover_image'] : null,
        'location' => $dest['location'],
        'latitude' => $dest['latitude'],
        'longitude' => $dest['longitude'],
        'google_maps_url' => $dest['google_maps_url'],
        'category_name' => $dest['category_name'],
        'attraction_count' => $dest['attraction_count'],
        'created_at' => $dest['created_at']
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
