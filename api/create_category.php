<?php include "db_connect.php"; ?>
<!DOCTYPE html>
<html>
<head><title>Add Category</title></head>
<body>

<h2>Add Category</h2>

<form method="POST">
    Category Name: <input type="text" name="category_name" required><br><br>
    Description: <input type="text" name="description"><br><br>
    <input type="submit" name="save" value="Save Category">
</form>

<br><a href="category_list.php">Back to List</a>

<?php
if(isset($_POST['save'])){
    $name = $_POST['category_name'];
    $desc = $_POST['description'];

    $sql = "INSERT INTO categories (category_name, description) VALUES ('$name', '$desc')";

    if($conn->query($sql)){
        echo "<p style='color:green;'>✔ Category Added!</p>";
    } else {
        echo "<p style='color:red;'>❌ Error: " . $conn->error . "</p>";
    }
}
?>
</body>
</html>
<?php
// ==========================================
// api/create_category.php
// ==========================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include "../db_connect.php";

if (isset($_POST['save'])) {
    try {
        $name = $conn->real_escape_string($_POST['category_name']);
        $desc = $conn->real_escape_string($_POST['description']);
        
        $sql = "INSERT INTO categories (category_name, description) VALUES ('$name', '$desc')";
        
        if ($conn->query($sql)) {
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Category added successfully',
                'id' => $conn->insert_id
            ]);
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
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn->close();
?>

<?php
