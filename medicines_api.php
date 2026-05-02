<?php
// ============================================================
//  medicines_api.php
//  PURPOSE : JSON endpoint – returns medicines from the DB.
//  PRODUCES: application/json
//  CONSUMED: by jQuery/AJAX on the dashboard (see dashboard.php)
//
//  Supports query params:
//    ?search=<term>      – filter by medicine name (LIKE)
//    ?category_id=<int>  – filter by category
//    ?low_stock=1        – only stock < 10
//    ?format=pretty      – indent output (for Postman / browser)
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');   // allow same-origin AJAX

require_once 'db_connect.php';
require_once 'JsonSchemaValidator.php';


$conditions = [];
$params     = [];
$types      = '';

if (!empty($_GET['search'])) {
    $conditions[] = "m.medicine_name LIKE ?";
    $params[]     = '%' . $_GET['search'] . '%';
    $types       .= 's';
}

if (!empty($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $conditions[] = "m.category_id = ?";
    $params[]     = (int)$_GET['category_id'];
    $types       .= 'i';
}

if (!empty($_GET['low_stock'])) {
    $conditions[] = "m.stock < 10";
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$sql = "SELECT m.medicine_id,
               m.medicine_name,
               m.category_id,
               c.category_name,
               m.supplier_id,
               m.price,
               m.stock,
               m.prescription_required
        FROM medicines m
        LEFT JOIN categories c ON m.category_id = c.category_id
        $where
        ORDER BY m.medicine_name ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result    = $stmt->get_result();
$medicines = [];

while ($row = $result->fetch_assoc()) {
    // Cast types so JSON output is correct (MySQL returns strings)
    $medicines[] = [
        'medicine_id'          => (int)$row['medicine_id'],
        'medicine_name'        => $row['medicine_name'],
        'category_id'          => $row['category_id'] !== null ? (int)$row['category_id'] : null,
        'category_name'        => $row['category_name'],
        'supplier_id'          => $row['supplier_id'] !== null ? (int)$row['supplier_id'] : null,
        'price'                => (float)$row['price'],
        'stock'                => (int)$row['stock'],
        'prescription_required'=> $row['prescription_required'],
    ];
}

// ── Validate the outgoing payload against schema ─────────────
// This demonstrates JSON Schema validation AT CONSUMPTION TIME
// (i.e. verifying the data we are about to serve is schema-compliant)
$validator     = new JsonSchemaValidator(__DIR__ . '/json');
$schemaFile    = __DIR__ . '/json/medicine_schema.json';
$schema        = json_decode(file_get_contents($schemaFile), true);
$schemaErrors  = [];

foreach ($medicines as $index => $med) {
    if (!$validator->validateSchema($med, $schema)) {
        $schemaErrors["medicine[$index]"] = $validator->getErrors();
    }
}

// ── Build response envelope ───────────────────────────────────
$response = [
    'success'        => true,
    'total'          => count($medicines),
    'schema_valid'   => empty($schemaErrors),
    'schema_errors'  => $schemaErrors,   // empty array if all valid
    'medicines'      => $medicines,
];

$flags = isset($_GET['format']) && $_GET['format'] === 'pretty'
       ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
       : JSON_UNESCAPED_UNICODE;

echo json_encode($response, $flags);
