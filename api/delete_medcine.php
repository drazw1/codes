<?php
// ==========================================
// api/delete_category.php
// ==========================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include "../db_connect.php";

if (isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        
        $sql = "DELETE FROM categories WHERE category_id=$id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}

$conn->close();
?>
