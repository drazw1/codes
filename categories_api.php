<?php
// ============================================================
//  categories_api.php
//  PURPOSE : JSON endpoint – returns all categories.
//  CONSUMED: by jQuery/AJAX to populate the category dropdown
//            dynamically on the dashboard.
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

$result     = $conn->query("SELECT category_id, category_name, description FROM categories ORDER BY category_name");
$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = [
        'category_id'   => (int)$row['category_id'],
        'category_name' => $row['category_name'],
        'description'   => $row['description'],
    ];
}

echo json_encode([
    'success'    => true,
    'total'      => count($categories),
    'categories' => $categories,
], JSON_UNESCAPED_UNICODE);
