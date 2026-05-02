<?php include "db_connect.php";
$id = $_GET['id'];
$data = $conn->query("SELECT * FROM categories WHERE category_id=$id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><title>Edit Category</title></head>
<body>

<h2>Edit Category</h2>

<form method="POST">
Name: <input type="text" name="category_name" value="<?=$data['category_name']?>" required><br><br>
Description: <input type="text" name="description" value="<?=$data['description']?>"><br><br>
<input type="submit" name="update" value="Update Category">
</form>

<br><a href="category_list.php">Back</a>

<?php
if(isset($_POST['update'])){
    $name = $_POST['category_name'];
    $desc = $_POST['description'];

    $conn->query("UPDATE categories SET category_name='$name', description='$desc' WHERE category_id=$id");

    header("Location: category_list.php");
}
?>
</body>
</html>
<?php
// ==========================================
// api/update_category.php
// ==========================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include "../db_connect.php";

if (isset($_POST['update']) && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        $name = $conn->real_escape_string($_POST['category_name']);
        $desc = $conn->real_escape_string($_POST['description']);
        
        $sql = "UPDATE categories 
                SET category_name='$name', 
                    description='$desc' 
                WHERE category_id=$id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
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

