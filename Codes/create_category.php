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
