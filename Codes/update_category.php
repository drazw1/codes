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
