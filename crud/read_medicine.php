<?php
// api/read_medicine.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow AJAX requests

include "../db_connect.php";

try {
    // Join with categories to get category name
    $sql = "SELECT 
                m.medicine_id,
                m.medicine_name,
                m.category_id,
                c.category_name,
                m.supplier_id,
                m.price,
                m.stock,
                m.prescription_required
            FROM medicines m
            LEFT JOIN categories c ON m.category_id = c.category_id
            ORDER BY m.medicine_id DESC";
    
    $result = $conn->query($sql);
    $medicines = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $medicines[] = $row;
        }
        
        echo json_encode($medicines);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>