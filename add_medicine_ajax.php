<?php
// ============================================================
//  ajax/add_medicine_ajax.php
//  PURPOSE : Receives JSON-encoded medicine data from jQuery
//            AJAX POST, validates it against the JSON Schema,
//            then inserts into the DB.
//  RETURNS : JSON response
// ============================================================

header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../JsonSchemaValidator.php';

// ── Only accept AJAX POST ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST method required.']);
    exit;
}

// ── Read raw JSON body sent by jQuery ($.ajax with JSON.stringify) ──
$rawBody = file_get_contents('php://input');
$data    = json_decode($rawBody, true);

// Fall back to $_POST if Content-Type was not application/json
if ($data === null) {
    $data = $_POST;
}

if (empty($data)) {
    echo json_encode(['success' => false, 'message' => 'No data received.']);
    exit;
}

// ── Cast and sanitise ─────────────────────────────────────────
$medicine = [
    'medicine_name'         => trim($data['medicine_name'] ?? ''),
    'category_id'           => isset($data['category_id']) && $data['category_id'] !== ''
                               ? (int)$data['category_id'] : null,
    'supplier_id'           => isset($data['supplier_id']) && $data['supplier_id'] !== ''
                               ? (int)$data['supplier_id'] : null,
    'price'                 => isset($data['price']) ? (float)$data['price'] : 0,
    'stock'                 => isset($data['stock'])  ? (int)$data['stock']  : 0,
    'prescription_required' => in_array($data['prescription_required'] ?? '', ['YES','NO'])
                               ? $data['prescription_required'] : 'NO',
];

// ── JSON Schema validation BEFORE inserting ───────────────────
// This demonstrates validation AT CREATION TIME
$validator  = new JsonSchemaValidator(__DIR__ . '/../json');
$schemaFile = __DIR__ . '/../json/medicine_schema.json';
$schema     = json_decode(file_get_contents($schemaFile), true);

if (!$validator->validateSchema($medicine, $schema)) {
    echo json_encode([
        'success'       => false,
        'message'       => 'Schema validation failed. Data does not meet the required structure.',
        'schema_errors' => $validator->getErrors(),
    ]);
    exit;
}

// ── Insert into DB (prepared statement – no SQL injection) ────
$stmt = $conn->prepare(
    "INSERT INTO medicines (medicine_name, category_id, supplier_id, price, stock, prescription_required)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    'siidis',
    $medicine['medicine_name'],
    $medicine['category_id'],
    $medicine['supplier_id'],
    $medicine['price'],
    $medicine['stock'],
    $medicine['prescription_required']
);

if ($stmt->execute()) {
    $newId = $conn->insert_id;
    $medicine['medicine_id'] = $newId;   // return the new ID to JS

    echo json_encode([
        'success'      => true,
        'message'      => 'Medicine added successfully.',
        'schema_valid' => true,
        'medicine'     => $medicine,      // Return inserted object to jQuery
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $stmt->error,
    ]);
}
