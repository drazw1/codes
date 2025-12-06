<?php include "db_connect.php"; ?>
<!DOCTYPE html>
<html>
<head><title>Medicine List</title></head>
<body>

<h2>Medicine List</h2>
<a href="add_medicine.php">+ Add Medicine</a><br><br>
<a href="category_list.php">← Go to Categories</a><br><br>

<table border="1" cellpadding="10">
<tr>
<th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Rx</th><th>Action</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM medicines");
while($row = $result->fetch_assoc()){
    echo "<tr>
    <td>{$row['medicine_id']}</td>
    <td>{$row['medicine_name']}</td>
    <td>{$row['category_id']}</td>
    <td>{$row['price']}</td>
    <td>{$row['stock']}</td>
    <td>{$row['prescription_required']}</td>
    <td>
        <a href='edit_medicine.php?id={$row['medicine_id']}'>Edit</a> |
        <a href='delete_medicine.php?id={$row['medicine_id']}'>Delete</a>
    </td>
    </tr>";
}
?>
</table>
</body>
</html>
