<?php include "db_connect.php";
$id = $_GET['id'];
$data = $conn->query("SELECT * FROM medicines WHERE medicine_id=$id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><title>Edit Medicine</title></head>
<body>

<h2>Edit Medicine</h2>

<form method="POST">
Name: <input type="text" name="medicine_name" value="<?=$data['medicine_name']?>" required><br><br>
Price: <input type="number" step="0.01" name="price" value="<?=$data['price']?>" required><br><br>
Stock: <input type="number" name="stock" value="<?=$data['stock']?>" required><br><br>

<input type="submit" name="update" value="Update Medicine">
</form>

<br><a href="medicine_list.php">Back</a>

<?php
if(isset($_POST['update'])){
    $name = $_POST['medicine_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $conn->query("UPDATE medicines SET medicine_name='$name', price=$price, stock=$stock WHERE medicine_id=$id");

    header("Location: medicine_list.php");
}
?>
</body>
</html>
