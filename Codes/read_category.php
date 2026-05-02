<?php include "db_connect.php"; ?>
<!DOCTYPE html>
<html>
<head><title>Category List</title></head>
<body>

<h2>Category List</h2>
<a href="add_category.php">+ Add Category</a><br><br>
<a href="medicine_list.php">→ Go to Medicines</a><br><br>

<table border="1" cellpadding="10">
<tr>
<th>ID</th><th>Category Name</th><th>Description</th><th>Action</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM categories");
while($row = $result->fetch_assoc()){
    echo "<tr>
    <td>{$row['category_id']}</td>
    <td>{$row['category_name']}</td>
    <td>{$row['description']}</td>
    <td>
        <a href='edit_category.php?id={$row['category_id']}'>Edit</a> | 
        <a href='delete_category.php?id={$row['category_id']}'>Delete</a>
    </td>
    </tr>";
}
?>
</table>
</body>
</html>
