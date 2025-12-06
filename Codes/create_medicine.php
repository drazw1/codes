<?php include "db_connect.php"; ?>
<!DOCTYPE html>
<html>
<head><title>Add Medicine</title></head>
<body>

<h2>Add Medicine</h2>

<form method="POST">
Name: <input type="text" name="medicine_name" required><br><br>
Category ID: <input type="number" name="category_id"><br><br>
Supplier ID: <input type="number" name="supplier_id"><br><br>
Price: <input type="number" step="0.01" name="price" required><br><br>
Stock: <input type="number" name="stock" required><br><br>

Prescription Required:
<select name="prescription_required">
    <option value="YES">Yes</option>
    <option value="NO">No</option>
</select><br><br>

<input type="submit" name="save" value="Save Medicine">
</form>

<br><a href="medicine_list.php">Back</a>

<?php
if(isset($_POST['save'])){
    $name = $_POST['medicine_name'];
    $cat = $_POST['category_id'];
    $sup = $_POST['supplier_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $rx = $_POST['prescription_required'];

    $sql = "INSERT INTO medicines (medicine_name, category_id, supplier_id, price, stock, prescription_required)
            VALUES ('$name', $cat, $sup, $price, $stock, '$rx')";

    if($conn->query($sql)){
        echo "<p style='color:green;'>✔ Medicine Added!</p>";
    } else {
        echo "<p style='color:red;'>❌ Error: ".$conn->error."</p>";
    }
}
?>
</body>
</html>
