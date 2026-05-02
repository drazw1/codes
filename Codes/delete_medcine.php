<?php include "db_connect.php";
$id = $_GET['id'];
$conn->query("DELETE FROM medicines WHERE medicine_id=$id");
header("Location: medicine_list.php");
?>
