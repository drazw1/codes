<?php include "db_connect.php";
$id = $_GET['id'];
$conn->query("DELETE FROM categories WHERE category_id=$id");
header("Location: category_list.php");
?>
