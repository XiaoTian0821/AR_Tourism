<?php
require_once __DIR__ . '/../../../includes/config.php';

$query = $_SERVER['QUERY_STRING'] ?? '';
$target = APP_URL . '/admin/ar-hotspots/index.php' . ($query !== '' ? '?' . $query : '');
header('Location: ' . $target, true, 301);
exit;
