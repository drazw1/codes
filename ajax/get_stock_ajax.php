<?php
// ============================================================
//  ajax/get_stock_ajax.php
//  PURPOSE : Returns stock level for a single medicine.
//            Demonstrates a minimal, single-purpose AJAX call.
//  RETURNS : JSON { success, medicine_id, medicine_name, stock, status }
// ============================================================

header('Content-Type: application/json');
require_once '../db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid medicine ID.']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT medicine_id, medicine_name, stock FROM medicines WHERE medicine_id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Medicine not found.']);
    exit;
}

// Classify stock status
$stock  = (int)$row['stock'];
$status = match(true) {
    $stock === 0  => 'out_of_stock',
    $stock < 10   => 'low_stock',
    $stock < 50   => 'moderate',
    default       => 'well_stocked',
};

echo json_encode([
    'success'      => true,
    'medicine_id'  => (int)$row['medicine_id'],
    'medicine_name'=> $row['medicine_name'],
    'stock'        => $stock,
    'status'       => $status,
]);
