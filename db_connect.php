<?php
$servername = "localhost";
$username   = "root";
$password   = "";          // Change in production
$dbname     = "pharmacy_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // In AJAX contexts we need JSON error, not die() HTML
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
        exit;
    }
    die("Connection Failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
